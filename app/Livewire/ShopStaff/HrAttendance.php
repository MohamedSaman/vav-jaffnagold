<?php

namespace App\Livewire\ShopStaff;

use App\Livewire\Admin\HrAttendance as AdminHrAttendance;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.shop-staff')]
#[Title('Shop Staff - HR Attendance & Time Logs')]
class HrAttendance extends AdminHrAttendance
{
    // Inherits all logic, methods, date pickers, and views from AdminHrAttendance
}
