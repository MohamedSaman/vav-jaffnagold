<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 rounded-pill fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> Supplier Payouts
                </span>
                <span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill fw-semibold">
                    <i class="bi bi-cash-stack me-1"></i> Shop Cash
                </span>
            </div>
            <h3 class="fw-bold text-dark mt-2 mb-1">
                <i class="bi bi-bag-check text-primary me-2"></i> Purchasing Expenses
            </h3>
            <p class="text-muted mb-0 small">
                Track payments and petty cash given to local suppliers: 
                <strong class="text-primary">Mano anna</strong>, 
                <strong class="text-purple" style="color: #7c3aed;">RAMESH ANNA</strong>, and 
                <strong class="text-teal" style="color: #0d9488;">Sutha anna</strong>.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shop-staff.expenses') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-wallet2 me-1"></i> Daily Expenses
            </a>
            <button class="btn btn-primary" wire:click="openAddModal">
                <i class="bi bi-plus-circle me-1"></i> Add Purchasing Expense
            </button>
        </div>
    </div>

    {{-- Overall KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                <div class="card-body p-3 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase tracking-wider opacity-75 fw-bold" style="font-size: 11px;">Today's Payouts</small>
                        <i class="bi bi-calendar-event fs-5 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">Rs. {{ number_format($todayTotal, 2) }}</h3>
                    <small class="opacity-75" style="font-size: 12px;">Cash given to suppliers today</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #161b97 0%, #0e1268 100%);">
                <div class="card-body p-3 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase tracking-wider opacity-75 fw-bold" style="font-size: 11px;">This Month</small>
                        <i class="bi bi-calendar3 fs-5 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">Rs. {{ number_format($monthTotal, 2) }}</h3>
                    <small class="opacity-75" style="font-size: 12px;">{{ date('F Y') }} total supplier payouts</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);">
                <div class="card-body p-3 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase tracking-wider opacity-75 fw-bold" style="font-size: 11px;">All-Time Total</small>
                        <i class="bi bi-cash-coin fs-5 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">Rs. {{ number_format($allTimeTotal, 2) }}</h3>
                    <small class="opacity-75" style="font-size: 12px;">Cumulative local supplier payouts</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #475569 0%, #334155 100%);">
                <div class="card-body p-3 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase tracking-wider opacity-75 fw-bold" style="font-size: 11px;">Vouchers</small>
                        <i class="bi bi-receipt-cutoff fs-5 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">{{ number_format($totalCount) }}</h3>
                    <small class="opacity-75" style="font-size: 12px;">Total expense vouchers recorded</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Supplier Breakdown Cards (Clickable Quick Filters) --}}
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-people-fill text-primary me-1"></i> Supplier Payout Breakdown (Click to filter)
            </h6>
            @if($supplierFilter !== 'all')
                <button class="btn btn-sm btn-link text-decoration-none text-danger p-0" wire:click="setSupplierFilter('all')">
                    <i class="bi bi-x-circle me-1"></i> Clear Supplier Filter
                </button>
            @endif
        </div>
        <div class="row g-3">
            {{-- Mano anna --}}
            <div class="col-12 col-md-4">
                <div class="card border {{ $supplierFilter === 'Mano anna' ? 'border-primary shadow-sm bg-primary-subtle' : 'border-light-subtle shadow-sm bg-white' }} rounded-3 cursor-pointer transition"
                     style="cursor: pointer;"
                     wire:click="setSupplierFilter('Mano anna')">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-white" 
                                     style="width: 42px; height: 42px; background: #2563eb;">
                                    <i class="bi bi-person-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Mano anna</div>
                                    <small class="text-muted">{{ $manoCount }} Payout records</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary fs-5">Rs. {{ number_format($manoTotal, 2) }}</div>
                                @if($supplierFilter === 'Mano anna')
                                    <span class="badge bg-primary text-white" style="font-size: 10px;">Filtered</span>
                                @else
                                    <small class="text-muted" style="font-size: 11px;">Click to view</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RAMESH ANNA --}}
            <div class="col-12 col-md-4">
                <div class="card border {{ $supplierFilter === 'RAMESH ANNA' ? 'border-purple shadow-sm bg-purple-subtle' : 'border-light-subtle shadow-sm bg-white' }} rounded-3 cursor-pointer transition"
                     style="cursor: pointer; {{ $supplierFilter === 'RAMESH ANNA' ? 'border-color: #7c3aed !important; background-color: #f5f3ff !important;' : '' }}"
                     wire:click="setSupplierFilter('RAMESH ANNA')">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-white" 
                                     style="width: 42px; height: 42px; background: #7c3aed;">
                                    <i class="bi bi-person-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">RAMESH ANNA</div>
                                    <small class="text-muted">{{ $rameshCount }} Payout records</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-5" style="color: #7c3aed;">Rs. {{ number_format($rameshTotal, 2) }}</div>
                                @if($supplierFilter === 'RAMESH ANNA')
                                    <span class="badge text-white" style="background: #7c3aed; font-size: 10px;">Filtered</span>
                                @else
                                    <small class="text-muted" style="font-size: 11px;">Click to view</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sutha anna --}}
            <div class="col-12 col-md-4">
                <div class="card border {{ $supplierFilter === 'Sutha anna' ? 'border-teal shadow-sm bg-teal-subtle' : 'border-light-subtle shadow-sm bg-white' }} rounded-3 cursor-pointer transition"
                     style="cursor: pointer; {{ $supplierFilter === 'Sutha anna' ? 'border-color: #0d9488 !important; background-color: #f0fdfa !important;' : '' }}"
                     wire:click="setSupplierFilter('Sutha anna')">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-white" 
                                     style="width: 42px; height: 42px; background: #0d9488;">
                                    <i class="bi bi-person-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Sutha anna</div>
                                    <small class="text-muted">{{ $suthaCount }} Payout records</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-5" style="color: #0d9488;">Rs. {{ number_format($suthaTotal, 2) }}</div>
                                @if($supplierFilter === 'Sutha anna')
                                    <span class="badge text-white" style="background: #0d9488; font-size: 10px;">Filtered</span>
                                @else
                                    <small class="text-muted" style="font-size: 11px;">Click to view</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Filter & Table Card --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row g-3 align-items-center">
                {{-- Search --}}
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control bg-light border-start-0 ps-0" 
                               placeholder="Search note, supplier, or amount..." 
                               wire:model.live.debounce.300ms="search">
                        @if($search)
                            <button class="btn btn-light border" type="button" wire:click="$set('search', '')">
                                <i class="bi bi-x"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Supplier Select --}}
                <div class="col-6 col-md-3">
                    <select class="form-select" wire:model.live="supplierFilter">
                        <option value="all">All Suppliers (Mano, Ramesh, Sutha, etc.)</option>
                        <option value="Mano anna">Mano anna</option>
                        <option value="RAMESH ANNA">RAMESH ANNA</option>
                        <option value="Sutha anna">Sutha anna</option>
                        <option value="other">Other Suppliers</option>
                    </select>
                </div>

                {{-- Date Filter --}}
                <div class="col-6 col-md-3">
                    <select class="form-select" wire:model.live="dateFilter">
                        <option value="all">All Time History</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month ({{ date('M Y') }})</option>
                        <option value="last_month">Last Month</option>
                        <option value="custom">Custom Date Range...</option>
                    </select>
                </div>

                {{-- Quick Add Button in toolbar --}}
                <div class="col-12 col-md-2 text-md-end">
                    <button class="btn btn-outline-primary btn-sm w-100" wire:click="openAddModal">
                        <i class="bi bi-plus-lg me-1"></i> New Payout
                    </button>
                </div>
            </div>

            {{-- Custom Date Inputs (when selected) --}}
            @if($dateFilter === 'custom')
                <div class="row g-2 mt-2 pt-2 border-top">
                    <div class="col-auto d-flex align-items-center text-muted small fw-semibold">
                        <i class="bi bi-calendar-range me-1"></i> Custom Range:
                    </div>
                    <div class="col-auto">
                        <input type="date" class="form-control form-control-sm" wire:model.live="customStartDate" placeholder="Start Date">
                    </div>
                    <div class="col-auto d-flex align-items-center text-muted small">to</div>
                    <div class="col-auto">
                        <input type="date" class="form-control form-control-sm" wire:model.live="customEndDate" placeholder="End Date">
                    </div>
                </div>
            @endif
        </div>

        <div class="card-body p-0">
            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="ps-3 py-3" style="width: 130px;">Date</th>
                            <th style="width: 180px;">Supplier</th>
                            <th>Description / Purpose</th>
                            <th class="text-end" style="width: 160px;">Amount</th>
                            <th style="width: 140px;">Recorded By</th>
                            <th style="width: 120px;">Status</th>
                            <th class="text-center pe-3" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            @php
                                $text = strtolower($expense->expense_type . ' ' . $expense->description);
                                $badgeStyle = 'bg-secondary';
                                $badgeIcon = 'bi-person';
                                $detectedSupplier = 'Supplier';
                                
                                if (str_contains($text, 'mano')) {
                                    $badgeStyle = 'bg-primary text-white';
                                    $badgeIcon = 'bi-person-check-fill';
                                    $detectedSupplier = 'Mano anna';
                                } elseif (str_contains($text, 'ramesh')) {
                                    $badgeStyle = 'text-white';
                                    $badgeIcon = 'bi-person-check-fill';
                                    $detectedSupplier = 'RAMESH ANNA';
                                } elseif (str_contains($text, 'sutha') || str_contains($text, 'suthu')) {
                                    $badgeStyle = 'text-white';
                                    $badgeIcon = 'bi-person-check-fill';
                                    $detectedSupplier = 'Sutha anna';
                                } else {
                                    $badgeStyle = 'bg-dark text-white';
                                    $detectedSupplier = $expense->expense_type;
                                }
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold text-dark">{{ $expense->expense_date->format('d M, Y') }}</div>
                                    <small class="text-muted">{{ $expense->expense_date->format('D') }}</small>
                                </td>
                                <td>
                                    @if($detectedSupplier === 'RAMESH ANNA')
                                        <span class="badge py-1.5 px-2.5 rounded-pill fw-semibold" style="background-color: #7c3aed; color: #fff;">
                                            <i class="bi {{ $badgeIcon }} me-1"></i> {{ $expense->expense_type }}
                                        </span>
                                    @elseif($detectedSupplier === 'Sutha anna')
                                        <span class="badge py-1.5 px-2.5 rounded-pill fw-semibold" style="background-color: #0d9488; color: #fff;">
                                            <i class="bi {{ $badgeIcon }} me-1"></i> {{ $expense->expense_type }}
                                        </span>
                                    @else
                                        <span class="badge {{ $badgeStyle }} py-1.5 px-2.5 rounded-pill fw-semibold">
                                            <i class="bi {{ $badgeIcon }} me-1"></i> {{ $expense->expense_type }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark fw-medium">{{ $expense->description ?: 'Purchasing Payout' }}</div>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-wallet2 text-success me-1"></i> Deducted from Shop Cash
                                    </small>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold fs-6 text-danger">Rs. {{ number_format($expense->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="small text-muted">
                                        <i class="bi bi-person me-1"></i> {{ $expense->staff->name ?? 'Shop Staff' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i> Approved
                                    </span>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-secondary" wire:click="viewDetails({{ $expense->id }})" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" wire:click="confirmDelete({{ $expense->id }})" title="Delete Expense">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-bag-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        <h6 class="fw-semibold text-dark">No purchasing expenses found</h6>
                                        <p class="small text-muted mb-3">No expenses match your search or filter criteria.</p>
                                        <button class="btn btn-primary btn-sm" wire:click="openAddModal">
                                            <i class="bi bi-plus-circle me-1"></i> Record New Purchasing Expense
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination footer --}}
        @if($expenses->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Showing {{ $expenses->firstItem() }} to {{ $expenses->lastItem() }} of {{ $expenses->total() }} purchasing expenses
                </div>
                <div>
                    {{ $expenses->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Add Purchasing Expense Modal --}}
    @if($showAddModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%);">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-2"></i> Add Purchasing Expense
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeAddModal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="addPurchasingExpense">
                        {{-- Quick Supplier Buttons --}}
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted d-flex justify-content-between align-items-center mb-1">
                                <span><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Select Supplier:</span>
                                <span class="badge bg-light text-secondary border">Local Suppliers</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" 
                                        class="btn btn-sm {{ $supplier_name === 'Mano anna' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="quickSelectSupplier('Mano anna')">
                                    <i class="bi bi-person-fill me-1"></i> Mano anna
                                </button>
                                <button type="button" 
                                        class="btn btn-sm text-white"
                                        style="background-color: {{ $supplier_name === 'RAMESH ANNA' ? '#7c3aed' : '#a78bfa' }}; border: 1px solid #7c3aed;"
                                        wire:click="quickSelectSupplier('RAMESH ANNA')">
                                    <i class="bi bi-person-fill me-1"></i> RAMESH ANNA
                                </button>
                                <button type="button" 
                                        class="btn btn-sm text-white"
                                        style="background-color: {{ $supplier_name === 'Sutha anna' ? '#0d9488' : '#2dd4bf' }}; border: 1px solid #0d9488;"
                                        wire:click="quickSelectSupplier('Sutha anna')">
                                    <i class="bi bi-person-fill me-1"></i> Sutha anna
                                </button>
                            </div>
                        </div>

                        {{-- Supplier Name Input / Datalist --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Supplier Name / Category <span class="text-danger">*</span></label>
                            <input type="text" 
                                   list="purchasing-suppliers" 
                                   class="form-control @error('supplier_name') is-invalid @enderror" 
                                   wire:model="supplier_name" 
                                   placeholder="Select or type supplier name...">
                            <datalist id="purchasing-suppliers">
                                @foreach($registeredSuppliers as $sup)
                                    <option value="{{ $sup }}">
                                @endforeach
                            </datalist>
                            @error('supplier_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Purpose / Category --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Expense Purpose / Reason <span class="text-danger">*</span></label>
                            <select class="form-select @error('purpose') is-invalid @enderror" wire:model="purpose">
                                @foreach($purposeCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount Paid (Rs.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rs.</span>
                                <input type="number" 
                                       step="0.01" 
                                       min="1" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       wire:model="amount" 
                                       placeholder="0.00">
                            </div>
                            @error('amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expense_date') is-invalid @enderror" wire:model="expense_date">
                            @error('expense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description / Notes --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description / Remarks</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      rows="2" 
                                      wire:model="description" 
                                      placeholder="e.g. Gold water/poun bill, order advance, or bill number..."></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Info Alert --}}
                        <div class="alert alert-info py-2 px-3 small mb-0 rounded-2">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            This amount will be deducted directly from <strong>Cash in Hand (Shop Drawer)</strong> and auto-approved.
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="closeAddModal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm px-3" wire:click="addPurchasingExpense" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="addPurchasingExpense">
                            <i class="bi bi-check-lg me-1"></i> Save Payout
                        </span>
                        <span wire:loading wire:target="addPurchasingExpense">
                            <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedExpense)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-receipt me-2 text-primary"></i> Purchasing Expense Voucher #{{ $selectedExpense->id }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeDetailModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center p-3 rounded-3 bg-light mb-3">
                        <small class="text-muted text-uppercase fw-bold">Amount Paid</small>
                        <h2 class="fw-bold text-danger mb-0">Rs. {{ number_format($selectedExpense->amount, 2) }}</h2>
                        <span class="badge bg-success text-white mt-1">
                            <i class="bi bi-check-circle me-1"></i> {{ ucfirst($selectedExpense->status) }}
                        </span>
                    </div>

                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="text-muted" style="width: 140px;">Supplier:</th>
                            <td class="fw-bold text-dark">{{ $selectedExpense->expense_type }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Payment Date:</th>
                            <td class="fw-bold text-dark">{{ $selectedExpense->expense_date->format('d M, Y') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Recorded By:</th>
                            <td>{{ $selectedExpense->staff->name ?? 'Shop Staff' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Payment Source:</th>
                            <td><span class="badge bg-light text-dark border">Cash in Hand (Drawer)</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted align-top">Description:</th>
                            <td class="text-dark">{{ $selectedExpense->description ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Created At:</th>
                            <td class="small text-muted">{{ $selectedExpense->created_at->format('d M, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="closeDetailModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-triangle me-2"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelDelete"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-2">Are you sure you want to delete this purchasing expense?</p>
                    <p class="text-danger small mb-0">
                        <i class="bi bi-info-circle me-1"></i> The expense amount will be automatically restored back to <strong>Cash in Hand</strong>.
                    </p>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm px-3" wire:click="deleteExpense">
                        <i class="bi bi-trash me-1"></i> Delete &amp; Restore Cash
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
