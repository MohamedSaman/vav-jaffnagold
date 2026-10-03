<?php

namespace App\Services;

use App\Models\DeliverySale;
use App\Models\Payment;
use App\Models\PostOrderDetail;
use App\Models\Sale;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostTrackingService
{
    const BASE_SEARCH_URL = 'https://bepost.lk/p/Search/';

    /**
     * Get the public tracking / search URL for Sri Lanka Post (BePost).
     */
    public static function getTrackingUrl(?string $barcode = null): string
    {
        return self::BASE_SEARCH_URL;
    }

    /**
     * Parse date string safely into a Carbon instance.
     */
    public static function parsePostDate(?string $rawDate): Carbon
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
     * Fetch and parse live tracking details for a single barcode using the two-step BePost process.
     *
     * @param  int  $delaySeconds  Delay before POST to respect anti-bot/rate-limiting
     */
    public function trackBarcode(string $barcode, int $timeout = 10, int $delaySeconds = 2): array
    {
        $code = strtoupper(trim(ltrim($barcode, "# \t\n\r\0\x0B")));
        if (empty($code)) {
            return [
                'success' => false,
                'found' => false,
                'barcode' => $code,
                'message' => 'Empty barcode tracking number provided.',
            ];
        }

        $cookieFile = tempnam(sys_get_temp_dir(), 'bepost_cookie_');
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

        try {
            // Step A: Initial GET request to get session cookie & csrf_token
            $ch = curl_init(self::BASE_SEARCH_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIEJAR => $cookieFile,
                CURLOPT_COOKIEFILE => $cookieFile,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_USERAGENT => $userAgent,
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                ],
            ]);

            $initHtml = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($initHtml === false || $httpCode >= 400) {
                $err = curl_error($ch);
                @unlink($cookieFile);

                return [
                    'success' => false,
                    'found' => false,
                    'barcode' => $code,
                    'message' => "BePost initial connection failed (HTTP {$httpCode}): ".($err ?: 'Unable to reach server'),
                ];
            }

            // Extract CSRF token
            $token = null;
            if (preg_match('/name=[\"\']csrf_token[\"\']\s+value=[\"\']([^\"\']+)[\"\']/i', $initHtml, $tokenMatches)) {
                $token = $tokenMatches[1];
            }

            if (empty($token)) {
                @unlink($cookieFile);

                return [
                    'success' => false,
                    'found' => false,
                    'barcode' => $code,
                    'message' => 'Could not extract BePost CSRF token.',
                ];
            }

            // Delay between GET and POST if needed to satisfy rate limiting / anti-bot
            if ($delaySeconds > 0) {
                sleep($delaySeconds);
            }

            // Step B: POST request with csrf_token and barcode
            curl_setopt_array($ch, [
                CURLOPT_URL => self::BASE_SEARCH_URL,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'csrf_token' => $token,
                    'website' => '', // Anti-spam honeypot
                    'barcode' => $code,
                ]),
                CURLOPT_HTTPHEADER => [
                    'Origin: https://bepost.lk',
                    'Referer: '.self::BASE_SEARCH_URL,
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                ],
            ]);

            $postResponse = curl_exec($ch);
            $postCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            @unlink($cookieFile);

            if ($postResponse === false || $postCode >= 400) {
                return [
                    'success' => false,
                    'found' => false,
                    'barcode' => $code,
                    'message' => "BePost search request failed with HTTP {$postCode}",
                ];
            }

            return $this->parseHtml($postResponse, $code);
        } catch (\Throwable $e) {
            @unlink($cookieFile);
            Log::warning("BePost tracking error for {$code}: ".$e->getMessage());

            return [
                'success' => false,
                'found' => false,
                'barcode' => $code,
                'message' => 'Tracking exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Parse BePost HTML content into structured tracking information.
     */
    public function parseHtml(string $html, string $barcode): array
    {
        // Check for rate limit / wait message
        if (str_contains($html, 'Please wait a moment and try again')) {
            return [
                'success' => false,
                'found' => false,
                'barcode' => $barcode,
                'message' => 'BePost server asked to wait. Please retry in a few moments.',
            ];
        }

        // Check for actual no results message box (do NOT just check for the CSS class 'no-results' which is in the <style> header)
        if (
            preg_match('/<div[^>]*class=[\'"][^\'"]*no-results[^\'"]*[\'"][^>]*>(.*?)<\/div>/is', $html, $noResultMatch) ||
            str_contains($html, 'No record found') ||
            str_contains($html, 'Record Not Found')
        ) {
            $msg = ! empty($noResultMatch[1]) ? trim(strip_tags($noResultMatch[1])) : 'No record found for barcode in BePost system yet.';

            return [
                'success' => true,
                'found' => false,
                'barcode' => $barcode,
                'message' => $msg,
                'tracking_url' => self::getTrackingUrl($barcode),
            ];
        }

        $dom = new DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $data = [
            'success' => true,
            'found' => false,
            'barcode' => $barcode,
            'status' => null,
            'delivery_po' => null,
            'acceptance_po' => null,
            'received_date' => null,
            'date_accepted' => null,
            'settled_date' => null,
            'po_settled' => null,
            'cod_amount' => null,
            'raw_fields' => [],
            'events' => [],
            'tracking_url' => self::getTrackingUrl($barcode),
        ];

        // 1. Check vertical table rows (supports both <th>Key</th><td>Val</td> and <td>Key</td><td>Val</td>)
        $verticalRows = $xpath->query('//table[contains(@class, "result-table")]//tr');
        if ($verticalRows && $verticalRows->length > 0) {
            foreach ($verticalRows as $row) {
                $ths = $xpath->query('.//th', $row);
                $tds = $xpath->query('.//td', $row);

                // Try 1 th + 1 td
                if ($ths->length === 1 && $tds->length === 1 && ! empty(trim($ths->item(0)->textContent))) {
                    $key = trim(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ths->item(0)->textContent)));
                    $val = trim($tds->item(0)->textContent);
                    if (! empty($key)) {
                        $data['raw_fields'][$key] = $val;
                    }
                }

                // Try 2 tds (BePost standard format where each row is <td>Key:</td><td>Val</td>)
                if ($ths->length === 0 && $tds->length === 2) {
                    $k = trim(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $tds->item(0)->textContent)));
                    $v = trim($tds->item(1)->textContent);
                    if (! empty($k)) {
                        $data['raw_fields'][$k] = $v;
                    }
                }
            }
        }

        // 2. Check horizontal table (thead th -> tbody td or tr[1] th -> tr[2] td)
        if (empty($data['raw_fields'])) {
            $headerCols = $xpath->query('//table[contains(@class, "result-table")]//thead//th | //table[contains(@class, "result-table")]//tr[1]//th');
            $headers = [];
            foreach ($headerCols as $col) {
                $headers[] = trim(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $col->textContent)));
            }

            $bodyRows = $xpath->query('//table[contains(@class, "result-table")]//tbody//tr | //table[contains(@class, "result-table")]//tr[position()>1]');
            if ($bodyRows && $bodyRows->length > 0) {
                foreach ($bodyRows as $row) {
                    $dataCols = $xpath->query('.//td', $row);
                    if ($dataCols->length > 0 && count($headers) === $dataCols->length) {
                        $rowVals = [];
                        for ($i = 0; $i < count($headers); $i++) {
                            $rowVals[$headers[$i]] = trim($dataCols->item($i)->textContent);
                        }
                        if (empty($data['raw_fields'])) {
                            $data['raw_fields'] = $rowVals;
                        }
                        $data['events'][] = $rowVals;
                    }
                }
            }
        }

        // Map extracted raw fields to structured properties
        foreach ($data['raw_fields'] as $key => $val) {
            if (str_contains($key, 'deliverypo') || str_contains($key, 'deliveryoffice') || str_contains($key, 'destination')) {
                $data['delivery_po'] = $val;
            } elseif (str_contains($key, 'acceptingpo') || str_contains($key, 'acceptancepo') || str_contains($key, 'acceptanceoffice') || str_contains($key, 'acceptedpo') || str_contains($key, 'origin')) {
                $data['acceptance_po'] = $val;
            } elseif (str_contains($key, 'receiveddate') || str_contains($key, 'deliverydate') || str_contains($key, 'delivereddate')) {
                $data['received_date'] = $val;
            } elseif (str_contains($key, 'dateaccepted')) {
                $data['date_accepted'] = $val;
            } elseif (str_contains($key, 'settleddate')) {
                $data['settled_date'] = $val;
            } elseif (str_contains($key, 'posettled')) {
                $data['po_settled'] = $val;
            } elseif ($key === 'date' && empty($data['received_date'])) {
                $data['received_date'] = $val;
            } elseif (str_contains($key, 'status')) {
                $data['status'] = $val;
            } elseif ($key === 'amount' || $key === 'codamount' || $key === 'totalamount') {
                $data['cod_amount'] = $val;
            }
        }

        // Check CSS class badges if status not set via text column
        if (empty($data['status'])) {
            foreach (['settled', 'delivered', 'returned', 'pending'] as $st) {
                $nodes = $xpath->query("//*[contains(@class, 'status-{$st}')]");
                if ($nodes->length > 0) {
                    $data['status'] = ucfirst($st);
                    break;
                }
            }
        }

        // If we found any table data or status, mark found = true
        if (! empty($data['status']) || ! empty($data['delivery_po']) || ! empty($data['raw_fields'])) {
            $data['found'] = true;
        }

        if (! $data['found']) {
            return [
                'success' => true,
                'found' => false,
                'barcode' => $barcode,
                'message' => 'Tracking information table not found on page.',
                'tracking_url' => self::getTrackingUrl($barcode),
            ];
        }

        // Normalize status
        $rawStatus = (string) ($data['status'] ?? '');
        $lowerStatus = strtolower($rawStatus);
        $normalizedStatus = 'in_transit';
        $isDelivered = false;
        $isFinal = false;

        if (
            str_contains($lowerStatus, 'settled') ||
            str_contains($lowerStatus, 'delivered') ||
            str_contains($lowerStatus, 'cash received') ||
            str_contains($lowerStatus, 'completed')
        ) {
            $normalizedStatus = 'delivered';
            $isDelivered = true;
            $isFinal = true;
        } elseif (
            str_contains($lowerStatus, 'return') ||
            str_contains($lowerStatus, 'refused') ||
            str_contains($lowerStatus, 'cancelled') ||
            str_contains($lowerStatus, 'canceled')
        ) {
            $normalizedStatus = 'returned';
            $isFinal = true;
        } elseif (
            str_contains($lowerStatus, 'in transit') ||
            str_contains($lowerStatus, 'pending') ||
            str_contains($lowerStatus, 'dispatched') ||
            str_contains($lowerStatus, 'accepted')
        ) {
            $normalizedStatus = 'in_transit';
            $isFinal = false;
        }

        // Build status detail string
        $detailParts = [];
        if (! empty($rawStatus)) {
            $detailParts[] = ucfirst($rawStatus);
        }
        if (! empty($data['delivery_po'])) {
            $detailParts[] = 'Delivery PO: '.$data['delivery_po'];
        }
        if (! empty($data['received_date'])) {
            $detailParts[] = '('.$data['received_date'].')';
        }

        $statusDetail = ! empty($detailParts) ? implode(' - ', $detailParts) : ($rawStatus ?: 'In Transit');

        // Build chronological checkpoints from BePost milestone dates
        $events = [];
        if (! empty($data['date_accepted'])) {
            $events[] = [
                'date_time' => $data['date_accepted'],
                'status' => 'Accepted',
                'location' => $data['acceptance_po'] ?: 'Accepting Post Office',
            ];
        }
        if (! empty($data['received_date'])) {
            $events[] = [
                'date_time' => $data['received_date'],
                'status' => 'Delivered / Received',
                'location' => $data['delivery_po'] ?: 'Delivery Post Office',
            ];
        }
        if (! empty($data['settled_date'])) {
            $events[] = [
                'date_time' => $data['settled_date'],
                'status' => 'Settled',
                'location' => $data['po_settled'] ?? ($data['acceptance_po'] ?: 'Settled Post Office'),
            ];
        }

        if (empty($events) && ! empty($data['events'])) {
            $events = $data['events'];
        }

        if (empty($events)) {
            $events[] = [
                'date_time' => $data['received_date'] ?: ($data['settled_date'] ?: now()->format('Y-m-d H:i:s')),
                'status' => $rawStatus ?: 'In Transit',
                'location' => $data['delivery_po'] ?: ($data['acceptance_po'] ?: 'Post Office'),
            ];
        }

        $data['normalized_status'] = $normalizedStatus;
        $data['is_delivered'] = $isDelivered;
        $data['is_final'] = $isFinal;
        $data['status_detail'] = $statusDetail;
        $data['events'] = $events;

        return $data;
    }

    /**
     * Sync single order from BePost tracking and update database records.
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

        $barcode = trim($deliverySale->tracking_number);
        $res = $this->trackBarcode($barcode);

        if (! $res['success'] || ! $res['found']) {
            $deliverySale->update([
                'courier_last_synced_at' => now(),
                'courier_status_detail' => $deliverySale->courier_status_detail ?: 'Awaiting Post update',
            ]);

            return [
                'updated' => false,
                'barcode' => $barcode,
                'message' => $res['message'] ?? 'Barcode not found on BePost.',
            ];
        }

        return $this->applyTrackingResultToSale($sale, $deliverySale, $res);
    }

    /**
     * Apply tracking result array to Sale, DeliverySale and PostOrderDetail models.
     */
    public function applyTrackingResultToSale(Sale $sale, DeliverySale $deliverySale, array $tracking): array
    {
        return DB::transaction(function () use ($sale, $deliverySale, $tracking) {
            $oldStatus = $sale->delivery_status;
            $newStatus = $tracking['normalized_status'];
            $wasDelivered = false;

            $deliverySaleUpdates = [
                'tracking_number' => $tracking['barcode'] ?? $deliverySale->tracking_number,
                'courier_status_detail' => $tracking['status_detail'],
                'courier_last_synced_at' => now(),
                'courier_tracking_history' => $tracking['events'] ?? [],
            ];

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

                // Post courier delivered parcel to customer. Do not auto-record payment or auto-complete order;
                // Payment and order completion are handled manually once courier remittance is received.
            } elseif ($newStatus === 'returned' && $oldStatus !== 'returned') {
                $sale->delivery_status = 'returned';
                $deliverySaleUpdates['status'] = 'Returned';

                // Restore stock if returning
                if (! in_array($oldStatus, ['returned', 'cancelled'])) {
                    $sale->restoreStock();
                }
            } elseif ($newStatus === 'in_transit' && in_array($oldStatus, ['pending', 'packed'])) {
                $sale->delivery_status = 'in_transit';
                $deliverySaleUpdates['status'] = 'In Transit';
            }

            $sale->save();
            $deliverySale->update($deliverySaleUpdates);

            // Create or update PostOrderDetail record
            $isFinal = (bool) ($tracking['is_final'] ?? false);

            PostOrderDetail::updateOrCreate(
                ['barcode' => $tracking['barcode']],
                [
                    'sale_id' => $sale->id,
                    'delivery_sale_id' => $deliverySale->id,
                    'status' => $tracking['status'] ?? $tracking['status_detail'],
                    'normalized_status' => $newStatus,
                    'is_final' => $isFinal,
                    'delivery_po' => $tracking['delivery_po'] ?? null,
                    'acceptance_po' => $tracking['acceptance_po'] ?? null,
                    'received_date' => $tracking['received_date'] ?? null,
                    'cod_amount' => $tracking['cod_amount'] ?? null,
                    'tracking_history' => $tracking['events'] ?? [],
                    'raw_data' => $tracking['raw_fields'] ?? [],
                    'last_synced_at' => now(),
                ]
            );

            return [
                'updated' => true,
                'barcode' => $tracking['barcode'],
                'old_status' => $oldStatus,
                'new_status' => $sale->delivery_status,
                'status_detail' => $tracking['status_detail'],
                'was_delivered' => $wasDelivered,
                'is_final' => $isFinal,
            ];
        });
    }

    /**
     * Sync multiple Post delivery sales sequentially with rate limiting.
     * Skips orders that are already delivered/cancelled or marked is_final,
     * unless $force is set to true.
     *
     * @param  \Illuminate\Support\Collection|array  $sales
     * @param  int  $delaySeconds  Delay between requests to prevent IP block
     */
    public function syncMultipleOrders($sales, int $delaySeconds = 2, bool $force = false): array
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
            if ($sale->postOrderDetail && $sale->postOrderDetail->is_final) {
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
                'message' => 'No active Post orders with tracking numbers to sync.',
            ];
        }

        $syncedCount = 0;
        $deliveredCount = 0;
        $updatedCount = 0;
        $isFirst = true;

        foreach ($eligibleSales as $sale) {
            // Apply delay between requests (skipping delay before the very first request)
            if (! $isFirst && $delaySeconds > 0) {
                sleep($delaySeconds);
            }
            $isFirst = false;

            $barcode = trim($sale->deliverySale->tracking_number);
            $res = $this->trackBarcode($barcode, 10, $delaySeconds);

            if ($res['success'] && $res['found']) {
                $applied = $this->applyTrackingResultToSale($sale, $sale->deliverySale, $res);
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
        }

        return [
            'total' => $eligibleSales->count(),
            'synced' => $syncedCount,
            'delivered' => $deliveredCount,
            'updated' => $updatedCount,
            'message' => "Post (BePost) tracking updated: {$syncedCount} parcels checked, {$updatedCount} statuses refreshed.",
        ];
    }
}
