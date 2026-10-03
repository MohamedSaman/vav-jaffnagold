<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoldPlatingPayment extends Model
{
    use HasFactory;

    protected $table = 'gold_plating_payments';

    protected $fillable = [
        'gold_plating_order_id',
        'user_id',
        'amount',
        'payment_type',
        'payment_method',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(GoldPlatingOrder::class, 'gold_plating_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
