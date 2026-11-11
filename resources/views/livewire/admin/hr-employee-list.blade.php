<div class="hr-container py-3">
    <style>
        .hr-header-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .hr-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }
        .hr-subtitle {
            font-size: 0.88rem;
            color: #64748b;
        }
        .emp-table-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .emp-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .rate-badge {
            font-size: 0.82rem;
            padding: 0.35rem 0.65rem;
            border-radius: 8px;
            font-weight: 600;
        }
        .badge-monthly { background: #e0e7ff; color: #3730a3; }
        .badge-daily { background: #fef3c7; color: #92400e; }
        .badge-hourly { background: #e0f2fe; color: #0369a1; }
        .modal-header-custom {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
            padding: 1rem 1.5rem;
        }
        .stat-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.85rem;
            text-align: center;
        }
        .stat-box .val {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
        }
        .stat-box .lbl {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 0.2rem;
        }
    </style>

    {{-- Flash Message --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Top Header Card --}}
    <div class="hr-header-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="hr-title">
                    <i class="bi bi-people-fill text-primary"></i> Employees &amp; Work Hours
                </h1>
                <div class="hr-subtitle">
                    ZKBio Time biometric staff list — manage standard daily working hours and salary structures
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6">
                    <i class="bi bi-fingerprint me-1"></i> Biometric Synced
                </span>
            </div>
        </div>

        {{-- Filters Strip --}}
        <div class="row g-2 mt-3 pt-3 border-top">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" placeholder="Search by name, employee code, mobile..." wire:model.live.debounce.300ms="search">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select" wire:model.live="selectedDepartment">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->dept_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 text-end d-flex align-items-center justify-content-end">
                <span class="text-muted small">Showing <strong>{{ $employees->total() }}</strong> Employees</span>
            </div>
        </div>
    </div>

    {{-- Employees Table --}}
    <div class="emp-table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 70px;">Code</th>
                        <th>Employee</th>
                        <th>Department &amp; Area</th>
                        <th>Contact</th>
                        <th>Daily Working Hours</th>
                        <th>Salary Setup</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        @php
                            $setting = $emp->hrSetting;
                            $dailyHours = $setting->daily_working_hours ?? 8.00;
                            $salaryType = $setting->salary_type ?? 'monthly';
                            $basic = $setting->basic_salary ?? 0;
                            $dRate = $setting->daily_rate ?? 0;
                            $hRate = $setting->hourly_rate ?? 0;
                            $otRate = $setting->overtime_rate ?? 0;
                            $allowance = $setting->allowance ?? 0;
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-dark-subtle text-dark font-monospace fw-bold px-2 py-1">
                                    #{{ $emp->emp_code }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="emp-avatar">
                                        {{ strtoupper(substr($emp->first_name ?: 'E', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6" role="button" wire:click="openDetailsModal('{{ $emp->emp_code }}')" style="cursor: pointer;">
                                            {{ $emp->full_name }}
                                        </div>
                                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>Joined: {{ $emp->hire_date ? \Carbon\Carbon::parse($emp->hire_date)->format('d M Y') : 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $emp->department?->dept_name ?? 'Default Department' }}</div>
                                <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $emp->primary_area }}</small>
                            </td>
                            <td>
                                <div><i class="bi bi-telephone me-1 text-muted"></i>{{ $emp->mobile ?: '—' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1 fs-6">
                                    <i class="bi bi-clock me-1"></i>{{ number_format($dailyHours, 1) }} hrs/day
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div>
                                        @if($salaryType === 'monthly')
                                            <span class="rate-badge badge-monthly">Monthly: Rs. {{ number_format($basic, 2) }}</span>
                                        @elseif($salaryType === 'daily')
                                            <span class="rate-badge badge-daily">Daily: Rs. {{ number_format($dRate, 2) }}</span>
                                        @else
                                            <span class="rate-badge badge-hourly">Hourly: Rs. {{ number_format($hRate, 2) }}</span>
                                        @endif
                                    </div>
                                    @if($otRate > 0 || $allowance > 0)
                                        <div class="small text-muted">
                                            @if($otRate > 0) <span>OT: Rs. {{ number_format($otRate, 2) }}/hr</span> @endif
                                            @if($allowance > 0) <span class="ms-1">Allow: Rs. {{ number_format($allowance, 2) }}</span> @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-dark" wire:click="openEditProfileModal('{{ $emp->emp_code }}')" title="Edit Employee Name & Details">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </button>
                                    <button class="btn btn-outline-primary" wire:click="openSettingsModal('{{ $emp->emp_code }}')" title="Edit Working Hours & Salary">
                                        <i class="bi bi-gear-fill me-1"></i> Hours &amp; Salary
                                    </button>
                                    <button class="btn btn-outline-secondary" wire:click="openDetailsModal('{{ $emp->emp_code }}')" title="View Employee Details & Time Logs">
                                        <i class="bi bi-eye-fill me-1"></i> Details
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                                No biometric employees found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $employees->links() }}
        </div>
    </div>

    {{-- MODAL 1: Edit Working Hours & Salary Modal --}}
    @if($showSettingsModal && $selectedEmployee)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); z-index: 1055;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header modal-header-custom">
                        <div>
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="bi bi-sliders me-1 text-primary"></i> Working Hours &amp; Salary
                            </h5>
                            <small class="text-muted">Configuring for <strong>{{ $selectedEmployee->full_name }}</strong> (Emp #{{ $selectedEmployee->emp_code }})</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit.prevent="saveSettings">
                        <div class="modal-body p-4">
                            {{-- Standard Daily Working Hours --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1 text-primary"></i> Standard Daily Working Hours
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="1" max="24" class="form-control @error('daily_working_hours') is-invalid @enderror" wire:model="daily_working_hours" required>
                                    <span class="input-group-text bg-light">Hours / Day</span>
                                </div>
                                <small class="text-muted">Standard daily shift duration used to calculate attendance &amp; overtime.</small>
                                @error('daily_working_hours') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Salary Type --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-wallet2 me-1 text-success"></i> Salary Calculation Type
                                </label>
                                <select class="form-select @error('salary_type') is-invalid @enderror" wire:model.live="salary_type" required>
                                    <option value="monthly">Monthly Fixed Basic Salary</option>
                                    <option value="daily">Daily Rate (Based on Worked Days)</option>
                                    <option value="hourly">Hourly Rate (Based on Exact Worked Hours)</option>
                                </select>
                                @error('salary_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Conditional Rates --}}
                            @if($salary_type === 'monthly')
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Monthly Basic Salary (LKR)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rs.</span>
                                        <input type="number" step="0.01" min="0" class="form-control" wire:model="basic_salary" placeholder="e.g. 50000.00">
                                    </div>
                                </div>
                            @elseif($salary_type === 'daily')
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Daily Rate (LKR)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rs.</span>
                                        <input type="number" step="0.01" min="0" class="form-control" wire:model="daily_rate" placeholder="e.g. 2000.00">
                                        <span class="input-group-text bg-light">/ Day</span>
                                    </div>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Hourly Rate (LKR)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rs.</span>
                                        <input type="number" step="0.01" min="0" class="form-control" wire:model="hourly_rate" placeholder="e.g. 300.00">
                                        <span class="input-group-text bg-light">/ Hour</span>
                                    </div>
                                </div>
                            @endif

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Overtime Rate / Hr</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rs.</span>
                                        <input type="number" step="0.01" min="0" class="form-control" wire:model="overtime_rate" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Monthly Allowance</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rs.</span>
                                        <input type="number" step="0.01" min="0" class="form-control" wire:model="allowance" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notes / Remarks</label>
                                <textarea class="form-control" rows="2" wire:model="notes" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                            <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">
                                <span wire:loading.remove wire:target="saveSettings">
                                    <i class="bi bi-check2-circle me-1"></i> Save Settings
                                </span>
                                <span wire:loading wire:target="saveSettings">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 2: Employee Details & Date-Wise Time Logs Modal --}}
    @if($showDetailsModal && $selectedEmployee)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); z-index: 1055;">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header modal-header-custom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="emp-avatar fs-5">
                                {{ strtoupper(substr($selectedEmployee->first_name ?: 'E', 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark mb-0">{{ $selectedEmployee->full_name }}</h5>
                                <div class="small text-muted">
                                    <span>Code: #{{ $selectedEmployee->emp_code }}</span> &bull; 
                                    <span>Dept: {{ $selectedEmployee->department?->dept_name ?? 'N/A' }}</span> &bull; 
                                    <span>Area: {{ $selectedEmployee->primary_area }}</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body p-4">
                        {{-- Quick Metrics for Current Month --}}
                        <div class="row g-2 mb-4">
                            <div class="col-md-3 col-6">
                                <div class="stat-box">
                                    <div class="val text-primary">{{ $employeeSummary['work_days'] ?? 0 }} Days</div>
                                    <div class="lbl">Worked Days</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="stat-box">
                                    <div class="val text-info">{{ number_format($employeeSummary['total_hours'] ?? 0, 1) }} hrs</div>
                                    <div class="lbl">Total Hours</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="stat-box">
                                    <div class="val text-success">Rs. {{ number_format($employeeSummary['net_salary'] ?? 0, 2) }}</div>
                                    <div class="lbl">Month Salary</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="stat-box">
                                    <div class="val {{ ($employeeSummary['balance_amount'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                        Rs. {{ number_format($employeeSummary['balance_amount'] ?? 0, 2) }}
                                    </div>
                                    <div class="lbl">Balance Due</div>
                                </div>
                            </div>
                        </div>

                        {{-- Date-wise Pick Time Logs Section --}}
                        <div class="card border border-primary-subtle bg-light-subtle rounded-3 mb-4">
                            <div class="card-body p-3">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-calendar2-range text-primary me-1"></i> Date-Wise Biometric Time Logs
                                    </h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="small text-muted text-nowrap mb-0">Pick Date:</label>
                                        <input type="date" class="form-control form-control-sm" style="width: auto;" wire:model.live="detailLogDate">
                                    </div>
                                </div>

                                {{-- Punch List --}}
                                @if(count($detailLogs) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered bg-white mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Punch Time</th>
                                                    <th>Device / Terminal</th>
                                                    <th>Area</th>
                                                    <th>Verification</th>
                                                    <th>State</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($detailLogs as $log)
                                                    <tr>
                                                        <td class="fw-bold font-monospace text-primary">
                                                            {{ \Carbon\Carbon::parse($log->punch_time)->format('H:i:s') }}
                                                            <small class="text-muted fw-normal">({{ \Carbon\Carbon::parse($log->punch_time)->format('d M Y') }})</small>
                                                        </td>
                                                        <td>{{ $log->terminal_alias ?: ('Terminal #' . $log->terminal_id) }}</td>
                                                        <td>{{ $log->area_alias ?: 'Main Shop' }}</td>
                                                        <td>
                                                            <span class="badge bg-secondary-subtle text-secondary">
                                                                {{ $log->verify_type == 1 ? 'Fingerprint' : 'Biometric' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success-subtle text-success">Punch Logged</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-clock-history d-block fs-3 mb-1 text-secondary"></i>
                                        No punch logs recorded for this employee on {{ \Carbon\Carbon::parse($detailLogDate)->format('d M Y') }}.
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Attendance Details for Month --}}
                        @if(!empty($employeeSummary['daily_details']))
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-calendar-check me-1 text-success"></i> Attendance Days in Current Month</h6>
                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Worked Hours</th>
                                            <th>OT Hours</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employeeSummary['daily_details'] as $day)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($day['date'])->format('d M Y (D)') }}</td>
                                                <td><span class="badge bg-light text-dark font-monospace">{{ $day['first_in'] }}</span></td>
                                                <td><span class="badge bg-light text-dark font-monospace">{{ $day['last_out'] }}</span></td>
                                                <td class="fw-bold">{{ number_format($day['hours'], 2) }} hrs</td>
                                                <td>{{ number_format($day['ot'], 2) }} hrs</td>
                                                <td><span class="badge bg-success-subtle text-success">{{ ucfirst($day['status']) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Close</button>
                        <button type="button" class="btn btn-outline-dark" wire:click="openEditProfileModal('{{ $selectedEmployee->emp_code }}')">
                            <i class="bi bi-pencil-square me-1"></i> Edit Details
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="openSettingsModal('{{ $selectedEmployee->emp_code }}')">
                            <i class="bi bi-gear-fill me-1"></i> Edit Hours &amp; Salary
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 3: Edit Employee Details (Name, Contact, Department, etc.) --}}
    @if($showEditProfileModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header modal-header-custom">
                        <div>
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="bi bi-person-lines-fill me-1 text-primary"></i> Edit Employee Details
                            </h5>
                            <small class="text-muted">Editing personnel details for Emp #<strong>{{ $editEmpCode }}</strong></small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit.prevent="saveEmployeeProfile">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- First Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('editFirstName') is-invalid @enderror" wire:model="editFirstName" required placeholder="First name">
                                    @error('editFirstName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Last Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Last Name</label>
                                    <input type="text" class="form-control @error('editLastName') is-invalid @enderror" wire:model="editLastName" placeholder="Last name">
                                    @error('editLastName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Nickname / Display Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nickname / Display Name</label>
                                    <input type="text" class="form-control @error('editNickname') is-invalid @enderror" wire:model="editNickname" placeholder="e.g. Sam">
                                    @error('editNickname') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Department --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Department</label>
                                    <select class="form-select @error('editDepartmentId') is-invalid @enderror" wire:model="editDepartmentId">
                                        <option value="">No Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->dept_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('editDepartmentId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Mobile Number --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mobile Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                        <input type="text" class="form-control @error('editMobile') is-invalid @enderror" wire:model="editMobile" placeholder="e.g. 0771234567">
                                    </div>
                                    @error('editMobile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control @error('editEmail') is-invalid @enderror" wire:model="editEmail" placeholder="employee@example.com">
                                    </div>
                                    @error('editEmail') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Gender --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Gender</label>
                                    <select class="form-select" wire:model="editGender">
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                        <option value="O">Other</option>
                                    </select>
                                </div>

                                {{-- Hire Date --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Hire Date / Joining</label>
                                    <input type="date" class="form-control @error('editHireDate') is-invalid @enderror" wire:model="editHireDate">
                                    @error('editHireDate') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Birthday --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Birthday</label>
                                    <input type="date" class="form-control @error('editBirthday') is-invalid @enderror" wire:model="editBirthday">
                                    @error('editBirthday') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Address --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <textarea class="form-control @error('editAddress') is-invalid @enderror" rows="2" wire:model="editAddress" placeholder="Residential / Contact address"></textarea>
                                    @error('editAddress') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                            <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">
                                <span wire:loading.remove wire:target="saveEmployeeProfile">
                                    <i class="bi bi-check-circle me-1"></i> Save Changes
                                </span>
                                <span wire:loading wire:target="saveEmployeeProfile">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
