<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomProductInquiry extends Model
{
    use HasFactory;

    protected $table = 'custom_product_inquiries';

    protected $fillable = [
        'inquiry_number',
        'custom_product_id',
        'product_name',
        'category',
        'customer_name',
        'customer_phone',
        'customer_email',
        'city',
        'selected_pown',
        'selected_grams',
        'size_specifications',
        'gold_purity',
        'estimated_price',
        'quoted_price',
        'customer_notes',
        'admin_notes',
        'reference_image',
        'status',
        'assigned_to',
    ];

    protected $casts = [
        'selected_pown' => 'decimal:2',
        'selected_grams' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'quoted_price' => 'decimal:2',
    ];

    /**
     * Associated custom product.
     */
    public function customProduct(): BelongsTo
    {
        return $this->belongsTo(CustomProduct::class, 'custom_product_id');
    }

    /**
     * Associated staff/admin user assigned to this inquiry.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Generate unique inquiry tracking number.
     */
    public static function generateInquiryNumber(): string
    {
        $prefix = 'CUST-' . date('Ymd');
        $lastInquiry = self::where('inquiry_number', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastInquiry && preg_match('/-(\d{4})$/', $lastInquiry->inquiry_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%04d', $prefix, $sequence);
    }
}
