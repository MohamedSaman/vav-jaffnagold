<?php

namespace Tests\Feature;

use App\Livewire\Admin\Expenses;
use Tests\TestCase;

class AdminExpensesFilterTest extends TestCase
{
    /**
     * Test Expenses component defaults to current month.
     */
    public function test_expenses_defaults_to_current_month(): void
    {
        $component = new Expenses;
        $this->assertEquals('current', $component->filterMonth);
    }

    /**
     * Test switching month filter updates filterMonth and customMonth state.
     */
    public function test_switching_month_filter_updates_state(): void
    {
        $component = new class extends Expenses
        {
            public function loadExpenses()
            {
                // Bypass database calls for unit testing state transitions
            }
        };

        $this->assertEquals('current', $component->filterMonth);

        // Switch to all months
        $component->setMonthFilter('all');
        $this->assertEquals('all', $component->filterMonth);
        $this->assertEquals('', $component->customMonth);

        // Switch back to current month
        $component->setMonthFilter('current');
        $this->assertEquals('current', $component->filterMonth);

        // Switch to custom month via updatedCustomMonth
        $component->updatedCustomMonth('2026-08');
        $this->assertEquals('custom', $component->filterMonth);
    }

    /**
     * Test expenses view renders without sum() on array errors when properties are arrays.
     */
    public function test_expenses_view_renders_with_array_properties(): void
    {
        $rendered = view('livewire.admin.expenses', [
            'errors' => new \Illuminate\Support\ViewErrorBag,
            'filterMonth' => 'current',
            'customMonth' => '',
            'dailyExpenses' => [],
            'monthlyExpenses' => [],
            'dailyCategories' => [],
            'monthlyCategories' => [],
            'todayTotal' => 0,
            'monthTotal' => 0,
            'overallTotal' => 0,
            'filteredTotal' => 0,
            'category' => null,
            'amount' => null,
            'date' => date('Y-m-d'),
            'status' => null,
            'description' => null,
            'customCategory' => null,
            'is_salary_deduction' => false,
            'deduction_staff_id' => null,
            'staffSearch' => '',
            'filteredStaff' => [],
            'allStaff' => [],
            'expenseId' => null,
            'edit_category' => null,
            'edit_amount' => null,
            'edit_date' => null,
            'edit_status' => null,
            'edit_description' => null,
            'edit_expense_type' => null,
            'showEditDailyModal' => false,
            'showEditMonthlyModal' => false,
            'showDeleteModal' => false,
            'showViewModal' => false,
            'viewExpense' => null,
        ])->render();

        $this->assertStringContainsString('Daily Expenses', $rendered);
        $this->assertStringContainsString('Monthly Expenses', $rendered);
    }
}
