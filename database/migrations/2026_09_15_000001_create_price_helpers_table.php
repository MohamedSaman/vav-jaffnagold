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
        Schema::create('price_helpers', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('category')->nullable();
            $table->decimal('min_price', 12, 2);
            $table->decimal('max_price', 12, 2);
            $table->string('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed initial default entries requested by user
        DB::table('price_helpers')->insert([
            [
                'item_name' => '5kan kodi',
                'category' => 'Kodi',
                'min_price' => 14500.00,
                'max_price' => 19500.00,
                'notes' => '5 Kan Traditional Kodi Range',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_name' => '6kan kodi',
                'category' => 'Kodi',
                'min_price' => 24500.00,
                'max_price' => 27500.00,
                'notes' => '6 Kan Traditional Kodi Range',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_helpers');
    }
};
