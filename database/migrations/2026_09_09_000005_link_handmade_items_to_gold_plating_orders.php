<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gold_plating_orders', function (Blueprint $table) {
            $table->foreignId('handmade_item_id')->nullable()->after('worker_id')->constrained('handmade_items')->nullOnDelete();
            $table->enum('order_category', ['customer', 'handmade'])->default('customer')->after('service_type');
        });
    }

    public function down(): void
    {
        Schema::table('gold_plating_orders', function (Blueprint $table) {
            $table->dropForeign(['handmade_item_id']);
            $table->dropColumn(['handmade_item_id', 'order_category']);
        });
    }
};
