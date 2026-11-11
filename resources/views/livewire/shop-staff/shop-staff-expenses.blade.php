<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-wallet2 text-primary me-2"></i> Daily Expenses
            </h3>
            <p class="text-muted mb-0">Add and track your daily shop expenses</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shop-staff.purchasing-expenses') }}" class="btn btn-outline-primary">
                <i class="bi bi-bag-check me-1"></i> Purchasing Expenses
            </a>
            <button class="btn btn-primary" wire:click="openAddModal">
                <i class="bi bi-plus-lg me-1"></i> Add Expense
            </button>
        </div>
    </div>

    {{-- Summary Cards (Old Look) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <div class="card-body text-center py-3 text-white">
                    <h4 class="fw-bold mb-0">Rs. {{ number_format($todayExpenses, 2) }}</h4>
                    <small class="opacity-75">Today's Expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%);">
                <div class="card-body text-center py-3 text-white">
                    <h4 class="fw-bold mb-0">Rs. {{ number_format($monthExpenses, 2) }}</h4>
                    <small class="opacity-75">This Month Total</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses List Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-journal-text text-primary me-2"></i> Expense History
                    </h5>
                    <p class="text-muted small mb-0">All expenses you have recorded</p>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- Search & Date Picker Calendar --}}
            <div class="row g-2 mb-3">
                <div class="col-md-6 col-lg-7">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search expenses..." wire:model.live.debounce.300ms="search">
                        @if($search)
                            <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-calendar3 text-primary"></i></span>
                        <input type="date" class="form-control" wire:model.live="dateFilter" title="Select date from calendar">
                        @if($dateFilter)
                            <button class="btn btn-outline-secondary" type="button" wire:click="$set('dateFilter', '')" title="Show all dates">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-2 col-lg-2">
                    <button class="btn btn-outline-primary w-100" type="button" wire:click="setToday" title="Show today's expenses">
                        <i class="bi bi-calendar-check me-1"></i> Today
                    </button>
                </div>
            </div>

            {{-- Expenses Table (Old Look) --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d M, Y') }}</td>
                                <td>
                                    <span class="fw-medium">{{ $expense->expense_type }}</span>
                                    @if(\App\Services\PurchasingExpenseService::isPurchasingExpense($expense->expense_type, $expense->description))
                                        <span class="badge bg-info-subtle text-info border border-info ms-1" style="font-size: 10px;">
                                            <i class="bi bi-bag me-0.5"></i> Purchasing
                                        </span>
                                    @elseif(app(\App\Services\HrService::class)->detectSalaryEmployee($expense->expense_type, $expense->description))
                                        <span class="badge bg-success-subtle text-success border border-success ms-1" style="font-size: 10px;">
                                            <i class="bi bi-person-check-fill me-0.5"></i> HR Salary
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ Str::limit($expense->description, 50) ?: '-' }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger">Rs. {{ number_format($expense->amount, 2) }}</span>
                                </td>
                                <td>
                                    @if($expense->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </span>
                                    @elseif($expense->status === 'approved')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i> Approved
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $expense->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No expenses found. Click "Add Expense" to get started.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($expenses, 'hasPages') && $expenses->hasPages())
                <div class="pt-3">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Add Expense Modal --}}
    @if($showAddModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i> Add Daily Expense
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeAddModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="addExpense">
                        <!-- Salary Toggle -->
                        <div class="mb-3">
                            <div class="form-check form-switch p-0 d-flex align-items-center justify-content-between bg-light rounded px-3 py-2 border">
                                <label class="form-check-label fw-bold mb-0" for="salarySwitch">
                                    <i class="bi bi-person-badge-fill me-2 text-primary"></i> Is this a Staff Salary Payout?
                                </label>
                                <input class="form-check-input ms-0 mt-0" type="checkbox" role="switch" id="salarySwitch" wire:model.live="is_salary">
                            </div>
                        </div>

                        {{-- Quick Select Staff Salary Buttons (Always accessible for 1-click "SAMAN SALARY", etc.) --}}
                        <div class="p-2 mb-3 rounded border" style="background-color: #f0fdf4; border-color: #bbf7d0 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="small text-success fw-bold">
                                    <i class="bi bi-people-fill me-1"></i> Quick Select Staff Salary (Auto-Deducts from HR Salary):
                                </div>
                                @if($salary_employee_id)
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" style="font-size: 0.75rem;" wire:click="clearSelectedStaffSalary">
                                        <i class="bi bi-x-circle me-1"></i> Clear Staff
                                    </button>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($salaryEmployees as $emp)
                                    @php
                                        $empSalLabel = strtoupper(trim($emp->full_name)) . ' SALARY';
                                        $isSelectedEmp = ((string) $salary_employee_id === (string) $emp->emp_code) || (strtoupper(trim($expense_type)) === $empSalLabel);
                                    @endphp
                                    <button type="button"
                                            class="btn btn-sm {{ $isSelectedEmp ? 'btn-success text-white fw-bold shadow-sm' : 'btn-outline-success bg-white' }} py-1 px-2"
                                            style="font-size: 0.75rem;"
                                            wire:click="selectStaffSalary('{{ $emp->emp_code }}')">
                                        <i class="bi bi-person-badge me-1"></i> {{ $empSalLabel }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @if(!$is_salary)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0 fw-semibold">Expense Type / Category <span class="text-danger">*</span></label>
                                <span class="badge bg-light text-secondary border">Local Suppliers &amp; Staff Salary</span>
                            </div>

                            {{-- Quick Supplier Buttons for Mano anna, RAMESH ANNA, Sutha anna --}}
                            <div class="p-2 mb-2 bg-light rounded border">
                                <div class="small text-muted mb-1 fw-bold">
                                    <i class="bi bi-bag-check text-primary me-1"></i> Quick Select Local Supplier (Purchasing Expense):
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($registeredSuppliers ?? ['MANO ANNA', 'KANIY MANO', 'MARAN THALAI', 'PRAKAS', 'SATHES DAD', 'SATHES ANNA', 'UDAYAN RING', 'SANKAR ANNA', 'SUTHA ANNA', 'ATHAAN THANGA THANNIY', 'RAMESH ANNA'] as $sup)
                                        <button type="button"
                                                class="btn btn-sm {{ $expense_type === $sup ? 'btn-primary' : 'btn-outline-primary' }} py-0.5 px-2 mb-1"
                                                style="font-size: 0.75rem;"
                                                wire:click="selectSupplierCategory('{{ $sup }}')">
                                            <i class="bi bi-person-check me-1"></i> {{ $sup }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <input type="text" list="expense-types" class="form-control @error('expense_type') is-invalid @enderror"
                                   wire:model.live.debounce.250ms="expense_type" placeholder="Type e.g. SAMAN SALARY or select expense category...">
                            <datalist id="expense-types">
                                @foreach($salaryEmployees as $emp)
                                    <option value="{{ strtoupper(trim($emp->full_name)) }} SALARY">{{ strtoupper(trim($emp->full_name)) }} SALARY (Staff Salary)</option>
                                @endforeach
                                @foreach($registeredSuppliers ?? [] as $sup)
                                    <option value="{{ $sup }}">{{ $sup }} (Local Supplier)</option>
                                @endforeach
                                <option value="Purchasing Expenses">Purchasing Expenses</option>
                                @foreach($dbExpenseTypes as $type)
                                    <option value="{{ $type }}">
                                @endforeach
                            </datalist>
                            @error('expense_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($selectedStaffSnapshot)
                                <div class="text-success small mt-1 fw-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Recognized as <strong>{{ $selectedStaffSnapshot['employee_name'] }} Salary</strong> (will auto-deduct from HR Salary).
                                </div>
                            @elseif(\App\Services\PurchasingExpenseService::isPurchasingExpense($expense_type, $description))
                                <div class="text-success small mt-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Recognized as <strong>Purchasing Expense</strong> (will also appear in Purchasing Expenses).
                                </div>
                            @else
                                <div class="form-text text-muted small mt-1">Select a staff salary above, pick an existing type, or type a new one (e.g., SAMAN SALARY).</div>
                            @endif
                        </div>
                        @else
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Employee <span class="text-danger">*</span></label>
                            <select class="form-select @error('salary_employee_id') is-invalid @enderror" wire:model.live="salary_employee_id">
                                <option value="">Select Employee</option>
                                @foreach($salaryEmployees as $employee)
                                    <option value="{{ $employee->emp_code }}">
                                        #{{ $employee->emp_code }} - {{ $employee->full_name }} ({{ strtoupper(trim($employee->full_name)) }} SALARY)
                                    </option>
                                @endforeach
                            </select>
                            @error('salary_employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        {{-- Staff Salary & Attendance Balance Card (shown whenever a staff member is selected or detected) --}}
                        @if($selectedStaffSnapshot)
                            @php
                                $snap = $selectedStaffSnapshot;
                                $todayHrs = (float) ($snap['hours_worked'] ?? 0);
                                $todaySal = (float) ($snap['today_salary'] ?? 0);
                                $dayFull = (float) ($snap['day_full_salary'] ?? 0);
                                $paidToday = (float) ($snap['paid_today'] ?? 0);
                                $todayRem = (float) ($snap['today_balance'] ?? 0);
                                $prevSaved = (float) ($snap['previous_balance'] ?? 0);
                                $availBal = (float) ($snap['total_available_balance'] ?? 0);
                                $enteredAmt = is_numeric($amount) ? (float) $amount : 0.0;
                                $remainingAfterPay = round($availBal - $enteredAmt, 2);
                            @endphp
                            <div class="rounded-3 p-3 mb-3 border shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #f0fdf4 100%); border-color: #86efac !important;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success text-white px-2 py-1 font-monospace">#{{ $snap['emp_code'] }}</span>
                                        <span class="fw-bold text-dark fs-6">{{ strtoupper($snap['employee_name']) }} SALARY</span>
                                        <span class="badge bg-white text-secondary border small">
                                            Full Day (10h): Rs. {{ number_format($dayFull, 0) }} &bull; Rate: Rs. {{ number_format($snap['hourly_rate'] ?? 0, 0) }}/hr
                                        </span>
                                    </div>
                                    <div class="small text-muted fw-semibold">
                                        <i class="bi bi-calendar-event me-1"></i> {{ \Carbon\Carbon::parse($snap['date'])->format('d M, Y') }}
                                    </div>
                                </div>

                                {{-- 4-Box Balance & Attendance Breakdown --}}
                                <div class="row g-2 text-center mb-2">
                                    <div class="col-6 col-md-3">
                                        <div class="bg-white rounded-2 p-2 border h-100">
                                            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem;">Today Worked</div>
                                            <div class="fw-bold text-primary font-monospace fs-6 mt-1">
                                                {{ number_format($todayHrs, 2) }} hrs
                                            </div>
                                            <div class="text-muted" style="font-size: 0.68rem;">
                                                @if($snap['first_in'])
                                                    {{ $snap['first_in'] }} - {{ $snap['last_out'] ?: 'Live' }}
                                                @else
                                                    No punch on date
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="bg-white rounded-2 p-2 border h-100">
                                            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem;">Today Attendance Salary</div>
                                            <div class="fw-bold text-success font-monospace fs-6 mt-1">
                                                Rs. {{ number_format($todaySal, 2) }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.68rem;">
                                                @if($todayHrs >= 10.0)
                                                    <span class="text-success fw-semibold">Full Day (10h+)</span>
                                                @elseif($todayHrs > 0)
                                                    Pro-rated by hours
                                                @else
                                                    0 hrs worked
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="bg-white rounded-2 p-2 border h-100">
                                            <div class="text-muted text-uppercase fw-bold" style="font-size: 0.68rem;">Saved / Old Balance</div>
                                            <div class="fw-bold {{ $prevSaved >= 0 ? 'text-dark' : 'text-danger' }} font-monospace fs-6 mt-1">
                                                Rs. {{ number_format($prevSaved, 2) }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.68rem;">
                                                {{ $prevSaved >= 0 ? 'Unpaid previous days' : 'Previous advance' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <div class="rounded-2 p-2 border h-100" style="background-color: #dcfce7; border-color: #22c55e !important;">
                                            <div class="text-success-emphasis text-uppercase fw-bold" style="font-size: 0.68rem;">Available Salary Balance</div>
                                            <div class="fw-bold text-success font-monospace fs-6 mt-1">
                                                Rs. {{ number_format($availBal, 2) }}
                                            </div>
                                            <div class="text-success-emphasis" style="font-size: 0.68rem;">
                                                @if($paidToday > 0)
                                                    Paid today: Rs. {{ number_format($paidToday, 0) }}
                                                @else
                                                    Saved + Today Salary
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Fill Amount Buttons --}}
                                <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                                    <span class="small text-muted fw-semibold me-1" style="font-size: 0.74rem;">Quick Amount:</span>
                                    @if($todayRem > 0)
                                        <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size: 0.73rem;"
                                                wire:click="setQuickSalaryAmount({{ $todayRem }})">
                                            Today Salary (Rs. {{ number_format($todayRem, 0) }})
                                        </button>
                                    @endif
                                    @if($availBal > 0 && $availBal != $todayRem)
                                        <button type="button" class="btn btn-sm btn-success text-white py-0 px-2" style="font-size: 0.73rem;"
                                                wire:click="setQuickSalaryAmount({{ $availBal }})">
                                            Full Available Balance (Rs. {{ number_format($availBal, 0) }})
                                        </button>
                                    @endif
                                    @if($prevSaved > 0 && $prevSaved != $availBal && $prevSaved != $todayRem)
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.73rem;"
                                                wire:click="setQuickSalaryAmount({{ $prevSaved }})">
                                            Saved Balance (Rs. {{ number_format($prevSaved, 0) }})
                                        </button>
                                    @endif
                                    @if($dayFull > 0 && $dayFull != $todayRem)
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.73rem;"
                                                wire:click="setQuickSalaryAmount({{ $dayFull }})">
                                            Full Day Rate (Rs. {{ number_format($dayFull, 0) }})
                                        </button>
                                    @endif
                                </div>

                                {{-- Live Deduction & Remaining Saved Balance Preview --}}
                                <div class="mt-2 pt-2 border-top d-flex flex-wrap justify-content-between align-items-center small">
                                    <div>
                                        <span class="text-muted">Available Balance:</span>
                                        <strong class="text-dark font-monospace">Rs. {{ number_format($availBal, 2) }}</strong>
                                        <span class="mx-1 text-muted">&minus;</span>
                                        <span class="text-muted">Giving Now:</span>
                                        <strong class="text-danger font-monospace">Rs. {{ number_format($enteredAmt, 2) }}</strong>
                                    </div>
                                    <div>
                                        @if($remainingAfterPay >= 0)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace">
                                                Saved Balance Remaining: Rs. {{ number_format($remainingAfterPay, 2) }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1 font-monospace">
                                                Advance / Extra Given: Rs. {{ number_format(abs($remainingAfterPay), 2) }} (Deducts from next day)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Amount (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control fw-bold font-monospace @error('amount') is-invalid @enderror" wire:model.live.debounce.150ms="amount" placeholder="0.00">
                                @if($is_salary || $selectedStaffSnapshot)
                                    <div class="form-text text-info small"><i class="bi bi-info-circle me-1"></i> Auto-shows today's attendance salary. Any unpaid balance is saved for next day.</div>
                                @endif
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('expense_date') is-invalid @enderror" wire:model.live="expense_date">
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" rows="2" wire:model="description" placeholder="Brief description of the expense..."></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            This expense will be auto-approved and deducted from cash in hand{{ ($is_salary || $selectedStaffSnapshot) ? ' & staff HR salary balance' : '' }}.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAddModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="addExpense" wire:loading.attr="disabled" wire:target="addExpense">
                        <span wire:loading.remove wire:target="addExpense">
                            <i class="bi bi-check-lg me-1"></i> Add Expense
                        </span>
                        <span wire:loading wire:target="addExpense">
                            <span class="spinner-border spinner-border-sm me-1"></span> Adding...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this expense? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteExpense">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
