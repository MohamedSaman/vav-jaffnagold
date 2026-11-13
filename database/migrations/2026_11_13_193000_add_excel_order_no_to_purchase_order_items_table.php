<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_order_items') && !Schema::hasColumn('purchase_order_items', 'excel_order_no')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->integer('excel_order_no')->nullable()->after('sort_order')->index();
            });

            // Backfill existing rows with sort_order if available
            DB::table('purchase_order_items')
                ->whereNull('excel_order_no')
                ->update(['excel_order_no' => DB::raw('sort_order')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_order_items') && Schema::hasColumn('purchase_order_items', 'excel_order_no')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->dropColumn('excel_order_no');
            });
        }
    }
};
