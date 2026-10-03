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
        if (!Schema::hasColumn('product_prices', 'sell_multiplier')) {
            Schema::table('product_prices', function (Blueprint $table) {
                $table->decimal('sell_multiplier', 8, 2)->default(0.00)->after('cost_multiplier');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('product_prices', 'sell_multiplier')) {
            Schema::table('product_prices', function (Blueprint $table) {
                $table->dropColumn('sell_multiplier');
            });
        }
    }
};
