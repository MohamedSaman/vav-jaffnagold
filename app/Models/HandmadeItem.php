<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandmadeItem extends Model
{
    use HasFactory;

    protected $table = 'handmade_items';

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'category_id',
        'item_code',
        'item_name',
        'product_type',
        'status',
        'quantity',
        'weight',
        'purchase_cost',
        'stone_cost',
        'stone_worker_notes',
        'plating_cost',
        'plating_worker_id',
        'total_accumulated_cost',
        'selling_price',
        'product_detail_id',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'purchase_cost' => 'decimal:2',
        'stone_cost' => 'decimal:2',
        'plating_cost' => 'decimal:2',
        'total_accumulated_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(HandmadePurchase::class, 'purchase_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(HandmadeSupplier::class, 'supplier_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryList::class, 'category_id', 'id');
    }

    public function platingWorker(): BelongsTo
    {
        return $this->belongsTo(GoldPlatingWorker::class, 'plating_worker_id', 'id');
    }

    public function productDetail(): BelongsTo
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }

    public function recalculateTotalCost(): void
    {
        $this->total_accumulated_cost = round(
            (float) $this->purchase_cost + 
            (float) $this->stone_cost + 
            (float) $this->plating_cost, 
            2
        );
        $this->save();
    }

    public static function generateItemCode(?string $itemName = null, array $excludeCodes = []): string
    {
        // Format: SL-"product name "-001 e.g. SL-KODI-001
        $rawName = $itemName ? trim($itemName) : '';
        // Replace non-alphanumeric with hyphen
        $cleanName = strtoupper(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $rawName), '-'));
        if (empty($cleanName)) {
            $cleanName = 'ITEM';
        }
        // Limit slug length to 25 chars to fit within 50 chars column limit
        if (strlen($cleanName) > 25) {
            $cleanName = rtrim(substr($cleanName, 0, 25), '-');
        }

        $prefix = "SL-{$cleanName}-";

        // Query existing item codes with this prefix
        $existingCodes = self::where('item_code', 'LIKE', $prefix . '%')->pluck('item_code')->toArray();
        $allCodes = array_unique(array_merge($existingCodes, $excludeCodes));

        $maxNum = 0;
        foreach ($allCodes as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $code, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        $candidateCode = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        // Ensure candidateCode does not collide with existing HandmadeItem or ProductDetail
        while (
            in_array($candidateCode, $allCodes) ||
            self::where('item_code', $candidateCode)->exists() ||
            ProductDetail::where('code', $candidateCode)->exists() ||
            ProductDetail::where('barcode', $candidateCode)->exists()
        ) {
            $nextNum++;
            $candidateCode = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        return $candidateCode;
    }
}
