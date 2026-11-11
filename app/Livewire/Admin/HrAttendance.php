<?php

namespace App\Livewire\Admin;

use App\Models\Expense;
use App\Models\POSSession;
use App\Models\ZkEmployee;
use App\Services\HrService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('HR - Biometric Attendance & Time Logs')]
class HrAttendance extends Component
{
    public $selectedDate;

    public $selectedEmpCode = '';

    public $activeTab = 'summary'; // 'summary' or 'raw_logs'

    public $rawLogStartDate;

    public $rawLogEndDate;

    // Punch Logs Edit Modal States
    public $showPunchModal = false;

    // Reset Modal States
    public $showResetModal = false;
    public $resetScope = 'all'; // 'all' or 'month'
    public $resetConfirmText = '';
    public $resetMonth = '';
    public $resetClearOpeningBalance = true;

    public $punchModalEmpCode = null;

    public $punchModalEmployee = null;

    public $punchModalDate = null;

    public $punchModalList = [];

    public $punchModalSessions = [];

    public $punchModalTotalHours = 0;

    public $newPunchTimeInput = '';

    // Give Salary Modal States
    public $showGiveSalaryModal = false;

    public $giveSalaryEmpCode = null;

    public $giveSalaryEmployee = null;

    public $giveSalaryDate = null;

    public $giveSalaryHoursWorked = 0;

    public $giveSalaryCalculated = 0;

    public $giveSalaryDayFull = 0;

    public $giveSalaryHourlyRate = 0;

    public $giveSalaryAlreadyPaid = 0;

    public $giveSalaryBalance = 0;

    public $giveSalaryPreviousBalance = 0;

    public $giveSalaryTotalAvailableBalance = 0;

    public $giveSalaryPastPayments = [];

    public $giveSalaryAmount = '';

    public $giveSalaryPaymentMethod = 'Cash';

    public $giveSalaryReference = '';

    public $giveSalaryNotes = '';

    protected $cachedDailyAttendance = null;

    public function getDailyAttendanceData(?HrService $hrService = null)
    {
        if ($this->cachedDailyAttendance === null) {
            $hrService = $hrService ?: app(HrService::class);
            $this->cachedDailyAttendance = $hrService->getDailyAttendance(
                $this->selectedDate,
                $this->selectedEmpCode ?: null
            );
        }

        return $this->cachedDailyAttendance;
    }

    public function updatedSelectedDate()
    {
        $this->cachedDailyAttendance = null;
    }

    public function updatedSelectedEmpCode()
    {
        $this->cachedDailyAttendance = null;
    }

    public function mount()
    {
        $this->selectedDate = Carbon::now()->toDateString();
        $this->rawLogStartDate = Carbon::now()->toDateString();
        $this->rawLogEndDate = Carbon::now()->toDateString();
    }

    public function setDateToday()
    {
        $this->selectedDate = Carbon::now()->toDateString();
        $this->rawLogStartDate = Carbon::now()->toDateString();
        $this->rawLogEndDate = Carbon::now()->toDateString();
        $this->cachedDailyAttendance = null;
    }

    public function setDateYesterday()
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $this->selectedDate = $yesterday;
        $this->rawLogStartDate = $yesterday;
        $this->rawLogEndDate = $yesterday;
        $this->cachedDailyAttendance = null;
    }

    /**
     * Authorize admin or authorized shop staff access for punch/time edits & reset
     */
    protected function authorizeAdmin(): void
    {
        if (! auth()->check() || (! auth()->user()->isAdmin() && ! auth()->user()->isStaff())) {
            abort(403, 'Unauthorized. Only authorized users can edit biometric punch times or reset records.');
        }
    }

    /**
     * Open Punch Edit Modal for an employee on a given date
     */
    public function openPunchModal($empCode, $date = null)
    {
        $this->authorizeAdmin();

        $this->punchModalEmpCode = $empCode;
        $this->punchModalDate = $date ?: $this->selectedDate;
        $this->punchModalEmployee = ZkEmployee::with('department')->where('emp_code', $empCode)->first();
        $this->newPunchTimeInput = '';

        $this->loadPunchModalData(app(HrService::class));
        $this->showPunchModal = true;
    }

    /**
     * Load punches & sessions for the punch edit modal
     */
    public function loadPunchModalData(HrService $hrService)
    {
        if (! $this->punchModalEmpCode || ! $this->punchModalDate) {
            return;
        }

        $punches = $hrService->getPunchesForDate($this->punchModalEmpCode, $this->punchModalDate);
        $sessionData = $hrService->processPunchSessions($punches);

        $this->punchModalList = $sessionData['labeled_punches'];
        $this->punchModalSessions = $sessionData['sessions'];
        $this->punchModalTotalHours = $sessionData['total_hours'];
    }

    /**
     * Update a single punch time in the modal and recalculate immediately
     */
    public function updateSinglePunch($punchId, $newTime, ?HrService $hrService = null)
    {
        $this->authorizeAdmin();

        if (empty($newTime)) {
            return;
        }

        $hrService = $hrService ?: app(HrService::class);
        try {
            $parsed = Carbon::parse(trim($newTime));
            $newDateTime = Carbon::parse($this->punchModalDate.' '.$parsed->format('H:i:s'))->toDateTimeString();
        } catch (\Exception $e) {
            return;
        }

        $hrService->updatePunchTime($punchId, $newDateTime);
        $this->loadPunchModalData($hrService);

        // Re-sync monthly salary with new working hours
        $hrService->syncMonthlySalaries(Carbon::parse($this->punchModalDate)->format('Y-m'));
        $this->cachedDailyAttendance = null;
    }

    /**
     * Save all edited punch times from form inputs
     */
    public function saveAllPunches(?HrService $hrService = null)
    {
        $this->authorizeAdmin();

        $hrService = $hrService ?: app(HrService::class);
        foreach ($this->punchModalList as $item) {
            if (! empty($item['id']) && ! empty($item['time'])) {
                try {
                    $parsed = Carbon::parse(trim($item['time']));
                    $newDateTime = Carbon::parse($this->punchModalDate.' '.$parsed->format('H:i:s'))->toDateTimeString();
                    $hrService->updatePunchTime($item['id'], $newDateTime);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // Re-sync monthly salary
        $hrService->syncMonthlySalaries(Carbon::parse($this->punchModalDate)->format('Y-m'));
        $this->cachedDailyAttendance = null;

        $this->loadPunchModalData($hrService);
        $this->showPunchModal = false;

        session()->flash('success', "Punch logs and working hours updated successfully for {$this->punchModalEmployee?->full_name}!");
    }

    /**
     * Add a new punch for this employee on this date
     */
    public function addPunch(?HrService $hrService = null)
    {
        $this->authorizeAdmin();

        $this->validate([
            'newPunchTimeInput' => 'required|string',
        ]);

        $hrService = $hrService ?: app(HrService::class);

        try {
            $parsedTime = Carbon::parse(trim($this->newPunchTimeInput));
        } catch (\Exception $e) {
            $this->addError('newPunchTimeInput', 'Please enter a valid time (e.g. 09:05 or 09:05 PM or 21:05).');
            return;
        }

        $newDateTime = Carbon::parse($this->punchModalDate.' '.$parsedTime->format('H:i:s'))->toDateTimeString();
        $hrService->addPunch($this->punchModalEmpCode, $newDateTime);

        $this->newPunchTimeInput = '';
        $this->loadPunchModalData($hrService);

        // Re-sync monthly salary
        $hrService->syncMonthlySalaries(Carbon::parse($this->punchModalDate)->format('Y-m'));
        $this->cachedDailyAttendance = null;

        session()->flash('punch_success', 'New punch added successfully!');
    }

    /**
     * Delete a punch record
     */
    public function deletePunch($punchId, HrService $hrService)
    {
        $this->authorizeAdmin();

        $hrService->deletePunch($punchId);
        $this->loadPunchModalData($hrService);

        // Re-sync monthly salary
        $hrService->syncMonthlySalaries(Carbon::parse($this->punchModalDate)->format('Y-m'));
        $this->cachedDailyAttendance = null;

        session()->flash('punch_success', 'Punch deleted successfully!');
    }

    public function closePunchModal()
    {
        $this->showPunchModal = false;
    }

    /**
     * Open the Reset Modal
     */
    public function openResetModal()
    {
        $this->authorizeAdmin();
        $this->resetScope = 'all';
        $this->resetConfirmText = '';
        $this->resetMonth = Carbon::parse($this->selectedDate)->format('Y-m');
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
        $this->cachedDailyAttendance = null;

        $punchesDeleted = $result['deleted']['iclock_transaction'] ?? 0;
        $salariesDeleted = $result['deleted']['hr_salaries'] ?? 0;
        $paymentsDeleted = $result['deleted']['hr_salary_payments'] ?? 0;

        session()->flash('success', "Attendance & Salary records reset successfully! ({$punchesDeleted} biometric punches, {$salariesDeleted} monthly salaries, {$paymentsDeleted} payment logs cleared). All staff balances are ready for fresh entry.");
    }

    /**
     * Open Give Salary Modal for an employee on a given date
     */
    public function openGiveSalaryModal($empCode, $date = null)
    {
        $date = $date ?: $this->selectedDate;
        $this->giveSalaryEmpCode = (string) $empCode;
        $this->giveSalaryDate = $date;

        $hrService = app(HrService::class);
        $daily = $this->getDailyAttendanceData($hrService)->firstWhere('emp_code', (string) $empCode);

        if ($daily) {
            $this->giveSalaryEmployee = $daily['employee'];
            $this->giveSalaryHoursWorked = $daily['hours_worked'];
            $this->giveSalaryCalculated = $daily['today_salary'];
            $this->giveSalaryDayFull = $daily['day_full_salary'];
            $this->giveSalaryHourlyRate = $daily['hourly_rate'];
            $this->giveSalaryAlreadyPaid = $daily['paid_today'];
            $this->giveSalaryBalance = $daily['today_balance'];
            $this->giveSalaryPreviousBalance = (float) ($daily['previous_balance'] ?? 0);
            $this->giveSalaryTotalAvailableBalance = (float) ($daily['total_available_balance'] ?? 0);
            $this->giveSalaryPastPayments = collect($daily['today_payments'] ?? [])->map(function ($p) {
                return (array) $p;
            })->toArray();
            // Pre-fill with remaining balance for today, or calculated salary if none paid yet, or available balance
            $this->giveSalaryAmount = $this->giveSalaryBalance > 0
                ? (float) $this->giveSalaryBalance
                : ($this->giveSalaryCalculated > 0 ? (float) $this->giveSalaryCalculated : ($this->giveSalaryTotalAvailableBalance > 0 ? (float) $this->giveSalaryTotalAvailableBalance : 0));
        } else {
            $this->giveSalaryEmployee = ZkEmployee::with(['department', 'hrSetting', 'areas'])->where('emp_code', $empCode)->first();
            $this->giveSalaryHoursWorked = 0;
            $this->giveSalaryCalculated = 0;
            $this->giveSalaryDayFull = 0;
            $this->giveSalaryHourlyRate = 0;
            $this->giveSalaryAlreadyPaid = 0;
            $this->giveSalaryBalance = 0;
            $this->giveSalaryPreviousBalance = 0;
            $this->giveSalaryTotalAvailableBalance = 0;
            $this->giveSalaryPastPayments = [];
            $this->giveSalaryAmount = '';
        }

        $this->giveSalaryPaymentMethod = 'Cash';
        $this->giveSalaryReference = '';
        $this->giveSalaryNotes = 'Daily salary payment for ' . Carbon::parse($date)->format('Y-m-d');
        $this->showGiveSalaryModal = true;
    }

    public function closeGiveSalaryModal()
    {
        $this->showGiveSalaryModal = false;
    }

    /**
     * Set quick amount for give salary
     */
    public function setQuickGiveAmount($amount)
    {
        $this->giveSalaryAmount = (float) $amount;
    }

    /**
     * Submit salary payment for this employee on this date
     */
    public function submitGiveSalary(?HrService $hrService = null)
    {
        $this->validate([
            'giveSalaryAmount' => 'required|numeric|min:1',
            'giveSalaryPaymentMethod' => 'required|string',
        ]);

        if (! $this->giveSalaryEmpCode) {
            return;
        }

        $hrService = $hrService ?: app(HrService::class);
        $employee = $this->giveSalaryEmployee ?: ZkEmployee::with(['department', 'hrSetting'])->where('emp_code', $this->giveSalaryEmpCode)->first();

        if (! $employee) {
            return;
        }

        $yearMonth = Carbon::parse($this->giveSalaryDate)->format('Y-m');
        $salaryMonth = Carbon::parse($this->giveSalaryDate)->startOfMonth()->toDateString();

        // 1. Always re-sync monthly salary record first so today's attendance hours and saved balance are accurate
        $hrService->syncMonthlySalaries($yearMonth);

        $salary = \App\Models\HrSalary::where('emp_code', $this->giveSalaryEmpCode)
            ->where('salary_month', $salaryMonth)
            ->first();

        if (! $salary) {
            $salary = \App\Models\HrSalary::create([
                'zk_employee_id' => $employee->id,
                'emp_code' => $this->giveSalaryEmpCode,
                'salary_month' => $salaryMonth,
                'salary_type' => $employee->hrSetting?->salary_type ?: 'daily',
                'daily_working_hours' => $employee->hrSetting?->daily_working_hours ?: 10.00,
                'daily_rate' => $employee->hrSetting?->daily_rate ?: 0,
                'basic_salary' => $employee->hrSetting?->basic_salary ?: 0,
                'net_salary' => $this->giveSalaryCalculated,
                'payment_status' => 'unpaid',
            ]);
        }

        // 2. Record payment
        $payment = $hrService->recordPayment($salary->id, [
            'amount' => $this->giveSalaryAmount,
            'payment_date' => $this->giveSalaryDate,
            'payment_method' => $this->giveSalaryPaymentMethod,
            'reference_no' => $this->giveSalaryReference,
            'notes' => $this->giveSalaryNotes,
        ]);

        // Maintain correct data: if paid by Cash, create Daily Expense + StaffExpense and deduct Cash-in-Hand / POS Session
        if (strcasecmp($this->giveSalaryPaymentMethod, 'Cash') === 0) {
            $uid = Auth::id() ?: 1;
            $empName = trim($employee->full_name);
            $staffSalType = strtoupper($empName).' SALARY';

            $staffExpense = \App\Models\StaffExpense::create([
                'staff_id' => $uid,
                'expense_type' => $staffSalType,
                'amount' => (float) $this->giveSalaryAmount,
                'description' => "Salary given to {$empName} via HR Attendance",
                'expense_date' => $this->giveSalaryDate,
                'status' => 'approved',
                'admin_notes' => 'Auto-approved (HR Attendance)',
                'salary_employee_id' => null,
            ]);

            $expense = Expense::create([
                'category' => 'Staff Expense - '.$staffSalType,
                'amount' => (float) $this->giveSalaryAmount,
                'description' => "Salary given to {$empName} via HR Attendance",
                'date' => $this->giveSalaryDate,
                'expense_type' => 'daily',
                'user_id' => $uid,
            ]);

            // Deduct cash in hand
            $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
            if ($cashInHandRecord) {
                DB::table('cash_in_hands')
                    ->where('key', 'cash_amount')
                    ->update([
                        'value' => $cashInHandRecord->value - (float) $this->giveSalaryAmount,
                        'updated_at' => now(),
                    ]);
            }

            // Update today's POS session if payment is for today
            if (Carbon::parse($this->giveSalaryDate)->isToday()) {
                $session = POSSession::getTodaySession($uid);
                if (! $session) {
                    $session = POSSession::openSession($uid, 0);
                }
                $session->expenses = ($session->expenses ?? 0) + (float) $this->giveSalaryAmount;
                $session->save();
                $session->calculateDifference();
            }

            // Link STAFF-EXP and EXP references
            $payment->reference_no = ($this->giveSalaryReference ? $this->giveSalaryReference.' | ' : '')."STAFF-EXP-{$staffExpense->id} | EXP-{$expense->id}";
            $payment->save();
        }

        // 3. Re-sync monthly salary
        $hrService->syncMonthlySalaries($yearMonth);
        $this->cachedDailyAttendance = null;

        $this->showGiveSalaryModal = false;
        session()->flash('success', 'Salary payment of Rs. ' . number_format($this->giveSalaryAmount, 2) . ' given to ' . $employee->full_name . ' successfully!');
    }

    /**
     * Submit salary payment from instant Alpine modal
     */
    public function submitSalaryPayment($empCode, $date, $amount, $paymentMethod = 'Cash', $reference = '', $notes = '', ?HrService $hrService = null)
    {
        $this->giveSalaryEmpCode = (string) $empCode;
        $this->giveSalaryDate = $date;
        $this->giveSalaryAmount = (float) $amount;
        $this->giveSalaryPaymentMethod = $paymentMethod ?: 'Cash';
        $this->giveSalaryReference = $reference ?: '';
        $this->giveSalaryNotes = $notes ?: ('Daily salary payment for ' . Carbon::parse($date)->format('Y-m-d'));

        $this->submitGiveSalary($hrService);
    }

    public function render(HrService $hrService)
    {
        $employees = ZkEmployee::orderBy('emp_code', 'asc')->get();

        // 1. Processed daily attendance summary for selectedDate
        $dailyAttendance = $this->getDailyAttendanceData($hrService);

        // Stats
        $totalStaff = $dailyAttendance->count();
        $presentCount = $dailyAttendance->where('status', '!=', 'absent')->count();
        $absentCount = $dailyAttendance->where('status', 'absent')->count();
        $totalHoursToday = $dailyAttendance->sum('hours_worked');
        $totalSalaryToday = $dailyAttendance->sum('today_salary');
        $totalSalaryGivenToday = $dailyAttendance->sum('paid_today');

        // 2. Raw time logs for selected date or date range
        $rawLogs = [];
        if ($this->activeTab === 'raw_logs') {
            $rawLogs = $hrService->getRawTimeLogs(
                null,
                $this->rawLogStartDate,
                $this->rawLogEndDate,
                $this->selectedEmpCode ?: null
            );
        }

        return view('livewire.admin.hr-attendance', [
            'selectedDate' => $this->selectedDate,
            'employees' => $employees,
            'dailyAttendance' => $dailyAttendance,
            'totalStaff' => $totalStaff,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'totalHoursToday' => $totalHoursToday,
            'totalSalaryToday' => $totalSalaryToday,
            'totalSalaryGivenToday' => $totalSalaryGivenToday,
            'rawLogs' => $rawLogs,
        ]);
    }
}
