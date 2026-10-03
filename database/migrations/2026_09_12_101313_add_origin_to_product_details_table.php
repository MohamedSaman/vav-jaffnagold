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
        if (!Schema::hasColumn('product_details', 'origin')) {
            Schema::table('product_details', function (Blueprint $table) {
                $table->string('origin', 20)->default('indian')->after('model')->index();
            });
        }

        // Backfill existing products
        DB::table('product_details')
            ->where('model', 'Handmade Jewelry')
            ->orWhere('code', 'LIKE', 'SL-%')
            ->orWhere('barcode', 'LIKE', 'SL-%')
            ->orWhere('description', 'LIKE', '%locally crafted%')
            ->update(['origin' => 'local']);

        if (Schema::hasTable('handmade_items') && Schema::hasColumn('handmade_items', 'product_detail_id')) {
            $handmadeProductIds = DB::table('handmade_items')
                ->whereNotNull('product_detail_id')
                ->pluck('product_detail_id')
                ->toArray();

            if (!empty($handmadeProductIds)) {
                DB::table('product_details')
                    ->whereIn('id', $handmadeProductIds)
                    ->update(['origin' => 'local']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('product_details', 'origin')) {
            Schema::table('product_details', function (Blueprint $table) {
                $table->dropColumn('origin');
            });
        }
    }
};
