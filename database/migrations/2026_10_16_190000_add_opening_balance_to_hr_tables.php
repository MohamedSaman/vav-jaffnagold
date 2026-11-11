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
        if (Schema::hasTable('hr_employee_settings') && ! Schema::hasColumn('hr_employee_settings', 'opening_balance')) {
            Schema::table('hr_employee_settings', function (Blueprint $table) {
                $table->decimal('opening_balance', 12, 2)->default(0.00)->after('allowance');
            });
        }

        if (Schema::hasTable('hr_salaries') && ! Schema::hasColumn('hr_salaries', 'opening_balance')) {
            Schema::table('hr_salaries', function (Blueprint $table) {
                $table->decimal('opening_balance', 12, 2)->default(0.00)->after('basic_salary');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('hr_employee_settings') && Schema::hasColumn('hr_employee_settings', 'opening_balance')) {
            Schema::table('hr_employee_settings', function (Blueprint $table) {
                $table->dropColumn('opening_balance');
            });
        }

        if (Schema::hasTable('hr_salaries') && Schema::hasColumn('hr_salaries', 'opening_balance')) {
            Schema::table('hr_salaries', function (Blueprint $table) {
                $table->dropColumn('opening_balance');
            });
        }
    }
};
