<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact',
        'basic_salary',  // This now represents DAILY WAGE (fixed rate per day)
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
    ];

    /**
     * Get the salaries for this employee
     */
    public function salaries()
    {
        return $this->hasMany(Salary::class, 'employee_id');
    }

    /**
     * Get current month salary record
     */
    public function currentMonthSalary()
    {
        return $this->hasOne(Salary::class, 'employee_id')
            ->whereMonth('salary_month', now()->month)
            ->whereYear('salary_month', now()->year);
    }

    /**
     * Get cash advances (draws) for this employee
     */
    public function cashAdvances()
    {
        return $this->hasMany(CashAdvance::class, 'employee_id');
    }

    /**
     * Get payment records for this employee
     */
    public function payments()
    {
        return $this->hasMany(EmployeePayment::class, 'employee_id');
    }

    /**
     * Get total salary for a specific month
     * Since we're using daily fixed salary, we calculate based on:
     * Daily rate × Number of work days (tracked via cash advances/expenses)
     * 
     * For daily salary system, the "salary" is accumulated based on:
     * - Each day the employee works adds their daily rate to their due amount
     * - Work days are tracked indirectly through expense deductions
     * - If no attendance system, we calculate based on cash advances (draws)
     */
    public function getMonthlySalary($month, $year)
    {
        // Since we're using daily fixed salary, the basic_salary is the rate per day
        // Without attendance system, we assume employee worked all days that they have
        // cash advances/draws recorded OR we calculate based on previous patterns
        
        // For simplicity and to maintain compatibility with existing system:
        // The monthly salary is calculated as:
        // Daily rate × Number of days the employee had any activity (advances/expenses)
        
        $dailyRate = (float)$this->basic_salary;
        
        if ($dailyRate <= 0) {
            return 0;
        }
        
        // Get unique work days from cash advances (each cash advance represents a work day or expense)
        $workDays = $this->cashAdvances()
            ->where('month', $month)
            ->where('year', $year)
            ->distinct('date')
            ->count('date');
        
        // If no cash advances, return 0 (no work days recorded)
        if ($workDays == 0) {
            return 0;
        }
        
        // Calculate total salary based on daily rate × work days
        return $dailyRate * $workDays;
    }

    /**
     * Get total work days count for a month (based on cash advances/expenses)
     */
    public function getWorkDaysCount($month, $year)
    {
        return (int)$this->cashAdvances()
            ->where('month', $month)
            ->where('year', $year)
            ->distinct('date')
            ->count('date');
    }

    /**
     * Get previous overdue amount (balance carried forward from previous months)
     */
    public function getPreviousOverdue($month, $year)
    {
        $targetDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $startDate = $this->created_at->copy()->startOfMonth();
        
        $totalSalary = 0;
        $tempDate = $startDate->copy();
        
        // Calculate total salary for all months before target month
        while ($tempDate->lt($targetDate)) {
            $totalSalary += $this->getMonthlySalary($tempDate->month, $tempDate->year);
            $tempDate->addMonth();
        }

        // Get total advances/draws from all months before target month
        $totalAdvances = $this->cashAdvances()
            ->where(function($query) use ($targetDate) {
                $query->where('year', '<', $targetDate->year)
                      ->orWhere(function($q) use ($targetDate) {
                          $q->where('year', $targetDate->year)
                            ->where('month', '<', $targetDate->month);
                      });
            })
            ->sum('amount');

        // Get total payments made in all months before target month
        $totalPaid = $this->payments()
            ->where(function($query) use ($targetDate) {
                $query->where('year', '<', $targetDate->year)
                      ->orWhere(function($q) use ($targetDate) {
                          $q->where('year', $targetDate->year)
                            ->where('month', '<', $targetDate->month);
                      });
            })
            ->sum('amount_paid');

        return (float)($totalSalary - $totalAdvances - $totalPaid);
    }

    /**
     * Get total cash taken (advances/draws) for current month
     */
    public function getCurrentMonthCashTaken($month, $year)
    {
        return (float)$this->cashAdvances()
            ->where('month', $month)
            ->where('year', $year)
            ->sum('amount');
    }

    /**
     * Calculate net pay due for the employee for a specific month
     * Net Pay = (Monthly Salary based on daily rate) + Previous Overdue - Current Draws - Already Paid
     */
    public function calculateNetPay($month, $year)
    {
        $prevDue = $this->getPreviousOverdue($month, $year);
        $currentSalary = $this->getMonthlySalary($month, $year);
        $currentDraws = $this->getCurrentMonthCashTaken($month, $year);

        $totalNetPay = $currentSalary + $prevDue - $currentDraws;

        $alreadyPaidInMonth = (float)$this->payments()
            ->where('month', $month)
            ->where('year', $year)
            ->sum('amount_paid');

        return max(0, $totalNetPay - $alreadyPaidInMonth);
    }

    /**
     * Record a payment (full or partial) for the employee
     * 
     * @param float $amountPaid Amount being paid
     * @param string $date Payment date
     * @param int $month Month of payment
     * @param int $year Year of payment
     * @param string|null $note Optional payment note
     * @return EmployeePayment
     */
    public function recordPayOff($amountPaid, $date, $month, $year, $note = null)
    {
        // Get current net pay calculation
        $netPayDue = $this->calculateNetPay($month, $year);
        
        // Get existing payments for this month
        $existingPayment = $this->payments()
            ->where('month', $month)
            ->where('year', $year)
            ->first();
        
        $existingPaid = $existingPayment ? (float)$existingPayment->amount_paid : 0;
        $newTotalPaid = $existingPaid + $amountPaid;
        
        // Calculate remaining balance
        $remainingBalance = $netPayDue - $newTotalPaid;
        
        // Determine status
        $status = 'partial';
        if ($remainingBalance <= 0) {
            $status = 'completed';
        } elseif ($newTotalPaid > 0) {
            $status = 'partial';
        }
        
        // Calculate overdue carried forward (negative remaining becomes positive)
        $overdueCarried = $remainingBalance < 0 ? abs($remainingBalance) : 0;
        
        // Update or create payment record
        return $this->payments()->updateOrCreate(
            [
                'employee_id' => $this->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'amount_paid' => $newTotalPaid,
                'net_pay_due' => $netPayDue,
                'overdue_carried_forward' => $overdueCarried,
                'date' => $date,
                'status' => $status,
            ]
        );
    }

    /**
     * Check if employee is fully paid for a specific month
     */
    public function isFullyPaid($month, $year)
    {
        $netPayDue = $this->calculateNetPay($month, $year);
        $totalPaid = (float)$this->payments()
            ->where('month', $month)
            ->where('year', $year)
            ->sum('amount_paid');
        
        return $totalPaid >= $netPayDue && $netPayDue > 0;
    }

    /**
     * Get remaining balance for current month
     */
    public function getRemainingBalance($month, $year)
    {
        $netPayDue = $this->calculateNetPay($month, $year);
        $totalPaid = (float)$this->payments()
            ->where('month', $month)
            ->where('year', $year)
            ->sum('amount_paid');
        
        return max(0, $netPayDue - $totalPaid);
    }

    /**
     * Get all payment history with proper formatting
     */
    public function getPaymentHistory($limit = 12)
    {
        return $this->payments()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($payment) {
                $payment->formatted_date = Carbon::createFromDate($payment->year, $payment->month, 1)->format('F Y');
                return $payment;
            });
    }

    /**
     * Get summary for current month
     */
    public function getCurrentMonthSummary()
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;
        
        return [
            'daily_rate' => (float)$this->basic_salary,
            'work_days' => $this->getWorkDaysCount($month, $year),
            'total_salary' => $this->getMonthlySalary($month, $year),
            'total_draws' => $this->getCurrentMonthCashTaken($month, $year),
            'previous_overdue' => $this->getPreviousOverdue($month, $year),
            'net_pay_due' => $this->calculateNetPay($month, $year),
            'total_paid' => (float)$this->payments()
                ->where('month', $month)
                ->where('year', $year)
                ->sum('amount_paid'),
            'remaining_balance' => $this->getRemainingBalance($month, $year),
            'is_fully_paid' => $this->isFullyPaid($month, $year),
        ];
    }
}