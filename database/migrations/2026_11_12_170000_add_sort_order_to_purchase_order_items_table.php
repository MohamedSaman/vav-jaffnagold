<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_order_items') && !Schema::hasColumn('purchase_order_items', 'sort_order')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('order_id')->index();
            });

            // Backfill sort_order sequentially per order_id ordered by id asc
            $orderIds = DB::table('purchase_order_items')
                ->select('order_id')
                ->distinct()
                ->pluck('order_id');

            foreach ($orderIds as $orderId) {
                $itemIds = DB::table('purchase_order_items')
                    ->where('order_id', $orderId)
                    ->orderBy('id', 'asc')
                    ->pluck('id');

                foreach ($itemIds as $idx => $itemId) {
                    DB::table('purchase_order_items')
                        ->where('id', $itemId)
                        ->update(['sort_order' => $idx + 1]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_order_items') && Schema::hasColumn('purchase_order_items', 'sort_order')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
