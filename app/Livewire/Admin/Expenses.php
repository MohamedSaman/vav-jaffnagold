<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithDynamicLayout;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\POSSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Expenses')]
class Expenses extends Component
{
    use WithDynamicLayout;

    // Filter variables
    #[Url]
    public $filterMonth = 'current';

    #[Url]
    public $customMonth = '';

    // Data variables
    public $dailyExpenses = [];

    public $monthlyExpenses = [];

    public $dailyCategories = [];

    public $monthlyCategories = [];

    // Totals
    public $todayTotal = 0;

    public $monthTotal = 0;

    public $overallTotal = 0;

    public $filteredTotal = 0;

    // Form inputs for creating
    public $category;

    public $amount;

    public $date;

    public $status;

    public $description;

    public $customCategory;

    // Salary Deduction / Payout
    public $is_salary_deduction = false;

    public $deduction_staff_id = null;

    public $staffSearch = '';

    public $allStaff = [];

    public $selectedStaffSnapshot = null;

    // Form inputs for editing
    public $expenseId;

    public $edit_category;

    public $edit_amount;

    public $edit_date;

    public $edit_status;

    public $edit_description;

    public $edit_expense_type;

    // Delete confirmation
    public $expenseToDelete;

    // Modal states
    public $showEditDailyModal = false;

    public $showEditMonthlyModal = false;

    public $showDeleteModal = false;

    public $showViewModal = false;

    public $viewExpense = null;

    public function mount()
    {
        if (empty($this->filterMonth)) {
            $this->filterMonth = 'current';
        }
        $this->date = date('Y-m-d');
        $this->loadExpenses();
        $this->loadCategories();
        $this->loadStaffList();
    }

    public function updatedCategory($value)
    {
        if (in_array($value, ['Salary', 'Staff Salary', 'Salary Advance', 'Daily Wages', 'Salary Payout'])) {
            $this->is_salary_deduction = true;
        } else {
            try {
                $detected = app(\App\Services\HrService::class)->detectSalaryEmployee($value, $this->description);
                if ($detected) {
                    $this->is_salary_deduction = true;
                    $this->selectStaffForDeduction($detected->emp_code);
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    public function updatedCustomCategory($value)
    {
        try {
            $detected = app(\App\Services\HrService::class)->detectSalaryEmployee($value, $this->description);
            if ($detected) {
                $this->is_salary_deduction = true;
                $this->selectStaffForDeduction($detected->emp_code);
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    public function updatedDate($value)
    {
        if ($this->deduction_staff_id) {
            $this->loadStaffSalarySnapshot($this->deduction_staff_id, true);
        }
    }

    public function setQuickSalaryAmount($amount)
    {
        $this->amount = round((float) $amount, 2);
    }

    public function loadStaffSalarySnapshot($empCode, bool $autoFillAmount = true)
    {
        if (empty($empCode)) {
            $this->selectedStaffSnapshot = null;

            return;
        }

        try {
            $hrService = app(\App\Services\HrService::class);
            $dateToCheck = ! empty($this->date) ? $this->date : date('Y-m-d');
            $this->selectedStaffSnapshot = $hrService->getEmployeeSalarySnapshot((string) $empCode, $dateToCheck);

            if ($autoFillAmount && $this->selectedStaffSnapshot) {
                $todayRemaining = (float) ($this->selectedStaffSnapshot['today_balance'] ?? 0);
                $todaySalary = (float) ($this->selectedStaffSnapshot['today_salary'] ?? 0);
                $availableBalance = (float) ($this->selectedStaffSnapshot['total_available_balance'] ?? 0);

                if ($todayRemaining > 0) {
                    $this->amount = $todayRemaining;
                } elseif ($todaySalary > 0) {
                    $this->amount = $todaySalary;
                } elseif ($availableBalance > 0) {
                    $this->amount = $availableBalance;
                }
            }
        } catch (\Throwable $e) {
            $this->selectedStaffSnapshot = null;
        }
    }

    public function selectStaffForDeduction($empCode)
    {
        $this->deduction_staff_id = (string) $empCode;
        $staff = collect($this->allStaff)->firstWhere('emp_code', (string) $empCode);
        if ($staff) {
            if (empty($this->category) || $this->category === 'Other') {
                $this->category = strtoupper(trim($staff['name'])).' SALARY';
            }
            if (empty($this->description)) {
                $this->description = 'Salary payout to '.$staff['name'];
            }
        }
        $this->loadStaffSalarySnapshot((string) $empCode, true);
    }

    public function clearStaffSelection()
    {
        $this->deduction_staff_id = null;
        $this->selectedStaffSnapshot = null;
        $this->staffSearch = '';
    }

    public function getFilteredStaffProperty()
    {
        if (empty(trim($this->staffSearch))) {
            return $this->allStaff;
        }

        $term = strtolower(trim($this->staffSearch));

        return array_values(array_filter($this->allStaff, function ($staff) use ($term) {
            return str_contains(strtolower($staff['name']), $term)
                || str_contains(strtolower($staff['emp_code']), $term)
                || str_contains(strtolower($staff['department'] ?? ''), $term);
        }));
    }

    public function loadStaffList()
    {
        $this->allStaff = \App\Models\ZkEmployee::with(['department', 'hrSetting'])
            ->orderByRaw('CAST(emp_code AS UNSIGNED) ASC')
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => (string) $emp->emp_code,
                    'emp_code' => (string) $emp->emp_code,
                    'name' => $emp->full_name,
                    'department' => $emp->department?->dept_name ?? 'General Staff',
                    'daily_rate' => (float) ($emp->hrSetting?->daily_rate ?? 0),
                    'basic_salary' => (float) ($emp->hrSetting?->basic_salary ?? 0),
                    'salary_type' => $emp->hrSetting?->salary_type ?? 'monthly',
                ];
            })
            ->toArray();
    }

    public function loadCategories()
    {
        // Load Daily Expenses categories
        $categories = ExpenseCategory::where('expense_category', 'Daily Expenses')
            ->pluck('type')
            ->toArray();

        if (empty($categories)) {
            $defaultCategories = [
                'Sadhaka', 'Refreshment', 'Packaging', 'Stationery', 'Cleaning',
                'Telephone', 'Water', 'Electricity', 'Internet', 'Daily Wages',
                'Staff Meals', 'Overtime',
            ];
            foreach ($defaultCategories as $type) {
                ExpenseCategory::firstOrCreate([
                    'expense_category' => 'Daily Expenses',
                    'type' => $type,
                ]);
            }
            $categories = $defaultCategories;
        }

        $this->dailyCategories = array_filter($categories, fn ($cat) => $cat !== 'Other');

        // Load Monthly Expenses categories
        $this->monthlyCategories = ExpenseCategory::where('expense_category', 'Monthly Expenses')
            ->pluck('type')
            ->toArray();
    }

    public function loadExpenses()
    {
        $dailyQuery = Expense::where('expense_type', 'daily');
        $monthlyQuery = Expense::where('expense_type', 'monthly');

        if ($this->filterMonth === 'current') {
            $now = Carbon::now();
            $dailyQuery->whereMonth('date', $now->month)->whereYear('date', $now->year);
            $monthlyQuery->whereMonth('date', $now->month)->whereYear('date', $now->year);
        } elseif ($this->filterMonth === 'custom' && ! empty($this->customMonth)) {
            try {
                $parsed = Carbon::parse($this->customMonth);
                $dailyQuery->whereMonth('date', $parsed->month)->whereYear('date', $parsed->year);
                $monthlyQuery->whereMonth('date', $parsed->month)->whereYear('date', $parsed->year);
            } catch (\Exception $e) {
                $now = Carbon::now();
                $dailyQuery->whereMonth('date', $now->month)->whereYear('date', $now->year);
                $monthlyQuery->whereMonth('date', $now->month)->whereYear('date', $now->year);
            }
        }

        // Daily and Monthly lists (latest date first, then latest created)
        $this->dailyExpenses = $dailyQuery->orderByDesc('date')->orderByDesc('id')->get();
        $this->monthlyExpenses = $monthlyQuery->orderByDesc('date')->orderByDesc('id')->get();

        // Totals
        $this->todayTotal = Expense::whereDate('date', Carbon::today())->sum('amount');
        $this->monthTotal = Expense::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
        $this->overallTotal = Expense::sum('amount');
        $this->filteredTotal = collect($this->dailyExpenses)->sum('amount') + collect($this->monthlyExpenses)->sum('amount');
    }

    public function setMonthFilter($filter)
    {
        $this->filterMonth = $filter;
        if ($filter !== 'custom') {
            $this->customMonth = '';
        }
        $this->loadExpenses();
    }

    public function updatedFilterMonth()
    {
        $this->loadExpenses();
    }

    public function updatedCustomMonth($value)
    {
        if (! empty($value)) {
            $this->filterMonth = 'custom';
        } else {
            $this->filterMonth = 'current';
        }
        $this->loadExpenses();
    }

    public function saveDailyExpense()
    {
        $this->validate([
            'category' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $hrService = app(\App\Services\HrService::class);

            // Save new category if 'Other' is selected
            if ($this->category === 'Other') {
                if (trim((string) $this->customCategory) !== '') {
                    $this->category = trim($this->customCategory);
                }
            }

            // Auto-detect staff salary if typed like "SAMAN SALARY" even if toggle wasn't clicked
            if (! $this->is_salary_deduction || empty($this->deduction_staff_id)) {
                $detectedEmp = $hrService->detectSalaryEmployee($this->category, $this->description);
                if ($detectedEmp) {
                    $this->is_salary_deduction = true;
                    $this->deduction_staff_id = (string) $detectedEmp->emp_code;
                }
            }

            $employee = null;
            if ($this->is_salary_deduction && $this->deduction_staff_id) {
                $employee = \App\Models\ZkEmployee::with(['department', 'hrSetting'])
                    ->where('emp_code', (string) $this->deduction_staff_id)
                    ->first();
            }

            $staffSalType = $employee ? strtoupper(trim($employee->full_name)).' SALARY' : $this->category;

            if (! $employee && $this->category !== 'Other') {
                \App\Models\ExpenseCategory::firstOrCreate([
                    'expense_category' => 'Daily Expenses',
                    'type' => $this->category,
                ]);
            }

            // 1. Create the primary expense record
            $expense = Expense::create([
                'category' => $employee ? 'Staff Expense - '.$staffSalType : $this->category,
                'amount' => $this->amount,
                'description' => $this->description ?: ($employee ? "Salary for {$employee->full_name}" : null),
                'date' => $this->date,
                'expense_type' => 'daily',
                'user_id' => Auth::id(),
            ]);

            // 2. Handle Salary Payout / Deduction in real HR module if enabled
            if ($this->is_salary_deduction && $employee) {
                $empCode = (string) $employee->emp_code;
                $yearMonth = Carbon::parse($this->date)->format('Y-m');
                $salaryMonth = Carbon::parse($this->date)->startOfMonth()->toDateString();

                // Also create StaffExpense record so ShopStaffExpenses & DailyEntryBook stay synced
                $staffExpense = \App\Models\StaffExpense::create([
                    'staff_id' => Auth::id() ?: 1,
                    'expense_type' => $staffSalType,
                    'amount' => $this->amount,
                    'description' => $this->description ?: "Salary for {$employee->full_name}",
                    'expense_date' => $this->date,
                    'status' => 'approved',
                    'admin_notes' => 'Auto-approved (Salary Payout)',
                    'salary_employee_id' => null,
                ]);

                // Always re-sync monthly salaries so today's attendance and previous saved balance are up to date
                $hrService->syncMonthlySalaries($yearMonth);

                $salary = \App\Models\HrSalary::where('emp_code', $empCode)
                    ->where('salary_month', $salaryMonth)
                    ->first();

                if (! $salary) {
                    $salary = \App\Models\HrSalary::create([
                        'zk_employee_id' => $employee->id,
                        'emp_code' => $empCode,
                        'salary_month' => $salaryMonth,
                        'salary_type' => $employee->hrSetting?->salary_type ?: 'monthly',
                        'daily_working_hours' => $employee->hrSetting?->daily_working_hours ?: 10.00,
                        'daily_rate' => $employee->hrSetting?->daily_rate ?: 0,
                        'basic_salary' => $employee->hrSetting?->basic_salary ?: 0,
                        'net_salary' => 0,
                        'payment_status' => 'unpaid',
                    ]);
                }

                // Record payment in HR Salary Payments table
                $hrService->recordPayment($salary->id, [
                    'amount' => $this->amount,
                    'payment_date' => $this->date,
                    'payment_method' => 'Cash',
                    'reference_no' => "STAFF-EXP-{$staffExpense->id} | EXP-{$expense->id}",
                    'notes' => 'Salary given via Daily Expenses ('.$staffSalType.'): '.($this->description ?: 'Salary Payment'),
                ]);
            }

            // 3. Update cash in hands - subtract expense amount
            $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
            if ($cashInHandRecord) {
                DB::table('cash_in_hands')
                    ->where('key', 'cash_amount')
                    ->update([
                        'value' => $cashInHandRecord->value - $this->amount,
                        'updated_at' => now(),
                    ]);
            }

            // 4. Update today's POS session if expense is for today
            if (Carbon::parse($this->date)->isToday()) {
                $uid = Auth::id() ?: 1;
                $session = POSSession::getTodaySession($uid);
                if (! $session) {
                    $session = POSSession::openSession($uid, 0);
                }
                $session->expenses = ($session->expenses ?? 0) + $this->amount;
                $session->save();
                $session->calculateDifference();
            }

            DB::commit();

            $this->reset(['category', 'amount', 'description', 'is_salary_deduction', 'deduction_staff_id', 'selectedStaffSnapshot', 'staffSearch', 'customCategory']);
            $this->date = date('Y-m-d');
            $this->loadExpenses();
            $this->loadCategories();
            $this->js("swal.fire('Success!', 'Daily expense added successfully.', 'success')");
            $this->dispatch('close-modal', 'addDailyExpenseModal');
            $this->dispatch('refreshPage');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save daily expense: '.$e->getMessage());
            $this->js("swal.fire('Error!', 'Could not add expense. Check logs.', 'error')");
        }
    }

    public function saveMonthlyExpense()
    {
        $this->validate([
            'date' => 'required|date',
            'category' => 'required',
            'amount' => 'required|numeric|min:0',
        ]);

        Expense::create([
            'date' => $this->date,
            'category' => $this->category,
            'amount' => $this->amount,
            'status' => $this->status,
            'description' => $this->description,
            'expense_type' => 'monthly',
        ]);

        // Update cash in hands - subtract expense amount
        $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();

        if ($cashInHandRecord) {
            DB::table('cash_in_hands')
                ->where('key', 'cash_amount')
                ->update([
                    'value' => $cashInHandRecord->value - $this->amount,
                    'updated_at' => now(),
                ]);
        }

        // If the monthly expense is for today, update today's POS session totals
        try {
            if ($this->date && Carbon::parse($this->date)->toDateString() === Carbon::today()->toDateString()) {
                $session = POSSession::getTodaySession(Auth::id());
                if (! $session) {
                    $session = POSSession::openSession(Auth::id(), 0);
                }

                $session->expenses = ($session->expenses ?? 0) + $this->amount;
                $session->save();
                $session->calculateDifference();
            }
        } catch (\Exception $e) {
            Log::error('Failed to update POS session after monthly expense: '.$e->getMessage());
        }

        $this->reset(['date', 'category', 'amount', 'status', 'description']);
        $this->loadExpenses();
        $this->js("swal.fire('Success!', 'Monthly expense added successfully.', 'success')");
        $this->dispatch('close-modal', 'addMonthlyExpenseModal');
        $this->dispatch('refreshPage');
    }

    public function confirmDelete($id)
    {
        $this->expenseToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteExpense()
    {
        if ($this->expenseToDelete) {
            $expense = Expense::find($this->expenseToDelete);
            if ($expense) {
                $expenseId = $expense->id;
                $amount = (float) $expense->amount;
                $expenseDate = $expense->date;

                // 1. If this expense was linked to an HR salary payment, remove it, clean up StaffExpense, and recalculate balance
                $linkedPayment = \App\Models\HrSalaryPayment::where('reference_no', 'like', "%EXP-{$expenseId}%")->first();
                if ($linkedPayment) {
                    if (preg_match('/STAFF-EXP-(\d+)/', (string) $linkedPayment->reference_no, $matches)) {
                        \App\Models\StaffExpense::where('id', (int) $matches[1])->delete();
                    }
                    $salary = $linkedPayment->salary;
                    $linkedPayment->delete();
                    if ($salary) {
                        $salary->recalculateBalance();
                    }
                }

                // 2. Delete the expense
                $expense->delete();

                // 3. Restore cash in hand
                $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
                if ($cashInHandRecord) {
                    DB::table('cash_in_hands')
                        ->where('key', 'cash_amount')
                        ->update([
                            'value' => $cashInHandRecord->value + $amount,
                            'updated_at' => now(),
                        ]);
                }

                // 4. Update today's POS session if was today
                if ($expenseDate && Carbon::parse($expenseDate)->isToday()) {
                    $uid = Auth::id() ?: 1;
                    $session = POSSession::getTodaySession($uid);
                    if ($session) {
                        $session->expenses = max(0, ($session->expenses ?? 0) - $amount);
                        $session->save();
                        $session->calculateDifference();
                    }
                }
            }

            $this->loadExpenses();
            $this->js("swal.fire('Deleted!', 'Expense has been deleted and salary/cash restored.', 'success')");
            $this->showDeleteModal = false;
            $this->expenseToDelete = null;
            $this->dispatch('refreshPage');
        }
    }

    public function editExpense($id)
    {
        $expense = Expense::findOrFail($id);

        $this->expenseId = $expense->id;
        $this->edit_category = $expense->category;
        $this->edit_description = $expense->description;
        $this->edit_amount = $expense->amount;
        $this->edit_date = $expense->date ? $expense->date->format('Y-m-d') : '';
        $this->edit_status = $expense->status;
        $this->edit_expense_type = $expense->expense_type;

        // Open modal based on expense type
        if ($expense->expense_type === 'daily') {
            $this->showEditDailyModal = true;
        } else {
            $this->showEditMonthlyModal = true;
        }
    }

    public function viewExpense($id)
    {
        $this->viewExpense = Expense::findOrFail($id);
        $this->showViewModal = true;
    }

    public function updateExpense()
    {
        $this->validate([
            'edit_category' => 'required|string',
            'edit_amount' => 'required|numeric|min:0',
        ]);

        $expense = Expense::findOrFail($this->expenseId);

        $updateData = [
            'category' => $this->edit_category,
            'description' => $this->edit_description,
            'amount' => $this->edit_amount,
        ];

        if ($expense->expense_type === 'monthly') {
            $this->validate(['edit_date' => 'required|date']);
            $updateData['date'] = $this->edit_date;
            $updateData['status'] = $this->edit_status;
        } else {
            // Preserve the existing expense date (or use edit_date if provided) instead of overwriting with now()
            $updateData['date'] = ! empty($this->edit_date) ? $this->edit_date : $expense->date;
        }

        $expense->update($updateData);

        // Close the modals
        $this->showEditDailyModal = false;
        $this->showEditMonthlyModal = false;

        $this->resetEditFields();
        $this->loadExpenses();
        $this->js("swal.fire('Success!', 'Expense updated successfully.', 'success')");
        $this->dispatch('refreshPage');
    }

    public function resetEditFields()
    {
        $this->reset([
            'expenseId',
            'edit_category',
            'edit_amount',
            'edit_date',
            'edit_status',
            'edit_description',
            'edit_expense_type',
        ]);
        $this->resetErrorBag();
    }

    public function resetFields()
    {
        $this->reset(['category', 'amount', 'date', 'status', 'description']);
        $this->resetErrorBag();
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewExpense = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->expenseToDelete = null;
    }

    public function closeEditDailyModal()
    {
        $this->showEditDailyModal = false;
        $this->resetEditFields();
    }

    public function closeEditMonthlyModal()
    {
        $this->showEditMonthlyModal = false;
        $this->resetEditFields();
    }

    public function render()
    {
        return view('livewire.admin.expenses')->layout($this->layout);
    }
}
