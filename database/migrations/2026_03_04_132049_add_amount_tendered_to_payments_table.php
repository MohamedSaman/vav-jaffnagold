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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'amount_tendered')) {
                $table->decimal('amount_tendered', 10, 2)->nullable()->after('amount')
                    ->comment('Actual cash customer handed over; may exceed sale amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'amount_tendered')) {
                $table->dropColumn('amount_tendered');
            }
        });
    }
};
