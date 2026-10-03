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
        if (!Schema::hasColumn('product_suppliers', 'cost_multiplier')) {
            Schema::table('product_suppliers', function (Blueprint $table) {
                $table->decimal('cost_multiplier', 8, 2)->default(1.00)->after('overpayment');
                $table->decimal('sell_multiplier', 8, 2)->default(0.00)->after('cost_multiplier');
            });
        }

        if (!Schema::hasColumn('purchase_orders', 'total_selling_amount')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->decimal('cost_multiplier', 8, 2)->nullable()->after('total_system_amount');
                $table->decimal('sell_multiplier', 8, 2)->nullable()->after('cost_multiplier');
                $table->decimal('total_selling_amount', 14, 2)->default(0)->after('sell_multiplier');
            });
        }

        if (!Schema::hasColumn('purchase_order_items', 'selling_price')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->decimal('selling_price', 12, 2)->nullable()->after('total_system_cost');
                $table->decimal('sell_multiplier', 8, 2)->default(0.00)->after('selling_price');
                $table->decimal('total_selling_price', 14, 2)->nullable()->after('sell_multiplier');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_suppliers', function (Blueprint $table) {
            $table->dropColumn(['cost_multiplier', 'sell_multiplier']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['cost_multiplier', 'sell_multiplier', 'total_selling_amount']);
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['selling_price', 'sell_multiplier', 'total_selling_price']);
        });
    }
};
