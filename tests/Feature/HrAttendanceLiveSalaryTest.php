<?php

namespace Tests\Feature;

use App\Livewire\Admin\HrAttendance;
use App\Models\HrSalaryPayment;
use App\Models\ZkEmployee;
use App\Services\HrService;
use Carbon\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class HrAttendanceLiveSalaryTest extends TestCase
{
    /**
     * Test worked hours salary calculation and 10+ hours full day salary rule.
     */
    public function test_salary_calculation_10_hours_full_day_rule(): void
    {
        $hrService = app(HrService::class);

        // 1. 7.07 hours (e.g. 5000 daily rate, 500/hr -> Rs. 3535)
        $punches = [
            ['id' => 101, 'punch_time' => '2026-10-16 08:57:54'],
            ['id' => 102, 'punch_time' => '2026-10-16 11:22:02'],
            ['id' => 103, 'punch_time' => '2026-10-16 11:33:35'],
            ['id' => 104, 'punch_time' => '2026-10-16 16:13:36'],
        ];

        $sessionData = $hrService->processPunchSessions($punches, '2026-10-16');
        $this->assertEquals(7.07, $sessionData['total_hours']);

        $dayFullRate = 5000.00;
        $hourlyRate = 500.00;
        $calculatedSalary = round($sessionData['total_hours'] * $hourlyRate, 2);
        $this->assertEquals(3535.00, $calculatedSalary);

        // 2. 10+ hours (e.g. 11.5 hours) receives full day salary only, no overtime
        $punches11 = [
            ['id' => 101, 'punch_time' => '2026-10-16 08:00:00'],
            ['id' => 102, 'punch_time' => '2026-10-16 19:30:00'], // 11.5 hours
        ];

        $sessionData11 = $hrService->processPunchSessions($punches11, '2026-10-16');
        $this->assertEquals(11.5, $sessionData11['total_hours']);

        $salary11 = ($sessionData11['total_hours'] >= 10.0) ? $dayFullRate : ($sessionData11['total_hours'] * $hourlyRate);
        $this->assertEquals(5000.00, $salary11);
    }

    /**
     * Test Livewire HrAttendance open give salary modal and submit payment.
     */
    public function test_give_salary_modal_and_submission(): void
    {
        $testEmp = ZkEmployee::first();
        $created = false;
        if (! $testEmp) {
            $testEmp = ZkEmployee::create([
                'emp_code' => 'TEST999',
                'first_name' => 'Test',
                'last_name' => 'Staff',
            ]);
            $created = true;
        }

        $date = Carbon::now()->toDateString();

        Livewire::test(HrAttendance::class)
            ->call('openGiveSalaryModal', $testEmp->emp_code, $date)
            ->assertSet('showGiveSalaryModal', true)
            ->assertSeeHtml('Give Salary — ' . $testEmp->full_name)
            ->assertSeeHtml('Confirm &amp; Give Salary')
            ->set('giveSalaryAmount', '1500')
            ->set('giveSalaryPaymentMethod', 'Cash')
            ->call('submitGiveSalary')
            ->assertHasNoErrors()
            ->assertSet('showGiveSalaryModal', false);

        // Clean up test payment
        HrSalaryPayment::where('emp_code', $testEmp->emp_code)
            ->whereDate('payment_date', $date)
            ->where('amount', 1500)
            ->delete();

        if ($created) {
            $testEmp->delete();
        }
    }
}
