<?php

namespace App\Livewire\ShopStaff;

use App\Models\SalaryEmployee;
use App\Models\Expense;
use App\Models\StaffExpense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('components.layouts.shop-staff')]
#[Title('Shop Staff Salary')]
class ShopStaffSalary extends Component
{
    use WithPagination;

    // Employee Form
    public $name = '';
    public $phone = '';
    public $address = '';
    public $daily_salary = '';
    public $opening_balance = 0;

    // Detail Modal
    public $selectedEmployee = null;
    public $dailyRecords = [];
    public $filterMonth;
    public $monthlyEarned = 0;
    public $monthlyCollected = 0;

    // Quick Payout
    public $payoutAmount = '';
    public $payoutDate = '';

    // Modal states
    public $showAddModal = false;
    public $showDetailsModal = false;
    public $showPayoutModal = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->payoutDate = date('Y-m-d');
        $this->filterMonth = date('Y-m');
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->phone = '';
        $this->address = '';
        $this->daily_salary = '';
        $this->opening_balance = 0;
        $this->resetValidation();
    }

    public function saveEmployee()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'daily_salary' => 'required|numeric|min:0',
            'opening_balance' => 'required|numeric',
        ]);

        SalaryEmployee::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'daily_salary' => $this->daily_salary,
            'opening_balance' => $this->opening_balance,
        ]);

        $this->showToast('success', 'Employee added successfully!');
        $this->closeAddModal();
    }

    public function viewDetails($id)
    {
        $this->selectedEmployee = SalaryEmployee::find($id);
        $this->loadSalaryRecords();
        $this->showDetailsModal = true;
    }

    public function updatedFilterMonth()
    {
        if ($this->selectedEmployee) {
            $this->loadSalaryRecords();
        }
    }

    private function loadSalaryRecords()
    {
        if (!$this->selectedEmployee) return;

        $id = $this->selectedEmployee->id;
        $startOfMonth = \Carbon\Carbon::parse($this->filterMonth)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($this->filterMonth)->endOfMonth();

        // Get all unique attendance dates within the selected month
        $expenseDates = Expense::where('salary_employee_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'));

        $staffDates = StaffExpense::where('salary_employee_id', $id)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->pluck('expense_date')
            ->map(fn($d) => $d->format('Y-m-d'));

        $allAttendanceDates = $expenseDates->merge($staffDates)->unique()->sortDesc();

        $this->dailyRecords = [];
        $this->monthlyEarned = 0;
        $this->monthlyCollected = 0;

        // Add Attendance Records
        foreach ($allAttendanceDates as $date) {
            // Collected amount for this specific date
            $collected = Expense::where('salary_employee_id', $id)
                ->whereDate('date', $date)
                ->sum('amount');

            $earned = $this->selectedEmployee->daily_salary;
            
            $this->dailyRecords[] = [
                'date' => $date,
                'earned' => $earned,
                'collected' => $collected,
                'type' => 'daily',
            ];

            $this->monthlyEarned += $earned;
            $this->monthlyCollected += $collected;
        }
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedEmployee = null;
        $this->dailyRecords = [];
        $this->monthlyEarned = 0;
        $this->monthlyCollected = 0;
    }

    public function openPayoutModal($id)
    {
        $this->selectedEmployee = SalaryEmployee::find($id);
        $this->payoutAmount = '';
        $this->payoutDate = date('Y-m-d');
        $this->showPayoutModal = true;
    }

    public function closePayoutModal()
    {
        $this->showPayoutModal = false;
        $this->selectedEmployee = null;
    }

    public function savePayout()
    {
        $this->validate([
            'payoutAmount' => 'required|numeric|min:0',
            'payoutDate' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $employee = $this->selectedEmployee;

            // Create staff expense with auto-approval
            StaffExpense::create([
                'staff_id' => Auth::id(),
                'expense_type' => 'Salary Payout',
                'amount' => $this->payoutAmount,
                'description' => "Salary for {$employee->name}",
                'expense_date' => $this->payoutDate,
                'status' => 'approved',
                'admin_notes' => 'Auto-approved via Shop Staff Salary Dashboard',
                'salary_employee_id' => $employee->id,
            ]);

            // Create a regular expense record
            Expense::create([
                'category' => 'Salary Payout',
                'amount' => $this->payoutAmount,
                'description' => "Salary for {$employee->name} (Recorded by: " . Auth::user()->name . ")",
                'date' => $this->payoutDate,
                'expense_type' => 'daily',
                'salary_employee_id' => $employee->id,
                'user_id' => Auth::id(),
            ]);

            // Update cash in hands
            $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
            if ($cashInHandRecord) {
                DB::table('cash_in_hands')
                    ->where('key', 'cash_amount')
                    ->update([
                        'value' => $cashInHandRecord->value - $this->payoutAmount,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            $this->showToast('success', 'Payout recorded successfully!');
            $this->closePayoutModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showToast('error', $e->getMessage());
        }
    }

    public function deleteEmployee($id)
    {
        $employee = SalaryEmployee::find($id);
        if ($employee) {
            $employee->update(['is_active' => false]);
            $this->showToast('success', 'Employee deleted successfully!');
        }
    }

    public function render()
    {
        $employees = SalaryEmployee::where('is_active', true)->paginate(10);
        
        $employeeData = collect($employees->items())->map(function($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'daily_salary' => $emp->daily_salary,
                'total_earned' => $emp->calculateTotalEarned(),
                'total_collected' => $emp->calculateTotalCollected(),
                'balance' => $emp->calculateBalance(),
            ];
        });

        return view('livewire.shop-staff.shop-staff-salary', [
            'employees' => $employees,
            'employeeData' => $employeeData,
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
