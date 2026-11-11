<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrSalaryPayment extends Model
{
    protected $table = 'hr_salary_payments';

    protected $fillable = [
        'hr_salary_id',
        'emp_code',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function salary()
    {
        return $this->belongsTo(HrSalary::class, 'hr_salary_id');
    }

    public function employee()
    {
        return $this->belongsTo(ZkEmployee::class, 'emp_code', 'emp_code');
    }
}
