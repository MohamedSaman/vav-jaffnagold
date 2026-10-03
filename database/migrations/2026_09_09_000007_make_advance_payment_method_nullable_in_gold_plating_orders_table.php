<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE `gold_plating_orders` MODIFY COLUMN `advance_payment_method` VARCHAR(50) NULL DEFAULT 'cash'");
        } catch (\Throwable $e) {
            Schema::table('gold_plating_orders', function (Blueprint $table) {
                $table->string('advance_payment_method', 50)->nullable()->default('cash')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `gold_plating_orders` MODIFY COLUMN `advance_payment_method` VARCHAR(50) NOT NULL DEFAULT 'cash'");
        } catch (\Throwable $e) {
            Schema::table('gold_plating_orders', function (Blueprint $table) {
                $table->string('advance_payment_method', 50)->nullable(false)->default('cash')->change();
            });
        }
    }
};
