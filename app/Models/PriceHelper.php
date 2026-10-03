<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHelper extends Model
{
    use HasFactory;

    protected $table = 'price_helpers';

    protected $fillable = [
        'item_name',
        'category',
        'min_price',
        'max_price',
        'notes',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope for active records
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Formatted Price Range Attribute
     */
    public function getFormattedRangeAttribute(): string
    {
        return 'Rs. ' . number_format($this->min_price, 0) . ' - ' . number_format($this->max_price, 0);
    }
}
