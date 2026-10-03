<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('db_database')->nullable()->comment('Database name for auto-sync, e.g. sewanagala');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pre-seed with Sewanagala Branch
        DB::table('destination_shops')->insert([
            'name' => 'Sewanagala Branch',
            'code' => 'SWN',
            'phone' => null,
            'address' => 'Sewanagala',
            'db_database' => 'sewanagala',
            'is_active' => true,
            'notes' => 'Auto-sync enabled — stock transfers directly update sewanagala database.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_shops');
    }
};
