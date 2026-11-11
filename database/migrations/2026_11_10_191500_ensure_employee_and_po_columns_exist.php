<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cash_advances')) {
            Schema::table('cash_advances', function (Blueprint $table) {
                if (! Schema::hasColumn('cash_advances', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->nullable()->index();
                }
                if (! Schema::hasColumn('cash_advances', 'amount')) {
                    $table->decimal('amount', 12, 2)->default(0);
                }
                if (! Schema::hasColumn('cash_advances', 'date')) {
                    $table->date('date')->nullable();
                }
                if (! Schema::hasColumn('cash_advances', 'note')) {
                    $table->text('note')->nullable();
                }
                if (! Schema::hasColumn('cash_advances', 'month')) {
                    $table->integer('month')->nullable();
                }
                if (! Schema::hasColumn('cash_advances', 'year')) {
                    $table->integer('year')->nullable();
                }
            });
        }

        if (Schema::hasTable('employee_payments')) {
            Schema::table('employee_payments', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_payments', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->nullable()->index();
                }
                if (! Schema::hasColumn('employee_payments', 'amount_paid')) {
                    $table->decimal('amount_paid', 12, 2)->default(0);
                }
                if (! Schema::hasColumn('employee_payments', 'net_pay_due')) {
                    $table->decimal('net_pay_due', 12, 2)->default(0);
                }
                if (! Schema::hasColumn('employee_payments', 'overdue_carried_forward')) {
                    $table->decimal('overdue_carried_forward', 12, 2)->default(0);
                }
                if (! Schema::hasColumn('employee_payments', 'date')) {
                    $table->date('date')->nullable();
                }
                if (! Schema::hasColumn('employee_payments', 'month')) {
                    $table->integer('month')->nullable();
                }
                if (! Schema::hasColumn('employee_payments', 'year')) {
                    $table->integer('year')->nullable();
                }
                if (! Schema::hasColumn('employee_payments', 'status')) {
                    $table->string('status', 50)->default('paid');
                }
            });
        }

        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('purchase_orders', 'discount_amount')) {
                    $table->decimal('discount_amount', 14, 2)->default(0);
                }
            });
        }
    }

    public function down(): void
    {
    }
};
