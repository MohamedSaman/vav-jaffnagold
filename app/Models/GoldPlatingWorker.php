<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoldPlatingWorker extends Model
{
    use HasFactory;

    protected $table = 'gold_plating_workers';

    protected $fillable = [
        'name',
        'phone',
        'opening_payable_balance',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'opening_payable_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(GoldPlatingOrder::class, 'worker_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(GoldPlatingWorkerPayment::class, 'worker_id');
    }

    public function handmadeItems(): HasMany
    {
        return $this->hasMany(HandmadeItem::class, 'plating_worker_id');
    }

    /**
     * Total workshop charge earned from completed works (ready or delivered).
     */
    public function getTotalEarnedAttribute(): float
    {
        return (float) $this->orders()
            ->whereIn('status', ['ready', 'delivered'])
            ->sum('our_cost');
    }

    /**
     * Total workshop charge across all assigned orders (including in progress / received).
     */
    public function getTotalAssignedCostAttribute(): float
    {
        return (float) $this->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('our_cost');
    }

    /**
     * Total payout payments given to this worker.
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /**
     * Current outstanding payable balance:
     * Opening Balance + Completed Works Cost - Total Payments Made
     */
    public function getOutstandingBalanceAttribute(): float
    {
        return round((float) $this->opening_payable_balance + $this->total_earned - $this->total_paid, 2);
    }

    public function getAssignedOrdersCountAttribute(): int
    {
        return $this->orders()->where('status', '!=', 'cancelled')->count();
    }

    public function getCompletedOrdersCountAttribute(): int
    {
        return $this->orders()->whereIn('status', ['ready', 'delivered'])->count();
    }
}
