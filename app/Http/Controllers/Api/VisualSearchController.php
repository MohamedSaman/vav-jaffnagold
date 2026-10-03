<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductDetail;
use App\Services\ImageSearch\ImageSearchClient;
use App\Services\ImageSearch\ImageSearchException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * JSON API for AI visual product search.
 *
 * Complements the Livewire page: the same capability for the mobile admin app,
 * a future storefront SPA, or anything else that wants results as data.
 *
 * The Python service is never exposed directly — Laravel remains the only
 * public surface, so validation, throttling and product visibility rules are
 * enforced in exactly one place.
 */
class VisualSearchController extends Controller
{
    public function __construct(private readonly ImageSearchClient $client)
    {
    }

    /**
     * POST /api/visual-search
     *
     * Accepts multipart/form-data with an `image` file.
     */
    public function search(Request $request): JsonResponse
    {
        if (! config('image_search.enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Visual search is currently disabled.',
            ], 503);
        }

        $upload = (array) config('image_search.upload');

        $validated = $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimes:' . implode(',', $upload['mimes'] ?? ['jpeg', 'png', 'webp']),
                'mimetypes:' . implode(',', $upload['mime_types'] ?? ['image/jpeg']),
                'max:' . ($upload['max_kilobytes'] ?? 5120),
                'dimensions:min_width=' . ($upload['min_dimension'] ?? 32)
                    . ',min_height=' . ($upload['min_dimension'] ?? 32),
            ],
            'top_k' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('category_lists', 'id')],
            'min_similarity' => ['sometimes', 'numeric', 'between:-1,1'],
            'strategy' => ['sometimes', Rule::in(['boost', 'partition', 'strict'])],
        ]);

        try {
            $response = $this->client->searchByImage($request->file('image'), array_filter([
                'top_k' => $validated['top_k'] ?? (int) config('image_search.search.top_k', 20),
                'category_id' => $validated['category_id'] ?? null,
                'min_similarity' => $validated['min_similarity'] ?? config('image_search.search.min_similarity'),
                'strategy' => $validated['strategy'] ?? config('image_search.search.strategy'),
            ], static fn ($value) => $value !== null));
        } catch (ImageSearchException $exception) {
            Log::warning('Visual search API request failed', [
                'error' => $exception->errorCode,
                'message' => $exception->getMessage(),
            ]);

            // Pass the service's status through when it is a client error, so the
            // caller can distinguish "bad image" from "service down".
            $status = $exception->getCode() >= 400 && $exception->getCode() < 600
                ? (int) $exception->getCode()
                : 502;

            return response()->json([
                'success' => false,
                'error' => $exception->errorCode,
                'message' => $exception->userMessage(),
            ], $status);
        }

        return response()->json([
            'success' => true,
            'query_id' => $response['query_id'] ?? null,
            'detected_type' => $response['detected_type'] ?? null,
            'took_ms' => $response['took_ms'] ?? null,
            'total_indexed' => $response['total_indexed'] ?? 0,
            'count' => count($response['results'] ?? []),
            'results' => $this->hydrate($response['results'] ?? []),
        ]);
    }

    /**
     * POST /api/visual-search/text
     *
     * Text-to-image search over the same vectors.
     */
    public function searchByText(Request $request): JsonResponse
    {
        if (! config('image_search.enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Visual search is currently disabled.',
            ], 503);
        }

        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:200'],
            'top_k' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('category_lists', 'id')],
        ]);

        try {
            $response = $this->client->searchByText($validated['query'], [
                'top_k' => $validated['top_k'] ?? (int) config('image_search.search.top_k', 20),
                'category_id' => $validated['category_id'] ?? null,
            ]);
        } catch (ImageSearchException $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->errorCode,
                'message' => $exception->userMessage(),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'query_id' => $response['query_id'] ?? null,
            'took_ms' => $response['took_ms'] ?? null,
            'count' => count($response['results'] ?? []),
            'results' => $this->hydrate($response['results'] ?? []),
        ]);
    }

    /**
     * GET /api/visual-search/health
     *
     * Whether the AI service is up. Useful for a status dashboard or for hiding
     * the upload UI when the feature cannot serve requests.
     */
    public function health(): JsonResponse
    {
        $health = $this->client->health();

        return response()->json([
            'success' => $health['healthy'],
            'enabled' => (bool) config('image_search.enabled'),
            'reachable' => $health['reachable'],
            'service' => $health['details'] ?? [],
        ], $health['healthy'] ? 200 : 503);
    }

    /**
     * Attach live product data to the AI's ranked ids.
     *
     * The index holds only vectors and a little metadata, so price, stock and
     * visibility must come from MySQL. Re-applying the storefront's visibility
     * filter here is what prevents a stale index from surfacing a hidden product.
     *
     * @param  array<int,array<string,mixed>>  $matches
     * @return array<int,array<string,mixed>>
     */
    private function hydrate(array $matches): array
    {
        if ($matches === []) {
            return [];
        }

        $scores = collect($matches)->keyBy(fn (array $match) => (int) $match['product_id']);

        $products = ProductDetail::query()
            ->with([
                'category:id,category_name',
                'brand:id,brand_name',
                'price:product_id,retail_price,selling_price,discount_price',
                'stock:product_id,available_stock',
            ])
            ->whereIn('id', $scores->keys()->all())
            ->where('status', 'active')
            ->where('website_live', true)
            ->get();

        return $products
            ->map(function (ProductDetail $product) use ($scores) {
                $match = $scores->get($product->id, []);
                $retail = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0);

                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'category' => $product->category?->category_name,
                    'brand' => $product->brand?->brand_name,
                    'image_url' => $this->imageUrl($product),
                    'retail_price' => $retail,
                    // The storefront advertises a flat 10% off retail.
                    'display_price' => round($retail * 0.90, 2),
                    'in_stock' => (int) ($product->stock?->available_stock ?? 0) > 0,
                    'available_stock' => (int) ($product->stock?->available_stock ?? 0),
                    'similarity' => round((float) ($match['similarity'] ?? 0), 4),
                    'similarity_percent' => round(((float) ($match['similarity'] ?? 0)) * 100, 1),
                    'score' => round((float) ($match['score'] ?? 0), 4),
                    'same_type' => (bool) ($match['same_type'] ?? false),
                    'jewelry_type' => $match['jewelry_type'] ?? null,
                    'product_url' => route('product-detail', ['id' => $product->id]),
                ];
            })
            // MySQL returns its own order; similarity order is the point.
            ->sortByDesc('score')
            ->values()
            ->all();
    }

    private function imageUrl(ProductDetail $product): string
    {
        $image = trim((string) ($product->image ?? ''));

        if ($image === '' || $image === 'images/product.jpg') {
            return asset('images/product.jpg');
        }

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
}
