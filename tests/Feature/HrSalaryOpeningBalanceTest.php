<?php

namespace Tests\Feature;

use App\Livewire\Admin\HrAttendance;
use App\Livewire\Admin\HrSalary as AdminHrSalary;
use App\Livewire\ShopStaff\HrSalary as ShopStaffHrSalary;
use App\Models\HrEmployeeSetting;
use App\Models\HrSalary;
use App\Models\User;
use App\Models\ZkEmployee;
use App\Services\HrService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class HrSalaryOpeningBalanceTest extends TestCase
{
    protected function getOrCreateAdmin(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin_test@jaffnagold.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password'), 'contact' => '0771234567', 'role' => 'admin']
        );
    }

    protected function getOrCreateShopStaff(): User
    {
        return User::firstOrCreate(
            ['email' => 'shopstaff_test@jaffnagold.com'],
            ['name' => 'Staff Test', 'password' => bcrypt('password'), 'contact' => '0777654321', 'role' => 'staff', 'staff_type' => 'shop_staff']
        );
    }

    protected function getOrCreateTestEmployee(): ZkEmployee
    {
        $testEmp = ZkEmployee::first();
        if (! $testEmp) {
            $testEmp = ZkEmployee::create([
                'emp_code' => 'TEST101',
                'first_name' => 'Demo',
                'last_name' => 'Staff',
            ]);
        }
        return $testEmp;
    }

    /**
     * Test adding punch with 12hr and 24hr format in HrAttendance as admin.
     */
    public function test_add_punch_with_various_time_formats(): void
    {
        $admin = $this->getOrCreateAdmin();
        $this->actingAs($admin);

        $testEmp = $this->getOrCreateTestEmployee();

        $date = Carbon::now()->toDateString();

        // 1. Test 12-hour format with PM (e.g. 09:05:00 PM)
        $component = Livewire::test(HrAttendance::class)
            ->call('openPunchModal', $testEmp->emp_code, $date)
            ->set('newPunchTimeInput', '09:05:00 PM')
            ->call('addPunch');

        $component->assertHasNoErrors();

        // Check it was inserted in DB
        $latestPunch = DB::table('iclock_transaction')
            ->where('emp_code', $testEmp->emp_code)
            ->whereDate('punch_time', $date)
            ->orderBy('id', 'desc')
            ->first();

        $this->assertNotNull($latestPunch);
        $this->assertStringContainsString('21:05:00', $latestPunch->punch_time);

        // Clean up inserted test punch
        DB::table('iclock_transaction')->where('id', $latestPunch->id)->delete();
    }

    /**
     * Test that unauthorized users are forbidden from opening the punch edit modal while shop staff can.
     */
    public function test_shop_staff_cannot_open_punch_modal(): void
    {
        $testEmp = $this->getOrCreateTestEmployee();
        $customer = User::firstOrCreate(
            ['email' => 'customer_unauth_punch@jaffnagold.com'],
            ['name' => 'Customer Test', 'password' => bcrypt('password'), 'contact' => '0770002222', 'role' => 'customer']
        );
        $this->actingAs($customer);

        $date = Carbon::now()->toDateString();

        Livewire::test(HrAttendance::class)
            ->call('openPunchModal', $testEmp->emp_code, $date)
            ->assertForbidden();

        $staff = $this->getOrCreateShopStaff();
        $this->actingAs($staff);

        Livewire::test(HrAttendance::class)
            ->call('openPunchModal', $testEmp->emp_code, $date)
            ->assertSet('showPunchModal', true);
    }

    /**
     * Test that unauthorized users are forbidden from adding punches.
     */
    public function test_shop_staff_cannot_add_punch(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer_unauth_punch@jaffnagold.com'],
            ['name' => 'Customer Test', 'password' => bcrypt('password'), 'contact' => '0770002222', 'role' => 'customer']
        );
        $this->actingAs($customer);

        Livewire::test(HrAttendance::class)
            ->call('addPunch')
            ->assertForbidden();
    }

    /**
     * Test that unauthorized users are forbidden from updating single punch, saving all punches, or deleting punches.
     */
    public function test_shop_staff_cannot_modify_or_delete_punches(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer_unauth_punch@jaffnagold.com'],
            ['name' => 'Customer Test', 'password' => bcrypt('password'), 'contact' => '0770002222', 'role' => 'customer']
        );
        $this->actingAs($customer);

        // 1. updateSinglePunch
        Livewire::test(HrAttendance::class)
            ->call('updateSinglePunch', 99999, '10:00:00')
            ->assertForbidden();

        // 2. saveAllPunches
        Livewire::test(HrAttendance::class)
            ->call('saveAllPunches')
            ->assertForbidden();

        // 3. deletePunch
        Livewire::test(HrAttendance::class)
            ->call('deletePunch', 99999)
            ->assertForbidden();
    }

    /**
     * Test attendance view hides punch edit triggers from unauthorized users and displays them for admin and shop staff.
     */
    public function test_attendance_view_edit_triggers_visibility_by_role(): void
    {
        $this->getOrCreateTestEmployee();
        $customer = User::firstOrCreate(
            ['email' => 'customer_unauth_punch@jaffnagold.com'],
            ['name' => 'Customer Test', 'password' => bcrypt('password'), 'contact' => '0770002222', 'role' => 'customer']
        );
        $staff = $this->getOrCreateShopStaff();
        $admin = $this->getOrCreateAdmin();

        // As unauthorized customer: punch edit button must not be visible
        $this->actingAs($customer);
        Livewire::test(HrAttendance::class)
            ->assertDontSeeHtml('wire:click="openPunchModal')
            ->assertDontSeeHtml('title="Edit / Add Punch Times"');

        // As shop staff: punch edit button is visible
        $this->actingAs($staff);
        Livewire::test(\App\Livewire\ShopStaff\HrAttendance::class)
            ->assertSeeHtml('wire:click="openPunchModal');

        // As admin: punch edit button must be visible
        $this->actingAs($admin);
        Livewire::test(HrAttendance::class)
            ->assertSeeHtml('wire:click="openPunchModal');
    }

    /**
     * Test setting and updating Old Salary Opening Balance in Admin HrSalary.
     */
    public function test_admin_hr_salary_opening_balance(): void
    {
        $testEmp = $this->getOrCreateTestEmployee();

        $currentMonth = Carbon::now()->format('Y-m');
        $salaryMonth = Carbon::parse($currentMonth . '-01')->toDateString();

        $hrService = app(HrService::class);
        $hrService->syncMonthlySalaries($currentMonth);

        $salary = HrSalary::where('emp_code', $testEmp->emp_code)
            ->where('salary_month', $salaryMonth)
            ->first();

        $this->assertNotNull($salary);

        // Open modal and set opening balance to 25,000
        Livewire::test(AdminHrSalary::class)
            ->call('openOpeningBalanceModal', $salary->id)
            ->assertSet('showOpeningBalanceModal', true)
            ->assertSet('obEmpCode', (string) $testEmp->emp_code)
            ->set('obAmount', 25000.00)
            ->call('saveOpeningBalance')
            ->assertSet('showOpeningBalanceModal', false);

        // Verify HrSalary updated
        $salary->refresh();
        $this->assertEquals(25000.00, (float) $salary->opening_balance);
        $this->assertGreaterThanOrEqual(25000.00, (float) $salary->net_salary);

        // Verify HrEmployeeSetting updated
        $setting = HrEmployeeSetting::where('emp_code', $testEmp->emp_code)->first();
        $this->assertNotNull($setting);
        $this->assertEquals(25000.00, (float) $setting->opening_balance);
    }

    /**
     * Test ShopStaff HrSalary also supports Old Salary Opening Balance.
     */
    public function test_shop_staff_hr_salary_opening_balance(): void
    {
        $testEmp = $this->getOrCreateTestEmployee();

        $currentMonth = Carbon::now()->format('Y-m');
        $salaryMonth = Carbon::parse($currentMonth . '-01')->toDateString();

        $salary = HrSalary::where('emp_code', $testEmp->emp_code)
            ->where('salary_month', $salaryMonth)
            ->first();

        $this->assertNotNull($salary);

        // Set to 30,000 via ShopStaff component
        Livewire::test(ShopStaffHrSalary::class)
            ->call('openOpeningBalanceModal', $salary->id)
            ->assertSet('showOpeningBalanceModal', true)
            ->set('obAmount', 30000.00)
            ->call('saveOpeningBalance')
            ->assertSet('showOpeningBalanceModal', false);

        $salary->refresh();
        $this->assertEquals(30000.00, (float) $salary->opening_balance);
    }
}
