<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithDynamicLayout;
use App\Models\HandmadeSupplier;
use App\Models\StaffExpense;
use App\Services\PurchasingExpenseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Purchasing Expenses - Admin')]
class PurchasingExpenses extends Component
{
    use WithDynamicLayout;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    #[Url]
    public $search = '';

    #[Url]
    public $supplierFilter = 'all'; // 'all', 'MANO ANNA', 'KANIY MANO', etc.

    #[Url]
    public $monthFilter = 'current'; // 'current', 'last_month', 'custom_month', 'all', 'custom_range', 'today', 'this_week'

    #[Url]
    public $selectedMonth = ''; // YYYY-MM string, e.g. '2026-10'

    #[Url]
    public $customStartDate = '';

    #[Url]
    public $customEndDate = '';

    #[Url]
    public $purposeFilter = 'all';

    #[Url]
    public $statusFilter = 'all';

    public $perPage = 20;

    // Add Expense Modal
    public $showAddModal = false;

    public $supplier_name = '';

    public $amount = '';

    public $expense_date = '';

    public $purpose = 'Raw Gold / Poun Purchase';

    public $description = '';

    // Delete Modal
    public $showDeleteModal = false;

    public $expenseToDelete = null;

    // Detail Modal
    public $showDetailModal = false;

    public $selectedExpense = null;

    // Suppliers & Categories
    public $registeredSuppliers = [];

    public $purposeCategories = [];

    public $quickSuppliers = ['MANO ANNA', 'KANIY MANO', 'RAMESH ANNA', 'SUTHA ANNA'];

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
        if (empty($this->selectedMonth)) {
            $this->selectedMonth = date('Y-m');
        }
        if (empty($this->monthFilter)) {
            $this->monthFilter = 'current';
        }
        $this->expense_date = date('Y-m-d');
        $this->purposeCategories = PurchasingExpenseService::PURPOSE_CATEGORIES;
        $this->loadSuppliers();
    }

    public function loadSuppliers()
    {
        $this->registeredSuppliers = HandmadeSupplier::where('is_active', true)
            ->pluck('name')
            ->toArray();

        foreach (['MANO ANNA', 'KANIY MANO', 'RAMESH ANNA', 'SUTHA ANNA'] as $sup) {
            $exists = false;
            foreach ($this->registeredSuppliers as $reg) {
                if (strtoupper(trim($reg)) === strtoupper(trim($sup))) {
                    $exists = true;
                    break;
                }
            }
            if (! $exists) {
                $this->registeredSuppliers[] = $sup;
            }
        }
    }

    public function setMonthFilter($filter, $monthValue = null)
    {
        $this->monthFilter = $filter;
        if ($filter === 'current') {
            $this->selectedMonth = date('Y-m');
            $this->customStartDate = '';
            $this->customEndDate = '';
        } elseif ($filter === 'last_month') {
            $this->selectedMonth = Carbon::now()->subMonth()->format('Y-m');
            $this->customStartDate = '';
            $this->customEndDate = '';
        } elseif ($filter === 'custom_month' && $monthValue) {
            $this->selectedMonth = $monthValue;
        } elseif ($filter === 'all') {
            $this->customStartDate = '';
            $this->customEndDate = '';
        }
        $this->resetPage();
    }

    public function selectMonth($monthStr)
    {
        $this->monthFilter = 'custom_month';
        $this->selectedMonth = $monthStr;
        $this->resetPage();
    }

    public function updatedSelectedMonth($value)
    {
        if (! empty($value)) {
            $this->monthFilter = 'custom_month';
        } else {
            $this->monthFilter = 'all';
        }
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSupplierFilter()
    {
        $this->resetPage();
    }

    public function updatedPurposeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function setSupplierFilter($supplier)
    {
        $this->supplierFilter = $supplier;
        $this->resetPage();
    }

    public function clearAllFilters()
    {
        $this->search = '';
        $this->supplierFilter = 'all';
        $this->monthFilter = 'current';
        $this->selectedMonth = date('Y-m');
        $this->customStartDate = '';
        $this->customEndDate = '';
        $this->purposeFilter = 'all';
        $this->statusFilter = 'all';
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->reset(['supplier_name', 'amount', 'description']);
        $this->expense_date = date('Y-m-d');
        $this->purpose = 'Raw Gold / Poun Purchase';
        $this->loadSuppliers();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->reset(['supplier_name', 'amount', 'description']);
    }

    public function selectQuickSupplier($name)
    {
        $this->supplier_name = $name;
    }

    public function addExpense()
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

            $this->showAddModal = false;
            $this->reset(['supplier_name', 'amount', 'description']);
            $this->loadSuppliers();
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Purchasing expense created successfully and synced with supplier ledger.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Error recording purchasing expense: '.$e->getMessage(),
            ]);
        }
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

    public function confirmDelete($id)
    {
        $this->expenseToDelete = StaffExpense::find($id);
        if ($this->expenseToDelete) {
            $this->showDeleteModal = true;
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->expenseToDelete = null;
    }

    public function deleteExpense()
    {
        if (! $this->expenseToDelete) {
            return;
        }

        try {
            $success = PurchasingExpenseService::deletePurchasingExpense($this->expenseToDelete->id, null);

            if ($success) {
                $this->dispatch('alert', [
                    'type' => 'success',
                    'message' => 'Purchasing expense voucher deleted and cash restored.',
                ]);
            } else {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Expense could not be found.',
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Error deleting expense: '.$e->getMessage(),
            ]);
        }

        $this->cancelDelete();
    }

    /**
     * Apply the active month/date filter to an Eloquent query.
     */
    protected function applyDateFilterToQuery($query)
    {
        if ($this->monthFilter === 'current') {
            $now = Carbon::now();
            $query->whereYear('expense_date', $now->year)->whereMonth('expense_date', $now->month);
        } elseif ($this->monthFilter === 'last_month') {
            $last = Carbon::now()->subMonth();
            $query->whereYear('expense_date', $last->year)->whereMonth('expense_date', $last->month);
        } elseif ($this->monthFilter === 'custom_month' && ! empty($this->selectedMonth)) {
            try {
                $parsed = Carbon::parse($this->selectedMonth.'-01');
                $query->whereYear('expense_date', $parsed->year)->whereMonth('expense_date', $parsed->month);
            } catch (\Exception $e) {
                $now = Carbon::now();
                $query->whereYear('expense_date', $now->year)->whereMonth('expense_date', $now->month);
            }
        } elseif ($this->monthFilter === 'today') {
            $query->whereDate('expense_date', Carbon::today());
        } elseif ($this->monthFilter === 'yesterday') {
            $query->whereDate('expense_date', Carbon::yesterday());
        } elseif ($this->monthFilter === 'this_week') {
            $query->whereBetween('expense_date', [
                Carbon::now()->startOfWeek()->toDateString(),
                Carbon::now()->endOfWeek()->toDateString(),
            ]);
        } elseif ($this->monthFilter === 'custom_range' && ! empty($this->customStartDate) && ! empty($this->customEndDate)) {
            $query->whereBetween('expense_date', [
                $this->customStartDate,
                $this->customEndDate,
            ]);
        }

        return $query;
    }

    public function render()
    {
        $baseQuery = PurchasingExpenseService::getPurchasingExpensesQuery();

        // 1. Overall & High-level KPIs
        $todayTotal = (clone $baseQuery)->whereDate('expense_date', Carbon::today())->sum('amount');
        $allTimeTotal = (clone $baseQuery)->sum('amount');
        $allTimeCount = (clone $baseQuery)->count();

        // Filtered Period KPIs
        $periodQuery = clone $baseQuery;
        $this->applyDateFilterToQuery($periodQuery);
        $filteredPeriodTotal = (float) (clone $periodQuery)->sum('amount');
        $filteredPeriodCount = (int) (clone $periodQuery)->count();

        // Current Month Total (always visible for benchmark)
        $currentMonthTotal = (clone $baseQuery)
            ->whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->sum('amount');

        // 2. Historical Monthly Breakdown (for the monthly breakdown overview)
        $monthlyBreakdown = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month_key, COUNT(*) as voucher_count, SUM(amount) as total_amount")
            ->groupBy('month_key')
            ->orderByDesc('month_key')
            ->get()
            ->map(function ($row) {
                $carbon = Carbon::parse($row->month_key.'-01');
                return [
                    'key' => $row->month_key,
                    'label' => $carbon->format('F Y'),
                    'short_label' => $carbon->format('M Y'),
                    'count' => (int) $row->voucher_count,
                    'total' => (float) $row->total_amount,
                ];
            });

        // 3. Local Supplier Breakdown for the selected period
        $this->loadSuppliers();
        $supplierSummaries = [];
        $allKnownKeywords = [];

        foreach ($this->registeredSuppliers as $supName) {
            $keywords = PurchasingExpenseService::getSupplierKeywords($supName);
            $allKnownKeywords = array_merge($allKnownKeywords, $keywords, [strtolower($supName)]);

            // Filter supplier within the selected month/period
            $q = clone $periodQuery;
            PurchasingExpenseService::applySupplierFilter($q, $supName);

            $total = (float) $q->sum('amount');
            $count = (int) $q->count();
            $color = PurchasingExpenseService::getSupplierColor($supName);

            $supplierSummaries[] = [
                'name' => $supName,
                'total' => $total,
                'count' => $count,
                'color' => $color,
            ];
        }

        // 4. Build Filtered Table Query
        $tableQuery = clone $periodQuery;

        // Apply supplier filter
        if ($this->supplierFilter !== 'all') {
            if ($this->supplierFilter === 'other') {
                $allKnownKeywords = array_unique(array_filter($allKnownKeywords));
                $tableQuery->where(function ($q) use ($allKnownKeywords) {
                    foreach ($allKnownKeywords as $k) {
                        $q->where('expense_type', 'NOT LIKE', "%{$k}%")
                            ->where('description', 'NOT LIKE', "%{$k}%");
                    }
                });
            } else {
                PurchasingExpenseService::applySupplierFilter($tableQuery, $this->supplierFilter);
            }
        }

        // Apply purpose filter
        if ($this->purposeFilter !== 'all') {
            $tableQuery->where('description', 'LIKE', "%{$this->purposeFilter}%");
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $tableQuery->where('status', $this->statusFilter);
        }

        // Apply search keyword filter
        if (! empty(trim($this->search))) {
            $term = trim($this->search);
            $tableQuery->where(function ($q) use ($term) {
                $q->where('expense_type', 'LIKE', "%{$term}%")
                    ->orWhere('description', 'LIKE', "%{$term}%")
                    ->orWhere('amount', 'LIKE', "%{$term}%")
                    ->orWhere('id', 'LIKE', "%{$term}%")
                    ->orWhereHas('staff', function ($sq) use ($term) {
                        $sq->where('name', 'LIKE', "%{$term}%");
                    });
            });
        }

        $tableExpenses = $tableQuery
            ->with('staff')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        // Friendly label of current active filter
        $activeMonthLabel = 'Current Month ('.now()->format('F Y').')';
        if ($this->monthFilter === 'last_month') {
            $activeMonthLabel = 'Last Month ('.now()->subMonth()->format('F Y').')';
        } elseif ($this->monthFilter === 'custom_month' && ! empty($this->selectedMonth)) {
            $activeMonthLabel = Carbon::parse($this->selectedMonth.'-01')->format('F Y');
        } elseif ($this->monthFilter === 'all') {
            $activeMonthLabel = 'All Time History';
        } elseif ($this->monthFilter === 'today') {
            $activeMonthLabel = 'Today ('.now()->format('d M Y').')';
        } elseif ($this->monthFilter === 'this_week') {
            $activeMonthLabel = 'This Week';
        } elseif ($this->monthFilter === 'custom_range') {
            $activeMonthLabel = "Range: {$this->customStartDate} to {$this->customEndDate}";
        }

        return view('livewire.admin.purchasing-expenses', [
            'expenses' => $tableExpenses,
            'todayTotal' => $todayTotal,
            'allTimeTotal' => $allTimeTotal,
            'allTimeCount' => $allTimeCount,
            'filteredPeriodTotal' => $filteredPeriodTotal,
            'filteredPeriodCount' => $filteredPeriodCount,
            'currentMonthTotal' => $currentMonthTotal,
            'supplierSummaries' => $supplierSummaries,
            'monthlyBreakdown' => $monthlyBreakdown,
            'activeMonthLabel' => $activeMonthLabel,
        ])->layout($this->layout);
    }
}
