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
        Schema::create('gold_plating_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gold_plating_order_id')->constrained('gold_plating_orders')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_type', 50)->default('advance'); // advance, balance, full
            $table->string('payment_method', 50)->default('cash'); // cash, online, bank_transfer
            $table->date('payment_date');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_plating_payments');
    }
};
