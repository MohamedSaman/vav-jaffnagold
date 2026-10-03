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

    // Salary Deduction
    public $is_salary_deduction = false;

    public $deduction_staff_id = null;

    public $allStaff = [];

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
        $this->loadExpenses();
        $this->loadCategories();
        $this->loadStaffList();
        $this->date = date('Y-m-d');
    }

    public function loadStaffList()
    {
        $staffUsers = \App\Models\User::where('role', 'staff')->orderBy('name')->get()->map(function ($user) {
            return [
                'id' => 'u_'.$user->id,
                'name' => $user->name,
                'type' => 'user',
                'real_id' => $user->id,
            ];
        });

        $employees = \App\Models\Employee::orderBy('name')->get()->map(function ($emp) {
            return [
                'id' => 'e_'.$emp->id,
                'name' => $emp->name,
                'type' => 'employee',
                'real_id' => $emp->id,
            ];
        });

        $this->allStaff = $staffUsers->concat($employees)->toArray();
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
        $this->filteredTotal = $this->dailyExpenses->sum('amount') + $this->monthlyExpenses->sum('amount');
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

            // Save new category if 'Other' is selected
            if ($this->category === 'Other') {
                if (trim($this->customCategory) !== '') {
                    $this->category = trim($this->customCategory);
                    \App\Models\ExpenseCategory::firstOrCreate([
                        'expense_category' => 'Daily Expenses',
                        'type' => $this->category,
                    ]);
                }
            }

            // 1. Create the primary expense record
            $expense = Expense::create([
                'category' => $this->category,
                'amount' => $this->amount,
                'description' => $this->description,
                'date' => $this->date,
                'expense_type' => 'daily',
            ]);

            // 2. Handle Salary Deduction if enabled
            if ($this->is_salary_deduction && $this->deduction_staff_id) {
                $target = collect($this->allStaff)->firstWhere('id', $this->deduction_staff_id);
                if ($target) {
                    $salaryMonthDate = \Carbon\Carbon::parse($this->date)->startOfMonth()->format('Y-m-d');

                    $salaryData = [
                        'salary_month' => $salaryMonthDate,
                        'salary_type' => 'monthly',
                        'payment_status' => 'pending',
                        'deductions' => 0,
                        'net_salary' => 0,
                        'total_hours' => 0,
                        'overtime_hours' => 0,
                    ];

                    if ($target['type'] === 'user') {
                        $staffUser = \App\Models\User::with('userDetail')->find($target['real_id']);

                        // Create a StaffExpense for tracking/approval
                        \App\Models\StaffExpense::create([
                            'staff_id' => $target['real_id'],
                            'expense_type' => $this->category,
                            'amount' => $this->amount,
                            'description' => 'Salary Deduction: '.($this->description ?? $this->category),
                            'expense_date' => $this->date,
                            'status' => 'approved',
                            'admin_notes' => 'Salary deduction via Admin Expense Management',
                        ]);

                        $salaryData['user_id'] = $target['real_id'];
                        $salaryData['employee_id'] = null;
                        $salaryData['basic_salary'] = $staffUser->userDetail->basic_salary ?? 0;

                        $salary = \App\Models\Salary::firstOrCreate(
                            ['user_id' => $target['real_id'], 'salary_month' => $salaryMonthDate],
                            $salaryData
                        );
                    } else {
                        $employee = \App\Models\Employee::find($target['real_id']);

                        // Note: StaffExpense might not support non-user employees if it checks foreign keys to users table
                        // I'll skip staff_expenses tracking for non-user employees for now OR I'll add employee_id to staff_expenses too.
                        // Actually, Expense record already tracks the description.

                        $salaryData['user_id'] = null;
                        $salaryData['employee_id'] = $target['real_id'];
                        $salaryData['basic_salary'] = $employee->basic_salary ?? 0;

                        $salary = \App\Models\Salary::firstOrCreate(
                            ['employee_id' => $target['real_id'], 'salary_month' => $salaryMonthDate],
                            $salaryData
                        );
                    }

                    $salary->increment('deductions', $this->amount);

                    // Recalculate net salary for the month
                    $salary->net_salary = $salary->basic_salary
                        + ($salary->bonus ?? 0)
                        + ($salary->allowance ?? 0)
                        + ($salary->overtime ?? 0)
                        - $salary->deductions
                        - ($salary->additional_salary ?? 0);
                    $salary->save();
                }
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
                $session = POSSession::getTodaySession(Auth::id());
                if (! $session) {
                    $session = POSSession::openSession(Auth::id(), 0);
                }
                $session->expenses = ($session->expenses ?? 0) + $this->amount;
                $session->save();
                $session->calculateDifference();
            }

            DB::commit();

            $this->reset(['category', 'amount', 'description', 'is_salary_deduction', 'deduction_staff_id', 'customCategory']);
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
            Expense::findOrFail($this->expenseToDelete)->delete();
            $this->loadExpenses();
            $this->js("swal.fire('Deleted!', 'Expense has been deleted.', 'success')");
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
            // For daily, use today's date
            $updateData['date'] = now();
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
