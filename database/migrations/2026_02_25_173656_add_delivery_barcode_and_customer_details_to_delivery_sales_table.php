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
            $table->string('delivery_barcode')->unique()->nullable()->after('status');
            $table->text('customer_details')->nullable()->after('delivery_barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_sales', function (Blueprint $table) {
            $table->dropColumn(['delivery_barcode', 'customer_details']);
        });
    }
};
