<?php

namespace App\Livewire\ShopStaff;

use App\Models\Expense;
use App\Models\POSSession;
use App\Models\StaffExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.shop-staff')]
#[Title('Daily Expenses')]
class ShopStaffExpenses extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $expense_type = '';

    public $dbExpenseTypes = [];

    public $quickSuppliers = ['Mano anna', 'RAMESH ANNA', 'Sutha anna'];

    public $amount = '';

    public $description = '';

    public $expense_date = '';

    public $search = '';

    public $dateFilter = ''; // Date picker calendar filter

    // Salary Payout properties
    public $is_salary = false;

    public $salary_employee_id = '';

    public $salaryEmployees = [];

    public $selectedStaffSnapshot = null;

    // Modal states
    public $showAddModal = false;

    public $showDeleteModal = false;

    public $expenseToDelete = null;

    public $registeredSuppliers = [];

    public function rules()
    {
        return [
            'expense_type' => $this->is_salary ? 'nullable|string' : 'required|string|max:255',
            'salary_employee_id' => $this->is_salary ? 'required' : 'nullable',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'expense_date' => 'required|date',
        ];
    }

    public function mount()
    {
        $this->expense_date = date('Y-m-d');
        $this->dateFilter = date('Y-m-d'); // Default to today's date on calendar
        try {
            $this->loadExpenseTypes();
            $this->salaryEmployees = \App\Models\ZkEmployee::with(['department', 'hrSetting'])
                ->orderByRaw('CAST(emp_code AS UNSIGNED) ASC')
                ->get();
        } catch (\Throwable $e) {
            $this->registeredSuppliers = [];
            $this->dbExpenseTypes = [];
            $this->salaryEmployees = [];
        }
    }

    public function loadExpenseTypes()
    {
        $this->registeredSuppliers = \App\Models\HandmadeSupplier::where('is_active', true)->pluck('name')->toArray();

        $this->dbExpenseTypes = \App\Models\ExpenseCategory::where('expense_category', 'Daily Expenses')
            ->pluck('type')
            ->filter()
            ->values()
            ->toArray();

        // If empty, pre-seed with standard clean categories
        if (empty($this->dbExpenseTypes)) {
            $this->dbExpenseTypes = [
                'Tea & Refreshment', 'Staff Meals', 'Water & Amenities', 'Transport & Fuel',
                'Packaging & Bags', 'Stationery & Printing', 'Cleaning & Maintenance',
                'Courier & Delivery', 'Sadhaka & Charity', 'Utilities (Phone/WiFi/Power)',
                'Daily Wages & Overtime', 'Customer Hospitality', 'Other Daily Expenses',
            ];
        }
    }

    public function selectSupplierCategory($name)
    {
        $this->is_salary = false;
        $this->salary_employee_id = '';
        $this->selectedStaffSnapshot = null;
        $this->expense_type = $name;
    }

    public function selectStaffSalary($empCode)
    {
        $this->is_salary = true;
        $this->salary_employee_id = (string) $empCode;

        $emp = collect($this->salaryEmployees)->firstWhere('emp_code', (string) $empCode)
            ?: \App\Models\ZkEmployee::with(['department', 'hrSetting'])->where('emp_code', (string) $empCode)->first();

        if ($emp) {
            $this->expense_type = strtoupper(trim($emp->full_name)).' SALARY';
        }

        $this->loadSalarySnapshot((string) $empCode, true);
    }

    public function clearSelectedStaffSalary()
    {
        $this->is_salary = false;
        $this->salary_employee_id = '';
        $this->selectedStaffSnapshot = null;
        $this->expense_type = '';
        $this->amount = '';
    }

    public function setQuickSalaryAmount($amount)
    {
        $this->amount = round((float) $amount, 2);
    }

    public function loadSalarySnapshot($empCode, bool $autoFillAmount = true)
    {
        if (empty($empCode)) {
            $this->selectedStaffSnapshot = null;

            return;
        }

        try {
            $hrService = app(\App\Services\HrService::class);
            $dateToCheck = ! empty($this->expense_date) ? $this->expense_date : date('Y-m-d');
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
                } else {
                    $this->amount = '';
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to load staff salary snapshot: '.$e->getMessage());
            $this->selectedStaffSnapshot = null;
        }
    }

    public function updatedIsSalary($value)
    {
        if ($value) {
            if (! empty($this->salary_employee_id)) {
                $this->selectStaffSalary($this->salary_employee_id);
            } else {
                $this->expense_type = 'Salary Payout';
            }
        } else {
            $this->expense_type = '';
            $this->salary_employee_id = '';
            $this->selectedStaffSnapshot = null;
        }
    }

    public function updatedSalaryEmployeeId($value)
    {
        if (! empty($value)) {
            $this->selectStaffSalary($value);
        } else {
            $this->selectedStaffSnapshot = null;
        }
    }

    public function updatedExpenseType($value)
    {
        try {
            $hrService = app(\App\Services\HrService::class);
            $detected = $hrService->detectSalaryEmployee($value, $this->description);
            if ($detected) {
                $prevEmpCode = (string) $this->salary_employee_id;
                $this->salary_employee_id = (string) $detected->emp_code;
                $this->loadSalarySnapshot(
                    (string) $detected->emp_code,
                    $prevEmpCode !== (string) $detected->emp_code || $this->amount === '' || $this->amount === null
                );
            } elseif (! $this->is_salary) {
                $this->salary_employee_id = '';
                $this->selectedStaffSnapshot = null;
            }
        } catch (\Throwable $e) {
            // Ignore detection error while typing
        }
    }

    public function updatedExpenseDate($value)
    {
        if (! empty($this->salary_employee_id)) {
            $this->loadSalarySnapshot((string) $this->salary_employee_id, true);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function setToday()
    {
        $this->dateFilter = date('Y-m-d');
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->loadExpenseTypes();
        $this->salaryEmployees = \App\Models\ZkEmployee::with(['department', 'hrSetting'])
            ->orderByRaw('CAST(emp_code AS UNSIGNED) ASC')
            ->get();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function addExpense()
    {
        // Auto-detect staff salary if typed like "SAMAN SALARY" even without flipping the switch
        $hrService = app(\App\Services\HrService::class);
        if (! $this->is_salary || empty($this->salary_employee_id)) {
            $detectedEmp = $hrService->detectSalaryEmployee($this->expense_type, $this->description);
            if ($detectedEmp) {
                $this->is_salary = true;
                $this->salary_employee_id = (string) $detectedEmp->emp_code;
            }
        }

        $this->validate();

        try {
            DB::beginTransaction();

            $employeeName = '';
            $zkEmp = null;
            if ($this->is_salary && ! empty($this->salary_employee_id)) {
                $zkEmp = \App\Models\ZkEmployee::with(['department', 'hrSetting'])
                    ->where('emp_code', (string) $this->salary_employee_id)
                    ->first();
                $employeeName = $zkEmp ? trim($zkEmp->full_name) : '';
            }

            $type = ($this->is_salary && $employeeName !== '')
                ? strtoupper($employeeName).' SALARY'
                : ($this->is_salary ? 'Salary Payout' : trim($this->expense_type));

            $isPurchasing = ! $this->is_salary && \App\Services\PurchasingExpenseService::isPurchasingExpense($type, $this->description);

            // Save new expense type to database only if it's not a salary, not a supplier purchasing expense, and doesn't exist yet
            if (! $this->is_salary && ! $isPurchasing && $type !== '') {
                \App\Models\ExpenseCategory::firstOrCreate(
                    ['expense_category' => 'Daily Expenses', 'type' => $type]
                );
            }

            $descText = $this->is_salary
                ? (! empty(trim((string) $this->description)) ? trim($this->description) : "Salary for {$employeeName}")
                : $this->description;

            // Create staff expense with auto-approval
            $staffExpense = StaffExpense::create([
                'staff_id' => Auth::id(),
                'expense_type' => $type,
                'amount' => $this->amount,
                'description' => $descText,
                'expense_date' => $this->expense_date,
                'status' => 'approved',
                'admin_notes' => 'Auto-approved (Shop Staff)',
                'salary_employee_id' => null,
            ]);

            // Create a regular expense record
            $categoryName = $this->is_salary
                ? 'Staff Expense - '.$type
                : ($isPurchasing ? 'Purchasing Expense - '.$type : 'Staff Expense - '.$type);

            $expense = Expense::create([
                'category' => $categoryName,
                'amount' => $this->amount,
                'description' => $this->is_salary ? $descText : 'Staff: '.Auth::user()->name.' - '.($this->description ?: $type),
                'date' => $this->expense_date,
                'expense_type' => 'daily',
                'salary_employee_id' => null,
                'user_id' => Auth::id(),
            ]);

            // If salary payout, link and deduct from HR Salary (syncing attendance & balance first)
            if ($this->is_salary && $zkEmp) {
                $yearMonth = Carbon::parse($this->expense_date)->format('Y-m');
                $salaryMonth = Carbon::parse($this->expense_date)->startOfMonth()->toDateString();

                // Always re-sync monthly salaries so today's attendance hours and previous saved balance are up to date
                $hrService->syncMonthlySalaries($yearMonth);

                $salary = \App\Models\HrSalary::where('emp_code', (string) $zkEmp->emp_code)
                    ->where('salary_month', $salaryMonth)
                    ->first();

                if (! $salary) {
                    $salary = \App\Models\HrSalary::create([
                        'zk_employee_id' => $zkEmp->id,
                        'emp_code' => (string) $zkEmp->emp_code,
                        'salary_month' => $salaryMonth,
                        'salary_type' => $zkEmp->hrSetting?->salary_type ?: 'daily',
                        'daily_working_hours' => $zkEmp->hrSetting?->daily_working_hours ?: 10.00,
                        'daily_rate' => $zkEmp->hrSetting?->daily_rate ?: 0,
                        'basic_salary' => $zkEmp->hrSetting?->basic_salary ?: 0,
                        'net_salary' => 0,
                        'payment_status' => 'unpaid',
                    ]);
                }

                if ($salary && (float) $this->amount > 0) {
                    $hrService->recordPayment($salary->id, [
                        'amount' => (float) $this->amount,
                        'payment_date' => $this->expense_date,
                        'payment_method' => 'Cash',
                        'reference_no' => "STAFF-EXP-{$staffExpense->id} | EXP-{$expense->id}",
                        'notes' => 'Salary given by '.Auth::user()->name.' via Shop Staff Daily Expenses ('.$type.')',
                    ]);
                }
            }

            // Update cash in hands — subtract expense amount
            $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();

            if ($cashInHandRecord) {
                DB::table('cash_in_hands')
                    ->where('key', 'cash_amount')
                    ->update([
                        'value' => $cashInHandRecord->value - $this->amount,
                        'updated_at' => now(),
                    ]);
            }

            // Update today's POS session if expense is for today
            try {
                if (Carbon::parse($this->expense_date)->isToday()) {
                    $session = POSSession::getTodaySession(Auth::id());
                    if ($session) {
                        $session->expenses = ($session->expenses ?? 0) + $this->amount;
                        $session->save();
                        $session->calculateDifference();
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to update POS session after staff expense: '.$e->getMessage());
            }

            // If purchasing expense, sync to supplier payment if matching supplier exists
            if ($isPurchasing) {
                try {
                    $supplierModel = \App\Services\PurchasingExpenseService::findHandmadeSupplier($type);
                    if ($supplierModel) {
                        \App\Models\HandmadeSupplierPayment::create([
                            'supplier_id' => $supplierModel->id,
                            'user_id' => Auth::id(),
                            'amount' => (float) $this->amount,
                            'payment_method' => 'cash',
                            'payment_date' => $this->expense_date,
                            'notes' => 'From Daily Expenses: '.($this->description ?: $type),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to sync HandmadeSupplierPayment: '.$e->getMessage());
                }
            }

            DB::commit();

            $this->showToast('success', 'Expense added successfully!');
            $this->closeAddModal();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showToast('error', 'Error adding expense: '.$e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->expense_type = '';
        $this->amount = '';
        $this->description = '';
        $this->expense_date = date('Y-m-d');
        $this->is_salary = false;
        $this->salary_employee_id = '';
        $this->selectedStaffSnapshot = null;
        $this->resetValidation();
    }

    public function confirmDelete($expenseId)
    {
        $this->expenseToDelete = $expenseId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->expenseToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteExpense()
    {
        try {
            $expense = StaffExpense::where('staff_id', Auth::id())
                ->where('id', $this->expenseToDelete)
                ->first();

            if ($expense) {
                $amount = (float) $expense->amount;
                $expenseDate = $expense->expense_date;

                // If approved, add back the amount to cash in hand
                if ($expense->status === 'approved') {
                    $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
                    if ($cashInHandRecord) {
                        DB::table('cash_in_hands')
                            ->where('key', 'cash_amount')
                            ->update([
                                'value' => $cashInHandRecord->value + $amount,
                                'updated_at' => now(),
                            ]);
                    }
                }

                // If this was a salary payout, clean up linked HR payment and find exact linked Expense ID
                $linkedExpenseId = null;
                $linkedPayment = \App\Models\HrSalaryPayment::where('reference_no', 'like', "%STAFF-EXP-{$expense->id}%")->first();
                if ($linkedPayment) {
                    if (preg_match('/EXP-(\d+)/', (string) $linkedPayment->reference_no, $matches)) {
                        $linkedExpenseId = (int) $matches[1];
                    }
                    $salary = $linkedPayment->salary;
                    $linkedPayment->delete();
                    if ($salary) {
                        $salary->recalculateBalance();
                    }
                }

                // Delete only the single matching regular Expense record (not all expenses of the same amount on the date)
                if ($linkedExpenseId) {
                    Expense::where('id', $linkedExpenseId)->delete();
                } else {
                    $matchingExpense = Expense::where('user_id', $expense->staff_id)
                        ->where('amount', $expense->amount)
                        ->whereDate('date', $expense->expense_date)
                        ->latest('id')
                        ->first();
                    if ($matchingExpense) {
                        $matchingExpense->delete();
                    }
                }

                // Restore today's POS session expenses if deleted expense was for today
                try {
                    if ($expenseDate && Carbon::parse($expenseDate)->isToday()) {
                        $session = POSSession::getTodaySession(Auth::id());
                        if ($session) {
                            $session->expenses = max(0, ($session->expenses ?? 0) - $amount);
                            $session->save();
                            $session->calculateDifference();
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to update POS session after deleting staff expense: '.$e->getMessage());
                }

                $expense->delete();
                $this->showToast('success', 'Expense deleted and balance restored successfully.');
            } else {
                $this->showToast('error', 'Expense not found.');
            }
        } catch (\Exception $e) {
            $this->showToast('error', 'Error deleting expense: '.$e->getMessage());
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $query = StaffExpense::where('staff_id', Auth::id());

        // Apply date picker calendar filter if selected
        if ($this->dateFilter) {
            $query->whereDate('expense_date', $this->dateFilter);
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('expense_type', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        $expenses = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate totals
        $todayExpenses = StaffExpense::where('staff_id', Auth::id())
            ->whereDate('expense_date', now()->toDateString())
            ->sum('amount');

        $monthExpenses = StaffExpense::where('staff_id', Auth::id())
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return view('livewire.shop-staff.shop-staff-expenses', [
            'expenses' => $expenses,
            'todayExpenses' => $todayExpenses,
            'monthExpenses' => $monthExpenses,
        ]);
    }

    private function showToast($type, $message)
    {
        $bgColors = [
            'success' => '#10b981',
            'error' => '#ef4444',
            'warning' => '#f59e0b',
            'info' => '#3b82f6',
        ];

        $icons = [
            'success' => '✓',
            'error' => '✕',
            'warning' => '⚠',
            'info' => 'ℹ',
        ];

        $bg = $bgColors[$type] ?? $bgColors['info'];
        $icon = $icons[$type] ?? $icons['info'];
        $escapedMessage = addslashes($message);

        $this->js("
            const toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;top:20px;right:20px;background:{$bg};color:white;padding:16px 24px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;font-size:14px;font-weight:600;display:flex;align-items:center;gap:12px;animation:slideIn 0.3s ease;min-width:300px;max-width:500px;';
            toast.innerHTML = '<span style=\"font-size:20px;font-weight:bold;\">{$icon}</span><span>{$escapedMessage}</span>';
            document.body.appendChild(toast);
            const style = document.createElement('style');
            style.textContent = '@keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(400px); opacity: 0; } }';
            document.head.appendChild(style);
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        ");
    }
}
