<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->enum('barcode_printed', ['Yes', 'No'])->default('Yes')->after('barcode')
                ->comment('Yes = barcode was entered/scanned (already printed), No = barcode was auto-generated (needs printing)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->dropColumn('barcode_printed');
        });
    }
};
