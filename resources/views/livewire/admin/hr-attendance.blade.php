<div class="hr-container py-3">
    <style>
        [x-cloak] { display: none !important; }
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
            padding: 1rem 1.25rem;
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
        .icon-total { background: #e0f2fe; color: #0369a1; }
        .icon-present { background: #dcfce7; color: #166534; }
        .icon-absent { background: #fee2e2; color: #991b1b; }
        .icon-hours { background: #fef3c7; color: #92400e; }
        .icon-salary { background: #ede9fe; color: #5b21b6; }
        .icon-given { background: #dcfce7; color: #15803d; }
        .table-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .nav-tabs-custom {
            border-bottom: 2px solid #e2e8f0;
            gap: 0.5rem;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            padding: 0.65rem 1.25rem;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
        }
        .nav-tabs-custom .nav-link.active {
            color: #4f46e5;
            background: transparent;
            border-bottom: 3px solid #4f46e5;
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
                    <i class="bi bi-clock-history text-primary"></i> Attendance &amp; Biometric Time Logs
                </h1>
                <div class="hr-subtitle">
                    Live fingerprint punch records &amp; daily shift check-in / check-out calculations
                </div>
            </div>
            {{-- Quick Date Selectors --}}
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm {{ $selectedDate === \Carbon\Carbon::now()->toDateString() ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="setDateToday">
                    Today
                </button>
                <button type="button" class="btn btn-sm {{ $selectedDate === \Carbon\Carbon::yesterday()->toDateString() ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="setDateYesterday">
                    Yesterday
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="openResetModal" title="Reset attendance and salary records">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Records
                </button>
            </div>
        </div>

        {{-- Filter Controls --}}
        <div class="row g-2 mt-3 pt-3 border-top align-items-center">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Pick Date:</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                    <input type="date" class="form-control" wire:model.live="selectedDate">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1 fw-semibold">Filter by Employee:</label>
                <select class="form-select form-select-sm" wire:model.live="selectedEmpCode">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->emp_code }}">#{{ $emp->emp_code }} - {{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5 text-end pt-3">
                <span class="badge bg-light text-dark border px-3 py-2 font-monospace fs-6">
                    <i class="bi bi-calendar-event me-1 text-primary"></i> {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Metrics Strip for Selected Date --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-total"><i class="bi bi-people"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-dark">{{ $totalStaff }}</div>
                    <div class="small text-muted fw-semibold">Total Staff</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-present"><i class="bi bi-person-check-fill"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-success">{{ $presentCount }}</div>
                    <div class="small text-muted fw-semibold">Present Today</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-absent"><i class="bi bi-person-x-fill"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-danger">{{ $absentCount }}</div>
                    <div class="small text-muted fw-semibold">Absent Today</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-hours"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-warning-emphasis">{{ number_format($totalHoursToday, 1) }} hrs</div>
                    <div class="small text-muted fw-semibold">Hours Worked</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-salary"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-primary font-monospace">Rs. {{ number_format($totalSalaryToday, 0) }}</div>
                    <div class="small text-muted fw-semibold">Today Salary</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="metric-card">
                <div class="metric-icon icon-given"><i class="bi bi-cash-coin"></i></div>
                <div>
                    <div class="fs-4 fw-bold text-success font-monospace">Rs. {{ number_format($totalSalaryGivenToday, 0) }}</div>
                    <div class="small text-muted fw-semibold">Salary Given</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Switcher --}}
    <ul class="nav nav-tabs nav-tabs-custom mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'summary' ? 'active' : '' }}" wire:click="$set('activeTab', 'summary')">
                <i class="bi bi-table me-1"></i> Daily Attendance Summary
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'raw_logs' ? 'active' : '' }}" wire:click="$set('activeTab', 'raw_logs')">
                <i class="bi bi-fingerprint me-1"></i> Biometric Time Logs (Raw Punches)
            </a>
        </li>
    </ul>

    {{-- TAB 1: Daily Attendance Summary --}}
    @if($activeTab === 'summary')
        <div class="table-card" @if($selectedDate === \Carbon\Carbon::now()->toDateString() && ! $showGiveSalaryModal && ! $showPunchModal) wire:poll.30s @endif>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 70px;">Code</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>First In</th>
                            <th>Last Out</th>
                            <th>Worked Hours</th>
                            <th>Expected</th>
                            <th>Today Salary</th>
                            <th>Status</th>
                            <th class="pe-3">Punch Log</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyAttendance as $item)
                            @php
                                $emp = $item['employee'];
                                $status = $item['status'];
                                $todaySal = (float) ($item['today_salary'] ?? 0);
                                $dayFull = (float) ($item['day_full_salary'] ?? 0);
                                $paidToday = (float) ($item['paid_today'] ?? 0);
                                $balToday = (float) ($item['today_balance'] ?? 0);
                                $payStatus = $item['today_payment_status'] ?? 'unpaid';
                                $hrs = (float) ($item['hours_worked'] ?? 0);
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-dark-subtle text-dark font-monospace fw-bold px-2 py-1">
                                        #{{ $item['emp_code'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $emp->full_name }}</div>
                                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $emp->primary_area }}</small>
                                </td>
                                <td>{{ $emp->department?->dept_name ?? 'Default Dept' }}</td>
                                <td>
                                    @if($item['first_in'])
                                        <span class="badge bg-light text-success font-monospace fs-6 border">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>{{ $item['first_in'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item['last_out'])
                                        <span class="badge bg-light text-danger font-monospace fs-6 border">
                                            <i class="bi bi-box-arrow-right me-1"></i>{{ $item['last_out'] }}
                                        </span>
                                    @elseif($item['first_in'])
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Still In</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="fw-bold {{ $hrs > 0 ? 'text-primary' : 'text-muted' }}">
                                            {{ number_format($hrs, 2) }} hrs
                                        </span>
                                        @if($item['is_live'] ?? false)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle d-inline-flex align-items-center gap-1 py-0 px-1" style="font-size: 0.65rem;" title="Clock is active and ticking live right now">
                                                <span class="spinner-grow spinner-grow-sm" style="width: 5px; height: 5px;"></span> Live
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ number_format($item['expected_hours'], 1) }} hrs</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="fw-bold font-monospace fs-6 {{ $todaySal > 0 ? 'text-success' : 'text-muted' }}">
                                                Rs. {{ number_format($todaySal, 2) }}
                                            </span>
                                            @if($hrs >= 10.0 && $dayFull > 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-1" style="font-size: 0.68rem;" title="10+ hours: Full Day Salary">
                                                    Full Day
                                                </span>
                                            @endif
                                        </div>

                                        @if($dayFull > 0)
                                            <small class="text-muted" style="font-size: 0.72rem;">
                                                Full: Rs. {{ number_format($dayFull, 0) }} / 10h
                                            </small>
                                        @endif

                                        @if((float)($item['total_available_balance'] ?? 0) > 0 && (float)($item['previous_balance'] ?? 0) != 0)
                                            <small class="text-primary fw-semibold font-monospace" style="font-size: 0.72rem;">
                                                Avail Bal: Rs. {{ number_format((float)$item['total_available_balance'], 0) }}
                                            </small>
                                        @endif

                                        {{-- Payment badge and action --}}
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            @if($payStatus === 'paid' && $paidToday > 0)
                                                <span class="badge bg-success text-white py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;" title="Given: Rs. {{ number_format($paidToday, 2) }}">
                                                    <i class="bi bi-check2-circle"></i> Given Rs. {{ number_format($paidToday, 0) }}
                                                </span>
                                            @elseif($payStatus === 'partial' && $paidToday > 0)
                                                <span class="badge bg-warning text-dark py-1 px-2" style="font-size: 0.72rem;">
                                                    Paid Rs. {{ number_format($paidToday, 0) }}
                                                </span>
                                                <button type="button" 
                                                    class="btn btn-sm btn-outline-success py-0 px-2 fw-semibold" 
                                                    style="font-size: 0.72rem; border-radius: 6px;"
                                                    wire:click="openGiveSalaryModal('{{ $item['emp_code'] }}', '{{ $item['date'] }}')" 
                                                    wire:loading.attr="disabled"
                                                    title="Give remaining balance Rs. {{ number_format($balToday, 2) }}">
                                                    <span wire:loading.remove wire:target="openGiveSalaryModal('{{ $item['emp_code'] }}', '{{ $item['date'] }}')">
                                                        Pay Bal
                                                    </span>
                                                    <span wire:loading wire:target="openGiveSalaryModal('{{ $item['emp_code'] }}', '{{ $item['date'] }}')">
                                                        <span class="spinner-border spinner-border-sm" role="status" style="width: 0.7rem; height: 0.7rem;"></span>
                                                    </span>
                                                </button>
                                            @elseif($todaySal > 0 || (float)($item['total_available_balance'] ?? 0) > 0)
                                                <button type="button" 
                                                    class="btn btn-sm btn-success py-0 px-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-1" 
                                                    style="font-size: 0.74rem; border-radius: 6px;"
                                                    wire:click="openGiveSalaryModal('{{ $item['emp_code'] }}', '{{ $item['date'] }}')" 
                                                    wire:loading.attr="disabled"
                                                    title="Give Today Salary to {{ $emp->full_name }}">
                                                    <span wire:loading.remove wire:target="openGiveSalaryModal('{{ $item['emp_code'] }}', '{{ $item['date'] }}')">
                                                        <i class="bi bi-cash-stack"></i> Give Salary
                                                    </span>
                                                    <span wire:loading wire:target="openGiveSalaryModal('{{ $item['emp_code'] }}', '{{ $item['date'] }}')">
                                                        <span class="spinner-border spinner-border-sm" role="status" style="width: 0.75rem; height: 0.75rem;"></span>
                                                    </span>
                                                </button>
                                            @else
                                                <span class="text-muted small" style="font-size: 0.72rem;">—</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($status === 'full_day')
                                        <span class="badge bg-success px-2 py-1 text-white"><i class="bi bi-check-circle-fill me-1"></i>Full Day (10h+)</span>
                                    @elseif($status === 'present')
                                        <span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Present</span>
                                    @elseif($status === 'checked_in')
                                        <span class="badge bg-info-subtle text-info px-2 py-1"><i class="bi bi-play-circle me-1"></i>Checked In</span>
                                    @elseif($status === 'half_day')
                                        <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1"><i class="bi bi-clock me-1"></i>Half Day</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Absent</span>
                                    @endif
                                </td>
                                <td class="pe-3">
                                    @php
                                        $isAdmin = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff());
                                    @endphp
                                    @if(count($item['punches']) > 0)
                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                            @foreach($item['punches'] as $p)
                                                @if($isAdmin)
                                                    <button type="button" 
                                                        class="btn btn-sm py-0 px-2 font-monospace {{ $p['type'] === 'IN' ? 'btn-outline-success border-success' : 'btn-outline-warning text-dark border-warning' }} punch-chip" 
                                                        style="font-size: 0.75rem; border-radius: 6px; cursor: pointer;"
                                                        wire:click="openPunchModal('{{ $item['emp_code'] }}', '{{ $item['date'] }}')" 
                                                        title="Click to edit punch time: {{ $p['time'] }} ({{ $p['type'] }})">
                                                        {{ $p['time'] }} <span class="badge {{ $p['type'] === 'IN' ? 'bg-success' : 'bg-warning text-dark' }} px-1 ms-1" style="font-size: 0.65rem;">{{ $p['type'] }}</span>
                                                    </button>
                                                @else
                                                    <span class="badge py-1 px-2 font-monospace {{ $p['type'] === 'IN' ? 'bg-success-subtle text-success border border-success' : 'bg-warning-subtle text-warning-emphasis border border-warning' }}" 
                                                        style="font-size: 0.75rem; border-radius: 6px;"
                                                        title="Punch time: {{ $p['time'] }} ({{ $p['type'] }})">
                                                        {{ $p['time'] }} <span class="badge {{ $p['type'] === 'IN' ? 'bg-success text-white' : 'bg-warning text-dark' }} px-1 ms-1" style="font-size: 0.65rem;">{{ $p['type'] }}</span>
                                                    </span>
                                                @endif
                                            @endforeach
                                            @if($isAdmin)
                                                <button type="button" 
                                                    class="btn btn-sm btn-light border py-0 px-1 text-primary shadow-sm" 
                                                    style="font-size: 0.75rem;" 
                                                    wire:click="openPunchModal('{{ $item['emp_code'] }}', '{{ $item['date'] }}')" 
                                                    title="Edit / Add Punch Times">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="text-muted small">No punch</span>
                                            @if($isAdmin)
                                                <button type="button" 
                                                    class="btn btn-sm btn-outline-primary py-0 px-1" 
                                                    style="font-size: 0.72rem;" 
                                                    wire:click="openPunchModal('{{ $item['emp_code'] }}', '{{ $item['date'] }}')" 
                                                    title="Add Punch Time">
                                                    <i class="bi bi-plus"></i> Add
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    No employee attendance records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TAB 2: Biometric Raw Punches & Time Logs --}}
    @if($activeTab === 'raw_logs')
        <div class="table-card">
            <div class="p-3 bg-light border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-fingerprint text-primary fs-5"></i>
                    <span>Raw Biometric Punch Records from ZKBio Device</span>
                </div>
                {{-- Date Range Picker for Logs --}}
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted text-nowrap mb-0">From:</label>
                    <input type="date" class="form-control form-control-sm" wire:model.live="rawLogStartDate">
                    <label class="small text-muted text-nowrap mb-0">To:</label>
                    <input type="date" class="form-control form-control-sm" wire:model.live="rawLogEndDate">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Punch ID</th>
                            <th>Punch Time</th>
                            <th>Emp Code</th>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Device / Terminal</th>
                            <th>Area</th>
                            <th>Verification</th>
                            <th class="pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rawLogs as $log)
                            <tr>
                                <td class="ps-3 font-monospace text-muted">#{{ $log->id }}</td>
                                <td class="fw-bold font-monospace text-primary">
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($log->punch_time)->format('Y-m-d H:i:s') }}
                                </td>
                                <td>
                                    <span class="badge bg-dark-subtle text-dark font-monospace fw-bold">#{{ $log->emp_code }}</span>
                                </td>
                                <td class="fw-semibold text-dark">
                                    {{ $log->first_name ? $log->first_name . ' ' . ($log->last_name !== $log->first_name ? $log->last_name : '') : 'Employee #' . $log->emp_code }}
                                </td>
                                <td>{{ $log->dept_name ?? '—' }}</td>
                                <td>{{ $log->terminal_alias ?: ('Terminal #' . $log->terminal_id) }}</td>
                                <td>{{ $log->area_alias ?: 'Main Shop' }}</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info-emphasis">
                                        <i class="bi bi-fingerprint me-1"></i>{{ $log->verify_type == 1 ? 'Fingerprint' : 'Biometric' }}
                                    </span>
                                </td>
                                <td class="pe-3">
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-check2 me-1"></i>Synced
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-clock-history fs-1 d-block mb-2 text-secondary"></i>
                                    No punch logs recorded between {{ $rawLogStartDate }} and {{ $rawLogEndDate }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- MODAL: Edit Punch Logs & Working Sessions (Auto Calculate Working Time) --}}
    @if($showPunchModal && auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff()))
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header bg-light border-bottom p-3" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">
                                <i class="bi bi-clock-history text-primary me-1"></i> Edit Punch Logs &amp; Working Sessions
                            </h5>
                            <small class="text-muted">
                                <strong>{{ $punchModalEmployee?->full_name }}</strong> (Emp #{{ $punchModalEmpCode }}) &bull; Date: <strong>{{ \Carbon\Carbon::parse($punchModalDate)->format('l, d M Y') }}</strong>
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closePunchModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        @if (session()->has('punch_success'))
                            <div class="alert alert-success alert-dismissible fade show py-2 px-3 small d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div>{{ session('punch_success') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Working Hours Calculation Summary --}}
                        <div class="card border border-success-subtle bg-success-subtle bg-opacity-10 rounded-3 mb-4">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-bold text-dark">
                                        <i class="bi bi-stopwatch text-success me-1"></i> Calculated Daily Working Time
                                    </div>
                                    <div class="fs-4 fw-bold text-success font-monospace">
                                        {{ number_format($punchModalTotalHours, 2) }} hrs
                                    </div>
                                </div>
                                <div class="small text-muted mb-2">
                                    Working time is automatically calculated by pairing alternating <strong>IN</strong> and <strong>OUT</strong> punches:
                                </div>

                                {{-- Session Breakdown List --}}
                                @if(count($punchModalSessions) > 0)
                                    <div class="bg-white rounded border p-2">
                                        @foreach($punchModalSessions as $sess)
                                            <div class="d-flex justify-content-between align-items-center py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                <div>
                                                    <span class="badge bg-light text-dark border me-1">Session {{ $sess['session'] }}</span>
                                                    <strong class="text-success font-monospace">{{ $sess['in_time'] }} IN</strong>
                                                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                                    @if($sess['is_completed'])
                                                        <strong class="text-danger font-monospace">{{ $sess['out_time'] }} OUT</strong>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning-emphasis">Currently Clocked IN</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($sess['is_completed'])
                                                        <span class="fw-bold text-dark font-monospace">{{ $sess['duration_minutes'] }} mins</span>
                                                        <small class="text-muted">({{ number_format($sess['duration_hours'], 2) }} hrs)</small>
                                                    @else
                                                        <span class="text-muted small">Running</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted small text-center py-2 bg-white rounded border">
                                        No punch logs found for this date.
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Editable Punches List --}}
                        <div class="card border rounded-3 mb-4">
                            <div class="card-header bg-white py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-list-check me-1 text-primary"></i> Registered Punches on this Date</span>
                                <span class="badge bg-primary">{{ count($punchModalList) }} Punches</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3" style="width: 50px;">#</th>
                                            <th>Type</th>
                                            <th>Assigned Session</th>
                                            <th style="width: 220px;">Punch Time (HH:MM:SS)</th>
                                            <th class="text-end pe-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($punchModalList as $index => $p)
                                            <tr>
                                                <td class="ps-3 font-monospace text-muted">{{ $index + 1 }}</td>
                                                <td>
                                                    <span class="badge {{ $p['type'] === 'IN' ? 'bg-success' : 'bg-warning text-dark' }} px-2 py-1 font-monospace">
                                                        {{ $p['type'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="small text-muted">Session {{ $p['session'] }} ({{ $p['type'] === 'IN' ? 'Check In' : 'Check Out' }})</span>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="time" step="1" class="form-control font-monospace fw-bold" wire:model.defer="punchModalList.{{ $index }}.time">
                                                    </div>
                                                </td>
                                                <td class="text-end pe-3">
                                                    @if(!empty($p['id']))
                                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deletePunch({{ $p['id'] }})" title="Delete Punch Record">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    No punches recorded for this employee on this date.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Add Missing Punch Form --}}
                        <div class="card border border-primary-subtle bg-light rounded-3">
                            <div class="card-body p-3">
                                <div class="fw-bold text-dark mb-2 small">
                                    <i class="bi bi-plus-circle text-primary me-1"></i> Add Missing Punch Time
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-sm-5 col-8">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="bi bi-clock"></i></span>
                                            <input type="time" step="1" class="form-control font-monospace fw-bold @error('newPunchTimeInput') is-invalid @enderror" wire:model="newPunchTimeInput" placeholder="HH:MM">
                                        </div>
                                        @error('newPunchTimeInput') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-sm-4 col-4">
                                        <button type="button" class="btn btn-sm btn-primary w-100" wire:click="addPunch" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="addPunch">
                                                <i class="bi bi-plus-lg me-1"></i> Add Punch
                                            </span>
                                            <span wire:loading wire:target="addPunch">
                                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Adding...
                                            </span>
                                        </button>
                                    </div>
                                    <div class="col-sm-3 col-12 text-sm-end">
                                        <small class="text-muted">Adds punch to device log</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                        <button type="button" class="btn btn-secondary" wire:click="closePunchModal">Cancel</button>
                        <button type="button" class="btn btn-success px-4" wire:click="saveAllPunches">
                            <span wire:loading.remove wire:target="saveAllPunches">
                                <i class="bi bi-check-circle me-1"></i> Save &amp; Recalculate Working Hours
                            </span>
                            <span wire:loading wire:target="saveAllPunches">
                                <span class="spinner-border spinner-border-sm me-1"></span> Recalculating...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: Give Today's Salary Modal --}}
    @if($showGiveSalaryModal && $giveSalaryEmployee)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0, 0, 0, 0.5); z-index: 1060;" wire:click.self="closeGiveSalaryModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                    <div class="modal-header bg-light border-bottom p-3" style="border-top-left-radius: 14px; border-top-right-radius: 14px;">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-cash-stack text-success fs-5"></i>
                                <span>Give Salary — {{ $giveSalaryEmployee->full_name }}</span>
                            </h5>
                            <small class="text-muted">
                                Employee #{{ $giveSalaryEmpCode }} &bull; {{ $giveSalaryEmployee->department?->dept_name ?? 'Default Dept' }} &bull; Date: <strong>{{ \Carbon\Carbon::parse($giveSalaryDate)->format('D, d M Y') }}</strong>
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeGiveSalaryModal"></button>
                    </div>

                    <form wire:submit.prevent="submitGiveSalary">
                        <div class="modal-body p-4">
                            {{-- Salary & Hours Snapshot Box --}}
                            <div class="bg-light border rounded-3 p-3 mb-3">
                                <div class="row g-2 text-center">
                                    <div class="col-4 border-end">
                                        <div class="small text-muted fw-semibold text-uppercase" style="font-size: 0.7rem;">Worked Hours</div>
                                        <div class="fs-5 fw-bold text-primary font-monospace mt-1">
                                            {{ number_format($giveSalaryHoursWorked, 2) }} hrs
                                        </div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="small text-muted fw-semibold text-uppercase" style="font-size: 0.7rem;">Day Full Rate</div>
                                        <div class="fs-5 fw-bold text-dark font-monospace mt-1">
                                            Rs. {{ number_format($giveSalaryDayFull, 0) }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.68rem;">(10h full day)</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted fw-semibold text-uppercase" style="font-size: 0.7rem;">Today Salary</div>
                                        <div class="fs-5 fw-bold text-success font-monospace mt-1">
                                            Rs. {{ number_format($giveSalaryCalculated, 2) }}
                                        </div>
                                        @if($giveSalaryHoursWorked >= 10.0)
                                            <span class="badge bg-success-subtle text-success" style="font-size: 0.65rem;">Full Day (10h+)</span>
                                        @endif
                                    </div>
                                </div>

                                @if($giveSalaryAlreadyPaid > 0 || $giveSalaryPreviousBalance != 0 || $giveSalaryTotalAvailableBalance > 0)
                                    <div class="mt-3 pt-2 border-top small">
                                        @if($giveSalaryPreviousBalance != 0)
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-muted">Previous Saved Balance:</span>
                                                <span class="fw-bold text-dark font-monospace">Rs. {{ number_format($giveSalaryPreviousBalance, 2) }}</span>
                                            </div>
                                        @endif
                                        @if($giveSalaryAlreadyPaid > 0)
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-muted">Already Given Today:</span>
                                                <span class="fw-bold text-dark font-monospace">Rs. {{ number_format($giveSalaryAlreadyPaid, 2) }}</span>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center p-2 rounded mt-1" style="background-color: #dcfce7;">
                                            <span class="fw-bold text-success-emphasis">Total Available Salary Balance:</span>
                                            <span class="fw-bold text-success font-monospace fs-6">Rs. {{ number_format($giveSalaryTotalAvailableBalance, 2) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Payment Amount Input --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Amount to Give (LKR)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rs.</span>
                                    <input type="number" step="0.01" min="1" 
                                        class="form-control form-control-lg fw-bold font-monospace text-success @error('giveSalaryAmount') is-invalid @enderror" 
                                        wire:model="giveSalaryAmount" required>
                                </div>
                                @error('giveSalaryAmount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                {{-- Quick Amount Buttons --}}
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @if($giveSalaryTotalAvailableBalance > 0)
                                        <button type="button" class="btn btn-sm btn-success text-white py-1 px-2" style="font-size: 0.75rem;" 
                                            wire:click="setQuickGiveAmount({{ $giveSalaryTotalAvailableBalance }})">
                                            Available Balance (Rs. {{ number_format($giveSalaryTotalAvailableBalance, 0) }})
                                        </button>
                                    @endif
                                    @if($giveSalaryBalance > 0 && $giveSalaryBalance != $giveSalaryCalculated && $giveSalaryBalance != $giveSalaryTotalAvailableBalance)
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.75rem;" 
                                            wire:click="setQuickGiveAmount({{ $giveSalaryBalance }})">
                                            Today Remaining (Rs. {{ number_format($giveSalaryBalance, 0) }})
                                        </button>
                                    @endif
                                    @if($giveSalaryCalculated > 0 && $giveSalaryCalculated != $giveSalaryTotalAvailableBalance)
                                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.75rem;" 
                                            wire:click="setQuickGiveAmount({{ $giveSalaryCalculated }})">
                                            Today Salary (Rs. {{ number_format($giveSalaryCalculated, 0) }})
                                        </button>
                                    @endif
                                    @if($giveSalaryDayFull > 0 && $giveSalaryDayFull != $giveSalaryCalculated)
                                        <button type="button" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 0.75rem;" 
                                            wire:click="setQuickGiveAmount({{ $giveSalaryDayFull }})">
                                            Full Day (Rs. {{ number_format($giveSalaryDayFull, 0) }})
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Payment Method & Ref --}}
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Payment Method</label>
                                    <select class="form-select form-select-sm" wire:model="giveSalaryPaymentMethod" required>
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Online">Online</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Reference / Cheque #</label>
                                    <input type="text" class="form-control form-control-sm" wire:model="giveSalaryReference" placeholder="Optional ref">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Payment Notes</label>
                                <input type="text" class="form-control form-control-sm" wire:model="giveSalaryNotes" placeholder="Payment notes...">
                            </div>

                            {{-- Past Payments on this Date if any --}}
                            @if(count($giveSalaryPastPayments) > 0)
                                <div class="mt-3 pt-3 border-top">
                                    <div class="small fw-semibold text-muted mb-2">Previous Payments Recorded for {{ $giveSalaryDate }}:</div>
                                    <div class="list-group list-group-flush border rounded-2 small">
                                        @foreach($giveSalaryPastPayments as $pmt)
                                            @php
                                                $pmtMethod = is_array($pmt) ? ($pmt['payment_method'] ?? 'Cash') : ($pmt->payment_method ?? 'Cash');
                                                $pmtRef = is_array($pmt) ? ($pmt['reference_no'] ?? '') : ($pmt->reference_no ?? '');
                                                $pmtAmount = is_array($pmt) ? ($pmt['amount'] ?? 0) : ($pmt->amount ?? 0);
                                            @endphp
                                            <div class="list-group-item d-flex justify-content-between align-items-center py-1 px-2">
                                                <span>{{ $pmtMethod }} {{ !empty($pmtRef) ? '(' . $pmtRef . ')' : '' }}</span>
                                                <span class="fw-bold font-monospace text-success">Rs. {{ number_format($pmtAmount, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                            <button type="button" class="btn btn-secondary" wire:click="closeGiveSalaryModal">Cancel</button>
                            <button type="submit" class="btn btn-success px-4 fw-semibold" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submitGiveSalary">
                                    <i class="bi bi-cash-stack me-1"></i> Confirm &amp; Give Salary
                                </span>
                                <span wire:loading wire:target="submitGiveSalary">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: Reset Attendance & Salary Records Modal --}}
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
                                    <input class="form-check-input" type="radio" name="resetScope" id="scopeAllAttendance" value="all" wire:model.live="resetScope">
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="scopeAllAttendance">
                                        <div><i class="bi bi-stars text-danger me-1"></i> Reset ALL Attendance &amp; Salary Records</div>
                                        <small class="text-muted fw-normal">Wipe all punch logs and monthly salaries for a complete fresh start (ideal before 1st of next month).</small>
                                    </label>
                                </div>
                                <div class="form-check p-3 bg-light rounded-3 border mb-2">
                                    <input class="form-check-input" type="radio" name="resetScope" id="scopeMonthAttendance" value="month" wire:model.live="resetScope">
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="scopeMonthAttendance">
                                        <div><i class="bi bi-calendar-month text-primary me-1"></i> Reset Selected Month Only</div>
                                        <small class="text-muted fw-normal">Wipe attendance punches and salary calculations for month: <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('F Y') }}</strong></small>
                                    </label>
                                </div>
                                <div class="form-check p-3 bg-warning-subtle rounded-3 border border-warning">
                                    <input class="form-check-input" type="checkbox" id="clearObAttendance" wire:model="resetClearOpeningBalance">
                                    <label class="form-check-label fw-semibold text-dark ms-2" for="clearObAttendance">
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
