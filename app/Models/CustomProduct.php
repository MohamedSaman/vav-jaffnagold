<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomProduct extends Model
{
    use HasFactory;

    protected $table = 'custom_products';

    protected $fillable = [
        'name',
        'tamil_name',
        'slug',
        'category',
        'category_label',
        'purity',
        'description',
        'pown_options',
        'min_pown',
        'max_pown',
        'base_pown',
        'price_per_pown_estimate',
        'price_range_min',
        'price_range_max',
        'making_days',
        'image',
        'gallery_images',
        'specifications',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'pown_options' => 'array',
        'gallery_images' => 'array',
        'specifications' => 'array',
        'min_pown' => 'decimal:2',
        'max_pown' => 'decimal:2',
        'base_pown' => 'decimal:2',
        'price_per_pown_estimate' => 'decimal:2',
        'price_range_min' => 'decimal:2',
        'price_range_max' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'making_days' => 'integer',
    ];

    /**
     * Scope for active custom products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured items.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope by category.
     */
    public function scopeCategory($query, string $category)
    {
        if ($category && $category !== 'all') {
            return $query->where('category', $category);
        }

        return $query;
    }

    /**
     * Inquiries submitted for this custom product.
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(CustomProductInquiry::class, 'custom_product_id');
    }

    /**
     * Weight in grams for min pown (1 Pown = 8 grams in Sri Lanka).
     */
    public function getMinGramsAttribute(): float
    {
        return (float) $this->min_pown * 8.0;
    }

    /**
     * Weight in grams for max pown (1 Pown = 8 grams in Sri Lanka).
     */
    public function getMaxGramsAttribute(): float
    {
        return (float) $this->max_pown * 8.0;
    }

    /**
     * Formatted price range in LKR.
     */
    public function getFormattedPriceRangeAttribute(): string
    {
        return 'LKR '.number_format($this->price_range_min, 0).' – '.number_format($this->price_range_max, 0);
    }

    /**
     * Display image URL accessor.
     */
    public function getImageUrlAttribute(): string
    {
        if (! empty($this->image) && file_exists(public_path($this->image))) {
            return asset($this->image);
        }

        return asset('images/website images/img/img_1.jpeg');
    }

    /**
     * Get all image paths (main image + gallery images).
     */
    public function getAllImagesAttribute(): array
    {
        $images = [];
        if (! empty($this->image)) {
            $images[] = $this->image;
        }

        if (! empty($this->gallery_images) && is_array($this->gallery_images)) {
            foreach ($this->gallery_images as $img) {
                if (! empty($img) && ! in_array($img, $images)) {
                    $images[] = $img;
                }
            }
        }

        if (empty($images)) {
            $images[] = 'images/website images/img/img_1.jpeg';
        }

        return $images;
    }

    /**
     * Get all resolved image URLs (main image + gallery images).
     */
    public function getAllImageUrlsAttribute(): array
    {
        $urls = [];
        foreach ($this->all_images as $img) {
            if (! empty($img)) {
                $urls[] = asset($img);
            }
        }

        return $urls ?: [asset('images/website images/img/img_1.jpeg')];
    }
}
