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
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('saving_method', 100)->default('seedu')->after('amount');
            $table->string('source_of_funds', 50)->default('cash_sale')->after('saving_method');
            $table->string('reference_no', 150)->nullable()->after('source_of_funds');
            $table->foreignId('user_id')->nullable()->after('reference_no')->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('completed')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'saving_method',
                'source_of_funds',
                'reference_no',
                'user_id',
                'status',
            ]);
        });
    }
};
