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

    {{-- Summary Cards --}}
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
            {{-- Search --}}
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search expenses..." wire:model.live="search">
                    </div>
                </div>
            </div>

            {{-- Expenses Table --}}
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

        </div>
    </div>

    {{-- Add Expense Modal --}}
    @if($showAddModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
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
                                    <i class="bi bi-person-badge-fill me-2 text-primary"></i> Is this a Salary Payout?
                                </label>
                                <input class="form-check-input ms-0 mt-0" type="checkbox" role="switch" id="salarySwitch" wire:model.live="is_salary">
                            </div>
                        </div>

                        @if(!$is_salary)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0 fw-semibold">Expense Type / Category <span class="text-danger">*</span></label>
                                <span class="badge bg-light text-secondary border">Local Suppliers</span>
                            </div>

                            {{-- Quick Supplier Buttons for Mano anna, RAMESH ANNA, Sutha anna --}}
                            <div class="p-2 mb-2 bg-light rounded border">
                                <div class="small text-muted mb-1 fw-bold">
                                    <i class="bi bi-bag-check text-primary me-1"></i> Quick Select Local Supplier (Purchasing Expense):
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" 
                                            class="btn btn-sm {{ $expense_type === 'Mano anna' ? 'btn-primary' : 'btn-outline-primary' }} py-0.5 px-2"
                                            wire:click="selectSupplierCategory('Mano anna')">
                                        <i class="bi bi-person-check me-1"></i> Mano anna
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm text-white py-0.5 px-2"
                                            style="background-color: {{ $expense_type === 'RAMESH ANNA' ? '#7c3aed' : '#a78bfa' }};"
                                            wire:click="selectSupplierCategory('RAMESH ANNA')">
                                        <i class="bi bi-person-check me-1"></i> RAMESH ANNA
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm text-white py-0.5 px-2"
                                            style="background-color: {{ $expense_type === 'Sutha anna' ? '#0d9488' : '#2dd4bf' }};"
                                            wire:click="selectSupplierCategory('Sutha anna')">
                                        <i class="bi bi-person-check me-1"></i> Sutha anna
                                    </button>
                                </div>
                            </div>

                            <input type="text" list="expense-types" class="form-control @error('expense_type') is-invalid @enderror" 
                                   wire:model="expense_type" placeholder="Type or select expense category...">
                            <datalist id="expense-types">
                                <option value="Mano anna">Mano anna (Local Supplier)</option>
                                <option value="RAMESH ANNA">RAMESH ANNA (Local Supplier)</option>
                                <option value="Sutha anna">Sutha anna (Local Supplier)</option>
                                <option value="Purchasing Expenses">Purchasing Expenses</option>
                                @foreach($dbExpenseTypes as $type)
                                    <option value="{{ $type }}">
                                @endforeach
                            </datalist>
                            @error('expense_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(\App\Services\PurchasingExpenseService::isPurchasingExpense($expense_type, $description))
                                <div class="text-success small mt-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Recognized as <strong>Purchasing Expense</strong> (will also appear in Purchasing Expenses).
                                </div>
                            @else
                                <div class="form-text text-muted small mt-1">Select an existing type or type a new one to save it.</div>
                            @endif
                        </div>
                        @else
                        <div class="mb-3">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select class="form-select @error('salary_employee_id') is-invalid @enderror" wire:model="salary_employee_id">
                                <option value="">Select Employee</option>
                                @foreach($salaryEmployees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} (Rs. {{ number_format($employee->daily_salary, 0) }}/day)</option>
                                @endforeach
                            </select>
                            @error('salary_employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" wire:model="amount" placeholder="0.00">
                            @if($is_salary)
                                <div class="form-text text-info small"><i class="bi bi-info-circle me-1"></i> Enter 0 if they attended but did not collect salary today.</div>
                            @endif
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expense_date') is-invalid @enderror" wire:model="expense_date">
                            @error('expense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" rows="3" wire:model="description" placeholder="Brief description of the expense..."></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            This expense will be auto-approved and deducted from cash in hand.
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
