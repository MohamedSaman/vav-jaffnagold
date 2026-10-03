<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Services\PostTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPostTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:sync-tracking 
                            {--force : Force sync even finalized or delivered orders}
                            {--order= : Specific Sale ID to sync}
                            {--delay=2 : Delay in seconds between requests to prevent IP block}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically fetch and sync live tracking status from Sri Lanka Post (BePost) for active shipments';

    /**
     * Execute the console command.
     */
    public function handle(PostTrackingService $postService): int
    {
        $force = (bool) $this->option('force');
        $orderId = $this->option('order');
        $delay = max(1, (int) $this->option('delay'));

        $this->info('🚀 Starting Sri Lanka Post (BePost) Tracking Sync...');

        if ($orderId) {
            $sale = Sale::with(['deliverySale', 'postOrderDetail'])->find($orderId);
            if (! $sale) {
                $this->error("❌ Order #{$orderId} not found.");

                return Command::FAILURE;
            }

            if (! $sale->deliverySale || empty($sale->deliverySale->tracking_number)) {
                $this->error("❌ Order #{$orderId} does not have a Post tracking number.");

                return Command::FAILURE;
            }

            $this->line("📦 Syncing single Order #{$sale->invoice_number} (Barcode: {$sale->deliverySale->tracking_number})...");
            $result = $postService->syncOrder($sale);

            if ($result['updated']) {
                $this->info("✅ Order #{$sale->invoice_number} updated: {$result['status_detail']} (Delivery Status: {$result['new_status']})");

                return Command::SUCCESS;
            } else {
                $this->warn("⚠️ Order #{$sale->invoice_number}: ".($result['message'] ?? 'Could not update tracking.'));

                return Command::FAILURE;
            }
        }

        // Active tracking pipeline query
        $query = Sale::with(['deliverySale', 'postOrderDetail'])
            ->whereHas('deliverySale', function ($dq) {
                $dq->whereRaw('LOWER(delivery_method) = ?', ['post'])
                    ->whereNotNull('tracking_number')
                    ->where('tracking_number', '!=', '');
            });

        if (! $force) {
            // Exclude already delivered/cancelled/returned orders or finalized tracking
            $query->whereNotIn('delivery_status', ['delivered', 'cancelled', 'returned'])
                ->whereDoesntHave('postOrderDetail', function ($pq) {
                    $pq->where('is_final', true);
                });
        }

        $sales = $query->get();

        if ($sales->isEmpty()) {
            $this->info('ℹ️ No active Post shipments requiring tracking update.');

            return Command::SUCCESS;
        }

        $this->info("🔍 Found {$sales->count()} active Post shipment(s) in tracking queue. Polling BePost (delay: {$delay}s)...");

        $bar = $this->output->createProgressBar($sales->count());
        $bar->start();

        $syncedCount = 0;
        $deliveredCount = 0;
        $updatedCount = 0;
        $isFirst = true;

        foreach ($sales as $sale) {
            if (! $isFirst && $delay > 0) {
                sleep($delay);
            }
            $isFirst = false;

            $barcode = trim($sale->deliverySale->tracking_number);
            $res = $postService->trackBarcode($barcode, 10, $delay);

            if ($res['success'] && $res['found']) {
                $applied = $postService->applyTrackingResultToSale($sale, $sale->deliverySale, $res);
                $syncedCount++;
                if ($applied['was_delivered']) {
                    $deliveredCount++;
                }
                if ($applied['old_status'] !== $applied['new_status']) {
                    $updatedCount++;
                }
            } else {
                $sale->deliverySale->update(['courier_last_synced_at' => now()]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $summary = "Post (BePost) Sync Completed: {$syncedCount}/{$sales->count()} checked, {$deliveredCount} marked DELIVERED, {$updatedCount} status changed.";
        $this->info("🎉 {$summary}");
        Log::info("SyncPostTracking: {$summary}");

        return Command::SUCCESS;
    }
}
