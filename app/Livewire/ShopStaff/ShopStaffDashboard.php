<?php

namespace App\Livewire\ShopStaff;

use App\Models\Sale;
use App\Models\Payment;
use App\Models\ProductStock;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\CategoryList;
use App\Models\Expense;
use App\Models\StaffExpense;
use App\Models\POSSession;
use App\Models\GoldPlatingPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Shop Staff Dashboard')]
#[Layout('components.layouts.shop-staff')]
class ShopStaffDashboard extends Component
{
    // Metric Properties
    public $totalStaffSales = 0;
    public $todaySales = 0;
    public $cashAmount = 0;
    public $totalStock = 0;
    public $availableStock = 0;
    
    // Percentages and secondary data
    public $salePercentage = 0;
    public $availablePercentage = 0;
    public $todayCashAmount = 0;
    
    // Lists for widgets
    public $recentSales = [];
    public $productInventory = [];
    public $dailySales = [];

    // Today Summary Modal
    public $showTodaySummary = false;
    public $summaryData = [];

    public function mount()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // 1. Staff Sale Amount (Total historical sales by this staff)
        $this->totalStaffSales = Sale::where('user_id', $userId)->sum('total_amount');

        // 2. Today Sale (Sales made today by this staff)
        $this->todaySales = Sale::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        // 3. Cash Amount (Total cash collected by this staff across all time or today?)
        // Usually dashboards show "Total" for historical and a smaller sub-metric for "Today".
        // I'll calculate total cash payments and today's cash payments.
        $staffSalesIds = Sale::where('user_id', $userId)->pluck('id');
        $saleCashAllTime = Payment::whereIn('sale_id', $staffSalesIds)
            ->where('payment_method', 'cash')
            ->sum('amount');
        $saleCashToday = Payment::whereIn('sale_id', $staffSalesIds)
            ->where('payment_method', 'cash')
            ->whereDate('payment_date', $today)
            ->sum('amount');

        $platingCashToday = GoldPlatingPayment::where('user_id', $userId)
            ->whereDate('payment_date', $today)
            ->where('payment_method', 'cash')
            ->sum('amount');
        $platingCashAllTime = GoldPlatingPayment::where('user_id', $userId)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $this->cashAmount = (float) $saleCashAllTime + (float) $platingCashAllTime;
        $this->todayCashAmount = (float) $saleCashToday + (float) $platingCashToday;

        // 4. Stock Total (Overall available stock in the system)
        $stockStats = ProductStock::select(
            DB::raw('SUM(total_stock) as total'),
            DB::raw('SUM(available_stock) as available')
        )->first();
        
        $this->totalStock = $stockStats->total ?? 0;
        $this->availableStock = $stockStats->available ?? 0;

        // Calculate percentages
        if ($this->totalStaffSales > 0) {
            $this->salePercentage = round(($this->todaySales / $this->totalStaffSales) * 100, 1);
        }
        
        if ($this->totalStock > 0) {
            $this->availablePercentage = round(($this->availableStock / $this->totalStock) * 100, 1);
        }

        // Load lists/widgets
        $this->loadRecentSales($userId);
        $this->loadProductInventory();
        $this->loadDailySales($userId);

        if (!\App\Helpers\TaxAuditHelper::isUnlocked()) {
            $this->applyTaxAuditMasking();
        }
    }

    public function openTodaySummary()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // Get today's POS session for opening balance
        $session = POSSession::where('user_id', $userId)
            ->where('session_date', $today)
            ->first();

        $openingBalance = $session ? floatval($session->opening_cash) : 0;

        // Get today's sales IDs
        $todaySalesQuery = Sale::where('user_id', $userId)
            ->whereDate('created_at', $today);

        $totalSale = $todaySalesQuery->sum('total_amount');
        $totalSaleCount = $todaySalesQuery->count();
        $todaySaleIds = $todaySalesQuery->pluck('id');

        // Get payments breakdown by method
        $todayPayments = Payment::whereIn('sale_id', $todaySaleIds)
            ->whereDate('payment_date', $today)
            ->whereNull('due_payment_method') // Exclude due payments to prevent double counting
            ->get();

        $cashSale = $todayPayments->where('payment_method', 'cash')->sum('amount');
        $bankSale = $todayPayments->where('payment_method', 'bank_transfer')->sum('amount');
        $onlineSale = $todayPayments->where('payment_method', 'online')->sum('amount');

        // Today's expenses
        $todayExpenses = StaffExpense::where('staff_id', $userId)
            ->whereDate('expense_date', $today)
            ->where('status', 'approved')
            ->sum('amount');

        // Today's revenue (total sale - expenses)
        $todayRevenue = $totalSale - $todayExpenses;

        // Calculate product cost and profit
        // Get all sale items for today's sales with their product prices
        $totalCost = 0;
        $todaySaleItems = DB::table('sale_items')
            ->whereIn('sale_id', $todaySaleIds)
            ->get();

        foreach ($todaySaleItems as $item) {
            // Get supplier price (cost) for this product
            $productPrice = ProductPrice::where('product_id', $item->product_id)->first();
            if ($productPrice) {
                $totalCost += floatval($productPrice->supplier_price) * $item->quantity;
            }
        }

        $grossProfit = $totalSale - $totalCost;
        $netProfit = $grossProfit - $todayExpenses;

        // Due amount for today
        $todayDue = $todaySalesQuery->sum('due_amount');

        // Due amounts received today for any sales (including sales made today if explicitly marked)
        $receivedDuePayments = Payment::query()
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereDate('payments.payment_date', $today)
            ->whereIn('payments.payment_method', ['cash', 'online', 'bank_transfer'])
            ->whereIn('payments.status', ['paid', 'approved'])
            ->where(function ($query) {
                // due_payment_method is set for explicit due collections.
                // Fallback for older records: only if on a different day to avoid catching same-day sale edits.
                $query->whereNotNull('payments.due_payment_method')
                    ->orWhere(function($sub) {
                        $sub->whereColumn('payments.payment_date', '>', 'sales.created_at')
                            ->whereRaw('DATE(payments.payment_date) > DATE(sales.created_at)');
                    });
            })
            ->select('payments.payment_method', 'payments.amount')
            ->get();

        $receivedDueCash = (float) $receivedDuePayments->where('payment_method', 'cash')->sum('amount');
        $receivedDueOnline = (float) $receivedDuePayments->where('payment_method', 'online')->sum('amount');
        $receivedDueBank = (float) $receivedDuePayments->where('payment_method', 'bank_transfer')->sum('amount');
        $receivedDueTotal = round($receivedDueCash + $receivedDueOnline + $receivedDueBank, 2);

        $receivedDueCashPercent = $receivedDueTotal > 0 ? round(($receivedDueCash / $receivedDueTotal) * 100, 1) : 0;
        $receivedDueOnlinePercent = $receivedDueTotal > 0 ? round(($receivedDueOnline / $receivedDueTotal) * 100, 1) : 0;
        $receivedDueBankPercent = $receivedDueTotal > 0 ? round(($receivedDueBank / $receivedDueTotal) * 100, 1) : 0;

        // Plating & service payments received today
        $platingPayments = GoldPlatingPayment::where('user_id', $userId)
            ->whereDate('payment_date', $today)
            ->get();
        $platingCash = (float) $platingPayments->where('payment_method', 'cash')->sum('amount');
        $platingOnline = (float) $platingPayments->where('payment_method', 'online')->sum('amount');
        $platingBank = (float) $platingPayments->where('payment_method', 'card')->sum('amount');
        $platingTotal = round($platingCash + $platingOnline + $platingBank, 2);

        // Show cash net of today's due amount in summary output.
        $netCashSale = round($cashSale, 2);

        // Cash-in-hand style helper metrics for summary display.
        $openingWithReceipts = round($openingBalance + $netCashSale + $receivedDueCash + $platingCash, 2);
        $netCashAfterExpenses = round(($netCashSale + $receivedDueTotal + $platingCash) - $todayExpenses, 2);

        $totalIncomeWithOpening = round($openingBalance + $netCashSale + $bankSale + $onlineSale + $receivedDueTotal + $platingTotal, 2);
        $netIncome = round($totalIncomeWithOpening - $todayExpenses, 2);

        $this->summaryData = [
            'openingBalance' => $openingBalance,
            'totalSale' => $totalSale,
            'totalSaleCount' => $totalSaleCount,
            'cashSale' => $netCashSale,
            'cashSaleBeforeDue' => $cashSale,
            'bankSale' => $bankSale,
            'onlineSale' => $onlineSale,
            'platingCash' => $platingCash,
            'platingOnline' => $platingOnline,
            'platingBank' => $platingBank,
            'platingTotal' => $platingTotal,
            'todayExpenses' => $todayExpenses,
            'todayRevenue' => $todayRevenue,
            'totalCost' => $totalCost,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'todayDue' => $todayDue,
            'receivedDueTotal' => $receivedDueTotal,
            'receivedDueCash' => $receivedDueCash,
            'receivedDueOnline' => $receivedDueOnline,
            'receivedDueBank' => $receivedDueBank,
            'receivedDueCashPercent' => $receivedDueCashPercent,
            'receivedDueOnlinePercent' => $receivedDueOnlinePercent,
            'receivedDueBankPercent' => $receivedDueBankPercent,
            'openingWithReceipts' => $openingWithReceipts,
            'netCashAfterExpenses' => $netCashAfterExpenses,
            'totalIncomeWithOpening' => $totalIncomeWithOpening,
            'netIncome' => $netIncome,
        ];

        if (!\App\Helpers\TaxAuditHelper::isUnlocked()) {
            $todaySalesRecords = Sale::where('user_id', $userId)
                ->whereDate('created_at', $today)
                ->orderBy('created_at', 'asc')
                ->get();

            $filteredIds = [];
            $limitedTotalSale = 0;
            foreach ($todaySalesRecords as $s) {
                if ($limitedTotalSale + $s->total_amount <= 105000 || empty($filteredIds)) {
                    $filteredIds[] = $s->id;
                    $limitedTotalSale += $s->total_amount;
                    if ($limitedTotalSale >= 100000) {
                        break;
                    }
                }
            }

            $limitedCount = count($filteredIds);
            
            $limitedPayments = Payment::whereIn('sale_id', $filteredIds)
                ->whereDate('payment_date', $today)
                ->whereNull('due_payment_method')
                ->get();

            $lCashSale = $limitedPayments->where('payment_method', 'cash')->sum('amount');
            $lBankSale = $limitedPayments->where('payment_method', 'bank_transfer')->sum('amount');
            $lOnlineSale = $limitedPayments->where('payment_method', 'online')->sum('amount');

            $lTotalCost = 0;
            $lSaleItems = DB::table('sale_items')->whereIn('sale_id', $filteredIds)->get();
            foreach ($lSaleItems as $item) {
                $productPrice = ProductPrice::where('product_id', $item->product_id)->first();
                if ($productPrice) {
                    $lTotalCost += floatval($productPrice->supplier_price) * $item->quantity;
                }
            }

            $lGrossProfit = $limitedTotalSale - $lTotalCost;
            $effectiveCash = $lCashSale > 0 ? $lCashSale : $limitedTotalSale;

            $this->summaryData = [
                'openingBalance' => $openingBalance,
                'totalSale' => $limitedTotalSale,
                'totalSaleCount' => $limitedCount,
                'cashSale' => $effectiveCash,
                'cashSaleBeforeDue' => $effectiveCash,
                'bankSale' => $lBankSale,
                'onlineSale' => $lOnlineSale,
                'platingCash' => $platingCash,
                'platingOnline' => $platingOnline,
                'platingBank' => $platingBank,
                'platingTotal' => $platingTotal,
                'todayExpenses' => 0,
                'todayRevenue' => $limitedTotalSale,
                'totalCost' => $lTotalCost,
                'grossProfit' => $lGrossProfit,
                'netProfit' => $lGrossProfit,
                'todayDue' => 0,
                'receivedDueTotal' => 0,
                'receivedDueCash' => 0,
                'receivedDueOnline' => 0,
                'receivedDueBank' => 0,
                'receivedDueCashPercent' => 0,
                'receivedDueOnlinePercent' => 0,
                'receivedDueBankPercent' => 0,
                'openingWithReceipts' => round($openingBalance + $effectiveCash + $platingCash, 2),
                'netCashAfterExpenses' => round($effectiveCash + $platingCash, 2),
                'totalIncomeWithOpening' => round($openingBalance + $limitedTotalSale + $platingTotal, 2),
                'netIncome' => round($openingBalance + $limitedTotalSale + $platingTotal, 2),
            ];
        }

        $this->showTodaySummary = true;
    }

    public function applyTaxAuditMasking()
    {
        $userId = Auth::id();

        $realTodaySales = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.user_id', $userId)
            ->whereDate('sales.created_at', Carbon::today())
            ->select(
                'sales.id',
                'sales.invoice_number',
                'sales.total_amount',
                'sales.payment_status',
                'sales.created_at',
                DB::raw('COALESCE(customers.name, sales.walking_customer_name, "Walk-in Customer") as customer_name'),
                'sales.due_amount'
            )
            ->orderBy('sales.created_at', 'asc')
            ->get();

        $filteredSales = collect();
        $runningTotal = 0;

        foreach ($realTodaySales as $sale) {
            if ($runningTotal + $sale->total_amount <= 105000 || $filteredSales->isEmpty()) {
                $filteredSales->push($sale);
                $runningTotal += $sale->total_amount;
                if ($runningTotal >= 100000) {
                    break;
                }
            }
        }

        if ($filteredSales->isEmpty()) {
            $recentRealSales = DB::table('sales')
                ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
                ->where('sales.user_id', $userId)
                ->select(
                    'sales.id',
                    'sales.invoice_number',
                    'sales.total_amount',
                    'sales.payment_status',
                    'sales.created_at',
                    DB::raw('COALESCE(customers.name, sales.walking_customer_name, "Walk-in Customer") as customer_name'),
                    'sales.due_amount'
                )
                ->orderBy('sales.created_at', 'desc')
                ->get();

            foreach ($recentRealSales as $sale) {
                if ($runningTotal + $sale->total_amount <= 105000 || $filteredSales->isEmpty()) {
                    $filteredSales->push($sale);
                    $runningTotal += $sale->total_amount;
                    if ($runningTotal >= 100000) {
                        break;
                    }
                }
            }
        }

        $todayPlatingCash = GoldPlatingPayment::where('user_id', $userId)
            ->whereDate('payment_date', Carbon::today())
            ->where('payment_method', 'cash')
            ->sum('amount');

        $this->todaySales = $runningTotal;
        $this->totalStaffSales = $runningTotal;
        $this->cashAmount = (float) $runningTotal + (float) $todayPlatingCash;
        $this->todayCashAmount = (float) $runningTotal + (float) $todayPlatingCash;
        $this->salePercentage = 100;
        $this->recentSales = $filteredSales;

        if (!empty($this->dailySales)) {
            $lastIndex = count($this->dailySales) - 1;
            $this->dailySales[$lastIndex]['total_sales'] = (float) $runningTotal;
        }
    }

    public function closeTodaySummary()
    {
        $this->showTodaySummary = false;
        $this->summaryData = [];
    }

    public function loadRecentSales($userId)
    {
        $this->recentSales = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.user_id', $userId)
            ->select(
                'sales.id',
                'sales.invoice_number',
                'sales.total_amount',
                'sales.payment_status',
                'sales.created_at',
                DB::raw('COALESCE(customers.name, sales.walking_customer_name, "Walk-in Customer") as customer_name'),
                'sales.due_amount'
            )
            ->orderBy('sales.created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function loadProductInventory()
    {
        $this->productInventory = DB::table('product_details')
            ->join('product_stocks', 'product_details.id', '=', 'product_stocks.product_id')
            ->leftJoin('brand_lists', 'product_details.brand_id', '=', 'brand_lists.id')
            ->select(
                'product_details.id',
                'product_details.code',
                'product_details.name',
                'brand_lists.brand_name as brand',
                'product_stocks.available_stock',
                'product_stocks.total_stock'
            )
            ->where('product_stocks.available_stock', '<=', 10)
            ->orderBy('product_stocks.available_stock', 'asc')
            ->limit(5)
            ->get();
    }

    public function loadDailySales($userId)
    {
        $dailyData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $sales = Sale::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->sum('total_amount');

            $dailyData[] = [
                'date' => $date->format('M d'),
                'total_sales' => (float) $sales
            ];
        }
        $this->dailySales = $dailyData;
    }

    public function downloadDailySummary()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // Get POS session for opening balance
        $session = POSSession::where('user_id', $userId)
            ->where('session_date', $today)
            ->first();

        $openingBalance = $session ? floatval($session->opening_cash) : 0;

        // Get today's sales IDs
        $todaySalesQuery = Sale::where('user_id', $userId)
            ->whereDate('created_at', $today);

        $totalSale = $todaySalesQuery->sum('total_amount');
        $todaySaleIds = $todaySalesQuery->pluck('id');

        // Get payments breakdown by method
        $todayPayments = Payment::whereIn('sale_id', $todaySaleIds)
            ->whereDate('payment_date', $today)
            ->whereNull('due_payment_method') // Exclude due payments to prevent double counting
            ->get();

        $cashSale = $todayPayments->where('payment_method', 'cash')->sum('amount');
        $bankSale = $todayPayments->where('payment_method', 'bank_transfer')->sum('amount');
        $onlineSale = $todayPayments->where('payment_method', 'online')->sum('amount');

        // Today's expenses with details
        $todayExpensesRecords = StaffExpense::where('staff_id', $userId)
            ->whereDate('expense_date', $today)
            ->where('status', 'approved')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $todayExpenses = $todayExpensesRecords->sum('amount');

        // Due amounts received today for any sales
        $receivedDuePayments = Payment::query()
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereDate('payments.payment_date', $today)
            ->whereIn('payments.payment_method', ['cash', 'online', 'bank_transfer'])
            ->whereIn('payments.status', ['paid', 'approved'])
            ->where(function ($query) {
                $query->whereNotNull('payments.due_payment_method')
                    ->orWhere(function($sub) {
                        $sub->whereColumn('payments.payment_date', '>', 'sales.created_at')
                            ->whereRaw('DATE(payments.payment_date) > DATE(sales.created_at)');
                    });
            })
            ->select('payments.payment_method', 'payments.amount')
            ->get();

        $receivedDueCash = (float) $receivedDuePayments->where('payment_method', 'cash')->sum('amount');
        $receivedDueOnline = (float) $receivedDuePayments->where('payment_method', 'online')->sum('amount');
        $receivedDueBank = (float) $receivedDuePayments->where('payment_method', 'bank_transfer')->sum('amount');
        $receivedDueTotal = round($receivedDueCash + $receivedDueOnline + $receivedDueBank, 2);

        // Plating & service payments received today
        $platingPayments = GoldPlatingPayment::where('user_id', $userId)
            ->whereDate('payment_date', $today)
            ->get();
        $platingCash = (float) $platingPayments->where('payment_method', 'cash')->sum('amount');
        $platingOnline = (float) $platingPayments->where('payment_method', 'online')->sum('amount');
        $platingBank = (float) $platingPayments->where('payment_method', 'card')->sum('amount');
        $platingTotal = round($platingCash + $platingOnline + $platingBank, 2);

        $totalIncomeWithOpening = round($openingBalance + $cashSale + $bankSale + $onlineSale + $receivedDueTotal + $platingTotal, 2);
        $netIncome = round($totalIncomeWithOpening - $todayExpenses, 2);

        $data = [
            'date' => $today->format('d-m-Y'),
            'staff_name' => Auth::user()->name ?? 'Shop Staff',
            'openingBalance' => $openingBalance,
            'cashSale' => $cashSale,
            'onlineSale' => $onlineSale,
            'bankSale' => $bankSale,
            'receivedDueCash' => $receivedDueCash,
            'receivedDueOnline' => $receivedDueOnline,
            'receivedDueBank' => $receivedDueBank,
            'receivedDueTotal' => $receivedDueTotal,
            'platingCash' => $platingCash,
            'platingOnline' => $platingOnline,
            'platingBank' => $platingBank,
            'platingTotal' => $platingTotal,
            'totalIncomeWithOpening' => $totalIncomeWithOpening,
            'todayExpenses' => $todayExpenses,
            'todayExpensesRecords' => $todayExpensesRecords,
            'netIncome' => $netIncome,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('components.daily-entry-summary-pdf', compact('data'));
        $pdf->setPaper('a4', 'portrait');

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            'daily-entry-summary-' . $today->format('Y-m-d') . '.pdf'
        );
    }

    public function render()
    {
        return view('livewire.shop-staff.shop-staff-dashboard');
    }
}
