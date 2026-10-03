<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HandmadePurchase extends Model
{
    use HasFactory;

    protected $table = 'handmade_purchases';

    protected $fillable = [
        'purchase_code',
        'supplier_id',
        'user_id',
        'purchase_date',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(HandmadeSupplier::class, 'supplier_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HandmadeItem::class, 'purchase_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HandmadeSupplierPayment::class, 'purchase_id');
    }

    public static function generatePurchaseCode(): string
    {
        $datePrefix = 'HP-' . date('Ymd') . '-';
        $last = self::where('purchase_code', 'like', $datePrefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $num = intval(substr($last->purchase_code, strlen($datePrefix)));
            $next = str_pad($num + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $next = '001';
        }

        return $datePrefix . $next;
    }
}
