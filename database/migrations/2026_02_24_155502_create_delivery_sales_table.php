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
        Schema::create('delivery_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->enum('delivery_method', ['Post', 'Domex'])->default('Post');
            $table->enum('payment_method', ['Cash on Delivery', 'Online Payment'])->default('Cash on Delivery');
            $table->enum('status', ['Processing', 'Packed', 'Delivered'])->default('Processing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_sales');
    }
};
