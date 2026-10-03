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
        Schema::table('delivery_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_sales', 'courier_status_detail')) {
                $table->string('courier_status_detail')->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('delivery_sales', 'courier_last_synced_at')) {
                $table->timestamp('courier_last_synced_at')->nullable()->after('courier_status_detail');
            }
            if (!Schema::hasColumn('delivery_sales', 'courier_tracking_history')) {
                $table->json('courier_tracking_history')->nullable()->after('courier_last_synced_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_sales', function (Blueprint $table) {
            $table->dropColumn([
                'courier_status_detail',
                'courier_last_synced_at',
                'courier_tracking_history',
            ]);
        });
    }
};
