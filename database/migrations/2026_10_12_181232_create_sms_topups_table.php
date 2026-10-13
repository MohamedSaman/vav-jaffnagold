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
        Schema::create('sms_topups', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed initial default top-up of 1000 and settings
        \Illuminate\Support\Facades\DB::table('sms_topups')->insert([
            'amount' => 1000.00,
            'notes' => 'Default initial balance top-up',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $defaultSettings = [
            'sms_rate' => '0.85',
            'sms_low_balance_threshold' => '50.00',
            'sms_topup_contact' => '0759037101',
        ];

        foreach ($defaultSettings as $key => $val) {
            \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $val,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_topups');
    }
};
