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
        // Add 'online' and 'multiple' to payment_method enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'cheque', 'credit', 'bank_transfer', 'online', 'multiple') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values (be careful if 'online' or 'multiple' data exists)
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM('cash', 'cheque', 'credit', 'bank_transfer') DEFAULT 'cash'");
    }
};
