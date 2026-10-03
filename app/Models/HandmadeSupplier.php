<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HandmadeSupplier extends Model
{
    use HasFactory;

    protected $table = 'handmade_suppliers';

    protected $fillable = [
        'name',
        'phone',
        'address',
        'opening_payable_balance',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'opening_payable_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(HandmadePurchase::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HandmadeItem::class, 'supplier_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HandmadeSupplierPayment::class, 'supplier_id');
    }

    public function getTotalPurchasedAttribute(): float
    {
        return (float) $this->purchases()->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return round((float) $this->opening_payable_balance + $this->total_purchased - $this->total_paid, 2);
    }
}
