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
        Schema::create('gold_plating_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 50)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_address')->nullable();
            $table->string('service_type', 50)->default('gold_plating'); // gold_plating, cleaning, polishing, repair, other
            $table->text('item_description');
            $table->decimal('item_weight', 8, 2)->nullable();
            $table->boolean('is_charge_tbd')->default(false);
            $table->decimal('our_cost', 10, 2)->nullable()->default(0);
            $table->decimal('customer_charge', 10, 2)->nullable()->default(0);
            $table->decimal('advance_amount', 10, 2)->default(0);
            $table->string('advance_payment_method', 50)->default('cash'); // cash, online, bank_transfer
            $table->decimal('balance_amount', 10, 2)->nullable()->default(0);
            $table->string('status', 50)->default('received'); // received, in_progress, ready, delivered, cancelled
            $table->date('received_date');
            $table->date('expected_delivery_date')->nullable();
            $table->timestamp('actual_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_plating_orders');
    }
};
