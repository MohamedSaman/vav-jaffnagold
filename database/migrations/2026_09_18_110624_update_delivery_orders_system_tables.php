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
        // 1. Update delivery_status on sales table to varchar(50) for flexibility (pending, packed, in_transit, delivered, returned, cancelled)
        DB::statement("ALTER TABLE `sales` MODIFY `delivery_status` VARCHAR(50) NOT NULL DEFAULT 'pending'");

        // 2. Add tracking_number, returned_at, return_reason to delivery_sales table if not exists
        Schema::table('delivery_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_sales', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('delivery_barcode');
            }
            if (!Schema::hasColumn('delivery_sales', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('delivery_sales', 'return_reason')) {
                $table->text('return_reason')->nullable()->after('returned_at');
            }
        });

        // 3. Update delivery_method and status on delivery_sales table to varchar(50) to allow flexible couriers and full status lifecycle
        DB::statement("ALTER TABLE `delivery_sales` MODIFY `delivery_method` VARCHAR(50) NOT NULL DEFAULT 'Post'");
        DB::statement("ALTER TABLE `delivery_sales` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'Processing'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_sales', function (Blueprint $table) {
            $table->dropColumn(['tracking_number', 'returned_at', 'return_reason']);
        });
    }
};
