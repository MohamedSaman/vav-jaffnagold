<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoldPlatingOrderItem extends Model
{
    use HasFactory;

    protected $table = 'gold_plating_order_items';

    protected $fillable = [
        'gold_plating_order_id',
        'item_name',
        'fee',
        'cost',
        'status',
        'received_at',
        'received_by',
        'received_notes',
        'notes',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'cost' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(GoldPlatingOrder::class, 'gold_plating_order_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function markReceived(?int $userId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'received',
            'received_at' => now(),
            'received_by' => $userId,
            'received_notes' => $notes ?: $this->received_notes,
        ]);
    }

    public function markPending(): void
    {
        $this->update([
            'status' => 'pending',
            'received_at' => null,
            'received_by' => null,
        ]);
    }
}
