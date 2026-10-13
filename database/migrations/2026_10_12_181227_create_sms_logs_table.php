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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient', 50)->index();
            $table->text('message');
            $table->unsignedInteger('sms_count')->default(1);
            $table->decimal('cost_per_sms', 10, 4)->default(0.8500);
            $table->decimal('total_cost', 10, 4)->default(0.8500);
            $table->boolean('is_double_charge')->default(false);
            $table->string('status', 30)->default('sent')->index();
            $table->string('sender_id', 50)->nullable();
            $table->text('response')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
