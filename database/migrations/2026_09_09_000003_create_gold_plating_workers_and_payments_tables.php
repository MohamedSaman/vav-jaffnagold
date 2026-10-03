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
        // 1. Workshop workers table (e.g. Ramesh, Sanker)
        Schema::create('gold_plating_workers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->decimal('opening_payable_balance', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Payments given to workshop workers (payouts / advances)
        Schema::create('gold_plating_worker_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('gold_plating_workers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50)->default('cash'); // cash, bank_transfer, online
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Add worker_id to gold_plating_orders
        Schema::table('gold_plating_orders', function (Blueprint $table) {
            $table->foreignId('worker_id')->nullable()->after('user_id')->constrained('gold_plating_workers')->nullOnDelete();
        });

        // 4. Seed initial default workers: Ramesh and Sanker
        $now = now();
        DB::table('gold_plating_workers')->insert([
            [
                'name' => 'Ramesh',
                'phone' => null,
                'opening_payable_balance' => 0.00,
                'is_active' => true,
                'notes' => 'Workshop Gold Plating & Polishing Craftsman',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sanker',
                'phone' => null,
                'opening_payable_balance' => 0.00,
                'is_active' => true,
                'notes' => 'Workshop Jewelry Plating & Cleaning Craftsman',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gold_plating_orders', function (Blueprint $table) {
            $table->dropForeign(['worker_id']);
            $table->dropColumn('worker_id');
        });

        Schema::dropIfExists('gold_plating_worker_payments');
        Schema::dropIfExists('gold_plating_workers');
    }
};
