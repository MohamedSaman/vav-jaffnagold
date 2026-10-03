<?php

namespace App\Console\Commands;

use App\Models\ProductDetail;
use App\Services\ImageSearch\ImageSearchClient;
use App\Services\ImageSearch\ImageSearchException;
use Illuminate\Console\Command;

/**
 * Operator front-end for the AI visual search index.
 *
 *   php artisan image-search:index status
 *   php artisan image-search:index rebuild            # incremental
 *   php artisan image-search:index rebuild --force    # re-embed everything
 *   php artisan image-search:index sync --id=42       # one product
 *   php artisan image-search:index sync               # every eligible product
 *   php artisan image-search:index health
 */
class ImageSearchIndex extends Command
{
    protected $signature = 'image-search:index
        {action=status : status | rebuild | sync | health | clear}
        {--id=* : Product id(s), for the sync action}
        {--force : Re-embed even when the image is unchanged}
        {--no-prune : Keep vectors for products that are no longer eligible}
        {--chunk=100 : Products per batch when syncing everything}
        {--watch : Poll rebuild progress until it finishes}';

    protected $description = 'Manage the AI visual search (CLIP + FAISS) product index';

    public function handle(ImageSearchClient $client): int
    {
        if (! config('image_search.enabled')) {
            $this->error('Visual search is disabled. Set IMAGE_SEARCH_ENABLED=true in .env.');

            return self::FAILURE;
        }

        if (! $client->isConfigured()) {
            $this->error('Visual search is not configured. Set IMAGE_SEARCH_URL and IMAGE_SEARCH_API_KEY in .env.');

            return self::FAILURE;
        }

        try {
            return match ($this->argument('action')) {
                'status' => $this->showStatus($client),
                'health' => $this->showHealth($client),
                'rebuild' => $this->rebuild($client),
                'sync' => $this->sync($client),
                'clear' => $this->clear($client),
                default => $this->unknownAction(),
            };
        } catch (ImageSearchException $exception) {
            $this->newLine();
            $this->error("Image search service error: {$exception->getMessage()}");

            if ($exception->errorCode) {
                $this->line("  Code: {$exception->errorCode}");
            }

            return self::FAILURE;
        }
    }

    private function unknownAction(): int
    {
        $this->error("Unknown action '{$this->argument('action')}'.");
        $this->line('Valid actions: status, rebuild, sync, health, clear');

        return self::FAILURE;
    }

    private function showHealth(ImageSearchClient $client): int
    {
        $health = $client->health();
        $details = $health['details'] ?? [];

        if (! $health['reachable']) {
            $this->error('Service unreachable at ' . config('image_search.base_url'));
            $this->line('  ' . ($details['message'] ?? 'no details'));

            return self::FAILURE;
        }

        $this->table(['Property', 'Value'], [
            ['URL', config('image_search.base_url')],
            ['Status', $details['status'] ?? 'unknown'],
            ['Model ready', ($details['model_ready'] ?? false) ? 'yes' : 'no'],
            ['CLIP model', $details['clip_model'] ?? '-'],
            ['Device', $details['device'] ?? '-'],
            ['Embedding dim', $details['embedding_dim'] ?? '-'],
            ['Indexed products', $details['total_indexed'] ?? 0],
            ['Uptime (s)', $details['uptime_seconds'] ?? '-'],
        ]);

        return $health['healthy'] ? self::SUCCESS : self::FAILURE;
    }

    private function showStatus(ImageSearchClient $client): int
    {
        $status = $client->status();
        $rebuild = $status['rebuild'] ?? [];

        $eligible = $this->eligibleQuery()->count();

        $this->table(['Property', 'Value'], [
            ['Vectors in index', $status['total_indexed'] ?? 0],
            ['Metadata rows', $status['metadata_rows'] ?? 0],
            ['Eligible products in MySQL', $eligible],
            ['Embedding dimension', $status['embedding_dim'] ?? '-'],
            ['Index type', $status['index_type'] ?? '-'],
            ['Index file', $status['index_path'] ?? '-'],
            ['Last indexed at', $status['last_indexed_at'] ?? 'never'],
            ['Rebuild state', $rebuild['state'] ?? 'idle'],
        ]);

        if (! empty($status['types'])) {
            $this->newLine();
            $this->line('<comment>Indexed by jewelry type:</comment>');
            $rows = [];
            foreach ($status['types'] as $type => $count) {
                $rows[] = [$type, $count];
            }
            $this->table(['Type', 'Products'], $rows);
        }

        $indexed = (int) ($status['total_indexed'] ?? 0);
        if ($indexed !== $eligible) {
            $this->newLine();
            $this->warn("Index holds {$indexed} vectors but {$eligible} products are eligible.");
            $this->line('  Run: php artisan image-search:index rebuild');
        }

        return self::SUCCESS;
    }

    private function rebuild(ImageSearchClient $client): int
    {
        $force = (bool) $this->option('force');

        if ($force && ! $this->confirmForce()) {
            return self::SUCCESS;
        }

        $result = $client->rebuild(force: $force, prune: ! $this->option('no-prune'));

        $this->info($result['message'] ?? 'Rebuild started.');

        if (! $this->option('watch')) {
            $this->line('Poll progress with: php artisan image-search:index status');

            return self::SUCCESS;
        }

        return $this->watchRebuild($client);
    }

    private function confirmForce(): bool
    {
        $this->warn('--force clears the index and re-embeds every product.');
        $this->line('Visual search will return no results until the rebuild finishes.');

        // A non-interactive run (cron, CI) should not hang waiting for input.
        if (! $this->input->isInteractive()) {
            return true;
        }

        return $this->confirm('Continue?', false);
    }

    private function watchRebuild(ImageSearchClient $client): int
    {
        $this->newLine();
        $bar = null;

        // Poll rather than stream: the rebuild runs inside the Python process and
        // publishes progress on its status endpoint.
        while (true) {
            sleep(2);

            $rebuild = $client->status()['rebuild'] ?? [];
            $state = $rebuild['state'] ?? 'idle';
            $total = (int) ($rebuild['total'] ?? 0);

            if ($bar === null && $total > 0) {
                $bar = $this->output->createProgressBar($total);
                $bar->start();
            }

            $bar?->setProgress(min((int) ($rebuild['processed'] ?? 0), $total));

            if (in_array($state, ['completed', 'failed', 'idle'], true)) {
                $bar?->finish();
                $this->newLine(2);

                if ($state === 'failed') {
                    $this->error('Rebuild failed: ' . ($rebuild['error'] ?? 'unknown error'));

                    return self::FAILURE;
                }

                $this->info(sprintf(
                    'Rebuild complete: %d embedded, %d skipped, %d failed, %d pruned.',
                    $rebuild['indexed'] ?? 0,
                    $rebuild['skipped'] ?? 0,
                    $rebuild['failed'] ?? 0,
                    $rebuild['removed'] ?? 0
                ));

                return self::SUCCESS;
            }
        }
    }

    /**
     * Push products to the index from Laravel rather than letting Python read
     * MySQL. Useful when the two do not share a database host.
     */
    private function sync(ImageSearchClient $client): int
    {
        $ids = array_filter((array) $this->option('id'));

        if ($ids !== []) {
            return $this->syncSpecific($client, array_map('intval', $ids));
        }

        $query = $this->eligibleQuery()->with('category');
        $total = $query->count();

        if ($total === 0) {
            $this->warn('No eligible products found. Products need status=active, website_live=1 and an image.');

            return self::SUCCESS;
        }

        $this->info("Syncing {$total} products to the image index...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $indexed = $skipped = $failed = 0;
        $force = (bool) $this->option('force');

        // Chunk by id: the underlying rows are not modified here, so a stable
        // cursor is unnecessary, and chunkById avoids an expensive OFFSET.
        $query->chunkById((int) $this->option('chunk'), function ($products) use (
            $client, $force, $bar, &$indexed, &$skipped, &$failed
        ) {
            try {
                $result = $client->indexProducts($products, force: $force);

                foreach ($result['items'] ?? [] as $item) {
                    match ($item['status'] ?? '') {
                        'indexed' => $indexed++,
                        'skipped' => $skipped++,
                        default => $failed++,
                    };
                }
            } catch (ImageSearchException $exception) {
                $failed += $products->count();
                $this->newLine();
                $this->warn('Batch failed: ' . $exception->getMessage());
            }

            $bar->advance($products->count());
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Embedded: {$indexed}   Skipped (unchanged): {$skipped}   Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function syncSpecific(ImageSearchClient $client, array $ids): int
    {
        $products = ProductDetail::with('category')->whereIn('id', $ids)->get();
        $missing = array_diff($ids, $products->pluck('id')->all());

        foreach ($missing as $id) {
            $this->warn("Product {$id} not found; removing it from the index.");
            $client->deleteProduct((int) $id);
        }

        $failed = 0;

        foreach ($products as $product) {
            try {
                $result = $client->indexProduct($product, force: true);
                $item = $result['items'][0] ?? [];
                $status = $item['status'] ?? 'unknown';

                if ($status === 'failed') {
                    $failed++;
                    $this->error("  #{$product->id} {$product->name}: " . ($item['message'] ?? 'failed'));
                } else {
                    $this->line("  #{$product->id} {$product->name}: {$status}"
                        . (isset($item['jewelry_type']) ? " ({$item['jewelry_type']})" : ''));
                }
            } catch (ImageSearchException $exception) {
                $failed++;
                $this->error("  #{$product->id}: {$exception->getMessage()}");
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function clear(ImageSearchClient $client): int
    {
        $this->warn('This deletes every vector from the index.');

        if ($this->input->isInteractive() && ! $this->confirm('Continue?', false)) {
            return self::SUCCESS;
        }

        // No dedicated client method: clearing is rare, deliberate and destructive,
        // so it stays an explicit call rather than part of the everyday surface.
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'X-API-Key' => config('image_search.api_key'),
            'Accept' => 'application/json',
        ])->timeout(30)->delete(config('image_search.base_url') . '/api/v1/index/products');

        if ($response->failed()) {
            $this->error('Failed to clear the index: ' . $response->body());

            return self::FAILURE;
        }

        $this->info('Index cleared. Run a rebuild to repopulate it.');

        return self::SUCCESS;
    }

    /**
     * Products that belong in the index — the storefront's visibility rules plus
     * a real (non-placeholder) image.
     */
    private function eligibleQuery()
    {
        $query = ProductDetail::query()
            ->where('status', 'active')
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->whereNotIn('image', (array) config('image_search.images.placeholders', []));

        if (config('image_search.sync.website_live_only', true)) {
            $query->where('website_live', true);
        }

        return $query;
    }
}
