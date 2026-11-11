<?php

namespace Tests\Feature;

use App\Livewire\Admin\PurchasingExpenses;
use App\Models\HandmadeSupplier;
use App\Models\StaffExpense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPurchasingExpensesTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin_purchasing_'.uniqid().'@jaffnagold.lk',
            'password' => bcrypt('password123'),
        ]);

        // Ensure key suppliers exist
        HandmadeSupplier::firstOrCreate(['name' => 'MANO ANNA'], ['phone' => '', 'opening_payable_balance' => 0, 'is_active' => true]);
        HandmadeSupplier::firstOrCreate(['name' => 'KANIY MANO'], ['phone' => '', 'opening_payable_balance' => 0, 'is_active' => true]);
        HandmadeSupplier::firstOrCreate(['name' => 'MARAN THALAI'], ['phone' => '', 'opening_payable_balance' => 0, 'is_active' => true]);
        HandmadeSupplier::firstOrCreate(['name' => 'RAMESH ANNA'], ['phone' => '', 'opening_payable_balance' => 0, 'is_active' => true]);
        HandmadeSupplier::firstOrCreate(['name' => 'SUTHA ANNA'], ['phone' => '', 'opening_payable_balance' => 0, 'is_active' => true]);
    }

    /**
     * Test admin can access purchasing expenses page.
     */
    public function test_admin_can_access_purchasing_expenses_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.purchasing-expenses'));
        $response->assertStatus(200);
        $response->assertSee('Purchasing Expenses');
        $response->assertSee('Pick Month');
        $response->assertSee('Period Spend');
    }

    /**
     * Test admin sidebar contains purchasing expenses link under Finance.
     */
    public function test_admin_sidebar_contains_purchasing_expenses_link(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.purchasing-expenses'));
        $response->assertStatus(200);
        $response->assertSee(route('admin.purchasing-expenses'));
    }

    /**
     * Test monthly-wise filtering filters expenses by selected month.
     */
    public function test_admin_can_filter_expenses_monthly_wise(): void
    {
        $this->actingAs($this->adminUser);

        // Expense in October 2026
        StaffExpense::create([
            'staff_id' => $this->adminUser->id,
            'expense_type' => 'MANO ANNA',
            'amount' => 45000,
            'description' => 'October gold purchase',
            'expense_date' => '2026-10-15',
            'status' => 'approved',
        ]);

        // Expense in September 2026
        StaffExpense::create([
            'staff_id' => $this->adminUser->id,
            'expense_type' => 'KANIY MANO',
            'amount' => 28000,
            'description' => 'September kolaya payout',
            'expense_date' => '2026-09-20',
            'status' => 'approved',
        ]);

        // When filtering by September 2026
        Livewire::test(PurchasingExpenses::class)
            ->set('perPage', 100)
            ->call('selectMonth', '2026-09')
            ->assertSee('September kolaya payout')
            ->assertDontSee('October gold purchase');

        // When filtering by October 2026
        Livewire::test(PurchasingExpenses::class)
            ->set('perPage', 100)
            ->call('selectMonth', '2026-10')
            ->assertSee('October gold purchase')
            ->assertDontSee('September kolaya payout');
    }

    /**
     * Test admin can filter purchasing expenses by supplier independently.
     */
    public function test_admin_can_filter_by_supplier(): void
    {
        $this->actingAs($this->adminUser);

        StaffExpense::create([
            'staff_id' => $this->adminUser->id,
            'expense_type' => 'MARAN THALAI',
            'amount' => 15000,
            'description' => 'Maran thalai thalliy payout',
            'expense_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
        ]);

        StaffExpense::create([
            'staff_id' => $this->adminUser->id,
            'expense_type' => 'RAMESH ANNA',
            'amount' => 20000,
            'description' => 'Ramesh Anna chain settlement',
            'expense_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'approved',
        ]);

        Livewire::test(PurchasingExpenses::class)
            ->set('monthFilter', 'all')
            ->set('supplierFilter', 'MARAN THALAI')
            ->assertSee('Maran thalai thalliy payout')
            ->assertDontSee('Ramesh Anna chain settlement');
    }

    /**
     * Test admin can record a purchasing expense.
     */
    public function test_admin_can_add_purchasing_expense(): void
    {
        $this->actingAs($this->adminUser);

        DB::table('cash_in_hands')->updateOrInsert(
            ['key' => 'cash_amount'],
            ['value' => 500000, 'updated_at' => now()]
        );

        Livewire::test(PurchasingExpenses::class)
            ->call('openAddModal')
            ->set('supplier_name', 'MANO ANNA')
            ->set('amount', 50000)
            ->set('expense_date', Carbon::now()->format('Y-m-d'))
            ->set('purpose', 'Raw Gold / Poun Purchase')
            ->set('description', 'Direct gold bars purchase')
            ->call('addExpense')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('staff_expenses', [
            'expense_type' => 'MANO ANNA',
            'amount' => 50000,
            'status' => 'approved',
        ]);
    }
}
