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
        Schema::table('product_details', function (Blueprint $table) {
            $table->boolean('in_slider')->default(false)->after('website_live');
            $table->decimal('discount_percent', 5, 2)->default(0)->after('in_slider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->dropColumn(['in_slider', 'discount_percent']);
        });
    }
};
