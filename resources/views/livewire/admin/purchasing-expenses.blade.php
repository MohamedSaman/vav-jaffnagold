<div class="py-3">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 rounded-pill fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> Admin Finance
                </span>
                <span class="badge bg-info-subtle text-info px-2.5 py-1.5 rounded-pill fw-semibold">
                    <i class="bi bi-people-fill me-1"></i> 11 Local Suppliers
                </span>
                <span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill fw-semibold">
                    <i class="bi bi-calendar3 me-1"></i> Monthly Wise Tracking
                </span>
            </div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-bag-check-fill text-primary me-2"></i> Purchasing Expenses
            </h3>
            <p class="text-muted mb-0 small">
                Monitor and audit purchasing payouts given to local suppliers across months, shop staff cash deductions, and supplier ledgers.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.expenses') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="bi bi-wallet2 me-1"></i> All Company Expenses
            </a>
            <button class="btn btn-primary btn-sm rounded-3 px-3 py-2 shadow-sm" wire:click="openAddModal">
                <i class="bi bi-plus-circle me-1"></i> Record Purchasing Expense
            </button>
        </div>
    </div>

    {{-- Month-wise Filter Bar (Prominent & User-Friendly) --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                {{-- Quick Month Preset Toggles --}}
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="small fw-bold text-dark me-1">
                        <i class="bi bi-calendar-range text-primary me-1"></i> Period:
                    </span>
                    <div class="btn-group p-1 bg-light border rounded-3" role="group">
                        <button type="button" 
                                wire:click="setMonthFilter('current')" 
                                class="btn btn-sm px-3 py-1.5 fw-semibold rounded-2 {{ $monthFilter === 'current' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}">
                            <i class="bi bi-calendar-check me-1"></i> This Month ({{ now()->format('M Y') }})
                        </button>
                        <button type="button" 
                                wire:click="setMonthFilter('last_month')" 
                                class="btn btn-sm px-3 py-1.5 fw-semibold rounded-2 {{ $monthFilter === 'last_month' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}">
                            <i class="bi bi-calendar-minus me-1"></i> Last Month ({{ now()->subMonth()->format('M Y') }})
                        </button>
                        <button type="button" 
                                wire:click="setMonthFilter('all')" 
                                class="btn btn-sm px-3 py-1.5 fw-semibold rounded-2 {{ $monthFilter === 'all' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}">
                            <i class="bi bi-infinity me-1"></i> All Time
                        </button>
                    </div>

                    {{-- Quick Day Presets --}}
                    <div class="btn-group p-1 bg-light border rounded-3 ms-lg-1" role="group">
                        <button type="button" 
                                wire:click="setMonthFilter('today')" 
                                class="btn btn-sm px-2.5 py-1.5 fw-semibold rounded-2 {{ $monthFilter === 'today' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                                title="Today">
                            Today
                        </button>
                        <button type="button" 
                                wire:click="setMonthFilter('this_week')" 
                                class="btn btn-sm px-2.5 py-1.5 fw-semibold rounded-2 {{ $monthFilter === 'this_week' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                                title="This Week">
                            This Week
                        </button>
                    </div>
                </div>

                {{-- Month Selector Input (<input type="month">) --}}
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="d-flex align-items-center bg-light p-1 px-3 border rounded-3 shadow-none" title="Choose specific month and year">
                        <label for="adminMonthPicker" class="small fw-semibold text-muted me-2 mb-0">
                            <i class="bi bi-funnel text-primary me-1"></i> Pick Month:
                        </label>
                        <input type="month" 
                               id="adminMonthPicker"
                               wire:model.live="selectedMonth" 
                               class="form-control form-control-sm border-0 bg-transparent p-0 shadow-none fw-bold text-primary" 
                               style="width: 140px; font-size: 0.9rem;"
                               title="Filter by any specific month">
                        @if($monthFilter === 'custom_month' || ($selectedMonth && $monthFilter !== 'current'))
                            <button type="button" 
                                    class="btn btn-link btn-sm text-danger p-0 ms-2 text-decoration-none" 
                                    wire:click="setMonthFilter('current')" 
                                    title="Reset to current month">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Clear All / Reset --}}
                    @if($monthFilter !== 'current' || $supplierFilter !== 'all' || !empty($search) || $purposeFilter !== 'all')
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-2.5" wire:click="clearAllFilters">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                        </button>
                    @endif

                    {{-- Loading Spinner --}}
                    <div wire:loading wire:target="setMonthFilter, selectedMonth, selectMonth, supplierFilter" class="ms-1">
                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly History Quick Switcher (Horizontal Card Overview) --}}
    @if(count($monthlyBreakdown) > 0)
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-header bg-transparent border-0 pt-3 pb-1 px-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-graph-up text-primary me-1"></i> Monthly Expense History
                    </h6>
                    <small class="text-muted">Click any month below to instantly view that month's full vouchers and supplier breakdown</small>
                </div>
                <span class="badge bg-light text-muted border px-2 py-1 rounded-pill small">
                    {{ count($monthlyBreakdown) }} Months on Record
                </span>
            </div>
            <div class="card-body p-3 pt-2">
                <div class="d-flex flex-nowrap gap-2 overflow-auto pb-2" style="scrollbar-width: thin;">
                    @foreach($monthlyBreakdown as $m)
                        @php
                            $isSelected = ($monthFilter === 'custom_month' && $selectedMonth === $m['key']) || 
                                          ($monthFilter === 'current' && $m['key'] === now()->format('Y-m')) ||
                                          ($monthFilter === 'last_month' && $m['key'] === now()->subMonth()->format('Y-m'));
                        @endphp
                        <div class="flex-shrink-0" style="min-width: 175px;">
                            <div class="card h-100 rounded-3 border transition {{ $isSelected ? 'border-primary shadow-sm bg-primary-subtle' : 'border-light-subtle bg-light' }}"
                                 style="cursor: pointer; transition: all 0.2s ease;"
                                 wire:click="selectMonth('{{ $m['key'] }}')">
                                <div class="card-body p-2.5 d-flex flex-column justify-content-between">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold small {{ $isSelected ? 'text-primary' : 'text-dark' }}">
                                            {{ $m['short_label'] }}
                                        </span>
                                        @if($isSelected)
                                            <span class="badge bg-primary text-white rounded-pill" style="font-size: 9px;">Active</span>
                                        @else
                                            <span class="badge bg-white text-muted border rounded-pill" style="font-size: 9px;">{{ $m['count'] }} vchr</span>
                                        @endif
                                    </div>
                                    <div class="fw-bold {{ $isSelected ? 'text-primary' : 'text-dark' }}" style="font-size: 0.95rem;">
                                        Rs. {{ number_format($m['total'], 0) }}
                                    </div>
                                    <div class="small text-muted" style="font-size: 11px;">
                                        {{ $m['count'] }} {{ Str::plural('payout', $m['count']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Overall KPI Cards --}}
    <div class="row g-3 mb-4">
        {{-- Selected Period Total --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #161b97 0%, #0e1268 100%);">
                <div class="card-body p-3 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase tracking-wider opacity-75 fw-bold" style="font-size: 11px;">Period Spend</small>
                        <i class="bi bi-calendar-check fs-5 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">Rs. {{ number_format($filteredPeriodTotal, 2) }}</h3>
                    <small class="opacity-75" style="font-size: 12px;">Total in {{ $activeMonthLabel }}</small>
                </div>
            </div>
        </div>

        {{-- Current Month Total Benchmark --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                <div class="card-body p-3 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase tracking-wider opacity-75 fw-bold" style="font-size: 11px;">Current Month</small>
                        <i class="bi bi-calendar3 fs-5 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">Rs. {{ number_format($currentMonthTotal, 2) }}</h3>
                    <small class="opacity-75" style="font-size: 12px;">{{ now()->format('F Y') }} Total</small>
                </div>
            </div>
        </div>

        {{-- Today Payouts --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                <div class="card-body p-3 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase tracking-wider opacity-75 fw-bold" style="font-size: 11px;">Today's Payouts</small>
                        <i class="bi bi-cash-coin fs-5 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">Rs. {{ number_format($todayTotal, 2) }}</h3>
                    <small class="opacity-75" style="font-size: 12px;">Cash given to suppliers today</small>
                </div>
            </div>
        </div>

        {{-- Filtered Vouchers Count --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #475569 0%, #334155 100%);">
                <div class="card-body p-3 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-uppercase tracking-wider opacity-75 fw-bold" style="font-size: 11px;">Vouchers</small>
                        <i class="bi bi-receipt-cutoff fs-5 opacity-75"></i>
                    </div>
                    <h3 class="fw-bold mb-0">{{ number_format($filteredPeriodCount) }}</h3>
                    <small class="opacity-75" style="font-size: 12px;">{{ number_format($allTimeCount) }} all-time (Rs. {{ number_format($allTimeTotal, 0) }})</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Local Supplier Breakdown Cards (For the Selected Period) --}}
    <div class="mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-people-fill text-primary me-1"></i> Supplier Payout Breakdown in {{ $activeMonthLabel }}
                    <span class="badge bg-primary-subtle text-primary rounded-pill ms-1">{{ count($supplierSummaries) }} Suppliers</span>
                </h6>
                <small class="text-muted">Click any supplier card to instantly filter their vouchers for this period</small>
            </div>
            @if($supplierFilter !== 'all')
                <div>
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" wire:click="setSupplierFilter('all')">
                        <i class="bi bi-x-circle me-1"></i> Clear Supplier Filter (Active: <strong>{{ $supplierFilter }}</strong>)
                    </button>
                </div>
            @endif
        </div>

        {{-- Dynamic Supplier Cards Grid --}}
        <div class="row g-2.5 g-md-3">
            @foreach($supplierSummaries as $summary)
                @php
                    $isFiltered = ($supplierFilter === $summary['name']);
                    $cardColor = $summary['color'];
                @endphp
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 col-xxl-2">
                    <div class="card h-100 rounded-3 shadow-sm transition"
                         style="cursor: pointer; transition: all 0.2s ease; border: 2px solid {{ $isFiltered ? $cardColor['bg'] : '#e2e8f0' }} !important; background: {{ $isFiltered ? $cardColor['light_bg'] : '#ffffff' }} !important;"
                         wire:click="setSupplierFilter('{{ $summary['name'] }}')">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-sm" 
                                     style="width: 36px; height: 36px; background: {{ $cardColor['bg'] }}; font-size: 15px;">
                                    <i class="bi {{ $cardColor['icon'] }}"></i>
                                </div>
                                @if($isFiltered)
                                    <span class="badge text-white px-2 py-1 rounded-pill" style="background: {{ $cardColor['bg'] }}; font-size: 10px;">
                                        <i class="bi bi-check2 me-0.5"></i> Active
                                    </span>
                                @elseif($summary['count'] > 0)
                                    <span class="badge bg-light text-muted border px-1.5 py-0.5 rounded-pill" style="font-size: 10px;">
                                        {{ $summary['count'] }} {{ Str::plural('payout', $summary['count']) }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-1.5 py-0.5 rounded-pill" style="font-size: 10px;">
                                        No payouts
                                    </span>
                                @endif
                            </div>

                            <div>
                                <h6 class="fw-bold mb-1 text-truncate" title="{{ $summary['name'] }}" 
                                    style="color: {{ $isFiltered ? $cardColor['text'] : '#1e293b' }}; font-size: 0.9rem;">
                                    {{ $summary['name'] }}
                                </h6>
                                <div class="fw-bold" style="font-size: 1.05rem; color: {{ $cardColor['text'] }};">
                                    Rs. {{ number_format($summary['total'], 2) }}
                                </div>
                                <small class="text-muted d-block mt-0.5" style="font-size: 11px;">
                                    {{ $activeMonthLabel }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Main Vouchers List Card --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom p-3">
            <div class="row g-2 align-items-center">
                {{-- Search Bar --}}
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" 
                               placeholder="Search voucher, supplier, notes, staff..." 
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                {{-- Supplier Select Dropdown --}}
                <div class="col-6 col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="supplierFilter">
                        <option value="all">All Suppliers (11 Registered)</option>
                        @foreach($registeredSuppliers as $sup)
                            <option value="{{ $sup }}">{{ $sup }}</option>
                        @endforeach
                        <option value="other">Other / Uncategorized</option>
                    </select>
                </div>

                {{-- Purpose Filter Dropdown --}}
                <div class="col-6 col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="purposeFilter">
                        <option value="all">All Purpose Categories</option>
                        @foreach($purposeCategories as $purp)
                            <option value="{{ $purp }}">{{ $purp }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Per Page --}}
                <div class="col-12 col-md-2 text-md-end">
                    <select class="form-select form-select-sm d-inline-block w-auto" wire:model.live="perPage">
                        <option value="15">15 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-muted small text-uppercase">
                        <th style="width: 100px;">Voucher #</th>
                        <th style="width: 130px;">Date</th>
                        <th style="width: 190px;">Supplier</th>
                        <th>Purpose / Category</th>
                        <th>Recorded By</th>
                        <th class="text-end" style="width: 150px;">Amount (Rs.)</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        @php
                            $detectedName = \App\Services\PurchasingExpenseService::detectSupplierName($expense->expense_type, $expense->description);
                            $color = \App\Services\PurchasingExpenseService::getSupplierColor($detectedName);
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-bold text-dark font-monospace" style="font-size: 12px;">
                                    #EXP-{{ str_pad($expense->id, 5, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">
                                    {{ $expense->expense_date ? $expense->expense_date->format('d M, Y') : '-' }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $expense->created_at ? $expense->created_at->format('h:i A') : '' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge px-2.5 py-1.5 rounded-pill d-inline-flex align-items-center gap-1.5"
                                      style="background: {{ $color['light_bg'] }}; color: {{ $color['text'] }}; border: 1px solid {{ $color['border'] }}; font-size: 11.5px;">
                                    <i class="bi {{ $color['icon'] }}"></i>
                                    <strong>{{ $expense->expense_type }}</strong>
                                </span>
                            </td>
                            <td>
                                <div class="text-dark fw-medium small">
                                    {{ Str::limit($expense->description, 60) ?: '-' }}
                                </div>
                                @if($expense->admin_notes)
                                    <div class="text-muted fst-italic" style="font-size: 11px;">
                                        {{ Str::limit($expense->admin_notes, 40) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 10px;">
                                        <i class="bi bi-person me-0.5"></i> {{ $expense->staff->name ?? 'Staff #'.$expense->staff_id }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-danger" style="font-size: 14.5px;">
                                    Rs. {{ number_format($expense->amount, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($expense->status === 'approved')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 11px;">
                                        <i class="bi bi-check-circle-fill me-0.5"></i> Approved
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1" style="font-size: 11px;">
                                        <i class="bi bi-clock-fill me-0.5"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary py-0.5 px-2" 
                                            wire:click="viewDetails({{ $expense->id }})" 
                                            title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-danger py-0.5 px-2" 
                                            wire:click="confirmDelete({{ $expense->id }})" 
                                            title="Delete Voucher">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-dark">No Purchasing Expenses Found</h6>
                                    <p class="small text-muted mb-3">
                                        No supplier payouts recorded matching the selected month ({{ $activeMonthLabel }}) and filters.
                                    </p>
                                    <button class="btn btn-sm btn-outline-primary" wire:click="clearAllFilters">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($expenses->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $expenses->firstItem() }} to {{ $expenses->lastItem() }} of {{ $expenses->total() }} vouchers
                </small>
                <div>
                    {{ $expenses->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedExpense)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-receipt me-2"></i> Voucher #EXP-{{ str_pad($selectedExpense->id, 5, '0', STR_PAD_LEFT) }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDetailModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center mb-3">
                            <small class="text-muted text-uppercase tracking-wider fw-bold">Payout Amount</small>
                            <h2 class="fw-bold text-danger mb-0">Rs. {{ number_format($selectedExpense->amount, 2) }}</h2>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mt-1">
                                <i class="bi bi-check-circle-fill me-1"></i> {{ ucfirst($selectedExpense->status) }}
                            </span>
                        </div>

                        <div class="list-group list-group-flush border rounded-3 mb-3">
                            <div class="list-group-item d-flex justify-content-between py-2.5">
                                <span class="text-muted small">Supplier</span>
                                <span class="fw-bold text-primary">{{ $selectedExpense->expense_type }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between py-2.5">
                                <span class="text-muted small">Payment Date</span>
                                <span class="fw-semibold text-dark">{{ $selectedExpense->expense_date->format('d F, Y') }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between py-2.5">
                                <span class="text-muted small">Recorded By</span>
                                <span class="fw-semibold text-dark">{{ $selectedExpense->staff->name ?? 'Staff #'.$selectedExpense->staff_id }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between py-2.5">
                                <span class="text-muted small">Created At</span>
                                <span class="text-muted small">{{ $selectedExpense->created_at->format('d M, Y h:i A') }}</span>
                            </div>
                            <div class="list-group-item py-2.5">
                                <span class="text-muted small d-block mb-1">Purpose / Description</span>
                                <span class="fw-medium text-dark">{{ $selectedExpense->description ?: '-' }}</span>
                            </div>
                            @if($selectedExpense->admin_notes)
                                <div class="list-group-item py-2.5">
                                    <span class="text-muted small d-block mb-1">Audit Notes</span>
                                    <span class="text-secondary small fst-italic">{{ $selectedExpense->admin_notes }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeDetailModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Purchasing Expense Modal --}}
    @if($showAddModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle me-2"></i> Record Purchasing Expense
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeAddModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form wire:submit.prevent="addExpense">
                            {{-- Quick Supplier Selection --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="bi bi-people-fill text-primary me-1"></i> Quick Select Supplier:
                                </label>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @foreach($registeredSuppliers as $sup)
                                        <button type="button" 
                                                class="btn btn-sm {{ $supplier_name === $sup ? 'btn-primary' : 'btn-outline-primary' }} py-0.5 px-2"
                                                style="font-size: 0.75rem;"
                                                wire:click="selectQuickSupplier('{{ $sup }}')">
                                            {{ $sup }}
                                        </button>
                                    @endforeach
                                </div>
                                <input type="text" list="admin-suppliers-list" 
                                       class="form-control @error('supplier_name') is-invalid @enderror"
                                       wire:model="supplier_name" 
                                       placeholder="Or type supplier name...">
                                <datalist id="admin-suppliers-list">
                                    @foreach($registeredSuppliers as $sup)
                                        <option value="{{ $sup }}">
                                    @endforeach
                                </datalist>
                                @error('supplier_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Amount --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">Amount (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="1" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       wire:model="amount" 
                                       placeholder="0.00">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Expense Date --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">Payout Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('expense_date') is-invalid @enderror" 
                                       wire:model="expense_date">
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Purpose Category --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">Purpose / Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('purpose') is-invalid @enderror" wire:model="purpose">
                                    @foreach($purposeCategories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                                @error('purpose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Description / Notes --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">Notes / Description</label>
                                <textarea class="form-control" rows="2" 
                                          wire:model="description" 
                                          placeholder="Additional details (items received, weights, reference...)..."></textarea>
                            </div>

                            <div class="alert alert-info py-2 small mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Automatically creates the expense voucher, updates shop cash-in-hand, and synchronizes to the supplier's payment ledger.
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" class="btn btn-secondary btn-sm" wire:click="closeAddModal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3">
                                    <i class="bi bi-check-circle me-1"></i> Save Voucher
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal && $expenseToDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Voucher Deletion
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cancelDelete"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold mt-2">Delete Voucher #EXP-{{ str_pad($expenseToDelete->id, 5, '0', STR_PAD_LEFT) }}?</h5>
                        <p class="text-muted small mb-3">
                            Supplier: <strong>{{ $expenseToDelete->expense_type }}</strong> &bull; Amount: <strong class="text-danger">Rs. {{ number_format($expenseToDelete->amount, 2) }}</strong>
                        </p>
                        <div class="alert alert-warning py-2 small text-start">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Deleting this purchasing voucher will automatically restore <strong>Rs. {{ number_format($expenseToDelete->amount, 2) }}</strong> back to the shop's cash in hand.
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary btn-sm px-3" wire:click="cancelDelete">Cancel</button>
                        <button type="button" class="btn btn-danger btn-sm px-3" wire:click="deleteExpense">
                            <i class="bi bi-trash me-1"></i> Confirm & Restore Cash
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
