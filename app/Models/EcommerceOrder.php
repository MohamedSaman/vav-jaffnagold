<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcommerceOrder extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_orders';

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'city',
        'postal_code',
        'subtotal',
        'shipping_charge',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'notes',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(EcommerceOrderItem::class, 'order_id');
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-';
        $date = now()->format('Ymd');
        
        // Find latest order for today
        $lastOrder = self::where('order_number', 'like', "{$prefix}{$date}-%")
            ->orderBy('order_number', 'desc')
            ->first();

        $nextNumber = 1;

        if ($lastOrder) {
            $parts = explode('-', $lastOrder->order_number);
            $lastNumber = intval(end($parts));
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . $date . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
