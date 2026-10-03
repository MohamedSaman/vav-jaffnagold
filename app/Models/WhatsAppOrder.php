<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WhatsAppOrder extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_orders';

    protected $fillable = [
        'token',
        'created_by',
        'customer_name',
        'customer_phone',
        'items',
        'subtotal',
        'discount_amount',
        'delivery_charge',
        'total_amount',
        'notes',
        'status',
        'sale_id',
        'expires_at',
    ];

    protected $casts = [
        'items' => 'array',
        'expires_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot to auto-generate unique token and expiration
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->token)) {
                $order->token = self::generateUniqueToken();
            }
            if (empty($order->expires_at)) {
                $order->expires_at = now()->addDays(3); // 72 hours expiry
            }
        });
    }

    /**
     * Generate unique random token for checkout URL
     */
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(24);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Get the user / delivery staff who created the order link.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the completed sale record once booked.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * Get direct checkout URL for customer.
     */
    public function getCheckoutUrlAttribute(): string
    {
        return url('/order-checkout/' . $this->token);
    }

    /**
     * Format WhatsApp share link with greeting and item breakdown
     */
    public function getWhatsAppShareLink(?string $phone = null): string
    {
        $targetPhone = $phone ?: $this->customer_phone;
        $cleanPhone = '';
        if ($targetPhone) {
            $digits = preg_replace('/[^0-9]/', '', $targetPhone);
            if (str_starts_with($digits, '0')) {
                $cleanPhone = '94' . substr($digits, 1);
            } elseif (!str_starts_with($digits, '94') && strlen($digits) === 9) {
                $cleanPhone = '94' . $digits;
            } else {
                $cleanPhone = $digits;
            }
        }

        $itemsSummary = '';
        if (is_array($this->items)) {
            foreach ($this->items as $idx => $it) {
                $itemsSummary .= "\n• " . ($it['name'] ?? 'Item') . ' (' . ($it['code'] ?? '') . ') x ' . ($it['quantity'] ?? 1) . ' = Rs. ' . number_format($it['subtotal'] ?? 0, 2);
            }
        }

        $discountText = ((float) $this->discount_amount > 0)
            ? ("🏷️ *Discount Savings:* -Rs. " . number_format($this->discount_amount, 2) . "\n")
            : '';

        $greeting = $this->customer_name ? "Hello " . $this->customer_name . "!" : "Hello!";
        $message = "✨ *JAFFNA GOLD COVERING* ✨\n\n"
            . "{$greeting}\n"
            . "Here is your order summary:"
            . "{$itemsSummary}\n\n"
            . $discountText
            . "📦 *Delivery Charge:* Rs. " . number_format($this->delivery_charge, 2) . "\n"
            . "💰 *Total Amount:* Rs. " . number_format($this->total_amount, 2) . "\n\n"
            . "👉 *Please click the link below to enter your delivery address & confirm your parcel:*\n"
            . $this->checkout_url . "\n\n"
            . "Thank you for choosing Jaffna Gold!";

        if ($cleanPhone) {
            return "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
        }

        return "https://api.whatsapp.com/send?text=" . urlencode($message);
    }
}
