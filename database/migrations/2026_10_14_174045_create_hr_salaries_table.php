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
        // 1. Employee salary & hours settings (linked to ZKBio personnel_employee)
        if (! Schema::hasTable('hr_employee_settings')) {
            Schema::create('hr_employee_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('zk_employee_id')->index();
                $table->string('emp_code', 20)->unique();
                $table->string('salary_type', 20)->default('monthly'); // 'monthly', 'daily', 'hourly'
                $table->decimal('daily_working_hours', 4, 2)->default(8.00); // Standard working hours per day
                $table->decimal('basic_salary', 12, 2)->default(0.00); // Monthly basic salary
                $table->decimal('daily_rate', 10, 2)->default(0.00);   // Daily rate
                $table->decimal('hourly_rate', 10, 2)->default(0.00);  // Hourly rate
                $table->decimal('overtime_rate', 10, 2)->default(0.00); // Overtime per hour rate
                $table->decimal('allowance', 10, 2)->default(0.00);    // Fixed monthly allowance
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Monthly salary records calculated from biometric punches + employee settings
        if (! Schema::hasTable('hr_salaries')) {
            Schema::create('hr_salaries', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('zk_employee_id');
                $table->string('emp_code', 20);
                $table->date('salary_month'); // e.g. 2026-10-01
                $table->string('salary_type', 20)->default('monthly');
                $table->decimal('daily_working_hours', 4, 2)->default(8.00);
                $table->integer('total_work_days')->default(0);
                $table->decimal('total_hours_worked', 8, 2)->default(0);
                $table->decimal('expected_hours', 8, 2)->default(0);
                $table->decimal('overtime_hours', 8, 2)->default(0);
                $table->decimal('daily_rate', 10, 2)->default(0);
                $table->decimal('hourly_rate', 10, 2)->default(0);
                $table->decimal('basic_salary', 10, 2)->default(0);
                $table->decimal('overtime_pay', 10, 2)->default(0);
                $table->decimal('bonus', 10, 2)->default(0);
                $table->decimal('allowance', 10, 2)->default(0);
                $table->decimal('deductions', 10, 2)->default(0);
                $table->decimal('advance_taken', 10, 2)->default(0);
                $table->decimal('net_salary', 10, 2)->default(0); // Total current month salary
                $table->decimal('paid_amount', 10, 2)->default(0); // Paid amount
                $table->decimal('balance_amount', 10, 2)->default(0); // Balance salary
                $table->string('payment_status', 20)->default('unpaid'); // unpaid, partial, paid
                $table->date('paid_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['zk_employee_id', 'salary_month']);
                $table->index('salary_month');
                $table->index('payment_status');
                $table->index('emp_code');
            });
        }

        // 3. Salary payment transactions history
        if (! Schema::hasTable('hr_salary_payments')) {
            Schema::create('hr_salary_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('hr_salary_id')->index();
                $table->string('emp_code', 20)->index();
                $table->date('payment_date');
                $table->decimal('amount', 10, 2);
                $table->string('payment_method', 50)->default('Cash'); // Cash, Bank Transfer, Cheque, Online
                $table->string('reference_no', 100)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_salary_payments');
        Schema::dropIfExists('hr_salaries');
        Schema::dropIfExists('hr_employee_settings');
    }
};
