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
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'salary_employee_id')) {
                $table->foreignId('salary_employee_id')->nullable()->constrained('salary_employees')->onDelete('set null');
            }
        });

        Schema::table('staff_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_expenses', 'salary_employee_id')) {
                $table->foreignId('salary_employee_id')->nullable()->constrained('salary_employees')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'salary_employee_id')) {
                $table->dropForeign(['salary_employee_id']);
                $table->dropColumn('salary_employee_id');
            }
        });

        Schema::table('staff_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('staff_expenses', 'salary_employee_id')) {
                $table->dropForeign(['salary_employee_id']);
                $table->dropColumn('salary_employee_id');
            }
        });
    }
};
