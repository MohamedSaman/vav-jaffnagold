<?php

namespace App\Http\Controllers;

use App\Models\Sale;

class PublicInvoiceController extends Controller
{
    /**
     * Display a public invoice page for customers.
     * URL uses a HMAC-signed token so invoice IDs aren't guessable or tamperable.
     */
    public function show(string $token)
    {
        $decoded = self::urlSafeBase64Decode($token);
        if (! $decoded || ! str_contains($decoded, ':')) {
            abort(404, 'Invalid invoice link');
        }

        [$saleId, $signature] = explode(':', $decoded, 2);

        // Verify HMAC signature
        $expectedSignature = hash_hmac('sha256', (string) $saleId, config('app.key'));
        if (! hash_equals($expectedSignature, $signature)) {
            abort(404, 'Invoice link expired or invalid');
        }

        $sale = Sale::with([
            'items.product',
            'payments',
            'customer',
            'returns.product',
            'user',
            'deliverySale',
        ])->find($saleId);

        if (! $sale) {
            abort(404, 'Invoice not found');
        }

        return view('public.invoice', compact('sale'));
    }

    /**
     * Generate a secure public URL token for a sale.
     */
    public static function generateToken(Sale $sale): string
    {
        $signature = hash_hmac('sha256', (string) $sale->id, config('app.key'));

        return self::urlSafeBase64Encode($sale->id.':'.$signature);
    }

    /**
     * Generate the full public URL for a sale invoice.
     */
    public static function generateUrl(Sale $sale): string
    {
        $token = self::generateToken($sale);

        return route('public.invoice', ['token' => $token]);
    }

    public static function urlSafeBase64Encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function urlSafeBase64Decode(string $data): string|false
    {
        // Handle URL safe base64
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        $decoded = base64_decode(strtr($data, '-_', '+/'));
        if ($decoded !== false && str_contains($decoded, ':')) {
            return $decoded;
        }

        // Fallback for standard base64
        return base64_decode($data);
    }
}
