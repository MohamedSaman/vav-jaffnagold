<?php

namespace App\Services\ImageSearch;

use App\Models\ProductDetail;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The single place Laravel talks to the Python image search microservice.
 *
 * Every method either returns decoded data or throws {@see ImageSearchException},
 * so callers never have to reason about HTTP status codes themselves.
 */
class ImageSearchClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly array $timeouts,
        private readonly array $retries,
        private readonly array $imageConfig,
    ) {
    }

    public static function fromConfig(): self
    {
        return new self(
            baseUrl: (string) config('image_search.base_url'),
            apiKey: (string) config('image_search.api_key'),
            timeouts: (array) config('image_search.timeouts'),
            retries: (array) config('image_search.retries'),
            imageConfig: (array) config('image_search.images'),
        );
    }

    // ------------------------------------------------------------------ search

    /**
     * Find products visually similar to an uploaded image.
     *
     * @param  UploadedFile|string  $image  An upload, or an absolute path to a readable file.
     * @param  array{top_k?:int,category_id?:int,min_similarity?:float,strategy?:string,detect_type?:bool}  $options
     * @return array{query_id:string,detected_type:?array,strategy:string,total_indexed:int,returned:int,took_ms:float,results:array<int,array>}
     *
     * @throws ImageSearchException
     */
    public function searchByImage(UploadedFile|string $image, array $options = []): array
    {
        [$contents, $filename] = $this->readImage($image);

        $form = array_filter([
            'top_k' => $options['top_k'] ?? null,
            'category_id' => $options['category_id'] ?? null,
            'min_similarity' => $options['min_similarity'] ?? null,
            'strategy' => $options['strategy'] ?? null,
            'detect_type' => array_key_exists('detect_type', $options)
                ? ($options['detect_type'] ? 'true' : 'false')
                : null,
        ], static fn ($value) => $value !== null);

        // Multipart requests are not retried: re-sending several megabytes on a
        // timeout would make a slow situation worse, and the customer is waiting.
        $response = $this->send(
            fn (PendingRequest $request) => $request
                ->asMultipart()
                ->attach('image', $contents, $filename)
                ->post($this->url('/api/v1/search'), $form),
            timeout: $this->timeout('search'),
            retryable: false,
            operation: 'search'
        );

        return $response->json();
    }

    /**
     * Search the same vectors with a text description (CLIP is multi-modal).
     *
     * @throws ImageSearchException
     */
    public function searchByText(string $query, array $options = []): array
    {
        $response = $this->send(
            fn (PendingRequest $request) => $request->post($this->url('/api/v1/search/text'), array_filter([
                'query' => $query,
                'top_k' => $options['top_k'] ?? null,
                'category_id' => $options['category_id'] ?? null,
                'min_similarity' => $options['min_similarity'] ?? null,
            ], static fn ($value) => $value !== null)),
            timeout: $this->timeout('search'),
            operation: 'search_text'
        );

        return $response->json();
    }

    // ------------------------------------------------------------- index writes

    /**
     * Add or replace one product in the index. Idempotent.
     *
     * @throws ImageSearchException
     */
    public function indexProduct(ProductDetail $product, bool $force = false): array
    {
        $payload = $this->buildPayload($product, $force);

        $response = $this->send(
            fn (PendingRequest $request) => $request->post($this->url('/api/v1/index/products'), $payload),
            timeout: $this->timeout('index'),
            operation: 'index_product'
        );

        return $response->json();
    }

    /**
     * Update an already-indexed product. Always re-embeds the image.
     *
     * @throws ImageSearchException
     */
    public function updateProduct(ProductDetail $product): array
    {
        $payload = $this->buildPayload($product, force: true);

        $response = $this->send(
            fn (PendingRequest $request) => $request->put(
                $this->url("/api/v1/index/products/{$product->id}"),
                $payload
            ),
            timeout: $this->timeout('index'),
            operation: 'update_product'
        );

        return $response->json();
    }

    /**
     * Index many products in one call — much faster than looping.
     *
     * @param  iterable<ProductDetail>  $products
     *
     * @throws ImageSearchException
     */
    public function indexProducts(iterable $products, bool $force = false): array
    {
        $payload = [];
        foreach ($products as $product) {
            $payload[] = $this->buildPayload($product, $force);
        }

        if ($payload === []) {
            return ['success' => true, 'total_indexed' => 0, 'items' => []];
        }

        $response = $this->send(
            fn (PendingRequest $request) => $request->post(
                $this->url('/api/v1/index/products/bulk'),
                ['products' => $payload]
            ),
            timeout: $this->timeout('index') * 3,
            operation: 'index_bulk'
        );

        return $response->json();
    }

    /**
     * Remove a product from the index.
     *
     * A product that was never indexed is reported as already gone rather than
     * treated as a failure, so the delete observer never has to retry.
     *
     * @throws ImageSearchException
     */
    public function deleteProduct(int $productId): array
    {
        try {
            $response = $this->send(
                fn (PendingRequest $request) => $request->delete($this->url("/api/v1/index/products/{$productId}")),
                timeout: $this->timeout('index'),
                operation: 'delete_product'
            );

            return $response->json();
        } catch (ImageSearchException $exception) {
            if ($exception->errorCode === 'product_not_indexed') {
                return ['success' => true, 'product_id' => $productId, 'removed' => false];
            }

            throw $exception;
        }
    }

    // -------------------------------------------------------------- maintenance

    /**
     * Start a full rebuild. Returns as soon as the service accepts the job (202).
     *
     * @throws ImageSearchException
     */
    public function rebuild(bool $force = false, bool $prune = true): array
    {
        $response = $this->send(
            fn (PendingRequest $request) => $request->post($this->url('/api/v1/index/rebuild'), [
                'force' => $force,
                'prune' => $prune,
            ]),
            timeout: $this->timeout('rebuild'),
            operation: 'rebuild'
        );

        return $response->json();
    }

    /**
     * Index statistics, including live rebuild progress.
     *
     * @throws ImageSearchException
     */
    public function status(): array
    {
        $response = $this->send(
            fn (PendingRequest $request) => $request->get($this->url('/api/v1/index/status')),
            timeout: $this->timeout('health'),
            operation: 'status'
        );

        return $response->json();
    }

    /**
     * Service health. Never throws — returns a shaped array either way, so it is
     * safe to call from a status page or a Blade view.
     */
    public function health(): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->connectTimeout($this->timeout('connect'))
                ->timeout($this->timeout('health'))
                ->acceptJson()
                ->get($this->url('/health'));

            $body = $response->json() ?? [];

            return [
                'reachable' => true,
                'healthy' => $response->successful() && ($body['status'] ?? null) === 'ok',
                'status_code' => $response->status(),
                'details' => $body,
            ];
        } catch (Throwable $exception) {
            Log::warning('Image search health check failed', [
                'url' => $this->baseUrl,
                'error' => $exception->getMessage(),
            ]);

            return [
                'reachable' => false,
                'healthy' => false,
                'status_code' => 0,
                'details' => ['message' => $exception->getMessage()],
            ];
        }
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    // -------------------------------------------------------------- internals

    /**
     * Build the payload describing one product for the indexer.
     *
     * Prefers the raw ``image`` column so Python can read the file straight off
     * disk. The model's accessor substitutes a placeholder for empty values, so
     * getRawOriginal() is used to see what is really stored.
     */
    private function buildPayload(ProductDetail $product, bool $force = false): array
    {
        $rawImage = (string) ($product->getRawOriginal('image') ?? '');

        $payload = [
            'product_id' => (int) $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'category_id' => $product->category_id !== null ? (int) $product->category_id : null,
            'category_name' => $product->category?->category_name,
            'force' => $force,
        ];

        if (($this->imageConfig['send_absolute_urls'] ?? false) && $rawImage !== '') {
            $payload['image_url'] = $this->absoluteImageUrl($rawImage);
        } else {
            $payload['image'] = $rawImage;
        }

        return $payload;
    }

    /**
     * Turn a stored image path into a URL the Python service can fetch.
     *
     * Mirrors the resolution the storefront uses, so both agree on which file a
     * given database value refers to.
     */
    private function absoluteImageUrl(string $image): string
    {
        $image = trim($image);

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        if (str_starts_with($image, 'storage/images/')) {
            return url('/product-image-serve/' . substr($image, strlen('storage/images/')));
        }

        if (str_contains($image, '/')) {
            return asset(ltrim($image, '/'));
        }

        return url('/product-image-serve/' . ltrim($image, '/'));
    }

    /**
     * @return array{0:string,1:string} raw bytes and a filename for the multipart part
     *
     * @throws ImageSearchException
     */
    private function readImage(UploadedFile|string $image): array
    {
        if ($image instanceof UploadedFile) {
            $contents = @file_get_contents($image->getRealPath());
            $filename = $image->getClientOriginalName() ?: 'upload.jpg';
        } else {
            $contents = @file_get_contents($image);
            $filename = basename($image) ?: 'upload.jpg';
        }

        if ($contents === false || $contents === '') {
            throw new ImageSearchException(
                'The uploaded image could not be read from temporary storage.',
                422,
                errorCode: 'invalid_image'
            );
        }

        return [$contents, $filename];
    }

    /**
     * Execute a request, translating transport and HTTP failures into
     * {@see ImageSearchException}.
     *
     * @param  callable(PendingRequest):Response  $callback
     *
     * @throws ImageSearchException
     */
    private function send(
        callable $callback,
        float $timeout,
        bool $retryable = true,
        string $operation = 'request'
    ): Response {
        $request = Http::withHeaders($this->headers())
            ->connectTimeout($this->timeout('connect'))
            ->timeout($timeout)
            ->acceptJson();

        $times = $retryable ? max(1, (int) ($this->retries['times'] ?? 1)) : 1;
        if ($times > 1) {
            $request = $request->retry(
                $times,
                (int) ($this->retries['sleep'] ?? 200),
                // Retry only on connection-level failures. A 4xx will never
                // become a 2xx, and retrying a 5xx that is doing real work
                // (indexing) risks duplicating effort.
                fn (Throwable $exception) => $exception instanceof ConnectionException,
                throw: false
            );
        }

        try {
            $response = $callback($request);
        } catch (ConnectionException $exception) {
            Log::error('Image search service unreachable', [
                'operation' => $operation,
                'url' => $this->baseUrl,
                'error' => $exception->getMessage(),
            ]);

            throw ImageSearchException::unreachable($exception->getMessage(), $exception);
        } catch (RequestException $exception) {
            throw ImageSearchException::fromResponse(
                $exception->response->status(),
                $exception->response->json() ?? []
            );
        } catch (Throwable $exception) {
            Log::error('Image search request failed unexpectedly', [
                'operation' => $operation,
                'error' => $exception->getMessage(),
            ]);

            throw new ImageSearchException(
                'The image search request could not be completed.',
                500,
                $exception,
                retryable: true,
                errorCode: 'internal_error'
            );
        }

        if ($response->failed()) {
            $body = $response->json() ?? [];

            Log::warning('Image search service returned an error', [
                'operation' => $operation,
                'status' => $response->status(),
                'error' => $body['error'] ?? null,
                'message' => $body['message'] ?? $response->body(),
            ]);

            throw ImageSearchException::fromResponse($response->status(), $body);
        }

        return $response;
    }

    private function headers(): array
    {
        $headers = ['Accept' => 'application/json'];

        if ($this->apiKey !== '') {
            $headers['X-API-Key'] = $this->apiKey;
        }

        return $headers;
    }

    private function url(string $path): string
    {
        return $this->baseUrl . $path;
    }

    private function timeout(string $key): float
    {
        return (float) ($this->timeouts[$key] ?? 15);
    }
}
