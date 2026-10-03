<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Set all distributor_price values to null (field removed from UI).
     */
    public function up(): void
    {
        // Set all existing distributor_price values to null
        DB::table('product_prices')->update(['distributor_price' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore original values; set to 0 as safe default
        DB::table('product_prices')->whereNull('distributor_price')->update(['distributor_price' => 0]);
    }
};
