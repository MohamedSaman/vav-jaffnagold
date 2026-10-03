<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-bar-chart-line-fill me-1"></i> Sales Analytics
                </span>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-columns-gap me-1"></i> 2-Column Comparison
                </span>
            </div>
            <h3 class="fw-bold text-dark mb-1">
                Sold Products Overview
            </h3>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-printer"></i> Print Summary
            </button>
            <a href="{{ route('shop-staff.store-billing') }}" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-cart-plus"></i> New Sale
            </a>
        </div>
    </div>

    {{-- Executive KPI Metrics Cards --}}
    <div class="row g-3 mb-4">
        {{-- Total Sales Revenue --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-white h-100 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #161b97 0%, #0d1066 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-white-50 text-uppercase fw-semibold small">Overall Sold Products</span>
                        <span class="p-2 rounded-3 bg-white bg-opacity-10 text-white">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1">Rs. {{ number_format($overallTotalAmount, 2) }}</h3>
                    <div class="text-white-50 small d-flex align-items-center gap-2 mt-2">
                        <span class="badge bg-white text-primary fw-bold">{{ $overallTotalQty }} Units</span>
                        <span>across {{ $overallUniqueCount }} unique items</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Local Handmade Sales --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-emerald"
                 style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-success px-2 py-1 text-uppercase fw-bold" style="font-size: 0.7rem;">
                                🇱🇰 Local (Handmade)
                            </span>
                        </div>
                        <span class="p-2 rounded-3 bg-success bg-opacity-20 text-success">
                            <i class="bi bi-hammer fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold text-success mb-1">Rs. {{ number_format($localTotalAmount, 2) }}</h3>
                    <div class="small text-muted d-flex align-items-center gap-2 mt-2">
                        <span class="badge bg-success bg-opacity-20 text-success fw-bold">{{ $localTotalQty }} Units</span>
                        <span class="fw-semibold text-dark">{{ $localShare }}% of Total Sales</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Indian Imported Sales --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-indigo"
                 style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-primary px-2 py-1 text-uppercase fw-bold" style="font-size: 0.7rem;">
                                🇮🇳 Indian (Imported)
                            </span>
                        </div>
                        <span class="p-2 rounded-3 bg-primary bg-opacity-20 text-primary">
                            <i class="bi bi-box-seam fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-bold text-primary mb-1">Rs. {{ number_format($indianTotalAmount, 2) }}</h3>
                    <div class="small text-muted d-flex align-items-center gap-2 mt-2">
                        <span class="badge bg-primary bg-opacity-20 text-primary fw-bold">{{ $indianTotalQty }} Units</span>
                        <span class="fw-semibold text-dark">{{ $indianShare }}% of Total Sales</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Selling Highlights --}}
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-purple text-uppercase fw-bold small text-muted">🏆 Top Performers</span>
                        <span class="p-2 rounded-3 bg-warning bg-opacity-20 text-warning">
                            <i class="bi bi-trophy-fill fs-5"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column gap-2 mt-1">
                        <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded-3 shadow-xs">
                            <div class="text-truncate me-2" style="max-width: 140px;">
                                <span class="badge bg-success-subtle text-success p-1 me-1">Local</span>
                                <strong class="small text-dark">{{ $topLocalProduct->product_name ?? 'No sales yet' }}</strong>
                            </div>
                            <span class="badge bg-success text-white">{{ $topLocalProduct->total_qty_sold ?? 0 }} sold</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded-3 shadow-xs">
                            <div class="text-truncate me-2" style="max-width: 140px;">
                                <span class="badge bg-primary-subtle text-primary p-1 me-1">Indian</span>
                                <strong class="small text-dark">{{ $topIndianProduct->product_name ?? 'No sales yet' }}</strong>
                            </div>
                            <span class="badge bg-primary text-white">{{ $topIndianProduct->total_qty_sold ?? 0 }} sold</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Period Bar --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            {{-- Quick Date Presets --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 mb-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted small fw-semibold me-1"><i class="bi bi-calendar3 me-1"></i> Period:</span>
                    <button type="button" wire:click="applyDatePreset('today')" 
                            class="btn btn-sm rounded-pill {{ $datePreset === 'today' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Today
                    </button>
                    <button type="button" wire:click="applyDatePreset('yesterday')" 
                            class="btn btn-sm rounded-pill {{ $datePreset === 'yesterday' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Yesterday
                    </button>
                    <button type="button" wire:click="applyDatePreset('this_week')" 
                            class="btn btn-sm rounded-pill {{ $datePreset === 'this_week' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        This Week
                    </button>
                    <button type="button" wire:click="applyDatePreset('this_month')" 
                            class="btn btn-sm rounded-pill {{ $datePreset === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        This Month
                    </button>
                    <button type="button" wire:click="applyDatePreset('last_month')" 
                            class="btn btn-sm rounded-pill {{ $datePreset === 'last_month' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Last Month
                    </button>
                    <button type="button" wire:click="applyDatePreset('all_time')" 
                            class="btn btn-sm rounded-pill {{ $datePreset === 'all_time' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        All Time
                    </button>
                </div>

                {{-- Column View Toggler for Mobile / Small Screens --}}
                <div class="d-flex align-items-center gap-1">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" wire:click="$set('activeTab', 'all')" 
                                class="btn {{ $activeTab === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                            <i class="bi bi-columns-gap me-1"></i> Both Columns
                        </button>
                        <button type="button" wire:click="$set('activeTab', 'local')" 
                                class="btn {{ $activeTab === 'local' ? 'btn-success' : 'btn-outline-secondary' }}">
                            🇱🇰 Local Only
                        </button>
                        <button type="button" wire:click="$set('activeTab', 'indian')" 
                                class="btn {{ $activeTab === 'indian' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            🇮🇳 Indian Only
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search & Secondary Filters --}}
            <div class="row g-3 align-items-center">
                {{-- Search Bar --}}
                <div class="col-lg-4 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Search product name, code, model...">
                        @if($search)
                        <button wire:click="$set('search', '')" class="btn btn-outline-secondary border-start-0" type="button">
                            <i class="bi bi-x"></i>
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Category Filter --}}
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select wire:model.live="categoryFilter" class="form-select">
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort By --}}
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <select wire:model.live="sortBy" class="form-select">
                        <option value="qty_desc">🔥 Highest Quantity Sold (Default)</option>
                        <option value="amount_desc">💰 Highest Total Revenue</option>
                        <option value="name_asc">🔤 Product Name (A - Z)</option>
                    </select>
                </div>

                {{-- Custom Date Range Pickers --}}
                <div class="col-lg-3 col-md-12">
                    <div class="d-flex align-items-center gap-1">
                        <input type="date" wire:model.live="startDate" class="form-control form-control-sm" placeholder="Start Date" title="From Date">
                        <span class="text-muted small">to</span>
                        <input type="date" wire:model.live="endDate" class="form-control form-control-sm" placeholder="End Date" title="To Date">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Two-Column View: Local (Handmade) vs Indian (Imported) --}}
    <div class="row g-4">
        {{-- ============================================================
             COLUMN 1: LOCAL (HANDMADE) SOLD PRODUCTS
        ============================================================ --}}
        @if($activeTab === 'all' || $activeTab === 'local')
        <div class="{{ $activeTab === 'all' ? 'col-lg-6' : 'col-12' }}">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                {{-- Column Header Banner --}}
                <div class="card-header bg-white border-bottom p-3 p-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success p-2 rounded-3 text-white fs-6">
                                🇱🇰
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">
                                    Local (Handmade) Sold Items
                                </h5>
                                <small class="text-muted">Aasari & workshop handcrafted jewelry</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill fs-6">
                                {{ $localTotalQty }} Sold
                            </span>
                            <div class="fw-bold text-dark mt-1">Rs. {{ number_format($localTotalAmount, 2) }}</div>
                        </div>
                    </div>

                    {{-- Column Summary Pills --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top small text-muted">
                        <span><strong>{{ $localUniqueCount }}</strong> Unique Items Sold</span>
                        <span>•</span>
                        <span>Share: <strong class="text-success">{{ $localShare }}%</strong> of Total Revenue</span>
                        @if($topLocalProduct)
                        <span>•</span>
                        <span>Top: <strong>{{ $topLocalProduct->product_name }}</strong> ({{ $topLocalProduct->total_qty_sold }})</span>
                        @endif
                    </div>
                </div>

                {{-- Column Table Body --}}
                <div class="card-body p-0">
                    @if($localProducts->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="text-muted mb-2 opacity-50" style="font-size: 3rem;">
                                <i class="bi bi-hammer"></i>
                            </div>
                            <h6 class="fw-bold text-secondary">No Local Handmade Items Sold</h6>
                            <p class="text-muted small mb-0">No sales recorded for local handmade jewelry in this selected period.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase small text-muted">
                                    <tr>
                                        <th class="ps-3 text-center" style="width: 50px;">Rank</th>
                                        <th>Product</th>
                                        <th class="text-center">Sold Qty</th>
                                        <th class="text-end">Total Amount</th>
                                        <th class="text-center">Stock</th>
                                        <th class="pe-3 text-center" style="width: 70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $maxLocalQty = $localProducts->max('total_qty_sold') ?: 1;
                                    @endphp
                                    @foreach($localProducts as $item)
                                    <tr>
                                        {{-- Rank --}}
                                        <td class="ps-3 text-center">
                                            @if($item->rank === 1)
                                                <span class="badge rounded-circle bg-warning text-dark p-2" title="Top Selling Local Product" style="width: 28px; height: 28px; line-height: 14px;">🥇</span>
                                            @elseif($item->rank === 2)
                                                <span class="badge rounded-circle bg-secondary-subtle text-dark p-2" title="2nd Best Seller" style="width: 28px; height: 28px; line-height: 14px;">🥈</span>
                                            @elseif($item->rank === 3)
                                                <span class="badge rounded-circle bg-warning-subtle text-dark p-2" title="3rd Best Seller" style="width: 28px; height: 28px; line-height: 14px;">🥉</span>
                                            @else
                                                <span class="badge rounded-pill bg-light text-muted border px-2 py-1">#{{ $item->rank }}</span>
                                            @endif
                                        </td>

                                        {{-- Product Info --}}
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" 
                                                     style="width: 38px; height: 38px;">
                                                    <i class="bi bi-gem"></i>
                                                </div>
                                                <div class="text-truncate" style="max-width: 190px;">
                                                    <div class="fw-bold text-dark text-truncate" title="{{ $item->product_name }}">
                                                        {{ $item->product_name }}
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1 text-muted small">
                                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.65rem;">{{ $item->product_code }}</span>
                                                        <span class="text-truncate" style="font-size: 0.75rem;">{{ $item->category_name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Sold Qty & Visual Progress --}}
                                        <td class="text-center">
                                            <div class="fw-bold fs-6 text-dark">{{ $item->total_qty_sold }}</div>
                                            <div class="progress mt-1 mx-auto" style="height: 4px; width: 60px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: {{ ($item->total_qty_sold / $maxLocalQty) * 100 }}%"></div>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.65rem;">{{ $item->total_transactions }} bills</small>
                                        </td>

                                        {{-- Total Amount --}}
                                        <td class="text-end">
                                            <div class="fw-bold text-dark">Rs. {{ number_format($item->total_amount_sold, 2) }}</div>
                                            <small class="text-muted" style="font-size: 0.7rem;">@ Rs. {{ number_format($item->avg_unit_price, 0) }}</small>
                                        </td>

                                        {{-- Stock Status --}}
                                        <td class="text-center">
                                            @if(is_null($item->available_stock))
                                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">N/A</span>
                                            @elseif($item->available_stock > 5)
                                                <span class="badge bg-success-subtle text-success px-2 py-1" style="font-size: 0.7rem;" title="In Stock">
                                                    {{ $item->available_stock }} left
                                                </span>
                                            @elseif($item->available_stock > 0)
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size: 0.7rem;" title="Low Stock">
                                                    ⚠️ {{ $item->available_stock }} left
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1" style="font-size: 0.7rem;" title="Out of Stock">
                                                    Out of Stock
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Action --}}
                                        <td class="pe-3 text-center">
                                            <button type="button" 
                                                    wire:click="openHistoryModal({{ $item->product_id ? $item->product_id : 'null' }}, '{{ $item->product_code }}', '{{ addslashes($item->product_name) }}')"
                                                    class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" 
                                                    title="View Sales History Breakdown">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ============================================================
             COLUMN 2: INDIAN (IMPORTED) SOLD PRODUCTS
        ============================================================ --}}
        @if($activeTab === 'all' || $activeTab === 'indian')
        <div class="{{ $activeTab === 'all' ? 'col-lg-6' : 'col-12' }}">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                {{-- Column Header Banner --}}
                <div class="card-header bg-white border-bottom p-3 p-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary p-2 rounded-3 text-white fs-6">
                                🇮🇳
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">
                                    Indian (Imported) Sold Items
                                </h5>
                                <small class="text-muted">Machine-made & imported covering jewelry</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill fs-6">
                                {{ $indianTotalQty }} Sold
                            </span>
                            <div class="fw-bold text-dark mt-1">Rs. {{ number_format($indianTotalAmount, 2) }}</div>
                        </div>
                    </div>

                    {{-- Column Summary Pills --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top small text-muted">
                        <span><strong>{{ $indianUniqueCount }}</strong> Unique Items Sold</span>
                        <span>•</span>
                        <span>Share: <strong class="text-primary">{{ $indianShare }}%</strong> of Total Revenue</span>
                        @if($topIndianProduct)
                        <span>•</span>
                        <span>Top: <strong>{{ $topIndianProduct->product_name }}</strong> ({{ $topIndianProduct->total_qty_sold }})</span>
                        @endif
                    </div>
                </div>

                {{-- Column Table Body --}}
                <div class="card-body p-0">
                    @if($indianProducts->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="text-muted mb-2 opacity-50" style="font-size: 3rem;">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <h6 class="fw-bold text-secondary">No Indian Items Sold</h6>
                            <p class="text-muted small mb-0">No sales recorded for Indian imported jewelry in this selected period.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase small text-muted">
                                    <tr>
                                        <th class="ps-3 text-center" style="width: 50px;">Rank</th>
                                        <th>Product</th>
                                        <th class="text-center">Sold Qty</th>
                                        <th class="text-end">Total Amount</th>
                                        <th class="text-center">Stock</th>
                                        <th class="pe-3 text-center" style="width: 70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $maxIndianQty = $indianProducts->max('total_qty_sold') ?: 1;
                                    @endphp
                                    @foreach($indianProducts as $item)
                                    <tr>
                                        {{-- Rank --}}
                                        <td class="ps-3 text-center">
                                            @if($item->rank === 1)
                                                <span class="badge rounded-circle bg-warning text-dark p-2" title="Top Selling Indian Product" style="width: 28px; height: 28px; line-height: 14px;">🥇</span>
                                            @elseif($item->rank === 2)
                                                <span class="badge rounded-circle bg-secondary-subtle text-dark p-2" title="2nd Best Seller" style="width: 28px; height: 28px; line-height: 14px;">🥈</span>
                                            @elseif($item->rank === 3)
                                                <span class="badge rounded-circle bg-warning-subtle text-dark p-2" title="3rd Best Seller" style="width: 28px; height: 28px; line-height: 14px;">🥉</span>
                                            @else
                                                <span class="badge rounded-pill bg-light text-muted border px-2 py-1">#{{ $item->rank }}</span>
                                            @endif
                                        </td>

                                        {{-- Product Info --}}
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" 
                                                     style="width: 38px; height: 38px;">
                                                    <i class="bi bi-box"></i>
                                                </div>
                                                <div class="text-truncate" style="max-width: 190px;">
                                                    <div class="fw-bold text-dark text-truncate" title="{{ $item->product_name }}">
                                                        {{ $item->product_name }}
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1 text-muted small">
                                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.65rem;">{{ $item->product_code }}</span>
                                                        <span class="text-truncate" style="font-size: 0.75rem;">{{ $item->category_name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Sold Qty & Visual Progress --}}
                                        <td class="text-center">
                                            <div class="fw-bold fs-6 text-dark">{{ $item->total_qty_sold }}</div>
                                            <div class="progress mt-1 mx-auto" style="height: 4px; width: 60px;">
                                                <div class="progress-bar bg-primary" role="progressbar" 
                                                     style="width: {{ ($item->total_qty_sold / $maxIndianQty) * 100 }}%"></div>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.65rem;">{{ $item->total_transactions }} bills</small>
                                        </td>

                                        {{-- Total Amount --}}
                                        <td class="text-end">
                                            <div class="fw-bold text-dark">Rs. {{ number_format($item->total_amount_sold, 2) }}</div>
                                            <small class="text-muted" style="font-size: 0.7rem;">@ Rs. {{ number_format($item->avg_unit_price, 0) }}</small>
                                        </td>

                                        {{-- Stock Status --}}
                                        <td class="text-center">
                                            @if(is_null($item->available_stock))
                                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">N/A</span>
                                            @elseif($item->available_stock > 5)
                                                <span class="badge bg-success-subtle text-success px-2 py-1" style="font-size: 0.7rem;" title="In Stock">
                                                    {{ $item->available_stock }} left
                                                </span>
                                            @elseif($item->available_stock > 0)
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size: 0.7rem;" title="Low Stock">
                                                    ⚠️ {{ $item->available_stock }} left
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1" style="font-size: 0.7rem;" title="Out of Stock">
                                                    Out of Stock
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Action --}}
                                        <td class="pe-3 text-center">
                                            <button type="button" 
                                                    wire:click="openHistoryModal({{ $item->product_id ? $item->product_id : 'null' }}, '{{ $item->product_code }}', '{{ addslashes($item->product_name) }}')"
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" 
                                                    title="View Sales History Breakdown">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ============================================================
         DETAILED PRODUCT SALES HISTORY MODAL
    ============================================================ --}}
    @if($showHistoryModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6);" wire:keydown.escape="closeHistoryModal">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Modal Header --}}
                <div class="modal-header bg-light border-bottom px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 rounded-3 {{ ($selectedProductDetails['origin'] ?? '') === 'Local (Handmade)' ? 'bg-success bg-opacity-10 text-success' : 'bg-primary bg-opacity-10 text-primary' }}">
                            <i class="bi {{ ($selectedProductDetails['origin'] ?? '') === 'Local (Handmade)' ? 'bi-hammer' : 'bi-box-seam' }} fs-4"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold text-dark mb-0">{{ $selectedProductDetails['name'] ?? $selectedProductName }}</h5>
                                <span class="badge {{ ($selectedProductDetails['origin'] ?? '') === 'Local (Handmade)' ? 'bg-success' : 'bg-primary' }}">
                                    {{ $selectedProductDetails['origin'] ?? 'Product' }}
                                </span>
                            </div>
                            <small class="text-muted">
                                Code: <strong>{{ $selectedProductDetails['code'] ?? $selectedProductCode }}</strong> | 
                                Category: <strong>{{ $selectedProductDetails['category'] ?? '-' }}</strong> | 
                                In Stock: <strong class="text-dark">{{ $selectedProductDetails['available_stock'] ?? 'N/A' }}</strong>
                            </small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" wire:click="closeHistoryModal"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4">
                    {{-- Summary Pills in Modal --}}
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small text-uppercase fw-semibold">Total Quantity Sold</span>
                                <h4 class="fw-bold text-dark mb-0 mt-1">{{ $selectedProductDetails['total_qty_in_period'] ?? 0 }} Units</h4>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small text-uppercase fw-semibold">Total Revenue Generated</span>
                                <h4 class="fw-bold text-success mb-0 mt-1">Rs. {{ number_format($selectedProductDetails['total_amount_in_period'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-receipt-cutoff me-1 text-primary"></i> Sale Transactions ({{ count($selectedProductSales) }})
                    </h6>

                    @if(empty($selectedProductSales))
                        <div class="text-center py-4 text-muted">
                            No detailed sales found for the selected period.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light text-uppercase">
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th>Staff</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedProductSales as $sale)
                                    <tr>
                                        <td class="text-nowrap text-muted">{{ $sale['date'] }}</td>
                                        <td>
                                            <span class="fw-bold font-monospace text-primary">{{ $sale['invoice_number'] }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $sale['customer_name'] }}</div>
                                            <small class="text-muted">{{ $sale['customer_phone'] }}</small>
                                        </td>
                                        <td class="text-muted">{{ $sale['staff_name'] }}</td>
                                        <td class="text-center fw-bold">{{ $sale['quantity'] }}</td>
                                        <td class="text-end">Rs. {{ number_format($sale['unit_price'], 2) }}</td>
                                        <td class="text-end fw-bold text-dark">Rs. {{ number_format($sale['total'], 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $sale['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ ucfirst($sale['payment_status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer bg-light border-top px-4 py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeHistoryModal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
