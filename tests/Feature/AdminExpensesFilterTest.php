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
}
