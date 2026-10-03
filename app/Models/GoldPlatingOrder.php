<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoldPlatingOrder extends Model
{
    use HasFactory;

    protected $table = 'gold_plating_orders';

    protected $fillable = [
        'order_code',
        'user_id',
        'worker_id',
        'handmade_item_id',
        'order_category',
        'customer_name',
        'customer_phone',
        'customer_address',
        'service_type',
        'item_description',
        'item_weight',
        'is_charge_tbd',
        'our_cost',
        'customer_charge',
        'advance_amount',
        'advance_payment_method',
        'balance_amount',
        'status',
        'received_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'ready_sms_sent_at',
        'notes',
    ];

    protected $casts = [
        'item_weight' => 'decimal:2',
        'is_charge_tbd' => 'boolean',
        'our_cost' => 'decimal:2',
        'customer_charge' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'received_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'datetime',
        'ready_sms_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(GoldPlatingWorker::class, 'worker_id', 'id');
    }

    public function handmadeItem(): BelongsTo
    {
        return $this->belongsTo(HandmadeItem::class, 'handmade_item_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(GoldPlatingPayment::class, 'gold_plating_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoldPlatingOrderItem::class, 'gold_plating_order_id');
    }

    /**
     * Calculated profit (Customer Charge - Our Cost)
     */
    public function getProfitAttribute(): ?float
    {
        if ($this->is_charge_tbd || is_null($this->customer_charge)) {
            return null;
        }

        return (float) ($this->customer_charge - ($this->our_cost ?? 0));
    }

    public function getReceivedItemsCountAttribute(): int
    {
        return $this->items->where('status', 'received')->count();
    }

    public function getTotalItemsCountAttribute(): int
    {
        return $this->items->count();
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        $total = $this->total_items_count;

        return $total > 0 && $this->received_items_count === $total;
    }

    public function getIsPartiallyReceivedAttribute(): bool
    {
        $received = $this->received_items_count;
        $total = $this->total_items_count;

        return $total > 0 && $received > 0 && $received < $total;
    }

    /**
     * Recalculate and synchronize order status based on item-level receiving progress.
     */
    public function syncReceivingStatus(): string
    {
        $this->load('items');
        $total = $this->items->count();

        // If no items registered, don't auto-change
        if ($total === 0) {
            return $this->status;
        }

        $received = $this->items->where('status', 'received')->count();

        // Do not alter terminal delivered or cancelled states
        if (in_array($this->status, ['delivered', 'cancelled'])) {
            return $this->status;
        }

        $newStatus = $this->status;

        if ($received === $total) {
            $newStatus = 'ready';
        } elseif ($received > 0 && $received < $total) {
            $newStatus = 'partial';
        } elseif ($received === 0) {
            if (in_array($this->status, ['partial', 'ready'])) {
                $newStatus = 'in_progress';
            }
        }

        if ($newStatus !== $this->status) {
            $this->update(['status' => $newStatus]);

            // Sync handmade item if applicable
            if ($this->handmade_item_id) {
                $handmadeStatus = match ($newStatus) {
                    'ready' => 'ready_for_stock',
                    'partial', 'in_progress', 'received' => 'plating',
                    default => 'plating'
                };
                HandmadeItem::where('id', $this->handmade_item_id)->update(['status' => $handmadeStatus]);
            }
        }

        return $newStatus;
    }

    /**
     * Generate unique readable order code (e.g. GP-20260909-001)
     */
    public static function generateOrderCode(): string
    {
        $datePrefix = 'GP-'.date('Ymd').'-';
        $lastOrder = self::where('order_code', 'like', $datePrefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_code, strlen($datePrefix)));
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return $datePrefix.$nextNumber;
    }
}
