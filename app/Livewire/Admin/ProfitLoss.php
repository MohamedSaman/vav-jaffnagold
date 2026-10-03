<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithDynamicLayout;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\HandmadePurchase;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\StaffExpense;
use App\Services\ExpenseDepositSyncService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Profit & Loss (NPL) Statement')]
class ProfitLoss extends Component
{
    use WithDynamicLayout;

    // Date range filters
    public $startDate;

    public $endDate;

    public $preset = 'this_month';

    // Active Tab Navigation
    public $activeTab = 'summary'; // 'summary', 'purchasing', 'sales', 'expenses', 'deposits'

    // Table Search & Filters
    public $purchaseSearch = '';

    public $purchaseTypeFilter = 'all'; // 'all', 'regular', 'handmade'

    public $saleSearch = '';

    public $saleTypeFilter = 'all';

    public $expenseSearch = '';

    public $expenseCategoryFilter = 'all';

    public $expenseTypeFilter = 'all'; // 'all', 'operating_only', 'deposits_only'

    public $depositSearch = '';

    public $depositMethodFilter = 'all';

    // Core Summary Metrics
    public $grossSales = 0;

    public $totalSalesCount = 0;

    public $totalDiscounts = 0;

    public $totalReturns = 0;

    public $netRevenue = 0;

    public $totalCOGS = 0;

    public $grossProfit = 0;

    public $grossProfitPercentage = 0;

    // Purchasing Value & Stats
    public $totalPurchasingValue = 0;

    public $totalPurchasingCount = 0;

    public $totalPurchasingPaid = 0;

    public $totalPurchasingDue = 0;

    // Operating Expenses Stats (Excluded Seedu / Undiyal / Retained Deposits)
    public $totalExpenses = 0;

    public $shopExpenses = 0;

    public $staffExpenses = 0;

    public $totalExpensesCount = 0;

    // Retained Savings & Cash Deposits (Seedu, Undiyal, Bank, Card)
    public $totalDeposits = 0;

    public $seeduSavings = 0;

    public $undiyalSavings = 0;

    public $bankSavings = 0;

    public $cardSavings = 0;

    public $otherSavings = 0;

    public $totalDepositsCount = 0;

    public $depositBreakdown = [];

    // Stock In Hand Metrics
    public $totalStockCostValue = 0;

    public $totalStockSellingValue = 0;

    public $availableStockUnits = 0;

    public $totalStockUnits = 0;

    // Net Profit & Loss (NPL)
    public $netProfit = 0;

    public $netProfitPercentage = 0;

    public $cashflowNet = 0;

    // Breakdowns
    public $revenueBreakdown = [];

    public $paymentMethodWiseRevenue = [];

    public $expenseBreakdown = [];

    public $expenseCategoriesList = [];

    public $monthlyTrends = [];

    public function mount()
    {
        // Default to current month: 1st of month to today (e.g. Sep 1 to Today)
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->preset = 'this_month';

        $this->loadData();
    }

    public function updatedStartDate()
    {
        $this->preset = 'custom';
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->preset = 'custom';
        $this->loadData();
    }

    public function setDatePreset(string $preset)
    {
        $this->preset = $preset;

        switch ($preset) {
            case 'today':
                $this->startDate = Carbon::now()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = Carbon::now()->subDay()->format('Y-m-d');
                $this->endDate = Carbon::now()->subDay()->format('Y-m-d');
                break;
            case 'this_week':
                $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->startDate = Carbon::now()->subDays(29)->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'this_year':
                $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'all_time':
                $this->startDate = null;
                $this->endDate = null;
                break;
        }

        $this->loadData();
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function resetFilters()
    {
        $this->setDatePreset('this_month');
        $this->purchaseSearch = '';
        $this->purchaseTypeFilter = 'all';
        $this->saleSearch = '';
        $this->saleTypeFilter = 'all';
        $this->expenseSearch = '';
        $this->expenseCategoryFilter = 'all';
        $this->expenseTypeFilter = 'all';
        $this->depositSearch = '';
        $this->depositMethodFilter = 'all';
    }

    public function loadData()
    {
        try {
            $this->calculateSalesAndRevenue();
            $this->calculatePurchasingValue();
            $this->calculateExpenses();
            $this->calculateDeposits();
            $this->calculateStockInHand();
            $this->calculateNPL();
            $this->calculateMonthlyTrends();
        } catch (\Exception $e) {
            Log::error('P&L Calculation Error: '.$e->getMessage());
        }
    }

    /**
     * Calculate Sales, Revenue, Discounts, and COGS for the chosen period
     */
    private function calculateSalesAndRevenue(): void
    {
        $salesQuery = Sale::query()->where('status', 'confirm');

        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $salesQuery->whereBetween('created_at', [$start, $end]);
        } elseif ($this->startDate) {
            $salesQuery->where('created_at', '>=', Carbon::parse($this->startDate)->startOfDay());
        } elseif ($this->endDate) {
            $salesQuery->where('created_at', '<=', Carbon::parse($this->endDate)->endOfDay());
        }

        $this->grossSales = (float) $salesQuery->sum('total_amount');
        $this->totalSalesCount = (int) $salesQuery->count();
        $this->totalDiscounts = (float) $salesQuery->sum('discount_amount');

        // Revenue Breakdown by Sale Channel / Type
        $salesList = $salesQuery->get();
        $this->revenueBreakdown = [];
        foreach ($salesList as $sale) {
            $type = ucfirst($sale->sale_type ?: ($sale->order_source ?: 'Store POS'));
            if (! isset($this->revenueBreakdown[$type])) {
                $this->revenueBreakdown[$type] = [
                    'count' => 0,
                    'amount' => 0.0,
                ];
            }
            $this->revenueBreakdown[$type]['count']++;
            $this->revenueBreakdown[$type]['amount'] += (float) $sale->total_amount;
        }

        // Returns Calculation
        $returnsQuery = DB::table('returns_products')
            ->join('sales', 'returns_products.sale_id', '=', 'sales.id')
            ->where('sales.status', 'confirm');

        if ($this->startDate && $this->endDate) {
            $returnsQuery->whereBetween('sales.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }
        $this->totalReturns = (float) ($returnsQuery->sum('returns_products.total_amount') ?? 0);

        // Net Sales Revenue
        $this->netRevenue = max(0, $this->grossSales - $this->totalReturns);

        // Cost of Goods Sold (COGS) for confirmed sales
        $cogsQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('product_prices', 'sale_items.product_id', '=', 'product_prices.product_id')
            ->where('sales.status', 'confirm')
            ->select(DB::raw('SUM(COALESCE(product_prices.supplier_price, 0) * sale_items.quantity) as total_cogs'));

        if ($this->startDate && $this->endDate) {
            $cogsQuery->whereBetween('sales.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }
        $cogsResult = $cogsQuery->first();
        $this->totalCOGS = (float) ($cogsResult ? ($cogsResult->total_cogs ?? 0) : 0);

        // Gross Profit (Trading margin = Net Revenue - COGS)
        $this->grossProfit = $this->netRevenue - $this->totalCOGS;
        $this->grossProfitPercentage = $this->netRevenue > 0
            ? round(($this->grossProfit / $this->netRevenue) * 100, 2)
            : 0;

        // Payment Methods Breakdown
        $paymentQuery = Payment::query();
        if ($this->startDate && $this->endDate) {
            $paymentQuery->whereBetween('payment_date', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }
        $paymentMethods = $paymentQuery->groupBy('payment_method')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->get();

        $this->paymentMethodWiseRevenue = [];
        foreach ($paymentMethods as $m) {
            $name = $m->payment_method ? ucwords(str_replace('_', ' ', $m->payment_method)) : 'Cash';
            $this->paymentMethodWiseRevenue[$name] = (float) $m->total;
        }
    }

    /**
     * Calculate Total Purchasing Value (Regular Purchase Orders + Handmade Purchases)
     */
    private function calculatePurchasingValue(): void
    {
        // 1. Regular Purchase Orders
        $poQuery = PurchaseOrder::query();
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $poQuery->whereBetween('created_at', [$start, $end]);
        }
        $poTotal = (float) $poQuery->sum('total_amount');
        $poDue = (float) $poQuery->sum('due_amount');
        $poCount = (int) $poQuery->count();
        $poPaid = max(0, $poTotal - $poDue);

        // 2. Handmade Purchases (if table exists)
        $hpTotal = 0.0;
        $hpDue = 0.0;
        $hpPaid = 0.0;
        $hpCount = 0;

        if (class_exists(HandmadePurchase::class)) {
            $hpQuery = HandmadePurchase::query();
            if ($this->startDate && $this->endDate) {
                $start = Carbon::parse($this->startDate)->startOfDay();
                $end = Carbon::parse($this->endDate)->endOfDay();
                $hpQuery->whereBetween('created_at', [$start, $end]);
            }
            $hpTotal = (float) $hpQuery->sum('total_amount');
            $hpDue = (float) $hpQuery->sum('due_amount');
            $hpPaid = (float) $hpQuery->sum('paid_amount');
            $hpCount = (int) $hpQuery->count();
        }

        $this->totalPurchasingValue = $poTotal + $hpTotal;
        $this->totalPurchasingCount = $poCount + $hpCount;
        $this->totalPurchasingPaid = $poPaid + $hpPaid;
        $this->totalPurchasingDue = $poDue + $hpDue;
    }

    /**
     * Calculate Operating Expenses & Staff Expenses
     * Excludes Seedu, Undiyal, and other retained deposits/savings from operating expenses
     */
    private function calculateExpenses(): void
    {
        $expenseQuery = Expense::query();
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $expenseQuery->where(function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('created_at', [$start, $end]);
            });
        }

        $expenses = $expenseQuery->get();
        $this->shopExpenses = 0.0;
        $this->totalExpensesCount = 0;
        $this->expenseBreakdown = [];

        foreach ($expenses as $exp) {
            // If this expense is a deposit / saving (Seedu, Undiyal, etc.), exclude it from Operating Expenses
            if (ExpenseDepositSyncService::isDeposit($exp->category, $exp->description)) {
                continue;
            }

            $amount = (float) $exp->amount;
            $this->shopExpenses += $amount;
            $this->totalExpensesCount++;

            $cat = $exp->category ?: 'General Operational';
            if (! isset($this->expenseBreakdown[$cat])) {
                $this->expenseBreakdown[$cat] = 0.0;
            }
            $this->expenseBreakdown[$cat] += $amount;
        }

        $this->totalExpenses = $this->shopExpenses;

        // Approved Staff Expenses (excluding any deposits)
        $this->staffExpenses = 0.0;
        if (class_exists(StaffExpense::class)) {
            $staffExpQuery = StaffExpense::query()->where('status', 'approved');
            if ($this->startDate && $this->endDate) {
                $staffExpQuery->whereBetween('expense_date', [
                    Carbon::parse($this->startDate)->toDateString(),
                    Carbon::parse($this->endDate)->toDateString(),
                ]);
            }
            $staffExpensesList = $staffExpQuery->get();
            foreach ($staffExpensesList as $sExp) {
                if (ExpenseDepositSyncService::isDeposit($sExp->expense_type, $sExp->description)) {
                    continue;
                }

                $sAmount = (float) $sExp->amount;
                $this->staffExpenses += $sAmount;
                $this->totalExpensesCount++;
                $this->totalExpenses += $sAmount;

                $cat = 'Staff: '.($sExp->expense_type ?: 'General');
                if (! isset($this->expenseBreakdown[$cat])) {
                    $this->expenseBreakdown[$cat] = 0.0;
                }
                $this->expenseBreakdown[$cat] += $sAmount;
            }
        }

        $this->expenseCategoriesList = array_keys($this->expenseBreakdown);
    }

    /**
     * Calculate Retained Savings & Cash Deposits (Seedu, Undiyal, Bank, Card)
     */
    private function calculateDeposits(): void
    {
        $depositQuery = Deposit::query();
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $depositQuery->where(function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('created_at', [$start, $end]);
            });
        }

        $deposits = $depositQuery->get();
        $this->totalDeposits = (float) $deposits->sum('amount');
        $this->totalDepositsCount = $deposits->count();

        $this->seeduSavings = (float) $deposits->where('saving_method', 'seedu')->sum('amount');
        $this->undiyalSavings = (float) $deposits->where('saving_method', 'undiyal')->sum('amount');
        $this->bankSavings = (float) $deposits->where('saving_method', 'bank_deposit')->sum('amount');
        $this->cardSavings = (float) $deposits->where('saving_method', 'card_deposit')->sum('amount');
        $this->otherSavings = max(0, $this->totalDeposits - ($this->seeduSavings + $this->undiyalSavings + $this->bankSavings + $this->cardSavings));

        $this->depositBreakdown = [];
        foreach ($deposits as $dep) {
            $method = $dep->saving_method ? ucwords(str_replace('_', ' ', $dep->saving_method)) : 'General Savings';
            if (! isset($this->depositBreakdown[$method])) {
                $this->depositBreakdown[$method] = [
                    'count' => 0,
                    'amount' => 0.0,
                ];
            }
            $this->depositBreakdown[$method]['count']++;
            $this->depositBreakdown[$method]['amount'] += (float) $dep->amount;
        }
    }

    /**
     * Calculate Total Stock In Hand (Cost Value, Retail Selling Value, Units)
     */
    private function calculateStockInHand(): void
    {
        $stockData = DB::table('product_stocks')
            ->leftJoin('product_prices', 'product_stocks.product_id', '=', 'product_prices.product_id')
            ->selectRaw('
                SUM(product_stocks.available_stock) as available_units,
                SUM(product_stocks.total_stock) as total_units,
                SUM(product_stocks.available_stock * COALESCE(product_prices.supplier_price, 0)) as total_cost_value,
                SUM(product_stocks.available_stock * COALESCE(product_prices.selling_price, 0)) as total_selling_value
            ')
            ->first();

        $this->availableStockUnits = (int) ($stockData->available_units ?? 0);
        $this->totalStockUnits = (int) ($stockData->total_units ?? 0);
        $this->totalStockCostValue = (float) ($stockData->total_cost_value ?? 0.0);
        $this->totalStockSellingValue = (float) ($stockData->total_selling_value ?? 0.0);
    }

    /**
     * Calculate Net Profit & Loss (NPL)
     * Formula: Net Profit = Total Sales - (Total Sale Items Cost + Operating Expenses)
     * Retained Savings (Seedu/Undiyal) are excluded from Operating Expenses and do not reduce Net Profit.
     */
    private function calculateNPL(): void
    {
        // Net Profit = Net Revenue (Total Sales - Returns) - (Total Sale Items Cost [COGS] + Total Operating Expenses)
        $this->netProfit = $this->netRevenue - ($this->totalCOGS + $this->totalExpenses);

        // Cashflow Net Position = Net Revenue - Total Purchasing Value - Total Operating Expenses
        $this->cashflowNet = $this->netRevenue - $this->totalPurchasingValue - $this->totalExpenses;

        $this->netProfitPercentage = $this->netRevenue > 0
            ? round(($this->netProfit / $this->netRevenue) * 100, 2)
            : 0;
    }

    /**
     * Calculate Monthly Trends for comparison
     */
    private function calculateMonthlyTrends(): void
    {
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfMonth();
            $end = Carbon::parse($this->endDate)->endOfMonth();
        } else {
            $earliestSale = Sale::orderBy('created_at')->first();
            $start = $earliestSale ? $earliestSale->created_at->copy()->startOfMonth() : now()->subMonths(5)->startOfMonth();
            $end = now()->endOfMonth();
        }

        $this->monthlyTrends = [];
        $current = $start->copy();

        while ($current <= $end) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $monthSales = (float) Sale::where('status', 'confirm')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_amount');

            $monthPurchases = (float) PurchaseOrder::whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_amount');

            $expensesForMonth = Expense::where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->orWhereBetween('created_at', [$monthStart, $monthEnd]);
            })->get();

            $monthExpenses = 0.0;
            $monthDeposits = 0.0;
            foreach ($expensesForMonth as $exp) {
                if (ExpenseDepositSyncService::isDeposit($exp->category, $exp->description)) {
                    $monthDeposits += (float) $exp->amount;
                } else {
                    $monthExpenses += (float) $exp->amount;
                }
            }

            $monthNet = $monthSales - $monthPurchases - $monthExpenses;

            $this->monthlyTrends[] = [
                'month' => $current->format('M Y'),
                'sales' => $monthSales,
                'purchases' => $monthPurchases,
                'expenses' => $monthExpenses,
                'deposits' => $monthDeposits,
                'net' => $monthNet,
            ];

            $current->addMonth();
        }
    }

    /**
     * Get detailed purchasing rows for the Purchasing Tab
     */
    public function getPurchasingRecordsProperty()
    {
        $records = [];

        // Regular Purchase Orders
        if ($this->purchaseTypeFilter === 'all' || $this->purchaseTypeFilter === 'regular') {
            $poQuery = PurchaseOrder::with(['supplier', 'items'])->orderBy('created_at', 'desc');

            if ($this->startDate && $this->endDate) {
                $start = Carbon::parse($this->startDate)->startOfDay();
                $end = Carbon::parse($this->endDate)->endOfDay();
                $poQuery->whereBetween('created_at', [$start, $end]);
            }

            if (! empty($this->purchaseSearch)) {
                $search = $this->purchaseSearch;
                $poQuery->where(function ($q) use ($search) {
                    $q->where('order_code', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%")
                                ->orWhere('businessname', 'like', "%{$search}%");
                        });
                });
            }

            foreach ($poQuery->get() as $po) {
                $records[] = [
                    'id' => 'PO-'.$po->id,
                    'code' => $po->order_code ?: 'PO-'.$po->id,
                    'type' => 'Stock PO',
                    'date' => $po->order_date ? Carbon::parse($po->order_date)->format('Y-m-d') : ($po->created_at ? $po->created_at->format('Y-m-d') : '-'),
                    'supplier_name' => $po->supplier->name ?? ($po->supplier->businessname ?? 'Direct Supplier'),
                    'supplier_phone' => $po->supplier->phone ?? '',
                    'items_count' => $po->items->count(),
                    'total_amount' => (float) $po->total_amount,
                    'paid_amount' => max(0, (float) $po->total_amount - (float) $po->due_amount),
                    'due_amount' => (float) $po->due_amount,
                    'status' => $po->status ?: 'received',
                    'raw_date' => $po->created_at,
                ];
            }
        }

        // Handmade Purchases
        if (($this->purchaseTypeFilter === 'all' || $this->purchaseTypeFilter === 'handmade') && class_exists(HandmadePurchase::class)) {
            $hpQuery = HandmadePurchase::with(['supplier', 'items'])->orderBy('created_at', 'desc');

            if ($this->startDate && $this->endDate) {
                $start = Carbon::parse($this->startDate)->startOfDay();
                $end = Carbon::parse($this->endDate)->endOfDay();
                $hpQuery->whereBetween('created_at', [$start, $end]);
            }

            if (! empty($this->purchaseSearch)) {
                $search = $this->purchaseSearch;
                $hpQuery->where(function ($q) use ($search) {
                    $q->where('purchase_code', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        });
                });
            }

            foreach ($hpQuery->get() as $hp) {
                $records[] = [
                    'id' => 'HP-'.$hp->id,
                    'code' => $hp->purchase_code ?: 'HP-'.$hp->id,
                    'type' => 'Handmade Craftsman',
                    'date' => $hp->purchase_date ? Carbon::parse($hp->purchase_date)->format('Y-m-d') : ($hp->created_at ? $hp->created_at->format('Y-m-d') : '-'),
                    'supplier_name' => $hp->supplier->name ?? 'Goldsmith Artisan',
                    'supplier_phone' => $hp->supplier->phone ?? '',
                    'items_count' => $hp->items->count(),
                    'total_amount' => (float) $hp->total_amount,
                    'paid_amount' => (float) $hp->paid_amount,
                    'due_amount' => (float) $hp->due_amount,
                    'status' => (float) $hp->due_amount <= 0 ? 'paid' : 'partial',
                    'raw_date' => $hp->created_at,
                ];
            }
        }

        // Sort all purchases by date descending
        usort($records, function ($a, $b) {
            return ($b['date'] ?? '') <=> ($a['date'] ?? '');
        });

        return $records;
    }

    /**
     * Get detailed sales rows for the Sales Tab
     */
    public function getSalesRecordsProperty()
    {
        $salesQuery = Sale::with(['customer', 'items', 'user'])->orderBy('created_at', 'desc');

        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $salesQuery->whereBetween('created_at', [$start, $end]);
        }

        if ($this->saleTypeFilter !== 'all') {
            $salesQuery->where('sale_type', $this->saleTypeFilter);
        }

        if (! empty($this->saleSearch)) {
            $search = $this->saleSearch;
            $salesQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('sale_id', 'like', "%{$search}%")
                    ->orWhere('walking_customer_name', 'like', "%{$search}%")
                    ->orWhere('walking_customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        return $salesQuery->get();
    }

    /**
     * Get detailed expense rows for the Expenses Tab
     */
    public function getExpenseRecordsProperty()
    {
        $records = [];

        $expenseQuery = Expense::with(['salaryEmployee'])->orderBy('created_at', 'desc');

        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $expenseQuery->where(function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('created_at', [$start, $end]);
            });
        }

        if ($this->expenseCategoryFilter !== 'all') {
            $expenseQuery->where('category', $this->expenseCategoryFilter);
        }

        if (! empty($this->expenseSearch)) {
            $search = $this->expenseSearch;
            $expenseQuery->where(function ($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('expense_type', 'like', "%{$search}%");
            });
        }

        foreach ($expenseQuery->get() as $exp) {
            $isDeposit = ExpenseDepositSyncService::isDeposit($exp->category, $exp->description);
            $savingMethod = $isDeposit ? ExpenseDepositSyncService::detectSavingMethod($exp->category, $exp->description) : null;

            if ($this->expenseTypeFilter === 'operating_only' && $isDeposit) {
                continue;
            }
            if ($this->expenseTypeFilter === 'deposits_only' && ! $isDeposit) {
                continue;
            }

            $records[] = [
                'id' => 'EXP-'.$exp->id,
                'date' => $exp->date ? Carbon::parse($exp->date)->format('Y-m-d') : ($exp->created_at ? $exp->created_at->format('Y-m-d') : '-'),
                'category' => $exp->category ?: 'General',
                'type' => $exp->expense_type ?: 'Operational',
                'description' => $exp->description ?: 'Shop Operating Expense',
                'recorded_by' => 'Admin',
                'amount' => (float) $exp->amount,
                'status' => $exp->status ?: 'approved',
                'source' => $isDeposit ? 'Retained Deposit' : 'Shop Expense',
                'is_deposit' => $isDeposit,
                'saving_method' => $savingMethod,
            ];
        }

        // Add Approved Staff Expenses if any
        if (class_exists(StaffExpense::class)) {
            $staffExpQuery = StaffExpense::with('staff')->where('status', 'approved')->orderBy('created_at', 'desc');
            if ($this->startDate && $this->endDate) {
                $staffExpQuery->whereBetween('expense_date', [
                    Carbon::parse($this->startDate)->toDateString(),
                    Carbon::parse($this->endDate)->toDateString(),
                ]);
            }

            if (! empty($this->expenseSearch)) {
                $search = $this->expenseSearch;
                $staffExpQuery->where(function ($q) use ($search) {
                    $q->where('expense_type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('staff', function ($sq) use ($search) {
                            $sq->where('name', 'like', "%{$search}%");
                        });
                });
            }

            foreach ($staffExpQuery->get() as $sExp) {
                $isDeposit = ExpenseDepositSyncService::isDeposit($sExp->expense_type, $sExp->description);
                $savingMethod = $isDeposit ? ExpenseDepositSyncService::detectSavingMethod($sExp->expense_type, $sExp->description) : null;

                if ($this->expenseTypeFilter === 'operating_only' && $isDeposit) {
                    continue;
                }
                if ($this->expenseTypeFilter === 'deposits_only' && ! $isDeposit) {
                    continue;
                }

                $records[] = [
                    'id' => 'STF-'.$sExp->id,
                    'date' => $sExp->expense_date ? Carbon::parse($sExp->expense_date)->format('Y-m-d') : ($sExp->created_at ? $sExp->created_at->format('Y-m-d') : '-'),
                    'category' => 'Staff Expense',
                    'type' => $sExp->expense_type ?: 'Staff Reimbursed',
                    'description' => $sExp->description ?: 'Staff claim',
                    'recorded_by' => $sExp->staff->name ?? 'Staff Member',
                    'amount' => (float) $sExp->amount,
                    'status' => 'approved',
                    'source' => $isDeposit ? 'Retained Deposit' : 'Staff Claim',
                    'is_deposit' => $isDeposit,
                    'saving_method' => $savingMethod,
                ];
            }
        }

        usort($records, function ($a, $b) {
            return ($b['date'] ?? '') <=> ($a['date'] ?? '');
        });

        return $records;
    }

    /**
     * Get detailed deposit rows for the Savings & Deposits Tab
     */
    public function getDepositRecordsProperty()
    {
        $depositQuery = Deposit::with('user')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $depositQuery->where(function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('created_at', [$start, $end]);
            });
        }

        if ($this->depositMethodFilter !== 'all') {
            $depositQuery->where('saving_method', $this->depositMethodFilter);
        }

        if (! empty($this->depositSearch)) {
            $search = $this->depositSearch;
            $depositQuery->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('saving_method', 'like', "%{$search}%")
                    ->orWhere('source_of_funds', 'like', "%{$search}%");
            });
        }

        return $depositQuery->get();
    }

    public function render()
    {
        return view('livewire.admin.profit-loss', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'preset' => $this->preset,
            'activeTab' => $this->activeTab,
            'grossSales' => $this->grossSales,
            'totalSalesCount' => $this->totalSalesCount,
            'totalDiscounts' => $this->totalDiscounts,
            'totalReturns' => $this->totalReturns,
            'netRevenue' => $this->netRevenue,
            'totalCOGS' => $this->totalCOGS,
            'grossProfit' => $this->grossProfit,
            'grossProfitPercentage' => $this->grossProfitPercentage,
            'totalPurchasingValue' => $this->totalPurchasingValue,
            'totalPurchasingCount' => $this->totalPurchasingCount,
            'totalPurchasingPaid' => $this->totalPurchasingPaid,
            'totalPurchasingDue' => $this->totalPurchasingDue,
            'totalExpenses' => $this->totalExpenses,
            'shopExpenses' => $this->shopExpenses,
            'staffExpenses' => $this->staffExpenses,
            'totalExpensesCount' => $this->totalExpensesCount,
            'totalDeposits' => $this->totalDeposits,
            'seeduSavings' => $this->seeduSavings,
            'undiyalSavings' => $this->undiyalSavings,
            'bankSavings' => $this->bankSavings,
            'cardSavings' => $this->cardSavings,
            'otherSavings' => $this->otherSavings,
            'totalDepositsCount' => $this->totalDepositsCount,
            'depositBreakdown' => $this->depositBreakdown,
            'expenseTypeFilter' => $this->expenseTypeFilter,
            'depositSearch' => $this->depositSearch,
            'depositMethodFilter' => $this->depositMethodFilter,
            'totalStockCostValue' => $this->totalStockCostValue,
            'totalStockSellingValue' => $this->totalStockSellingValue,
            'availableStockUnits' => $this->availableStockUnits,
            'totalStockUnits' => $this->totalStockUnits,
            'netProfit' => $this->netProfit,
            'netProfitPercentage' => $this->netProfitPercentage,
            'cashflowNet' => $this->cashflowNet,
            'revenueBreakdown' => $this->revenueBreakdown,
            'paymentMethodWiseRevenue' => $this->paymentMethodWiseRevenue,
            'expenseBreakdown' => $this->expenseBreakdown,
            'expenseCategoriesList' => $this->expenseCategoriesList ?: [],
            'monthlyTrends' => $this->monthlyTrends ?: [],
            'purchasingRecords' => $this->purchasingRecords,
            'salesRecords' => $this->salesRecords,
            'expenseRecords' => $this->expenseRecords,
            'depositRecords' => $this->depositRecords,
        ])->layout($this->layout);
    }
}
