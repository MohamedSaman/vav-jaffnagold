<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Performance indexes for iclock_transaction (ZKTeco Biometric punch logs)
        if (Schema::hasTable('iclock_transaction')) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM iclock_transaction"))->pluck('Key_name')->unique()->toArray();

            Schema::table('iclock_transaction', function (Blueprint $table) use ($existingIndexes) {
                if (! in_array('idx_iclock_punch_time', $existingIndexes)) {
                    $table->index('punch_time', 'idx_iclock_punch_time');
                }
                if (! in_array('idx_iclock_emp_punch', $existingIndexes)) {
                    $table->index(['emp_code', 'punch_time'], 'idx_iclock_emp_punch');
                }
                if (! in_array('idx_iclock_emp_code', $existingIndexes)) {
                    $table->index('emp_code', 'idx_iclock_emp_code');
                }
            });
        }

        // 2. Performance indexes for hr_salary_payments
        if (Schema::hasTable('hr_salary_payments')) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM hr_salary_payments"))->pluck('Key_name')->unique()->toArray();

            Schema::table('hr_salary_payments', function (Blueprint $table) use ($existingIndexes) {
                if (! in_array('idx_hr_payments_date', $existingIndexes)) {
                    $table->index('payment_date', 'idx_hr_payments_date');
                }
                if (! in_array('idx_hr_payments_emp_date', $existingIndexes)) {
                    $table->index(['emp_code', 'payment_date'], 'idx_hr_payments_emp_date');
                }
            });
        }

        // 3. Performance indexes for personnel_employee
        if (Schema::hasTable('personnel_employee')) {
            $existingIndexes = collect(DB::select("SHOW INDEX FROM personnel_employee"))->pluck('Key_name')->unique()->toArray();

            Schema::table('personnel_employee', function (Blueprint $table) use ($existingIndexes) {
                if (! in_array('idx_personnel_emp_dept', $existingIndexes)) {
                    $table->index('department_id', 'idx_personnel_emp_dept');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('iclock_transaction')) {
            Schema::table('iclock_transaction', function (Blueprint $table) {
                $table->dropIndex('idx_iclock_punch_time');
                $table->dropIndex('idx_iclock_emp_punch');
                $table->dropIndex('idx_iclock_emp_code');
            });
        }

        if (Schema::hasTable('hr_salary_payments')) {
            Schema::table('hr_salary_payments', function (Blueprint $table) {
                $table->dropIndex('idx_hr_payments_date');
                $table->dropIndex('idx_hr_payments_emp_date');
            });
        }

        if (Schema::hasTable('personnel_employee')) {
            Schema::table('personnel_employee', function (Blueprint $table) {
                $table->dropIndex('idx_personnel_emp_dept');
            });
        }
    }
};
