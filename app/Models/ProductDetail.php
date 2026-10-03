<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'size',
        'model',
        'image',
        'images',
        'description',
        'barcode',
        'barcode_printed',
        'status',
        'sale_bonus',
        'brand_id',
        'category_id',
        'supplier_id',
        'variant_id',
        'origin',
        'website_live',
    ];

    public function isLocal(): bool
    {
        return ($this->origin === 'local') 
            || ($this->model === 'Handmade Jewelry') 
            || str_starts_with($this->code, 'SL-')
            || str_starts_with($this->barcode ?? '', 'SL-');
    }

    public function isIndian(): bool
    {
        return !$this->isLocal();
    }

    protected $casts = [
        'website_live' => 'boolean',
        'images' => 'array',
    ];

    public function price(): HasOne
    {
        return $this->hasOne(ProductPrice::class, 'product_id');
    }

    public function stock(): HasOne
    {
        return $this->hasOne(ProductStock::class, 'product_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandList::class, 'brand_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryList::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(ProductSupplier::class, 'supplier_id');
    }

    /**
     * Get the variant this product uses
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get all prices for this product (including variant prices)
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'product_id');
    }

    /**
     * Get all stock records for this product (including variant stocks)
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }

    /**
     * Check if product has variants
     */
    public function hasVariants(): bool
    {
        return $this->variant_id !== null;
    }

    public function returns()
    {
        return $this->hasMany(ReturnsProduct::class, 'product_id');
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class, 'product_id');
    }

    public function activeBatches()
    {
        return $this->hasMany(ProductBatch::class, 'product_id')
            ->where('status', 'active')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc');
    }

    public function handmadeItem(): HasOne
    {
        return $this->hasOne(HandmadeItem::class, 'product_detail_id');
    }

    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'code');
    }

    /**
     * Get the product image URL or default image
     *
     * @return string
     */
    public function getImageAttribute($value)
    {
        // If image exists, return it; otherwise return default image path
        return $value ?: 'images/product.jpg';
    }

    /**
     * Get clean URL slug for product.
     */
    public function getSlugAttribute(): string
    {
        $baseName = $this->name ?: 'product';
        $slugName = \Illuminate\Support\Str::slug($baseName);
        return $slugName ? $slugName . '-' . $this->id : 'product-' . $this->id;
    }

    /**
     * Resolve any image path or filename to a valid web URL.
     */
    public static function resolveImageUrl(?string $img): string
    {
        $image = trim((string) ($img ?? ''));

        if ($image === '' || $image === 'images/product.jpg') {
            return asset('images/product.jpg');
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        if (str_starts_with($image, 'storage/images/')) {
            $filename = substr($image, strlen('storage/images/'));
            return url('/product-image-serve/' . $filename);
        }

        if (str_starts_with($image, 'images/') || str_starts_with($image, 'storage/')) {
            return asset($image);
        }

        if (str_contains($image, '/')) {
            return asset(ltrim($image, '/'));
        }

        return url('/product-image-serve/' . ltrim($image, '/'));
    }

    /**
     * Get all images as fully-qualified URLs for galleries.
     */
    public function getAllImageUrlsAttribute(): array
    {
        $paths = [];

        $primary = $this->getRawOriginal('image') ?? $this->image;
        if ($primary && $primary !== 'images/product.jpg') {
            $paths[] = $primary;
        }

        $gallery = is_array($this->images) ? $this->images : (json_decode($this->images ?? '[]', true) ?: []);
        foreach ($gallery as $img) {
            if ($img && !in_array($img, $paths)) {
                $paths[] = $img;
            }
        }

        if (empty($paths)) {
            return [asset('images/product.jpg')];
        }

        return array_map([self::class, 'resolveImageUrl'], $paths);
    }
}
