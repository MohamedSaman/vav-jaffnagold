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
        if (!Schema::hasColumn('purchase_orders', 'total_supplier_amount')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->decimal('total_supplier_amount', 14, 2)->default(0)->after('status');
                $table->decimal('total_system_amount', 14, 2)->default(0)->after('total_supplier_amount');
            });
        }

        if (!Schema::hasColumn('purchase_order_items', 'supplier_cost')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->decimal('supplier_cost', 12, 2)->nullable()->after('received_quantity');
                $table->decimal('system_cost', 12, 2)->nullable()->after('supplier_cost');
                $table->decimal('cost_multiplier', 8, 2)->default(1.00)->after('system_cost');
                $table->decimal('total_supplier_cost', 14, 2)->nullable()->after('cost_multiplier');
                $table->decimal('total_system_cost', 14, 2)->nullable()->after('total_supplier_cost');
            });
        }

        if (!Schema::hasColumn('product_prices', 'system_cost')) {
            Schema::table('product_prices', function (Blueprint $table) {
                $table->decimal('system_cost', 12, 2)->nullable()->after('supplier_price');
                $table->decimal('cost_multiplier', 8, 2)->default(1.00)->after('system_cost');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['total_supplier_amount', 'total_system_amount']);
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['supplier_cost', 'system_cost', 'cost_multiplier', 'total_supplier_cost', 'total_system_cost']);
        });

        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropColumn(['system_cost', 'cost_multiplier']);
        });
    }
};
