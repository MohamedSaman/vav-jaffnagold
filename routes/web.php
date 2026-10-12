<?php

use App\Http\Controllers\Admin\CashController;
use App\Http\Controllers\Admin\PrintController;
use App\Http\Controllers\ProductApiController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductsExportController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\StaffSaleExportController;
use App\Livewire\Admin\AddCustomerReceipt;
use App\Livewire\Admin\AddSupplierReceipt;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\Analytics;
use App\Livewire\Admin\BillingPage;
use App\Livewire\Admin\ChequeList;
use App\Livewire\Admin\CustomerSaleDetails;
use App\Livewire\Admin\DaySummary;
use App\Livewire\Admin\DaySummaryDetails;
use App\Livewire\Admin\Deposits;
use App\Livewire\Admin\DuePayments as AdminDuePayments;
use App\Livewire\Admin\Expenses;
use App\Livewire\Admin\FastProductCreate;
use App\Livewire\Admin\GRN;
use App\Livewire\Admin\Income;
use App\Livewire\Admin\ListCustomerReceipt;
use App\Livewire\Admin\ListSupplierReceipt;
use App\Livewire\Admin\ListSupplierReturn;
use App\Livewire\Admin\LoanManage;
use App\Livewire\Admin\ManageAdmin;
use App\Livewire\Admin\ManageCustomer;
use App\Livewire\Admin\ManageStaff;
use App\Livewire\Admin\PaymentApproval;
use App\Livewire\Admin\PosSales;
use App\Livewire\Admin\ProductBrandlist;
use App\Livewire\Admin\ProductCategorylist;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\ProductStockDetails;
use App\Livewire\Admin\PurchaseOrderList;
use App\Livewire\Admin\Quotation;
use App\Livewire\Admin\QuotationList;
use App\Livewire\Admin\QuotationSystem;
use App\Livewire\Admin\Reports;
use App\Livewire\Admin\ReturnCheque;
use App\Livewire\Admin\ReturnList;
use App\Livewire\Admin\ReturnProduct;
use App\Livewire\Admin\ReturnSupplier;
use App\Livewire\Admin\SaleApproval;
use App\Livewire\Admin\SalesList;
use App\Livewire\Admin\SalesSystem;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\StaffAllocatedList;
use App\Livewire\Admin\StaffAttendance;
use App\Livewire\Admin\StaffDueDetails;
use App\Livewire\Admin\StaffPaymentApproval;
use App\Livewire\Admin\StaffProductAllocation;
use App\Livewire\Admin\StaffProductReentry;
use App\Livewire\Admin\StaffSalary;
use App\Livewire\Admin\StaffSalesView;
use App\Livewire\Admin\StaffSallary;
use App\Livewire\Admin\StaffStockDetails;
use App\Livewire\Admin\StoreBilling;
use App\Livewire\Admin\SupplierManage;
use App\Livewire\Admin\ViewPayments;
use App\Livewire\Admin\ViewStaffAllocatedProducts;
use App\Livewire\CustomLogin;
use App\Livewire\DeliveryMan\DeliveryManCompletedDeliveries;
use App\Livewire\DeliveryMan\DeliveryManDashboard;
use App\Livewire\DeliveryMan\DeliveryManFinance;
use App\Livewire\DeliveryMan\DeliveryManSalesSystem;
use App\Livewire\Salesman\SalesmanBilling;
use App\Livewire\Salesman\SalesmanCustomerDues;
use App\Livewire\Salesman\SalesmanDashboard;
use App\Livewire\Salesman\SalesmanProductList;
use App\Livewire\Salesman\SalesmanSalesList;
use App\Livewire\ShopStaff\DeliveryPacking as ShopStaffDeliveryPacking;
use App\Livewire\ShopStaff\ShopStaffDashboard;
use App\Livewire\ShopStaff\ShopStaffExpenses;
use App\Livewire\ShopStaff\ShopStaffProductList;
use App\Livewire\ShopStaff\ShopStaffSalesList;
use App\Livewire\ShopStaff\StoreBilling as ShopStaffStoreBilling;
use App\Livewire\Staff\Billing;
use App\Livewire\Staff\CustomerSaleManagement;
use App\Livewire\Staff\StaffDashboard;
use App\Livewire\Staff\StaffStockOverview;
use App\Livewire\WhatsAppOrderCheckout;
use Illuminate\Http\Request;
// Web Routes

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// System root route - redirect to dashboard/login
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

// Direct customer links
Route::get('/order-checkout/{token}', WhatsAppOrderCheckout::class)->name('whatsapp.checkout');
Route::get('/invoice/{token}', [\App\Http\Controllers\PublicInvoiceController::class, 'show'])->name('public.invoice');

Route::get('/robots.txt', function () {
    $robots = "User-agent: *\n";
    $robots .= "Disallow: /\n";

    return response($robots, 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');

// Public routes
Route::get('/login', CustomLogin::class)->name('login')->middleware('guest');

// Product Image Upload (public - accessible from phone via QR code scan)
Route::get('/product-image', [ProductImageController::class, 'scanner'])->name('product-image.scanner');
Route::post('/product-image/lookup', [ProductImageController::class, 'lookup'])->name('product-image.lookup');
Route::get('/product-image/{identifier}', [ProductImageController::class, 'show'])->name('product-image.show');
Route::post('/product-image/{identifier}/upload', [ProductImageController::class, 'upload'])->name('product-image.upload');
Route::post('/product-image/{identifier}/delete', [ProductImageController::class, 'deleteImage'])->name('product-image.delete');
Route::post('/product-image/{identifier}/set-primary', [ProductImageController::class, 'setPrimaryImage'])->name('product-image.set-primary');
Route::post('/product-image/{identifier}/toggle-website-live', [ProductImageController::class, 'toggleWebsiteLive'])->name('product-image.toggle-website-live');
Route::get('/product-image-serve/{filename}', [ProductImageController::class, 'serveImage'])->name('product-image.serve');

// Public proxy route for gold price (avoids CORS and rate-limit from browser)
Route::get('/api/gold-price', function () {
    $cacheKey = 'gold_price_v1';

    // Cache the raw array data (not a Response) to avoid serialization issues
    $data = Cache::remember($cacheKey, 60, function () {
        try {
            $goldResp = Http::get('https://data-asg.goldprice.org/dbXRates/USD');
            if (! $goldResp->ok()) {
                return ['error' => 'gold_endpoint_failed', 'status' => 502];
            }
            $goldJson = $goldResp->json();
            $xauPrice = data_get($goldJson, 'items.0.xauPrice');
            if (! $xauPrice) {
                return ['error' => 'no_xau_price', 'status' => 502];
            }

            $gramsPerOunce = 31.1034768;
            $perGramUSD = $xauPrice / $gramsPerOunce;

            // Try exchange rate
            $rateResp = Http::get('https://open.er-api.com/v6/latest/USD');
            $lkrRate = null;
            if ($rateResp->ok()) {
                $rateJson = $rateResp->json();
                $lkrRate = data_get($rateJson, 'rates.LKR');
            }

            $perGramLKR = $lkrRate ? ($perGramUSD * $lkrRate) : null;

            return [
                'xau' => $xauPrice,
                'per_gram_usd' => $perGramUSD,
                'per_gram_lkr' => $perGramLKR,
                'lkr_rate' => $lkrRate,
                'ts' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            // return a simple array so it can be cached/returned safely
            return ['error' => 'exception', 'message' => $e->getMessage(), 'status' => 500];
        }
    });

    // If the cached data indicates an error, respond with appropriate status
    if (isset($data['error'])) {
        $status = $data['status'] ?? 500;

        return response()->json($data, $status);
    }

    return response()->json($data);
});

// Custom logout route
Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

// Routes that require authentication
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // Profile route - accessible to all authenticated users
    Route::get('/user/profile', function () {
        return view('profile.show');
    })->name('profile.show');

    // Generic dashboard route: redirect authenticated users to their role-specific dashboard
    Route::get('/dashboard', function () {
        $user = Auth::user();

        // If for some reason there's no authenticated user, send them to the welcome page
        if (! $user) {
            return redirect()->route('login');
        }

        // Admin users go to admin dashboard
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Staff users - redirect based on staff_type
        if ($user->isStaff()) {
            // Check staff type and redirect accordingly
            switch ($user->staff_type) {
                case 'salesman':
                    return redirect()->route('salesman.dashboard');
                case 'delivery_man':
                    return redirect()->route('delivery.dashboard');
                case 'shop_staff':
                    return redirect()->route('shop-staff.dashboard');
                default:
                    // If no staff_type set, use the general staff dashboard
                    return redirect()->route('staff.dashboard');
            }
        }

        // Fallback: redirect to a safe page (profile) instead of looping back to dashboard
        return redirect()->route('profile.show');
    })->name('dashboard');

    // Settings route - accessible to all authenticated users

    // API route for products (client-side caching)
    Route::get('/api/products/all', [ProductApiController::class, 'getAllProducts'])->name('api.products.all');

    // !! Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('/Product-list', Products::class)->name('Productes');
        Route::get('/fast-product-create', FastProductCreate::class)->name('fast-product-create');
        Route::get('/manage-variants', \App\Livewire\Admin\ProductVariants::class)->name('manage-variants');
        Route::get('/barcode-print', \App\Livewire\Admin\BarcodePrint::class)->name('barcode-print');
        Route::get('/product-transfer', \App\Livewire\Admin\ProductTransfer::class)->name('product-transfer');
        Route::get('/add-Product-brand', ProductBrandlist::class)->name('Product-brand');
        Route::get('/Product-category', ProductCategorylist::class)->name('Product-category');
        Route::get('/billing-page', BillingPage::class)->name('billing-page');
        Route::get('/manage-admin', ManageAdmin::class)->name('manage-admin');
        Route::get('/manage-staff', ManageStaff::class)->name('manage-staff');
        Route::get('/manage-customer', ManageCustomer::class)->name('manage-customer');
        Route::get('/Product-stock-details', ProductStockDetails::class)->name('Product-stock-details');
        Route::get('/staff-stock-details', StaffStockDetails::class)->name('staff-stock-details');

        Route::get('/staff-due-details', StaffDueDetails::class)->name('staff-due-details');
        Route::get('/customer-sale-details', CustomerSaleDetails::class)->name('customer-sale-details');

        Route::get('/view-payments', ViewPayments::class)->name('view-payments');
        Route::get('/admin/staff/{staffId}/reentry', \App\Livewire\Admin\StockReentry::class)->name('staff.reentry');
        Route::get('/store-billing', StoreBilling::class)->name('store-billing');
        Route::get('/print/sale/{id}', [PrintController::class, 'printSale'])->name('print.sale');
        Route::get('/print/dummy-sale-thermal', [PrintController::class, 'printDummySaleThermal'])->name('print.dummy-sale-thermal');
        Route::get('/print/dummy-sale-a4', [PrintController::class, 'printDummySaleA4'])->name('print.dummy-sale-a4');
        Route::get('/print/delivery-label/{id}', [PrintController::class, 'printDeliveryLabel'])->name('print.delivery-label');
        Route::get('/print/sale/{id}/download', [PrintController::class, 'downloadSale'])->name('print.sale.download');
        Route::get('/print/quotation/{id}', [PrintController::class, 'printQuotation'])->name('quotation.print');
        Route::get('/due-payments', AdminDuePayments::class)->name('due-payments');
        Route::get('/staff-attendance', StaffAttendance::class)->name('staff-attendance');
        Route::get('/staff-salary', StaffSallary::class)->name('staff-salary');
        Route::get('/staff-salary-management', StaffSalary::class)->name('staff-salary-management');
        Route::get('/loan-management', LoanManage::class)->name('loan-management');
        Route::get('/sales-system', SalesSystem::class)->name('sales-system');
        Route::get('/staff-product-allocation', StaffProductAllocation::class)->name('staff-product-allocation');
        Route::get('/staff-allocated-list', StaffAllocatedList::class)->name('staff-allocated-list');
        Route::get('/staff-allocated-products/{staffId}', ViewStaffAllocatedProducts::class)->name('staff-allocated-products.view');
        Route::get('/staff-product-reentry/{staffId}', StaffProductReentry::class)->name('staff-product-reentry');
        Route::get('/staff-sales', StaffSalesView::class)->name('staff-sales');
        Route::get('/staff-sales-detail/{staffId}', \App\Livewire\Admin\StaffSalesDetail::class)->name('staff-sales-detail');
        Route::get('/staff-payment-approval', StaffPaymentApproval::class)->name('staff-payment-approval');
        Route::get('/pos-sales', PosSales::class)->name('pos-sales');
        Route::get('/custom-requests', \App\Livewire\Admin\CustomProductRequests::class)->name('custom-requests');
        Route::get('/custom-products-manage', \App\Livewire\Admin\CustomProductManagement::class)->name('custom-products-manage');

        Route::get('/supplier-management', SupplierManage::class)->name('supplier-management');
        Route::get('/quotation', Quotation::class)->name('quotation');
        Route::get('/goods-receive-note', GRN::class)->name('grn');
        Route::get('/expenses', Expenses::class)->name('expenses');
        Route::get('/day-summary', Income::class)->name('income');

        Route::get('/systemsetting', Settings::class)->name('systemsetting');
        Route::get('/reports', Reports::class)->name('reports');
        Route::get('/analytics', Analytics::class)->name('analytics');
        Route::get('/profit-loss', \App\Livewire\Admin\ProfitLoss::class)->name('profit-loss');
        Route::get('/quotation-system', QuotationSystem::class)->name('quotation-system');
        Route::get('/quotation-list', QuotationList::class)->name('quotation-list');
        Route::get('/sales-list', SalesList::class)->name('sales-list');
        Route::get('/settings', Settings::class)->name('settings');
        Route::get('/return-product', ReturnProduct::class)->name('return-product');
        Route::get('/purchase-order-list', PurchaseOrderList::class)->name('purchase-order-list');
        Route::get('/return-list', ReturnList::class)->name('return-list');
        Route::get('/add-customer-receipt', AddCustomerReceipt::class)->name('add-customer-receipt');
        Route::get('/cheque-list', ChequeList::class)->name('cheque-list');
        Route::get('/return-cheque', ReturnCheque::class)->name('return-cheque');
        Route::get('/list-customer-receipt', ListCustomerReceipt::class)->name('list-customer-receipt');
        Route::get('/add-supplier-receipt', AddSupplierReceipt::class)->name('add-supplier-receipt');
        Route::get('/list-supplier-receipt', ListSupplierReceipt::class)->name('list-supplier-receipt');
        Route::get('/return-supplier', ReturnSupplier::class)->name('return-supplier');
        Route::get('/list-supplier-return', ListSupplierReturn::class)->name('list-supplier-return');
        Route::get('/cash-deposit', DaySummary::class)->name('day-summary');
        Route::get('/deposits', Deposits::class)->name('deposits');
        Route::get('/register-report/{sessionId}', DaySummaryDetails::class)->name('day-summary-details');
        // Route to reopen today's closed POS session (AJAX)
        Route::post('/reopen-pos-session', [StoreBilling::class, 'reopenPOSSession'])->name('reopen-pos-session');
    });
    Route::post('/admin/update-cash', [CashController::class, 'updateCashInHand'])
        ->name('admin.updateCashInHand')
        ->middleware(['auth', 'role:admin']);

    Route::get('/admin/check-pos-session', [CashController::class, 'checkPOSSession'])
        ->name('admin.check-pos-session')
        ->middleware(['auth', 'role:admin']);

    // !! Staff routes - All admin routes available to staff (permissions control access)
    Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {
        // Dashboard
        Route::get('/dashboard', StaffDashboard::class)->name('dashboard');

        // Products
        Route::get('/Product-list', Products::class)->name('Productes');
        Route::get('/fast-product-create', FastProductCreate::class)->name('fast-product-create');
        Route::get('/add-Product-brand', ProductBrandlist::class)->name('Product-brand');
        Route::get('/Product-category', ProductCategorylist::class)->name('Product-category');
        Route::get('/Product-stock-details', ProductStockDetails::class)->name('Product-stock-details');

        // Sales
        Route::get('/billing', Billing::class)->name('billing');
        Route::get('/billing-page', BillingPage::class)->name('billing-page');
        Route::get('/sales-system', \App\Livewire\Staff\StaffSalesSystem::class)->name('sales-system');
        Route::get('/pos-sales', PosSales::class)->name('pos-sales');
        Route::get('/sales-list', \App\Livewire\Staff\StaffSalesList::class)->name('sales-list');
        Route::get('/store-billing', StoreBilling::class)->name('store-billing');
        Route::get('/print/sale/{id}', [PrintController::class, 'printSale'])->name('print.sale');
        Route::get('/print/delivery-label/{id}', [PrintController::class, 'printDeliveryLabel'])->name('print.delivery-label');
        Route::get('/print/sale/{id}/download', [PrintController::class, 'downloadSale'])->name('print.sale.download');

        // Customers
        Route::get('/manage-customer', ManageCustomer::class)->name('manage-customer');
        Route::get('/customer-sale-details', CustomerSaleDetails::class)->name('customer-sale-details');
        Route::get('/customer-sale-management', CustomerSaleManagement::class)->name('customer-sale-management');

        // Stock/Inventory
        Route::get('/staff-stock-overview', StaffStockOverview::class)->name('staff-stock-overview');
        Route::get('/staff-stock-details', StaffStockDetails::class)->name('staff-stock-details');

        // Purchases
        Route::get('/goods-receive-note', GRN::class)->name('grn');
        Route::get('/purchase-order-list', PurchaseOrderList::class)->name('purchase-order-list');
        Route::get('/supplier-management', SupplierManage::class)->name('supplier-management');

        // Customers
        Route::get('/manage-customers', \App\Livewire\Staff\ManageCustomer::class)->name('manage-customers');

        // Quotations
        Route::get('/quotation-system', \App\Livewire\Staff\StaffQuotationSystem::class)->name('quotation-system');
        Route::get('/quotation-list', \App\Livewire\Staff\StaffQuotationList::class)->name('quotation-list');
        Route::get('/print/quotation/{id}', [PrintController::class, 'printQuotation'])->name('print.quotation');

        // Returns
        Route::get('/return-add', \App\Livewire\Staff\StaffReturnManagement::class)->name('return-add');
        Route::get('/return-list', \App\Livewire\Staff\StaffReturnList::class)->name('return-list');
        Route::get('/return-supplier', ReturnSupplier::class)->name('return-supplier');
        Route::get('/list-supplier-return', ListSupplierReturn::class)->name('list-supplier-return');

        // Expenses
        Route::get('/expenses', \App\Livewire\Staff\StaffExpenseManagement::class)->name('expenses');

        // Payments
        Route::get('/due-payments', \App\Livewire\Staff\AddPayment::class)->name('due-payments');
        Route::get('/payments-list', \App\Livewire\Staff\PaymentsList::class)->name('payments-list');
        Route::get('/view-payments', ViewPayments::class)->name('view-payments');
        Route::get('/add-customer-receipt', AddCustomerReceipt::class)->name('add-customer-receipt');
        Route::get('/list-customer-receipt', ListCustomerReceipt::class)->name('list-customer-receipt');
        Route::get('/add-supplier-receipt', AddSupplierReceipt::class)->name('add-supplier-receipt');
        Route::get('/list-supplier-receipt', ListSupplierReceipt::class)->name('list-supplier-receipt');

        // Cheques/Banks
        Route::get('/cheque-list', ChequeList::class)->name('cheque-list');
        Route::get('/return-cheque', ReturnCheque::class)->name('return-cheque');

        // Finance
        Route::get('/income', Income::class)->name('income');
        Route::get('/loan-management', LoanManage::class)->name('loan-management');

        // HR/Staff Management
        Route::get('/manage-staff', ManageStaff::class)->name('manage-staff');
        Route::get('/staff-attendance', StaffAttendance::class)->name('staff-attendance');
        Route::get('/staff-salary', StaffSallary::class)->name('staff-salary');
        Route::get('/staff-due-details', StaffDueDetails::class)->name('staff-due-details');

        // Reports & Analytics
        Route::get('/reports', Reports::class)->name('reports');
        Route::get('/analytics', Analytics::class)->name('analytics');

        // Settings
        Route::get('/settings', Settings::class)->name('settings');
    });

    // !! Export routes (accessible to authenticated users)
    Route::get('/Productes/export', [ProductsExportController::class, 'export'])->name('Productes.export');
    Route::get('/staff-sales/export', [StaffSaleExportController::class, 'export'])->name('staff-sales.export');

    // Receipt download (accessible to authenticated users)
    Route::get('/receipts/{id}/download', [ReceiptController::class, 'download'])->name('receipts.download');

    // Export staff stock details
    Route::get('/export/staff-stock', function () {
        return app(StaffStockDetails::class)->exportToCSV();
    })->name('export.staff-stock');

    // Test route for product history
    Route::get('/test/product-history/{id}', function ($id) {
        $product = \App\Models\ProductDetail::with(['price', 'stock'])->findOrFail($id);

        // Debug: Check raw sale items count
        $rawCount = \App\Models\SaleItem::where('product_id', $id)->count();

        // Debug: Get raw sale items
        $rawSaleItems = \App\Models\SaleItem::where('product_id', $id)->get();

        // Load sales history with join
        $salesItems = \App\Models\SaleItem::with(['sale.customer', 'sale.user'])
            ->where('sale_items.product_id', $id)
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'sale_items.*',
                'sales.invoice_number',
                'sales.sale_type',
                'sales.customer_type',
                'sales.payment_type',
                'sales.payment_status',
                'sales.status as sale_status',
                'sales.created_at as sale_date'
            )
            ->orderBy('sales.created_at', 'desc')
            ->get();

        $salesHistory = $salesItems->map(function ($sale) {
            return [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'sale_type' => $sale->sale_type ?? 'regular',
                'customer_type' => $sale->customer_type ?? 'walk-in',
                'quantity' => $sale->quantity,
                'unit_price' => $sale->unit_price,
                'discount_per_unit' => $sale->discount_per_unit ?? 0,
                'total_discount' => $sale->total_discount ?? 0,
                'total' => $sale->total,
                'payment_type' => $sale->payment_type ?? 'cash',
                'payment_status' => $sale->payment_status ?? 'unpaid',
                'sale_status' => $sale->sale_status ?? 'completed',
                'sale_date' => $sale->sale_date,
                'customer_name' => $sale->sale && $sale->sale->customer ? $sale->sale->customer->name : 'Walk-in Customer',
                'customer_phone' => $sale->sale && $sale->sale->customer ? $sale->sale->customer->phone : 'N/A',
                'user_name' => $sale->sale && $sale->sale->user ? $sale->sale->user->name : 'N/A',
            ];
        })->toArray();

        // Load purchases history
        $purchaseItems = \App\Models\PurchaseOrderItem::with(['order.supplier'])
            ->where('purchase_order_items.product_id', $id)
            ->join('purchase_orders', 'purchase_order_items.order_id', '=', 'purchase_orders.id')
            ->select(
                'purchase_order_items.*',
                'purchase_orders.order_code',
                'purchase_orders.order_date',
                'purchase_orders.received_date',
                'purchase_orders.status as order_status'
            )
            ->orderBy('purchase_orders.order_date', 'desc')
            ->get();

        $purchasesHistory = $purchaseItems->map(function ($purchase) {
            $total = $purchase->received_quantity * $purchase->unit_price;
            if (isset($purchase->discount) && $purchase->discount > 0) {
                $total -= $purchase->discount;
            }

            return [
                'id' => $purchase->id,
                'order_code' => $purchase->order_code,
                'order_date' => $purchase->order_date,
                'received_date' => $purchase->received_date ?? 'Pending',
                'quantity' => $purchase->quantity,
                'received_quantity' => $purchase->received_quantity,
                'unit_price' => $purchase->unit_price,
                'discount' => $purchase->discount ?? 0,
                'total' => $total,
                'order_status' => $purchase->order_status ?? 'pending',
                'supplier_name' => $purchase->order && $purchase->order->supplier ? $purchase->order->supplier->name : 'N/A',
                'supplier_phone' => $purchase->order && $purchase->order->supplier ? $purchase->order->supplier->phone : 'N/A',
            ];
        })->toArray();

        // Load returns history
        $returns = \App\Models\ReturnsProduct::with(['sale.customer', 'product'])
            ->where('returns_products.product_id', $id)
            ->join('sales', 'returns_products.sale_id', '=', 'sales.id')
            ->select(
                'returns_products.*',
                'sales.invoice_number'
            )
            ->orderBy('returns_products.created_at', 'desc')
            ->get();

        $returnsHistory = $returns->map(function ($return) {
            return [
                'id' => $return->id,
                'invoice_number' => $return->invoice_number,
                'return_quantity' => $return->return_quantity,
                'selling_price' => $return->selling_price ?? 0,
                'total_amount' => $return->total_amount ?? 0,
                'notes' => $return->notes ?? 'No notes provided',
                'return_date' => $return->created_at,
                'customer_name' => $return->sale && $return->sale->customer ? $return->sale->customer->name : 'Walk-in Customer',
                'customer_phone' => $return->sale && $return->sale->customer ? $return->sale->customer->phone : 'N/A',
            ];
        })->toArray();

        // Load quotations history
        $quotations = \App\Models\Quotation::with(['creator', 'customer'])
            ->where('status', '!=', 'draft')
            ->orderBy('quotation_date', 'desc')
            ->get();

        $quotationsHistory = [];

        foreach ($quotations as $quotation) {
            $items = is_array($quotation->items) ? $quotation->items : json_decode($quotation->items, true);

            if (! empty($items)) {
                foreach ($items as $item) {
                    if (isset($item['product_id']) && $item['product_id'] == $id) {
                        $quotationsHistory[] = [
                            'id' => $quotation->id,
                            'quotation_number' => $quotation->quotation_number,
                            'reference_number' => $quotation->reference_number ?? 'N/A',
                            'customer_name' => $quotation->customer_name ?? ($quotation->customer->name ?? 'N/A'),
                            'customer_phone' => $quotation->customer_phone ?? ($quotation->customer->phone ?? 'N/A'),
                            'customer_email' => $quotation->customer_email ?? 'N/A',
                            'quotation_date' => $quotation->quotation_date,
                            'valid_until' => $quotation->valid_until,
                            'status' => $quotation->status,
                            'quantity' => $item['quantity'] ?? 0,
                            'unit_price' => $item['unit_price'] ?? 0,
                            'discount' => $item['discount'] ?? 0,
                            'total' => $item['total'] ?? 0,
                            'product_name' => $item['product_name'] ?? 'N/A',
                            'product_code' => $item['product_code'] ?? 'N/A',
                            'created_by_name' => $quotation->creator->name ?? 'N/A',
                        ];
                    }
                }
            }
        }

        return view('test.product-history', [
            'product' => $product,
            'salesHistory' => $salesHistory,
            'purchasesHistory' => $purchasesHistory,
            'returnsHistory' => $returnsHistory,
            'quotationsHistory' => $quotationsHistory,
            'rawCount' => $rawCount,
            'rawSaleItems' => $rawSaleItems,
            'salesItems' => $salesItems,
            'historyTab' => 'sales',
        ]);
    })->name('test.product-history');
});

// ============================================================================
// STAFF TYPE SPECIFIC ROUTES
// ============================================================================

// Admin Approval Routes (added to admin group)
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/sale-approval', SaleApproval::class)->name('sale-approval');
        Route::get('/payment-approval', PaymentApproval::class)->name('payment-approval');
        Route::get('/staff-expense-approval', \App\Livewire\Admin\StaffExpenseApproval::class)->name('staff-expense-approval');
    });

// Salesman Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'staff_type:salesman'])
    ->prefix('salesman')
    ->name('salesman.')
    ->group(function () {
        Route::get('/dashboard', SalesmanDashboard::class)->name('dashboard');
        Route::get('/billing', SalesmanBilling::class)->name('billing');
        Route::get('/billing/{saleId}/edit', SalesmanBilling::class)->name('billing.edit');
        Route::get('/products', SalesmanProductList::class)->name('products');
        Route::get('/sales', SalesmanSalesList::class)->name('sales');
        Route::get('/customer-dues', SalesmanCustomerDues::class)->name('customer-dues');
        Route::get('/expenses', \App\Livewire\Salesman\SalesmanExpenses::class)->name('expenses');
        Route::get('/salary', \App\Livewire\Salesman\SalesmanSalary::class)->name('salary');
    });

// Delivery Man Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'staff_type:delivery_man'])
    ->prefix('delivery')
    ->name('delivery.')
    ->group(function () {
        Route::get('/dashboard', DeliveryManDashboard::class)->name('dashboard');
        Route::get('/orders', DeliveryManDashboard::class)->name('orders');
        Route::get('/orders/{id}', \App\Livewire\DeliveryMan\DeliveryOrderDetail::class)->name('order-detail');
        Route::get('/finance', DeliveryManFinance::class)->name('finance');
        Route::get('/pending', function () {
            return redirect()->route('delivery.dashboard', ['status' => 'pending']);
        })->name('pending');
        Route::get('/completed', DeliveryManCompletedDeliveries::class)->name('completed');
        Route::get('/domex-import', \App\Livewire\DeliveryMan\DomexRemittanceImport::class)->name('domex-import');
        Route::get('/payments', DeliveryManFinance::class)->name('payments');
        Route::get('/payment-list', DeliveryManFinance::class)->name('payment-list');
        Route::get('/expenses', \App\Livewire\DeliveryMan\DeliveryManExpenses::class)->name('expenses');
        Route::get('/sales-system', DeliveryManSalesSystem::class)->name('sales-system');
        Route::get('/print/sale/{id}', [PrintController::class, 'printSale'])->name('print.sale');
        Route::get('/print/delivery-label/{id}', [PrintController::class, 'printDeliveryLabel'])->name('print.delivery-label');
    });

// Shop Staff Routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'staff_type:shop_staff'])
    ->prefix('shop-staff')
    ->name('shop-staff.')
    ->group(function () {
        Route::get('/dashboard', ShopStaffDashboard::class)->name('dashboard');
        Route::get('/products', ShopStaffProductList::class)->name('products');
        Route::get('/store-billing', ShopStaffStoreBilling::class)->name('store-billing');
        Route::get('/sales', ShopStaffSalesList::class)->name('sales');
        Route::get('/sold-products', \App\Livewire\ShopStaff\SoldProducts::class)->name('sold-products');
        Route::get('/due-sales', \App\Livewire\ShopStaff\ShopStaffDueSales::class)->name('due-sales');
        Route::get('/expenses', ShopStaffExpenses::class)->name('expenses');
        Route::get('/purchasing-expenses', \App\Livewire\ShopStaff\PurchasingExpenses::class)->name('purchasing-expenses');
        Route::get('/daily-savings', \App\Livewire\ShopStaff\DailySavings::class)->name('daily-savings');
        Route::get('/deposits', \App\Livewire\ShopStaff\DailySavings::class)->name('deposits');
        Route::get('/salary', \App\Livewire\ShopStaff\ShopStaffSalary::class)->name('salary');
        Route::get('/quotations', \App\Livewire\ShopStaff\ShopStaffQuotationList::class)->name('quotation-list');
        Route::get('/quotation-billing', \App\Livewire\ShopStaff\ShopStaffQuotationBilling::class)->name('quotation-billing');
        Route::get('/print/quotation/{id}', [App\Http\Controllers\Admin\PrintController::class, 'printQuotationThermal'])->name('print.quotation');
        Route::get('/print/sale/{id}', [App\Http\Controllers\Admin\PrintController::class, 'printSaleThermal'])->name('print.sale');
        Route::get('/print/dummy-sale-thermal', [App\Http\Controllers\Admin\PrintController::class, 'printDummySaleThermal'])->name('print.dummy-sale-thermal');
        Route::get('/print/dummy-sale-a4', [App\Http\Controllers\Admin\PrintController::class, 'printDummySaleA4'])->name('print.dummy-sale-a4');
        Route::get('/delivery-packing', ShopStaffDeliveryPacking::class)->name('delivery-packing');
        Route::get('/manage-employees', \App\Livewire\ShopStaff\ManageEmployees::class)->name('manage-employees');
        Route::get('/gold-plating', \App\Livewire\ShopStaff\GoldPlating::class)->name('gold-plating');
        Route::get('/print/gold-plating/{id}', [App\Http\Controllers\Admin\PrintController::class, 'printGoldPlatingThermal'])->name('print.gold-plating');
        Route::get('/handmade-purchasing', \App\Livewire\ShopStaff\HandmadePurchasing::class)->name('handmade-purchasing');
        Route::get('/handmade/purchasing', \App\Livewire\ShopStaff\HandmadePurchasing::class)->name('handmade.purchasing');
    });
