<?php

namespace App\Livewire\ShopStaff;

use App\Models\HandmadeSupplier;
use App\Models\StaffExpense;
use App\Services\PurchasingExpenseService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.shop-staff')]
#[Title('Purchasing Expenses')]
class PurchasingExpenses extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';

    public $supplierFilter = 'all'; // all, Mano anna, RAMESH ANNA, Sutha anna, other

    public $dateFilter = 'all'; // all, today, yesterday, this_week, this_month, last_month, custom

    public $customStartDate = '';

    public $customEndDate = '';

    public $perPage = 20;

    // Add Modal Form State
    public $showAddModal = false;

    public $supplier_name = '';

    public $amount = '';

    public $expense_date = '';

    public $purpose = 'Raw Gold / Poun Purchase';

    public $description = '';

    // Delete Modal State
    public $showDeleteModal = false;

    public $expenseToDelete = null;

    // Detail Modal State
    public $showDetailModal = false;

    public $selectedExpense = null;

    // Predefined quick suppliers
    public $quickSuppliers = ['Mano anna', 'RAMESH ANNA', 'Sutha anna'];

    public $registeredSuppliers = [];

    public $purposeCategories = [];

    protected function rules(): array
    {
        return [
            'supplier_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'purpose' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function mount()
    {
        $this->expense_date = date('Y-m-d');
        $this->purposeCategories = PurchasingExpenseService::PURPOSE_CATEGORIES;
        $this->loadSuppliers();
    }

    public function loadSuppliers()
    {
        $this->registeredSuppliers = HandmadeSupplier::where('is_active', true)
            ->pluck('name')
            ->toArray();

        // Ensure primary suppliers are always included
        foreach ($this->quickSuppliers as $sup) {
            if (! in_array($sup, $this->registeredSuppliers)) {
                $this->registeredSuppliers[] = $sup;
            }
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSupplierFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function setSupplierFilter($supplier)
    {
        $this->supplierFilter = $supplier;
        $this->resetPage();
    }

    public function setDateFilter($filter)
    {
        $this->dateFilter = $filter;
        $this->resetPage();
    }

    public function quickSelectSupplier($name)
    {
        $this->supplier_name = $name;
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->loadSuppliers();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->supplier_name = '';
        $this->amount = '';
        $this->expense_date = date('Y-m-d');
        $this->purpose = 'Raw Gold / Poun Purchase';
        $this->description = '';
        $this->resetValidation();
    }

    public function addPurchasingExpense()
    {
        $this->validate();

        try {
            PurchasingExpenseService::createPurchasingExpense([
                'supplier_name' => $this->supplier_name,
                'amount' => $this->amount,
                'expense_date' => $this->expense_date,
                'purpose' => $this->purpose,
                'description' => $this->description,
                'staff_id' => Auth::id(),
            ]);

            $this->showToast('success', "Purchasing expense for {$this->supplier_name} recorded successfully!");
            $this->closeAddModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->showToast('error', 'Error adding purchasing expense: '.$e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->expenseToDelete = $id;
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
            $success = PurchasingExpenseService::deletePurchasingExpense($this->expenseToDelete);

            if ($success) {
                $this->showToast('success', 'Purchasing expense deleted and cash restored.');
            } else {
                $this->showToast('error', 'Expense could not be found.');
            }
        } catch (\Exception $e) {
            $this->showToast('error', 'Error deleting expense: '.$e->getMessage());
        }

        $this->cancelDelete();
    }

    public function viewDetails($id)
    {
        $this->selectedExpense = StaffExpense::with('staff')->find($id);
        if ($this->selectedExpense) {
            $this->showDetailModal = true;
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedExpense = null;
    }

    public function render()
    {
        // Base query for all purchasing expenses
        $baseQuery = PurchasingExpenseService::getPurchasingExpensesQuery();

        // 1. Calculate summary KPIs across all records
        $todayTotal = (clone $baseQuery)
            ->whereDate('expense_date', now()->toDateString())
            ->sum('amount');

        $monthTotal = (clone $baseQuery)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        $allTimeTotal = (clone $baseQuery)->sum('amount');
        $totalCount = (clone $baseQuery)->count();

        // Supplier specific totals
        $manoQuery = (clone $baseQuery)->where(function ($q) {
            $q->where('expense_type', 'LIKE', '%mano%')
                ->orWhere('description', 'LIKE', '%mano%');
        });
        $manoTotal = $manoQuery->sum('amount');
        $manoCount = $manoQuery->count();

        $rameshQuery = (clone $baseQuery)->where(function ($q) {
            $q->where('expense_type', 'LIKE', '%ramesh%')
                ->orWhere('description', 'LIKE', '%ramesh%');
        });
        $rameshTotal = $rameshQuery->sum('amount');
        $rameshCount = $rameshQuery->count();

        $suthaQuery = (clone $baseQuery)->where(function ($q) {
            $q->where('expense_type', 'LIKE', '%sutha%')
                ->orWhere('expense_type', 'LIKE', '%suthu%')
                ->orWhere('description', 'LIKE', '%sutha%')
                ->orWhere('description', 'LIKE', '%suthu%');
        });
        $suthaTotal = $suthaQuery->sum('amount');
        $suthaCount = $suthaQuery->count();

        // 2. Build filtered table query
        $tableQuery = clone $baseQuery;

        // Apply supplier filter
        if ($this->supplierFilter === 'Mano anna') {
            $tableQuery->where(function ($q) {
                $q->where('expense_type', 'LIKE', '%mano%')
                    ->orWhere('description', 'LIKE', '%mano%');
            });
        } elseif ($this->supplierFilter === 'RAMESH ANNA') {
            $tableQuery->where(function ($q) {
                $q->where('expense_type', 'LIKE', '%ramesh%')
                    ->orWhere('description', 'LIKE', '%ramesh%');
            });
        } elseif ($this->supplierFilter === 'Sutha anna') {
            $tableQuery->where(function ($q) {
                $q->where('expense_type', 'LIKE', '%sutha%')
                    ->orWhere('expense_type', 'LIKE', '%suthu%')
                    ->orWhere('description', 'LIKE', '%sutha%')
                    ->orWhere('description', 'LIKE', '%suthu%');
            });
        } elseif ($this->supplierFilter === 'other') {
            $tableQuery->where(function ($q) {
                $q->where('expense_type', 'NOT LIKE', '%mano%')
                    ->where('expense_type', 'NOT LIKE', '%ramesh%')
                    ->where('expense_type', 'NOT LIKE', '%sutha%')
                    ->where('expense_type', 'NOT LIKE', '%suthu%')
                    ->where('description', 'NOT LIKE', '%mano%')
                    ->where('description', 'NOT LIKE', '%ramesh%')
                    ->where('description', 'NOT LIKE', '%sutha%')
                    ->where('description', 'NOT LIKE', '%suthu%');
            });
        }

        // Apply date filter
        if ($this->dateFilter === 'today') {
            $tableQuery->whereDate('expense_date', now()->toDateString());
        } elseif ($this->dateFilter === 'yesterday') {
            $tableQuery->whereDate('expense_date', now()->subDay()->toDateString());
        } elseif ($this->dateFilter === 'this_week') {
            $tableQuery->whereBetween('expense_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($this->dateFilter === 'this_month') {
            $tableQuery->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year);
        } elseif ($this->dateFilter === 'last_month') {
            $tableQuery->whereMonth('expense_date', now()->subMonth()->month)
                ->whereYear('expense_date', now()->subMonth()->year);
        } elseif ($this->dateFilter === 'custom' && $this->customStartDate && $this->customEndDate) {
            $tableQuery->whereBetween('expense_date', [$this->customStartDate, $this->customEndDate]);
        }

        // Apply search filter
        if ($this->search) {
            $s = trim($this->search);
            $tableQuery->where(function ($q) use ($s) {
                $q->where('expense_type', 'LIKE', "%{$s}%")
                    ->orWhere('description', 'LIKE', "%{$s}%")
                    ->orWhere('amount', 'LIKE', "%{$s}%");
            });
        }

        $expenses = $tableQuery->with('staff')
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.shop-staff.purchasing-expenses', [
            'expenses' => $expenses,
            'todayTotal' => $todayTotal,
            'monthTotal' => $monthTotal,
            'allTimeTotal' => $allTimeTotal,
            'totalCount' => $totalCount,
            'manoTotal' => $manoTotal,
            'manoCount' => $manoCount,
            'rameshTotal' => $rameshTotal,
            'rameshCount' => $rameshCount,
            'suthaTotal' => $suthaTotal,
            'suthaCount' => $suthaCount,
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
