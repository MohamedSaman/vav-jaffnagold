<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoldPlatingWorkerPayment extends Model
{
    use HasFactory;

    protected $table = 'gold_plating_worker_payments';

    protected $fillable = [
        'worker_id',
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

    public function worker(): BelongsTo
    {
        return $this->belongsTo(GoldPlatingWorker::class, 'worker_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
