<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient',
        'message',
        'sms_count',
        'cost_per_sms',
        'total_cost',
        'is_double_charge',
        'status',
        'sender_id',
        'response',
        'user_id',
    ];

    protected $casts = [
        'sms_count' => 'integer',
        'cost_per_sms' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'is_double_charge' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
