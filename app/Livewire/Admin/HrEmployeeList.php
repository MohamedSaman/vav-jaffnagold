<?php

namespace App\Livewire\Admin;

use App\Models\ZkDepartment;
use App\Models\ZkEmployee;
use App\Services\HrService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('HR - Employee Management')]
class HrEmployeeList extends Component
{
    use WithPagination;

    public $search = '';

    public $selectedDepartment = '';

    // Modals
    public $showSettingsModal = false;

    public $showDetailsModal = false;

    public $showEditProfileModal = false;

    // Edit Profile Form
    public $editEmpId = null;

    public $editEmpCode = '';

    public $editFirstName = '';

    public $editLastName = '';

    public $editNickname = '';

    public $editMobile = '';

    public $editEmail = '';

    public $editDepartmentId = '';

    public $editGender = 'M';

    public $editHireDate = '';

    public $editBirthday = '';

    public $editAddress = '';

    // Selected Employee
    public $selectedEmpCode = null;

    public $selectedEmployee = null;

    // Settings Form
    public $daily_working_hours = 8.00;

    public $salary_type = 'monthly';

    public $basic_salary = 0.00;

    public $daily_rate = 0.00;

    public $hourly_rate = 0.00;

    public $overtime_rate = 0.00;

    public $allowance = 0.00;

    public $notes = '';

    // Details Modal Data
    public $employeeSummary = [];

    public $detailLogDate = '';

    public $detailLogs = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->detailLogDate = Carbon::now()->toDateString();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedDepartment()
    {
        $this->resetPage();
    }

    /**
     * Open Settings Modal to configure working hours & salary
     */
    public function openSettingsModal($empCode, HrService $hrService)
    {
        $employee = ZkEmployee::with('hrSetting')->where('emp_code', $empCode)->firstOrFail();
        $this->selectedEmpCode = $empCode;
        $this->selectedEmployee = $employee;

        $setting = $employee->hrSetting ?? $hrService->getOrCreateSetting($employee);

        $this->daily_working_hours = (float) $setting->daily_working_hours ?: 8.00;
        $this->salary_type = $setting->salary_type ?: 'monthly';
        $this->basic_salary = (float) $setting->basic_salary;
        $this->daily_rate = (float) $setting->daily_rate;
        $this->hourly_rate = (float) $setting->hourly_rate;
        $this->overtime_rate = (float) $setting->overtime_rate;
        $this->allowance = (float) $setting->allowance;
        $this->notes = $setting->notes ?? '';

        $this->showSettingsModal = true;
    }

    /**
     * Save employee working hours and salary settings
     */
    public function saveSettings(HrService $hrService)
    {
        $this->validate([
            'daily_working_hours' => 'required|numeric|min:1|max:24',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'basic_salary' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
        ]);

        $hrService->updateEmployeeSetting($this->selectedEmpCode, [
            'salary_type' => $this->salary_type,
            'daily_working_hours' => $this->daily_working_hours,
            'basic_salary' => $this->basic_salary,
            'daily_rate' => $this->daily_rate,
            'hourly_rate' => $this->hourly_rate,
            'overtime_rate' => $this->overtime_rate,
            'allowance' => $this->allowance,
            'notes' => $this->notes,
        ]);

        // Re-sync current month salary with updated settings
        $hrService->syncMonthlySalaries(Carbon::now()->format('Y-m'));

        $this->showSettingsModal = false;
        session()->flash('success', "Working hours & salary updated successfully for {$this->selectedEmployee->full_name}!");
    }

    /**
     * Open Details Modal to inspect employee profile, attendance, and salary
     */
    public function openDetailsModal($empCode, HrService $hrService)
    {
        $employee = ZkEmployee::with(['department', 'areas', 'hrSetting', 'hrSalaries'])
            ->where('emp_code', $empCode)
            ->firstOrFail();

        $this->selectedEmpCode = $empCode;
        $this->selectedEmployee = $employee;

        $currentMonth = Carbon::now()->format('Y-m');
        $attendance = $hrService->getMonthlyAttendanceSummary($currentMonth, $empCode)->first();

        $salary = $employee->hrSalaries()
            ->where('salary_month', Carbon::now()->startOfMonth()->toDateString())
            ->first();

        $this->employeeSummary = [
            'work_days' => $attendance['work_days'] ?? 0,
            'total_hours' => $attendance['total_hours'] ?? 0,
            'expected_hours' => $attendance['expected_hours'] ?? 0,
            'overtime_hours' => $attendance['overtime_hours'] ?? 0,
            'net_salary' => $salary ? (float) $salary->net_salary : 0,
            'paid_amount' => $salary ? (float) $salary->paid_amount : 0,
            'balance_amount' => $salary ? (float) $salary->balance_amount : 0,
            'payment_status' => $salary->payment_status ?? 'unpaid',
            'daily_details' => $attendance['daily_details'] ?? [],
        ];

        $this->loadDetailLogs($hrService);
        $this->showDetailsModal = true;
    }

    public function updatedDetailLogDate()
    {
        $this->loadDetailLogs(app(HrService::class));
    }

    protected function loadDetailLogs(HrService $hrService)
    {
        if ($this->selectedEmpCode && $this->detailLogDate) {
            $this->detailLogs = $hrService->getRawTimeLogs($this->detailLogDate, null, null, $this->selectedEmpCode)->toArray();
        } else {
            $this->detailLogs = [];
        }
    }

    public function openEditProfileModal($empCode)
    {
        $employee = ZkEmployee::where('emp_code', $empCode)->firstOrFail();
        $this->editEmpId = $employee->id;
        $this->editEmpCode = $employee->emp_code;
        $this->editFirstName = $employee->first_name;
        $this->editLastName = $employee->last_name !== $employee->first_name ? $employee->last_name : '';
        $this->editNickname = $employee->nickname ?? '';
        $this->editMobile = $employee->mobile ?? '';
        $this->editEmail = $employee->email ?? '';
        $this->editDepartmentId = $employee->department_id ?? '';
        $this->editGender = $employee->gender ?: 'M';
        $this->editHireDate = $employee->hire_date ? Carbon::parse($employee->hire_date)->toDateString() : '';
        $this->editBirthday = $employee->birthday ? Carbon::parse($employee->birthday)->toDateString() : '';
        $this->editAddress = $employee->address ?? '';

        $this->showEditProfileModal = true;
    }

    public function saveEmployeeProfile()
    {
        $this->validate([
            'editFirstName' => 'required|string|max:100',
            'editLastName' => 'nullable|string|max:100',
            'editNickname' => 'nullable|string|max:100',
            'editMobile' => 'nullable|string|max:20',
            'editEmail' => 'nullable|email|max:50',
            'editDepartmentId' => 'nullable|integer',
            'editGender' => 'nullable|in:M,F,O',
            'editHireDate' => 'nullable|date',
            'editBirthday' => 'nullable|date',
            'editAddress' => 'nullable|string|max:200',
        ]);

        $employee = ZkEmployee::findOrFail($this->editEmpId);
        $lastName = ! empty($this->editLastName) ? trim($this->editLastName) : trim($this->editFirstName);

        $employee->update([
            'first_name' => trim($this->editFirstName),
            'last_name' => $lastName,
            'nickname' => $this->editNickname ? trim($this->editNickname) : null,
            'mobile' => $this->editMobile ? trim($this->editMobile) : null,
            'email' => $this->editEmail ? trim($this->editEmail) : null,
            'department_id' => $this->editDepartmentId ?: null,
            'gender' => $this->editGender ?: 'M',
            'hire_date' => $this->editHireDate ?: null,
            'birthday' => $this->editBirthday ?: null,
            'address' => $this->editAddress ? trim($this->editAddress) : null,
        ]);

        $this->showEditProfileModal = false;

        // If details modal was open for this employee, refresh selectedEmployee
        if ($this->selectedEmpCode == $employee->emp_code) {
            $this->selectedEmployee = $employee->fresh(['department', 'areas', 'hrSetting', 'hrSalaries']);
        }

        session()->flash('success', "Employee details updated successfully for {$employee->full_name}!");
    }

    public function closeModals()
    {
        $this->showSettingsModal = false;
        $this->showDetailsModal = false;
        $this->showEditProfileModal = false;
    }

    public function render()
    {
        $query = ZkEmployee::with(['department', 'areas', 'hrSetting']);

        if (! empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('emp_code', 'like', "%{$s}%")
                    ->orWhere('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('mobile', 'like', "%{$s}%");
            });
        }

        if (! empty($this->selectedDepartment)) {
            $query->where('department_id', $this->selectedDepartment);
        }

        $employees = $query->orderBy('emp_code', 'asc')->paginate(15);
        $departments = ZkDepartment::orderBy('dept_name')->get();

        return view('livewire.admin.hr-employee-list', [
            'employees' => $employees,
            'departments' => $departments,
        ]);
    }
}
