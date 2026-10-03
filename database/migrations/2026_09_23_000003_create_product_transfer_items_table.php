<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_transfer_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_code')->nullable();
            $table->string('product_name');
            $table->string('barcode')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('product_transfer_id')->references('id')->on('product_transfers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_transfer_items');
    }
};
