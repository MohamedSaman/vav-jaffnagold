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
        .metric-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 1.15rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .metric-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .icon-salary { background: #e0e7ff; color: #4338ca; }
        .icon-paid { background: #dcfce7; color: #15803d; }
        .icon-balance { background: #fee2e2; color: #b91c1c; }
        .icon-staff { background: #fef3c7; color: #b45309; }
        .table-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .emp-click-row {
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .emp-click-row:hover {
            background: #f8fafc !important;
        }
        .salary-breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 0.92rem;
        }
        .salary-breakdown-row:last-child {
            border-bottom: none;
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

    {{-- Top Header Card with Month Selector --}}
    <div class="hr-header-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="hr-title">
                    <i class="bi bi-cash-coin text-success"></i> Monthly Salary Management
                </h1>
                <div class="hr-subtitle">
                    Attendance-based salary calculations, total month pay, paid amount &amp; balance tracking
                </div>
            </div>

            {{-- Month Navigator --}}
            <div class="d-flex align-items-center gap-2">
                <div class="btn-group">
                    <button class="btn btn-outline-secondary btn-sm" wire:click="previousMonth" title="Previous Month">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <input type="month" class="form-control form-control-sm text-center fw-bold font-monospace" style="width: 140px;" wire:model.live="selectedMonth">
                    <button class="btn btn-outline-secondary btn-sm" wire:click="nextMonth" title="Next Month">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <button class="btn btn-primary btn-sm" wire:click="syncSalaries" title="Recalculate and sync with biometric punches">
                    <span wire:loading.remove wire:target="syncSalaries">
                        <i class="bi bi-arrow-clockwise me-1"></i> Sync &amp; Recalculate
                    </span>
                    <span wire:loading wire:target="syncSalaries">
                        <span class="spinner-border spinner-border-sm me-1"></span> Syncing...
                    </span>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="openResetModal" title="Reset attendance punches and salary records">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Records
                </button>
            </div>
        </div>

        {{-- Filter Row --}}
        <div class="row g-2 mt-3 pt-3 border-top align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" placeholder="Search employee name or code..." wire:model.live.debounce.300ms="search">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" wire:model.live="statusFilter">
                    <option value="">All Payment Statuses</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partially Paid</option>
                    <option value="paid">Fully Paid</option>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-light text-dark border px-3 py-2 font-monospace fs-6">
                    <i class="bi bi-calendar-check me-1 text-primary"></i> {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Financial Summary Cards --}}
    <div class="row g-3 mb-4">
        {{-- Total Salary Current Month --}}
        <div class="col-md-3 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-salary"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-primary">Rs. {{ number_format($totalNetSalary, 2) }}</div>
                    <div class="small text-muted fw-semibold">Total Month Salary</div>
                    @if(isset($totalOpeningBalance) && $totalOpeningBalance > 0)
                        <div class="badge bg-secondary-subtle text-secondary font-monospace mt-1" style="font-size: 0.72rem;">
                            Incl. Rs. {{ number_format($totalOpeningBalance, 0) }} Old Bal
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Total Paid Amount --}}
        <div class="col-md-3 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-paid"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-success">Rs. {{ number_format($totalPaidAmount, 2) }}</div>
                    <div class="small text-muted fw-semibold">Total Paid Amount</div>
                </div>
            </div>
        </div>

        {{-- Total Balance Salary --}}
        <div class="col-md-3 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-balance"><i class="bi bi-exclamation-circle"></i></div>
                <div>
                    <div class="fs-4 fw-bold {{ $totalBalanceAmount > 0 ? 'text-danger' : 'text-success' }}">
                        Rs. {{ number_format($totalBalanceAmount, 2) }}
                    </div>
                    <div class="small text-muted fw-semibold">Total Balance Due</div>
                </div>
            </div>
        </div>

        {{-- Total Staff --}}
        <div class="col-md-3 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-staff"><i class="bi bi-people"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-dark">{{ $totalStaff }}</div>
                    <div class="small text-muted fw-semibold">Employees in Payroll</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Salaries Table --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 70px;">Code</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Work Days &amp; Hours</th>
                        <th style="width: 140px;">Opening Bal</th>
                        <th>Total Salary</th>
                        <th>Paid Amount</th>
                        <th>Balance Salary</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $sal)
                        @php
                            $emp = $sal->employee;
                        @endphp
                        <tr class="emp-click-row">
                            <td class="ps-3" wire:click="openDetailsModal({{ $sal->id }})">
                                <span class="badge bg-dark-subtle text-dark font-monospace fw-bold px-2 py-1">
                                    #{{ $sal->emp_code }}
                                </span>
                            </td>
                            <td wire:click="openDetailsModal({{ $sal->id }})">
                                <div class="fw-bold text-dark fs-6 text-primary">{{ $emp?->full_name ?? ('Employee #' . $sal->emp_code) }}</div>
                                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Click to view details</small>
                            </td>
                            <td wire:click="openDetailsModal({{ $sal->id }})">
                                <div>{{ $emp?->department?->dept_name ?? 'Default Dept' }}</div>
                                <small class="text-muted">{{ ucfirst($sal->salary_type) }} basis</small>
                            </td>
                            <td wire:click="openDetailsModal({{ $sal->id }})">
                                <div class="fw-semibold">{{ $sal->total_work_days }} Days</div>
                                <small class="text-muted">{{ number_format($sal->total_hours_worked, 1) }} hrs worked</small>
                                @if($sal->overtime_hours > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis font-monospace" style="font-size: 0.72rem;">
                                        +{{ number_format($sal->overtime_hours, 1) }} OT
                                    </span>
                                @endif
                            </td>
                            <td wire:click.stop>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 d-flex align-items-center gap-1" wire:click="openOpeningBalanceModal({{ $sal->id }})" title="Click to edit old salary opening balance">
                                    <span class="fw-bold font-monospace {{ $sal->opening_balance > 0 ? 'text-primary' : 'text-muted' }}">
                                        Rs. {{ number_format($sal->opening_balance, 2) }}
                                    </span>
                                    <i class="bi bi-pencil-square text-secondary" style="font-size: 0.8rem;"></i>
                                </button>
                            </td>
                            <td wire:click="openDetailsModal({{ $sal->id }})">
                                <div class="fw-bold fs-6 text-dark font-monospace">
                                    Rs. {{ number_format($sal->net_salary, 2) }}
                                </div>
                                @if($sal->opening_balance > 0)
                                    <div class="small text-muted" style="font-size: 0.73rem;">
                                        <span class="badge bg-secondary-subtle text-secondary">+Rs. {{ number_format($sal->opening_balance, 0) }} Old Bal</span>
                                    </div>
                                @endif
                            </td>
                            <td wire:click="openDetailsModal({{ $sal->id }})">
                                <div class="fw-bold font-monospace text-success">
                                    Rs. {{ number_format($sal->paid_amount, 2) }}
                                </div>
                            </td>
                            <td wire:click="openDetailsModal({{ $sal->id }})">
                                <div class="fw-bold font-monospace {{ $sal->balance_amount > 0 ? 'text-danger' : 'text-success' }}">
                                    Rs. {{ number_format($sal->balance_amount, 2) }}
                                </div>
                            </td>
                            <td wire:click="openDetailsModal({{ $sal->id }})">
                                @if($sal->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success px-2 py-1">
                                        <i class="bi bi-check-all me-1"></i>Paid
                                    </span>
                                @elseif($sal->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1">
                                        <i class="bi bi-pie-chart me-1"></i>Partial
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1">
                                        <i class="bi bi-x-circle me-1"></i>Unpaid
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="openDetailsModal({{ $sal->id }})" title="View Details Breakdown">
                                        <i class="bi bi-eye me-1"></i> Details
                                    </button>
                                    <button class="btn btn-outline-secondary" wire:click="openOpeningBalanceModal({{ $sal->id }})" title="Add/Edit Old Salary Opening Balance">
                                        <i class="bi bi-wallet2 me-1"></i> Opening Bal
                                    </button>
                                    @if($sal->balance_amount > 0)
                                        <button class="btn btn-success" wire:click="openPaymentModal({{ $sal->id }})" title="Record Payment">
                                            <i class="bi bi-cash-stack me-1"></i> Pay
                                        </button>
                                    @else
                                        <button class="btn btn-outline-secondary" disabled>
                                            <i class="bi bi-check-circle me-1"></i> Paid
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-stack fs-1 d-block mb-2 text-secondary"></i>
                                No salary records found for this month. Click <strong>"Sync &amp; Recalculate"</strong> to generate from biometric punches.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL 1: Employee Details Modal (Click employee to see details) --}}
    @if($showDetailsModal && $selectedSalary && $selectedEmployee)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); z-index: 1055;">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header bg-light border-bottom p-3" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                                {{ strtoupper(substr($selectedEmployee->first_name ?: 'E', 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark mb-0">{{ $selectedEmployee->full_name }}</h5>
                                <div class="small text-muted">
                                    <span>Code: #{{ $selectedEmployee->emp_code }}</span> &bull; 
                                    <span>Dept: {{ $selectedEmployee->department?->dept_name ?? 'N/A' }}</span> &bull; 
                                    <span>Month: {{ \Carbon\Carbon::parse($selectedSalary->salary_month)->format('F Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>

                    <div class="modal-body p-4">
                        {{-- 1. Financial Snapshot --}}
                        <div class="row g-2 mb-4">
                            <div class="col-md-4 col-12">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <div class="small text-muted text-uppercase fw-semibold">Total Month Salary</div>
                                    <div class="fs-4 fw-bold text-primary font-monospace mt-1">
                                        Rs. {{ number_format($selectedSalary->net_salary, 2) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <div class="small text-muted text-uppercase fw-semibold">Paid Amount</div>
                                    <div class="fs-4 fw-bold text-success font-monospace mt-1">
                                        Rs. {{ number_format($selectedSalary->paid_amount, 2) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <div class="small text-muted text-uppercase fw-semibold">Balance Salary</div>
                                    <div class="fs-4 fw-bold {{ $selectedSalary->balance_amount > 0 ? 'text-danger' : 'text-success' }} font-monospace mt-1">
                                        Rs. {{ number_format($selectedSalary->balance_amount, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Detailed Salary & Attendance Breakdown --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card border h-100 rounded-3">
                                    <div class="card-header bg-white fw-bold text-dark py-2">
                                        <i class="bi bi-clock-history me-1 text-primary"></i> Attendance Performance
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="salary-breakdown-row">
                                            <span class="text-muted">Total Work Days</span>
                                            <span class="fw-bold">{{ $selectedSalary->total_work_days }} Days</span>
                                        </div>
                                        <div class="salary-breakdown-row">
                                            <span class="text-muted">Standard Daily Hours</span>
                                            <span class="fw-bold">{{ number_format($selectedSalary->daily_working_hours, 1) }} hrs/day</span>
                                        </div>
                                        <div class="salary-breakdown-row">
                                            <span class="text-muted">Total Worked Hours</span>
                                            <span class="fw-bold text-primary">{{ number_format($selectedSalary->total_hours_worked, 2) }} hrs</span>
                                        </div>
                                        <div class="salary-breakdown-row">
                                            <span class="text-muted">Expected Hours</span>
                                            <span class="fw-bold">{{ number_format($selectedSalary->expected_hours, 2) }} hrs</span>
                                        </div>
                                        <div class="salary-breakdown-row">
                                            <span class="text-muted">Overtime Hours</span>
                                            <span class="fw-bold text-warning-emphasis">+{{ number_format($selectedSalary->overtime_hours, 2) }} hrs</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border h-100 rounded-3">
                                    <div class="card-header bg-white fw-bold text-dark py-2">
                                        <i class="bi bi-calculator me-1 text-success"></i> Calculation Breakdown
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="salary-breakdown-row bg-primary-subtle bg-opacity-25 p-2 rounded mb-2 border border-primary-subtle">
                                            <div>
                                                <div class="fw-bold text-dark"><i class="bi bi-wallet2 text-primary me-1"></i> Old Salary Opening Balance</div>
                                                <small class="text-muted">Previous unpaid/pending balance</small>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold font-monospace fs-6 text-primary">+Rs. {{ number_format($selectedSalary->opening_balance, 2) }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" wire:click="openOpeningBalanceModal({{ $selectedSalary->id }})" title="Edit Old Salary Opening Balance">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                        <div class="salary-breakdown-row">
                                            <span class="text-muted">Basic / Earned Salary ({{ ucfirst($selectedSalary->salary_type) }})</span>
                                            <span class="fw-bold font-monospace">Rs. {{ number_format($selectedSalary->basic_salary, 2) }}</span>
                                        </div>
                                        @if($selectedSalary->overtime_pay > 0)
                                            <div class="salary-breakdown-row">
                                                <span class="text-muted">Overtime Pay</span>
                                                <span class="fw-bold font-monospace text-success">+Rs. {{ number_format($selectedSalary->overtime_pay, 2) }}</span>
                                            </div>
                                        @endif
                                        <div class="salary-breakdown-row">
                                            <span class="text-muted">Allowance</span>
                                            <span class="fw-bold font-monospace text-success">+Rs. {{ number_format($selectedSalary->allowance, 2) }}</span>
                                        </div>
                                        <div class="salary-breakdown-row">
                                            <span class="text-muted">Bonus</span>
                                            <span class="fw-bold font-monospace text-success">+Rs. {{ number_format($selectedSalary->bonus, 2) }}</span>
                                        </div>
                                        <div class="salary-breakdown-row">
                                            <span class="text-muted">Deductions / Advances</span>
                                            <span class="fw-bold font-monospace text-danger">-Rs. {{ number_format($selectedSalary->deductions + $selectedSalary->advance_taken, 2) }}</span>
                                        </div>
                                        <div class="salary-breakdown-row pt-2 border-top border-dark">
                                            <span class="fw-bold text-dark">Net Salary Total</span>
                                            <span class="fw-bold text-primary font-monospace fs-6">Rs. {{ number_format($selectedSalary->net_salary, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Date-Wise Pick Time Logs Option --}}
                        <div class="card border border-primary-subtle bg-light-subtle rounded-3 mb-4">
                            <div class="card-body p-3">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-fingerprint text-primary me-1"></i> Date-Wise Biometric Time Logs
                                    </h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="small text-muted text-nowrap mb-0">Pick Date:</label>
                                        <input type="date" class="form-control form-control-sm" style="width: auto;" wire:model.live="modalLogDate">
                                    </div>
                                </div>

                                @if(count($modalTimeLogs) > 0)
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
                                                @foreach($modalTimeLogs as $log)
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
                                    <div class="text-center py-2 text-muted small">
                                        <i class="bi bi-clock-history me-1"></i> No biometric punch logs recorded for this employee on {{ \Carbon\Carbon::parse($modalLogDate)->format('d M Y') }}.
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 4. Payment History Logs --}}
                        <div class="card border rounded-3 mb-2">
                            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark"><i class="bi bi-receipt me-1 text-success"></i> Payment History</span>
                                @if($selectedSalary->balance_amount > 0)
                                    <button class="btn btn-sm btn-success" wire:click="openPaymentModal({{ $selectedSalary->id }})">
                                        <i class="bi bi-plus-circle me-1"></i> Record Payment
                                    </button>
                                @endif
                            </div>
                            <div class="card-body p-0">
                                @if($selectedSalary->payments->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3">Date</th>
                                                    <th>Amount</th>
                                                    <th>Payment Method</th>
                                                    <th>Reference</th>
                                                    <th class="pe-3">Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedSalary->payments as $pmt)
                                                    <tr>
                                                        <td class="ps-3 font-monospace">{{ $pmt->payment_date ? \Carbon\Carbon::parse($pmt->payment_date)->format('d M Y') : '—' }}</td>
                                                        <td class="fw-bold text-success font-monospace">Rs. {{ number_format($pmt->amount, 2) }}</td>
                                                        <td><span class="badge bg-light text-dark border">{{ $pmt->payment_method }}</span></td>
                                                        <td class="text-muted small">{{ $pmt->reference_no ?: '—' }}</td>
                                                        <td class="pe-3 text-muted small">{{ $pmt->notes ?: '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-3 text-muted small">
                                        No payments recorded yet for this month's salary.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Close</button>
                        @if($selectedSalary->balance_amount > 0)
                            <button type="button" class="btn btn-success px-4" wire:click="openPaymentModal({{ $selectedSalary->id }})">
                                <i class="bi bi-cash-stack me-1"></i> Pay Balance (Rs. {{ number_format($selectedSalary->balance_amount, 2) }})
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 2: Record Payment Modal --}}
    @if($showPaymentModal && $selectedSalary && $selectedEmployee)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header bg-light border-bottom p-3" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">
                                <i class="bi bi-cash-stack text-success me-1"></i> Record Salary Payment
                            </h5>
                            <small class="text-muted">Paying <strong>{{ $selectedEmployee->full_name }}</strong> &bull; Balance: <strong>Rs. {{ number_format($selectedSalary->balance_amount, 2) }}</strong></small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit.prevent="submitPayment">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Payment Amount (LKR)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rs.</span>
                                    <input type="number" step="0.01" min="1" max="{{ $selectedSalary->balance_amount }}" class="form-control form-control-lg fw-bold font-monospace text-success @error('payAmount') is-invalid @enderror" wire:model="payAmount" required>
                                </div>
                                <small class="text-muted">Current remaining balance: Rs. {{ number_format($selectedSalary->balance_amount, 2) }}</small>
                                @error('payAmount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Payment Date</label>
                                    <input type="date" class="form-control @error('payDate') is-invalid @enderror" wire:model="payDate" required>
                                    @error('payDate') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Payment Method</label>
                                    <select class="form-select @error('payMethod') is-invalid @enderror" wire:model="payMethod" required>
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Online">Online</option>
                                    </select>
                                    @error('payMethod') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Reference / Cheque No</label>
                                <input type="text" class="form-control" wire:model="payReference" placeholder="e.g. TRX-123456 or Cheque #">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Payment Notes</label>
                                <textarea class="form-control" rows="2" wire:model="payNotes" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                            <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                            <button type="submit" class="btn btn-success px-4">
                                <span wire:loading.remove wire:target="submitPayment">
                                    <i class="bi bi-check-circle me-1"></i> Confirm &amp; Pay
                                </span>
                                <span wire:loading wire:target="submitPayment">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 3: Old Salary Opening Balance Modal --}}
    @if($showOpeningBalanceModal && $obEmployee)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); z-index: 1065;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header bg-light border-bottom p-3" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">
                                <i class="bi bi-wallet2 text-primary me-1"></i> Old Salary Opening Balance
                            </h5>
                            <small class="text-muted">Employee: <strong>{{ $obEmployee->full_name }}</strong> (Emp #{{ $obEmpCode }}) &bull; Month: <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong></small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeOpeningBalanceModal"></button>
                    </div>
                    <form wire:submit.prevent="saveOpeningBalance">
                        <div class="modal-body p-4">
                            <div class="alert alert-info border-0 rounded-3 py-2 px-3 small mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                The <strong>Old Salary Opening Balance</strong> represents unpaid or pending salary from previous months. It is added to the total payable salary and saved to this employee's profile.
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Old Salary Opening Balance (LKR)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light fw-bold font-monospace">Rs.</span>
                                    <input type="number" step="0.01" min="0" class="form-control fw-bold font-monospace text-primary @error('obAmount') is-invalid @enderror" wire:model="obAmount" placeholder="0.00" autofocus required>
                                </div>
                                <small class="text-muted">Enter previous pending balance (e.g. 50000.00). Enter 0 to clear.</small>
                                @error('obAmount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Employee Name:</span>
                                    <span class="fw-bold text-dark">{{ $obEmployee->full_name }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Department:</span>
                                    <span class="fw-semibold text-dark">{{ $obEmployee->department?->dept_name ?? 'Default Dept' }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>New Old Salary Balance:</span>
                                    <span class="font-monospace fw-bold text-primary">+ Rs. {{ number_format((float) ($obAmount ?: 0), 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                            <button type="button" class="btn btn-secondary" wire:click="closeOpeningBalanceModal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">
                                <span wire:loading.remove wire:target="saveOpeningBalance">
                                    <i class="bi bi-check-circle me-1"></i> Save Opening Balance
                                </span>
                                <span wire:loading wire:target="saveOpeningBalance">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 4: Reset Attendance & Salary Records Modal --}}
    @if($showResetModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 1070;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header bg-danger text-white border-bottom p-3" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Reset Attendance &amp; Salary Records
                            </h5>
                            <small class="text-white-50">Admin Action &bull; Automatic Backup Created First</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeResetModal"></button>
                    </div>
                    <form wire:submit.prevent="executeReset">
                        <div class="modal-body p-4">
                            <div class="alert alert-danger border-0 rounded-3 py-2 px-3 small mb-3">
                                <i class="bi bi-shield-exclamation me-1 fs-6"></i>
                                <strong>Warning:</strong> This will delete biometric attendance punch records, calculated monthly salaries, and payment logs.
                            </div>

                            <div class="alert alert-success border-0 rounded-3 py-2 px-3 small mb-3">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                <strong>Staff Profiles Safe:</strong> Employee master profiles, employee names, fingerprint enrollments, and configured salary rates will <u>NOT</u> be touched.
                            </div>

                            <div class="alert alert-info border-0 rounded-3 py-2 px-3 small mb-3">
                                <i class="bi bi-cloud-arrow-down-fill me-1"></i>
                                <strong>Automatic Backup:</strong> A complete SQL &amp; JSON snapshot is automatically saved to <code>storage/app/backups/</code> before any data is deleted.
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Select Reset Scope</label>
                                <div class="form-check p-3 bg-light rounded-3 border mb-2">
                                    <input class="form-check-input" type="radio" name="resetScope" id="scopeAll" value="all" wire:model.live="resetScope">
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="scopeAll">
                                        <div><i class="bi bi-stars text-danger me-1"></i> Reset ALL Attendance &amp; Salary Records</div>
                                        <small class="text-muted fw-normal">Wipe all punch logs and monthly salaries for a complete fresh start (ideal before 1st of next month).</small>
                                    </label>
                                </div>
                                <div class="form-check p-3 bg-light rounded-3 border mb-2">
                                    <input class="form-check-input" type="radio" name="resetScope" id="scopeMonth" value="month" wire:model.live="resetScope">
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="scopeMonth">
                                        <div><i class="bi bi-calendar-month text-primary me-1"></i> Reset Selected Month Only</div>
                                        <small class="text-muted fw-normal">Wipe attendance punches and salary calculations for month: <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong></small>
                                    </label>
                                </div>
                                <div class="form-check p-3 bg-warning-subtle rounded-3 border border-warning">
                                    <input class="form-check-input" type="checkbox" id="clearObSalary" wire:model="resetClearOpeningBalance">
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="clearObSalary">
                                        <div><i class="bi bi-arrow-counterclockwise text-danger me-1"></i> Also Clear Old Salary Opening Balances to Rs. 0.00</div>
                                        <small class="text-muted fw-normal">Resets all previous/carry-over opening balances to 0.00 so every staff member starts fresh at Rs. 0.00.</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Type <span class="text-danger font-monospace">RESET</span> to confirm:</label>
                                <input type="text" class="form-control font-monospace text-uppercase fw-bold @error('resetConfirmText') is-invalid @enderror" wire:model="resetConfirmText" placeholder="RESET" required autofocus>
                                <small class="text-muted">Type the word <code>RESET</code> in capital letters to confirm.</small>
                                @error('resetConfirmText') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                            <button type="button" class="btn btn-secondary" wire:click="closeResetModal">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="executeReset">
                                    <i class="bi bi-trash3-fill me-1"></i> Confirm &amp; Reset Records
                                </span>
                                <span wire:loading wire:target="executeReset">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Backing up &amp; Resetting...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
