<?php

namespace Tests\Feature;

use App\Livewire\Admin\HrAttendance;
use App\Livewire\Admin\HrSalary;
use App\Models\User;
use App\Models\ZkEmployee;
use App\Services\HrService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class HrResetRecordsTest extends TestCase
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

    /**
     * Test that unauthorized users cannot access the reset modal while authorized shop staff can.
     */
    public function test_shop_staff_cannot_execute_reset(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer_unauth@jaffnagold.com'],
            ['name' => 'Customer Test', 'password' => bcrypt('password'), 'contact' => '0770001111', 'role' => 'customer']
        );
        $this->actingAs($customer);

        Livewire::test(HrSalary::class)
            ->call('openResetModal')
            ->assertForbidden();

        Livewire::test(HrAttendance::class)
            ->call('openResetModal')
            ->assertForbidden();

        $staff = $this->getOrCreateShopStaff();
        $this->actingAs($staff);

        Livewire::test(HrSalary::class)
            ->call('openResetModal')
            ->assertSet('showResetModal', true);
    }

    /**
     * Test that admin can open reset modal and execute reset with typing RESET.
     */
    public function test_admin_can_reset_records_via_hr_salary(): void
    {
        $admin = $this->getOrCreateAdmin();
        $this->actingAs($admin);

        // Insert a dummy punch to verify deletion
        $testEmp = ZkEmployee::first();
        if (! $testEmp) {
            $testEmp = ZkEmployee::create(['emp_code' => 'TEST99', 'first_name' => 'Test', 'last_name' => 'Emp']);
        }

        DB::table('iclock_transaction')->insert([
            'emp_code' => $testEmp->emp_code,
            'punch_time' => Carbon::now()->toDateTimeString(),
            'punch_state' => '0',
            'verify_type' => 1,
            'emp_id' => $testEmp->id,
            'terminal_id' => 1,
            'terminal_alias' => 'Test Terminal',
            'area_alias' => 'Shop',
            'upload_time' => Carbon::now()->toDateTimeString(),
        ]);

        $this->assertGreaterThan(0, DB::table('iclock_transaction')->count());

        // Attempt without typing RESET
        Livewire::test(HrSalary::class)
            ->call('openResetModal')
            ->assertSet('showResetModal', true)
            ->set('resetConfirmText', 'wrong')
            ->call('executeReset')
            ->assertHasErrors(['resetConfirmText']);

        // Execute with typing RESET
        Livewire::test(HrSalary::class)
            ->call('openResetModal')
            ->set('resetConfirmText', 'RESET')
            ->set('resetScope', 'all')
            ->call('executeReset')
            ->assertHasNoErrors()
            ->assertSet('showResetModal', false);

        // Verify all punches are cleared
        $this->assertEquals(0, DB::table('iclock_transaction')->count());
        // Verify staff profile is NOT deleted
        $this->assertNotNull(ZkEmployee::where('emp_code', $testEmp->emp_code)->first());
    }
}
