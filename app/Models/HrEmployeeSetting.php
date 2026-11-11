<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmployeeSetting extends Model
{
    protected $table = 'hr_employee_settings';

    protected $fillable = [
        'zk_employee_id',
        'emp_code',
        'salary_type',
        'daily_working_hours',
        'basic_salary',
        'daily_rate',
        'hourly_rate',
        'overtime_rate',
        'allowance',
        'opening_balance',
        'notes',
    ];

    protected $casts = [
        'daily_working_hours' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'allowance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(ZkEmployee::class, 'emp_code', 'emp_code');
    }
}
