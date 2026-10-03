<?php

namespace App\Jobs;

use App\Models\ProductDetail;
use App\Services\ImageSearch\ImageSearchClient;
use App\Services\ImageSearch\ImageSearchException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Keeps one product's vector in the FAISS index in step with MySQL.
 *
 * This runs on the queue rather than inline for one important reason: saving a
 * product in the admin panel must never fail, or slow down, because the Python
 * service is restarting. The worst case here is a stale index entry, which the
 * nightly rebuild repairs.
 */
class SyncProductImageIndex implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const ACTION_UPSERT = 'upsert';
    public const ACTION_DELETE = 'delete';

    public int $tries;

    /** Keep the job out of the queue for a minute if the service is restarting. */
    public int $timeout = 60;

    /**
     * The model is intentionally *not* serialised — only its id. A queued job
     * can run seconds after the request, and re-reading gives it the current row
     * instead of a stale snapshot.
     */
    public function __construct(
        public readonly int $productId,
        public readonly string $action = self::ACTION_UPSERT,
    ) {
        $this->tries = (int) config('image_search.sync.tries', 3);
        $this->onQueue((string) config('image_search.sync.queue', 'default'));

        if ($connection = config('image_search.sync.connection')) {
            $this->onConnection($connection);
        }
    }

    /**
     * Escalating waits between attempts: a service restart resolves in seconds,
     * a bad deploy might take minutes.
     *
     * @return array<int,int>
     */
    public function backoff(): array
    {
        return (array) config('image_search.sync.backoff', [30, 120, 600]);
    }

    public function handle(ImageSearchClient $client): void
    {
        if (! config('image_search.enabled') || ! config('image_search.sync.enabled')) {
            return;
        }

        try {
            if ($this->action === self::ACTION_DELETE) {
                $this->removeFromIndex($client);

                return;
            }

            $this->upsertToIndex($client);
        } catch (ImageSearchException $exception) {
            // A 4xx means this payload will never be accepted — retrying just
            // burns queue capacity, so fail permanently and log it for review.
            if (! $exception->retryable) {
                Log::warning('Image index sync rejected; not retrying', [
                    'product_id' => $this->productId,
                    'action' => $this->action,
                    'error' => $exception->errorCode,
                    'message' => $exception->getMessage(),
                ]);
                $this->fail($exception);

                return;
            }

            throw $exception;
        }
    }

    private function upsertToIndex(ImageSearchClient $client): void
    {
        $product = ProductDetail::with('category')->find($this->productId);

        // Deleted between dispatch and execution — make sure it is gone from the
        // index too, rather than leaving a vector pointing at a missing row.
        if ($product === null) {
            $this->removeFromIndex($client);

            return;
        }

        // No longer eligible for the storefront (unpublished, deactivated, or the
        // image was cleared), so it must not appear in visual search results.
        if (! self::isIndexable($product)) {
            Log::info('Product no longer indexable; removing from image index', [
                'product_id' => $this->productId,
            ]);
            $this->removeFromIndex($client);

            return;
        }

        $result = $client->indexProduct($product, force: true);
        $item = $result['items'][0] ?? [];

        Log::info('Product synced to image index', [
            'product_id' => $this->productId,
            'status' => $item['status'] ?? 'unknown',
            'jewelry_type' => $item['jewelry_type'] ?? null,
            'total_indexed' => $result['total_indexed'] ?? null,
        ]);

        // A per-item failure is usually a missing or corrupt image file. That is
        // a data problem an operator must fix, so surface it without retrying.
        if (($item['status'] ?? null) === 'failed') {
            Log::warning('Image index rejected the product image', [
                'product_id' => $this->productId,
                'reason' => $item['message'] ?? null,
                'image' => $product->getRawOriginal('image'),
            ]);
        }
    }

    private function removeFromIndex(ImageSearchClient $client): void
    {
        $result = $client->deleteProduct($this->productId);

        Log::info('Product removed from image index', [
            'product_id' => $this->productId,
            'removed' => $result['removed'] ?? false,
        ]);
    }

    /**
     * Whether a product belongs in the visual search index.
     *
     * Mirrors the storefront's own visibility rules plus the requirement of a
     * real image — a placeholder carries no visual signal worth indexing.
     */
    public static function isIndexable(ProductDetail $product): bool
    {
        if ($product->status !== 'active') {
            return false;
        }

        if (config('image_search.sync.website_live_only', true) && ! $product->website_live) {
            return false;
        }

        $image = trim((string) ($product->getRawOriginal('image') ?? ''));

        if ($image === '') {
            return false;
        }

        return ! in_array($image, (array) config('image_search.images.placeholders', []), true);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Image index sync failed permanently', [
            'product_id' => $this->productId,
            'action' => $this->action,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Collapse duplicate jobs for the same product (via ShouldBeUnique).
     *
     * Saving a product in the admin panel can fire several update events in one
     * request; without this the queue would embed the same image repeatedly.
     */
    public function uniqueId(): string
    {
        return "{$this->action}:{$this->productId}";
    }

    /**
     * Release the uniqueness lock after this many seconds even if the job never
     * completed, so a crashed worker cannot block a product from ever syncing again.
     */
    public function uniqueFor(): int
    {
        return 900;
    }
}
