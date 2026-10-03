<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `sales` MODIFY COLUMN `status` ENUM('pending', 'confirm', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `sales` MODIFY COLUMN `status` ENUM('pending', 'confirm', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
