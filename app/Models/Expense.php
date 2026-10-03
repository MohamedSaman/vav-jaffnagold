<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'expense_type',
        'amount',
        'date',
        'status',
        'description',
        'user_id',
        'category_id',
        'salary_employee_id',
    ];

    public function salaryEmployee()
    {
        return $this->belongsTo(SalaryEmployee::class, 'salary_employee_id');
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    protected static function booted()
    {
        static::saved(function ($expense) {
            \App\Services\ExpenseDepositSyncService::syncExpenseToDeposit($expense);
        });

        static::deleted(function ($expense) {
            \App\Services\ExpenseDepositSyncService::deleteDepositForExpense($expense);
        });
    }
}
