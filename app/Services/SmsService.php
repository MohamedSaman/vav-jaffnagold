<?php

namespace App\Services;

use App\Http\Controllers\PublicInvoiceController;
use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsService
{
    /**
     * Normalize phone numbers to the +94XXXXXXXXX format expected by SMSlenz.
     * Handles:
     * - 07XXXXXXXX -> +947XXXXXXXX
     * - 947XXXXXXXX -> +947XXXXXXXX
     * - +947XXXXXXXX -> +947XXXXXXXX
     */
    public static function normalizePhoneNumber(?string $phoneNumber): string
    {
        $raw = trim($phoneNumber ?? '');
        $digits = preg_replace('/\D/', '', $raw);

        if (empty($digits)) {
            return '';
        }

        // Already in +94 format with 11 digits (94 + 9 digits)
        if (str_starts_with($raw, '+94') && strlen($digits) === 11) {
            return '+'.$digits;
        }

        // 94XXXXXXXXX (11 digits starting with 94)
        if (str_starts_with($digits, '94') && strlen($digits) === 11) {
            return '+'.$digits;
        }

        // 0XXXXXXXXX local format (10 digits starting with 0)
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '+94'.substr($digits, 1);
        }

        // 9-digit Lankan mobile without leading 0 (e.g. 771234567)
        if (strlen($digits) === 9 && in_array($digits[0], ['7', '1', '2', '3', '4', '5', '6', '8', '9'])) {
            return '+94'.$digits;
        }

        // Generic fallback: preserve + if present, else prepend +
        return str_starts_with($raw, '+') ? $raw : '+'.$digits;
    }

    /**
     * Get configured rate per single SMS part (default 0.85).
     */
    public static function getPerSmsRate(): float
    {
        $rate = \App\Models\Setting::where('key', 'sms_rate')->value('value');

        return $rate !== null ? (float) $rate : 0.85;
    }

    /**
     * Get low balance threshold (default 50.00).
     */
    public static function getLowBalanceThreshold(): float
    {
        $val = \App\Models\Setting::where('key', 'sms_low_balance_threshold')->value('value');

        return $val !== null ? (float) $val : 50.00;
    }

    /**
     * Get top-up contact number for recharge requests.
     */
    public static function getTopupContact(): string
    {
        $contact = \App\Models\Setting::where('key', 'sms_topup_contact')->value('value');

        return ! empty($contact) ? (string) $contact : '0759037101';
    }

    /**
     * Total deposited / credited amount for SMS.
     */
    public static function getTotalTopupAmount(): float
    {
        return (float) \App\Models\SmsTopup::sum('amount');
    }

    /**
     * Total spent amount across successful and mocked SMS dispatches.
     */
    public static function getTotalSmsCost(): float
    {
        return (float) \App\Models\SmsLog::whereIn('status', ['sent', 'mocked'])->sum('total_cost');
    }

    /**
     * Total number of SMS message parts sent.
     */
    public static function getTotalSmsSentCount(): int
    {
        return (int) \App\Models\SmsLog::whereIn('status', ['sent', 'mocked'])->sum('sms_count');
    }

    /**
     * Calculate remaining SMS account balance (Topups - Spent).
     */
    public static function getBalance(): float
    {
        return (float) (self::getTotalTopupAmount() - self::getTotalSmsCost());
    }

    /**
     * Check if balance is below the low threshold.
     */
    public static function isBalanceLow(): bool
    {
        return self::getBalance() <= self::getLowBalanceThreshold();
    }

    /**
     * Check if balance is completely exhausted (<= 0).
     */
    public static function isBalanceFinished(): bool
    {
        return self::getBalance() <= 0;
    }

    /**
     * Calculate SMS message parts based on length and encoding.
     */
    public static function calculateSmsParts(string $message): int
    {
        $len = mb_strlen($message, 'UTF-8');
        if ($len === 0) {
            return 1;
        }

        // Check if message contains non-GSM characters (e.g. Tamil, Sinhala, emojis)
        $isUnicode = preg_match('/[^\x20-\x7E\r\n]/u', $message);
        if ($isUnicode) {
            return $len <= 70 ? 1 : (int) ceil($len / 67);
        }

        return $len <= 160 ? 1 : (int) ceil($len / 153);
    }

    /**
     * Send an SMS message via configured provider (default SMSlenz).
     *
     * @param  string  $phone  The recipient phone number
     * @param  string  $message  The SMS text message
     * @param  bool  $forceDoubleCharge  Whether double rate is applied due to low/exhausted balance override
     * @return array ['success' => bool, 'message' => string, 'data' => mixed, 'sms_log' => mixed]
     */
    public static function sendSms(string $phone, string $message, bool $forceDoubleCharge = false, bool $billable = true): array
    {
        $enabled = config('sms.enabled', true);
        $provider = config('sms.provider', 'smslenz');
        $sendUrl = config('sms.send_url', 'https://smslenz.lk/api/send-sms');
        $userId = config('sms.user_id', '');
        $apiKey = config('sms.api_key', '');
        $senderId = config('sms.sender_id', 'Sportynix');
        $timeout = config('sms.timeout', 5);

        $baseRate = self::getPerSmsRate();
        $isDoubleCharge = $billable ? ($forceDoubleCharge || self::isBalanceFinished()) : false;
        $appliedRate = $billable ? ($isDoubleCharge ? ($baseRate * 2) : $baseRate) : 0.00;
        $smsParts = self::calculateSmsParts($message);
        $totalCost = $billable ? round($smsParts * $appliedRate, 4) : 0.00;

        $normalizedContact = self::normalizePhoneNumber($phone);

        if (empty($normalizedContact)) {
            Log::warning('[SmsService] Skipping SMS send: Empty or invalid phone number.', ['phone' => $phone]);

            return [
                'success' => false,
                'message' => 'Invalid recipient phone number.',
            ];
        }

        // If SMS is disabled in config, mock the success and log for debugging
        if (! $enabled || empty($sendUrl) || empty($apiKey)) {
            Log::info('[SmsService] SMS provider disabled or unconfigured (Mocked).', [
                'recipient' => $normalizedContact,
                'message' => $message,
                'sender_id' => $senderId,
                'cost' => $totalCost,
            ]);

            $log = \App\Models\SmsLog::create([
                'recipient' => $normalizedContact,
                'message' => $message,
                'sms_count' => $smsParts,
                'cost_per_sms' => $appliedRate,
                'total_cost' => $totalCost,
                'is_double_charge' => $isDoubleCharge,
                'status' => 'mocked',
                'sender_id' => $senderId,
                'response' => 'SMS mocked (Provider disabled or not configured).',
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => true,
                'mocked' => true,
                'message' => 'SMS mocked (Provider disabled or not configured).',
                'sms_log' => $log,
            ];
        }

        $payload = [
            'user_id' => $userId,
            'api_key' => $apiKey,
            'sender_id' => $senderId,
            'contact' => $normalizedContact,
            'message' => $message,
        ];

        try {
            $response = Http::asForm()
                ->timeout($timeout)
                ->post($sendUrl, $payload);

            $data = $response->json();

            if ($response->successful() && is_array($data) && ($data['success'] ?? false)) {
                Log::info('[SmsService] SMS dispatched successfully.', [
                    'recipient' => $normalizedContact,
                    'sender_id' => $senderId,
                    'cost' => $totalCost,
                    'provider_response' => $data,
                ]);

                $log = \App\Models\SmsLog::create([
                    'recipient' => $normalizedContact,
                    'message' => $message,
                    'sms_count' => $smsParts,
                    'cost_per_sms' => $appliedRate,
                    'total_cost' => $totalCost,
                    'is_double_charge' => $isDoubleCharge,
                    'status' => 'sent',
                    'sender_id' => $senderId,
                    'response' => json_encode($data),
                    'user_id' => auth()->id(),
                ]);

                return [
                    'success' => true,
                    'message' => $data['message'] ?? 'SMS sent successfully.',
                    'data' => $data,
                    'sms_log' => $log,
                ];
            }

            $errorMessage = is_array($data) ? ($data['message'] ?? 'SMS provider rejected the request.') : $response->body();
            Log::error('[SmsService] SMS provider returned an error.', [
                'status_code' => $response->status(),
                'response' => $errorMessage,
                'recipient' => $normalizedContact,
            ]);

            $log = \App\Models\SmsLog::create([
                'recipient' => $normalizedContact,
                'message' => $message,
                'sms_count' => $smsParts,
                'cost_per_sms' => $appliedRate,
                'total_cost' => 0.00,
                'is_double_charge' => $isDoubleCharge,
                'status' => 'failed',
                'sender_id' => $senderId,
                'response' => $errorMessage,
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'status_code' => $response->status(),
                'sms_log' => $log,
            ];

        } catch (Throwable $e) {
            Log::error('[SmsService] Exception while sending SMS: '.$e->getMessage(), [
                'recipient' => $normalizedContact,
                'trace' => $e->getTraceAsString(),
            ]);

            $log = \App\Models\SmsLog::create([
                'recipient' => $normalizedContact,
                'message' => $message,
                'sms_count' => $smsParts,
                'cost_per_sms' => $appliedRate,
                'total_cost' => 0.00,
                'is_double_charge' => $isDoubleCharge,
                'status' => 'failed',
                'sender_id' => $senderId,
                'response' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => false,
                'message' => 'SMS gateway communication error: '.$e->getMessage(),
                'sms_log' => $log,
            ];
        }
    }

    /**
     * Check SMSlenz account status and credit balance.
     */
    public static function checkAccountStatus(): array
    {
        $statusUrl = config('sms.status_url', 'https://smslenz.lk/api/account-status');
        $userId = config('sms.user_id', '');
        $apiKey = config('sms.api_key', '');
        $timeout = config('sms.timeout', 5);

        if (empty($statusUrl) || empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Missing SMSlenz status URL or API key.',
            ];
        }

        try {
            $response = Http::asForm()
                ->timeout($timeout)
                ->post($statusUrl, [
                    'user_id' => $userId,
                    'api_key' => $apiKey,
                ]);

            return $response->json() ?: [
                'success' => $response->successful(),
                'body' => $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to check account status: '.$e->getMessage(),
            ];
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Order Lifecycle Notifications
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Get configured branch name for SMS messages.
     */
    public static function getBranchName(): string
    {
        return config('shop.branch', 'Vavuniya Branch');
    }

    /**
     * 1. Notify Customer when an order is placed.
     */
    public static function notifyOrderPlaced($order, $sale = null): array
    {
        $phone = $order->customer_phone ?? ($sale->customer_display_phone ?? '');
        $name = $order->customer_name ?? ($sale->customer_display_name ?? 'Valued Customer');
        $orderNumber = $order->order_number ?? ($sale->invoice_number ?? 'N/A');
        $total = number_format((float) ($order->total_amount ?? ($sale->total_amount ?? 0)), 2);
        $branchName = self::getBranchName();

        $message = "Dear {$name}, thank you for ordering at Jaffna Gold ({$branchName})! Your order #{$orderNumber} (LKR {$total}) has been received. We will notify you once confirmed.";

        return self::sendSms($phone, $message);
    }

    /**
     * 2. Notify Customer when their order is confirmed.
     */
    public static function notifyOrderConfirmed($sale, $order = null): array
    {
        $phone = $sale->customer_display_phone ?: ($order->customer_phone ?? '');
        $name = $sale->customer_display_name ?: ($order->customer_name ?? 'Valued Customer');
        $invoice = $sale->invoice_number ?: ($order->order_number ?? 'N/A');
        $branchName = self::getBranchName();

        $message = "Dear {$name}, your Jaffna Gold ({$branchName}) order #{$invoice} has been CONFIRMED! Your jewelry piece is now being prepared for dispatch.";

        return self::sendSms($phone, $message);
    }

    /**
     * 3. Notify Customer when order is dispatched / in-transit with courier.
     */
    public static function notifyOrderInTransit($sale, ?string $courierMethod = null, ?string $trackingNumber = null): array
    {
        $phone = $sale->customer_display_phone ?? '';
        $name = $sale->customer_display_name ?: 'Valued Customer';
        $invoice = $sale->invoice_number ?: 'N/A';
        $courier = $courierMethod ?: ($sale->deliverySale->delivery_method ?? 'Courier');
        $tracking = $trackingNumber ?: ($sale->deliverySale->tracking_number ?? '');
        $branchName = self::getBranchName();

        $trackingPart = ! empty($tracking) ? " Tracking: {$tracking}." : '';
        $message = "Dear {$name}, your Jaffna Gold ({$branchName}) order #{$invoice} is OUT FOR DELIVERY via {$courier}.{$trackingPart} Thank you for your patience!";

        return self::sendSms($phone, $message);
    }

    /**
     * 4. Notify Customer when order has been delivered.
     */
    public static function notifyOrderDelivered($sale): array
    {
        $phone = $sale->customer_display_phone ?? '';
        $name = $sale->customer_display_name ?: 'Valued Customer';
        $invoice = $sale->invoice_number ?: 'N/A';
        $branchName = self::getBranchName();

        $message = "Dear {$name}, your Jaffna Gold ({$branchName}) order #{$invoice} has been DELIVERED! Thank you for choosing Jaffna Gold ({$branchName}).";

        return self::sendSms($phone, $message);
    }

    /**
     * 5. Notify Customer when order is cancelled.
     */
    public static function notifyOrderCancelled($sale, ?string $reason = null): array
    {
        $phone = $sale->customer_display_phone ?? '';
        $name = $sale->customer_display_name ?: 'Valued Customer';
        $invoice = $sale->invoice_number ?: 'N/A';
        $reasonPart = ! empty($reason) ? " Reason: {$reason}." : '';
        $branchName = self::getBranchName();

        $message = "Dear {$name}, your Jaffna Gold ({$branchName}) order #{$invoice} has been cancelled.{$reasonPart} Please contact us for inquiries.";

        return self::sendSms($phone, $message);
    }

    /**
     * 6. Format SMS message for Gold Plating / Service order ready for delivery / pickup.
     * Contains 7-day pickup request and shop contact info as requested by user.
     */
    public static function formatGoldPlatingReadyMessage($order): string
    {
        $name = trim($order->customer_name ?: 'Valued Customer');
        $code = $order->order_code ?? 'N/A';

        $balanceText = '';
        if (! $order->is_charge_tbd && ! is_null($order->customer_charge)) {
            $balance = (float) ($order->balance_amount ?? 0);
            if ($balance > 0) {
                $balanceText = ' Balance Due: Rs. '.number_format($balance, 2).'.';
            } else {
                $balanceText = ' Balance: Fully Paid.';
            }
        }

        $branchName = config('shop.branch', 'Vavuniya Branch');
        $shopAddress = config('shop.address', '2nd Cross Street, Vavuniya');
        $shopPhone = config('shop.phone', '0761919650');
        $shopHotline = config('shop.hotline', '021 222 85 89');

        return "Dear {$name}, your jewelry service order #{$code} is READY for collection!{$balanceText} Please collect your item(s) within 7 days. Jaffna Gold ({$branchName}), {$shopAddress}. Tel: {$shopPhone} / {$shopHotline}.";
    }

    /**
     * 7. Notify Customer when gold plating / service item is ready for collection / delivery.
     */
    public static function notifyGoldPlatingReady($order, ?string $customMessage = null): array
    {
        // Skip internal shop work or handmade inventory
        if (isset($order->order_category) && in_array($order->order_category, ['shop', 'handmade'])) {
            return [
                'success' => false,
                'message' => 'Internal shop work or inventory orders do not require customer SMS.',
            ];
        }

        $phone = $order->customer_phone ?? '';
        if (empty(trim($phone))) {
            return [
                'success' => false,
                'message' => 'Customer has no phone number recorded.',
            ];
        }

        $message = $customMessage ?: self::formatGoldPlatingReadyMessage($order);
        $result = self::sendSms($phone, $message);

        if ($result['success'] ?? false) {
            try {
                $order->update(['ready_sms_sent_at' => now()]);
            } catch (Throwable $e) {
                Log::warning('[SmsService] Could not update ready_sms_sent_at on GoldPlatingOrder #'.($order->id ?? 0).': '.$e->getMessage());
            }
        }

        return $result;
    }

    /**
     * 8. Notify Customer with store bill invoice total and public view link.
     */
    public static function notifyStoreSale(Sale $sale): array
    {
        $phone = $sale->customer_display_phone;
        if (empty($phone) || $phone === 'xxxxx') {
            return [
                'success' => false,
                'message' => 'No valid phone number for customer.',
            ];
        }

        $name = trim($sale->customer_display_name ?: '');
        if (empty($name) || $name === 'Walking Customer' || $name === 'Online Customer') {
            $name = 'Valued Customer';
        }

        $invoiceNumber = $sale->invoice_number ?: ($sale->sale_id ?: 'INV-'.$sale->id);
        $totalFormatted = number_format((float) ($sale->total_amount ?? 0), 2);
        $due = (float) ($sale->due_amount ?? 0);
        $invoiceUrl = PublicInvoiceController::generateUrl($sale);
        $branchName = self::getBranchName();

        if ($due > 0) {
            $dueFormatted = number_format($due, 2);
            $message = "Dear {$name}, thank you for your purchase at Jaffna Gold ({$branchName})! Bill #{$invoiceNumber}, Total: Rs.{$totalFormatted} (Due: Rs.{$dueFormatted}). View your bill: {$invoiceUrl}";
        } else {
            $message = "Dear {$name}, thank you for your purchase at Jaffna Gold ({$branchName})! Bill #{$invoiceNumber}, Total: Rs.{$totalFormatted}. View your bill: {$invoiceUrl}";
        }

        $result = self::sendSms($phone, $message);

        Log::info('[SmsService] Store sale SMS attempt', [
            'sale_id' => $sale->id,
            'phone' => $phone,
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
        ]);

        return $result;
    }
}
