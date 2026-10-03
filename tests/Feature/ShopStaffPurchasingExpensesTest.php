<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\PurchasingExpenses;
use App\Livewire\ShopStaff\ShopStaffExpenses;
use App\Models\Expense;
use App\Models\HandmadeSupplier;
use App\Models\HandmadeSupplierPayment;
use App\Models\StaffExpense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ShopStaffPurchasingExpensesTest extends TestCase
{
    use DatabaseTransactions;

    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staffUser = User::create([
            'name' => 'Shop Staff Test',
            'email' => 'staff_purchasing_'.uniqid().'@jaffnagold.lk',
            'contact' => '0777111222',
            'role' => 'staff',
            'staff_type' => 'shop_staff',
            'password' => bcrypt('password123'),
        ]);

        // Ensure primary suppliers exist in HandmadeSupplier
        HandmadeSupplier::firstOrCreate(['name' => 'MANO ANNA'], ['phone' => '', 'opening_payable_balance' => 0, 'is_active' => true]);
        HandmadeSupplier::firstOrCreate(['name' => 'RAMESH ANNA'], ['phone' => '', 'opening_payable_balance' => 0, 'is_active' => true]);
        HandmadeSupplier::firstOrCreate(['name' => 'SUTHA ANNA'], ['phone' => '', 'opening_payable_balance' => 0, 'is_active' => true]);
    }

    /**
     * Test shop staff can access purchasing expenses page.
     */
    public function test_shop_staff_can_access_purchasing_expenses_page(): void
    {
        $this->actingAs($this->staffUser);

        $response = $this->get(route('shop-staff.purchasing-expenses'));
        $response->assertStatus(200);
        $response->assertSee('Purchasing Expenses');
        $response->assertSee('Mano anna');
        $response->assertSee('RAMESH ANNA');
        $response->assertSee('Sutha anna');
    }

    /**
     * Test sidebar contains purchasing expenses link under finance.
     */
    public function test_sidebar_contains_purchasing_expenses_link(): void
    {
        $this->actingAs($this->staffUser);

        $response = $this->get(route('shop-staff.dashboard'));
        $response->assertStatus(200);
        $response->assertSee(route('shop-staff.purchasing-expenses'));
        $response->assertSee('Purchasing Expenses');
    }

    /**
     * Test that supplier payouts for Mano, Ramesh, and Sutha are recognized and displayed.
     */
    public function test_supplier_expenses_appear_on_purchasing_page(): void
    {
        $this->actingAs($this->staffUser);

        // Create test supplier expenses
        $manoExp = StaffExpense::create([
            'staff_id' => $this->staffUser->id,
            'expense_type' => 'Mano anna',
            'amount' => 25000,
            'description' => 'Gold advance payment',
            'expense_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
        ]);

        $rameshExp = StaffExpense::create([
            'staff_id' => $this->staffUser->id,
            'expense_type' => 'RAMESH ANNA',
            'amount' => 12000,
            'description' => 'Poun thanniy bill',
            'expense_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
        ]);

        $suthaExp = StaffExpense::create([
            'staff_id' => $this->staffUser->id,
            'expense_type' => 'Sutha anna',
            'amount' => 4500,
            'description' => 'Kappu order settlement',
            'expense_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
        ]);

        Livewire::test(PurchasingExpenses::class)
            ->assertSee('Mano anna')
            ->assertSee('RAMESH ANNA')
            ->assertSee('Sutha anna')
            ->assertSee('25,000.00')
            ->assertSee('12,000.00')
            ->assertSee('4,500.00');
    }

    /**
     * Test filtering purchasing expenses by specific supplier.
     */
    public function test_can_filter_purchasing_expenses_by_supplier(): void
    {
        $this->actingAs($this->staffUser);

        StaffExpense::create([
            'staff_id' => $this->staffUser->id,
            'expense_type' => 'Mano anna',
            'amount' => 30000,
            'description' => 'Raw gold purchase Mano',
            'expense_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
        ]);

        StaffExpense::create([
            'staff_id' => $this->staffUser->id,
            'expense_type' => 'Sutha anna',
            'amount' => 5000,
            'description' => 'Sutha anna payout',
            'expense_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
        ]);

        Livewire::test(PurchasingExpenses::class)
            ->set('supplierFilter', 'Mano anna')
            ->assertSee('Raw gold purchase Mano')
            ->assertDontSee('Sutha anna payout');
    }

    /**
     * Test recording a purchasing expense via modal.
     */
    public function test_can_add_purchasing_expense_via_modal(): void
    {
        $this->actingAs($this->staffUser);

        // Ensure cash in hand row exists
        DB::table('cash_in_hands')->updateOrInsert(
            ['key' => 'cash_amount'],
            ['value' => 500000, 'updated_at' => now()]
        );

        $initialCash = (float) DB::table('cash_in_hands')->where('key', 'cash_amount')->value('value');

        Livewire::test(PurchasingExpenses::class)
            ->call('openAddModal')
            ->set('supplier_name', 'Mano anna')
            ->set('amount', '20000')
            ->set('purpose', 'Raw Gold / Poun Purchase')
            ->set('expense_date', Carbon::now()->format('Y-m-d'))
            ->set('description', 'Test payout for gold')
            ->call('addPurchasingExpense')
            ->assertHasNoErrors();

        // Verify StaffExpense created
        $this->assertDatabaseHas('staff_expenses', [
            'staff_id' => $this->staffUser->id,
            'expense_type' => 'Mano anna',
            'amount' => 20000,
        ]);

        // Verify regular Expense created
        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->staffUser->id,
            'amount' => 20000,
            'category' => 'Purchasing Expense - Mano anna',
        ]);

        // Verify cash in hands deducted
        $newCash = (float) DB::table('cash_in_hands')->where('key', 'cash_amount')->value('value');
        $this->assertEquals($initialCash - 20000, $newCash);

        // Verify HandmadeSupplierPayment synced
        $supplier = HandmadeSupplier::where('name', 'LIKE', '%mano%')->first();
        $this->assertDatabaseHas('handmade_supplier_payments', [
            'supplier_id' => $supplier->id,
            'amount' => 20000,
        ]);
    }

    /**
     * Test deleting a purchasing expense restores cash in hand.
     */
    public function test_deleting_purchasing_expense_restores_cash(): void
    {
        $this->actingAs($this->staffUser);

        DB::table('cash_in_hands')->updateOrInsert(
            ['key' => 'cash_amount'],
            ['value' => 100000, 'updated_at' => now()]
        );

        $initialCash = (float) DB::table('cash_in_hands')->where('key', 'cash_amount')->value('value');

        $expense = StaffExpense::create([
            'staff_id' => $this->staffUser->id,
            'expense_type' => 'RAMESH ANNA',
            'amount' => 15000,
            'description' => 'Advance payment',
            'expense_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
        ]);

        // Simulate that initial creation deducted 15000
        DB::table('cash_in_hands')->where('key', 'cash_amount')->update(['value' => $initialCash - 15000]);

        Livewire::test(PurchasingExpenses::class)
            ->call('confirmDelete', $expense->id)
            ->call('deleteExpense');

        $this->assertDatabaseMissing('staff_expenses', ['id' => $expense->id]);

        $restoredCash = (float) DB::table('cash_in_hands')->where('key', 'cash_amount')->value('value');
        $this->assertEquals($initialCash, $restoredCash);
    }

    /**
     * Test selecting supplier from Daily Expenses modal.
     */
    public function test_daily_expenses_quick_supplier_selection(): void
    {
        $this->actingAs($this->staffUser);

        Livewire::test(ShopStaffExpenses::class)
            ->call('openAddModal')
            ->call('selectSupplierCategory', 'Sutha anna')
            ->assertSet('expense_type', 'Sutha anna')
            ->assertSet('is_salary', false);
    }
}
