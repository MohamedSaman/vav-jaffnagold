<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverySale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'ecommerce_order_id',
        'delivery_method',
        'payment_method',
        'delivery_charge',
        'status',
        'delivery_barcode',
        'tracking_number',
        'courier_status_detail',
        'courier_last_synced_at',
        'courier_tracking_history',
        'customer_details',
        'slip_image',
        'returned_at',
        'return_reason',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
        'courier_last_synced_at' => 'datetime',
        'courier_tracking_history' => 'array',
    ];

    /**
     * Boot the model and auto-generate delivery barcode on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deliverySale) {
            if (empty($deliverySale->delivery_barcode)) {
                $deliverySale->delivery_barcode = self::generateDeliveryBarcode();
            }
        });
    }

    /**
     * Generate a unique delivery barcode.
     * Format: DLV-YYYYMMDD-XXXXX (e.g., DLV-20260223-00001)
     */
    public static function generateDeliveryBarcode(): string
    {
        $prefix = 'DLV-'.date('Ymd').'-';

        // Get the latest barcode for today
        $latestBarcode = self::where('delivery_barcode', 'like', $prefix.'%')
            ->orderBy('delivery_barcode', 'desc')
            ->value('delivery_barcode');

        if ($latestBarcode) {
            // Extract the numeric part and increment
            $lastNumber = (int) substr($latestBarcode, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get the sale that this delivery belongs to.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function domexOrderDetail()
    {
        return $this->hasOne(DomexOrderDetail::class, 'delivery_sale_id');
    }

    public function postOrderDetail()
    {
        return $this->hasOne(PostOrderDetail::class, 'delivery_sale_id');
    }
}
