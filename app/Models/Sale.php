<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'invoice_number',
        'customer_id',
        'walking_customer_name',
        'walking_customer_phone',
        'customer_type',
        'subtotal',
        'discount_amount',
        'discount_type',
        'additional_discount_type',
        'additional_discount_percentage',
        'total_amount',
        'payment_type',
        'payment_status',
        'status',
        'notes',
        'due_amount',
        'user_id',
        'sale_type',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'delivered_by',
        'delivered_at',
        'delivery_status',
        'order_source',
        'ecommerce_order_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate unique sale ID with date prefix and collision avoidance
     * Example: SALE-20261017-0001
     */
    public static function generateSaleId(): string
    {
        $prefix = 'SALE-';
        $date = now()->format('Ymd');
        $lastSale = self::where('sale_id', 'like', "{$prefix}{$date}%")
            ->orderBy('sale_id', 'desc')
            ->lockForUpdate()
            ->first();

        $nextNumber = 1;

        if ($lastSale) {
            $parts = explode('-', $lastSale->sale_id);
            $lastNumber = intval(end($parts));
            $nextNumber = $lastNumber + 1;
        }

        $candidate = $prefix.$date.'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        while (self::where('sale_id', $candidate)->exists()) {
            $nextNumber++;
            $candidate = $prefix.$date.'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    /**
     * Generate unique invoice numbers starting from 001.
     * Guarantees no duplicate key collision even if non-numeric, prefixed,
     * or out-of-order records exist in the sales table.
     */
    public static function generateInvoiceNumber(): string
    {
        $driver = DB::connection()->getDriverName();
        $maxNumeric = null;

        if ($driver === 'mysql' || $driver === 'mariadb') {
            try {
                $maxNumeric = DB::table('sales')
                    ->where(function ($q) {
                        $q->whereRaw("invoice_number REGEXP '^[0-9]+$'")
                          ->orWhereRaw("invoice_number REGEXP '^INV-[0-9]+$'");
                    })
                    ->selectRaw("MAX(CAST(CASE WHEN invoice_number LIKE 'INV-%' THEN SUBSTRING(invoice_number, 5) ELSE invoice_number END AS UNSIGNED)) as max_inv")
                    ->value('max_inv');
            } catch (\Throwable $e) {
                $maxNumeric = null;
            }
        }

        // Fallback or SQLite/in-memory test environment: inspect recent sales
        if ($maxNumeric === null) {
            $recentSales = self::orderBy('id', 'desc')->take(100)->get(['invoice_number']);
            foreach ($recentSales as $sale) {
                $inv = $sale->invoice_number;
                if (! $inv) {
                    continue;
                }
                if (str_starts_with($inv, 'INV-')) {
                    $inv = substr($inv, 4);
                }
                if (ctype_digit($inv)) {
                    $num = intval($inv);
                    if ($maxNumeric === null || $num > $maxNumeric) {
                        $maxNumeric = $num;
                    }
                }
            }
        }

        $nextNumber = ($maxNumeric !== null && $maxNumeric > 0) ? ($maxNumeric + 1) : 1;
        $candidate = str_pad($nextNumber, max(3, strlen((string) $nextNumber)), '0', STR_PAD_LEFT);

        // Absolute guarantee: never return an invoice_number that already exists in the database
        while (self::where('invoice_number', $candidate)->exists()) {
            $nextNumber++;
            $candidate = str_pad($nextNumber, max(3, strlen((string) $nextNumber)), '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public function returns()
    {
        return $this->hasMany(ReturnsProduct::class, 'sale_id');
    }

    /**
     * Relationship: Delivery sale details
     */
    public function deliverySale()
    {
        return $this->hasOne(DeliverySale::class);
    }

    /**
     * Relationship: Domex live order tracking details
     */
    public function domexOrderDetail()
    {
        return $this->hasOne(DomexOrderDetail::class);
    }

    /**
     * Relationship: Post (BePost) live order tracking details
     */
    public function postOrderDetail()
    {
        return $this->hasOne(PostOrderDetail::class);
    }

    /**
     * Relationship: Approved by admin
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship: Delivered by delivery man
     */
    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    /**
     * Check if sale is pending approval
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if sale is approved
     */
    public function isApproved()
    {
        return $this->status === 'confirm';
    }

    /**
     * Check if sale is rejected or cancelled
     */
    public function isRejected()
    {
        return in_array($this->status, ['rejected', 'cancelled']);
    }

    /**
     * Check if sale is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled' || $this->delivery_status === 'cancelled';
    }

    /**
     * Check if sale is delivered
     */
    public function isDelivered()
    {
        return $this->delivery_status === 'delivered';
    }

    /**
     * Accessor: Customer display name
     */
    public function getCustomerDisplayNameAttribute()
    {
        if ($this->customer && $this->customer->name && $this->customer->name !== 'Walking Customer') {
            return $this->customer->name;
        }
        if (! empty($this->walking_customer_name)) {
            return $this->walking_customer_name;
        }
        if ($this->deliverySale && ! empty($this->deliverySale->customer_details)) {
            if (preg_match('/Customer:\s*([^|\n]+)/i', $this->deliverySale->customer_details, $m)) {
                return trim($m[1]);
            }
        }

        return 'Online Customer';
    }

    /**
     * Accessor: Customer display phone
     */
    public function getCustomerDisplayPhoneAttribute()
    {
        if ($this->customer && $this->customer->phone && $this->customer->phone !== 'xxxxx') {
            return $this->customer->phone;
        }
        if (! empty($this->walking_customer_phone)) {
            return $this->walking_customer_phone;
        }
        if ($this->deliverySale && ! empty($this->deliverySale->customer_details)) {
            if (preg_match('/Phone:\s*([^|\n]+)/i', $this->deliverySale->customer_details, $m)) {
                return trim($m[1]);
            }
        }

        return null;
    }

    /**
     * Accessor: Customer display address
     */
    public function getCustomerDisplayAddressAttribute()
    {
        if ($this->deliverySale && ! empty($this->deliverySale->customer_details)) {
            if (preg_match('/Address:\s*(.+)/is', $this->deliverySale->customer_details, $m)) {
                return trim($m[1]);
            }
        }
        if ($this->customer && $this->customer->address && $this->customer->address !== 'xxxxx') {
            return $this->customer->address;
        }

        return 'No address provided';
    }

    /**
     * Accessor: WhatsApp chat link
     */
    public function getWhatsappLinkAttribute()
    {
        $phone = $this->customer_display_phone;
        if (! $phone) {
            return null;
        }
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '94'.substr($clean, 1);
        } elseif (! str_starts_with($clean, '94') && strlen($clean) === 9) {
            $clean = '94'.$clean;
        }

        return 'https://wa.me/'.$clean;
    }

    /**
     * Get pending quantity for a specific product (for available stock calculation)
     */
    public static function getPendingQuantityForProduct($productId, $variantValue = null)
    {
        $query = self::where('status', 'pending')
            ->whereHas('items', function ($q) use ($productId, $variantValue) {
                $q->where('product_id', $productId);
                if ($variantValue) {
                    $q->where('variant_value', $variantValue);
                }
            });

        $sales = $query->with(['items' => function ($q) use ($productId, $variantValue) {
            $q->where('product_id', $productId);
            if ($variantValue) {
                $q->where('variant_value', $variantValue);
            }
        }])->get();

        return $sales->sum(function ($sale) {
            return $sale->items->sum('quantity');
        });
    }

    /**
     * Confirm the order and deduct product stocks if not already deducted.
     * Returns true if confirmation succeeded and deducted, false if already confirmed.
     */
    public function confirmAndDeductStock(): bool
    {
        if ($this->status === 'confirm') {
            return false; // Already confirmed, prevent double deduction
        }

        $this->loadMissing('items');

        foreach ($this->items as $item) {
            if ($item->product_id) {
                $stock = ProductStock::where('product_id', $item->product_id)->first();
                if ($stock) {
                    $stock->available_stock = max(0, $stock->available_stock - $item->quantity);
                    $stock->sold_count += $item->quantity;
                    $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                    $stock->save();
                }
            }
        }

        $this->status = 'confirm';
        $this->save();

        // Also sync EcommerceOrder status if present
        if ($this->ecommerce_order_id) {
            $ecommerceOrder = EcommerceOrder::find($this->ecommerce_order_id);
            if ($ecommerceOrder && $ecommerceOrder->order_status === 'pending') {
                $ecommerceOrder->order_status = 'confirmed';
                $ecommerceOrder->save();
            }
        }

        return true;
    }

    /**
     * Restore deducted stock if an order is cancelled or returned.
     */
    public function restoreStock(): bool
    {
        // Prevent restoring if already returned or cancelled
        if (in_array($this->delivery_status, ['returned', 'cancelled'])) {
            return false;
        }

        $this->loadMissing('items');

        foreach ($this->items as $item) {
            if (! $item->product_id || $item->quantity <= 0) {
                continue;
            }

            // Restore stock using centralized FIFOStockService (which updates ProductStock and ProductBatch)
            try {
                \App\Services\FIFOStockService::restoreStock(
                    $item->product_id,
                    $item->quantity,
                    $item->variant_id ?? null,
                    $item->variant_value ?? null
                );
            } catch (\Throwable $e) {
                // Fallback to manual restore if FIFOStockService fails
                $stockQuery = ProductStock::where('product_id', $item->product_id);
                if (! empty($item->variant_id)) {
                    $stockQuery->where('variant_id', $item->variant_id);
                    if (! empty($item->variant_value)) {
                        $stockQuery->where('variant_value', $item->variant_value);
                    }
                }
                $stock = $stockQuery->first();
                if (! $stock) {
                    $stock = ProductStock::where('product_id', $item->product_id)->first();
                }

                if ($stock) {
                    $stock->available_stock += $item->quantity;
                    if ($stock->sold_count >= $item->quantity) {
                        $stock->sold_count -= $item->quantity;
                    }
                    $stock->updateTotals();
                }
            }
        }

        // Clear API product cache so updated stock reflects immediately
        try {
            \App\Http\Controllers\ProductApiController::clearCache();
        } catch (\Throwable $e) {
        }

        return true;
    }
}
