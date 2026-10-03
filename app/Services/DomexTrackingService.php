<?php

namespace App\Services;

use App\Models\DeliverySale;
use App\Models\DomexOrderDetail;
use App\Models\Payment;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomexTrackingService
{
    const BASE_TRACKING_URL = 'https://domex.lk/Order-Details.php?wbno=';

    /**
     * Parse Domex date string e.g. "2026-09-22 @ (8:43 am)" safely into a Carbon instance.
     */
    public static function parseDomexDate(?string $rawDate): Carbon
    {
        if (empty($rawDate)) {
            return now();
        }

        try {
            $clean = trim(preg_replace('/\s+/', ' ', str_replace(['@', '(', ')'], ' ', $rawDate)));

            return Carbon::parse($clean);
        } catch (\Throwable $e) {
            return now();
        }
    }

    /**
     * Get the public tracking URL for a given waybill number.
     */
    public static function getTrackingUrl(?string $waybillNumber): string
    {
        return self::BASE_TRACKING_URL.trim((string) $waybillNumber);
    }

    /**
     * Fetch and parse live tracking details for a single waybill number.
     */
    public function trackWaybill(string $waybillNumber, int $timeout = 8): array
    {
        $wbno = trim($waybillNumber);
        if (empty($wbno)) {
            return [
                'success' => false,
                'found' => false,
                'waybill' => $wbno,
                'message' => 'Empty tracking number provided.',
            ];
        }

        try {
            $url = self::getTrackingUrl($wbno);
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout($timeout)->get($url);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'found' => false,
                    'waybill' => $wbno,
                    'message' => 'Domex server returned HTTP '.$response->status(),
                ];
            }

            return $this->parseHtml($response->body(), $wbno);
        } catch (\Throwable $e) {
            Log::warning("Domex tracking request failed for {$wbno}: ".$e->getMessage());

            return [
                'success' => false,
                'found' => false,
                'waybill' => $wbno,
                'message' => 'Network error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Parse Domex HTML content into structured tracking information.
     */
    public function parseHtml(string $html, string $wbno): array
    {
        if (str_contains($html, 'Record Not Found') || str_contains($html, 'NOT found')) {
            return [
                'success' => true,
                'found' => false,
                'waybill' => $wbno,
                'message' => 'Waybill not found in Domex system yet.',
                'tracking_url' => self::getTrackingUrl($wbno),
            ];
        }

        // Helper to extract by element ID
        $extractTag = function ($id) use ($html) {
            if (preg_match('/id=[\"\']'.$id.'[\"\'][^>]*>(.*?)<\/(td|span|div)>/is', $html, $m)) {
                return trim(strip_tags($m[1]));
            }

            return null;
        };

        $deliveredTo = $extractTag('deliveredTo') ?: '';
        $deliveredBy = $extractTag('deliveredBy') ?: '';
        $isPaid = $extractTag('ispaid') ?: '';
        $pickupDate = $extractTag('pickupDate') ?: '';
        $destinationCity = $extractTag('DistinationCity') ?: '';
        $receiverName = $extractTag('ReceiverName') ?: '';
        $receiverPhone = $extractTag('ReceiverPhone') ?: '';
        $pickupCity = $extractTag('pickupCity') ?: '';
        $paymentMethod = $extractTag('paymentMethod') ?: '';
        $itemType = $extractTag('itemType') ?: '';
        $pieces = $extractTag('no_of_peace') ?: '';
        $weight = $extractTag('weight') ?: '';
        $senderName = $extractTag('senderName') ?: '';
        $senderAddress = $extractTag('senderAddress') ?: '';
        $senderContact = $extractTag('senderContactNo') ?: '';

        // Extract timeline events from <tbody id="tracking-table-body">
        $events = [];
        if (preg_match('/<tbody[^>]*id=[\"\']tracking-table-body[\"\'][^>]*>(.*?)<\/tbody>/is', $html, $tbodyMatch)) {
            if (preg_match_all('/<tr>\s*<td[^>]*>(.*?)<\/td>\s*<td[^>]*>(.*?)<\/td>\s*<\/tr>/is', $tbodyMatch[1], $rows)) {
                $count = count($rows[1]);
                for ($i = 0; $i < $count; $i++) {
                    $dateTime = trim(strip_tags($rows[1][$i]));
                    $statusText = trim(strip_tags($rows[2][$i]));
                    if (! empty($statusText)) {
                        $events[] = [
                            'date_time' => $dateTime,
                            'status' => $statusText,
                        ];
                    }
                }
            }
        }

        $latestEvent = ! empty($events) ? end($events) : null;
        $latestStatusText = $latestEvent['status'] ?? '';
        $latestTime = $latestEvent['date_time'] ?? '';

        // Determine normalized system delivery status
        $normalizedStatus = 'in_transit'; // Default for tracked parcels
        $isDelivered = false;
        $isReturned = false;

        // Check all historical events in timeline
        foreach ($events as $ev) {
            $evText = strtolower($ev['status'] ?? '');
            if (
                str_contains($evText, 'delivered') ||
                str_contains($evText, 'cash received') ||
                str_contains($evText, 'pod submitted') ||
                str_contains($evText, 'handover')
            ) {
                $isDelivered = true;
            } elseif (
                str_contains($evText, 'return to client') ||
                str_contains($evText, 'returned to sender') ||
                str_contains($evText, 'returned by') ||
                str_contains($evText, 'parcel cancelled') ||
                str_contains($evText, 'cancelled by')
            ) {
                $isReturned = true;
            }
        }

        $lowerStatus = strtolower($latestStatusText);
        $lowerDeliveredTo = strtolower($deliveredTo);

        if (
            ! empty($deliveredTo) ||
            $isDelivered ||
            str_contains($lowerStatus, 'delivered') ||
            str_contains($lowerStatus, 'cash received') ||
            str_contains($lowerStatus, 'pod submitted') ||
            str_contains($lowerStatus, 'handover')
        ) {
            $normalizedStatus = 'delivered';
            $isDelivered = true;
        } elseif (
            $isReturned ||
            str_contains($lowerStatus, 'return to') ||
            str_contains($lowerStatus, 'returned') ||
            str_contains($lowerStatus, 'cancel') ||
            str_contains($lowerStatus, 'refused')
        ) {
            $normalizedStatus = 'returned';
        } elseif (
            str_contains($lowerStatus, 'out for delivery') ||
            str_contains($lowerStatus, 'reschedule') ||
            str_contains($lowerStatus, 'in transit') ||
            str_contains($lowerStatus, 'received by') ||
            str_contains($lowerStatus, 'sort facility') ||
            str_contains($lowerStatus, 'collected by')
        ) {
            $normalizedStatus = 'in_transit';
        }

        // Build a user-friendly detail string
        $statusDetail = $latestStatusText;
        if (! empty($latestTime)) {
            $statusDetail .= ' ('.$latestTime.')';
        }

        return [
            'success' => true,
            'found' => true,
            'waybill' => $wbno,
            'normalized_status' => $normalizedStatus,
            'is_delivered' => $isDelivered,
            'status_detail' => $statusDetail,
            'latest_status' => $latestStatusText,
            'latest_time' => $latestTime,
            'delivered_to' => $deliveredTo,
            'delivered_by' => $deliveredBy,
            'is_paid' => $isPaid,
            'payment_method' => $paymentMethod,
            'item_type' => $itemType,
            'pieces' => $pieces,
            'weight' => $weight,
            'sender_name' => $senderName,
            'sender_address' => $senderAddress,
            'sender_contact' => $senderContact,
            'pickup_city' => $pickupCity,
            'destination_city' => $destinationCity,
            'receiver_name' => $receiverName,
            'receiver_phone' => $receiverPhone,
            'pickup_date' => $pickupDate,
            'events' => $events,
            'tracking_url' => self::getTrackingUrl($wbno),
        ];
    }

    /**
     * Sync single order from Domex tracking and update database records.
     */
    public function syncOrder(Sale $sale): array
    {
        $deliverySale = $sale->deliverySale;
        if (! $deliverySale || empty($deliverySale->tracking_number)) {
            return [
                'updated' => false,
                'message' => 'No tracking number found for order.',
            ];
        }

        $wbno = trim($deliverySale->tracking_number);
        $res = $this->trackWaybill($wbno);

        if (! $res['success'] || ! $res['found']) {
            // Still record that we attempted to sync
            $deliverySale->update([
                'courier_last_synced_at' => now(),
                'courier_status_detail' => $deliverySale->courier_status_detail ?: 'Awaiting Domex update',
            ]);

            return [
                'updated' => false,
                'waybill' => $wbno,
                'message' => $res['message'] ?? 'Waybill not found on Domex.',
            ];
        }

        return $this->applyTrackingResultToSale($sale, $deliverySale, $res);
    }

    /**
     * Apply tracking result array to Sale, DeliverySale and DomexOrderDetail models.
     */
    protected function applyTrackingResultToSale(Sale $sale, DeliverySale $deliverySale, array $tracking): array
    {
        return DB::transaction(function () use ($sale, $deliverySale, $tracking) {
            $oldStatus = $sale->delivery_status;
            $newStatus = $tracking['normalized_status'];
            $wasDelivered = false;

            $deliverySaleUpdates = [
                'courier_status_detail' => $tracking['status_detail'],
                'courier_last_synced_at' => now(),
                'courier_tracking_history' => $tracking['events'] ?? [],
            ];

            // Update statuses accordingly
            if ($tracking['is_delivered']) {
                if ($oldStatus !== 'delivered') {
                    $sale->delivery_status = 'delivered';
                    $sale->delivered_at = now();
                    $wasDelivered = true;

                    // Deduct stock if not already deducted
                    if ($sale->status !== 'confirm') {
                        $sale->confirmAndDeductStock();
                    }

                    // Send customer delivery SMS
                    try {
                        \App\Services\SmsService::notifyOrderDelivered($sale);
                    } catch (\Throwable $e) {
                    }
                }
                $deliverySaleUpdates['status'] = 'Delivered';

                // Courier delivered parcel to customer. Do not auto-record payment or auto-complete order;
                // Payment and order completion are handled manually once courier remittance is received.
            } elseif ($newStatus === 'returned' && $oldStatus !== 'returned') {
                $sale->delivery_status = 'returned';
                $deliverySaleUpdates['status'] = 'Returned';

                // Restore inventory stock if returning
                if (! in_array($oldStatus, ['returned', 'cancelled'])) {
                    $sale->restoreStock();
                }
            } elseif ($newStatus === 'in_transit' && in_array($oldStatus, ['pending', 'packed'])) {
                $sale->delivery_status = 'in_transit';
                $deliverySaleUpdates['status'] = 'In Transit';
            }

            $sale->save();
            $deliverySale->update($deliverySaleUpdates);

            // Update or create detailed Domex tracking table record
            // Mark as final if delivered, returned or cancelled so further polling stops
            $isFinal = (bool) ($tracking['is_delivered'] || in_array($newStatus, ['delivered', 'cancelled', 'canceled', 'returned']));

            DomexOrderDetail::updateOrCreate(
                ['waybill_number' => $tracking['waybill']],
                [
                    'sale_id' => $sale->id,
                    'delivery_sale_id' => $deliverySale->id,
                    'current_status' => $tracking['latest_status'] ?: $tracking['status_detail'],
                    'normalized_status' => $newStatus,
                    'is_final' => $isFinal,
                    'pickup_date' => $tracking['pickup_date'] ?? null,
                    'payment_method' => $tracking['payment_method'] ?? null,
                    'item_type' => $tracking['item_type'] ?? null,
                    'is_paid' => $tracking['is_paid'] ?? null,
                    'pieces' => $tracking['pieces'] ?? null,
                    'weight' => $tracking['weight'] ?? null,
                    'sender_name' => $tracking['sender_name'] ?? null,
                    'sender_address' => $tracking['sender_address'] ?? null,
                    'sender_contact' => $tracking['sender_contact'] ?? null,
                    'pickup_city' => $tracking['pickup_city'] ?? null,
                    'delivered_by' => $tracking['delivered_by'] ?? null,
                    'delivered_to' => $tracking['delivered_to'] ?? null,
                    'receiver_name' => $tracking['receiver_name'] ?? null,
                    'receiver_phone' => $tracking['receiver_phone'] ?? null,
                    'destination_city' => $tracking['destination_city'] ?? null,
                    'tracking_history' => $tracking['events'] ?? [],
                    'last_checkpoint_time' => $tracking['latest_time'] ?? null,
                    'last_synced_at' => now(),
                ]
            );

            return [
                'updated' => true,
                'waybill' => $tracking['waybill'],
                'old_status' => $oldStatus,
                'new_status' => $sale->delivery_status,
                'status_detail' => $tracking['status_detail'],
                'was_delivered' => $wasDelivered,
                'is_final' => $isFinal,
            ];
        });
    }

    /**
     * Sync multiple sales concurrently in batches using Http::pool.
     * Skips orders that are already delivered/cancelled or marked is_final,
     * unless $force is set to true.
     *
     * @param  \Illuminate\Support\Collection|array  $sales
     */
    public function syncMultipleOrders($sales, int $chunkSize = 5, bool $force = false): array
    {
        $eligibleSales = collect($sales)->filter(function ($sale) use ($force) {
            if (! $sale || ! $sale->deliverySale || empty($sale->deliverySale->tracking_number)) {
                return false;
            }
            if ($force) {
                return true;
            }
            // Skip finalized orders (delivered / cancelled / returned)
            if (in_array($sale->delivery_status, ['delivered', 'cancelled', 'returned'])) {
                return false;
            }
            if ($sale->domexOrderDetail && $sale->domexOrderDetail->is_final) {
                return false;
            }

            return true;
        });

        if ($eligibleSales->isEmpty()) {
            return [
                'total' => 0,
                'synced' => 0,
                'delivered' => 0,
                'updated' => 0,
                'message' => 'No orders with Domex tracking numbers to sync.',
            ];
        }

        $syncedCount = 0;
        $deliveredCount = 0;
        $updatedCount = 0;

        // Group into chunks to prevent connection limits or timeouts
        $chunks = $eligibleSales->chunk($chunkSize);

        foreach ($chunks as $chunk) {
            $wbnos = [];
            foreach ($chunk as $sale) {
                $wb = trim($sale->deliverySale->tracking_number);
                if (! empty($wb)) {
                    $wbnos[$wb] = $sale;
                }
            }

            if (empty($wbnos)) {
                continue;
            }

            // Execute concurrent requests
            try {
                $responses = Http::pool(function (Pool $pool) use ($wbnos) {
                    $reqs = [];
                    foreach (array_keys($wbnos) as $wb) {
                        $reqs[$wb] = $pool->as($wb)
                            ->withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                            ])
                            ->timeout(7)
                            ->get(self::getTrackingUrl($wb));
                    }

                    return $reqs;
                });

                foreach ($wbnos as $wb => $sale) {
                    $resp = $responses[$wb] ?? null;

                    if (! ($resp instanceof \Illuminate\Http\Client\Response) || ! $resp->successful()) {
                        // Mark checked timestamp even on temporary network glitch
                        $sale->deliverySale->update(['courier_last_synced_at' => now()]);

                        continue;
                    }

                    $tracking = $this->parseHtml($resp->body(), $wb);
                    if ($tracking['success'] && $tracking['found']) {
                        $res = $this->applyTrackingResultToSale($sale, $sale->deliverySale, $tracking);
                        $syncedCount++;
                        if ($res['was_delivered']) {
                            $deliveredCount++;
                        }
                        if ($res['old_status'] !== $res['new_status']) {
                            $updatedCount++;
                        }
                    } else {
                        $sale->deliverySale->update(['courier_last_synced_at' => now()]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Batch Domex sync error: '.$e->getMessage());
            }
        }

        return [
            'total' => $eligibleSales->count(),
            'synced' => $syncedCount,
            'delivered' => $deliveredCount,
            'updated' => $updatedCount,
            'message' => "Domex tracking updated: {$syncedCount} parcels checked, {$updatedCount} statuses refreshed.",
        ];
    }
}
