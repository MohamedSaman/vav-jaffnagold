<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandmadeSupplierPayment extends Model
{
    use HasFactory;

    protected $table = 'handmade_supplier_payments';

    protected $fillable = [
        'supplier_id',
        'purchase_id',
        'user_id',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(HandmadeSupplier::class, 'supplier_id', 'id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(HandmadePurchase::class, 'purchase_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
