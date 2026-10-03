<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoldPlatingOrder;
use App\Models\GoldPlatingPayment;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function printSale($id)
    {
        // Load sale with all necessary relationships including returns and deliverySale
        $sale = Sale::with(['customer', 'items.product', 'payments', 'deliverySale', 'returns' => function ($q) {
            $q->with('product');
        }])->findOrFail($id);

        // Return the print view
        return view('components.sale-receipt-print', compact('sale'));
    }

    public function printSaleThermal($id)
    {
        // Load sale with all necessary relationships including returns, user and deliverySale
        $sale = Sale::with(['customer', 'items.product', 'payments', 'deliverySale', 'user', 'returns' => function ($q) {
            $q->with('product');
        }])->findOrFail($id);

        // Return the thermal print view
        return view('components.sale-receipt-thermal-print', compact('sale'));
    }

    public function printGoldPlatingThermal($id)
    {
        $order = GoldPlatingOrder::with(['user', 'payments', 'worker', 'items'])->findOrFail($id);

        return view('components.gold-plating-receipt-thermal-print', compact('order'));
    }

    public function printDeliveryLabel($id)
    {
        // Load sale with delivery details
        $sale = Sale::with(['customer', 'deliverySale'])->findOrFail($id);

        if (! $sale->deliverySale) {
            $customerName = $sale->customer_display_name ?: 'Walking Customer';
            $customerPhone = $sale->customer_display_phone ?: '';
            $customerAddress = $sale->customer_display_address ?: '';
            $customerDetails = "Customer: {$customerName}\nPhone: {$customerPhone}\nAddress: {$customerAddress}";

            $deliverySale = \App\Models\DeliverySale::create([
                'sale_id' => $sale->id,
                'delivery_method' => 'Post',
                'payment_method' => ($sale->due_amount > 0) ? 'Cash on Delivery' : 'Online Payment',
                'delivery_charge' => 450,
                'status' => 'Processing',
                'customer_details' => $customerDetails,
            ]);

            $sale->setRelation('deliverySale', $deliverySale);
        }

        // Return the delivery label view
        return view('admin.sales.delivery-label', compact('sale'));
    }

    public function downloadSale($id)
    {
        // Load sale with all necessary relationships including returns
        $sale = Sale::with(['customer', 'items.product', 'payments', 'returns' => function ($q) {
            $q->with('product');
        }])->findOrFail($id);

        // Generate PDF
        $pdf = PDF::loadView('admin.sales.invoice', compact('sale'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('dpi', 150);
        $pdf->setOption('defaultFont', 'sans-serif');

        return $pdf->download('invoice-'.$sale->invoice_number.'.pdf');
    }

    public function printQuotation($id)
    {
        // Load quotation with all necessary relationships
        $quotation = Quotation::findOrFail($id);

        // Decode items if stored as JSON
        if (is_string($quotation->items)) {
            $quotation->items = json_decode($quotation->items, true);
        }

        // Return the print view
        return view('admin.quotations.print', compact('quotation'));
    }

    public function printQuotationThermal($id)
    {
        // Load quotation with creator/user
        $quotation = Quotation::with('user')->findOrFail($id);

        // Decode items if stored as JSON
        if (is_string($quotation->items)) {
            $quotation->items = json_decode($quotation->items, true);
        }

        // Return the thermal print view
        return view('components.quotation-receipt-thermal-print', compact('quotation'));
    }

    public function downloadDailySummary(Request $request)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $today = \Carbon\Carbon::today();

        // Get POS session for opening balance
        $session = \App\Models\POSSession::where('user_id', $userId)
            ->where('session_date', $today)
            ->first();

        $openingBalance = $session ? floatval($session->opening_cash) : 0;

        // Get today's sales IDs for this staff
        $todaySalesQuery = \App\Models\Sale::where('user_id', $userId)
            ->whereDate('created_at', $today);

        $totalSale = $todaySalesQuery->sum('total_amount');
        $todaySaleIds = $todaySalesQuery->pluck('id');

        // Get payments breakdown (excluding due payments)
        $todayPayments = \App\Models\Payment::whereIn('sale_id', $todaySaleIds)
            ->whereDate('payment_date', $today)
            ->whereNull('due_payment_method')
            ->get();

        $cashSale = (float) $todayPayments->where('payment_method', 'cash')->sum('amount');
        $bankSale = (float) $todayPayments->where('payment_method', 'bank_transfer')->sum('amount');
        $onlineSale = (float) $todayPayments->where('payment_method', 'online')->sum('amount');

        // Today's approved expenses with details
        $todayExpensesRecords = \App\Models\StaffExpense::where('staff_id', $userId)
            ->whereDate('expense_date', $today)
            ->where('status', 'approved')
            ->orderBy('created_at', 'asc')
            ->get();

        $todayExpenses = $todayExpensesRecords->sum('amount');

        // Due amounts received today
        $receivedDuePayments = \App\Models\Payment::query()
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.user_id', $userId)
            ->whereDate('payments.payment_date', $today)
            ->whereIn('payments.payment_method', ['cash', 'online', 'bank_transfer'])
            ->whereIn('payments.status', ['paid', 'approved'])
            ->where(function ($q) {
                $q->whereNotNull('payments.due_payment_method')
                    ->orWhere(function ($sub) {
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
            'staff_name' => \Illuminate\Support\Facades\Auth::user()->name ?? 'Shop Staff',
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
            'todayExpenses' => (float) $todayExpenses,
            'todayExpensesRecords' => $todayExpensesRecords,
            'netIncome' => $netIncome,
        ];

        $pdf = PDF::loadView('components.daily-entry-summary-pdf', compact('data'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('dpi', 150);
        $pdf->setOption('defaultFont', 'sans-serif');

        return $pdf->download('daily-entry-summary-'.$today->format('Y-m-d').'.pdf');
    }

    public function printDummySaleThermal(Request $request)
    {
        $dummyData = session('dummy_sale_print');
        if (! $dummyData || empty($dummyData['sale_id'])) {
            abort(404, 'No dummy bill data found in session. Please generate it from the sales list.');
        }

        $sale = $this->buildDummySaleModel($dummyData);

        return view('components.sale-receipt-thermal-print', compact('sale'));
    }

    public function printDummySaleA4(Request $request)
    {
        $dummyData = session('dummy_sale_print');
        if (! $dummyData || empty($dummyData['sale_id'])) {
            abort(404, 'No dummy bill data found in session. Please generate it from the sales list.');
        }

        $sale = $this->buildDummySaleModel($dummyData);

        return view('components.sale-receipt-print', compact('sale'));
    }

    protected function buildDummySaleModel(array $dummyData): Sale
    {
        $realSale = Sale::with(['customer', 'payments', 'user', 'deliverySale'])->findOrFail($dummyData['sale_id']);

        $dummySale = clone $realSale;
        $dummySale->invoice_number = $dummyData['invoice_number'] ?? $realSale->invoice_number;

        $totalAmount = (float) ($dummyData['total_amount'] ?? 0);
        $dummySale->subtotal = $totalAmount;
        $dummySale->discount_amount = 0;
        $dummySale->total_amount = $totalAmount;
        $dummySale->due_amount = 0;
        $dummySale->payment_status = 'paid';

        $dummyPayment = new Payment([
            'payment_method' => 'cash',
            'amount' => $totalAmount,
            'payment_date' => $realSale->created_at,
        ]);
        $dummySale->setRelation('payments', collect([$dummyPayment]));

        $dummyItems = collect($dummyData['items'] ?? [])->map(function ($rawItem) use ($realSale) {
            return new SaleItem([
                'sale_id' => $realSale->id,
                'product_name' => $rawItem['product_name'] ?? 'Item',
                'product_code' => $rawItem['product_code'] ?? '',
                'product_model' => $rawItem['product_model'] ?? null,
                'quantity' => (int) ($rawItem['quantity'] ?? 1),
                'unit_price' => (float) ($rawItem['unit_price'] ?? 0),
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => (float) ($rawItem['total'] ?? 0),
                'has_warranty' => ! empty($rawItem['has_warranty']),
                'warranty_duration' => $rawItem['warranty_duration'] ?? null,
            ]);
        });

        $dummySale->setRelation('items', $dummyItems);
        $dummySale->setRelation('returns', collect());

        return $dummySale;
    }
}
