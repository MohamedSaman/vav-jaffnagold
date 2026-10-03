<div class="delivery-finance-wrapper">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-wallet2 text-primary me-2"></i> Delivery Finance & Sales Analytics
            </h4>
            <p class="text-muted small mb-0">
                Track delivery sales volume, completed & pending orders, COD collections, expenses, and cash in hand.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-primary fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2" wire:click="openSettlementModal">
                <i class="bi bi-receipt-cutoff fs-5"></i>
                <span>Cash Handover Slip</span>
            </button>
            <a href="{{ route('delivery.dashboard') }}" class="btn btn-primary fw-semibold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-box-seam fs-5"></i>
                <span>Orders & Parcels</span>
            </a>
        </div>
    </div>

    {{-- Top 5 Financial KPI Summary Cards --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: Total Delivery Sales Volume --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Delivery Sales</div>
                        <h4 class="fw-bold text-dark mb-1 mt-1">Rs. {{ number_format($stats['totalDeliverySales'], 2) }}</h4>
                        <div class="d-flex align-items-center gap-2 text-muted small mt-2" style="font-size: 12px;">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold rounded-pill px-2">
                                {{ $stats['totalDeliveryOrdersCount'] }} Orders
                            </span>
                            <span>Fee: Rs. {{ number_format($stats['totalDeliveryCharges'], 0) }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-currency-exchange fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Completed Deliveries --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden cursor-pointer"
                 wire:click="$set('statusFilter', 'delivered')" style="cursor: pointer;" title="Filter Delivered">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-success small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Completed Deliveries</div>
                        <h4 class="fw-bold text-success mb-1 mt-1">{{ $stats['completedDeliveriesCount'] }} <span class="fs-6 fw-normal text-muted">Delivered</span></h4>
                        <div class="d-flex align-items-center gap-2 text-muted small mt-2" style="font-size: 12px;">
                            <span class="badge bg-success bg-opacity-10 text-success fw-semibold rounded-pill px-2">
                                {{ $stats['totalDeliveryOrdersCount'] > 0 ? round(($stats['completedDeliveriesCount'] / $stats['totalDeliveryOrdersCount']) * 100) : 0 }}% Rate
                            </span>
                            <span>Rs. {{ number_format($stats['completedDeliveriesAmount'], 0) }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Pending Deliveries & COD Due --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden cursor-pointer"
                 wire:click="$set('statusFilter', 'pending')" style="cursor: pointer;" title="Filter Pending">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-warning small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Pending Deliveries</div>
                        <h4 class="fw-bold text-dark mb-1 mt-1">{{ $stats['pendingDeliveriesCount'] }} <span class="fs-6 fw-normal text-muted">Active</span></h4>
                        <div class="d-flex align-items-center gap-2 text-muted small mt-2" style="font-size: 12px;">
                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 fw-semibold rounded-pill px-2">COD with Courier</span>
                            <span class="text-primary fw-bold">Rs. {{ number_format($stats['pendingCodAmount'], 2) }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Total Collected Amount --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-info small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Total Collected COD</div>
                        <h4 class="fw-bold text-dark mb-1 mt-1">Rs. {{ number_format($stats['totalCollectedCod'], 2) }}</h4>
                        <div class="d-flex align-items-center gap-2 text-muted small mt-2" style="font-size: 12px;">
                            <span class="badge bg-info bg-opacity-10 text-info fw-semibold rounded-pill px-2">
                                Today: Rs. {{ number_format($stats['todayCollectedCod'], 0) }}
                            </span>
                            <span>Cash: Rs. {{ number_format($stats['cashCollected'], 0) }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-info bg-opacity-10 text-info">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 5: Net Cash in Hand / Handover --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-dark text-white h-100 position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-light small fw-semibold text-uppercase opacity-75" style="letter-spacing: 0.05em; font-size: 11px;">Net Cash in Hand</div>
                        <h4 class="fw-bold text-white mb-1 mt-1">Rs. {{ number_format($stats['netCashInHand'], 2) }}</h4>
                        <div class="d-flex align-items-center gap-2 small mt-2 opacity-75" style="font-size: 12px;">
                            <span>Expenses: Rs. {{ number_format($stats['totalExpenses'], 0) }}</span>
                            <span>•</span>
                            <span class="text-warning">Remit to Shop</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 text-white">
                        <i class="bi bi-safe fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Courier Delivery Finance: Domex vs Post Breakdown Cards --}}
    <div class="row g-3 mb-4">
        {{-- Domex Delivery Finance Card --}}
        <div class="col-12 col-md-6 {{ $stats['directOrdersCount'] > 0 ? 'col-xl-4' : 'col-xl-6' }}">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white h-100 position-relative border-top border-4 border-primary">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary px-2.5 py-1 rounded-pill fw-semibold">
                            <i class="bi bi-truck me-1"></i> Domex Courier
                        </span>
                        <span class="text-muted small fw-semibold">Express Delivery</span>
                    </div>
                    <button type="button" 
                            class="btn btn-sm {{ $courierFilter === 'Domex' ? 'btn-primary shadow-sm' : 'btn-outline-primary' }} rounded-pill px-2.5 py-0.5"
                            style="font-size: 11px;"
                            wire:click="setCourierFilter('Domex')"
                            title="Filter orders list by Domex">
                        <i class="bi bi-funnel-fill me-0.5"></i> {{ $courierFilter === 'Domex' ? 'Active Filter' : 'Filter Domex' }}
                    </button>
                </div>

                <div class="mb-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Domex Delivery Amount</div>
                    <h3 class="fw-bold text-primary mb-0 mt-1">Rs. {{ number_format($stats['domexSalesAmount'], 2) }}</h3>
                    <div class="text-muted small mt-1" style="font-size: 11.5px;">
                        <span><strong class="text-dark">{{ $stats['domexOrdersCount'] }}</strong> Total Parcels</span>
                        <span class="mx-1">&bull;</span>
                        <span>Courier Fee: <strong>Rs. {{ number_format($stats['domexDeliveryCharges'], 2) }}</strong></span>
                    </div>
                </div>

                <div class="row g-2 pt-2 border-top text-center">
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-success bg-opacity-10 h-100">
                            <div class="text-success small fw-semibold" style="font-size: 10.5px;">Delivered</div>
                            <div class="fw-bold text-success fs-6 mt-0.5">Rs. {{ number_format($stats['domexDeliveredAmount'], 0) }}</div>
                            <div class="text-muted" style="font-size: 10px;">{{ $stats['domexDeliveredCount'] }} parcels</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-warning bg-opacity-10 h-100">
                            <div class="text-warning-emphasis small fw-semibold" style="font-size: 10.5px;">In Transit</div>
                            <div class="fw-bold text-dark fs-6 mt-0.5">Rs. {{ number_format($stats['domexInTransitAmount'], 0) }}</div>
                            <div class="text-muted" style="font-size: 10px;">{{ $stats['domexInTransitCount'] }} with Domex</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-info bg-opacity-10 h-100">
                            <div class="text-info-emphasis small fw-semibold" style="font-size: 10.5px;">Collected</div>
                            <div class="fw-bold text-info fs-6 mt-0.5">Rs. {{ number_format($stats['domexCollectedAmount'], 0) }}</div>
                            <div class="text-muted" style="font-size: 10px;">Due: Rs. {{ number_format($stats['domexDueAmount'], 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Post Delivery Finance Card --}}
        <div class="col-12 col-md-6 {{ $stats['directOrdersCount'] > 0 ? 'col-xl-4' : 'col-xl-6' }}">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white h-100 position-relative border-top border-4 border-danger">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger px-2.5 py-1 rounded-pill fw-semibold">
                            <i class="bi bi-envelope-paper me-1"></i> Post Delivery
                        </span>
                        <span class="text-muted small fw-semibold">Postal Service</span>
                    </div>
                    <button type="button" 
                            class="btn btn-sm {{ $courierFilter === 'Post' ? 'btn-danger shadow-sm' : 'btn-outline-danger' }} rounded-pill px-2.5 py-0.5"
                            style="font-size: 11px;"
                            wire:click="setCourierFilter('Post')"
                            title="Filter orders list by Post">
                        <i class="bi bi-funnel-fill me-0.5"></i> {{ $courierFilter === 'Post' ? 'Active Filter' : 'Filter Post' }}
                    </button>
                </div>

                <div class="mb-3">
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Post Delivery Amount</div>
                    <h3 class="fw-bold text-danger mb-0 mt-1">Rs. {{ number_format($stats['postSalesAmount'], 2) }}</h3>
                    <div class="text-muted small mt-1" style="font-size: 11.5px;">
                        <span><strong class="text-dark">{{ $stats['postOrdersCount'] }}</strong> Total Parcels</span>
                        <span class="mx-1">&bull;</span>
                        <span>Courier Fee: <strong>Rs. {{ number_format($stats['postDeliveryCharges'], 2) }}</strong></span>
                    </div>
                </div>

                <div class="row g-2 pt-2 border-top text-center">
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-success bg-opacity-10 h-100">
                            <div class="text-success small fw-semibold" style="font-size: 10.5px;">Delivered</div>
                            <div class="fw-bold text-success fs-6 mt-0.5">Rs. {{ number_format($stats['postDeliveredAmount'], 0) }}</div>
                            <div class="text-muted" style="font-size: 10px;">{{ $stats['postDeliveredCount'] }} parcels</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-warning bg-opacity-10 h-100">
                            <div class="text-warning-emphasis small fw-semibold" style="font-size: 10.5px;">In Transit</div>
                            <div class="fw-bold text-dark fs-6 mt-0.5">Rs. {{ number_format($stats['postInTransitAmount'], 0) }}</div>
                            <div class="text-muted" style="font-size: 10px;">{{ $stats['postInTransitCount'] }} with Post</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-info bg-opacity-10 h-100">
                            <div class="text-info-emphasis small fw-semibold" style="font-size: 10.5px;">Collected</div>
                            <div class="fw-bold text-info fs-6 mt-0.5">Rs. {{ number_format($stats['postCollectedAmount'], 0) }}</div>
                            <div class="text-muted" style="font-size: 10px;">Due: Rs. {{ number_format($stats['postDueAmount'], 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Direct / In-House Handover Card (if present) --}}
        @if($stats['directOrdersCount'] > 0)
            <div class="col-12 col-md-12 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white h-100 position-relative border-top border-4 border-secondary">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary px-2.5 py-1 rounded-pill fw-semibold">
                                <i class="bi bi-person me-1"></i> Direct Handover
                            </span>
                            <span class="text-muted small fw-semibold">Rider / In-House</span>
                        </div>
                        <button type="button" 
                                class="btn btn-sm {{ strtolower($courierFilter) === 'direct' ? 'btn-secondary shadow-sm' : 'btn-outline-secondary' }} rounded-pill px-2.5 py-0.5"
                                style="font-size: 11px;"
                                wire:click="setCourierFilter('direct')"
                                title="Filter orders list by Direct Delivery">
                            <i class="bi bi-funnel-fill me-0.5"></i> {{ strtolower($courierFilter) === 'direct' ? 'Active Filter' : 'Filter Direct' }}
                        </button>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Direct Delivery Amount</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">Rs. {{ number_format($stats['directSalesAmount'], 2) }}</h3>
                        <div class="text-muted small mt-1" style="font-size: 11.5px;">
                            <span><strong class="text-dark">{{ $stats['directOrdersCount'] }}</strong> Total Parcels</span>
                            <span class="mx-1">&bull;</span>
                            <span>Direct Shop Delivery</span>
                        </div>
                    </div>

                    <div class="row g-2 pt-2 border-top text-center">
                        <div class="col-6">
                            <div class="p-2 rounded-3 bg-success bg-opacity-10 h-100">
                                <div class="text-success small fw-semibold" style="font-size: 10.5px;">Collected</div>
                                <div class="fw-bold text-success fs-6 mt-0.5">Rs. {{ number_format($stats['directCollectedAmount'], 0) }}</div>
                                <div class="text-muted" style="font-size: 10px;">Delivered: {{ $stats['directDeliveredCount'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 bg-danger bg-opacity-10 h-100">
                                <div class="text-danger small fw-semibold" style="font-size: 10.5px;">Balance Due</div>
                                <div class="fw-bold text-danger fs-6 mt-0.5">Rs. {{ number_format($stats['directDueAmount'], 0) }}</div>
                                <div class="text-muted" style="font-size: 10px;">Pending collection</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Interactive Sales & Collections Graph (Chart.js) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-graph-up-arrow text-primary me-2"></i> Delivery Sales & Collections Graph
                        </h5>
                        <p class="text-muted small mb-0">Daily delivery revenue (Domex vs Post vs Total) and payments collected over time</p>
                    </div>

                    {{-- Period Selectors --}}
                    <div class="btn-group btn-group-sm bg-light p-1 rounded-3" role="group">
                        <button type="button" class="btn rounded-2 {{ $chartPeriod === '7_days' ? 'btn-primary shadow-sm' : 'btn-light' }}"
                                wire:click="setChartPeriod('7_days')">
                            7 Days
                        </button>
                        <button type="button" class="btn rounded-2 {{ $chartPeriod === '14_days' ? 'btn-primary shadow-sm' : 'btn-light' }}"
                                wire:click="setChartPeriod('14_days')">
                            14 Days
                        </button>
                        <button type="button" class="btn rounded-2 {{ $chartPeriod === '30_days' ? 'btn-primary shadow-sm' : 'btn-light' }}"
                                wire:click="setChartPeriod('30_days')">
                            30 Days
                        </button>
                        <button type="button" class="btn rounded-2 {{ $chartPeriod === 'this_month' ? 'btn-primary shadow-sm' : 'btn-light' }}"
                                wire:click="setChartPeriod('this_month')">
                            This Month
                        </button>
                    </div>
                </div>

                {{-- Chart Canvas Container --}}
                <div class="position-relative" style="height: 330px;" wire:ignore>
                    <canvas id="deliverySalesChart"></canvas>
                </div>

                {{-- Legend & Summary footer --}}
                <div class="d-flex flex-wrap justify-content-center gap-3 mt-3 pt-3 border-top small text-muted">
                    <div class="d-flex align-items-center gap-1.5">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background-color: #161b97;"></span>
                        <span class="fw-semibold text-dark">Total Delivery Sales</span>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <span style="width: 16px; height: 3px; border-top: 2px dashed #0284c7;"></span>
                        <span class="fw-semibold text-primary">Domex Sales</span>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <span style="width: 16px; height: 3px; border-top: 2px dashed #dc2626;"></span>
                        <span class="fw-semibold text-danger">Post Sales</span>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background-color: #10b981;"></span>
                        <span class="fw-semibold text-success">Payments Collected</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Side Cards: Courier Distribution & Settlement Health --}}
        <div class="col-12 col-lg-4">
            <div class="d-flex flex-column gap-3 h-100">
                {{-- Courier Breakdown Card --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 flex-fill">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-truck text-primary"></i> Courier Breakdown
                        </h6>
                        <span class="text-muted small" style="font-size: 11px;">Domex vs Post</span>
                    </div>
                    <div class="d-flex flex-column gap-2.5">
                        {{-- Domex --}}
                        <div class="p-2.5 rounded-3 bg-light border-start border-4 border-primary">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary px-2 py-0.5 rounded">Domex</span>
                                    <span class="small fw-bold text-dark">Express Courier</span>
                                </div>
                                <span class="fw-bold text-primary">Rs. {{ number_format($stats['domexSalesAmount'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 11px;">
                                <span>{{ $stats['domexOrdersCount'] }} parcels &bull; Fee: Rs. {{ number_format($stats['domexDeliveryCharges'], 0) }}</span>
                                <span>Collected: <strong class="text-success">Rs. {{ number_format($stats['domexCollectedAmount'], 0) }}</strong></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small mt-1" style="font-size: 11px;">
                                <span>With Domex: <strong class="text-warning">Rs. {{ number_format($stats['domexInTransitAmount'], 0) }}</strong></span>
                                <span>Due: <strong class="text-danger">Rs. {{ number_format($stats['domexDueAmount'], 0) }}</strong></span>
                            </div>
                        </div>

                        {{-- Post --}}
                        <div class="p-2.5 rounded-3 bg-light border-start border-4 border-danger">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger px-2 py-0.5 rounded">Post</span>
                                    <span class="small fw-bold text-dark">Postal Service</span>
                                </div>
                                <span class="fw-bold text-danger">Rs. {{ number_format($stats['postSalesAmount'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 11px;">
                                <span>{{ $stats['postOrdersCount'] }} parcels &bull; Fee: Rs. {{ number_format($stats['postDeliveryCharges'], 0) }}</span>
                                <span>Collected: <strong class="text-success">Rs. {{ number_format($stats['postCollectedAmount'], 0) }}</strong></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small mt-1" style="font-size: 11px;">
                                <span>With Post: <strong class="text-warning">Rs. {{ number_format($stats['postInTransitAmount'], 0) }}</strong></span>
                                <span>Due: <strong class="text-danger">Rs. {{ number_format($stats['postDueAmount'], 0) }}</strong></span>
                            </div>
                        </div>

                        {{-- Direct / In-house --}}
                        <div class="p-2.5 rounded-3 bg-light border-start border-4 border-secondary">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary px-2 py-0.5 rounded">Direct</span>
                                    <span class="small fw-bold text-dark">Rider / Handover</span>
                                </div>
                                <span class="fw-bold text-dark">Rs. {{ number_format($stats['directSalesAmount'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 11px;">
                                <span>{{ $stats['directOrdersCount'] }} parcels</span>
                                <span>Collected: <strong class="text-success">Rs. {{ number_format($stats['directCollectedAmount'], 0) }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cash Settlement Overview Card --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 flex-fill">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart text-success"></i> Collection Breakdown
                    </h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Cash Collected:</span>
                            <span class="fw-bold text-success">Rs. {{ number_format($stats['cashCollected'], 2) }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            @php
                                $totalCollected = $stats['totalCollectedCod'] > 0 ? $stats['totalCollectedCod'] : 1;
                                $cashPercent = round(($stats['cashCollected'] / $totalCollected) * 100);
                            @endphp
                            <div class="progress-bar bg-success" style="width: {{ $cashPercent }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Bank / Online Paid:</span>
                            <span class="fw-bold text-info">Rs. {{ number_format($stats['bankCollected'], 2) }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            @php
                                $bankPercent = round(($stats['bankCollected'] / $totalCollected) * 100);
                            @endphp
                            <div class="progress-bar bg-info" style="width: {{ $bankPercent }}%"></div>
                        </div>
                    </div>

                    <div class="pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Recorded Expenses:</span>
                            <span class="fw-bold text-danger">Rs. {{ number_format($stats['totalExpenses'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold text-dark small">Cash to Handover:</span>
                            <span class="fw-bold text-dark fs-6">Rs. {{ number_format($stats['netCashInHand'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delivery Financial Orders & Ledger Table --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        {{-- Filter Bar --}}
        <div class="p-3 border-bottom bg-light bg-opacity-50">
            <div class="row g-2 align-items-center mb-2">
                {{-- Search --}}
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" wire:model.live.debounce.300ms="search" 
                               placeholder="Search invoice, customer, tracking...">
                    </div>
                </div>

                {{-- Delivery Status Filter --}}
                <div class="col-6 col-sm-4 col-md-2">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="delivered">Delivered (Completed)</option>
                        <option value="in_transit">In Transit (Courier)</option>
                        <option value="pending">Pending Pack / Packed</option>
                        <option value="returned">Returned / Cancelled</option>
                    </select>
                </div>

                {{-- Payment Status Filter --}}
                <div class="col-6 col-sm-4 col-md-2">
                    <select class="form-select" wire:model.live="paymentFilter">
                        <option value="all">All Payments</option>
                        <option value="paid">Fully Paid / Collected</option>
                        <option value="due">COD Pending (Due)</option>
                        <option value="partial">Partially Paid</option>
                    </select>
                </div>

                {{-- Courier Filter --}}
                <div class="col-6 col-sm-4 col-md-2">
                    <select class="form-select" wire:model.live="courierFilter">
                        <option value="all">All Couriers</option>
                        <option value="Domex">Domex</option>
                        <option value="Post">Post</option>
                        <option value="direct">Direct / In-House</option>
                    </select>
                </div>

                {{-- Reset Button --}}
                <div class="col-6 col-sm-4 col-md-2 text-end">
                    <button class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-1.5" wire:click="resetFilters" title="Reset All Filters">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                    </button>
                </div>
            </div>

            {{-- Quick Courier Selector Pills --}}
            <div class="d-flex align-items-center gap-1.5 flex-wrap my-2 pt-2 border-top">
                <span class="text-muted small fw-semibold me-1"><i class="bi bi-truck me-1"></i> Quick Courier:</span>
                <button type="button" class="btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold {{ $courierFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}"
                        wire:click="setCourierFilter('all')">
                    All (Rs. {{ number_format($stats['totalDeliverySales'], 0) }})
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold {{ $courierFilter === 'Domex' ? 'btn-primary' : 'btn-outline-primary' }}"
                        wire:click="setCourierFilter('Domex')">
                    <i class="bi bi-truck me-1"></i> Domex: Rs. {{ number_format($stats['domexSalesAmount'], 0) }}
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold {{ $courierFilter === 'Post' ? 'btn-danger' : 'btn-outline-danger' }}"
                        wire:click="setCourierFilter('Post')">
                    <i class="bi bi-envelope-paper me-1"></i> Post: Rs. {{ number_format($stats['postSalesAmount'], 0) }}
                </button>
                @if($stats['directOrdersCount'] > 0)
                    <button type="button" class="btn btn-sm rounded-pill px-2.5 py-0.5 fw-semibold {{ strtolower($courierFilter) === 'direct' ? 'btn-secondary' : 'btn-outline-secondary' }}"
                            wire:click="setCourierFilter('direct')">
                        <i class="bi bi-person me-1"></i> Direct: Rs. {{ number_format($stats['directSalesAmount'], 0) }}
                    </button>
                @endif
            </div>

            {{-- Date Range & Same-Day Filter Sub-Bar --}}
            <div class="pt-2 border-top">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    
                    {{-- Quick Date Presets --}}
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span class="text-muted small fw-semibold me-1"><i class="bi bi-calendar3 me-1"></i> Date Filter:</span>
                        <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $dateFilter === 'all' && empty($dateFrom) && empty($dateTo) ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="setQuickDate('all')">
                            All Time
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 {{ $dateFilter === 'today' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="setQuickDate('today')">
                            Today
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
                    </div>

                    {{-- Exact Date Pickers (From / To / Same Date) --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        {{-- Date Field Selector --}}
                        <div class="input-group input-group-sm" style="width: auto;">
                            <span class="input-group-text bg-white text-muted" style="font-size: 11px;">Filter By</span>
                            <select class="form-select form-select-sm" wire:model.live="dateField" style="font-size: 11px;">
                                <option value="created_at">Order Date</option>
                                <option value="delivered_at">Delivery Date</option>
                            </select>
                        </div>

                        {{-- From Date --}}
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-muted small">From:</span>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom" style="width: 140px;">
                        </div>

                        {{-- To Date --}}
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-muted small">To:</span>
                            <input type="date" class="form-control form-control-sm" wire:model.live="dateTo" style="width: 140px;">
                        </div>

                        @if(!empty($dateFrom))
                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill py-0.5 px-2"
                                    wire:click="setSameDate('{{ $dateFrom }}')"
                                    title="View sales on this exact date only">
                                <i class="bi bi-calendar-check me-0.5"></i> Same Day Only
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Filter Period Summary Banner --}}
                @if(!empty($dateFrom) || !empty($dateTo) || in_array($dateFilter, ['today', 'yesterday', 'week', 'month', 'custom']))
                    <div class="mt-2.5 p-2 px-3 rounded-3 bg-white border d-flex align-items-center justify-content-between flex-wrap gap-2 text-dark small">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-primary px-2 py-1 rounded-pill">
                                <i class="bi bi-funnel-fill me-1"></i> Active Period
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
                            <span><i class="bi bi-box-seam me-1 text-muted"></i> Orders: <strong>{{ $filteredSummary['ordersCount'] }}</strong></span>
                            <span><i class="bi bi-cash-stack me-1 text-primary"></i> Total: <strong>Rs. {{ number_format($filteredSummary['totalAmount'], 2) }}</strong></span>
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
                            <span><i class="bi bi-exclamation-circle me-1 text-danger"></i> COD Due: <strong>Rs. {{ number_format($filteredSummary['totalDue'], 2) }}</strong></span>
                            <span><i class="bi bi-check2-circle me-1 text-success"></i> Settled: <strong>Rs. {{ number_format($filteredSummary['totalCollected'], 2) }}</strong></span>
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
                        <th class="ps-3 py-3">Date / Invoice</th>
                        <th class="py-3">Customer</th>
                        <th class="py-3">Courier / Tracking</th>
                        <th class="text-end py-3">Order Amount</th>
                        <th class="text-end py-3">Delivery Fee</th>
                        <th class="text-end py-3">Total Payable</th>
                        <th class="text-end py-3">Collected</th>
                        <th class="text-end py-3">COD Due</th>
                        <th class="text-center py-3">Delivery</th>
                        <th class="text-center py-3">Payment</th>
                        <th class="text-end pe-3 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $cName = $order->customer ? $order->customer->name : ($order->walking_customer_name ?: 'Customer');
                            $cPhone = $order->customer ? $order->customer->phone : ($order->walking_customer_phone ?: '');
                            $cAddress = $order->customer ? $order->customer->address : ($order->deliverySale?->customer_details ?: '');
                            $courier = $order->deliverySale?->delivery_method ?? 'Domex';
                            $isThirdPartyCourier = in_array(strtolower($courier), ['domex', 'post']);
                            $isDelivered = $order->delivery_status === 'delivered';
                            $isInTransitCourier = $isThirdPartyCourier && in_array($order->delivery_status, ['in_transit', 'packed', 'pending']);
                            $tracking = $order->deliverySale?->tracking_number;
                            $delCharge = (float) ($order->deliverySale?->delivery_charge ?? 0);
                            $isPaid = (float) $order->due_amount <= 0;
                            $collectedAmount = (float) $order->payments->sum('amount');
                        @endphp
                        <tr>
                            {{-- Date & Invoice --}}
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $order->invoice_number ?: $order->sale_id }}</div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    {{ $order->created_at->format('M d, Y h:i A') }}
                                </div>
                                <span class="badge bg-light text-muted border px-1.5 py-0.5 rounded-pill mt-1" style="font-size: 10px;">
                                    {{ ucfirst($order->order_source) }}
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
                                    <div class="text-muted text-truncate" style="max-width: 180px; font-size: 11px;" title="{{ $cAddress }}">
                                        <i class="bi bi-geo-alt me-0.5"></i>{{ $cAddress }}
                                    </div>
                                @endif
                            </td>

                            {{-- Courier & Tracking --}}
                            <td>
                                <div class="d-flex align-items-center gap-1.5 mb-0.5">
                                    <span class="badge {{ $courier === 'Domex' ? 'bg-primary' : ($courier === 'Post' ? 'bg-danger' : 'bg-secondary') }} px-2 py-0.5">
                                        {{ $courier }}
                                    </span>
                                </div>
                                @if($tracking)
                                    @if(strcasecmp($courier, 'Domex') === 0)
                                        <a href="https://domex.lk/Order-Details.php?wbno={{ $tracking }}" target="_blank"
                                           class="text-primary text-decoration-none small font-monospace fw-bold d-inline-flex align-items-center"
                                           title="Open Domex live tracking in new tab">
                                            <i class="bi bi-truck me-1"></i>{{ $tracking }} <i class="bi bi-box-arrow-up-right ms-1 small" style="font-size: 9px;"></i>
                                        </a>
                                    @else
                                        <div class="text-dark small font-monospace fw-semibold" style="font-size: 11px;">
                                            <i class="bi bi-upc-scan me-1 text-muted"></i>{{ $tracking }}
                                        </div>
                                    @endif
                                    @if($order->deliverySale?->courier_status_detail)
                                        <div class="mt-0.5">
                                            <span class="badge bg-light text-dark border" style="font-size: 9.5px;" title="Live Domex Status">
                                                {{ $order->deliverySale->courier_status_detail }}
                                            </span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted small fst-italic" style="font-size: 11px;">No Tracking #</span>
                                @endif
                            </td>

                            {{-- Order Amount (Subtotal) --}}
                            <td class="text-end fw-semibold text-dark">
                                Rs. {{ number_format($order->subtotal ?? ($order->total_amount - $delCharge), 2) }}
                            </td>

                            {{-- Delivery Fee --}}
                            <td class="text-end text-muted small">
                                Rs. {{ number_format($delCharge, 2) }}
                            </td>

                            {{-- Total Payable --}}
                            <td class="text-end fw-bold text-dark">
                                Rs. {{ number_format($order->total_amount, 2) }}
                            </td>

                            {{-- Collected Amount --}}
                            <td class="text-end fw-bold text-success">
                                Rs. {{ number_format($collectedAmount, 2) }}
                            </td>

                            {{-- COD Due --}}
                            <td class="text-end">
                                @if($order->due_amount > 0)
                                    @if($isInTransitCourier)
                                        <span class="fw-bold text-primary">Rs. {{ number_format($order->due_amount, 2) }}</span>
                                        <div class="text-muted" style="font-size: 10px;" title="Cash with courier until delivered">
                                            <i class="bi bi-truck me-0.5"></i>With {{ $courier }}
                                        </div>
                                    @else
                                        <span class="fw-bold text-danger">Rs. {{ number_format($order->due_amount, 2) }}</span>
                                    @endif
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-semibold">
                                        <i class="bi bi-check2 me-0.5"></i> Settled
                                    </span>
                                @endif
                            </td>

                            {{-- Delivery Status --}}
                            <td class="text-center">
                                @if($order->delivery_status === 'delivered')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-check2-circle me-1"></i> Delivered
                                    </span>
                                @elseif($order->delivery_status === 'in_transit')
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-truck me-1"></i> In Transit
                                    </span>
                                @elseif($order->delivery_status === 'packed')
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-box-seam me-1"></i> Packed
                                    </span>
                                @elseif(in_array($order->delivery_status, ['returned', 'cancelled']))
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-x-circle me-1"></i> {{ ucfirst($order->delivery_status) }}
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </td>

                            {{-- Payment Status --}}
                            <td class="text-center">
                                @if($isPaid)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-check-lg me-0.5"></i> Paid
                                    </span>
                                @elseif($order->payment_status === 'partial')
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        Partial
                                    </span>
                                @elseif($isInTransitCourier)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold" title="Parcel is in courier transit - customer pays courier upon delivery">
                                        <i class="bi bi-truck me-1"></i> With Courier
                                    </span>
                                @elseif($isThirdPartyCourier && $isDelivered)
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold" title="Delivered to customer. Settlement payout pending from {{ $courier }} service center.">
                                        <i class="bi bi-building me-1 text-info"></i> Remittance Due
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                        COD Pending
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- Collect / Remittance / In-Transit Status --}}
                                    @if($order->due_amount > 0)
                                        @if($isInTransitCourier)
                                            {{-- Parcel is with courier in transit: shop cannot collect from customer --}}
                                            <span class="badge bg-light text-muted border px-2 py-1.5 rounded d-inline-flex align-items-center"
                                                  title="Parcel is with {{ $courier }} (in transit). Cash will be collected by courier upon delivery. Payout will be received from service center.">
                                                <i class="bi bi-hourglass-split me-1 text-primary"></i>
                                                <span class="d-none d-xl-inline">With {{ $courier }}</span>
                                            </span>
                                        @elseif($isThirdPartyCourier && $isDelivered)
                                            {{-- Courier has delivered; ready to receive payout from courier service center --}}
                                            <button class="btn btn-sm btn-info text-white px-2 py-1 shadow-sm d-flex align-items-center gap-1"
                                                    wire:click="openPaymentModal({{ $order->id }})"
                                                    title="Receive COD Remittance from {{ $courier }} Service Center">
                                                <i class="bi bi-building-down"></i>
                                                <span class="d-none d-xl-inline">From {{ $courier }}</span>
                                            </button>
                                        @elseif(in_array($order->delivery_status, ['returned', 'cancelled']))
                                            {{-- Returned / cancelled parcel: no collection --}}
                                            <span class="badge bg-danger bg-opacity-10 text-danger border px-2 py-1.5 rounded" title="Order was returned or cancelled">
                                                <i class="bi bi-x-circle me-1"></i> Returned
                                            </span>
                                        @else
                                            {{-- Direct / In-house rider delivery doorstep collection --}}
                                            <button class="btn btn-sm btn-success px-2 py-1 shadow-sm d-flex align-items-center gap-1"
                                                    wire:click="openPaymentModal({{ $order->id }})" title="Collect COD Payment">
                                                <i class="bi bi-cash-stack"></i>
                                                <span class="d-none d-xl-inline">Collect</span>
                                            </button>
                                        @endif
                                    @endif

                                    {{-- Order Details Button --}}
                                    <button class="btn btn-sm btn-outline-secondary px-2 py-1"
                                            wire:click="openDetailsModal({{ $order->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- Print Delivery Label --}}
                                    <a href="{{ route('delivery.print.delivery-label', $order->id) }}" target="_blank"
                                       class="btn btn-sm btn-outline-dark px-2 py-1" title="Print Shipping Label">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <h6 class="fw-semibold text-dark">No delivery orders found</h6>
                                    <p class="small mb-3">Try adjusting your date range, search query, or status filters.</p>
                                    <button class="btn btn-sm btn-outline-primary" wire:click="resetFilters">
                                        Reset All Filters
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} delivery records
                </div>
                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- ==================== MODAL: Collect COD Payment / Receive Remittance ==================== --}}
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
                                <small class="text-muted">Settling COD funds from {{ $modalCourier }} Service Center &bull; Tracking: {{ $paymentSale->deliverySale?->tracking_number ?? 'N/A' }}</small>
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
                                {{ $modalIsCourier ? 'Remittance Payout Received (Rs.)' : 'Amount to Collect (Rs.)' }} <span class="text-danger">*</span>
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
                                    <input class="form-check-input" type="radio" wire:model="paymentMethod" value="bank_transfer" id="payMethodBank">
                                    <label class="form-check-label fw-medium" for="payMethodBank">
                                        <i class="bi bi-bank me-1 text-primary"></i> Bank Transfer / Deposit
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="paymentMethod" value="cash" id="payMethodCash">
                                    <label class="form-check-label fw-medium" for="payMethodCash">
                                        <i class="bi bi-cash me-1 text-success"></i> Cash (Service Center / Handover)
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
                            {{ $modalIsCourier ? 'Confirm Remittance Received' : 'Confirm & Collect' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== MODAL: Order Details ==================== --}}
    @if($showDetailsModal && $selectedSale)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3 px-4">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">
                                <i class="bi bi-receipt text-primary me-2"></i> Delivery Order Details
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
                                        <span class="text-muted">Barcode:</span>
                                        <span class="font-monospace text-dark">{{ $selectedSale->deliverySale?->delivery_barcode ?? 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Tracking #:</span>
                                        <span class="fw-bold text-primary">{{ $selectedSale->deliverySale?->tracking_number ?? 'Pending' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Status:</span>
                                        <span class="badge bg-primary">{{ ucfirst($selectedSale->delivery_status) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Line Items Table --}}
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-bag-check me-1"></i> Purchased Items</h6>
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
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Discounts:</span>
                                <span class="text-danger">- Rs. {{ number_format($selectedSale->discount_amount ?? 0, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top fw-bold">
                                <span>Grand Total:</span>
                                <span class="fs-6 text-dark">Rs. {{ number_format($selectedSale->total_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-1 text-success small">
                                <span>Total Paid:</span>
                                <span class="fw-bold">Rs. {{ number_format($selectedSale->payments->sum('amount'), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-1 text-danger fw-bold">
                                <span>Balance Due (COD):</span>
                                <span>Rs. {{ number_format($selectedSale->due_amount, 2) }}</span>
                            </div>
                        </div>

                        {{-- Payment History --}}
                        @if($selectedSale->payments->count() > 0)
                            <h6 class="fw-bold text-dark mt-4 mb-2"><i class="bi bi-clock-history me-1"></i> Payment History</h6>
                            <div class="table-responsive border rounded-3">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th class="ps-3 py-2">Date</th>
                                            <th class="py-2">Method</th>
                                            <th class="py-2">Notes</th>
                                            <th class="text-end pe-3 py-2">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedSale->payments as $payment)
                                            <tr>
                                                <td class="ps-3 small">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y h:i A') : $payment->created_at->format('M d, Y') }}</td>
                                                <td class="small"><span class="badge bg-secondary">{{ ucfirst($payment->payment_method) }}</span></td>
                                                <td class="small text-muted">{{ $payment->notes ?: '-' }}</td>
                                                <td class="text-end pe-3 fw-bold text-success">Rs. {{ number_format($payment->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer border-top py-3 px-4">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">Close</button>
                        <a href="{{ route('delivery.print.delivery-label', $selectedSale->id) }}" target="_blank" class="btn btn-outline-dark">
                            <i class="bi bi-printer me-1"></i> Shipping Label
                        </a>
                        @if($selectedSale->due_amount > 0)
                            <button type="button" class="btn btn-success" wire:click="openPaymentModal({{ $selectedSale->id }})">
                                <i class="bi bi-cash-stack me-1"></i> Collect Payment
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== MODAL: Daily Cash Settlement / Handover ==================== --}}
    @if($showSettlementModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3 px-4">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">
                                <i class="bi bi-receipt-cutoff text-primary me-2"></i> Daily Cash Handover Settlement
                            </h5>
                            <small class="text-muted">Date: {{ now()->format('M d, Y') }} | Delivery Staff: {{ auth()->user()->name }}</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeSettlementModal"></button>
                    </div>

                    <div class="modal-body p-4" id="printableSettlementSlip">
                        <div class="text-center pb-3 border-bottom mb-3">
                            <h5 class="fw-bold text-dark mb-0">Jaffna Gold Jewelry</h5>
                            <div class="small text-muted">Delivery Personnel Daily Cash Settlement Slip</div>
                        </div>

                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Staff Name:</span>
                                <span class="fw-bold text-dark">{{ auth()->user()->name }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Staff Email:</span>
                                <span class="font-monospace text-dark small">{{ auth()->user()->email }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Settlement Date:</span>
                                <span class="fw-semibold text-dark">{{ now()->format('d/m/Y h:i A') }}</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 bg-light mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Today's COD Collected (Cash):</span>
                                <span class="fw-bold text-success fs-6">Rs. {{ number_format($stats['todayCollectedCod'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Today's Staff Expenses:</span>
                                <span class="fw-bold text-danger fs-6">- Rs. {{ number_format($stats['todayExpenses'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="fw-bold text-dark">Today's Net Cash to Remit:</span>
                                <span class="fw-bold text-dark fs-5">Rs. {{ number_format($stats['todayNetCashInHand'], 2) }}</span>
                            </div>
                        </div>

                        {{-- Today's Courier Sales Split --}}
                        <div class="p-3 rounded-3 bg-light mb-3">
                            <div class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 11px;">
                                <i class="bi bi-truck me-1"></i> Today's Courier Orders Breakdown
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                                <span class="text-dark"><i class="bi bi-circle-fill text-primary me-1" style="font-size: 8px;"></i> Domex Sales Today:</span>
                                <span class="fw-bold text-primary">Rs. {{ number_format($stats['domexTodaySales'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                                <span class="text-dark"><i class="bi bi-circle-fill text-danger me-1" style="font-size: 8px;"></i> Post Sales Today:</span>
                                <span class="fw-bold text-danger">Rs. {{ number_format($stats['postTodaySales'], 2) }}</span>
                            </div>
                            @if(($stats['directTodaySales'] ?? 0) > 0)
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-dark"><i class="bi bi-circle-fill text-secondary me-1" style="font-size: 8px;"></i> Direct Handover Today:</span>
                                    <span class="fw-bold text-dark">Rs. {{ number_format($stats['directTodaySales'], 2) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-3 rounded-3 border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Total All-Time Cash Collected:</span>
                                <span class="fw-semibold text-dark">Rs. {{ number_format($stats['cashCollected'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Total All-Time Expenses:</span>
                                <span class="fw-semibold text-danger">- Rs. {{ number_format($stats['totalExpenses'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                                <span class="fw-bold text-dark small">Total Net Remittance:</span>
                                <span class="fw-bold text-dark">Rs. {{ number_format($stats['netCashInHand'], 2) }}</span>
                            </div>
                        </div>

                        <div class="row pt-4 text-center">
                            <div class="col-6">
                                <div class="border-top pt-1 small text-muted">Delivery Personnel</div>
                            </div>
                            <div class="col-6">
                                <div class="border-top pt-1 small text-muted">Cash Counter / Admin</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-3 px-4">
                        <button type="button" class="btn btn-secondary" wire:click="closeSettlementModal">Close</button>
                        <button type="button" class="btn btn-dark" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print Settlement Slip
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    let deliveryChartInstance = null;

    function renderDeliverySalesChart(chartData) {
        const canvas = document.getElementById('deliverySalesChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        if (deliveryChartInstance) {
            deliveryChartInstance.destroy();
        }

        // Create gradients
        const gradientSales = ctx.createLinearGradient(0, 0, 0, 300);
        gradientSales.addColorStop(0, 'rgba(22, 27, 151, 0.20)');
        gradientSales.addColorStop(1, 'rgba(22, 27, 151, 0.0)');

        const gradientCollected = ctx.createLinearGradient(0, 0, 0, 300);
        gradientCollected.addColorStop(0, 'rgba(16, 185, 129, 0.20)');
        gradientCollected.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        deliveryChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Total Delivery Sales (Rs.)',
                        data: chartData.sales,
                        borderColor: '#161b97',
                        backgroundColor: gradientSales,
                        borderWidth: 3,
                        pointBackgroundColor: '#161b97',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: false,
                        tension: 0.35,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Domex Sales (Rs.)',
                        data: chartData.domex || [],
                        borderColor: '#0284c7',
                        borderWidth: 2,
                        borderDash: [5, 4],
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5,
                        pointRadius: 3.5,
                        pointHoverRadius: 5.5,
                        fill: false,
                        tension: 0.35,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Post Sales (Rs.)',
                        data: chartData.post || [],
                        borderColor: '#dc2626',
                        borderWidth: 2,
                        borderDash: [3, 3],
                        pointBackgroundColor: '#dc2626',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5,
                        pointRadius: 3.5,
                        pointHoverRadius: 5.5,
                        fill: false,
                        tension: 0.35,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Payments Collected (Rs.)',
                        data: chartData.collections,
                        borderColor: '#10b981',
                        backgroundColor: gradientCollected,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rs. ' + Number(context.raw).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11, weight: '500' },
                            color: '#6b7280'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6',
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 11 },
                            color: '#6b7280',
                            callback: function(value) {
                                if (value >= 1000) return 'Rs. ' + (value / 1000).toFixed(0) + 'k';
                                return 'Rs. ' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    function initDeliveryChart() {
        const initialData = @json($chartData);
        renderDeliverySalesChart(initialData);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDeliveryChart);
    } else {
        initDeliveryChart();
    }
    document.addEventListener('livewire:navigated', initDeliveryChart);

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('update-sales-chart', (event) => {
            const data = event.data || (event[0] ? event[0].data : null);
            if (data) {
                renderDeliverySalesChart(data);
            }
        });

        Livewire.on('show-toast', (event) => {
            const type = event.type || (event[0] ? event[0].type : 'info');
            const message = event.message || (event[0] ? event[0].message : '');
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: type,
                    title: message,
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                alert(message);
            }
        });
    });

    window.addEventListener('resize', function() {
        if (deliveryChartInstance) {
            deliveryChartInstance.resize();
        }
    });
</script>
<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #printableSettlementSlip, #printableSettlementSlip * {
        visibility: visible !important;
    }
    #printableSettlementSlip {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        padding: 24px !important;
        background: #ffffff !important;
        color: #000000 !important;
        box-shadow: none !important;
    }
    .modal-backdrop, .btn-close, .modal-footer, header, nav, aside {
        display: none !important;
    }
}
</style>
@endpush
