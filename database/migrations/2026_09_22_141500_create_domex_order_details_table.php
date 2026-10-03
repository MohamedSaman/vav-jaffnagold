<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('domex_order_details')) {
            Schema::create('domex_order_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('cascade');
                $table->foreignId('delivery_sale_id')->nullable()->constrained('delivery_sales')->onDelete('cascade');
                $table->string('waybill_number')->index();
                
                // Statuses
                $table->string('current_status')->nullable();
                $table->string('normalized_status')->default('in_transit'); // pending, in_transit, delivered, returned, cancelled
                $table->boolean('is_final')->default(false); // True once delivered or cancelled so we stop polling
                
                // Waybill info
                $table->string('pickup_date')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('item_type')->nullable();
                $table->string('is_paid')->nullable();
                $table->string('pieces')->nullable();
                $table->string('weight')->nullable();
                
                // Sender info
                $table->string('sender_name')->nullable();
                $table->text('sender_address')->nullable();
                $table->string('sender_contact')->nullable();
                $table->string('pickup_city')->nullable();
                
                // Delivery info
                $table->string('delivered_by')->nullable();
                $table->string('delivered_to')->nullable();
                
                // Receiver info
                $table->string('receiver_name')->nullable();
                $table->string('receiver_phone')->nullable();
                $table->string('destination_city')->nullable();
                
                // Full tracking checkpoints
                $table->json('tracking_history')->nullable();
                $table->string('last_checkpoint_time')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domex_order_details');
    }
};
