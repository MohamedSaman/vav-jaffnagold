<?php

namespace App\Livewire\ShopStaff;

use App\Livewire\Admin\HrEmployeeList as AdminHrEmployeeList;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.shop-staff')]
#[Title('Shop Staff - HR Employees')]
class HrEmployeeList extends AdminHrEmployeeList
{
    // Inherits all logic, methods, modals, and views from AdminHrEmployeeList
}
