<?php

namespace Tests\Feature;

use App\Livewire\Admin\ProfitLoss;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\ProductSupplier;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfitLossTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Finance Admin',
            'email' => 'admin_pl_'.uniqid().'@jaffnagold.lk',
            'contact' => '0777123999',
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Test admin can access the profit and loss page via route.
     */
    public function test_admin_can_access_profit_loss_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.profit-loss'));
        $response->assertStatus(200);
        $response->assertSee('Profit & Loss (NPL) Performance');
        $response->assertSee('Total Sales Revenue');
        $response->assertSee('Total Purchasing Value');
        $response->assertSee('Operating Expenses');
        $response->assertSee('Net Profit & Loss (NPL)');
    }

    /**
     * Test sidebar contains the Profit & Loss (NPL) link.
     */
    public function test_admin_sidebar_contains_profit_loss_link(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.profit-loss'));
        $response->assertStatus(200);
        $response->assertSee(route('admin.profit-loss'));
        $response->assertSee('Profit &amp; Loss (NPL)', false);
    }

    /**
     * Test filtering by date preset loads and calculates data correctly.
     */
    public function test_profit_loss_date_presets_and_calculations(): void
    {
        $this->actingAs($this->adminUser);

        // Create sample supplier and purchase order in current month
        $supplier = ProductSupplier::firstOrCreate(
            ['name' => 'Colombo Gold Bullion Ltd'],
            ['phone' => '0112345678', 'status' => 'active']
        );

        $po = PurchaseOrder::create([
            'order_code' => 'PO-TEST-'.uniqid(),
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'total_amount' => 50000.00,
            'due_amount' => 10000.00,
            'status' => 'received',
            'created_at' => Carbon::now(),
        ]);

        // Create sample customer and sale in current month
        $customer = Customer::firstOrCreate(
            ['name' => 'Niroshan Ramanathan'],
            ['phone' => '0771234567', 'type' => 'retail']
        );

        $sale = Sale::create([
            'sale_id' => 'S-TEST-'.uniqid(),
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->adminUser->id,
            'subtotal' => 85000.00,
            'discount_amount' => 5000.00,
            'total_amount' => 80000.00,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
            'sale_type' => 'pos',
            'created_at' => Carbon::now(),
        ]);

        // Create sample expense in current month
        $expense = Expense::create([
            'category' => 'Shop Utilities',
            'expense_type' => 'Electricity',
            'amount' => 8500.00,
            'date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
            'description' => 'CEB Electricity Bill September',
            'created_at' => Carbon::now(),
        ]);

        Livewire::test(ProfitLoss::class)
            ->call('setDatePreset', 'this_month')
            ->assertSet('preset', 'this_month')
            ->assertSee('Profit & Loss (NPL) Performance')
            ->assertSee('Total Sales Revenue')
            ->assertSee('Stock Value In Hand')
            // Switch to purchasing tab
            ->call('switchTab', 'purchasing')
            ->assertSet('activeTab', 'purchasing')
            ->assertSee($po->order_code)
            ->assertSee('Colombo Gold Bullion Ltd')
            ->assertSee('50,000.00')
            // Switch to sales tab
            ->call('switchTab', 'sales')
            ->assertSet('activeTab', 'sales')
            ->assertSee($sale->invoice_number)
            ->assertSee('Niroshan Ramanathan')
            ->assertSee('80,000.00')
            // Switch to expenses tab
            ->call('switchTab', 'expenses')
            ->assertSet('activeTab', 'expenses')
            ->assertSee('Shop Utilities')
            ->assertSee('CEB Electricity Bill September')
            ->assertSee('8,500.00');
    }

    /**
     * Test Seedu & Undiyal expenses automatically sync to deposits table
     * and are excluded from Operating Expenses in P&L (not deducted from Net Profit).
     */
    public function test_seedu_and_undiyal_expenses_auto_sync_to_deposits_and_are_excluded_from_operating_expenses_in_profit_loss(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create a true operating expense
        $utilityExpense = Expense::create([
            'category' => 'Shop Utilities',
            'expense_type' => 'Internet',
            'amount' => 5000.00,
            'date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
            'description' => 'SLT Fiber Bill',
            'created_at' => Carbon::now(),
        ]);

        // 2. Create Seedu Expense (Chit fund saving)
        $seeduExpense = Expense::create([
            'category' => 'SEEDU SHIHAN',
            'expense_type' => 'daily',
            'amount' => 20000.00,
            'date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
            'description' => 'Daily Seedu chit saving payment',
            'created_at' => Carbon::now(),
        ]);

        // 3. Create Undiyal Expense (Coin box saving)
        $undiyalExpense = Expense::create([
            'category' => 'UNDIYAL',
            'expense_type' => 'daily',
            'amount' => 15000.00,
            'date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
            'description' => 'Daily undiyal counter deposit',
            'created_at' => Carbon::now(),
        ]);

        // Verify Deposit records were automatically synced
        $seeduDeposit = Deposit::where('reference_no', 'EXP-'.$seeduExpense->id)->first();
        $this->assertNotNull($seeduDeposit, 'Seedu expense was not auto-synced to deposits table.');
        $this->assertEquals(20000.00, (float) $seeduDeposit->amount);
        $this->assertEquals('seedu', $seeduDeposit->saving_method);

        $undiyalDeposit = Deposit::where('reference_no', 'EXP-'.$undiyalExpense->id)->first();
        $this->assertNotNull($undiyalDeposit, 'Undiyal expense was not auto-synced to deposits table.');
        $this->assertEquals(15000.00, (float) $undiyalDeposit->amount);
        $this->assertEquals('undiyal', $undiyalDeposit->saving_method);

        // Test Profit & Loss component
        $test = Livewire::test(ProfitLoss::class)
            ->call('setDatePreset', 'today');

        // Operating expenses should ONLY include true expenses (like 5000 utility), NOT the 35000 deposits!
        $expenseBreakdown = $test->get('expenseBreakdown');
        $this->assertArrayNotHasKey('SEEDU SHIHAN', $expenseBreakdown);
        $this->assertArrayNotHasKey('UNDIYAL', $expenseBreakdown);

        // Deposits should reflect Seedu and Undiyal amounts
        $this->assertGreaterThanOrEqual(20000.00, (float) $test->get('seeduSavings'));
        $this->assertGreaterThanOrEqual(15000.00, (float) $test->get('undiyalSavings'));
        $this->assertGreaterThanOrEqual(35000.00, (float) $test->get('totalDeposits'));

        // Test tabs & filtering
        $test->call('switchTab', 'expenses')
            ->assertSee('Retained Saving')
            ->assertSee('SEEDU SHIHAN')
            ->assertSee('UNDIYAL');

        // Filter only operating expenses
        $test->set('expenseTypeFilter', 'operating_only');
        $opRecords = $test->get('expenseRecords');
        $opCategories = array_column($opRecords, 'category');
        $this->assertContains('Shop Utilities', $opCategories);
        $this->assertNotContains('SEEDU SHIHAN', $opCategories);
        $this->assertNotContains('UNDIYAL', $opCategories);

        // Filter only deposits
        $test->set('expenseTypeFilter', 'deposits_only');
        $depRecords = $test->get('expenseRecords');
        $depCategories = array_column($depRecords, 'category');
        $this->assertContains('SEEDU SHIHAN', $depCategories);
        $this->assertContains('UNDIYAL', $depCategories);
        $this->assertNotContains('Shop Utilities', $depCategories);

        // Switch to deposits tab
        $test->call('switchTab', 'deposits')
            ->assertSee('EXP-'.$seeduExpense->id)
            ->assertSee('EXP-'.$undiyalExpense->id);
    }
}
