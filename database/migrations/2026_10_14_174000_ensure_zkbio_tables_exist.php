<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personnel_department')) {
            Schema::create('personnel_department', function (Blueprint $table) {
                $table->id();
                $table->string('dept_code', 50)->nullable();
                $table->string('dept_name', 100);
                $table->integer('parent_dept_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('personnel_employee')) {
            Schema::create('personnel_employee', function (Blueprint $table) {
                $table->id();
                $table->string('emp_code', 20)->unique();
                $table->string('first_name', 50)->nullable();
                $table->string('last_name', 50)->nullable();
                $table->string('nickname', 50)->nullable();
                $table->string('mobile', 50)->nullable();
                $table->string('email', 100)->nullable();
                $table->integer('department_id')->nullable();
                $table->date('hire_date')->nullable();
                $table->date('birthday')->nullable();
                $table->string('gender', 10)->nullable();
                $table->string('address', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('enable_payroll')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('iclock_transaction')) {
            Schema::create('iclock_transaction', function (Blueprint $table) {
                $table->id();
                $table->string('emp_code', 20);
                $table->dateTime('punch_time');
                $table->string('punch_state', 5)->default('0');
                $table->integer('verify_type')->default(1);
                $table->string('work_code', 20)->nullable();
                $table->string('terminal_sn', 50)->nullable();
                $table->string('terminal_alias', 50)->nullable();
                $table->string('area_alias', 100)->nullable();
                $table->integer('emp_id')->nullable();
                $table->integer('terminal_id')->nullable();
                $table->dateTime('upload_time')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('personnel_area')) {
            Schema::create('personnel_area', function (Blueprint $table) {
                $table->id();
                $table->string('area_code', 50)->nullable();
                $table->string('area_name', 100);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('personnel_employee_area')) {
            Schema::create('personnel_employee_area', function (Blueprint $table) {
                $table->id();
                $table->integer('employee_id');
                $table->integer('area_id');
            });
        }
    }

    public function down(): void
    {
    }
};
