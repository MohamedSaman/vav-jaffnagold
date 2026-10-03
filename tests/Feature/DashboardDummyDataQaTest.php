<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\DashboardController;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\DeliveryMan\DeliveryManDashboard;
use App\Livewire\Salesman\SalesmanDashboard;
use App\Livewire\ShopStaff\ShopStaffDashboard;
use App\Livewire\Staff\StaffDashboard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Tests\TestCase;

class DashboardDummyDataQaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-04-07 12:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_dashboard_functions_work_with_dummy_data(): void
    {
        $data = $this->seedDashboardFixture();
        $this->actingAs($data['admin']);

        $component = app(AdminDashboard::class);
        $component->mount();

        $this->assertSame(960.0, (float) $component->totalSales);
        $this->assertSame(135.0, (float) $component->totalDueAmount);
        $this->assertSame(825.0, (float) $component->totalRevenue);
        $this->assertSame(85.9, (float) $component->revenuePercentage);
        $this->assertSame(14.1, (float) $component->duePercentage);
        $this->assertSame(50.0, (float) $component->previousMonthRevenue);
        $this->assertSame(1550.0, (float) $component->revenueChangePercentage);
        $this->assertSame(3, (int) $component->fullPaidCount);
        $this->assertSame(450.0, (float) $component->fullPaidAmount);
        $this->assertSame(7, (int) $component->partialPaidCount);
        $this->assertSame(135.0, (float) $component->partialPaidAmount);
        $this->assertSame(30, (int) $component->totalStock);
        $this->assertSame(8, (int) $component->soldStock);
        $this->assertSame(22, (int) $component->availableStock);
        $this->assertSame(3, (int) $component->damagedStock);
        $this->assertSame(26.7, (float) $component->soldPercentage);
        $this->assertSame(73.3, (float) $component->availablePercentage);
        $this->assertSame(10.0, (float) $component->damagedPercentage);
        $this->assertSame(50.0, (float) $component->damagedValue);
        $this->assertSame(500.0, (float) $component->totalInventoryValue);
        $this->assertSame(370.0, (float) $component->totalAvailableInventory);
        $this->assertSame(4, (int) $component->totalStaffCount);
        $this->assertSame(4, (int) $component->staffWithAssignmentsCount);
        $this->assertSame(100.0, (float) $component->staffAssignmentPercentage);
        $this->assertSame(1000.0, (float) $component->totalStaffSalesValue);
        $this->assertSame(135.0, (float) $component->totalStaffDueAmount);
        $this->assertCount(5, $component->recentSales);
        $this->assertSame('Walk-in Customer', $component->recentSales[0]->name);
        $this->assertCount(2, $component->ProductInventory);
        $this->assertSame(7, (int) $component->ProductInventory[0]->available_stock);
        $this->assertCount(7, $component->dailySales);
        $this->assertSame(900.0, (float) $component->dailySales[6]['total_sales']);
        $this->assertCount(4, $component->staffSales);
        $this->assertInstanceOf(View::class, $component->render());

        $component->openTodaySummary();

        $this->assertTrue($component->showTodaySummary);
        $this->assertSame(1800.0, (float) $component->summaryData['openingBalance']);
        $this->assertSame(900.0, (float) $component->summaryData['totalSale']);
        $this->assertSame(9, (int) $component->summaryData['totalSaleCount']);
        $this->assertSame(600.0, (float) $component->summaryData['cashSale']);
        $this->assertSame(175.0, (float) $component->summaryData['bankSale']);
        $this->assertSame(0.0, (float) $component->summaryData['onlineSale']);
        $this->assertSame(67.0, (float) $component->summaryData['todayExpenses']);
        $this->assertSame(833.0, (float) $component->summaryData['todayRevenue']);
        $this->assertSame(590.0, (float) $component->summaryData['totalCost']);
        $this->assertSame(310.0, (float) $component->summaryData['grossProfit']);
        $this->assertSame(243.0, (float) $component->summaryData['netProfit']);
        $this->assertSame(125.0, (float) $component->summaryData['todayDue']);

        $component->closeTodaySummary();

        $this->assertFalse($component->showTodaySummary);
        $this->assertSame([], $component->summaryData);
    }

    public function test_staff_dashboard_functions_work_with_dummy_data(): void
    {
        $data = $this->seedDashboardFixture();
        $this->actingAs($data['generic_staff']);

        $component = app(StaffDashboard::class);
        $component->mount();

        $this->assertSame(3, (int) $component->totalSalesCount);
        $this->assertSame(2, (int) $component->todaySalesCount);
        $this->assertSame(180.0, (float) $component->todaySalesAmount);
        $this->assertSame(1, (int) $component->pendingSalesCount);
        $this->assertSame(2, (int) $component->confirmedSalesCount);
        $this->assertSame(270.0, (float) $component->availableStockValue);
        $this->assertSame(30.0, (float) $component->totalDueAmount);
        $this->assertSame(210.0, (float) $component->totalRevenue);
        $this->assertSame(87.5, (float) $component->revenuePercentage);
        $this->assertSame(12.5, (float) $component->duePercentage);
        $this->assertSame(1, (int) $component->fullPaidCount);
        $this->assertSame(100.0, (float) $component->fullPaidAmount);
        $this->assertSame(2, (int) $component->partialPaidCount);
        $this->assertSame(13, (int) $component->totalInventory);
        $this->assertSame(3, (int) $component->soldInventory);
        $this->assertSame(23.1, (float) $component->soldPercentage);
        $this->assertSame(2, (int) $component->totalCustomers);
        $this->assertSame(['retail' => 1, 'wholesale' => 1], $component->customerTypes);
        $this->assertCount(3, $component->recentSales);
        $this->assertSame('Walk-in Customer', $component->recentSales[0]->name);
        $this->assertCount(2, $component->productInventory);
        $this->assertCount(7, $component->dailySales);
        $this->assertSame(180.0, (float) $component->dailySales[6]['total_sales']);
        $this->assertCount(3, $component->customerPaymentStats);
        $this->assertInstanceOf(View::class, $component->render());
    }

    public function test_shop_staff_dashboard_functions_work_with_dummy_data(): void
    {
        $data = $this->seedDashboardFixture();
        $this->actingAs($data['shop_staff']);

        $component = app(ShopStaffDashboard::class);
        $component->mount();

        $this->assertSame(260.0, (float) $component->totalStaffSales);
        $this->assertSame(260.0, (float) $component->todaySales);
        $this->assertSame(200.0, (float) $component->cashAmount);
        $this->assertSame(200.0, (float) $component->todayCashAmount);
        $this->assertSame(30, (int) $component->totalStock);
        $this->assertSame(22, (int) $component->availableStock);
        $this->assertSame(100.0, (float) $component->salePercentage);
        $this->assertSame(73.3, (float) $component->availablePercentage);
        $this->assertCount(2, $component->recentSales);
        $this->assertSame('Walk-in Customer', $component->recentSales[0]->customer_name);
        $this->assertCount(1, $component->productInventory);
        $this->assertCount(30, $component->dailySales);
        $this->assertSame(260.0, (float) $component->dailySales[29]['total_sales']);
        $this->assertInstanceOf(View::class, $component->render());

        $component->openTodaySummary();

        $this->assertTrue($component->showTodaySummary);
        $this->assertSame(500.0, (float) $component->summaryData['openingBalance']);
        $this->assertSame(260.0, (float) $component->summaryData['totalSale']);
        $this->assertSame(2, (int) $component->summaryData['totalSaleCount']);
        $this->assertSame(200.0, (float) $component->summaryData['cashSale']);
        $this->assertSame(0.0, (float) $component->summaryData['bankSale']);
        $this->assertSame(15.0, (float) $component->summaryData['todayExpenses']);
        $this->assertSame(245.0, (float) $component->summaryData['todayRevenue']);
        $this->assertSame(140.0, (float) $component->summaryData['totalCost']);
        $this->assertSame(120.0, (float) $component->summaryData['grossProfit']);
        $this->assertSame(105.0, (float) $component->summaryData['netProfit']);
        $this->assertSame(10.0, (float) $component->summaryData['todayDue']);
        $this->assertSame(30.0, (float) $component->summaryData['receivedDueTotal']);
        $this->assertSame(215.0, (float) $component->summaryData['netCashAfterExpenses']);

        $pdfResponse = $component->downloadDailySummary();
        $this->assertNotNull($pdfResponse);

        $component->closeTodaySummary();

        $this->assertFalse($component->showTodaySummary);
        $this->assertSame([], $component->summaryData);
    }

    public function test_salesman_dashboard_functions_work_with_dummy_data(): void
    {
        $data = $this->seedDashboardFixture();
        $this->actingAs($data['salesman']);

        $component = app(SalesmanDashboard::class);
        $component->mount();

        $this->assertSame(3, (int) $component->totalSales);
        $this->assertSame(0, (int) $component->pendingSales);
        $this->assertSame(2, (int) $component->approvedSales);
        $this->assertSame(1, (int) $component->rejectedSales);
        $this->assertSame(3, (int) $component->totalCustomers);
        $this->assertSame(1, (int) $component->pendingDeliveries);
        $this->assertSame(1, (int) $component->inTransitDeliveries);
        $this->assertSame(0, (int) $component->completedDeliveries);
        $this->assertSame(30.0, (float) $component->totalDueAmount);
        $this->assertSame(1, (int) $component->customersWithDues);
        $this->assertCount(3, $component->recentSales);
        $this->assertInstanceOf(View::class, $component->render());
    }

    public function test_delivery_dashboard_functions_work_with_dummy_data(): void
    {
        $data = $this->seedDashboardFixture();
        $this->actingAs($data['delivery']);

        $component = app(DeliveryManDashboard::class);
        $component->mount();

        $this->assertSame(2, (int) $component->pendingDeliveries);
        $this->assertSame(1, (int) $component->completedDeliveries);
        $this->assertSame(1, (int) $component->todaysDeliveries);
        $this->assertSame(1, (int) $component->pendingPayments);
        $this->assertSame(80.0, (float) $component->collectedAmount);
        $this->assertCount(2, $component->recentDeliveries);
        $this->assertInstanceOf(View::class, $component->render());
    }

    public function test_api_dashboard_functions_work_with_dummy_data(): void
    {
        $this->seedDashboardFixture();

        $controller = app(DashboardController::class);

        $indexResponse = $controller->index(Request::create('/api/dashboard', 'GET'));
        $indexData = $indexResponse->getData(true);

        $this->assertTrue($indexData['success']);
        $this->assertSame(200, $indexResponse->status());
        $this->assertSame(2, $indexData['data']['products']['total']);
        $this->assertSame(2, $indexData['data']['products']['active']);
        $this->assertSame(1, $indexData['data']['products']['low_stock']);
        $this->assertSame(22, $indexData['data']['stock']['total_available']);
        $this->assertSame(1, $indexData['data']['stock']['low_stock_count']);
        $this->assertSame(10, $indexData['data']['sales']['total']);
        $this->assertSame(960.0, (float) $indexData['data']['sales']['amount']);
        $this->assertSame(900.0, (float) $indexData['data']['sales']['today']);
        $this->assertSame(900.0, (float) $indexData['data']['sales']['this_month']);
        $this->assertSame(135.0, (float) $indexData['data']['finance']['total_due']);
        $this->assertSame(3, $indexData['data']['customers']['total']);
        $this->assertSame(1, $indexData['data']['purchases']['pending_orders']);
        $this->assertSame(900.0, (float) $indexData['data']['purchases']['month_total']);
        $this->assertCount(5, $indexData['data']['recent_sales']);
        $this->assertCount(1, $indexData['data']['low_stock_products']);

        $recentActivityResponse = $controller->recentActivity(Request::create('/api/recent-activity', 'GET', ['limit' => 5]));
        $recentActivityData = $recentActivityResponse->getData(true);

        $this->assertTrue($recentActivityData['success']);
        $this->assertSame(200, $recentActivityResponse->status());
        $this->assertCount(5, $recentActivityData['data']);
        $this->assertSame('purchase', $recentActivityData['data'][0]['type']);
        $this->assertSame('purchase', $recentActivityData['data'][1]['type']);
    }

    private function seedDashboardFixture(): array
    {
        $this->resetTables();

        $admin = $this->createUser('Admin User', 'admin@example.com', 'admin', null);
        $genericStaff = $this->createUser('Generic Staff', 'staff@example.com', 'staff', 'salesman');
        $salesman = $this->createUser('Sales Man', 'sales@example.com', 'staff', 'salesman');
        $delivery = $this->createUser('Delivery Man', 'delivery@example.com', 'staff', 'delivery_man');
        $shopStaff = $this->createUser('Shop Staff', 'shop@example.com', 'staff', 'shop_staff');

        $customerRetail = $this->createCustomer('Retail Customer', 'retail');
        $customerWholesale = $this->createCustomer('Wholesale Customer', 'wholesale');
        $customerThird = $this->createCustomer('Third Customer', 'retail');

        $brandId = DB::table('brand_lists')->insertGetId([
            'brand_name' => 'Brand A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryId = DB::table('category_lists')->insertGetId([
            'category_name' => 'Category A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supplierId = DB::table('product_suppliers')->insertGetId([
            'name' => 'Supplier A',
            'businessname' => 'Supplier Business',
            'contact' => 'Supplier Contact',
            'address' => 'Supplier Address',
            'email' => 'supplier@example.com',
            'phone' => '0770000000',
            'status' => 'active',
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $stockOneId = DB::table('product_stocks')->insertGetId([
            'product_id' => 0,
            'variant_id' => null,
            'variant_value' => null,
            'available_stock' => 7,
            'damage_stock' => 1,
            'total_stock' => 10,
            'sold_count' => 3,
            'restocked_quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stockTwoId = DB::table('product_stocks')->insertGetId([
            'product_id' => 0,
            'variant_id' => null,
            'variant_value' => null,
            'available_stock' => 15,
            'damage_stock' => 2,
            'total_stock' => 20,
            'sold_count' => 5,
            'restocked_quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $priceOneId = DB::table('product_prices')->insertGetId([
            'product_id' => 0,
            'variant_id' => null,
            'variant_value' => null,
            'pricing_mode' => 'single',
            'supplier_price' => 10,
            'selling_price' => 20,
            'retail_price' => 20,
            'wholesale_price' => 18,
            'distributor_price' => 17,
            'discount_price' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $priceTwoId = DB::table('product_prices')->insertGetId([
            'product_id' => 0,
            'variant_id' => null,
            'variant_value' => null,
            'pricing_mode' => 'single',
            'supplier_price' => 20,
            'selling_price' => 30,
            'retail_price' => 30,
            'wholesale_price' => 28,
            'distributor_price' => 26,
            'discount_price' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productOneId = DB::table('product_details')->insertGetId([
            'code' => 'P-001',
            'name' => 'Product One',
            'model' => 'M-1',
            'image' => null,
            'description' => null,
            'barcode' => 'BC001',
            'status' => 'active',
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'sale_bonus' => 0,
            'variant_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productTwoId = DB::table('product_details')->insertGetId([
            'code' => 'P-002',
            'name' => 'Product Two',
            'model' => 'M-2',
            'image' => null,
            'description' => null,
            'barcode' => 'BC002',
            'status' => 'active',
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'sale_bonus' => 0,
            'variant_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_stocks')->where('id', $stockOneId)->update(['product_id' => $productOneId]);
        DB::table('product_stocks')->where('id', $stockTwoId)->update(['product_id' => $productTwoId]);
        DB::table('product_prices')->where('id', $priceOneId)->update(['product_id' => $productOneId]);
        DB::table('product_prices')->where('id', $priceTwoId)->update(['product_id' => $productTwoId]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('staff_sales')->insert([
            [
                'staff_id' => $genericStaff->id,
                'admin_id' => $admin->id,
                'total_quantity' => 5,
                'total_value' => 100,
                'sold_quantity' => 2,
                'sold_value' => 50,
                'status' => 'assigned',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $salesman->id,
                'admin_id' => $admin->id,
                'total_quantity' => 10,
                'total_value' => 200,
                'sold_quantity' => 4,
                'sold_value' => 80,
                'status' => 'partial',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $delivery->id,
                'admin_id' => $admin->id,
                'total_quantity' => 15,
                'total_value' => 300,
                'sold_quantity' => 6,
                'sold_value' => 120,
                'status' => 'partial',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $shopStaff->id,
                'admin_id' => $admin->id,
                'total_quantity' => 20,
                'total_value' => 400,
                'sold_quantity' => 8,
                'sold_value' => 160,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('staff_products')->insert([
            [
                'product_id' => $productOneId,
                'staff_id' => $genericStaff->id,
                'quantity' => 5,
                'unit_price' => 20,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total_value' => 100,
                'sold_quantity' => 2,
                'sold_value' => 40,
                'status' => 'assigned',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => $productTwoId,
                'staff_id' => $genericStaff->id,
                'quantity' => 8,
                'unit_price' => 30,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total_value' => 240,
                'sold_quantity' => 1,
                'sold_value' => 30,
                'status' => 'partial',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('expenses')->insert([
            [
                'category' => 'Admin Ops',
                'expense_type' => 'daily',
                'amount' => 40,
                'date' => now()->toDateString(),
                'status' => 'approved',
                'description' => 'Admin expense',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Admin Ops',
                'expense_type' => 'monthly',
                'amount' => 20,
                'date' => now()->subMonth()->toDateString(),
                'status' => 'approved',
                'description' => 'Previous month expense',
                'created_at' => now()->subMonth(),
                'updated_at' => now()->subMonth(),
            ],
        ]);

        DB::table('staff_expenses')->insert([
            [
                'staff_id' => $genericStaff->id,
                'expense_type' => 'daily',
                'amount' => 12,
                'description' => 'Generic staff expense',
                'expense_date' => now()->toDateString(),
                'receipt_image' => null,
                'status' => 'approved',
                'admin_notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staff_id' => $shopStaff->id,
                'expense_type' => 'daily',
                'amount' => 15,
                'description' => 'Shop staff expense',
                'expense_date' => now()->toDateString(),
                'receipt_image' => null,
                'status' => 'approved',
                'admin_notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('pos_sessions')->insert([
            [
                'user_id' => $admin->id,
                'session_date' => now()->toDateString(),
                'opening_cash' => 1000,
                'closing_cash' => null,
                'total_sales' => 0,
                'cash_sales' => 0,
                'cheque_payment' => 0,
                'credit_card_payment' => 0,
                'bank_transfer' => 0,
                'late_payment_bulk' => 0,
                'refunds' => 0,
                'expenses' => 0,
                'cash_deposit_bank' => 0,
                'expected_cash' => 0,
                'cash_difference' => 0,
                'notes' => null,
                'status' => 'open',
                'opened_at' => now(),
                'closed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $genericStaff->id,
                'session_date' => now()->toDateString(),
                'opening_cash' => 300,
                'closing_cash' => null,
                'total_sales' => 0,
                'cash_sales' => 0,
                'cheque_payment' => 0,
                'credit_card_payment' => 0,
                'bank_transfer' => 0,
                'late_payment_bulk' => 0,
                'refunds' => 0,
                'expenses' => 0,
                'cash_deposit_bank' => 0,
                'expected_cash' => 0,
                'cash_difference' => 0,
                'notes' => null,
                'status' => 'open',
                'opened_at' => now(),
                'closed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $shopStaff->id,
                'session_date' => now()->toDateString(),
                'opening_cash' => 500,
                'closing_cash' => null,
                'total_sales' => 0,
                'cash_sales' => 0,
                'cheque_payment' => 0,
                'credit_card_payment' => 0,
                'bank_transfer' => 0,
                'late_payment_bulk' => 0,
                'refunds' => 0,
                'expenses' => 0,
                'cash_deposit_bank' => 0,
                'expected_cash' => 0,
                'cash_difference' => 0,
                'notes' => null,
                'status' => 'open',
                'opened_at' => now(),
                'closed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('purchase_orders')->insert([
            [
                'order_code' => 'PO-001',
                'supplier_id' => $supplierId,
                'order_date' => now()->toDateString(),
                'received_date' => null,
                'status' => 'pending',
                'total_amount' => 500,
                'due_amount' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_code' => 'PO-002',
                'supplier_id' => $supplierId,
                'order_date' => now()->toDateString(),
                'received_date' => now()->toDateString(),
                'status' => 'received',
                'total_amount' => 400,
                'due_amount' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('sales')->insert([
            [
                'sale_id' => 'S-001',
                'invoice_number' => 'INV-001',
                'sale_type' => 'pos',
                'customer_id' => $customerRetail,
                'walking_customer_name' => null,
                'walking_customer_phone' => null,
                'user_id' => $genericStaff->id,
                'customer_type' => 'retail',
                'subtotal' => 100,
                'discount_amount' => 0,
                'total_amount' => 100,
                'payment_type' => 'full',
                'payment_status' => 'paid',
                'status' => 'confirm',
                'due_amount' => 0,
                'notes' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'delivered_by' => null,
                'delivered_at' => null,
                'delivery_status' => 'delivered',
                'created_at' => Carbon::parse('2026-04-07 08:00:00'),
                'updated_at' => Carbon::parse('2026-04-07 08:00:00'),
            ],
            [
                'sale_id' => 'S-002',
                'invoice_number' => 'INV-002',
                'sale_type' => 'pos',
                'customer_id' => null,
                'walking_customer_name' => 'Walk-in Customer',
                'walking_customer_phone' => null,
                'user_id' => $genericStaff->id,
                'customer_type' => 'retail',
                'subtotal' => 80,
                'discount_amount' => 0,
                'total_amount' => 80,
                'payment_type' => 'partial',
                'payment_status' => 'partial',
                'status' => 'pending',
                'due_amount' => 20,
                'notes' => null,
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null,
                'delivered_by' => null,
                'delivered_at' => null,
                'delivery_status' => 'pending',
                'created_at' => Carbon::parse('2026-04-07 08:30:00'),
                'updated_at' => Carbon::parse('2026-04-07 08:30:00'),
            ],
            [
                'sale_id' => 'S-003',
                'invoice_number' => 'INV-003',
                'sale_type' => 'pos',
                'customer_id' => $customerWholesale,
                'walking_customer_name' => null,
                'walking_customer_phone' => null,
                'user_id' => $genericStaff->id,
                'customer_type' => 'wholesale',
                'subtotal' => 60,
                'discount_amount' => 0,
                'total_amount' => 60,
                'payment_type' => 'partial',
                'payment_status' => 'partial',
                'status' => 'confirm',
                'due_amount' => 10,
                'notes' => null,
                'approved_by' => $admin->id,
                'approved_at' => now()->subMonth(),
                'rejection_reason' => null,
                'delivered_by' => null,
                'delivered_at' => null,
                'delivery_status' => 'delivered',
                'created_at' => Carbon::parse('2026-03-15 09:00:00'),
                'updated_at' => Carbon::parse('2026-03-15 09:00:00'),
            ],
            [
                'sale_id' => 'S-004',
                'invoice_number' => 'INV-004',
                'sale_type' => 'pos',
                'customer_id' => $customerRetail,
                'walking_customer_name' => null,
                'walking_customer_phone' => null,
                'user_id' => $salesman->id,
                'customer_type' => 'retail',
                'subtotal' => 150,
                'discount_amount' => 0,
                'total_amount' => 150,
                'payment_type' => 'full',
                'payment_status' => 'paid',
                'status' => 'confirm',
                'due_amount' => 0,
                'notes' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'delivered_by' => null,
                'delivered_at' => null,
                'delivery_status' => 'pending',
                'created_at' => Carbon::parse('2026-04-07 09:00:00'),
                'updated_at' => Carbon::parse('2026-04-07 09:00:00'),
            ],
            [
                'sale_id' => 'S-005',
                'invoice_number' => 'INV-005',
                'sale_type' => 'pos',
                'customer_id' => $customerWholesale,
                'walking_customer_name' => null,
                'walking_customer_phone' => null,
                'user_id' => $salesman->id,
                'customer_type' => 'wholesale',
                'subtotal' => 90,
                'discount_amount' => 0,
                'total_amount' => 90,
                'payment_type' => 'partial',
                'payment_status' => 'partial',
                'status' => 'confirm',
                'due_amount' => 30,
                'notes' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'delivered_by' => null,
                'delivered_at' => null,
                'delivery_status' => 'in_transit',
                'created_at' => Carbon::parse('2026-04-07 09:30:00'),
                'updated_at' => Carbon::parse('2026-04-07 09:30:00'),
            ],
            [
                'sale_id' => 'S-006',
                'invoice_number' => 'INV-006',
                'sale_type' => 'pos',
                'customer_id' => $customerThird,
                'walking_customer_name' => null,
                'walking_customer_phone' => null,
                'user_id' => $salesman->id,
                'customer_type' => 'retail',
                'subtotal' => 70,
                'discount_amount' => 0,
                'total_amount' => 70,
                'payment_type' => 'partial',
                'payment_status' => 'partial',
                'status' => 'rejected',
                'due_amount' => 20,
                'notes' => null,
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => 'Rejected for QA',
                'delivered_by' => null,
                'delivered_at' => null,
                'delivery_status' => 'cancelled',
                'created_at' => Carbon::parse('2026-04-07 10:00:00'),
                'updated_at' => Carbon::parse('2026-04-07 10:00:00'),
            ],
            [
                'sale_id' => 'S-007',
                'invoice_number' => 'INV-007',
                'sale_type' => 'staff',
                'customer_id' => $customerRetail,
                'walking_customer_name' => null,
                'walking_customer_phone' => null,
                'user_id' => $delivery->id,
                'customer_type' => 'retail',
                'subtotal' => 120,
                'discount_amount' => 0,
                'total_amount' => 120,
                'payment_type' => 'partial',
                'payment_status' => 'partial',
                'status' => 'confirm',
                'due_amount' => 40,
                'notes' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'delivered_by' => $delivery->id,
                'delivered_at' => Carbon::parse('2026-04-07 10:30:00'),
                'delivery_status' => 'delivered',
                'created_at' => Carbon::parse('2026-04-07 10:30:00'),
                'updated_at' => Carbon::parse('2026-04-07 10:30:00'),
            ],
            [
                'sale_id' => 'S-008',
                'invoice_number' => 'INV-008',
                'sale_type' => 'staff',
                'customer_id' => $customerWholesale,
                'walking_customer_name' => null,
                'walking_customer_phone' => null,
                'user_id' => $delivery->id,
                'customer_type' => 'wholesale',
                'subtotal' => 30,
                'discount_amount' => 0,
                'total_amount' => 30,
                'payment_type' => 'partial',
                'payment_status' => 'partial',
                'status' => 'confirm',
                'due_amount' => 5,
                'notes' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'delivered_by' => null,
                'delivered_at' => null,
                'delivery_status' => 'pending',
                'created_at' => Carbon::parse('2026-04-07 11:00:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:00:00'),
            ],
            [
                'sale_id' => 'S-009',
                'invoice_number' => 'INV-009',
                'sale_type' => 'pos',
                'customer_id' => $customerRetail,
                'walking_customer_name' => null,
                'walking_customer_phone' => null,
                'user_id' => $shopStaff->id,
                'customer_type' => 'retail',
                'subtotal' => 200,
                'discount_amount' => 0,
                'total_amount' => 200,
                'payment_type' => 'full',
                'payment_status' => 'paid',
                'status' => 'confirm',
                'due_amount' => 0,
                'notes' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'delivered_by' => null,
                'delivered_at' => null,
                'delivery_status' => 'delivered',
                'created_at' => Carbon::parse('2026-04-07 11:30:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:30:00'),
            ],
            [
                'sale_id' => 'S-010',
                'invoice_number' => 'INV-010',
                'sale_type' => 'pos',
                'customer_id' => null,
                'walking_customer_name' => 'Walk-in Customer',
                'walking_customer_phone' => null,
                'user_id' => $shopStaff->id,
                'customer_type' => 'retail',
                'subtotal' => 60,
                'discount_amount' => 0,
                'total_amount' => 60,
                'payment_type' => 'partial',
                'payment_status' => 'partial',
                'status' => 'confirm',
                'due_amount' => 10,
                'notes' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'delivered_by' => null,
                'delivered_at' => null,
                'delivery_status' => 'delivered',
                'created_at' => Carbon::parse('2026-04-07 11:45:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:45:00'),
            ],
        ]);

        DB::table('sale_items')->insert([
            [
                'sale_id' => 1,
                'product_id' => $productOneId,
                'product_code' => 'P-001',
                'product_name' => 'Product One',
                'product_model' => 'M-1',
                'quantity' => 5,
                'unit_price' => 20,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 100,
                'created_at' => Carbon::parse('2026-04-07 08:00:00'),
                'updated_at' => Carbon::parse('2026-04-07 08:00:00'),
            ],
            [
                'sale_id' => 2,
                'product_id' => $productTwoId,
                'product_code' => 'P-002',
                'product_name' => 'Product Two',
                'product_model' => 'M-2',
                'quantity' => 4,
                'unit_price' => 20,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 80,
                'created_at' => Carbon::parse('2026-04-07 08:30:00'),
                'updated_at' => Carbon::parse('2026-04-07 08:30:00'),
            ],
            [
                'sale_id' => 3,
                'product_id' => $productOneId,
                'product_code' => 'P-001',
                'product_name' => 'Product One',
                'product_model' => 'M-1',
                'quantity' => 3,
                'unit_price' => 20,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 60,
                'created_at' => Carbon::parse('2026-03-15 09:00:00'),
                'updated_at' => Carbon::parse('2026-03-15 09:00:00'),
            ],
            [
                'sale_id' => 4,
                'product_id' => $productOneId,
                'product_code' => 'P-001',
                'product_name' => 'Product One',
                'product_model' => 'M-1',
                'quantity' => 5,
                'unit_price' => 30,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 150,
                'created_at' => Carbon::parse('2026-04-07 09:00:00'),
                'updated_at' => Carbon::parse('2026-04-07 09:00:00'),
            ],
            [
                'sale_id' => 5,
                'product_id' => $productTwoId,
                'product_code' => 'P-002',
                'product_name' => 'Product Two',
                'product_model' => 'M-2',
                'quantity' => 3,
                'unit_price' => 30,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 90,
                'created_at' => Carbon::parse('2026-04-07 09:30:00'),
                'updated_at' => Carbon::parse('2026-04-07 09:30:00'),
            ],
            [
                'sale_id' => 6,
                'product_id' => $productOneId,
                'product_code' => 'P-001',
                'product_name' => 'Product One',
                'product_model' => 'M-1',
                'quantity' => 7,
                'unit_price' => 10,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 70,
                'created_at' => Carbon::parse('2026-04-07 10:00:00'),
                'updated_at' => Carbon::parse('2026-04-07 10:00:00'),
            ],
            [
                'sale_id' => 7,
                'product_id' => $productTwoId,
                'product_code' => 'P-002',
                'product_name' => 'Product Two',
                'product_model' => 'M-2',
                'quantity' => 6,
                'unit_price' => 20,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 120,
                'created_at' => Carbon::parse('2026-04-07 10:30:00'),
                'updated_at' => Carbon::parse('2026-04-07 10:30:00'),
            ],
            [
                'sale_id' => 8,
                'product_id' => $productOneId,
                'product_code' => 'P-001',
                'product_name' => 'Product One',
                'product_model' => 'M-1',
                'quantity' => 2,
                'unit_price' => 15,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 30,
                'created_at' => Carbon::parse('2026-04-07 11:00:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:00:00'),
            ],
            [
                'sale_id' => 9,
                'product_id' => $productOneId,
                'product_code' => 'P-001',
                'product_name' => 'Product One',
                'product_model' => 'M-1',
                'quantity' => 10,
                'unit_price' => 20,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 200,
                'created_at' => Carbon::parse('2026-04-07 11:30:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:30:00'),
            ],
            [
                'sale_id' => 10,
                'product_id' => $productTwoId,
                'product_code' => 'P-002',
                'product_name' => 'Product Two',
                'product_model' => 'M-2',
                'quantity' => 2,
                'unit_price' => 30,
                'discount_per_unit' => 0,
                'total_discount' => 0,
                'total' => 60,
                'created_at' => Carbon::parse('2026-04-07 11:45:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:45:00'),
            ],
        ]);

        DB::table('payments')->insert([
            [
                'sale_id' => 1,
                'amount' => 100,
                'payment_method' => 'cash',
                'payment_reference' => null,
                'card_number' => null,
                'bank_name' => null,
                'is_completed' => 1,
                'payment_date' => Carbon::parse('2026-04-07 08:05:00'),
                'due_date' => null,
                'due_payment_method' => null,
                'due_payment_attachment' => null,
                'status' => 'paid',
                'notes' => null,
                'created_by' => $admin->id,
                'transfer_date' => null,
                'transfer_reference' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'collected_by' => null,
                'collected_at' => null,
                'created_at' => Carbon::parse('2026-04-07 08:05:00'),
                'updated_at' => Carbon::parse('2026-04-07 08:05:00'),
            ],
            [
                'sale_id' => 2,
                'amount' => 60,
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'BT-002',
                'card_number' => null,
                'bank_name' => 'Bank A',
                'is_completed' => 1,
                'payment_date' => Carbon::parse('2026-04-07 08:35:00'),
                'due_date' => null,
                'due_payment_method' => null,
                'due_payment_attachment' => null,
                'status' => 'approved',
                'notes' => null,
                'created_by' => $admin->id,
                'transfer_date' => Carbon::parse('2026-04-07')->toDateString(),
                'transfer_reference' => 'TR-002',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'collected_by' => null,
                'collected_at' => null,
                'created_at' => Carbon::parse('2026-04-07 08:35:00'),
                'updated_at' => Carbon::parse('2026-04-07 08:35:00'),
            ],
            [
                'sale_id' => 3,
                'amount' => 10,
                'payment_method' => 'cash',
                'payment_reference' => 'DUE-003',
                'card_number' => null,
                'bank_name' => null,
                'is_completed' => 1,
                'payment_date' => Carbon::parse('2026-04-07 11:50:00'),
                'due_date' => null,
                'due_payment_method' => 'cash',
                'due_payment_attachment' => null,
                'status' => 'approved',
                'notes' => 'Due collection',
                'created_by' => $admin->id,
                'transfer_date' => null,
                'transfer_reference' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'collected_by' => null,
                'collected_at' => null,
                'created_at' => Carbon::parse('2026-04-07 11:50:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:50:00'),
            ],
            [
                'sale_id' => 4,
                'amount' => 150,
                'payment_method' => 'cash',
                'payment_reference' => null,
                'card_number' => null,
                'bank_name' => null,
                'is_completed' => 1,
                'payment_date' => Carbon::parse('2026-04-07 09:05:00'),
                'due_date' => null,
                'due_payment_method' => null,
                'due_payment_attachment' => null,
                'status' => 'paid',
                'notes' => null,
                'created_by' => $admin->id,
                'transfer_date' => null,
                'transfer_reference' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'collected_by' => null,
                'collected_at' => null,
                'created_at' => Carbon::parse('2026-04-07 09:05:00'),
                'updated_at' => Carbon::parse('2026-04-07 09:05:00'),
            ],
            [
                'sale_id' => 5,
                'amount' => 60,
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'BT-005',
                'card_number' => null,
                'bank_name' => 'Bank A',
                'is_completed' => 1,
                'payment_date' => Carbon::parse('2026-04-07 09:35:00'),
                'due_date' => null,
                'due_payment_method' => null,
                'due_payment_attachment' => null,
                'status' => 'approved',
                'notes' => null,
                'created_by' => $admin->id,
                'transfer_date' => Carbon::parse('2026-04-07')->toDateString(),
                'transfer_reference' => 'TR-005',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'collected_by' => null,
                'collected_at' => null,
                'created_at' => Carbon::parse('2026-04-07 09:35:00'),
                'updated_at' => Carbon::parse('2026-04-07 09:35:00'),
            ],
            [
                'sale_id' => 6,
                'amount' => 70,
                'payment_method' => 'cash',
                'payment_reference' => null,
                'card_number' => null,
                'bank_name' => null,
                'is_completed' => 1,
                'payment_date' => Carbon::parse('2026-04-07 10:05:00'),
                'due_date' => null,
                'due_payment_method' => null,
                'due_payment_attachment' => null,
                'status' => 'paid',
                'notes' => null,
                'created_by' => $admin->id,
                'transfer_date' => null,
                'transfer_reference' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'collected_by' => null,
                'collected_at' => null,
                'created_at' => Carbon::parse('2026-04-07 10:05:00'),
                'updated_at' => Carbon::parse('2026-04-07 10:05:00'),
            ],
            [
                'sale_id' => 7,
                'amount' => 80,
                'payment_method' => 'cash',
                'payment_reference' => null,
                'card_number' => null,
                'bank_name' => null,
                'is_completed' => 1,
                'payment_date' => Carbon::parse('2026-04-07 10:35:00'),
                'due_date' => null,
                'due_payment_method' => null,
                'due_payment_attachment' => null,
                'status' => 'approved',
                'notes' => null,
                'created_by' => $admin->id,
                'transfer_date' => null,
                'transfer_reference' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'collected_by' => $delivery->id,
                'collected_at' => Carbon::parse('2026-04-07 10:35:00'),
                'created_at' => Carbon::parse('2026-04-07 10:35:00'),
                'updated_at' => Carbon::parse('2026-04-07 10:35:00'),
            ],
            [
                'sale_id' => 8,
                'amount' => 25,
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'BT-008',
                'card_number' => null,
                'bank_name' => 'Bank A',
                'is_completed' => 0,
                'payment_date' => Carbon::parse('2026-04-07 11:05:00'),
                'due_date' => null,
                'due_payment_method' => null,
                'due_payment_attachment' => null,
                'status' => 'pending',
                'notes' => null,
                'created_by' => $admin->id,
                'transfer_date' => Carbon::parse('2026-04-07')->toDateString(),
                'transfer_reference' => 'TR-008',
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null,
                'collected_by' => $delivery->id,
                'collected_at' => null,
                'created_at' => Carbon::parse('2026-04-07 11:05:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:05:00'),
            ],
            [
                'sale_id' => 9,
                'amount' => 200,
                'payment_method' => 'cash',
                'payment_reference' => null,
                'card_number' => null,
                'bank_name' => null,
                'is_completed' => 1,
                'payment_date' => Carbon::parse('2026-04-07 11:35:00'),
                'due_date' => null,
                'due_payment_method' => null,
                'due_payment_attachment' => null,
                'status' => 'paid',
                'notes' => null,
                'created_by' => $admin->id,
                'transfer_date' => null,
                'transfer_reference' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'collected_by' => null,
                'collected_at' => null,
                'created_at' => Carbon::parse('2026-04-07 11:35:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:35:00'),
            ],
            [
                'sale_id' => 10,
                'amount' => 30,
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'BT-010',
                'card_number' => null,
                'bank_name' => 'Bank A',
                'is_completed' => 1,
                'payment_date' => Carbon::parse('2026-04-07 11:45:00'),
                'due_date' => null,
                'due_payment_method' => 'bank_transfer',
                'due_payment_attachment' => null,
                'status' => 'paid',
                'notes' => 'Shop due collection',
                'created_by' => $admin->id,
                'transfer_date' => Carbon::parse('2026-04-07')->toDateString(),
                'transfer_reference' => 'TR-010',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejection_reason' => null,
                'collected_by' => null,
                'collected_at' => null,
                'created_at' => Carbon::parse('2026-04-07 11:45:00'),
                'updated_at' => Carbon::parse('2026-04-07 11:45:00'),
            ],
        ]);

        return [
            'admin' => $admin,
            'generic_staff' => $genericStaff,
            'salesman' => $salesman,
            'delivery' => $delivery,
            'shop_staff' => $shopStaff,
        ];
    }

    private function createUser(string $name, string $email, string $role, ?string $staffType)
    {
        $userId = DB::table('users')->insertGetId([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => $role,
            'contact' => '1234567890',
            'staff_type' => $staffType,
            'email_verified_at' => now(),
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return \App\Models\User::find($userId);
    }

    private function createCustomer(string $name, string $type)
    {
        return DB::table('customers')->insertGetId([
            'name' => $name,
            'business_name' => null,
            'phone' => null,
            'email' => null,
            'type' => $type,
            'address' => null,
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resetTables(): void
    {
        $tables = [
            'payment_allocations',
            'payments',
            'sale_items',
            'sales',
            'staff_products',
            'staff_sales',
            'staff_expenses',
            'pos_sessions',
            'purchase_orders',
            'product_details',
            'product_prices',
            'product_stocks',
            'product_suppliers',
            'brand_lists',
            'category_lists',
            'customers',
            'expenses',
            'users',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
