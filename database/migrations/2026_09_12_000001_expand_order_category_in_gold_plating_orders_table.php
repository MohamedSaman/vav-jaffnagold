<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify order_category from enum('customer', 'handmade') to VARCHAR(50)
        // to safely support 'customer', 'handmade', 'shop', etc.
        DB::statement("ALTER TABLE `gold_plating_orders` MODIFY COLUMN `order_category` VARCHAR(50) NOT NULL DEFAULT 'customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `gold_plating_orders` MODIFY COLUMN `order_category` ENUM('customer', 'handmade') NOT NULL DEFAULT 'customer'");
    }
};
