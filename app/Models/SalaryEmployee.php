<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'daily_salary',
        'opening_balance',
        'is_active',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'salary_employee_id');
    }

    public function staffExpenses()
    {
        return $this->hasMany(StaffExpense::class, 'salary_employee_id');
    }

    /**
     * Calculate total earned salary based on attendance (unique days in expenses/staff_expenses)
     */
    public function calculateTotalEarned()
    {
        // Fetch dates and ensure they are strings for reliable uniqueness check
        $expenseDates = $this->expenses()->pluck('date')->map(function($date) {
            return $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : (string)$date;
        });

        $staffDates = $this->staffExpenses()->pluck('expense_date')->map(function($date) {
            return $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : (string)$date;
        });
        
        $allDates = $expenseDates->merge($staffDates)->unique();

        return $allDates->count() * $this->daily_salary;
    }

    /**
     * Calculate total collected amount
     */
    public function calculateTotalCollected()
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Calculate current due balance
     */
    public function calculateBalance()
    {
        return $this->opening_balance + $this->calculateTotalEarned() - $this->calculateTotalCollected();
    }
}
