<div class="delivery-completed-wrapper">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-check2-all text-success me-2"></i> Completed Orders & Delivery History
            </h4>
            <p class="text-muted small mb-0">
                Track and review all completed deliveries for Domex and Post, verify COD collections, and print shipping receipts.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('delivery.domex-import') }}" class="btn btn-warning text-dark fw-bold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" title="Upload Domex remittance Excel, check delivery fee variances, and confirm returns">
                <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                <span>Import Domex Excel</span>
            </a>
            <a href="{{ route('delivery.dashboard') }}" class="btn btn-outline-secondary fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                <span>Orders Dashboard</span>
            </a>
            <a href="{{ route('delivery.finance') }}" class="btn btn-outline-primary fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2">
                <i class="bi bi-wallet2"></i>
                <span>Finance & Analytics</span>
            </a>
        </div>
    </div>

    {{-- Top 5 Financial & Courier Metric Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Completed Orders --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Total Completed</div>
                        <h4 class="fw-bold text-dark mb-1 mt-1">Rs. {{ number_format($stats['totalCompletedAmount'], 2) }}</h4>
                        <div class="d-flex align-items-center gap-2 text-muted small mt-2" style="font-size: 12px;">
                            <span class="badge bg-success bg-opacity-10 text-success fw-semibold rounded-pill px-2">
                                {{ $stats['totalCompletedCount'] }} Orders
                            </span>
                            <span>Fee: Rs. {{ number_format($stats['totalDeliveryCharges'], 0) }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Delivered Today --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative cursor-pointer"
                 wire:click="setQuickDate('today')" style="cursor: pointer;" title="Click to filter today's deliveries">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-primary small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Delivered Today</div>
                        <h4 class="fw-bold text-primary mb-1 mt-1">Rs. {{ number_format($stats['todayCompletedAmount'], 2) }}</h4>
                        <div class="d-flex align-items-center gap-2 text-muted small mt-2" style="font-size: 12px;">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold rounded-pill px-2">
                                {{ $stats['todayCompletedCount'] }} Today
                            </span>
                            <span>Click to view</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Domex Completed --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative border-top border-3 border-primary cursor-pointer"
                 wire:click="setCourierTab('domex')" style="cursor: pointer;" title="Click to view Domex deliveries">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Domex Completed</div>
                        <h4 class="fw-bold text-dark mb-1 mt-1">Rs. {{ number_format($stats['domexCompletedAmount'], 2) }}</h4>
                        <div class="d-flex align-items-center gap-2 text-muted small mt-2" style="font-size: 12px;">
                            <span class="badge bg-primary px-2 rounded-pill">
                                {{ $stats['domexCompletedCount'] }} parcels
                            </span>
                            <span>Today: Rs. {{ number_format($stats['domexTodayAmount'], 0) }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-light text-primary">
                        <i class="bi bi-truck fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Post Completed --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative border-top border-3 border-danger cursor-pointer"
                 wire:click="setCourierTab('post')" style="cursor: pointer;" title="Click to view Post deliveries">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Post Completed</div>
                        <h4 class="fw-bold text-dark mb-1 mt-1">Rs. {{ number_format($stats['postCompletedAmount'], 2) }}</h4>
                        <div class="d-flex align-items-center gap-2 text-muted small mt-2" style="font-size: 12px;">
                            <span class="badge bg-danger px-2 rounded-pill">
                                {{ $stats['postCompletedCount'] }} parcels
                            </span>
                            <span>Today: Rs. {{ number_format($stats['postTodayAmount'], 0) }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-light text-danger">
                        <i class="bi bi-envelope-paper fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 5: COD Settlement --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">COD Collections</div>
                        <h4 class="fw-bold text-success mb-1 mt-1">Rs. {{ number_format($stats['totalCollected'], 2) }}</h4>
                        <div class="d-flex align-items-center gap-2 text-muted small mt-2" style="font-size: 12px;">
                            <span class="badge bg-info bg-opacity-10 text-info fw-semibold rounded-pill px-2">Settled</span>
                            <span class="text-danger fw-semibold">Due: Rs. {{ number_format($stats['totalDue'], 0) }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== TWO COURIER TABS ("TWO PAGE" VIEW: DOMEX & POST) ==================== --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-3 p-2">
        <ul class="nav nav-pills nav-fill gap-2" role="tablist">
            {{-- Tab 1: All Completed Deliveries --}}
            <li class="nav-item">
                <button type="button" 
                        class="nav-link py-2.5 px-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 {{ $courierTab === 'all' ? 'active bg-dark text-white shadow-sm' : 'text-muted bg-light' }}"
                        wire:click="setCourierTab('all')">
                    <i class="bi bi-collection"></i>
                    <span>All Deliveries</span>
                    <span class="badge {{ $courierTab === 'all' ? 'bg-white text-dark' : 'bg-secondary text-white' }} rounded-pill ms-1">
                        {{ $stats['totalCompletedCount'] }}
                    </span>
                    <span class="ms-1 small opacity-75 font-monospace">Rs. {{ number_format($stats['totalCompletedAmount'], 0) }}</span>
                </button>
            </li>

            {{-- Tab 2: Domex Deliveries --}}
            <li class="nav-item">
                <button type="button" 
                        class="nav-link py-2.5 px-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 {{ $courierTab === 'domex' ? 'active bg-primary text-white shadow-sm' : 'text-primary bg-light' }}"
                        wire:click="setCourierTab('domex')">
                    <i class="bi bi-truck"></i>
                    <span>Domex Deliveries</span>
                    <span class="badge {{ $courierTab === 'domex' ? 'bg-white text-primary' : 'bg-primary text-white' }} rounded-pill ms-1">
                        {{ $stats['domexCompletedCount'] }}
                    </span>
                    <span class="ms-1 small opacity-75 font-monospace">Rs. {{ number_format($stats['domexCompletedAmount'], 0) }}</span>
                </button>
            </li>

            {{-- Tab 3: Post Deliveries --}}
            <li class="nav-item">
                <button type="button" 
                        class="nav-link py-2.5 px-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 {{ $courierTab === 'post' ? 'active bg-danger text-white shadow-sm' : 'text-danger bg-light' }}"
                        wire:click="setCourierTab('post')">
                    <i class="bi bi-envelope-paper"></i>
                    <span>Post Deliveries (SL Post)</span>
                    <span class="badge {{ $courierTab === 'post' ? 'bg-white text-danger' : 'bg-danger text-white' }} rounded-pill ms-1">
                        {{ $stats['postCompletedCount'] }}
                    </span>
                    <span class="ms-1 small opacity-75 font-monospace">Rs. {{ number_format($stats['postCompletedAmount'], 0) }}</span>
                </button>
            </li>

            {{-- Tab 4: Direct Deliveries (if present) --}}
            @if($stats['directCompletedCount'] > 0)
                <li class="nav-item">
                    <button type="button" 
                            class="nav-link py-2.5 px-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 {{ $courierTab === 'direct' ? 'active bg-secondary text-white shadow-sm' : 'text-secondary bg-light' }}"
                            wire:click="setCourierTab('direct')">
                        <i class="bi bi-person"></i>
                        <span>Direct Handover</span>
                        <span class="badge {{ $courierTab === 'direct' ? 'bg-white text-secondary' : 'bg-secondary text-white' }} rounded-pill ms-1">
                            {{ $stats['directCompletedCount'] }}
                        </span>
                        <span class="ms-1 small opacity-75 font-monospace">Rs. {{ number_format($stats['directCompletedAmount'], 0) }}</span>
            @endif
        </ul>

        {{-- Secondary Filter Pill Row: Status (All, Delivered - Awaiting Courier Money, Completed - Paid) --}}
        <div class="d-flex align-items-center gap-2 px-2 pt-2 border-top flex-wrap">
            <span class="text-muted small fw-semibold me-1"><i class="bi bi-funnel me-1"></i> Order State:</span>
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold {{ $statusFilter === 'all' ? 'btn-dark text-white' : 'btn-outline-secondary' }}"
                    wire:click="setStatusFilter('all')">
                All Orders ({{ $stats['totalCompletedCount'] }})
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold {{ $statusFilter === 'delivered' ? 'btn-warning text-dark shadow-sm' : 'btn-outline-warning text-dark' }}"
                    wire:click="setStatusFilter('delivered')">
                <i class="bi bi-clock-history me-1"></i> Delivered (Awaiting Courier Payment)
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold {{ $statusFilter === 'completed' ? 'btn-success text-white shadow-sm' : 'btn-outline-success' }}"
                    wire:click="setStatusFilter('completed')">
                <i class="bi bi-check2-all me-1"></i> Completed (Payment Received)
            </button>
        </div>
    </div>

    {{-- Main Completed Deliveries Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        {{-- Filter Bar --}}
        <div class="p-3 border-bottom bg-light bg-opacity-50">
            <div class="row g-2 align-items-center mb-2">
                {{-- Search --}}
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" wire:model.live.debounce.300ms="search" 
                               placeholder="Search invoice, customer name, phone, tracking number...">
                    </div>
                </div>

                {{-- Payment Status Filter --}}
                <div class="col-6 col-sm-4 col-md-3">
                    <select class="form-select" wire:model.live="paymentFilter">
                        <option value="all">All Payment Statuses</option>
                        <option value="paid">Fully Paid / Settled</option>
                        <option value="due">COD Due / Remittance Pending</option>
                    </select>
                </div>

                {{-- Reset Button --}}
                <div class="col-6 col-sm-4 col-md-2 ms-auto text-end">
                    <button class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-1.5" wire:click="resetFilters" title="Reset All Filters">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                    </button>
                </div>
            </div>

            {{-- Date Range & Quick Preset Bar --}}
            <div class="pt-2 border-top">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    {{-- Quick Date Presets --}}
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span class="text-muted small fw-semibold me-1"><i class="bi bi-calendar3 me-1"></i> Date Filter:</span>
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold {{ $dateFilter === 'today' ? 'btn-primary shadow-sm' : 'btn-outline-primary' }}"
                                wire:click="setQuickDate('today')">
                            <i class="bi bi-star-fill me-1 small"></i> Today
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $dateFilter === 'yesterday' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="setQuickDate('yesterday')">
                            Yesterday
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $dateFilter === 'week' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="setQuickDate('week')">
                            This Week
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $dateFilter === 'month' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="setQuickDate('month')">
                            This Month
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $dateFilter === 'all' && empty($dateFrom) && empty($dateTo) ? 'btn-dark' : 'btn-outline-secondary' }}"
                                wire:click="setQuickDate('all')">
                            All Time
                        </button>
                    </div>

                    {{-- Exact Date Pickers (From / To / Same Date) --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        {{-- Date Field Selector --}}
                        <div class="input-group input-group-sm" style="width: auto;">
                            <span class="input-group-text bg-white text-muted" style="font-size: 11px;">Filter By</span>
                            <select class="form-select form-select-sm" wire:model.live="dateField" style="font-size: 11px;">
                                <option value="delivered_at">Delivered Date</option>
                                <option value="created_at">Order Date</option>
                            </select>
                        </div>

                        {{-- From Date --}}
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-muted small">From:</span>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom" style="width: 135px;">
                        </div>

                        {{-- To Date --}}
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-muted small">To:</span>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateTo" style="width: 135px;">
                        </div>

                        @if(!empty($dateFrom))
                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill py-0.5 px-2"
                                    wire:click="setSameDate('{{ $dateFrom }}')"
                                    title="View completed deliveries on this exact date only">
                                <i class="bi bi-calendar-check me-0.5"></i> Same Day
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Filter Period Summary Banner --}}
                @if(!empty($dateFrom) || !empty($dateTo) || in_array($dateFilter, ['today', 'yesterday', 'week', 'month', 'custom']))
                    <div class="mt-2.5 p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between flex-wrap gap-2 text-dark small">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-primary px-2 py-1 rounded-pill">
                                <i class="bi bi-funnel-fill me-1"></i> Active Filter Period
                            </span>
                            <strong>
                                @if(!empty($dateFrom) && !empty($dateTo))
                                    @if($dateFrom === $dateTo)
                                        Exact Date: <span class="text-primary font-monospace">{{ $dateFrom }}</span>
                                    @else
                                        Between: <span class="text-primary font-monospace">{{ $dateFrom }}</span> to <span class="text-primary font-monospace">{{ $dateTo }}</span>
                                    @endif
                                @elseif(!empty($dateFrom))
                                    From: <span class="text-primary font-monospace">{{ $dateFrom }} onwards</span>
                                @elseif(!empty($dateTo))
                                    Up to: <span class="text-primary font-monospace">{{ $dateTo }}</span>
                                @else
                                    Preset: <span class="text-primary text-uppercase">{{ $dateFilter }}</span>
                                @endif
                                (by {{ $dateField === 'delivered_at' ? 'Delivered Date' : 'Order Date' }})
                            </strong>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap font-monospace">
                            <span><i class="bi bi-box-seam me-1 text-muted"></i> Orders: <strong>{{ $filteredSummary['count'] }}</strong></span>
                            <span><i class="bi bi-cash-stack me-1 text-primary"></i> Total: <strong>Rs. {{ number_format($filteredSummary['amount'], 2) }}</strong></span>
                            <span><i class="bi bi-truck me-1 text-warning"></i> Delivery Fee: <strong>Rs. {{ number_format($filteredSummary['deliveryCharge'], 2) }}</strong></span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                <i class="bi bi-wallet2 me-1"></i> Receivable: <strong>Rs. {{ number_format($filteredSummary['receivableAmount'], 2) }}</strong>
                            </span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                <i class="bi bi-truck me-1"></i> Domex: <strong>Rs. {{ number_format($filteredSummary['domexAmount'], 2) }}</strong> ({{ $filteredSummary['domexCount'] }})
                            </span>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                <i class="bi bi-envelope-paper me-1"></i> Post: <strong>Rs. {{ number_format($filteredSummary['postAmount'], 2) }}</strong> ({{ $filteredSummary['postCount'] }})
                            </span>
                            @if(($filteredSummary['directCount'] ?? 0) > 0)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                    <i class="bi bi-person me-1"></i> Direct: <strong>Rs. {{ number_format($filteredSummary['directAmount'], 2) }}</strong> ({{ $filteredSummary['directCount'] }})
                                </span>
                            @endif
                            <span><i class="bi bi-check2-circle me-1 text-success"></i> Settled: <strong>Rs. {{ number_format($filteredSummary['collected'], 2) }}</strong></span>
                            @if($filteredSummary['due'] > 0)
                                <span><i class="bi bi-exclamation-circle me-1 text-danger"></i> Due: <strong>Rs. {{ number_format($filteredSummary['due'], 2) }}</strong></span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Table Content --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-3 py-3">Delivered Date / Invoice</th>
                        <th class="py-3">Customer</th>
                        <th class="py-3">Courier / Tracking</th>
                        <th class="text-center py-3">Order Status</th>
                        <th class="text-end py-3">Order Amount</th>
                        <th class="text-end py-3">Delivery Fee</th>
                        <th class="text-end py-3">Total Payable</th>
                        <th class="text-center py-3">Payment Status</th>
                        <th class="text-end pe-3 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        @php
                            $cName = ($sale->customer && $sale->customer->name !== 'Walking Customer') 
                                ? $sale->customer->name 
                                : ($sale->walking_customer_name ?: 'Customer');
                            $cPhone = ($sale->customer && $sale->customer->phone && $sale->customer->phone !== 'xxxxx')
                                ? $sale->customer->phone
                                : ($sale->walking_customer_phone ?: null);
                            $cAddress = $sale->customer?->address ?? ($sale->deliverySale?->customer_details ?: '');
                            $courier = $sale->deliverySale?->delivery_method ?? 'Domex';
                            $isThirdPartyCourier = in_array(strtolower($courier), ['domex', 'post']);
                            $tracking = $sale->deliverySale?->tracking_number;
                            $delCharge = (float) ($sale->deliverySale?->delivery_charge ?? 0);
                            $isPaid = (float) $sale->due_amount <= 0;
                            $collectedAmount = (float) $sale->payments->sum('amount');
                            $isFullyCompleted = ($sale->delivery_status === 'completed');
                        @endphp
                        <tr>
                            {{-- Delivered Date & Invoice --}}
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $sale->invoice_number ?: $sale->sale_id }}</div>
                                <div class="text-success small fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    {{ $sale->delivered_at ? $sale->delivered_at->format('M d, Y h:i A') : $sale->created_at->format('M d, Y') }}
                                </div>
                                <span class="badge bg-light text-muted border px-1.5 py-0.5 rounded-pill mt-1" style="font-size: 10px;">
                                    {{ ucfirst($sale->order_source) }}
                                </span>
                            </td>

                            {{-- Customer --}}
                            <td>
                                <div class="fw-semibold text-dark">{{ $cName }}</div>
                                @if($cPhone)
                                    <div class="text-muted small font-monospace" style="font-size: 11px;">
                                        <i class="bi bi-telephone me-1"></i>{{ $cPhone }}
                                    </div>
                                @endif
                                @if($cAddress)
                                    <div class="text-muted text-truncate" style="max-width: 200px; font-size: 11px;" title="{{ $cAddress }}">
                                        <i class="bi bi-geo-alt me-0.5"></i>{{ $cAddress }}
                                    </div>
                                @endif
                            </td>

                            {{-- Courier & Tracking --}}
                            <td>
                                <div class="d-flex align-items-center gap-1.5 mb-1">
                                    <span class="badge {{ strcasecmp($courier, 'Domex') === 0 ? 'bg-primary' : (strcasecmp($courier, 'Post') === 0 ? 'bg-danger' : 'bg-secondary') }} px-2 py-0.5">
                                        {{ $courier }}
                                    </span>
                                </div>
                                @if($tracking)
                                    @if(strcasecmp($courier, 'Domex') === 0)
                                        <a href="https://domex.lk/Order-Details.php?wbno={{ $tracking }}" target="_blank"
                                           class="text-primary text-decoration-none small font-monospace fw-bold d-inline-flex align-items-center"
                                           title="Open Domex live tracking">
                                            <i class="bi bi-truck me-1"></i>{{ $tracking }} <i class="bi bi-box-arrow-up-right ms-1 small" style="font-size: 9px;"></i>
                                        </a>
                                    @else
                                        <div class="text-dark small font-monospace fw-semibold" style="font-size: 11px;">
                                            <i class="bi bi-envelope-paper me-1 text-danger"></i>{{ $tracking }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted small fst-italic" style="font-size: 11px;">No Tracking #</span>
                                @endif
                            </td>

                            {{-- Order Status (Delivered vs Completed) --}}
                            <td class="text-center">
                                @if($isFullyCompleted)
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-check2-all me-1"></i> Completed
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold"
                                          title="Delivered by courier. Waiting for payment remittance from {{ $courier }}.">
                                        <i class="bi bi-truck me-1 text-primary"></i> Delivered
                                    </span>
                                @endif
                            </td>

                            {{-- Order Amount (Subtotal) --}}
                            <td class="text-end fw-semibold text-dark">
                                Rs. {{ number_format($sale->subtotal ?? ($sale->total_amount - $delCharge), 2) }}
                            </td>

                            {{-- Delivery Fee --}}
                            <td class="text-end text-muted small">
                                Rs. {{ number_format($delCharge, 2) }}
                            </td>

                            {{-- Total Payable --}}
                            <td class="text-end fw-bold text-dark fs-6">
                                Rs. {{ number_format($sale->total_amount, 2) }}
                            </td>

                            {{-- Payment Status --}}
                            <td class="text-center">
                                @if($isPaid)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-check-lg me-0.5"></i> Fully Paid
                                    </span>
                                @elseif($isThirdPartyCourier)
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold"
                                          title="Delivered to customer. Settlement payout pending from {{ $courier }} service center.">
                                        <i class="bi bi-building me-1 text-info"></i> {{ $courier }} Due (Rs. {{ number_format($sale->due_amount, 0) }})
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        COD Due: Rs. {{ number_format($sale->due_amount, 2) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    {{-- Complete Order Button (Manual completion after receiving courier money) --}}
                                    @if(! $isFullyCompleted || $sale->due_amount > 0)
                                        <button class="btn btn-sm btn-success px-2 py-1 shadow-sm d-flex align-items-center gap-1 fw-bold"
                                                wire:click="openPaymentModal({{ $sale->id }})"
                                                title="Record courier payment & mark order completed">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span class="d-none d-xl-inline">Complete</span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success px-2 py-1"
                                                wire:click="markAsCompleted({{ $sale->id }}, true)"
                                                wire:confirm="Confirm order #{{ $sale->invoice_number }} completed? (Payment received from courier)"
                                                title="1-Click Mark as Completed (Remittance Received)">
                                            <i class="bi bi-check-all"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill small d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-check2-all"></i> Done
                                        </span>
                                    @endif

                                    {{-- View Order Details Button --}}
                                    <button class="btn btn-sm btn-outline-secondary px-2 py-1"
                                            wire:click="viewDetails({{ $sale->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- Print Shipping Label --}}
                                    <a href="{{ route('delivery.print.delivery-label', $sale->id) }}" target="_blank"
                                       class="btn btn-sm btn-outline-dark px-2 py-1" title="Print Shipping Label">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success opacity-50"></i>
                                    <h6 class="fw-semibold text-dark">No completed deliveries found</h6>
                                    <p class="small mb-3">No completed orders match your current courier tab, date range, or search.</p>
                                    <button class="btn btn-sm btn-outline-primary" wire:click="resetFilters">
                                        Reset All Filters
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($sales->count() > 0)
                    <tfoot class="table-light border-top border-dark border-opacity-25 fw-bold" style="font-size: 0.92rem;">
                        <tr class="bg-light">
                            <td colspan="4" class="ps-3 py-3 text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-dark px-2.5 py-1 rounded-pill text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Summary Totals</span>
                                    <span class="text-uppercase fw-bold text-muted small">Total ({{ $filteredSummary['count'] }} {{ Str::plural('Order', $filteredSummary['count']) }})</span>
                                </div>
                            </td>
                            {{-- Order Amount (Receivable) --}}
                            <td class="text-end py-3">
                                <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Receivable Amount</div>
                                <div class="fs-6 fw-bold text-success font-monospace">Rs. {{ number_format($filteredSummary['receivableAmount'], 2) }}</div>
                            </td>
                            {{-- Delivery Fee --}}
                            <td class="text-end py-3">
                                <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Delivery Charge</div>
                                <div class="fs-6 fw-bold text-warning font-monospace">Rs. {{ number_format($filteredSummary['deliveryCharge'], 2) }}</div>
                            </td>
                            {{-- Total Payable / Total Amount --}}
                            <td class="text-end py-3">
                                <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Total Amount</div>
                                <div class="fs-6 fw-bold text-primary font-monospace">Rs. {{ number_format($filteredSummary['amount'], 2) }}</div>
                            </td>
                            {{-- Payment Status (Settled vs Due) --}}
                            <td class="text-center py-3">
                                <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5 rounded-pill mb-0.5 d-inline-block" style="font-size: 10px;">
                                    Settled: Rs. {{ number_format($filteredSummary['collected'], 0) }}
                                </div>
                                @if($filteredSummary['due'] > 0)
                                    <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5 rounded-pill d-inline-block" style="font-size: 10px;">
                                        Due: Rs. {{ number_format($filteredSummary['due'], 0) }}
                                    </div>
                                @endif
                            </td>
                            <td class="pe-3 py-3"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Bottom Financial Summary Strip: Total Amount, Delivery Charge, Receivable Amount --}}
        @if($sales->count() > 0)
            <div class="p-3 bg-white border-top">
                <div class="row g-2 align-items-stretch">
                    {{-- 1. Total Amount --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="h-100 p-2.5 rounded-3 border bg-light bg-opacity-50 d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-receipt me-1 text-primary"></i> Total Amount
                                </div>
                                <div class="fs-5 fw-bold text-dark mt-1 font-monospace">
                                    Rs. {{ number_format($filteredSummary['amount'], 2) }}
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">Gross Customer Payable</div>
                            </div>
                            <div class="badge bg-primary bg-opacity-10 text-primary p-2.5 rounded-circle fs-5">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Delivery Charge --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="h-100 p-2.5 rounded-3 border bg-light bg-opacity-50 d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-truck me-1 text-warning"></i> Delivery Charge
                                </div>
                                <div class="fs-5 fw-bold text-warning mt-1 font-monospace">
                                    Rs. {{ number_format($filteredSummary['deliveryCharge'], 2) }}
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">Courier Shipping Fee</div>
                            </div>
                            <div class="badge bg-warning bg-opacity-10 text-warning p-2.5 rounded-circle fs-5">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Receivable Amount --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="h-100 p-2.5 rounded-3 border border-success border-opacity-50 bg-success bg-opacity-10 d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <div class="text-success text-uppercase fw-bold" style="font-size: 11px;">
                                    <i class="bi bi-wallet2 me-1"></i> Receivable Amount
                                </div>
                                <div class="fs-5 fw-bold text-success mt-1 font-monospace">
                                    Rs. {{ number_format($filteredSummary['receivableAmount'], 2) }}
                                </div>
                                <div class="text-success text-opacity-75 small" style="font-size: 10px;">Net Order Amount (Total - Delivery)</div>
                            </div>
                            <div class="badge bg-success text-white p-2.5 rounded-circle fs-5 shadow-sm">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Courier Settlement Status --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="h-100 p-2.5 rounded-3 border bg-light bg-opacity-50 d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-building me-1 text-info"></i> Courier Settlement
                                </div>
                                <div class="d-flex align-items-baseline gap-2 mt-1">
                                    <span class="fs-6 fw-bold text-success font-monospace" title="Settled Remittance">
                                        Rs. {{ number_format($filteredSummary['collected'], 0) }}
                                    </span>
                                    @if($filteredSummary['due'] > 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-1.5 py-0.5 font-monospace" title="Pending Remittance from Courier">
                                            Due: Rs. {{ number_format($filteredSummary['due'], 0) }}
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-1.5 py-0.5">
                                            Fully Settled
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">Remittance Received vs Due</div>
                            </div>
                            <div class="badge bg-info bg-opacity-10 text-info p-2.5 rounded-circle fs-5">
                                <i class="bi bi-arrow-left-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Pagination --}}
        @if($sales->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} completed deliveries
                </div>
                <div>
                    {{ $sales->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- ==================== MODAL: Order Details ==================== --}}
    @if($showDetailsModal && $selectedSale)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3 px-4">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">
                                <i class="bi bi-check2-circle text-success me-2"></i> Completed Delivery Details
                            </h5>
                            <small class="text-muted">Invoice: {{ $selectedSale->invoice_number ?: $selectedSale->sale_id }}</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeDetailsModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3 mb-4">
                            {{-- Customer Info --}}
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light h-100">
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person me-1"></i> Customer Information</h6>
                                    <div class="text-dark fw-semibold mb-1">
                                        {{ $selectedSale->customer?->name ?? ($selectedSale->walking_customer_name ?: 'Customer') }}
                                    </div>
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-telephone me-1"></i>{{ $selectedSale->customer?->phone ?? ($selectedSale->walking_customer_phone ?: 'N/A') }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $selectedSale->customer?->address ?? ($selectedSale->deliverySale?->customer_details ?: 'Address not provided') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Delivery & Courier Info --}}
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-light h-100">
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-truck me-1"></i> Courier & Dispatch</h6>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Courier:</span>
                                        <span class="fw-semibold text-dark">{{ $selectedSale->deliverySale?->delivery_method ?? 'Domex' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Tracking #:</span>
                                        <span class="fw-bold text-primary">{{ $selectedSale->deliverySale?->tracking_number ?? 'Pending' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Delivered At:</span>
                                        <span class="fw-bold text-success">{{ $selectedSale->delivered_at ? $selectedSale->delivered_at->format('M d, Y h:i A') : 'Completed' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Status:</span>
                                        <span class="badge bg-success">Delivered (Completed)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Line Items Table --}}
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-bag-check me-1"></i> Delivered Products</h6>
                        <div class="table-responsive border rounded-3 mb-4">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th class="ps-3 py-2">Item</th>
                                        <th class="text-center py-2">Qty</th>
                                        <th class="text-end py-2">Price</th>
                                        <th class="text-end pe-3 py-2">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedSale->items as $item)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-dark">{{ $item->product_name ?? 'Jewelry Item' }}</div>
                                                <small class="text-muted font-monospace">{{ $item->product_code ?? '' }}</small>
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end pe-3 fw-semibold">Rs. {{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-2 text-muted">No item details available</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Financial Breakdown --}}
                        <div class="p-3 rounded-3 bg-light">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Subtotal:</span>
                                <span class="fw-semibold text-dark">Rs. {{ number_format($selectedSale->subtotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Delivery Fee:</span>
                                <span class="fw-semibold text-dark">Rs. {{ number_format($selectedSale->deliverySale?->delivery_charge ?? 0, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top fw-bold">
                                <span>Grand Total:</span>
                                <span class="fs-6 text-dark">Rs. {{ number_format($selectedSale->total_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-1 text-success small">
                                <span>Total Paid:</span>
                                <span class="fw-bold">Rs. {{ number_format($selectedSale->payments->sum('amount'), 2) }}</span>
                            </div>
                            @if($selectedSale->due_amount > 0)
                                <div class="d-flex justify-content-between pt-1 text-danger fw-bold">
                                    <span>Balance Due (COD):</span>
                                    <span>Rs. {{ number_format($selectedSale->due_amount, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer border-top py-3 px-4">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">Close</button>
                        <a href="{{ route('delivery.print.delivery-label', $selectedSale->id) }}" target="_blank" class="btn btn-outline-dark">
                            <i class="bi bi-printer me-1"></i> Shipping Label
                        </a>
                        @if($selectedSale->due_amount > 0)
                            <button type="button" class="btn btn-success" wire:click="openPaymentModal({{ $selectedSale->id }})">
                                <i class="bi bi-cash-stack me-1"></i> Collect / Settle COD
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== MODAL: Collect COD Payment / Remittance ==================== --}}
    @if($showPaymentModal && $paymentSale)
        @php
            $modalCourier = $paymentSale->deliverySale->delivery_method ?? 'Courier';
            $modalIsCourier = in_array(strtolower($modalCourier), ['domex', 'post']);
        @endphp
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3 px-4">
                        <div>
                            @if($modalIsCourier)
                                <h5 class="modal-title fw-bold text-dark mb-0">
                                    <i class="bi bi-building-down text-info me-2"></i> Receive Remittance from {{ $modalCourier }}
                                </h5>
                                <small class="text-muted">Settling COD funds from {{ $modalCourier }} &bull; Tracking: {{ $paymentSale->deliverySale?->tracking_number ?? 'N/A' }}</small>
                            @else
                                <h5 class="modal-title fw-bold text-dark mb-0">
                                    <i class="bi bi-cash-stack text-success me-2"></i> Collect COD Payment
                                </h5>
                                <small class="text-muted">Invoice: {{ $paymentSale->invoice_number ?: $paymentSale->sale_id }}</small>
                            @endif
                        </div>
                        <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
                    </div>

                    <div class="modal-body p-4">
                        {{-- Customer & Due summary banner --}}
                        <div class="p-3 rounded-3 bg-light mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Customer:</span>
                                <span class="fw-bold text-dark">
                                    {{ $paymentSale->customer?->name ?? ($paymentSale->walking_customer_name ?: 'Valued Customer') }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Order Total:</span>
                                <span class="fw-semibold text-dark">Rs. {{ number_format($paymentSale->total_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Already Paid:</span>
                                <span class="fw-semibold text-success">Rs. {{ number_format($paymentSale->payments->sum('amount'), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="fw-bold {{ $modalIsCourier ? 'text-primary' : 'text-danger' }}">
                                    {{ $modalIsCourier ? 'Pending Remittance:' : 'Pending Due (COD):' }}
                                </span>
                                <span class="fw-bold fs-5 {{ $modalIsCourier ? 'text-primary' : 'text-danger' }}">
                                    Rs. {{ number_format($paymentSale->due_amount, 2) }}
                                </span>
                            </div>
                        </div>

                        {{-- Payment Amount input --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark">
                                {{ $modalIsCourier ? 'Remittance Received Amount (Rs.)' : 'Amount to Collect (Rs.)' }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="0.01" class="form-control form-control-lg fw-bold {{ $modalIsCourier ? 'text-primary' : 'text-success' }}"
                                   wire:model="paymentAmount" placeholder="Enter amount">
                            @error('paymentAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- Payment Method --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark">Payment Method <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="paymentMethod" value="bank_transfer" id="payMethodBankComp">
                                    <label class="form-check-label fw-medium" for="payMethodBankComp">
                                        <i class="bi bi-bank me-1 text-primary"></i> Bank Transfer / Deposit
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="paymentMethod" value="cash" id="payMethodCashComp">
                                    <label class="form-check-label fw-medium" for="payMethodCashComp">
                                        <i class="bi bi-cash me-1 text-success"></i> Cash
                                    </label>
                                </div>
                            </div>
                            @error('paymentMethod') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="mb-2">
                            <label class="form-label fw-semibold small text-dark">Remittance / Collection Notes</label>
                            <textarea class="form-control" rows="2" wire:model="paymentNotes" placeholder="Service center voucher #, bank slip reference, notes..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-3 px-4">
                        <button type="button" class="btn btn-light" wire:click="closePaymentModal">Cancel</button>
                        <button type="button" class="btn {{ $modalIsCourier ? 'btn-info text-white' : 'btn-success' }} fw-semibold px-4 shadow-sm" wire:click="savePayment">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ $modalIsCourier ? 'Confirm Remittance' : 'Confirm & Collect' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
