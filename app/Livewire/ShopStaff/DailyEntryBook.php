<?php

namespace App\Livewire\ShopStaff;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\StaffExpense;
use App\Models\POSSession;
use App\Models\GoldPlatingPayment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyEntryBook extends Component
{
    public $showModal = false;
    public $tomorrowOpeningBalance = 0;
    
    public $summaryData = [];
    public $expenses = [];

    protected $listeners = ['openDailyEntryBook' => 'openModal'];

    public function openModal()
    {
        $this->loadData();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function loadData()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        // 1. Get Expenses
        $this->expenses = StaffExpense::with('salaryEmployee')
            ->where('staff_id', $userId)
            ->whereDate('expense_date', $today)
            ->where('status', 'approved')
            ->get();

        $totalExpenses = $this->expenses->sum('amount');

        // 2. Get Income (Payments made today)
        // Today's POS sales IDs
        $todaySaleIds = Sale::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->pluck('id');

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

            $todayPayments = Payment::whereIn('sale_id', $filteredIds)
                ->whereDate('payment_date', $today)
                ->whereNull('due_payment_method')
                ->get();

            $cashSale = $todayPayments->where('payment_method', 'cash')->sum('amount');
            if ($cashSale == 0 && $limitedTotalSale > 0) {
                $cashSale = $limitedTotalSale;
            }
            $onlineSale = $todayPayments->where('payment_method', 'online')->sum('amount');
            $bankSale = $todayPayments->where('payment_method', 'bank_transfer')->sum('amount');
            $receivedDuePayments = 0;
            $totalExpenses = 0;
        } else {
            // Payments for today's sales (not due payments)
            $todayPayments = Payment::whereIn('sale_id', $todaySaleIds)
                ->whereDate('payment_date', $today)
                ->whereNull('due_payment_method')
                ->get();

            $cashSale = $todayPayments->where('payment_method', 'cash')->sum('amount');
            $onlineSale = $todayPayments->where('payment_method', 'online')->sum('amount');
            $bankSale = $todayPayments->where('payment_method', 'bank_transfer')->sum('amount');

            // Due payments received today (from any sale)
            $receivedDuePayments = Payment::query()
                ->join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('sales.user_id', $userId)
                ->whereDate('payments.payment_date', $today)
                ->whereNotNull('payments.due_payment_method')
                ->whereIn('payments.status', ['paid', 'approved'])
                ->sum('payments.amount');
        }

        // Gold plating & service payments received today (always calculated for accurate register cash)
        $platingPayments = GoldPlatingPayment::where('user_id', $userId)
            ->whereDate('payment_date', $today)
            ->get();
        $platingCash = (float) $platingPayments->where('payment_method', 'cash')->sum('amount');
        $platingOnline = (float) $platingPayments->where('payment_method', 'online')->sum('amount');
        $platingCard = (float) $platingPayments->where('payment_method', 'card')->sum('amount');
        $platingTotal = round($platingCash + $platingOnline + $platingCard, 2);

        // Opening Balance
        $session = POSSession::where('user_id', $userId)
            ->where('session_date', $today)
            ->first();
        $openingBalance = $session ? $session->opening_cash : 0;

        // Total Cash = Opening Balance + Cash Sales + Received Due + Plating Cash - Expenses
        $totalCash = (float)$openingBalance + (float)$cashSale + (float)$receivedDuePayments + (float)$platingCash - (float)$totalExpenses;

        $totalIncome = (float)$openingBalance + (float)$cashSale + (float)$onlineSale + (float)$bankSale + (float)$receivedDuePayments + (float)$platingTotal;
        $netIncome = $totalIncome - $totalExpenses;

        $this->summaryData = [
            'openingBalance' => (float)$openingBalance,
            'cashSale' => (float)$cashSale,
            'onlineSale' => (float)$onlineSale,
            'bankSale' => (float)$bankSale,
            'receivedDue' => (float)$receivedDuePayments,
            'platingCash' => (float)$platingCash,
            'platingOnline' => (float)$platingOnline,
            'platingTotal' => (float)$platingTotal,
            'totalExpenses' => (float)$totalExpenses,
            'totalCash' => (float)$totalCash,
            'totalIncome' => (float)$totalIncome,
            'netIncome' => (float)$netIncome,
        ];

        // Default tomorrow opening to 0
        $this->tomorrowOpeningBalance = 0;
    }

    public function saveAndCloseDay()
    {
        $userId = Auth::id();
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        try {
            DB::beginTransaction();

            // 1. Update or create tomorrow's session with the opening balance
            POSSession::updateOrCreate(
                ['user_id' => $userId, 'session_date' => $tomorrow->toDateString()],
                [
                    'opening_cash' => $this->tomorrowOpeningBalance,
                    'status' => 'open',
                    'opened_at' => now(),
                ]
            );

            // 2. Mark today's session as closed
            $todaySession = POSSession::where('user_id', $userId)
                ->where('session_date', $today->toDateString())
                ->first();
            
            if ($todaySession) {
                $todaySession->update([
                    'closing_cash' => $this->summaryData['totalCash'],
                    'status' => 'closed',
                    'closed_at' => now(),
                    'cash_sales' => $this->summaryData['cashSale'],
                    'bank_transfer' => $this->summaryData['bankSale'],
                    'late_payment_bulk' => $this->summaryData['receivedDue'],
                    'expenses' => $this->summaryData['totalExpenses'],
                ]);
                $todaySession->calculateDifference();
            }

            DB::commit();

            $this->closeModal();
            $this->dispatch('showToast', type: 'success', message: 'Day closed and tomorrow opening balance set!');
            
            // Optionally redirect to dashboard to refresh state
            return redirect()->route('shop-staff.dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', type: 'error', message: 'Failed to close day: ' . $e->getMessage());
        }
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

        // Gold plating & service payments received today
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
        return view('livewire.shop-staff.daily-entry-book');
    }
}
