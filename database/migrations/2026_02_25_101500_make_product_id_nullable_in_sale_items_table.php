<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['product_id']);

            // Make product_id nullable for custom products
            $table->unsignedBigInteger('product_id')->nullable()->change();

            // Re-add foreign key but allow null
            $table->foreign('product_id')
                ->references('id')
                ->on('product_details')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->foreign('product_id')
                ->references('id')
                ->on('product_details')
                ->onDelete('cascade');
        });
    }
};
