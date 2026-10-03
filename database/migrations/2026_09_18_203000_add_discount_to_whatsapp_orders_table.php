<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_orders', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_orders', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_orders', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};
