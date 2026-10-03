<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'post_order_details';

    protected $fillable = [
        'sale_id',
        'delivery_sale_id',
        'barcode',
        'status',
        'normalized_status',
        'is_final',
        'delivery_po',
        'acceptance_po',
        'received_date',
        'cod_amount',
        'tracking_history',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'tracking_history' => 'array',
        'raw_data' => 'array',
        'last_synced_at' => 'datetime',
        'is_final' => 'boolean',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function deliverySale()
    {
        return $this->belongsTo(DeliverySale::class);
    }
}
