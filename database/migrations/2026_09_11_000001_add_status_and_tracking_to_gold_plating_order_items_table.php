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
        Schema::table('gold_plating_order_items', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->after('cost'); // pending, received
            $table->timestamp('received_at')->nullable()->after('status');
            $table->foreignId('received_by')->nullable()->after('received_at')->constrained('users')->nullOnDelete();
            $table->string('received_notes')->nullable()->after('received_by');
        });

        // Backfill existing order items: If parent order was already ready or delivered, mark items as received
        $readyOrders = DB::table('gold_plating_orders')
            ->whereIn('status', ['ready', 'delivered'])
            ->get(['id', 'status', 'actual_delivery_date', 'updated_at']);

        foreach ($readyOrders as $order) {
            DB::table('gold_plating_order_items')
                ->where('gold_plating_order_id', $order->id)
                ->update([
                    'status' => 'received',
                    'received_at' => $order->actual_delivery_date ?? $order->updated_at ?? now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gold_plating_order_items', function (Blueprint $table) {
            $table->dropForeign(['received_by']);
            $table->dropColumn(['status', 'received_at', 'received_by', 'received_notes']);
        });
    }
};
