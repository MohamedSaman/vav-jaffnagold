<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomexOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'domex_order_details';

    protected $fillable = [
        'sale_id',
        'delivery_sale_id',
        'waybill_number',
        'current_status',
        'normalized_status',
        'is_final',
        'pickup_date',
        'payment_method',
        'item_type',
        'is_paid',
        'pieces',
        'weight',
        'sender_name',
        'sender_address',
        'sender_contact',
        'pickup_city',
        'delivered_by',
        'delivered_to',
        'receiver_name',
        'receiver_phone',
        'destination_city',
        'tracking_history',
        'last_checkpoint_time',
        'last_synced_at',
    ];

    protected $casts = [
        'tracking_history' => 'array',
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
