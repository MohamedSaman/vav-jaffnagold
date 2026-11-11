<?php

namespace App\Livewire\Admin;

use App\Models\Expense;
use App\Models\HrSalary as HrSalaryModel;
use App\Models\POSSession;
use App\Services\HrService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('HR - Salary Management')]
class HrSalary extends Component
{
    public $selectedMonth;

    public $search = '';

    public $statusFilter = '';

    // Modals
    public $showDetailsModal = false;

    public $showPaymentModal = false;

    public $showOpeningBalanceModal = false;

    // Reset Modal States
    public $showResetModal = false;
    public $resetScope = 'all'; // 'all' or 'month'
    public $resetConfirmText = '';
    public $resetMonth = '';
    public $resetClearOpeningBalance = true;

    // Selected Salary & Employee
    public $selectedSalaryId = null;

    public $selectedSalary = null;

    public $selectedEmployee = null;

    // Payment Form
    public $payAmount = '';

    public $payDate = '';

    public $payMethod = 'Cash';

    public $payReference = '';

    public $payNotes = '';

    // Opening Balance Form
    public $obSalaryId = null;

    public $obEmpCode = null;

    public $obEmployee = null;

    public $obAmount = '';

    public $obNotes = '';

    // Modal Date-Wise Time Logs Picker
    public $modalLogDate = '';

    public $modalTimeLogs = [];

    public function mount(HrService $hrService)
    {
        $this->selectedMonth = Carbon::now()->format('Y-m');
        $this->payDate = Carbon::now()->toDateString();
        $this->modalLogDate = Carbon::now()->toDateString();

        // Initial sync of salaries for this month
        $hrService->syncMonthlySalaries($this->selectedMonth);
    }

    public function previousMonth(HrService $hrService)
    {
        $this->selectedMonth = Carbon::parse($this->selectedMonth.'-01')->subMonth()->format('Y-m');
        $hrService->syncMonthlySalaries($this->selectedMonth);
    }

    public function nextMonth(HrService $hrService)
    {
        $this->selectedMonth = Carbon::parse($this->selectedMonth.'-01')->addMonth()->format('Y-m');
        $hrService->syncMonthlySalaries($this->selectedMonth);
    }

    public function updatedSelectedMonth(HrService $hrService)
    {
        $hrService->syncMonthlySalaries($this->selectedMonth);
    }

    public function syncSalaries(HrService $hrService)
    {
        $hrService->syncMonthlySalaries($this->selectedMonth);
        session()->flash('success', 'Salary calculations & biometric hours re-synced successfully!');
    }

    /**
     * Open details modal when clicking on employee
     */
    public function openDetailsModal($salaryId, HrService $hrService)
    {
        $this->selectedSalaryId = $salaryId;
        $this->selectedSalary = HrSalaryModel::with(['employee.department', 'employee.hrSetting', 'payments'])
            ->findOrFail($salaryId);
        $this->selectedEmployee = $this->selectedSalary->employee;

        $this->modalLogDate = Carbon::now()->toDateString();
        $this->loadModalLogs($hrService);

        $this->showDetailsModal = true;
    }

    /**
     * Open payment modal to record a salary payout
     */
    public function openPaymentModal($salaryId)
    {
        $this->selectedSalaryId = $salaryId;
        $this->selectedSalary = HrSalaryModel::with('employee')->findOrFail($salaryId);
        $this->selectedEmployee = $this->selectedSalary->employee;

        $this->payAmount = $this->selectedSalary->balance_amount > 0 ? (float) $this->selectedSalary->balance_amount : '';
        $this->payDate = Carbon::now()->toDateString();
        $this->payMethod = 'Cash';
        $this->payReference = '';
        $this->payNotes = '';

        $this->showPaymentModal = true;
    }

    /**
     * Open opening balance modal for employee
     */
    public function openOpeningBalanceModal($salaryId)
    {
        $this->obSalaryId = $salaryId;
        $salary = HrSalaryModel::with('employee.hrSetting')->findOrFail($salaryId);
        $this->obEmpCode = (string) $salary->emp_code;
        $this->obEmployee = $salary->employee;
        $this->obAmount = $salary->opening_balance > 0
            ? (float) $salary->opening_balance
            : ($this->obEmployee?->hrSetting?->opening_balance > 0 ? (float) $this->obEmployee->hrSetting->opening_balance : '');
        $this->obNotes = '';

        $this->showOpeningBalanceModal = true;
    }

    public function closeOpeningBalanceModal()
    {
        $this->showOpeningBalanceModal = false;
        $this->obSalaryId = null;
        $this->obEmployee = null;
        $this->obEmpCode = null;
        $this->obAmount = '';
        $this->obNotes = '';
    }

    /**
     * Save Old Salary Opening Balance
     */
    public function saveOpeningBalance(HrService $hrService)
    {
        $this->validate([
            'obAmount' => 'required|numeric|min:0',
        ]);

        $hrService->updateOpeningBalance($this->obEmpCode, (float) $this->obAmount, $this->selectedMonth);

        $this->showOpeningBalanceModal = false;

        // If details modal was open, refresh selectedSalary
        if ($this->showDetailsModal && $this->selectedSalaryId == $this->obSalaryId) {
            $this->selectedSalary = HrSalaryModel::with(['employee.department', 'employee.hrSetting', 'payments'])
                ->findOrFail($this->selectedSalaryId);
        }

        session()->flash('success', 'Old Salary Opening Balance of Rs. '.number_format((float) $this->obAmount, 2)." updated successfully for {$this->obEmployee?->full_name}!");
    }

    /**
     * Submit payment towards employee's salary
     */
    public function submitPayment(HrService $hrService)
    {
        $this->validate([
            'payAmount' => 'required|numeric|min:1',
            'payDate' => 'required|date',
            'payMethod' => 'required|string',
        ]);

        $payment = $hrService->recordPayment($this->selectedSalaryId, [
            'amount' => $this->payAmount,
            'payment_date' => $this->payDate,
            'payment_method' => $this->payMethod,
            'reference_no' => $this->payReference,
            'notes' => $this->payNotes,
        ]);

        // Maintain correct data: if paid by Cash, create Daily Expense + StaffExpense and deduct Cash-in-Hand / POS Session
        if (strcasecmp($this->payMethod, 'Cash') === 0) {
            $empName = trim($this->selectedSalary->employee?->full_name ?? "Employee #{$this->selectedSalary->emp_code}");
            $staffSalType = strtoupper($empName).' SALARY';
            $monthStr = Carbon::parse($this->selectedSalary->salary_month)->format('M Y');
            $uid = Auth::id() ?: 1;

            $staffExpense = \App\Models\StaffExpense::create([
                'staff_id' => $uid,
                'expense_type' => $staffSalType,
                'amount' => (float) $this->payAmount,
                'description' => "Salary payment for {$empName} ({$monthStr}) via HR Salary",
                'expense_date' => $this->payDate,
                'status' => 'approved',
                'admin_notes' => 'Auto-approved (HR Salary)',
                'salary_employee_id' => null,
            ]);

            $expense = Expense::create([
                'category' => 'Staff Expense - '.$staffSalType,
                'amount' => (float) $this->payAmount,
                'description' => "Salary payment for {$empName} ({$monthStr}) via HR Salary",
                'date' => $this->payDate,
                'expense_type' => 'daily',
                'user_id' => $uid,
            ]);

            // Deduct cash in hand
            $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
            if ($cashInHandRecord) {
                DB::table('cash_in_hands')
                    ->where('key', 'cash_amount')
                    ->update([
                        'value' => $cashInHandRecord->value - (float) $this->payAmount,
                        'updated_at' => now(),
                    ]);
            }

            // Update today's POS session if payment is for today
            if (Carbon::parse($this->payDate)->isToday()) {
                $session = POSSession::getTodaySession($uid);
                if (! $session) {
                    $session = POSSession::openSession($uid, 0);
                }
                $session->expenses = ($session->expenses ?? 0) + (float) $this->payAmount;
                $session->save();
                $session->calculateDifference();
            }

            // Link STAFF-EXP and EXP references
            $payment->reference_no = ($this->payReference ? $this->payReference.' | ' : '')."STAFF-EXP-{$staffExpense->id} | EXP-{$expense->id}";
            $payment->save();
        }

        $this->showPaymentModal = false;

        // If details modal was open, refresh selectedSalary
        if ($this->showDetailsModal) {
            $this->selectedSalary = HrSalaryModel::with(['employee.department', 'employee.hrSetting', 'payments'])
                ->findOrFail($this->selectedSalaryId);
        }

        session()->flash('success', 'Payment of Rs. '.number_format($this->payAmount, 2).' recorded successfully!');
    }

    public function updatedModalLogDate()
    {
        $this->loadModalLogs(app(HrService::class));
    }

    protected function loadModalLogs(HrService $hrService)
    {
        if ($this->selectedSalary && $this->modalLogDate) {
            $this->modalTimeLogs = $hrService->getRawTimeLogs(
                $this->modalLogDate,
                null,
                null,
                $this->selectedSalary->emp_code
            )->toArray();
        } else {
            $this->modalTimeLogs = [];
        }
    }

    public function closeModals()
    {
        $this->showDetailsModal = false;
        $this->showPaymentModal = false;
        $this->showOpeningBalanceModal = false;
        $this->showResetModal = false;
    }

    /**
     * Authorize admin or authorized shop staff access
     */
    protected function authorizeAdmin(): void
    {
        if (! auth()->check() || (! auth()->user()->isAdmin() && ! auth()->user()->isStaff())) {
            abort(403, 'Unauthorized. Only authorized users can perform this reset action.');
        }
    }

    /**
     * Open the Reset Modal
     */
    public function openResetModal()
    {
        $this->authorizeAdmin();
        $this->resetScope = 'all';
        $this->resetConfirmText = '';
        $this->resetMonth = $this->selectedMonth ?: Carbon::now()->format('Y-m');
        $this->resetClearOpeningBalance = true;
        $this->showResetModal = true;
    }

    /**
     * Close the Reset Modal
     */
    public function closeResetModal()
    {
        $this->showResetModal = false;
        $this->resetConfirmText = '';
    }

    /**
     * Execute resetting records
     */
    public function executeReset(HrService $hrService)
    {
        $this->authorizeAdmin();

        if (trim(strtoupper($this->resetConfirmText)) !== 'RESET') {
            $this->addError('resetConfirmText', 'Please type RESET into the box to confirm.');
            return;
        }

        $monthToUse = ($this->resetScope === 'month') ? $this->resetMonth : null;
        $result = $hrService->resetAttendanceAndSalary($this->resetScope, $monthToUse, (bool) $this->resetClearOpeningBalance);

        $this->showResetModal = false;
        $this->resetConfirmText = '';

        $punchesDeleted = $result['deleted']['iclock_transaction'] ?? 0;
        $salariesDeleted = $result['deleted']['hr_salaries'] ?? 0;
        $paymentsDeleted = $result['deleted']['hr_salary_payments'] ?? 0;

        session()->flash('success', "Attendance & Salary records reset successfully! ({$punchesDeleted} biometric punches, {$salariesDeleted} monthly salaries, {$paymentsDeleted} payment logs cleared). All staff balances are ready for fresh entry.");
    }

    public function render()
    {
        $salaryMonth = Carbon::parse($this->selectedMonth.'-01')->toDateString();

        $query = HrSalaryModel::with(['employee.department', 'employee.hrSetting', 'payments'])
            ->where('salary_month', $salaryMonth);

        if (! empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('emp_code', 'like', "%{$s}%")
                    ->orWhereHas('employee', function ($eq) use ($s) {
                        $eq->where('first_name', 'like', "%{$s}%")
                            ->orWhere('last_name', 'like', "%{$s}%");
                    });
            });
        }

        if (! empty($this->statusFilter)) {
            $query->where('payment_status', $this->statusFilter);
        }

        $salaries = $query->orderBy('emp_code', 'asc')->get();

        // Aggregate Month Totals
        $totalNetSalary = $salaries->sum('net_salary');
        $totalPaidAmount = $salaries->sum('paid_amount');
        $totalBalanceAmount = $salaries->sum('balance_amount');
        $totalOpeningBalance = $salaries->sum('opening_balance');
        $totalStaff = $salaries->count();

        return view('livewire.admin.hr-salary', [
            'salaries' => $salaries,
            'totalNetSalary' => $totalNetSalary,
            'totalPaidAmount' => $totalPaidAmount,
            'totalBalanceAmount' => $totalBalanceAmount,
            'totalOpeningBalance' => $totalOpeningBalance,
            'totalStaff' => $totalStaff,
        ]);
    }
}
