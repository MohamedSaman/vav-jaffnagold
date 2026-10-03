<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'amount_paid', 'net_pay_due',
        'overdue_carried_forward', 'date', 'month', 'year', 'status',
    ];
}
