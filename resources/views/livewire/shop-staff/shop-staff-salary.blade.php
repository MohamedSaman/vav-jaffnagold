<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-people-fill text-primary me-2"></i> Shop Staff Salary Management
            </h3>
            <p class="text-muted mb-0">Manage employee daily salaries, collections, and balances</p>
        </div>
        <button class="btn btn-primary" wire:click="openAddModal">
            <i class="bi bi-person-plus-fill me-1"></i> Add New Employee
        </button>
    </div>

    <!-- Employee Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th class="text-end">Daily Salary</th>
                            <th class="text-end">Total Earned</th>
                            <th class="text-end text-success">Total Collected</th>
                            <th class="text-end text-danger">Due Balance</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeeData as $data)
                            <tr>
                                <td class="fw-bold text-dark">{{ $data['name'] }}</td>
                                <td class="text-end">Rs. {{ number_format($data['daily_salary'], 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($data['total_earned'], 2) }}</td>
                                <td class="text-end text-success fw-medium">Rs. {{ number_format($data['total_collected'], 2) }}</td>
                                <td class="text-end text-danger fw-bold">Rs. {{ number_format($data['balance'], 2) }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" title="View Daily Records" wire:click="viewDetails({{ $data['id'] }})">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" title="Payout / Collect" wire:click="openPayoutModal({{ $data['id'] }})">
                                            <i class="bi bi-cash"></i> Payout
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Delete Employee" onclick="confirm('Are you sure you want to delete this employee?') || event.stopImmediatePropagation()" wire:click="deleteEmployee({{ $data['id'] }})">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    No employees found. Click "Add New Employee" to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $employees->links() }}
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    @if($showAddModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i> Add New Employee</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeAddModal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Enter employee name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" wire:model="phone" placeholder="07XXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Daily Salary (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('daily_salary') is-invalid @enderror" wire:model="daily_salary" placeholder="2000">
                            @error('daily_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" rows="2" wire:model="address" placeholder="Residential address"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Opening Due Balance (Rs.)</label>
                            <input type="number" class="form-control" wire:model="opening_balance">
                            <div class="form-text text-muted small">Current amount owed to this employee.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAddModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveEmployee">Save Employee</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Details Modal -->
    @if($showDetailsModal && $selectedEmployee)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white d-flex align-items-center">
                    <h5 class="modal-title mb-0"><i class="bi bi-info-circle me-2"></i> Salary History: {{ $selectedEmployee->name }}</h5>
                    <div class="ms-auto d-flex align-items-center">
                        <label class="me-2 mb-0 small opacity-75">Filter Month:</label>
                        <input type="month" class="form-control form-control-sm border-0 bg-white bg-opacity-10 text-white" wire:model.live="filterMonth" style="width: 160px;">
                        <button type="button" class="btn-close btn-close-white ms-3" wire:click="closeDetailsModal"></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 bg-light border-bottom">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <small class="text-muted d-block mb-1">Daily Salary</small>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-wallet2 text-secondary me-2"></i>
                                            <span class="fw-bold">Rs. {{ number_format($selectedEmployee->daily_salary, 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                                    <div class="card-body p-3">
                                        <small class="text-muted d-block mb-1">Monthly Earned</small>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-check text-primary me-2"></i>
                                            <span class="fw-bold text-primary">Rs. {{ number_format($monthlyEarned, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                                    <div class="card-body p-3">
                                        <small class="text-muted d-block mb-1">Monthly Collected</small>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash-coin text-success me-2"></i>
                                            <span class="fw-bold text-success">Rs. {{ number_format($monthlyCollected, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                                    <div class="card-body p-3">
                                        <small class="text-muted d-block mb-1">Total Balance</small>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-piggy-bank text-danger me-2"></i>
                                            <span class="fw-bold text-danger">Rs. {{ number_format($selectedEmployee->calculateBalance(), 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light border-bottom-0">
                                <tr class="text-uppercase small fw-bold">
                                    <th class="ps-4 py-3">Date</th>
                                    <th class="text-end py-3">Daily Wage</th>
                                    <th class="text-end py-3">Collected</th>
                                    <th class="text-end pe-4 py-3">Net Daily Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Opening Balance Row - Only show if current month is the month of opening or if filtering is not active --}}
                                {{-- For simplicity, let's just show it always if it exists --}}
                                @if($selectedEmployee->opening_balance != 0)
                                <tr class="bg-info-subtle">
                                    <td class="ps-4 fw-bold">
                                        <i class="bi bi-arrow-right-circle me-1 text-info"></i>
                                        BROUGHT FORWARD (Opening Balance)
                                    </td>
                                    <td class="text-end"><span class="text-muted">Rs. 0.00</span></td>
                                    <td class="text-end text-success"><span class="text-muted">Rs. 0.00</span></td>
                                    <td class="text-end pe-4">
                                        <span class="badge rounded-pill bg-danger-subtle text-danger fs-6 px-3">
                                            Rs. {{ number_format($selectedEmployee->opening_balance, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @endif

                                @forelse($dailyRecords as $record)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($record['date'])->format('d M, Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($record['date'])->format('l') }}</small>
                                    </td>
                                    <td class="text-end fw-medium">Rs. {{ number_format($record['earned'], 2) }}</td>
                                    <td class="text-end">
                                        @if($record['collected'] > 0)
                                            <span class="text-success fw-medium">+Rs. {{ number_format($record['collected'], 2) }}</span>
                                        @else
                                            <span class="text-muted">Rs. 0.00</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 fw-bold">
                                        @php $change = $record['earned'] - $record['collected']; @endphp
                                        @if($change > 0)
                                            <span class="badge bg-danger-subtle text-danger px-3">
                                                Rs. {{ number_format($change, 2) }}
                                            </span>
                                        @elseif($change < 0)
                                            <span class="badge bg-primary-subtle text-primary px-3">
                                                Rs. {{ number_format(abs($change), 2) }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted px-3 border">
                                                Rs. 0.00
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                        No salary records found for {{ \Carbon\Carbon::parse($filterMonth)->format('F Y') }}.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if(count($dailyRecords) > 0)
                            <tfoot class="table-light fw-bold border-top">
                                <tr>
                                    <td class="ps-4 py-3">MONTHLY TOTAL ({{ \Carbon\Carbon::parse($filterMonth)->format('M Y') }})</td>
                                    <td class="text-end py-3 text-dark">Rs. {{ number_format($monthlyEarned, 2) }}</td>
                                    <td class="text-end py-3 text-success">Rs. {{ number_format($monthlyCollected, 2) }}</td>
                                    <td class="text-end pe-4 py-3 text-danger">Rs. {{ number_format($monthlyEarned - $monthlyCollected, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Showing records for <strong>{{ \Carbon\Carbon::parse($filterMonth)->format('F Y') }}</strong>
                    </div>
                    <button type="button" class="btn btn-secondary px-4" wire:click="closeDetailsModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Payout Modal -->
    @if($showPayoutModal && $selectedEmployee)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i> Record Payout: {{ $selectedEmployee->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closePayoutModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Outstanding Balance</label>
                        <h4 class="text-danger fw-bold">Rs. {{ number_format($selectedEmployee->calculateBalance(), 2) }}</h4>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payout / Collecting Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" class="form-control @error('payoutAmount') is-invalid @enderror" wire:model="payoutAmount" placeholder="0.00">
                        </div>
                        @error('payoutAmount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('payoutDate') is-invalid @enderror" wire:model="payoutDate">
                        @error('payoutDate') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closePayoutModal">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="savePayout">Confirm Payout</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
