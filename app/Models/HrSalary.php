<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrSalary extends Model
{
    protected $table = 'hr_salaries';

    protected $fillable = [
        'zk_employee_id',
        'emp_code',
        'salary_month',
        'salary_type',
        'daily_working_hours',
        'total_work_days',
        'total_hours_worked',
        'expected_hours',
        'overtime_hours',
        'daily_rate',
        'hourly_rate',
        'basic_salary',
        'opening_balance',
        'overtime_pay',
        'bonus',
        'allowance',
        'deductions',
        'advance_taken',
        'net_salary',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'salary_month' => 'date',
        'paid_date' => 'date',
        'daily_working_hours' => 'decimal:2',
        'total_hours_worked' => 'decimal:2',
        'expected_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'allowance' => 'decimal:2',
        'deductions' => 'decimal:2',
        'advance_taken' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(ZkEmployee::class, 'emp_code', 'emp_code');
    }

    public function payments()
    {
        return $this->hasMany(HrSalaryPayment::class, 'hr_salary_id')->orderBy('payment_date', 'desc');
    }

    /**
     * Recalculate balance and payment status based on net_salary and paid_amount
     */
    public function recalculateBalance(): void
    {
        $this->paid_amount = round((float) $this->payments()->sum('amount'), 2);
        $this->balance_amount = max(0, round((float) $this->net_salary - (float) $this->paid_amount, 2));

        if ($this->paid_amount <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($this->balance_amount <= 0.001) {
            $this->payment_status = 'paid';
            if (! $this->paid_date) {
                $this->paid_date = now()->toDateString();
            }
        } else {
            $this->payment_status = 'partial';
        }

        $this->save();
    }
}
