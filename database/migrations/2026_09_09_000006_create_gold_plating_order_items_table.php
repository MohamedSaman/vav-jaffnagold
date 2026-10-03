<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gold_plating_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gold_plating_order_id')->constrained('gold_plating_orders')->onDelete('cascade');
            $table->string('item_name');
            $table->decimal('fee', 10, 2)->default(0.00);
            $table->decimal('cost', 10, 2)->nullable()->default(0.00);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gold_plating_order_items');
    }
};
