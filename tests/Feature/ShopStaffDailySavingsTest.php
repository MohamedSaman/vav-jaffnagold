<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\DailySavings;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ShopStaffDailySavingsTest extends TestCase
{
    use DatabaseTransactions;

    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staffUser = User::create([
            'name' => 'Shop Staff Member',
            'email' => 'staff_savings_'.uniqid().'@jaffnagold.lk',
            'contact' => '0777999888',
            'role' => 'staff',
            'staff_type' => 'shop_staff',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Test shop staff can access the daily savings page and see payment breakdown.
     */
    public function test_shop_staff_can_access_daily_savings_page(): void
    {
        $this->actingAs($this->staffUser);

        $response = $this->get(route('shop-staff.daily-savings'));
        $response->assertStatus(200);
        $response->assertSee('Daily Savings & Cash Deposits');
        $response->assertSee('Sales Revenue & Payment Methods Breakdown');
        $response->assertSee('Cash Sales (Drawer Cash)');
        $response->assertSee('Bank Transfers');
        $response->assertSee('Card / Online POS');
        $response->assertSee('Seedu (சீட்டு சேமிப்பு)');
        $response->assertSee('Undiyal (உண்டியல்)');
    }

    /**
     * Test shop staff sidebar contains daily savings link.
     */
    public function test_shop_staff_sidebar_contains_daily_savings_link(): void
    {
        $this->actingAs($this->staffUser);

        $response = $this->get(route('shop-staff.daily-savings'));
        $response->assertStatus(200);
        $response->assertSee(route('shop-staff.daily-savings'));
        $response->assertSee('Daily Savings &amp; Deposits', false);
    }

    /**
     * Test recording a daily deposit for Seedu from cash sale.
     */
    public function test_can_record_daily_seedu_deposit(): void
    {
        $this->actingAs($this->staffUser);

        Livewire::test(DailySavings::class)
            ->set('depositDate', Carbon::now()->format('Y-m-d'))
            ->set('saving_method', 'seedu')
            ->set('source_of_funds', 'cash_sale')
            ->set('reference_no', 'Chit Group A - Month 1')
            ->set('amount', 26500.00)
            ->set('description', 'Weekly chit deposit from cash sales')
            ->call('saveDeposit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('deposits', [
            'saving_method' => 'seedu',
            'source_of_funds' => 'cash_sale',
            'reference_no' => 'Chit Group A - Month 1',
            'amount' => 26500.00,
        ]);
    }

    /**
     * Test recording a daily deposit for Undiyal.
     */
    public function test_can_record_daily_undiyal_deposit(): void
    {
        $this->actingAs($this->staffUser);

        Livewire::test(DailySavings::class)
            ->set('depositDate', Carbon::now()->format('Y-m-d'))
            ->set('saving_method', 'undiyal')
            ->set('source_of_funds', 'cash_sale')
            ->set('reference_no', 'Counter Undiyal Box #1')
            ->set('amount', 15000.00)
            ->set('description', 'End of day coin box saving')
            ->call('saveDeposit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('deposits', [
            'saving_method' => 'undiyal',
            'source_of_funds' => 'cash_sale',
            'reference_no' => 'Counter Undiyal Box #1',
            'amount' => 15000.00,
        ]);
    }

    /**
     * Test creating a new custom saving method.
     */
    public function test_can_create_new_saving_method(): void
    {
        $this->actingAs($this->staffUser);

        $methodName = 'Gold Chit Fund '.uniqid();

        Livewire::test(DailySavings::class)
            ->set('newMethodName', $methodName)
            ->set('newMethodDescription', 'Special festival gold saving chit')
            ->set('newMethodColor', 'warning')
            ->call('saveNewMethod')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saving_methods', [
            'name' => $methodName,
        ]);
    }

    /**
     * Test payment methods breakdown calculates cash, bank, and card amounts correctly.
     */
    public function test_payment_methods_breakdown_reflects_actual_sales(): void
    {
        $this->actingAs($this->staffUser);

        $customer = Customer::firstOrCreate(
            ['name' => 'Test Savings Customer'],
            ['phone' => '0779991122', 'type' => 'retail']
        );

        // 1. Create a cash sale
        $cashSale = Sale::create([
            'sale_id' => 'S-CASH-'.uniqid(),
            'invoice_number' => 'INV-CASH-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->staffUser->id,
            'subtotal' => 45000.00,
            'total_amount' => 45000.00,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
            'created_at' => Carbon::now(),
        ]);

        Payment::create([
            'sale_id' => $cashSale->id,
            'customer_id' => $customer->id,
            'amount' => 45000.00,
            'payment_method' => 'cash',
            'is_completed' => 1,
            'status' => 'approved',
            'created_at' => Carbon::now(),
        ]);

        // 2. Create a bank transfer sale
        $bankSale = Sale::create([
            'sale_id' => 'S-BANK-'.uniqid(),
            'invoice_number' => 'INV-BANK-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->staffUser->id,
            'subtotal' => 30000.00,
            'total_amount' => 30000.00,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
            'created_at' => Carbon::now(),
        ]);

        Payment::create([
            'sale_id' => $bankSale->id,
            'customer_id' => $customer->id,
            'amount' => 30000.00,
            'payment_method' => 'bank_transfer',
            'is_completed' => 1,
            'status' => 'approved',
            'created_at' => Carbon::now(),
        ]);

        $test = Livewire::test(DailySavings::class);
        $this->assertGreaterThanOrEqual(45000.00, $test->get('monthCashSales'));
        $this->assertGreaterThanOrEqual(30000.00, $test->get('monthBankSales'));
    }

    /**
     * Test sale with bank_transfer automatically creates a direct bank deposit record.
     */
    public function test_sale_with_bank_transfer_automatically_creates_direct_bank_deposit(): void
    {
        $this->actingAs($this->staffUser);

        $customItem = [
            'key' => 'custom_item_1',
            'id' => 'custom_'.uniqid(),
            'item_id' => 'custom_'.uniqid(),
            'code' => 'CUSTOM-001',
            'name' => 'Gold Ring Bank Sale',
            'model' => 'N/A',
            'product_id' => null,
            'is_custom' => true,
            'price' => 50000,
            'unit_price' => 50000,
            'quantity' => 1,
            'custom_weight' => 4.0,
            'unit_discount' => 0,
            'discount' => 0,
            'total' => 50000,
            'has_warranty' => false,
            'warranty_duration' => '6 Months',
        ];

        Livewire::test(\App\Livewire\ShopStaff\StoreBilling::class)
            ->set('cart', [$customItem])
            ->set('customerSearchName', 'Bank Buyer')
            ->set('customerSearchPhone', '0771234567')
            ->call('validateAndCreateSale')
            ->set('paymentMethod', 'bank_transfer')
            ->set('bankTransferAmount', 50000)
            ->set('bankTransferBankName', 'BOC Bank')
            ->set('bankTransferReferenceNumber', 'TXN-BANK-9988')
            ->call('completeSaleWithPayment');

        $sale = Sale::where('walking_customer_name', 'Bank Buyer')->latest()->first();
        $this->assertNotNull($sale);

        // Check deposit was automatically created
        $deposit = Deposit::where('reference_no', $sale->invoice_number)
            ->where('source_of_funds', 'sale_bank_transfer')
            ->first();

        $this->assertNotNull($deposit, 'Direct bank deposit was not automatically recorded from sale.');
        $this->assertEquals(50000.00, (float) $deposit->amount);
        $this->assertEquals('bank_deposit', $deposit->saving_method);
        $this->assertEquals('completed', $deposit->status);
        $this->assertStringContainsString('BOC Bank', $deposit->description);
    }

    /**
     * Test sale with online/card automatically creates a direct card deposit record.
     */
    public function test_sale_with_online_card_automatically_creates_direct_card_deposit(): void
    {
        $this->actingAs($this->staffUser);

        $customItem = [
            'key' => 'custom_item_2',
            'id' => 'custom_'.uniqid(),
            'item_id' => 'custom_'.uniqid(),
            'code' => 'CUSTOM-002',
            'name' => 'Gold Chain Card Sale',
            'model' => 'N/A',
            'product_id' => null,
            'is_custom' => true,
            'price' => 75000,
            'unit_price' => 75000,
            'quantity' => 1,
            'custom_weight' => 6.0,
            'unit_discount' => 0,
            'discount' => 0,
            'total' => 75000,
            'has_warranty' => false,
            'warranty_duration' => '6 Months',
        ];

        Livewire::test(\App\Livewire\ShopStaff\StoreBilling::class)
            ->set('cart', [$customItem])
            ->set('customerSearchName', 'Card Buyer')
            ->set('customerSearchPhone', '0777654321')
            ->call('validateAndCreateSale')
            ->set('paymentMethod', 'online')
            ->set('bankTransferAmount', 75000)
            ->set('bankTransferBankName', 'Commercial Bank POS')
            ->set('bankTransferReferenceNumber', 'POS-CARD-5544')
            ->call('completeSaleWithPayment');

        $sale = Sale::where('walking_customer_name', 'Card Buyer')->latest()->first();
        $this->assertNotNull($sale);

        // Check deposit was automatically created
        $deposit = Deposit::where('reference_no', $sale->invoice_number)
            ->where('source_of_funds', 'sale_card')
            ->first();

        $this->assertNotNull($deposit, 'Direct card deposit was not automatically recorded from sale.');
        $this->assertEquals(75000.00, (float) $deposit->amount);
        $this->assertEquals('card_deposit', $deposit->saving_method);
        $this->assertEquals('completed', $deposit->status);
    }

    /**
     * Test filtering deposits by cash_savings and direct_sales.
     */
    public function test_daily_savings_can_filter_by_method_categories(): void
    {
        $this->actingAs($this->staffUser);

        // Create Seedu deposit
        Deposit::create([
            'date' => Carbon::now()->format('Y-m-d'),
            'amount' => 12000.00,
            'saving_method' => 'seedu',
            'source_of_funds' => 'cash_sale',
            'reference_no' => 'SEEDU-FILTER-TEST',
            'user_id' => $this->staffUser->id,
            'status' => 'completed',
        ]);

        // Create direct sale bank deposit
        Deposit::create([
            'date' => Carbon::now()->format('Y-m-d'),
            'amount' => 34000.00,
            'saving_method' => 'bank_deposit',
            'source_of_funds' => 'sale_bank_transfer',
            'reference_no' => 'SALE-BANK-FILTER-TEST',
            'user_id' => $this->staffUser->id,
            'status' => 'completed',
        ]);

        // Test filtering by cash_savings
        $cashTest = Livewire::test(DailySavings::class)
            ->set('methodFilter', 'cash_savings');
        $cashTest->assertSee('SEEDU-FILTER-TEST');
        $cashTest->assertDontSee('SALE-BANK-FILTER-TEST');

        // Test filtering by direct_sales
        $saleTest = Livewire::test(DailySavings::class)
            ->set('methodFilter', 'direct_sales');
        $saleTest->assertSee('SALE-BANK-FILTER-TEST');
        $saleTest->assertDontSee('SEEDU-FILTER-TEST');
    }

    /**
     * Test daily savings defaults to Today view on initial load and can filter by month.
     */
    public function test_daily_savings_defaults_to_today_and_can_filter_month(): void
    {
        $this->actingAs($this->staffUser);

        // Deposit for Today
        $todayDeposit = Deposit::create([
            'date' => Carbon::now()->format('Y-m-d'),
            'amount' => 15000.00,
            'saving_method' => 'seedu',
            'source_of_funds' => 'cash_sale',
            'reference_no' => 'TODAY-DEP-'.uniqid(),
            'user_id' => $this->staffUser->id,
            'status' => 'completed',
        ]);

        // Deposit for Last Month
        $pastMonthDate = Carbon::now()->subMonths(1)->startOfMonth()->format('Y-m-d');
        $pastDeposit = Deposit::create([
            'date' => $pastMonthDate,
            'amount' => 22000.00,
            'saving_method' => 'undiyal',
            'source_of_funds' => 'cash_sale',
            'reference_no' => 'PAST-DEP-'.uniqid(),
            'user_id' => $this->staffUser->id,
            'status' => 'completed',
        ]);

        // 1. Initial load should default to 'today' mode
        $test = Livewire::test(DailySavings::class);
        $this->assertEquals('today', $test->get('dateFilterMode'));
        $test->assertSee($todayDeposit->reference_no);
        $test->assertDontSee($pastDeposit->reference_no);

        // 2. Switch to Month view
        $test->call('filterMonth', Carbon::now()->subMonths(1)->format('Y-m'))
            ->set('search', $pastDeposit->reference_no)
            ->assertSet('dateFilterMode', 'month')
            ->assertSee($pastDeposit->reference_no)
            ->assertDontSee($todayDeposit->reference_no);

        // 3. Switch back to Today view
        $test->set('search', '')
            ->call('filterToday')
            ->assertSet('dateFilterMode', 'today')
            ->assertSee($todayDeposit->reference_no)
            ->assertDontSee($pastDeposit->reference_no);
    }

    /**
     * Test malformed or incomplete month strings do not crash the component with InvalidFormatException.
     */
    public function test_invalid_or_incomplete_month_string_does_not_crash(): void
    {
        $this->actingAs($this->staffUser);

        // Setting incomplete strings like '2026-' or '2026--01' should not crash
        Livewire::test(DailySavings::class)
            ->set('selectedMonth', '2026-')
            ->assertHasNoErrors()
            ->set('selectedMonth', '2026--01')
            ->assertHasNoErrors()
            ->set('selectedMonth', '')
            ->assertHasNoErrors();
    }

    /**
     * Test shop staff sidebar is grouped with Sale and Finance submenus.
     */
    public function test_shop_staff_sidebar_is_grouped_with_sale_and_finance_submenus(): void
    {
        $this->actingAs($this->staffUser);

        $response = $this->get(route('shop-staff.dashboard'));
        $response->assertStatus(200);

        // Check Sale Group & Submenu items
        $response->assertSee('staffSalesSubmenu');
        $response->assertSee('Store Billing');
        $response->assertSee('Sale List');
        $response->assertSee('Due Sales');
        $response->assertSee('My Quotations');

        // Check Finance Group & Submenu items
        $response->assertSee('staffFinanceSubmenu');
        $response->assertSee('Daily Expenses');
        $response->assertSee('Daily Savings &amp; Deposits', false);
    }
}
