<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\ShopStaffExpenses;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ShopStaffExpensesFilterTest extends TestCase
{
    /**
     * Test ShopStaffExpenses defaults dateFilter to today.
     */
    public function test_shop_staff_expenses_defaults(): void
    {
        $component = new ShopStaffExpenses;
        $component->mount();

        $this->assertEquals(date('Y-m-d'), $component->dateFilter);
        $this->assertEquals('', $component->search);
    }

    /**
     * Test setting calendar date filter and setToday.
     */
    public function test_setting_calendar_date_filter_and_today(): void
    {
        $component = new ShopStaffExpenses;

        $component->dateFilter = '2026-10-15';
        $this->assertEquals('2026-10-15', $component->dateFilter);

        $component->setToday();
        $this->assertEquals(date('Y-m-d'), $component->dateFilter);
    }

    /**
     * Test blade view renders with old look and calendar date picker.
     */
    public function test_shop_staff_expenses_view_renders_old_look_with_calendar(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 20, 1);

        $rendered = view('livewire.shop-staff.shop-staff-expenses', [
            'errors' => new \Illuminate\Support\ViewErrorBag,
            'expenses' => $paginator,
            'todayExpenses' => 2500.00,
            'monthExpenses' => 45000.00,
            'search' => '',
            'dateFilter' => date('Y-m-d'),
            'showAddModal' => false,
            'showDeleteModal' => false,
            'is_salary' => false,
            'registeredSuppliers' => [],
            'dbExpenseTypes' => [],
            'salaryEmployees' => [],
        ])->render();

        $this->assertStringContainsString('Daily Expenses', $rendered);
        $this->assertStringContainsString("Today's Expenses", $rendered);
        $this->assertStringContainsString('This Month Total', $rendered);
        $this->assertStringContainsString('Expense History', $rendered);
        $this->assertStringContainsString('wire:model.live="dateFilter"', $rendered);
        $this->assertStringContainsString('type="date"', $rendered);
        $this->assertStringContainsString('Rs. 2,500.00', $rendered);
        $this->assertStringContainsString('Rs. 45,000.00', $rendered);
    }
}
