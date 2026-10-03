<div class="container-fluid py-4 min-vh-100 bg-light-soft">
    {{-- Header Section --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-receipt-cutoff text-success me-2"></i> Supplier Receipt Hub
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Supplier Payments</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="d-inline-flex align-items-center bg-white p-2 rounded-3 shadow-sm border">
                <div class="pe-3 border-end me-3 text-start">
                    <small class="text-muted d-block lh-1 mb-1">Total Purchases</small>
                    <span class="fw-bold text-dark">LKR {{ number_format($groupedPayments->sum('total_purchase_amount'), 2) }}</span>
                </div>
                <div class="text-start">
                    <small class="text-muted d-block lh-1 mb-1">Settled Cash</small>
                    <span class="fw-bold text-success">LKR {{ number_format($groupedPayments->sum('total_cash_paid'), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fw-bold text-dark-50 small mb-2 text-uppercase">
                        <i class="bi bi-search me-1"></i> Supplier Lookup
                    </label>
                    <div class="input-group input-group-modern">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-building text-muted"></i>
                        </span>
                        <input 
                            type="text" 
                            class="form-control border-start-0 ps-0" 
                            placeholder="Enter supplier name..." 
                            wire:model.live.debounce.300ms="filterSupplier"
                        >
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-bold text-dark-50 small mb-2 text-uppercase">
                        <i class="bi bi-calendar-event me-1"></i> Start Date
                    </label>
                    <input 
                        type="date" 
                        class="form-control form-control-modern" 
                        wire:model.live="filterDateFrom"
                    >
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-bold text-dark-50 small mb-2 text-uppercase">
                        <i class="bi bi-calendar-check me-1"></i> End Date
                    </label>
                    <input 
                        type="date" 
                        class="form-control form-control-modern" 
                        wire:model.live="filterDateTo"
                    >
                </div>
                <div class="col-lg-2 col-md-6 text-end">
                    @if($filterSupplier || $filterDateFrom || $filterDateTo)
                    <button class="btn btn-soft-danger w-100 fw-bold py-2" wire:click="clearFilters">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </button>
                    @else
                    <div class="btn btn-soft-primary w-100 disabled border-0 py-2">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table Section --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-list-ul text-primary me-2"></i> Purchase Records
            </h5>
            <div class="d-flex align-items-center">
                <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">
                    {{ $groupedPayments->total() }} Entries
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-soft text-dark-50 small text-uppercase fw-bold letter-spacing-1">
                        <tr>
                            <th class="ps-4 py-3" style="width: 60px;">ID</th>
                            <th>Supplier & Entity</th>
                            <th class="text-center">Arrival Date</th>
                            <th class="text-center">Units</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-end">Settled Cash</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($groupedPayments as $index => $group)
                        <tr>
                            <td class="ps-4 text-muted small">#{{ $groupedPayments->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-soft-primary text-primary rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-building fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $group->supplier_name }}</div>
                                        <div class="text-muted small">ID: {{ $group->supplier_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold text-dark">{{ date('d M Y', strtotime($group->received_date)) }}</div>
                                <div class="text-muted small">{{ date('l', strtotime($group->received_date)) }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2 py-1">{{ $group->order_count }} Orders</span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rs. {{ number_format($group->total_purchase_amount, 2) }}
                            </td>
                            <td class="text-end">
                                @if($group->total_cash_paid > 0)
                                <div class="fw-bold text-success">Rs. {{ number_format($group->total_cash_paid, 2) }}</div>
                                @else
                                <div class="text-muted small">Unsettled</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($group->total_cash_paid >= $group->total_purchase_amount)
                                    <span class="badge bg-soft-success text-success px-3 py-2 rounded-pill d-inline-flex align-items-center">
                                        <i class="bi bi-check-circle-fill me-1"></i> Fully Paid
                                    </span>
                                @elseif($group->total_cash_paid > 0)
                                    <span class="badge bg-soft-warning text-warning px-3 py-2 rounded-pill d-inline-flex align-items-center">
                                        <i class="bi bi-clock-history me-1"></i> Partial
                                    </span>
                                @else
                                    <span class="badge bg-soft-danger text-danger px-3 py-2 rounded-pill d-inline-flex align-items-center">
                                        <i class="bi bi-exclamation-circle me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-link link-dark text-decoration-none p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2">
                                        <li>
                                            <a class="dropdown-item py-2 px-3 d-flex align-items-center rounded-2" href="javascript:void(0)" wire:click="showGroupDetail({{ $group->supplier_id }}, '{{ $group->received_date }}')">
                                                <i class="bi bi-journal-text me-2 text-primary"></i> <span>View Purchases</span>
                                            </a>
                                        </li>
                                        @if($group->total_cash_paid < $group->total_purchase_amount)
                                        <li><hr class="dropdown-divider mx-2"></li>
                                        <li>
                                            <a class="dropdown-item py-2 px-3 d-flex align-items-center rounded-2 bg-soft-success text-success" href="javascript:void(0)" wire:click="openConfirmPay({{ $group->supplier_id }}, '{{ $group->received_date }}')">
                                                <i class="bi bi-cash-coin me-2"></i> <span>Settle Payment</span>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-inbox text-muted display-1 opacity-25"></i>
                                    <h5 class="mt-3 text-muted fw-normal">No receipt records match your criteria.</h5>
                                    <button class="btn btn-soft-primary mt-3 btn-sm" wire:click="clearFilters">Clear all filters</button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Optimized Pagination --}}
            @if($groupedPayments->hasPages())
            <div class="px-4 py-3 bg-light-soft border-top d-flex justify-content-center">
                {{ $groupedPayments->links('livewire.custom-pagination') }}
            </div>
            @endif
        </div>
    </div>

    {{-- Group Detail Modal --}}
    @if($showDetailModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.6); z-index: 1050;">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary text-white p-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm">
                            <i class="bi bi-journal-check fs-2"></i>
                        </div>
                        <div>
                            <h4 class="modal-title fw-bold mb-0">Purchases Statement</h4>
                            <p class="mb-0 opacity-75 small">{{ $selectedGroupSupplier }} • {{ date('M d, Y', strtotime($selectedGroupDate)) }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDetailModal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Detail Summary Header --}}
                    <div class="bg-light-soft px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold letter-spacing-1 text-start">Summary Overview</span>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="text-end">
                                <small class="text-muted d-block lh-1 mb-1">Total Due</small>
                                <span class="fw-bold text-danger">Rs. {{ number_format(collect($selectedGroupOrderSummary)->sum('due_amount'), 2) }}</span>
                            </div>
                            <div class="text-end border-start ps-3">
                                <small class="text-muted d-block lh-1 mb-1">Collection Total</small>
                                <h5 class="fw-bold text-dark mb-0">Rs. {{ number_format(collect($selectedGroupOrderSummary)->sum('total_amount'), 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light small font-weight-bold text-muted">
                                    <tr>
                                        <th class="ps-3 py-3">Product Item</th>
                                        <th class="text-center">Order Reference</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $grandTotal = 0; @endphp
                                    @foreach($selectedGroupItems as $item)
                                    @php $grandTotal += $item['total']; @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-soft-secondary text-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-box-seam"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark mb-0 small">{{ $item['product_name'] }}</div>
                                                    <div class="text-muted" style="font-size: 0.7rem;">Code: {{ $item['product_code'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border small">#{{ $item['order_code'] }}</span>
                                        </td>
                                        <td class="text-center fw-semibold text-dark">{{ $item['quantity'] }}</td>
                                        <td class="text-end text-muted small">Rs. {{ number_format($item['unit_price'], 2) }}</td>
                                        <td class="text-end fw-bold text-dark">Rs. {{ number_format($item['total'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light-soft">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold text-dark py-3">Item Subtotal:</td>
                                        <td class="text-end fw-bold text-primary py-3">Rs. {{ number_format($grandTotal, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Cash Details for this group --}}
                        <div class="card mt-4 border-0 bg-soft-success shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar-lg bg-success text-white rounded-4 d-flex align-items-center justify-content-center shadow">
                                            <i class="bi bi-wallet2 fs-2"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="text-success-dark fw-bold mb-1">CASH SETTLEMENT TRACKER</h6>
                                        <p class="text-success-dark opacity-75 mb-0 small">The amount shown here represents the total cash physical units processed for this daily batch.</p>
                                    </div>
                                    <div class="col-auto text-end">
                                        <span class="text-success-dark small d-block mb-1 text-end">Total Processed Cash</span>
                                        <h3 class="fw-bold text-success-dark mb-0">Rs. {{ number_format($selectedGroupTotalCash, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4">
                    <button type="button" class="btn btn-soft-secondary px-4 fw-bold" wire:click="closeDetailModal">
                        <i class="bi bi-x me-1"></i> Close View
                    </button>
                    @php $groupDue = collect($selectedGroupOrderSummary)->sum('due_amount'); @endphp
                    @if($groupDue > 0)
                    <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" wire:click="openConfirmPay({{ $confirmPaySupplierId ?? $selectedGroupOrderSummary[0]['id'] }}, '{{ $selectedGroupDate }}')" wire:click="closeDetailModal">
                        <i class="bi bi-cash-coin me-1"></i> Settle Balance
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Confirm Pay Modal --}}
    @if($showConfirmPayModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.6); z-index: 1100;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-success text-white py-3 px-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-check me-2"></i> Payment Authorization
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeConfirmPay"></button>
                </div>
                <div class="modal-body p-5 text-center">
                    <div class="avatar-xl bg-soft-success text-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4 animate-bounce">
                        <i class="bi bi-cash-stack fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Authorize Settlement?</h4>
                    <p class="text-muted mb-4 px-3">You are about to record a full cash settlement for <strong>{{ $confirmPayOrderCount }}</strong> orders from <strong>{{ $confirmPaySupplierName }}</strong>.</p>
                    
                    <div class="card bg-light border-0 rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted text-start">Processing Amount:</span>
                                <span class="fw-bold text-dark">Rs. {{ number_format($confirmPayAmount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-0">
                                <span class="text-muted text-start">Reference Date:</span>
                                <span class="fw-bold text-dark text-end">{{ date('d M Y', strtotime($confirmPayDate)) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-soft-secondary w-100 py-2 fw-bold" wire:click="closeConfirmPay">Cancel</button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-success w-100 py-2 fw-bold shadow-sm" wire:click="confirmMarkAsPaid" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="confirmMarkAsPaid">Process Now</span>
                                <span wire:loading wire:target="confirmMarkAsPaid">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .letter-spacing-1 { letter-spacing: 0.05rem; }
        .bg-light-soft { background-color: #f8f9fc; }
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
        .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
        .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
        .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
        .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
        .btn-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border: none; }
        .btn-soft-primary:hover { background-color: #0d6efd; color: #fff; }
        .btn-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border: none; }
        .btn-soft-danger:hover { background-color: #dc3545; color: #fff; }
        .btn-soft-secondary { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; border: none; }
        .btn-soft-secondary:hover { background-color: #6c757d; color: #fff; }
        
        .form-control-modern { border: 1px solid #e1e9f1; padding: 0.75rem 1rem; border-radius: 0.75rem; background-color: #fff; transition: all 0.2s; }
        .form-control-modern:focus { background-color: #fff; border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05); }
        .input-group-modern .form-control { border-radius: 0 0.75rem 0.75rem 0; padding: 0.75rem 1rem 0.75rem 0; border: 1px solid #e1e9f1; border-left: none; }
        .input-group-modern .input-group-text { border-radius: 0.75rem 0 0 0.75rem; border: 1px solid #e1e9f1; border-right: none; }
        
        .avatar-sm { width: 40px; height: 40px; }
        .avatar-md { width: 56px; height: 56px; }
        .avatar-lg { width: 64px; height: 64px; }
        .avatar-xl { width: 96px; height: 96px; }
        
        .text-success-dark { color: #0f5132; }
        .text-dark-50 { color: #6c7a91; }
        
        .rounded-4 { border-radius: 1.2rem !important; }
        
        .dropdown-item:hover { background-color: #f8f9fa; }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-bounce { animation: bounce 2s infinite; }

        .modal-dialog-scrollable .modal-body { overflow-x: hidden; }
        
        .table > :not(caption) > * > * { padding: 1.25rem 0.75rem; }
        
        .breadcrumb-item + .breadcrumb-item::before { content: "›"; color: #adb5bd; font-size: 1.2rem; line-height: 1; }
        .text-start { text-align: left !important; }
        .text-end { text-align: right !important; }
    </style>
</div>