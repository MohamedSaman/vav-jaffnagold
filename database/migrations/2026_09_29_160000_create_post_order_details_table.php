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
        if (! Schema::hasTable('post_order_details')) {
            Schema::create('post_order_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('cascade');
                $table->foreignId('delivery_sale_id')->nullable()->constrained('delivery_sales')->onDelete('cascade');
                $table->string('barcode')->index();

                // Statuses
                $table->string('status')->nullable(); // Settled, Delivered, In Transit, Pending, Returned
                $table->string('normalized_status')->default('in_transit'); // pending, in_transit, delivered, returned, cancelled
                $table->boolean('is_final')->default(false); // True once settled/delivered or returned so we stop polling

                // Postal Details
                $table->string('delivery_po')->nullable(); // Delivery Post Office
                $table->string('acceptance_po')->nullable(); // Acceptance Post Office
                $table->string('received_date')->nullable(); // Date/time delivered or received
                $table->string('cod_amount')->nullable(); // COD amount if provided

                // Checkpoint tracking and raw data
                $table->json('tracking_history')->nullable();
                $table->json('raw_data')->nullable();
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
        Schema::dropIfExists('post_order_details');
    }
};
