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
        Schema::create('saving_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('icon')->default('bi-piggy-bank');
            $table->string('color')->default('primary');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default methods requested by user: Seedu and Undiyal, plus Bank Deposit
        \Illuminate\Support\Facades\DB::table('saving_methods')->insert([
            [
                'name' => 'Seedu',
                'code' => 'seedu',
                'description' => 'Daily / Monthly Chit Fund Saving (சீட்டு)',
                'icon' => 'bi-safe2',
                'color' => 'success',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Undiyal',
                'code' => 'undiyal',
                'description' => 'Daily Undiyal Cash Box Saving (உண்டியல்)',
                'icon' => 'bi-piggy-bank',
                'color' => 'warning',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bank Deposit',
                'code' => 'bank_deposit',
                'description' => 'Direct Cash Deposit to Bank Account',
                'icon' => 'bi-bank',
                'color' => 'info',
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
        Schema::dropIfExists('saving_methods');
    }
};
