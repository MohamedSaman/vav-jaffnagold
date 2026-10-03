<?php

namespace Tests\Feature;

use App\Livewire\DeliveryMan\DeliveryManFinance;
use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\StaffExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DeliveryManFinanceTest extends TestCase
{
    use DatabaseTransactions;

    protected $deliveryUser;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Delivery Man',
            'email' => 'delivery_fin_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'staff_type' => 'delivery_man',
            'contact' => '0771234567',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deliveryUser = User::find($userId);
    }

    /** @test */
    public function delivery_man_can_access_finance_page_route()
    {
        $this->actingAs($this->deliveryUser);

        $response = $this->get(route('delivery.finance'));
        $response->assertStatus(200);
        $response->assertSee('Delivery Finance & Sales Analytics');
    }

    /** @test */
    public function finance_component_calculates_kpis_accurately()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'Finance Test Customer',
            'phone' => '0771122334',
            'email' => 'fin_'.uniqid().'@customer.com',
            'address' => 'Jaffna Town',
        ]);

        // 1. Delivered sale (fully paid)
        $deliveredSale = Sale::create([
            'sale_id' => 'SALE-DEL-01',
            'invoice_number' => 'INV-DEL-01',
            'customer_id' => $customer->id,
            'subtotal' => 5000,
            'total_amount' => 5450,
            'due_amount' => 0,
            'payment_status' => 'paid',
            'delivery_status' => 'delivered',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
        ]);

        DeliverySale::create([
            'sale_id' => $deliveredSale->id,
            'delivery_method' => 'Domex',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'Delivered',
            'tracking_number' => 'DOMEX12345',
        ]);

        Payment::create([
            'sale_id' => $deliveredSale->id,
            'customer_id' => $customer->id,
            'amount' => 5450,
            'payment_method' => 'cash',
            'is_completed' => true,
            'payment_date' => now(),
            'status' => 'paid',
            'collected_by' => $this->deliveryUser->id,
        ]);

        // 2. Pending sale (COD due)
        $pendingSale = Sale::create([
            'sale_id' => 'SALE-DEL-02',
            'invoice_number' => 'INV-DEL-02',
            'customer_id' => $customer->id,
            'subtotal' => 8000,
            'total_amount' => 8450,
            'due_amount' => 8450,
            'payment_status' => 'pending',
            'delivery_status' => 'in_transit',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'ecommerce',
        ]);

        DeliverySale::create([
            'sale_id' => $pendingSale->id,
            'delivery_method' => 'Post',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'In Transit',
            'tracking_number' => 'POST56789',
        ]);

        // 3. Expense
        StaffExpense::create([
            'staff_id' => $this->deliveryUser->id,
            'expense_type' => 'Petrol',
            'amount' => 500,
            'expense_date' => now()->toDateString(),
            'status' => 'approved',
        ]);

        $component = Livewire::test(DeliveryManFinance::class);

        $component->assertSee('Delivery Finance');
        $component->assertSee('Domex');
        $component->assertSee('Post');
        $component->assertSee('DOMEX12345');
        $component->assertSee('POST56789');

        // Check chart data
        $chartData = $component->instance()->getChartData();
        $this->assertNotEmpty($chartData['labels']);
        $this->assertNotEmpty($chartData['sales']);
        $this->assertNotEmpty($chartData['collections']);
    }

    /** @test */
    public function delivery_man_can_collect_cod_payment_from_finance_page()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'COD Collect Customer',
            'phone' => '0779988776',
            'email' => 'cod_'.uniqid().'@customer.com',
            'address' => 'Nallur',
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-COD-01',
            'invoice_number' => 'INV-COD-01',
            'customer_id' => $customer->id,
            'subtotal' => 6000,
            'total_amount' => 6450,
            'due_amount' => 6450,
            'payment_status' => 'pending',
            'delivery_status' => 'in_transit',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
        ]);

        DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Domex',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'In Transit',
        ]);

        Livewire::test(DeliveryManFinance::class)
            ->call('openPaymentModal', $sale->id)
            ->assertSet('showPaymentModal', true)
            ->assertSet('paymentAmount', 6450)
            ->set('paymentAmount', 6450)
            ->set('paymentMethod', 'cash')
            ->set('paymentNotes', 'Collected at customer doorstep')
            ->call('savePayment')
            ->assertDispatched('show-toast')
            ->assertDispatched('update-sales-chart')
            ->assertSet('showPaymentModal', false);

        $sale->refresh();
        $this->assertEquals(0, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('delivered', $sale->delivery_status);

        $this->assertDatabaseHas('payments', [
            'sale_id' => $sale->id,
            'amount' => 6450,
            'payment_method' => 'cash',
            'collected_by' => $this->deliveryUser->id,
        ]);
    }

    /** @test */
    public function delivery_man_can_switch_chart_periods_and_filter()
    {
        $this->actingAs($this->deliveryUser);

        Livewire::test(DeliveryManFinance::class)
            ->call('setChartPeriod', '30_days')
            ->assertSet('chartPeriod', '30_days')
            ->assertDispatched('update-sales-chart')
            ->set('statusFilter', 'delivered')
            ->assertSet('statusFilter', 'delivered')
            ->call('resetFilters')
            ->assertSet('statusFilter', 'all')
            ->assertSet('chartPeriod', '30_days');
    }

    /** @test */
    public function delivery_man_can_filter_sales_by_date_range_and_same_day()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'Date Filter Customer',
            'phone' => '0775566778',
            'email' => 'date_cust_'.uniqid().'@customer.com',
            'address' => 'Chavakachcheri',
        ]);

        // Sale 1: 5 days ago (2026-09-17)
        $salePast = Sale::create([
            'sale_id' => 'SALE-DATE-PAST',
            'invoice_number' => 'INV-DATE-PAST',
            'customer_id' => $customer->id,
            'subtotal' => 3000,
            'total_amount' => 3450,
            'due_amount' => 0,
            'payment_status' => 'paid',
            'delivery_status' => 'delivered',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
        ]);
        DB::table('sales')->where('id', $salePast->id)->update(['created_at' => now()->subDays(5)]);

        DeliverySale::create([
            'sale_id' => $salePast->id,
            'delivery_method' => 'Domex',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'Delivered',
            'tracking_number' => 'DOMEXPAST01',
        ]);

        // Sale 2: Today (2026-09-22)
        $saleToday = Sale::create([
            'sale_id' => 'SALE-DATE-TODAY',
            'invoice_number' => 'INV-DATE-TODAY',
            'customer_id' => $customer->id,
            'subtotal' => 7000,
            'total_amount' => 7450,
            'due_amount' => 7450,
            'payment_status' => 'pending',
            'delivery_status' => 'in_transit',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'ecommerce',
            'created_at' => now(),
        ]);

        DeliverySale::create([
            'sale_id' => $saleToday->id,
            'delivery_method' => 'Post',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'In Transit',
            'tracking_number' => 'POSTTODAY01',
        ]);

        // 1. Filter Today: should see saleToday, but NOT salePast
        Livewire::test(DeliveryManFinance::class)
            ->call('setQuickDate', 'today')
            ->assertSee('INV-DATE-TODAY')
            ->assertDontSee('INV-DATE-PAST');

        // 2. Filter Exact Same Day for 5 days ago: should see salePast, but NOT saleToday
        $pastDateString = now()->subDays(5)->toDateString();
        Livewire::test(DeliveryManFinance::class)
            ->call('setSameDate', $pastDateString)
            ->assertSet('dateFrom', $pastDateString)
            ->assertSet('dateTo', $pastDateString)
            ->assertSee('INV-DATE-PAST')
            ->assertDontSee('INV-DATE-TODAY');

        // 3. Filter Date Range covering both: from 6 days ago to today
        $rangeStart = now()->subDays(6)->toDateString();
        $rangeEnd = now()->toDateString();
        Livewire::test(DeliveryManFinance::class)
            ->set('search', 'INV-DATE')
            ->set('dateFrom', $rangeStart)
            ->set('dateTo', $rangeEnd)
            ->assertSee('INV-DATE-PAST')
            ->assertSee('INV-DATE-TODAY');

        // 4. Quick preset 'all' resets dates
        Livewire::test(DeliveryManFinance::class)
            ->set('search', 'INV-DATE')
            ->call('setQuickDate', 'all')
            ->assertSet('dateFrom', '')
            ->assertSet('dateTo', '')
            ->assertSee('INV-DATE-PAST')
            ->assertSee('INV-DATE-TODAY');
    }

    /** @test */
    public function payment_collection_rejects_overpayment_exceeding_due_amount()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'Overpay Customer',
            'phone' => '0778899001',
            'email' => 'overpay_'.uniqid().'@example.com',
            'address' => 'Jaffna',
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-OVERPAY-1',
            'invoice_number' => 'INV-OVERPAY-1',
            'customer_id' => $customer->id,
            'subtotal' => 1500,
            'total_amount' => 1950,
            'due_amount' => 1950,
            'payment_status' => 'pending',
            'delivery_status' => 'in_transit',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
        ]);

        Livewire::test(DeliveryManFinance::class)
            ->call('openPaymentModal', $sale->id)
            ->set('paymentAmount', 5000) // Exceeds due_amount 1950
            ->set('paymentMethod', 'cash')
            ->call('savePayment')
            ->assertHasErrors(['paymentAmount']);

        $sale->refresh();
        $this->assertEquals(1950, (float) $sale->due_amount);
        $this->assertEquals('pending', $sale->payment_status);
    }

    /** @test */
    public function inverted_date_range_is_swapped_automatically_and_queries_correctly()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'Invert Date Customer',
            'phone' => '0778899002',
            'email' => 'invert_'.uniqid().'@example.com',
            'address' => 'Jaffna',
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-INVERT-1',
            'invoice_number' => 'INV-INVERT-1',
            'customer_id' => $customer->id,
            'subtotal' => 2000,
            'total_amount' => 2450,
            'due_amount' => 2450,
            'payment_status' => 'pending',
            'delivery_status' => 'in_transit',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
        ]);

        DB::table('sales')->where('id', $sale->id)->update(['created_at' => now()->subDays(2)]);

        // User sets dateFrom in future/newer than dateTo
        Livewire::test(DeliveryManFinance::class)
            ->set('search', 'INV-INVERT-1')
            ->set('dateFrom', now()->toDateString())
            ->set('dateTo', now()->subDays(3)->toDateString())
            ->assertSee('INV-INVERT-1');
    }

    /** @test */
    public function courier_in_transit_orders_show_with_courier_and_suppress_collect_button()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'Domex Transit Customer',
            'phone' => '0772233445',
            'email' => 'domex_transit_'.uniqid().'@example.com',
            'address' => 'Kandy',
        ]);

        // Domex order in transit
        $transitSale = Sale::create([
            'sale_id' => 'SALE-DOMEX-TRANSIT',
            'invoice_number' => 'INV-DOMEX-TRANSIT',
            'customer_id' => $customer->id,
            'subtotal' => 3000,
            'total_amount' => 3450,
            'due_amount' => 3450,
            'payment_status' => 'pending',
            'delivery_status' => 'in_transit',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
        ]);

        DeliverySale::create([
            'sale_id' => $transitSale->id,
            'delivery_method' => 'Domex',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'In Transit',
            'tracking_number' => 'DOMEX99999',
        ]);

        // Domex order delivered (awaiting service center remittance)
        $deliveredSale = Sale::create([
            'sale_id' => 'SALE-DOMEX-DELIVERED',
            'invoice_number' => 'INV-DOMEX-DELIVERED',
            'customer_id' => $customer->id,
            'subtotal' => 2000,
            'total_amount' => 2450,
            'due_amount' => 2450,
            'payment_status' => 'pending',
            'delivery_status' => 'delivered',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
        ]);

        DeliverySale::create([
            'sale_id' => $deliveredSale->id,
            'delivery_method' => 'Domex',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'Delivered',
            'tracking_number' => 'DOMEX88888',
        ]);

        Livewire::test(DeliveryManFinance::class)
            ->assertSee('With Domex')
            ->assertSee('From Domex')
            ->assertSee('Remittance Due');
    }

    /** @test */
    public function finance_component_separates_post_and_domex_delivery_amounts()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'Courier Comparison Customer',
            'phone' => '0773344556',
            'email' => 'courier_comp_'.uniqid().'@example.com',
            'address' => 'Chundikuli, Jaffna',
        ]);

        // Create Domex Sale: Rs. 12,000 + Rs. 500 fee = 12,500
        $domexSale = Sale::create([
            'sale_id' => 'SALE-DOMEX-SEP-1',
            'invoice_number' => 'INV-DOMEX-SEP-1',
            'customer_id' => $customer->id,
            'subtotal' => 12000,
            'total_amount' => 12500,
            'due_amount' => 12500,
            'payment_status' => 'pending',
            'delivery_status' => 'in_transit',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
            'created_at' => now(),
        ]);

        DeliverySale::create([
            'sale_id' => $domexSale->id,
            'delivery_method' => 'Domex',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 500,
            'status' => 'In Transit',
            'tracking_number' => 'DX-SEP-001',
        ]);

        // Create Post Sale: Rs. 7,000 + Rs. 350 fee = 7,350
        $postSale = Sale::create([
            'sale_id' => 'SALE-POST-SEP-1',
            'invoice_number' => 'INV-POST-SEP-1',
            'customer_id' => $customer->id,
            'subtotal' => 7000,
            'total_amount' => 7350,
            'due_amount' => 0,
            'payment_status' => 'paid',
            'delivery_status' => 'delivered',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'ecommerce',
            'created_at' => now(),
        ]);

        DeliverySale::create([
            'sale_id' => $postSale->id,
            'delivery_method' => 'Post',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 350,
            'status' => 'Delivered',
            'tracking_number' => 'POST-SEP-001',
        ]);

        Payment::create([
            'sale_id' => $postSale->id,
            'customer_id' => $customer->id,
            'amount' => 7350,
            'payment_method' => 'cash',
            'is_completed' => true,
            'payment_date' => now(),
            'status' => 'paid',
            'collected_by' => $this->deliveryUser->id,
        ]);

        $test = Livewire::test(DeliveryManFinance::class);

        // 1. Check UI headers and separate amounts
        $test->assertSee('Domex Delivery Amount')
            ->assertSee('Post Delivery Amount')
            ->assertSee('12,500.00')
            ->assertSee('7,350.00')
            ->assertSee('DX-SEP-001')
            ->assertSee('POST-SEP-001');

        // 2. Test chart series has both domex and post series
        $chartData = $test->instance()->getChartData();
        $this->assertArrayHasKey('domex', $chartData);
        $this->assertArrayHasKey('post', $chartData);
        $this->assertNotEmpty($chartData['domex']);
        $this->assertNotEmpty($chartData['post']);

        // 3. Test filtering specifically by Domex
        $test->call('setCourierFilter', 'Domex')
            ->assertSet('courierFilter', 'Domex')
            ->assertSee('INV-DOMEX-SEP-1')
            ->assertDontSee('INV-POST-SEP-1');

        // 4. Test filtering specifically by Post
        $test->call('setCourierFilter', 'Post')
            ->assertSet('courierFilter', 'Post')
            ->assertSee('INV-POST-SEP-1')
            ->assertDontSee('INV-DOMEX-SEP-1');
    }
}
