<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->string('source_shop')->default('Jaffna Gold Main Shop');
            $table->unsignedBigInteger('destination_shop_id')->nullable();
            $table->string('destination_shop_name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('transfer_date');
            $table->integer('total_items')->default(0);
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->string('status')->default('completed'); // completed, in_transit, cancelled
            $table->boolean('is_synced')->default(false);
            $table->text('sync_log')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('destination_shop_id')->references('id')->on('destination_shops')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_transfers');
    }
};
