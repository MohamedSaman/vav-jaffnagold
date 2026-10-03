<?php

namespace App\Observers;

use App\Jobs\SyncProductImageIndex;
use App\Models\ProductDetail;
use Illuminate\Support\Facades\Log;

/**
 * Keeps the visual search index synchronised with product changes.
 *
 * Registered in {@see \App\Providers\AppServiceProvider}, so it covers every path
 * that touches a product through Eloquent — the admin panel, the mobile API and
 * artisan commands alike, with no per-caller wiring.
 *
 * Two deliberate constraints:
 *   1. Nothing here talks to the Python service directly. Work is queued, so a
 *      product save never blocks on, or fails because of, the AI service.
 *   2. A sync is only queued when a field that actually affects the index has
 *      changed. A price edit must not cause an unnecessary CLIP forward pass.
 */
class ProductDetailObserver
{
    /**
     * Fields that change what the index holds for a product.
     *
     * ``image`` changes the embedding; ``category_id`` and ``name`` feed jewelry
     * type detection; ``status`` and ``website_live`` decide whether the product
     * belongs in the index at all.
     */
    private const INDEX_RELEVANT_ATTRIBUTES = [
        'image',
        'name',
        'category_id',
        'status',
        'website_live',
    ];

    public function created(ProductDetail $product): void
    {
        if (! $this->syncEnabled()) {
            return;
        }

        // A product is often created before its image is uploaded. Skip it now;
        // the later image update will fire this observer again.
        if (! SyncProductImageIndex::isIndexable($product)) {
            return;
        }

        $this->dispatch($product->id, SyncProductImageIndex::ACTION_UPSERT, 'created');
    }

    public function updated(ProductDetail $product): void
    {
        if (! $this->syncEnabled()) {
            return;
        }

        if (! $product->wasChanged(self::INDEX_RELEVANT_ATTRIBUTES)) {
            return;
        }

        // The job re-checks eligibility and removes the product if it no longer
        // qualifies, so both directions of a visibility change are handled by
        // dispatching an upsert.
        $this->dispatch($product->id, SyncProductImageIndex::ACTION_UPSERT, 'updated');
    }

    public function deleted(ProductDetail $product): void
    {
        if (! $this->syncEnabled()) {
            return;
        }

        $this->dispatch($product->id, SyncProductImageIndex::ACTION_DELETE, 'deleted');
    }

    /**
     * Re-index a restored soft-deleted product.
     *
     * ProductDetail does not currently use SoftDeletes; this is here so the
     * behaviour is correct if that changes.
     */
    public function restored(ProductDetail $product): void
    {
        if (! $this->syncEnabled()) {
            return;
        }

        $this->dispatch($product->id, SyncProductImageIndex::ACTION_UPSERT, 'restored');
    }

    private function dispatch(int $productId, string $action, string $event): void
    {
        try {
            SyncProductImageIndex::dispatch($productId, $action)
                // Wait for the transaction to commit. Dispatching inside one
                // would let a worker read the row before it exists — or sync a
                // change that then gets rolled back.
                ->afterCommit();
        } catch (\Throwable $exception) {
            // Never let an indexing concern break a product save. A queue
            // failure here is repaired by the nightly rebuild.
            Log::error('Could not queue image index sync', [
                'product_id' => $productId,
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function syncEnabled(): bool
    {
        return (bool) config('image_search.enabled') && (bool) config('image_search.sync.enabled');
    }
}
