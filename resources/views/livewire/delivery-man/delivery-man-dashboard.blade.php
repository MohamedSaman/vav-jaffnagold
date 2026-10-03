<div class="delivery-dashboard-wrapper" wire:init="autoSyncDomexStatuses; autoSyncPostStatuses">
    {{-- Top Action Header --}}
    <div class="mb-4">
        {{-- Header Top Row: Title on Left, Total Delivered Count & Amount on Right Corner --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="bi bi-box-seam text-primary me-2"></i> Delivery & Parcel Management Hub
                </h4>
                <p class="text-muted small mb-0">
                    Manage WhatsApp orders, website storefront sales, parcel packing, barcode labels, and live courier tracking.
                </p>
            </div>

            {{-- Right Side Top Corner: Total Delivered Count and Amount --}}
            <div class="d-flex align-items-center gap-3 bg-white border border-success border-opacity-25 rounded-3 px-3 py-2 shadow-sm cursor-pointer"
                 wire:click="setStatusFilter('delivered')"
                 style="cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease;"
                 onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 0.5rem 1rem rgba(25, 135, 84, 0.15)';"
                 onmouseout="this.style.transform='none'; this.style.boxShadow='';"
                 title="Filter by Delivered Orders">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center text-success flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-check2-circle fs-3"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Total Delivered</span>
                        <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2 py-0 fw-bold" style="font-size: 11px;">
                            {{ number_format($stats['deliveredCount']) }} Orders
                        </span>
                    </div>
                    <div class="fs-4 fw-bold text-success lh-1 mt-1">
                        Rs. {{ number_format($stats['deliveredAmount'] ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons Row --}}
        <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-md-end">
            {{-- Domex Live Sync --}}
            <button class="btn btn-warning text-dark fw-semibold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2"
                    wire:click="syncAllDomexStatuses"
                    wire:loading.attr="disabled"
                    title="Fetch latest delivery status from Domex for all active orders">
                <i class="bi bi-arrow-repeat fs-5" wire:loading.class="spin" wire:target="syncAllDomexStatuses,autoSyncDomexStatuses"></i>
                <span wire:loading.remove wire:target="syncAllDomexStatuses,autoSyncDomexStatuses">Sync Domex</span>
                <span wire:loading wire:target="syncAllDomexStatuses,autoSyncDomexStatuses">Checking Domex...</span>
                @if($lastSyncedDomexAt)
                    <span class="badge bg-dark bg-opacity-20 text-dark small" style="font-size: 10px;">{{ $lastSyncedDomexAt }}</span>
                @endif
            </button>

            {{-- Post (BePost) Live Sync --}}
            <button class="btn btn-primary text-white fw-semibold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2"
                    wire:click="syncAllPostStatuses"
                    wire:loading.attr="disabled"
                    title="Fetch latest delivery status from Sri Lanka Post (BePost) for all active orders">
                <i class="bi bi-arrow-repeat fs-5" wire:loading.class="spin" wire:target="syncAllPostStatuses,autoSyncPostStatuses"></i>
                <span wire:loading.remove wire:target="syncAllPostStatuses,autoSyncPostStatuses">Sync Post (BePost)</span>
                <span wire:loading wire:target="syncAllPostStatuses,autoSyncPostStatuses">Checking Post...</span>
                @if($lastSyncedPostAt)
                    <span class="badge bg-white bg-opacity-25 text-white small" style="font-size: 10px;">{{ $lastSyncedPostAt }}</span>
                @endif
            </button>
            <button class="btn btn-success fw-semibold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" wire:click="openWhatsAppLinkModal">
                <i class="bi bi-whatsapp fs-5"></i>
                <span>Create WhatsApp Order Link</span>
            </button>
            <button class="btn btn-outline-success fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2" wire:click="openWhatsAppManager">
                <i class="bi bi-link-45deg fs-5"></i>
                <span>Sent WhatsApp Links</span>
            </button>
            <a href="{{ route('delivery.finance') }}" class="btn btn-outline-dark fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2">
                <i class="bi bi-wallet2 fs-5 text-primary"></i>
                <span>Finance & Sales</span>
            </a>
            <a href="{{ route('delivery.completed') }}" class="btn btn-outline-success fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2" title="View completed orders and courier settlements">
                <i class="bi bi-check2-all fs-5 text-success"></i>
                <span>Completed Orders</span>
            </a>
            <a href="{{ route('delivery.domex-import') }}" class="btn btn-warning text-dark fw-semibold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" title="Upload Domex remittance Excel, check delivery fee variances, and confirm returns">
                <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                <span>Domex Excel</span>
            </a>
            <button class="btn btn-primary fw-semibold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" wire:click="openCreateSaleModal">
                <i class="bi bi-plus-circle fs-5"></i>
                <span>Direct Delivery Sale</span>
            </button>
        </div>
    </div>

    {{-- Stats Cards Row: 100% Mathematically Complete Delivery Lifecycle --}}
    <div class="row g-3 mb-3">
        {{-- 1. Total Orders --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer {{ $statusFilter === 'all' && $sourceFilter === 'all' ? 'border-2 border-primary' : '' }}"
                 wire:click="resetFilters" style="cursor: pointer;"
                 title="View All Delivery Orders">
                <div class="text-muted small fw-semibold text-uppercase">Total Orders</div>
                <div class="fs-3 fw-bold text-dark mt-1">{{ $stats['totalOrders'] }}</div>
                <div class="fw-bold text-primary small" style="font-size: 11px;">Rs. {{ number_format($stats['totalOrdersAmount'] ?? 0, 2) }}</div>
                <div class="text-muted small" style="font-size: 10px;">All Delivery Orders</div>
            </div>
        </div>

        {{-- 2. Pending Pack --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer {{ $statusFilter === 'pending' ? 'border-2 border-warning' : '' }}"
                 wire:click="setStatusFilter('pending')" style="cursor: pointer;"
                 title="Filter Pending Pack Orders">
                <div class="text-warning small fw-semibold text-uppercase"><i class="bi bi-hourglass-split me-1"></i> Pending Pack</div>
                <div class="fs-3 fw-bold text-warning mt-1">{{ $stats['pendingPacking'] }}</div>
                <div class="fw-bold text-warning small" style="font-size: 11px;">Rs. {{ number_format($stats['pendingPackingAmount'] ?? 0, 2) }}</div>
                <div class="text-muted small" style="font-size: 10px;">Needs Packaging</div>
            </div>
        </div>

        {{-- 3. Packed & Ready --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer {{ $statusFilter === 'packed' ? 'border-2 border-info' : '' }}"
                 wire:click="setStatusFilter('packed')" style="cursor: pointer;"
                 title="Filter Packed Orders Ready for Courier">
                <div class="text-info small fw-semibold text-uppercase"><i class="bi bi-box-seam-fill me-1"></i> Packed & Ready</div>
                <div class="fs-3 fw-bold text-info mt-1">{{ $stats['packedCount'] }}</div>
                <div class="fw-bold text-info small" style="font-size: 11px;">Rs. {{ number_format($stats['packedAmount'] ?? 0, 2) }}</div>
                <div class="text-muted small" style="font-size: 10px;">Ready for Courier</div>
            </div>
        </div>

        {{-- 4. In Transit --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer {{ $statusFilter === 'in_transit' ? 'border-2 border-primary' : '' }}"
                 wire:click="setStatusFilter('in_transit')" style="cursor: pointer;"
                 title="Filter In Transit Courier Parcels">
                <div class="text-primary small fw-semibold text-uppercase"><i class="bi bi-truck me-1"></i> In Transit</div>
                <div class="fs-3 fw-bold text-primary mt-1">{{ $stats['inTransitCount'] }}</div>
                <div class="fw-bold text-primary small" style="font-size: 11px;">Rs. {{ number_format($stats['inTransitAmount'] ?? 0, 2) }}</div>
                <div class="text-muted small" style="font-size: 10px;">With Courier</div>
            </div>
        </div>

        {{-- 5. Delivered --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer {{ $statusFilter === 'delivered' ? 'border-2 border-success' : '' }}"
                 wire:click="setStatusFilter('delivered')" style="cursor: pointer;"
                 title="Filter Delivered Orders (Awaiting Courier Remittance)">
                <div class="text-success small fw-semibold text-uppercase"><i class="bi bi-check2-circle me-1"></i> Delivered</div>
                <div class="fs-3 fw-bold text-success mt-1">{{ $stats['deliveredCount'] }}</div>
                <div class="fw-bold text-success small" style="font-size: 11px;">Rs. {{ number_format($stats['deliveredAmount'] ?? 0, 2) }}</div>
                <div class="text-muted small" style="font-size: 10px;">Awaiting Courier Payout</div>
            </div>
        </div>

        {{-- 6. Returned / Cancelled --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 cursor-pointer {{ $statusFilter === 'returned_cancelled' ? 'border-2 border-danger' : '' }}"
                 wire:click="setStatusFilter('returned_cancelled')" style="cursor: pointer;"
                 title="Filter Returned or Cancelled Orders">
                <div class="text-danger small fw-semibold text-uppercase"><i class="bi bi-arrow-return-left me-1"></i> Returned</div>
                <div class="fs-3 fw-bold text-danger mt-1">{{ $stats['returnedCount'] }}</div>
                <div class="fw-bold text-danger small" style="font-size: 11px;">Rs. {{ number_format($stats['returnedAmount'] ?? 0, 2) }}</div>
                <div class="text-muted small" style="font-size: 10px;">Returned / Cancelled</div>
            </div>
        </div>
    </div>

    {{-- Order Source Quick Filter Bar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 bg-white p-2.5 rounded-3 shadow-sm border border-light">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted small fw-bold text-uppercase ms-1" style="font-size: 11px;">
                <i class="bi bi-funnel me-1"></i> Sources:
            </span>
            <button class="btn btn-sm py-1 px-3 rounded-pill fw-semibold {{ $sourceFilter === 'all' ? 'btn-dark' : 'btn-light text-muted border' }}"
                    wire:click="setSourceFilter('all')">
                All Sources ({{ $stats['totalOrders'] }})
            </button>
            <button class="btn btn-sm py-1 px-3 rounded-pill fw-semibold {{ $sourceFilter === 'whatsapp' ? 'btn-success text-white' : 'btn-light text-muted border' }}"
                    wire:click="setSourceFilter('whatsapp')">
                <i class="bi bi-whatsapp me-1 {{ $sourceFilter === 'whatsapp' ? 'text-white' : 'text-success' }}"></i> WhatsApp ({{ $stats['whatsappOrdersCount'] }})
            </button>
            <button class="btn btn-sm py-1 px-3 rounded-pill fw-semibold {{ $sourceFilter === 'ecommerce' ? 'btn-info text-dark' : 'btn-light text-muted border' }}"
                    wire:click="setSourceFilter('ecommerce')">
                <i class="bi bi-globe me-1 {{ $sourceFilter === 'ecommerce' ? 'text-dark' : 'text-info' }}"></i> Website Store ({{ $stats['ecommerceOrdersCount'] }})
            </button>
            <button class="btn btn-sm py-1 px-3 rounded-pill fw-semibold {{ $sourceFilter === 'delivery_man' ? 'btn-primary text-white' : 'btn-light text-muted border' }}"
                    wire:click="setSourceFilter('delivery_man')">
                <i class="bi bi-box-seam me-1 {{ $sourceFilter === 'delivery_man' ? 'text-white' : 'text-primary' }}"></i> Direct Delivery ({{ $stats['directDeliveryCount'] ?? 50 }})
            </button>
        </div>
        @if($sourceFilter !== 'all' || $statusFilter !== 'all' || $search)
            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" wire:click="resetFilters">
                <i class="bi bi-x-circle me-1"></i> Reset Filters
            </button>
        @endif
    </div>

    {{-- Main Orders Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        {{-- Card Filter Bar --}}
        <div class="p-3 border-bottom bg-light bg-opacity-50">
            <div class="row g-2 align-items-center">
                {{-- Search --}}
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" wire:model.live.debounce.300ms="search" 
                                placeholder="Search customer, phone, invoice, barcode, tracking...">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-sm-6 col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="statusFilter">
                        <option value="all">All Active Statuses</option>
                        <option value="pending">Pending Pack</option>
                        <option value="packed">Packed & Ready</option>
                        <option value="in_transit">In Transit (Courier)</option>
                        <option value="delivered">Delivered (Awaiting Remittance)</option>
                        <option value="returned_cancelled">Returned / Cancelled</option>
                    </select>
                </div>

                {{-- Source Filter --}}
                <div class="col-sm-6 col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="sourceFilter">
                        <option value="all">All Sources</option>
                        <option value="whatsapp">💬 WhatsApp Orders</option>
                        <option value="ecommerce">🌐 Website Store</option>
                        <option value="delivery_man">📦 Direct Delivery</option>
                    </select>
                </div>

                {{-- Courier Filter --}}
                <div class="col-sm-6 col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="courierFilter">
                        <option value="all">All Couriers</option>
                        <option value="Post">Post (Sri Lanka Post)</option>
                        <option value="Domex">Domex Courier</option>
                    </select>
                </div>

                {{-- Date Filter --}}
                <div class="col-sm-6 col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="dateFilter">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Orders Table --}}
        <div class="table-responsive" style="min-height: 380px;">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                <thead class="table-light text-muted text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-3 py-3" style="width: 140px;">Order / Date</th>
                        <th style="min-width: 200px;">Customer & Destination</th>
                        <th style="min-width: 180px;">Items Summary</th>
                        <th style="min-width: 150px;">Courier & Barcode</th>
                        <th style="min-width: 130px;">Payment & COD</th>
                        <th style="width: 120px;">Status</th>
                        <th class="text-end pe-3" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $cName = $this->getCustomerDisplayName($order);
                            $cPhone = $this->getCustomerPhone($order);
                            $cAddr = $this->getCustomerAddress($order);
                            $waLink = $this->getWhatsAppLink($cPhone);
                            $barcode = $order->deliverySale?->delivery_barcode ?? $order->invoice_number;
                            $courier = $order->deliverySale?->delivery_method ?? 'Domex';
                            $trackingNo = $order->deliverySale?->tracking_number ?? '';
                            $isCod = ($order->deliverySale?->payment_method === 'Cash on Delivery' || $order->payment_type === 'cod');
                            $codDue = $order->due_amount > 0 ? $order->due_amount : $order->total_amount;
                        @endphp
                        <tr wire:key="delivery-order-{{ $order->id }}">
                            {{-- Order / Date / Source --}}
                            <td class="ps-3">
                                <a href="{{ route('delivery.order-detail', $order->id) }}" class="fw-bold text-primary text-decoration-none font-monospace" title="Open Full Lifecycle Management">
                                    {{ $order->invoice_number }} <i class="bi bi-box-arrow-up-right small ms-0.5"></i>
                                </a>
                                <div class="text-muted" style="font-size: 11px;">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $order->created_at->format('d M, Y') }}
                                </div>
                                @if($order->order_source === 'whatsapp')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 mt-1" style="font-size: 10px;">
                                        <i class="bi bi-whatsapp me-0.5"></i> WhatsApp
                                    </span>
                                @elseif($order->order_source === 'ecommerce')
                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-0.5 mt-1" style="font-size: 10px;">
                                        <i class="bi bi-globe me-0.5"></i> Web Store
                                    </span>
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5 mt-1" style="font-size: 10px;">
                                        <i class="bi bi-box-seam me-0.5"></i> Direct
                                    </span>
                                @endif
                            </td>

                            {{-- Customer & Destination --}}
                            <td>
                                <div class="fw-bold text-dark">{{ $cName }}</div>
                                @if($cPhone)
                                    <div class="d-flex align-items-center gap-2 mt-0.5">
                                        <span class="text-muted small font-monospace"><i class="bi bi-telephone me-1"></i>{{ $cPhone }}</span>
                                        @if($order->order_source === 'whatsapp' && $waLink)
                                            <a href="{{ $waLink }}" target="_blank" class="badge bg-success bg-opacity-10 text-success text-decoration-none px-1.5 py-0.5 rounded-pill" title="Chat on WhatsApp">
                                                <i class="bi bi-whatsapp me-0.5"></i> WhatsApp
                                            </a>
                                        @endif
                                    </div>
                                @endif
                                <div class="text-muted text-truncate mt-1" style="font-size: 11px; max-width: 240px;" title="{{ $cAddr }}">
                                    <i class="bi bi-geo-alt me-0.5"></i> {{ $cAddr }}
                                </div>
                            </td>

                            {{-- Items Summary --}}
                            <td>
                                <span class="badge bg-light text-dark border mb-1">{{ $order->items->count() }} items</span>
                                <div class="text-truncate text-dark small" style="max-width: 200px;" title="{{ $order->items->pluck('product_name')->implode(', ') }}">
                                    {{ $order->items->first()?->product_name ?? 'Jewelry Item' }}
                                    @if($order->items->count() > 1)
                                        <span class="text-muted">+{{ $order->items->count() - 1 }} more</span>
                                    @endif
                                </div>
                                <div class="fw-bold text-dark mt-1">Rs. {{ number_format($order->total_amount, 2) }}</div>
                            </td>

                            {{-- Courier & Barcode --}}
                            <td>
                                <div class="d-flex align-items-center gap-1.5 mb-1">
                                    <span class="badge bg-primary px-2 py-1">{{ $courier }}</span>
                                    <a href="{{ route('delivery.print.delivery-label', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-dark py-0 px-1.5" title="Print 6x4 Thermal Label">
                                        <i class="bi bi-printer"></i> Label
                                    </a>
                                </div>
                                <div class="font-monospace text-muted" style="font-size: 11px;">
                                    {{ $barcode }}
                                </div>
                                @if($trackingNo)
                                    <div class="d-flex align-items-center gap-1.5 mt-1 flex-wrap">
                                        @if(strcasecmp($courier, 'Domex') === 0)
                                            <a href="https://domex.lk/Order-Details.php?wbno={{ $trackingNo }}" target="_blank"
                                               class="text-success text-decoration-none small fw-bold d-inline-flex align-items-center"
                                               title="Open live Domex tracking page in new tab">
                                                <i class="bi bi-truck me-1"></i> Track: {{ $trackingNo }}
                                                <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size: 9px;"></i>
                                            </a>
                                            <button type="button" class="btn btn-link p-0 text-muted ms-1"
                                                    wire:click="syncSingleDomex({{ $order->id }})"
                                                    title="Check live Domex status now">
                                                <i class="bi bi-arrow-repeat" style="font-size: 12px;" wire:loading.class="spin" wire:target="syncSingleDomex({{ $order->id }})"></i>
                                            </button>

                                            {{-- Button to open full Domex table & checkpoint modal --}}
                                            <div class="w-100 mt-1 d-flex align-items-center gap-1 flex-wrap">
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1.5 rounded-pill d-inline-flex align-items-center gap-1"
                                                        style="font-size: 10px; font-weight: 500;"
                                                        wire:click="openDomexModal({{ $order->id }})"
                                                        title="View full Domex details, sender, receiver, and checkpoints">
                                                    <i class="bi bi-file-earmark-ruled"></i> Full Domex Details
                                                </button>
                                                @if($order->domexOrderDetail?->is_final)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 9px;" title="Delivered/Cancelled - Tracking finalized">
                                                        <i class="bi bi-check2-all"></i> Finalized
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" style="font-size: 9px;" title="Auto-syncs on refresh until delivered or cancelled">
                                                        <i class="bi bi-arrow-repeat"></i> Auto-Tracking
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif(strcasecmp($courier, 'Post') === 0)
                                            <a href="https://bepost.lk/p/Search/" target="_blank"
                                               class="text-primary text-decoration-none small fw-bold d-inline-flex align-items-center"
                                               title="Open Sri Lanka Post (BePost) tracking page in new tab">
                                                <i class="bi bi-mailbox me-1"></i> Track: {{ $trackingNo }}
                                                <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size: 9px;"></i>
                                            </a>
                                            <button type="button" class="btn btn-link p-0 text-muted ms-1"
                                                    wire:click="syncSinglePost({{ $order->id }})"
                                                    title="Check live Post (BePost) status now">
                                                <i class="bi bi-arrow-repeat" style="font-size: 12px;" wire:loading.class="spin" wire:target="syncSinglePost({{ $order->id }})"></i>
                                            </button>

                                            {{-- Button to open full Post table & checkpoint modal --}}
                                            <div class="w-100 mt-1 d-flex align-items-center gap-1 flex-wrap">
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1.5 rounded-pill d-inline-flex align-items-center gap-1"
                                                        style="font-size: 10px; font-weight: 500;"
                                                        wire:click="openPostModal({{ $order->id }})"
                                                        title="View full Sri Lanka Post details, delivery PO, and status">
                                                    <i class="bi bi-file-earmark-ruled"></i> Full Post Details
                                                </button>
                                                @if($order->postOrderDetail?->is_final)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 9px;" title="Delivered/Settled - Tracking finalized">
                                                        <i class="bi bi-check2-all"></i> Finalized
                                                    </span>
                                                @else
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" style="font-size: 9px;" title="Auto-syncs on refresh until delivered or settled">
                                                        <i class="bi bi-arrow-repeat"></i> Auto-Tracking
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-success small fw-semibold" style="font-size: 11px;">
                                                <i class="bi bi-truck me-0.5"></i> Track: {{ $trackingNo }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Live Domex status detail if available --}}
                                    @if($order->deliverySale?->courier_status_detail)
                                        <div class="mt-1">
                                            <span class="badge bg-light text-dark border px-2 py-0.5 text-wrap text-start font-monospace" style="font-size: 10px; max-width: 220px;" title="Latest Domex status">
                                                <i class="bi bi-broadcast text-primary me-1"></i>{{ $order->deliverySale->courier_status_detail }}
                                            </span>
                                            @if($order->deliverySale?->courier_last_synced_at)
                                                <div class="text-muted" style="font-size: 9px;">
                                                    Synced {{ $order->deliverySale->courier_last_synced_at->diffForHumans() }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </td>

                            {{-- Payment & COD --}}
                            <td>
                                @if($isCod)
                                    <div class="badge bg-warning bg-opacity-25 text-dark fw-bold px-2 py-1">
                                        <i class="bi bi-cash-stack me-1"></i> COD
                                    </div>
                                    @if($order->due_amount > 0)
                                        <div class="text-danger fw-bold small mt-1">Collect: Rs. {{ number_format($order->due_amount, 2) }}</div>
                                    @else
                                        <div class="text-success fw-bold small mt-1"><i class="bi bi-check-circle"></i> Paid</div>
                                    @endif
                                @else
                                    <div class="badge bg-success bg-opacity-10 text-success fw-semibold px-2 py-1">
                                        <i class="bi bi-bank me-1"></i> Prepaid
                                    </div>
                                    <div class="text-muted small mt-1">Settled</div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                @if($order->delivery_status === 'pending')
                                    <span class="badge bg-warning bg-opacity-25 text-dark px-2.5 py-1.5 rounded-pill fw-semibold">
                                        <i class="bi bi-hourglass-split me-1"></i> Pending Pack
                                    </span>
                                @elseif($order->delivery_status === 'packed')
                                    <span class="badge bg-info bg-opacity-25 text-dark px-2.5 py-1.5 rounded-pill fw-semibold">
                                        <i class="bi bi-box-seam me-1"></i> Packed
                                    </span>
                                @elseif($order->delivery_status === 'in_transit')
                                    <span class="badge bg-primary bg-opacity-25 text-primary px-2.5 py-1.5 rounded-pill fw-semibold">
                                        <i class="bi bi-truck me-1"></i> In Transit
                                    </span>
                                @elseif($order->delivery_status === 'delivered')
                                    <span class="badge bg-success bg-opacity-25 text-success px-2.5 py-1.5 rounded-pill fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i> Delivered
                                    </span>
                                @elseif(in_array($order->delivery_status, ['returned', 'cancelled']))
                                    <span class="badge bg-danger bg-opacity-25 text-danger px-2.5 py-1.5 rounded-pill fw-semibold">
                                        <i class="bi bi-arrow-return-left me-1"></i> {{ ucfirst($order->delivery_status) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions Dropdown --}}
                            <td class="text-end pe-3">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm dropdown-toggle rounded-pill px-3 border shadow-sm" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="z-index: 1060; min-width: 230px;">
                                        <li>
                                            <a class="dropdown-item py-1.5 fw-semibold text-primary" href="{{ route('delivery.order-detail', $order->id) }}">
                                                <i class="bi bi-box-arrow-up-right text-primary me-2"></i> Dedicated Lifecycle Page
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-1.5" href="javascript:void(0)" wire:click="viewOrder({{ $order->id }})">
                                                <i class="bi bi-eye text-secondary me-2"></i> Quick Modal Preview
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-1.5" href="{{ route('delivery.print.delivery-label', $order->id) }}" target="_blank">
                                                <i class="bi bi-printer text-dark me-2"></i> Print 6x4 Label
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-1.5" href="javascript:void(0)" wire:click="openEditOrder({{ $order->id }})">
                                                <i class="bi bi-pencil text-secondary me-2"></i> Edit Destination / Charge
                                            </a>
                                        </li>
                                        @if($order->status !== 'confirm')
                                            <li>
                                                <a class="dropdown-item py-1.5 text-success fw-bold" href="javascript:void(0)" wire:click="confirmOrder({{ $order->id }})">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i> Confirm Order (Deduct Stock)
                                                </a>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider my-1"></li>

                                        {{-- Lifecycle Actions --}}
                                        @if($order->delivery_status === 'pending')
                                            <li>
                                                <a class="dropdown-item py-1.5 text-primary fw-semibold" href="javascript:void(0)" wire:click="openStatusModal({{ $order->id }}, 'packed')">
                                                    <i class="bi bi-box-seam me-2"></i> Mark as Packed
                                                </a>
                                            </li>
                                        @endif

                                        @if(in_array($order->delivery_status, ['pending', 'packed']))
                                            <li>
                                                <a class="dropdown-item py-1.5 text-primary fw-semibold" href="javascript:void(0)" wire:click="openStatusModal({{ $order->id }}, 'in_transit')">
                                                    <i class="bi bi-truck me-2"></i> Handover to Courier (In Transit)
                                                </a>
                                            </li>
                                        @endif

                                        @if($order->delivery_status === 'in_transit')
                                            <li>
                                                <a class="dropdown-item py-1.5 text-success fw-semibold" href="javascript:void(0)" wire:click="openStatusModal({{ $order->id }}, 'delivered')">
                                                    <i class="bi bi-check2-circle me-2"></i> Mark as Delivered
                                                </a>
                                            </li>
                                        @endif

                                        @if(in_array($order->delivery_status, ['in_transit', 'delivered']))
                                            <li>
                                                <a class="dropdown-item py-1.5 text-success fw-bold" href="javascript:void(0)" wire:click="openStatusModal({{ $order->id }}, 'completed')">
                                                    <i class="bi bi-check-all text-success me-2"></i> Mark as Completed
                                                </a>
                                            </li>
                                        @endif

                                        @if($isCod && $order->due_amount > 0)
                                            <li>
                                                <a class="dropdown-item py-1.5 text-warning fw-semibold" href="javascript:void(0)" wire:click="openPaymentModal({{ $order->id }})">
                                                    <i class="bi bi-cash-stack me-2"></i> Record COD Cash
                                                </a>
                                            </li>
                                        @endif

                                        @if(!in_array($order->delivery_status, ['returned', 'cancelled']))
                                            <li><hr class="dropdown-divider my-1"></li>
                                            @if($order->delivery_status !== 'delivered')
                                                <li>
                                                    <a class="dropdown-item py-1.5 text-danger fw-semibold" href="javascript:void(0)" wire:click="openStatusModal({{ $order->id }}, 'cancelled')">
                                                        <i class="bi bi-x-circle me-2"></i> Cancel Order (Restore Stock)
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item py-1.5 text-warning" href="javascript:void(0)" wire:click="openStatusModal({{ $order->id }}, 'returned')">
                                                    <i class="bi bi-arrow-return-left me-2"></i> Mark as Returned (Restore Stock)
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item py-1.5 text-danger" href="javascript:void(0)" wire:click="confirmDelete({{ $order->id }})">
                                                <i class="bi bi-trash me-2"></i> Delete Order
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3 fw-bold text-dark">No Delivery Orders Found</h5>
                                    <p class="text-muted small mb-3">Create a WhatsApp order link or generate a direct delivery sale to get started.</p>
                                    <button class="btn btn-success rounded-pill px-4 fw-semibold" wire:click="openWhatsAppLinkModal">
                                        <i class="bi bi-whatsapp me-1"></i> Create WhatsApp Order Link
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
            <div class="p-3 border-top bg-light bg-opacity-25 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                </div>
                <div>
                    {{ $orders->links('livewire.custom-pagination') }}
                </div>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Create WhatsApp Order Link                                           --}}
    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($showWhatsAppLinkModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 bg-success text-white py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-whatsapp me-2"></i> Generate WhatsApp Order Link
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeWhatsAppLinkModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        @if($generatedWhatsAppLink && $generatedWhatsAppOrder)
                            {{-- Success Screen after generating link --}}
                            <div class="text-center py-3">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 70px; height: 70px;">
                                    <i class="bi bi-check2-circle fs-1"></i>
                                </div>
                                <h4 class="fw-bold text-dark mb-1">Order Link Ready!</h4>
                                <p class="text-muted small mb-4">Send this link to your customer on WhatsApp so they can enter their delivery address.</p>

                                <div class="input-group mb-3">
                                    <input type="text" class="form-control form-control-lg bg-light" value="{{ $generatedWhatsAppLink }}" readonly id="generatedWaLinkInput">
                                    <button class="btn btn-outline-primary px-3" onclick="copyWaLink()">
                                        <i class="bi bi-clipboard me-1"></i> Copy Link
                                    </button>
                                </div>

                                <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                                    <a href="{{ $generatedWhatsAppOrder->getWhatsAppShareLink() }}" target="_blank" class="btn btn-success btn-lg px-4 rounded-pill fw-bold d-flex align-items-center gap-2">
                                        <i class="bi bi-whatsapp fs-5"></i> Send to WhatsApp Now
                                    </a>
                                    <button class="btn btn-light btn-lg px-4 rounded-pill fw-semibold border" wire:click="closeWhatsAppLinkModal">
                                        Done
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Order Link Form --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Customer Name (Optional)</label>
                                    <input type="text" class="form-control" wire:model="waCustomerName" placeholder="e.g., S. Ramesh">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Customer Phone / WhatsApp (Optional)</label>
                                    <input type="tel" class="form-control" wire:model="waCustomerPhone" placeholder="e.g., 0771234567">
                                </div>
                            </div>

                            {{-- Product Picker (Catalog Search or Instant Custom Item) --}}
                            <div class="card border rounded-3 p-3 bg-light mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold text-dark small mb-0">Search Catalog or Add Instant Item</label>
                                    <button type="button" class="btn btn-sm {{ $showWaCustomProductForm ? 'btn-secondary' : 'btn-outline-success' }} py-0.5 px-2.5 rounded-pill"
                                            wire:click="$toggle('showWaCustomProductForm')">
                                        <i class="bi {{ $showWaCustomProductForm ? 'bi-search' : 'bi-plus-circle' }} me-1"></i>
                                        {{ $showWaCustomProductForm ? 'Switch to Catalog Search' : '+ Custom / Instant Product' }}
                                    </button>
                                </div>

                                @if($showWaCustomProductForm)
                                    {{-- Instant Custom Product Form --}}
                                    <div class="p-3 bg-white rounded-3 border shadow-sm">
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold text-dark small mb-1">Custom Item Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" wire:model="waCustomItemName" placeholder="e.g. Custom 22K Ring Size 18">
                                                @error('waCustomItemName') <span class="text-danger small" style="font-size: 11px;">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold text-dark small mb-1">Unit Price (Rs.) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control form-control-sm" wire:model="waCustomItemPrice" placeholder="0" min="0" step="10">
                                                @error('waCustomItemPrice') <span class="text-danger small" style="font-size: 11px;">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold text-dark small mb-1">Code (Optional)</label>
                                                <input type="text" class="form-control form-control-sm" wire:model="waCustomItemCode" placeholder="e.g. CUST-01">
                                            </div>
                                        </div>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold text-dark small mb-1">Quantity</label>
                                                <input type="number" class="form-control form-control-sm" wire:model="waCustomItemQty" min="1">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold text-dark small mb-1">Discount (Rs.)</label>
                                                <input type="number" class="form-control form-control-sm" wire:model="waCustomItemDiscount" min="0" placeholder="0">
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <button type="button" class="btn btn-sm btn-success w-100 fw-bold shadow-sm" wire:click="addCustomProductToWhatsAppOrder">
                                                    <i class="bi bi-plus-lg me-1"></i> Add Custom Item
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Catalog Search --}}
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" wire:model.live.debounce.300ms="productSearch" placeholder="Type product name or code to search...">
                                    </div>

                                    @if(!empty($availableProducts) && count($availableProducts) > 0)
                                        <div class="list-group rounded-3 border shadow-sm" style="max-height: 180px; overflow-y: auto;">
                                            @foreach($availableProducts as $prod)
                                                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2"
                                                        wire:click="addProductToWhatsAppOrder({{ $prod->id }})">
                                                    <div>
                                                        <span class="fw-bold text-dark">{{ $prod->name }}</span>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $prod->code }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="fw-bold text-success">Rs. {{ number_format($prod->price?->retail_price ?? 0, 2) }}</span>
                                                        <span class="btn btn-sm btn-primary py-0 px-2">+ Add</span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- Selected Items List --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small mb-2">Selected Order Items ({{ count($selectedWhatsAppItems) }})</label>
                                @if(empty($selectedWhatsAppItems))
                                    <div class="text-center py-4 bg-light rounded-3 border text-muted small">
                                        <i class="bi bi-cart-x fs-3 d-block mb-1"></i>
                                        No items selected yet. Search products or add a custom item above.
                                    </div>
                                @else
                                    <div class="table-responsive border rounded-3 bg-white">
                                        <table class="table table-sm align-middle mb-0" style="font-size: 0.88rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Item</th>
                                                    <th style="width: 80px;">Qty</th>
                                                    <th style="width: 110px;">Price (Rs.)</th>
                                                    <th style="width: 110px;">Discount (Rs.)</th>
                                                    <th style="width: 110px;" class="text-end">Subtotal</th>
                                                    <th style="width: 35px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedWhatsAppItems as $index => $item)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold text-dark">{{ $item['name'] }}</div>
                                                            <div class="text-muted" style="font-size: 11px;">
                                                                {{ $item['code'] }}
                                                                @if(!empty($item['is_custom']))
                                                                    <span class="badge bg-warning bg-opacity-25 text-dark ms-1" style="font-size: 10px;">Custom</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm text-center" min="1" 
                                                                   value="{{ $item['quantity'] }}" 
                                                                   wire:change="updateWhatsAppItemQty({{ $index }}, $event.target.value)">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm text-end" min="0" step="10" 
                                                                   value="{{ $item['unit_price'] }}" 
                                                                   wire:change="updateWhatsAppItemPrice({{ $index }}, $event.target.value)">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm text-end" min="0" step="10" placeholder="0" 
                                                                   value="{{ $item['discount'] ?? 0 }}" 
                                                                   wire:change="updateWhatsAppItemDiscount({{ $index }}, $event.target.value)">
                                                        </td>
                                                        <td class="fw-bold text-end text-dark">
                                                            Rs. {{ number_format($item['subtotal'], 2) }}
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm text-danger p-0" wire:click="removeProductFromWhatsAppOrder({{ $index }})" title="Remove item">
                                                                <i class="bi bi-x-circle"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Delivery Charge & Order Discount Inputs --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Delivery Charge (Rs.)</label>
                                    <input type="number" class="form-control" wire:model.live="waDeliveryCharge" min="0" step="50">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Overall Order Discount (Rs.)</label>
                                    <input type="number" class="form-control" wire:model.live="waOrderDiscount" min="0" step="50" placeholder="0">
                                </div>
                            </div>

                            {{-- Summary Calculation Box --}}
                            <div class="card border rounded-3 p-3 bg-light mb-3">
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Items Gross Subtotal:</span>
                                    <span class="fw-semibold text-dark">Rs. {{ number_format($this->whatsAppGrossSubtotal, 2) }}</span>
                                </div>
                                @if($this->whatsAppTotalDiscount > 0)
                                    <div class="d-flex justify-content-between text-success small mb-1 fw-semibold">
                                        <span><i class="bi bi-tag-fill me-1"></i> Discount Savings:</span>
                                        <span>- Rs. {{ number_format($this->whatsAppTotalDiscount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Delivery Fee:</span>
                                    <span class="fw-semibold text-dark">
                                        @if((float)$waDeliveryCharge > 0)
                                            Rs. {{ number_format($waDeliveryCharge, 2) }}
                                        @else
                                            <span class="text-success fw-bold">FREE</span>
                                        @endif
                                    </span>
                                </div>
                                <hr class="my-2 border-secondary border-opacity-25">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Total Customer Pays:</span>
                                    <span class="fs-4 fw-bold text-success">Rs. {{ number_format($this->whatsAppTotal, 2) }}</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small">Internal Notes (Optional)</label>
                                <input type="text" class="form-control" wire:model="waNotes" placeholder="e.g., Special promo applied via WhatsApp voice note">
                            </div>
                        @endif
                    </div>
                    @if(!$generatedWhatsAppLink)
                        <div class="modal-footer border-0 bg-light p-3">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeWhatsAppLinkModal">Cancel</button>
                            <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" wire:click="generateWhatsAppOrderLink"
                                    {{ empty($selectedWhatsAppItems) ? 'disabled' : '' }}>
                                <i class="bi bi-link-45deg me-1"></i> Generate Order Link
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Sent WhatsApp Links Manager                                          --}}
    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($showWhatsAppManagerModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 bg-success text-white py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-whatsapp me-2"></i> Sent WhatsApp Order Links
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeWhatsAppManager"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Date</th>
                                        <th>Customer</th>
                                        <th>Items Payload</th>
                                        <th>Total (Rs.)</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allWhatsAppOrders as $wa)
                                        <tr>
                                            <td class="ps-3 font-monospace small">{{ $wa->created_at->format('d M Y, H:i') }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $wa->customer_name ?: 'Pending Customer' }}</div>
                                                <div class="text-muted small">{{ $wa->customer_phone ?: 'No phone prefilled' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ count($wa->items ?? []) }} items</span>
                                            </td>
                                            <td class="fw-bold text-dark">Rs. {{ number_format($wa->total_amount, 2) }}</td>
                                            <td>
                                                @if($wa->status === 'completed')
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1">
                                                        <i class="bi bi-check-circle me-1"></i> Completed (Booked)
                                                    </span>
                                                @elseif($wa->expires_at && $wa->expires_at->isPast())
                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1">
                                                        <i class="bi bi-clock-history me-1"></i> Expired
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning text-darken rounded-pill px-2.5 py-1">
                                                        <i class="bi bi-hourglass-split me-1"></i> Waiting for Customer
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-2.5" onclick="navigator.clipboard.writeText('{{ $wa->checkout_url }}'); Swal.fire({icon:'success', title:'Copied!', text:'Link copied to clipboard.', timer:1500, showConfirmButton:false});">
                                                    <i class="bi bi-clipboard"></i> Copy Link
                                                </button>
                                                <a href="{{ $wa->getWhatsAppShareLink() }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-2.5">
                                                    <i class="bi bi-whatsapp"></i> Send
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No WhatsApp order links generated yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeWhatsAppManager">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Direct Create Delivery Sale                                          --}}
    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($showCreateSaleModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 bg-primary text-white py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle me-2"></i> Create Direct Delivery Sale
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCreateSaleModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">Customer Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="directCustomerName" placeholder="e.g., S. Ramesh">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">Customer Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" wire:model="directCustomerPhone" placeholder="e.g., 0771234567">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Delivery Destination Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" wire:model="directCustomerAddress" rows="2" placeholder="House No, Street, City..."></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark small">Courier Method</label>
                                <select class="form-select" wire:model="directDeliveryMethod">
                                    <option value="Domex">Domex Courier</option>
                                    <option value="Post">Post (Sri Lanka Post)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark small">Payment Method</label>
                                <select class="form-select" wire:model="directPaymentMethod">
                                    <option value="Cash on Delivery">Cash on Delivery (COD)</option>
                                    <option value="Online Payment">Online / Bank Paid</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark small">Delivery Charge (Rs.)</label>
                                <input type="number" class="form-control" wire:model.live="directDeliveryCharge" min="0">
                            </div>
                        </div>

                        {{-- Product Picker / Custom Product for Direct Sale --}}
                        <div class="card border rounded-3 p-3 bg-light mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold text-dark small mb-0">
                                    <i class="bi {{ $showCustomProductForm ? 'bi-pencil-square' : 'bi-box-seam' }} me-1 text-primary"></i>
                                    {{ $showCustomProductForm ? 'Add Custom / Instant Product' : 'Search & Add Catalog Products' }}
                                </label>
                                <button type="button" class="btn btn-sm {{ $showCustomProductForm ? 'btn-secondary' : 'btn-outline-primary' }} py-0.5 px-2.5 rounded-pill"
                                        wire:click="$toggle('showCustomProductForm')">
                                    <i class="bi {{ $showCustomProductForm ? 'bi-search' : 'bi-plus-circle' }} me-1"></i>
                                    {{ $showCustomProductForm ? 'Switch to Catalog Search' : '+ Custom / Instant Product' }}
                                </button>
                            </div>

                            @if($showCustomProductForm)
                                {{-- Instant Custom Product Form --}}
                                <div class="p-3 bg-white rounded-3 border shadow-sm">
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-dark small mb-1">Custom Item Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" wire:model="customItemName" placeholder="e.g. Custom 22K Ring Size 18">
                                            @error('customItemName') <span class="text-danger small" style="font-size: 11px;">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold text-dark small mb-1">Unit Price (Rs.) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control form-control-sm" wire:model="customItemPrice" placeholder="0" min="0" step="10">
                                            @error('customItemPrice') <span class="text-danger small" style="font-size: 11px;">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold text-dark small mb-1">Code (Optional)</label>
                                            <input type="text" class="form-control form-control-sm" wire:model="customItemCode" placeholder="e.g. CUST-01">
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold text-dark small mb-1">Quantity</label>
                                            <input type="number" class="form-control form-control-sm" wire:model="customItemQty" min="1">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold text-dark small mb-1">Discount (Rs.)</label>
                                            <input type="number" class="form-control form-control-sm" wire:model="customItemDiscount" min="0" placeholder="0">
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button type="button" class="btn btn-sm btn-primary w-100 fw-bold shadow-sm" wire:click="addCustomProductToDirectSale">
                                                <i class="bi bi-plus-lg me-1"></i> Add Custom Item
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Catalog Search --}}
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" wire:model.live.debounce.300ms="productSearch" placeholder="Type product name or code to search...">
                                </div>

                                @if(!empty($availableProducts) && count($availableProducts) > 0)
                                    <div class="list-group rounded-3 border shadow-sm" style="max-height: 160px; overflow-y: auto;">
                                        @foreach($availableProducts as $prod)
                                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2"
                                                    wire:click="addProductToDirectSale({{ $prod->id }})">
                                                <div>
                                                    <span class="fw-bold text-dark">{{ $prod->name }}</span>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">{{ $prod->code }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-success">Rs. {{ number_format($prod->price?->retail_price ?? 0, 2) }}</span>
                                                    <span class="btn btn-sm btn-primary py-0 px-2">+ Add</span>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Direct Sale Selected Items --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small mb-2">Selected Items ({{ count($directSaleItems) }})</label>
                            @if(empty($directSaleItems))
                                <div class="text-center py-4 bg-light rounded-3 border text-muted small">
                                    <i class="bi bi-cart-x fs-3 d-block mb-1"></i>
                                    No items selected yet. Search products or add a custom item above.
                                </div>
                            @else
                                <div class="table-responsive border rounded-3 bg-white">
                                    <table class="table table-sm align-middle mb-0" style="font-size: 0.88rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item</th>
                                                <th style="width: 80px;">Qty</th>
                                                <th style="width: 110px;">Price (Rs.)</th>
                                                <th style="width: 110px;">Discount (Rs.)</th>
                                                <th style="width: 110px;" class="text-end">Subtotal</th>
                                                <th style="width: 35px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($directSaleItems as $index => $item)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold text-dark">{{ $item['name'] }}</div>
                                                        <div class="text-muted" style="font-size: 11px;">
                                                            {{ $item['code'] }}
                                                            @if(!empty($item['is_custom']))
                                                                <span class="badge bg-warning bg-opacity-25 text-dark ms-1" style="font-size: 10px;">Custom</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm text-center" min="1" 
                                                               value="{{ $item['quantity'] }}" 
                                                               wire:change="updateDirectItemQty({{ $index }}, $event.target.value)">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm text-end" min="0" step="10" 
                                                               value="{{ $item['unit_price'] }}" 
                                                               wire:change="updateDirectItemPrice({{ $index }}, $event.target.value)">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm text-end" min="0" step="10" placeholder="0" 
                                                               value="{{ $item['discount'] ?? 0 }}" 
                                                               wire:change="updateDirectItemDiscount({{ $index }}, $event.target.value)">
                                                    </td>
                                                    <td class="fw-bold text-end text-dark">Rs. {{ number_format($item['subtotal'], 2) }}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm text-danger p-0" wire:click="removeProductFromDirectSale({{ $index }})" title="Remove item">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- Order Discount Field --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-dark small">Additional Order Discount (Rs.)</label>
                                <input type="number" class="form-control" wire:model.live="directOrderDiscount" min="0" step="50" placeholder="0">
                            </div>
                        </div>

                        {{-- Calculation Breakdown Card --}}
                        <div class="card border rounded-3 p-3 bg-light mb-3">
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>Items Gross Subtotal:</span>
                                <span class="fw-semibold text-dark">Rs. {{ number_format($this->directGrossSubtotal, 2) }}</span>
                            </div>
                            @if($this->directTotalDiscount > 0)
                                <div class="d-flex justify-content-between text-success small mb-1 fw-semibold">
                                    <span><i class="bi bi-tag-fill me-1"></i> Discount Savings:</span>
                                    <span>- Rs. {{ number_format($this->directTotalDiscount, 2) }}</span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>Delivery Charge:</span>
                                <span class="fw-semibold text-dark">
                                    @if((float)$directDeliveryCharge > 0)
                                        Rs. {{ number_format($directDeliveryCharge, 2) }}
                                    @else
                                        <span class="text-success fw-bold">FREE</span>
                                    @endif
                                </span>
                            </div>
                            <hr class="my-2 border-secondary border-opacity-25">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark">Total Parcel Value:</span>
                                <span class="fs-4 fw-bold text-primary">Rs. {{ number_format($this->directTotal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeCreateSaleModal">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" wire:click="saveDirectDeliverySale"
                                {{ empty($directSaleItems) ? 'disabled' : '' }}>
                            <i class="bi bi-check2-circle me-1"></i> Create Sale & Book Parcel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: View Order Details                                                   --}}
    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($showDetailsModal && $selectedSale)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 bg-primary text-white py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-receipt me-2"></i> Order Details - {{ $selectedSale->invoice_number }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDetailsModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person me-1"></i> Customer Information</h6>
                                    <div><strong>Name:</strong> {{ $this->getCustomerDisplayName($selectedSale) }}</div>
                                    <div><strong>Phone:</strong> {{ $this->getCustomerPhone($selectedSale) ?? 'N/A' }}</div>
                                    <div><strong>Address:</strong> {{ $this->getCustomerAddress($selectedSale) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-truck me-1"></i> Delivery & Courier</h6>
                                        @if($selectedSale->deliverySale?->tracking_number)
                                            @if(strcasecmp($selectedSale->deliverySale?->delivery_method ?? '', 'Domex') === 0)
                                                <button type="button" class="btn btn-sm btn-outline-warning text-dark py-0 px-2 rounded-pill fw-semibold"
                                                        wire:click="syncSingleDomex({{ $selectedSale->id }})" title="Fetch latest status from Domex">
                                                    <i class="bi bi-arrow-repeat me-1" wire:loading.class="spin" wire:target="syncSingleDomex({{ $selectedSale->id }})"></i> Sync Domex
                                                </button>
                                            @elseif(strcasecmp($selectedSale->deliverySale?->delivery_method ?? '', 'Post') === 0)
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold"
                                                        wire:click="syncSinglePost({{ $selectedSale->id }})" title="Fetch latest status from Sri Lanka Post (BePost)">
                                                    <i class="bi bi-arrow-repeat me-1" wire:loading.class="spin" wire:target="syncSinglePost({{ $selectedSale->id }})"></i> Sync Post
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                    <div><strong>Barcode:</strong> <code class="fs-6">{{ $selectedSale->deliverySale?->delivery_barcode ?? 'N/A' }}</code></div>
                                    <div><strong>Courier:</strong> {{ $selectedSale->deliverySale?->delivery_method ?? 'Domex' }}</div>
                                    <div>
                                        <strong>Tracking #:</strong>
                                        @if($selectedSale->deliverySale?->tracking_number)
                                            @if(strcasecmp($selectedSale->deliverySale?->delivery_method ?? '', 'Domex') === 0)
                                                <a href="https://domex.lk/Order-Details.php?wbno={{ $selectedSale->deliverySale->tracking_number }}" target="_blank"
                                                   class="fw-bold text-success font-monospace text-decoration-none" title="Open Domex tracking page">
                                                    {{ $selectedSale->deliverySale->tracking_number }} <i class="bi bi-box-arrow-up-right small"></i>
                                                </a>
                                            @elseif(strcasecmp($selectedSale->deliverySale?->delivery_method ?? '', 'Post') === 0)
                                                <a href="https://bepost.lk/p/Search/" target="_blank"
                                                   class="fw-bold text-primary font-monospace text-decoration-none" title="Open Sri Lanka Post (BePost) tracking page">
                                                    {{ $selectedSale->deliverySale->tracking_number }} <i class="bi bi-box-arrow-up-right small"></i>
                                                </a>
                                            @else
                                                <span class="font-monospace fw-bold text-dark">{{ $selectedSale->deliverySale->tracking_number }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">Not assigned</span>
                                        @endif
                                    </div>
                                    <div><strong>Delivery Fee:</strong> Rs. {{ number_format($selectedSale->deliverySale?->delivery_charge ?? 0, 2) }}</div>
                                    @if($selectedSale->deliverySale?->courier_status_detail)
                                        <div class="mt-2 pt-2 border-top">
                                            <small class="text-muted d-block fw-semibold">Live {{ $selectedSale->deliverySale?->delivery_method ?? 'Courier' }} Status:</small>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle text-wrap text-start mt-0.5">
                                                <i class="bi bi-broadcast me-1"></i>{{ $selectedSale->deliverySale->courier_status_detail }}
                                            </span>
                                            @if($selectedSale->deliverySale->courier_last_synced_at)
                                                <small class="text-muted d-block mt-0.5" style="font-size: 10px;">
                                                    Updated {{ $selectedSale->deliverySale->courier_last_synced_at->diffForHumans() }}
                                                </small>
                                            @endif
                                        </div>
                                    @endif

                                    @if($selectedSale->deliverySale?->tracking_number)
                                        @if(strcasecmp($selectedSale->deliverySale?->delivery_method ?? '', 'Domex') === 0)
                                            <div class="mt-2 pt-2 border-top">
                                                <button type="button" class="btn btn-sm btn-outline-warning text-dark w-100 rounded-pill py-1 d-flex align-items-center justify-content-center gap-1.5 fw-semibold"
                                                        wire:click="openDomexModal({{ $selectedSale->id }})">
                                                    <i class="bi bi-file-earmark-ruled"></i> View Full Domex Waybill & Table
                                                </button>
                                            </div>
                                        @elseif(strcasecmp($selectedSale->deliverySale?->delivery_method ?? '', 'Post') === 0)
                                            <div class="mt-2 pt-2 border-top">
                                                <button type="button" class="btn btn-sm btn-outline-primary w-100 rounded-pill py-1 d-flex align-items-center justify-content-center gap-1.5 fw-semibold"
                                                        wire:click="openPostModal({{ $selectedSale->id }})">
                                                    <i class="bi bi-file-earmark-ruled"></i> View Full Post Waybill & Table
                                                </button>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Courier Tracking Timeline --}}
                        @if(!empty($selectedSale->deliverySale?->courier_tracking_history))
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-clock-history me-1 text-primary"></i> {{ $selectedSale->deliverySale?->delivery_method ?? 'Courier' }} Tracking Timeline</h6>
                                <div class="border rounded-3 p-3 bg-light">
                                    @foreach($selectedSale->deliverySale->courier_tracking_history as $hist)
                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light">
                                            <div class="fw-semibold text-dark small">
                                                <i class="bi bi-check-circle-fill text-success me-1.5" style="font-size: 11px;"></i>
                                                {{ $hist['status'] ?? '' }}
                                            </div>
                                            <div class="text-muted small font-monospace" style="font-size: 11px;">
                                                {{ $hist['date_time'] ?? '' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <h6 class="fw-bold text-dark mb-2">Order Items ({{ $selectedSale->items->count() }})</h6>
                        <div class="table-responsive border rounded-3 mb-3">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th>Code</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedSale->items as $idx => $it)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td class="fw-semibold text-dark">{{ $it->product_name }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $it->product_code }}</span></td>
                                            <td class="text-center">{{ $it->quantity }}</td>
                                            <td class="text-end">Rs. {{ number_format($it->unit_price, 2) }}</td>
                                            <td class="text-end fw-bold">Rs. {{ number_format($it->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="5" class="text-end">Grand Total:</td>
                                        <td class="text-end text-primary fs-5">Rs. {{ number_format($selectedSale->total_amount, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <a href="{{ route('delivery.print.delivery-label', $selectedSale->id) }}" target="_blank" class="btn btn-outline-dark rounded-pill px-4">
                            <i class="bi bi-printer me-1"></i> Print 6x4 Label
                        </a>
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeDetailsModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Edit Destination Details                                             --}}
    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 bg-dark text-white py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i> Edit Delivery Destination</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Customer Name</label>
                            <input type="text" class="form-control" wire:model="editCustomerName">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Customer Phone</label>
                            <input type="tel" class="form-control" wire:model="editCustomerPhone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Delivery Address</label>
                            <textarea class="form-control" wire:model="editCustomerAddress" rows="2"></textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-dark small">Courier Method</label>
                                <select class="form-select" wire:model="editDeliveryMethod">
                                    <option value="Domex">Domex</option>
                                    <option value="Post">Post</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-dark small">Tracking Number</label>
                                <input type="text" class="form-control" wire:model="editTrackingNumber" placeholder="e.g. SLP-12345">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-dark small">Payment Method</label>
                                <select class="form-select" wire:model="editPaymentMethod">
                                    <option value="Cash on Delivery">Cash on Delivery</option>
                                    <option value="Online Payment">Online / Bank Paid</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-dark small">Delivery Charge (Rs.)</label>
                                <input type="number" class="form-control" wire:model="editDeliveryCharge" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeEditModal">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" wire:click="saveEditOrder">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Status Change / Courier Tracking Handover                            --}}
    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($showStatusModal && $statusSale)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 {{ $targetStatus === 'completed' ? 'bg-success' : (in_array($targetStatus, ['cancelled', 'returned']) ? 'bg-danger' : 'bg-primary') }} text-white py-3">
                        <h5 class="modal-title fw-bold">
                            @if($targetStatus === 'packed')
                                <i class="bi bi-box-seam me-2"></i> Confirm Order Packed
                            @elseif($targetStatus === 'in_transit')
                                <i class="bi bi-truck me-2"></i> Handover to Courier (In Transit)
                            @elseif($targetStatus === 'delivered')
                                <i class="bi bi-check2-circle me-2"></i> Confirm Order Delivered
                            @elseif($targetStatus === 'completed')
                                <i class="bi bi-check-circle-fill me-2"></i> Mark Order as Completed
                            @elseif($targetStatus === 'cancelled')
                                <i class="bi bi-x-circle me-2"></i> Cancel Delivery Order
                            @elseif($targetStatus === 'returned')
                                <i class="bi bi-arrow-return-left me-2"></i> Return Parcel
                            @endif
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeStatusModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted mb-3">Order Invoice: <strong>{{ $statusSale->invoice_number }}</strong></p>

                        @if($targetStatus === 'in_transit')
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small">Enter Courier Tracking Number (Optional)</label>
                                <input type="text" class="form-control form-control-lg" wire:model="courierTrackingNumber" placeholder="e.g., SLP-987654 or DX-55442">
                                <div class="text-muted" style="font-size: 11px;">Tracking number will be attached to customer parcel history.</div>
                            </div>
                        @elseif($targetStatus === 'completed')
                            <div class="alert alert-success border-0 d-flex align-items-center gap-2 mb-3 py-2 px-3 rounded-3">
                                <i class="bi bi-check-circle-fill fs-4 text-success flex-shrink-0"></i>
                                <div class="small">
                                    <strong>Manual Order Completion:</strong> Confirm that payment has been received from the courier service (Domex / Post) and this order is fully completed.
                                </div>
                            </div>
                        @elseif($targetStatus === 'cancelled')
                            <div class="alert alert-danger border-0 d-flex align-items-center gap-2 mb-3 py-2 px-3 rounded-3">
                                <i class="bi bi-arrow-counterclockwise fs-4 text-danger flex-shrink-0"></i>
                                <div class="small">
                                    <strong>Stock Return Notice:</strong> Cancelling this order will automatically restore all product quantities back into available inventory stock.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small">Cancellation Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" wire:model="returnReason" rows="2" placeholder="e.g. Customer cancelled order, duplicate order, wrong items..."></textarea>
                            </div>
                        @elseif($targetStatus === 'returned')
                            <div class="alert alert-warning border-0 d-flex align-items-center gap-2 mb-3 py-2 px-3 rounded-3">
                                <i class="bi bi-arrow-counterclockwise fs-4 text-warning flex-shrink-0"></i>
                                <div class="small">
                                    <strong>Stock Return Notice:</strong> Marking this parcel as Returned will automatically restore all product quantities back into active available stock.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small">Return Reason</label>
                                <textarea class="form-control" wire:model="returnReason" rows="2" placeholder="e.g., Customer unreachable after 3 attempts / Refused..."></textarea>
                            </div>
                        @else
                            <div class="p-3 bg-light rounded-3 text-center">
                                Are you sure you want to advance this order status to <strong>{{ strtoupper(str_replace('_', ' ', $targetStatus)) }}</strong>?
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeStatusModal">Keep Order</button>
                        <button type="button" class="btn {{ $targetStatus === 'completed' ? 'btn-success' : (in_array($targetStatus, ['cancelled', 'returned']) ? 'btn-danger' : 'btn-primary') }} rounded-pill px-4 fw-bold" wire:click="executeStatusChange">
                            Confirm {{ ucfirst($targetStatus) }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Record COD Payment                                                   --}}
    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($showPaymentModal && $paymentSale)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 bg-warning text-dark py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-cash-stack me-2"></i> Record Collected COD Cash</h5>
                        <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Customer:</span>
                                <strong>{{ $this->getCustomerDisplayName($paymentSale) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Due Amount:</span>
                                <strong class="text-danger fs-5">Rs. {{ number_format($paymentSale->due_amount, 2) }}</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Collected Cash Amount (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg fw-bold text-success" wire:model="paymentAmount" min="1" step="10">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Payment Mode</label>
                            <select class="form-select" wire:model="paymentMethod">
                                <option value="cash">Cash Remitted by Courier</option>
                                <option value="bank_transfer">Direct Bank Transfer</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Notes (Optional)</label>
                            <input type="text" class="form-control" wire:model="paymentNotes" placeholder="e.g. Courier settlement receipt #">
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closePaymentModal">Cancel</button>
                        <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold" wire:click="savePayment">Save & Complete Order</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Delete Confirmation                                                  --}}
    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($showDeleteModal && $deleteSale)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 bg-danger text-white py-3">
                        <h5 class="modal-title fw-bold"><i class="bi bi-trash me-2"></i> Confirm Delete Order</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold text-dark mt-2">Delete Order {{ $deleteSale->invoice_number }}?</h5>
                        <p class="text-muted small mb-0">This will delete the sale record and restore all item quantities back into available inventory.</p>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeDeleteModal">Cancel</button>
                        <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" wire:click="executeDelete">Delete & Restore Stock</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL: Full Domex Live Order Details & Checkpoint Table                     --}}
    {{-- ═══════════════════════════════════════════════════════════════════════════ --}}
    @if($showDomexModal && $selectedDomexSale)
        @php
            $dmx = $selectedDomexDetail;
            $wb = $dmx?->waybill_number ?? $selectedDomexSale->deliverySale?->tracking_number ?? '';
            $domexUrl = "https://domex.lk/Order-Details.php?wbno={$wb}";
            $isFinal = (bool) ($dmx?->is_final ?? false);
            $cStatus = $dmx?->current_status ?? $selectedDomexSale->deliverySale?->courier_status_detail ?? 'Awaiting Domex Scan';
            $normStatus = $dmx?->normalized_status ?? $selectedDomexSale->delivery_status;
        @endphp
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    
                    {{-- Modal Header --}}
                    <div class="modal-header border-0 py-3 px-4 text-white" style="background: linear-gradient(135deg, #b91c1c 0%, #1e293b 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white bg-opacity-20 p-2 rounded-3 text-white">
                                <i class="bi bi-truck fs-4"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="modal-title fw-bold mb-0 text-white font-monospace">
                                        Domex Waybill #{{ $wb }}
                                    </h5>
                                    @if($isFinal)
                                        <span class="badge bg-success bg-opacity-25 text-white border border-success border-opacity-50 px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                                            <i class="bi bi-shield-check me-1"></i> Finalized / Delivery Complete
                                        </span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-25 text-white border border-warning border-opacity-50 px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                                            <i class="bi bi-arrow-repeat spin me-1"></i> Auto-Sync Active Until Delivered
                                        </span>
                                    @endif
                                </div>
                                <div class="text-white text-opacity-80 small mt-0.5">
                                    Linked with Jaffna Gold Order <span class="fw-bold font-monospace">{{ $selectedDomexSale->invoice_number }}</span>
                                    &bull; Customer: <span class="fw-semibold">{{ $this->getCustomerDisplayName($selectedDomexSale) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                                    wire:click="refreshModalDomex" wire:loading.attr="disabled" title="Force check live status from Domex server">
                                <i class="bi bi-arrow-repeat" wire:loading.class="spin" wire:target="refreshModalDomex"></i>
                                <span wire:loading.remove wire:target="refreshModalDomex">Refresh Live</span>
                                <span wire:loading wire:target="refreshModalDomex">Fetching...</span>
                            </button>
                            <a href="{{ $domexUrl }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" title="View on domex.lk">
                                <span>Domex.lk</span> <i class="bi bi-box-arrow-up-right small"></i>
                            </a>
                            <button type="button" class="btn-close btn-close-white ms-2" wire:click="closeDomexModal"></button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="modal-body p-4 bg-light">
                        
                        {{-- Live Status Alert Banner --}}
                        <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden"
                             style="background: {{ $isFinal ? 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)' : 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)' }}; border-left: 5px solid {{ $isFinal ? '#16a34a' : '#2563eb' }} !important;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-3 rounded-circle {{ $isFinal ? 'bg-success text-white' : 'bg-primary text-white' }} shadow-sm">
                                        <i class="bi {{ $isFinal ? 'bi-check2-circle' : 'bi-broadcast' }} fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="small text-uppercase fw-bold {{ $isFinal ? 'text-success' : 'text-primary' }}" style="letter-spacing: 0.05em; font-size: 11px;">
                                            Current Real-Time Status
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">
                                            {{ $cStatus }}
                                        </h5>
                                        <div class="text-muted small">
                                            @if($dmx?->last_checkpoint_time)
                                                <i class="bi bi-clock me-1"></i> Recorded at: <strong class="text-dark">{{ $dmx->last_checkpoint_time }}</strong> &bull;
                                            @endif
                                            <i class="bi bi-arrow-repeat me-1"></i>
                                            @if($dmx?->last_synced_at)
                                                Last Synced: {{ $dmx->last_synced_at->diffForHumans() }} ({{ $dmx->last_synced_at->format('Y-m-d h:i A') }})
                                            @else
                                                Last Synced: Just now
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($isFinal)
                                        <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-shield-check me-1"></i> FINALIZED & DELIVERED
                                        </span>
                                        <div class="text-muted" style="font-size: 11px; max-width: 250px;">
                                            Tracking completed. No further background polling needed.
                                        </div>
                                    @else
                                        <span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-arrow-repeat spin me-1"></i> POLLING ACTIVE
                                        </span>
                                        <div class="text-muted" style="font-size: 11px; max-width: 260px;">
                                            Auto-syncs on page refresh until parcel is Delivered or Cancelled.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Structured Information Grid --}}
                        <div class="row g-3 mb-4">
                            
                            {{-- 1. Waybill & Shipping Details --}}
                            <div class="col-lg-3 col-md-6">
                                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                            <i class="bi bi-upc-scan fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Waybill Details</h6>
                                            <small class="text-muted">Domex shipping specs</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2 small">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Waybill No:</span>
                                            <strong class="font-monospace text-primary">{{ $wb }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Pickup Date:</span>
                                            <strong>{{ $dmx?->pickup_date ?: 'N/A' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Item Type:</span>
                                            <span class="badge bg-light text-dark border">{{ $dmx?->item_type ?: 'Parcel' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">No. of Pieces:</span>
                                            <strong>{{ $dmx?->pieces ?: '1' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Weight:</span>
                                            <strong>{{ $dmx?->weight ?: '0 (g)' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Payment:</span>
                                            <strong>{{ $dmx?->payment_method ?: ($selectedDomexSale->deliverySale?->payment_method ?? 'Cash') }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Domex Paid:</span>
                                            <span class="badge {{ strtolower($dmx?->is_paid ?? '') === 'yes' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $dmx?->is_paid ?: 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Sender (Origin) --}}
                            <div class="col-lg-3 col-md-6">
                                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3">
                                            <i class="bi bi-box-arrow-up-right fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Sender (Origin)</h6>
                                            <small class="text-muted">Dispatch hub details</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2 small">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Sender Name:</span>
                                            <strong class="text-dark">{{ $dmx?->sender_name ?: 'JAFFNA GOLD COVERING CCP ONLINE' }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Contact Phone:</span>
                                            <strong class="font-monospace">{{ $dmx?->sender_contact ?: '0761919650' }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Pickup City:</span>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $dmx?->pickup_city ?: 'Jaffna' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Origin Address:</span>
                                            <span class="text-muted text-break" style="font-size: 11px;">{{ $dmx?->sender_address ?: 'NO. 25/46, CALIBA APTHUL, CAMIT LANE' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. Receiver (Destination) --}}
                            <div class="col-lg-3 col-md-6">
                                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <div class="bg-success bg-opacity-10 text-success p-2 rounded-3">
                                            <i class="bi bi-geo-alt-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Receiver (Destination)</h6>
                                            <small class="text-muted">Customer delivery info</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2 small">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Receiver Name:</span>
                                            <strong class="text-dark">{{ $dmx?->receiver_name ?: $this->getCustomerDisplayName($selectedDomexSale) }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Receiver Phone:</span>
                                            <strong class="font-monospace text-success">{{ $dmx?->receiver_phone ?: $this->getCustomerPhone($selectedDomexSale) }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Destination City:</span>
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">{{ $dmx?->destination_city ?: 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Destination Address:</span>
                                            <span class="text-muted text-break" style="font-size: 11px;">{{ $this->getCustomerAddress($selectedDomexSale) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. Delivery Handover & Order Link --}}
                            <div class="col-lg-3 col-md-6">
                                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <div class="bg-info bg-opacity-10 text-info p-2 rounded-3">
                                            <i class="bi bi-person-check-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Delivery Handover</h6>
                                            <small class="text-muted">Courier delivery audit</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2 small">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Delivered By:</span>
                                            <strong class="text-dark">{{ $dmx?->delivered_by ?: 'In Dispatch Process' }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Delivered To / Recipient:</span>
                                            <strong class="text-dark">{{ $dmx?->delivered_to ?: 'Awaiting Customer Handover' }}</strong>
                                        </div>
                                        <div class="pt-2 border-top">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Order Total:</span>
                                                <strong class="text-dark">Rs. {{ number_format($selectedDomexSale->total_amount, 2) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span class="text-muted">COD Due:</span>
                                                <strong class="{{ $selectedDomexSale->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                                                    Rs. {{ number_format($selectedDomexSale->due_amount, 2) }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Checkpoints Table & Tracking History --}}
                        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
                            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-clock-history text-primary"></i> Domex Checkpoint Journey & Timeline Table
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            {{ count($dmx?->tracking_history ?? []) }} Checkpoints
                                        </span>
                                    </h6>
                                    <small class="text-muted">Step-by-step parcel movements captured from Domex tracking log</small>
                                </div>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 11px;">
                                    Source: domex.lk
                                </span>
                            </div>
                            <div class="card-body p-0">
                                @if(!empty($dmx?->tracking_history))
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light text-muted small">
                                                <tr>
                                                    <th class="ps-4" style="width: 60px;">#</th>
                                                    <th style="min-width: 220px;">Timestamp</th>
                                                    <th>Checkpoint Activity / Location Description</th>
                                                    <th class="text-end pe-4" style="width: 140px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(array_reverse($dmx->tracking_history) as $idx => $event)
                                                    @php
                                                        $isLatest = ($idx === 0);
                                                        $eventStatus = $event['status'] ?? '';
                                                        $eventLower = strtolower($eventStatus);
                                                        $isDeliveredEvent = str_contains($eventLower, 'delivered');
                                                        $isOutForDelivery = str_contains($eventLower, 'out for delivery');
                                                    @endphp
                                                    <tr class="{{ $isLatest ? 'table-primary table-opacity-10' : '' }}">
                                                        <td class="ps-4 text-muted font-monospace small">
                                                            {{ count($dmx->tracking_history) - $idx }}
                                                        </td>
                                                        <td>
                                                            <div class="font-monospace fw-semibold {{ $isLatest ? 'text-primary' : 'text-dark' }} small">
                                                                <i class="bi bi-calendar-event me-1.5 text-muted"></i>{{ $event['date_time'] ?? 'N/A' }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                @if($isDeliveredEvent)
                                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                                @elseif($isOutForDelivery)
                                                                    <i class="bi bi-truck text-primary fs-5"></i>
                                                                @elseif($isLatest)
                                                                    <i class="bi bi-geo-alt-fill text-primary fs-5"></i>
                                                                @else
                                                                    <i class="bi bi-arrow-right-circle text-muted"></i>
                                                                @endif
                                                                <span class="fw-semibold {{ $isLatest ? 'text-primary fw-bold' : 'text-dark' }}">
                                                                    {{ $eventStatus }}
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="text-end pe-4">
                                                            @if($isDeliveredEvent)
                                                                <span class="badge bg-success px-2.5 py-1 rounded-pill">Delivered</span>
                                                            @elseif($isOutForDelivery)
                                                                <span class="badge bg-primary px-2.5 py-1 rounded-pill">Out for Delivery</span>
                                                            @elseif($isLatest)
                                                                <span class="badge bg-info px-2.5 py-1 rounded-pill">Latest Scan</span>
                                                            @else
                                                                <span class="badge bg-light text-muted border px-2 py-0.5 rounded-pill">Processed</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox text-muted" style="font-size: 2.5rem;"></i>
                                        <p class="text-dark fw-semibold mt-2 mb-1">No checkpoint events recorded yet on Domex</p>
                                        <p class="text-muted small mb-3">This parcel may be newly registered and awaiting initial courier scan at Jaffna hub.</p>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" wire:click="refreshModalDomex">
                                            <i class="bi bi-arrow-repeat me-1" wire:loading.class="spin" wire:target="refreshModalDomex"></i> Check Again
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer border-0 bg-white p-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i> Data saved in table <code>domex_order_details</code>. Auto-refresh runs on page load until marked Delivered or Cancelled.
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeDomexModal">
                                Close
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- Full Sri Lanka Post (BePost) Order Details & Checkpoints Modal --}}
    @if($showPostModal && $selectedPostSale)
        @php
            $post = $selectedPostDetail;
            $barcode = $post?->barcode ?? $selectedPostSale->deliverySale?->tracking_number ?? '';
            $bepostUrl = "https://bepost.lk/p/Search/";
            $isFinal = (bool) ($post?->is_final ?? false);
            $cStatus = $post?->status ?? $selectedPostSale->deliverySale?->courier_status_detail ?? 'Awaiting Post Scan';
            $normStatus = $post?->normalized_status ?? $selectedPostSale->delivery_status;
        @endphp
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    
                    {{-- Modal Header --}}
                    <div class="modal-header border-0 py-3 px-4 text-white" style="background: linear-gradient(135deg, #0056b3 0%, #1e293b 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white bg-opacity-20 p-2 rounded-3 text-white">
                                <i class="bi bi-mailbox fs-4"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="modal-title fw-bold mb-0 text-white font-monospace">
                                        Sri Lanka Post (BePost) Barcode #{{ $barcode }}
                                    </h5>
                                    @if($isFinal)
                                        <span class="badge bg-success bg-opacity-25 text-white border border-success border-opacity-50 px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                                            <i class="bi bi-shield-check me-1"></i> Finalized / Delivery Complete
                                        </span>
                                    @else
                                        <span class="badge bg-info bg-opacity-25 text-white border border-info border-opacity-50 px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                                            <i class="bi bi-arrow-repeat spin me-1"></i> Auto-Sync Active Until Delivered
                                        </span>
                                    @endif
                                </div>
                                <div class="text-white text-opacity-80 small mt-0.5">
                                    Linked with Jaffna Gold Order <span class="fw-bold font-monospace">{{ $selectedPostSale->invoice_number }}</span>
                                    &bull; Customer: <span class="fw-semibold">{{ $this->getCustomerDisplayName($selectedPostSale) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                                    wire:click="refreshModalPost" wire:loading.attr="disabled" title="Force check live status from BePost server">
                                <i class="bi bi-arrow-repeat" wire:loading.class="spin" wire:target="refreshModalPost"></i>
                                <span wire:loading.remove wire:target="refreshModalPost">Refresh Live</span>
                                <span wire:loading wire:target="refreshModalPost">Fetching...</span>
                            </button>
                            <a href="{{ $bepostUrl }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" title="View on bepost.lk">
                                <span>bepost.lk</span> <i class="bi bi-box-arrow-up-right small"></i>
                            </a>
                            <button type="button" class="btn-close btn-close-white ms-2" wire:click="closePostModal"></button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="modal-body p-4 bg-light">
                        
                        {{-- Live Status Alert Banner --}}
                        <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden"
                             style="background: {{ $isFinal ? 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)' : 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)' }}; border-left: 5px solid {{ $isFinal ? '#16a34a' : '#2563eb' }} !important;">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-3 rounded-circle {{ $isFinal ? 'bg-success text-white' : 'bg-primary text-white' }} shadow-sm">
                                        <i class="bi {{ $isFinal ? 'bi-check2-circle' : 'bi-broadcast' }} fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="small text-uppercase fw-bold {{ $isFinal ? 'text-success' : 'text-primary' }}" style="letter-spacing: 0.05em; font-size: 11px;">
                                            Current Real-Time Status
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">
                                            {{ $cStatus }}
                                        </h5>
                                        <div class="text-muted small">
                                            @if($post?->received_date)
                                                <i class="bi bi-calendar-event me-1"></i> Recorded date: <strong class="text-dark">{{ $post->received_date }}</strong> &bull;
                                            @endif
                                            @if($post?->delivery_po)
                                                <i class="bi bi-geo-alt me-1"></i> Delivery PO: <strong class="text-dark">{{ $post->delivery_po }}</strong> &bull;
                                            @endif
                                            <i class="bi bi-arrow-repeat me-1"></i>
                                            @if($post?->last_synced_at)
                                                Last Synced: {{ $post->last_synced_at->diffForHumans() }} ({{ $post->last_synced_at->format('Y-m-d h:i A') }})
                                            @else
                                                Last Synced: Just now
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    @if($isFinal)
                                        <div class="text-end">
                                            <span class="badge bg-success fs-6 px-3 py-1.5 rounded-pill shadow-sm">
                                                <i class="bi bi-check-circle me-1"></i> Finalized / Delivered
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size: 10px;">Parcel handover verified</div>
                                        </div>
                                    @else
                                        <div class="text-end">
                                            <span class="badge bg-primary fs-6 px-3 py-1.5 rounded-pill shadow-sm">
                                                <i class="bi bi-truck me-1"></i> Active In Transit
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size: 10px;">Auto-checks via cron pipeline</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- 4 Info Spec Cards --}}
                        <div class="row g-3 mb-4">
                            {{-- 1. Tracking Overview --}}
                            <div class="col-lg-3 col-md-6">
                                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                            <i class="bi bi-upc-scan fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Barcode Details</h6>
                                            <small class="text-muted">Post shipping specs</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2 small">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Barcode No:</span>
                                            <strong class="font-monospace text-primary">{{ $barcode }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Raw Status:</span>
                                            <span class="badge bg-light text-dark border">{{ $post?->status ?: 'Pending' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Normalized:</span>
                                            <span class="badge bg-info bg-opacity-15 text-info">{{ ucfirst($normStatus) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Tracking State:</span>
                                            <span class="badge {{ $isFinal ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $isFinal ? 'Complete' : 'In Progress' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Post Offices --}}
                            <div class="col-lg-3 col-md-6">
                                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3">
                                            <i class="bi bi-building fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Postal Route</h6>
                                            <small class="text-muted">Acceptance & Delivery PO</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2 small">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Acceptance PO (Origin):</span>
                                            <strong class="text-dark">{{ $post?->acceptance_po ?: 'Department of Posts' }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Delivery PO (Destination):</span>
                                            <strong class="text-primary">{{ $post?->delivery_po ?: 'Local Post Office' }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Courier Method:</span>
                                            <span class="badge bg-primary bg-opacity-10 text-primary">Sri Lanka Post</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. Delivery & Financials --}}
                            <div class="col-lg-3 col-md-6">
                                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <div class="bg-success bg-opacity-10 text-success p-2 rounded-3">
                                            <i class="bi bi-cash-stack fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Financial & Date</h6>
                                            <small class="text-muted">COD & Delivery charges</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2 small">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Received Date:</span>
                                            <strong class="text-dark">{{ $post?->received_date ?: 'Pending' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">COD Amount:</span>
                                            <strong class="text-success">{{ $post?->cod_amount ? 'Rs. ' . $post->cod_amount : 'Rs. ' . number_format($selectedPostSale->due_amount, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Delivery Fee:</span>
                                            <span>Rs. {{ number_format($selectedPostSale->deliverySale?->delivery_charge ?? 0, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Payment Status:</span>
                                            <span class="badge {{ $selectedPostSale->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ ucfirst($selectedPostSale->payment_status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. Customer Info --}}
                            <div class="col-lg-3 col-md-6">
                                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white p-3">
                                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                        <div class="bg-info bg-opacity-10 text-info p-2 rounded-3">
                                            <i class="bi bi-person-circle fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Customer</h6>
                                            <small class="text-muted">Order destination</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2 small">
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Name:</span>
                                            <strong class="text-dark">{{ $this->getCustomerDisplayName($selectedPostSale) }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Phone:</span>
                                            <strong class="font-monospace">{{ $this->getCustomerPhone($selectedPostSale) ?: 'N/A' }}</strong>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block" style="font-size: 11px;">Address:</span>
                                            <span class="text-muted text-break" style="font-size: 11px;">{{ $this->getCustomerAddress($selectedPostSale) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Checkpoints / Tracking Timeline Card --}}
                        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4">
                            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history fs-5 text-primary"></i>
                                    <h6 class="fw-bold text-dark mb-0">Post Tracking Timeline & Checkpoints</h6>
                                    @if(!empty($post?->tracking_history))
                                        <span class="badge bg-primary rounded-pill px-2.5 py-0.5 small">
                                            {{ count($post->tracking_history) }} record(s)
                                        </span>
                                    @endif
                                </div>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 11px;">
                                    Source: bepost.lk
                                </span>
                            </div>
                            <div class="card-body p-0">
                                @if(!empty($post?->tracking_history))
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light text-muted small">
                                                <tr>
                                                    <th class="ps-4" style="width: 60px;">#</th>
                                                    <th style="min-width: 200px;">Timestamp / Date</th>
                                                    <th>Location / Post Office</th>
                                                    <th class="text-end pe-4" style="width: 140px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(array_reverse($post->tracking_history) as $idx => $event)
                                                    @php
                                                        $isLatest = ($idx === 0);
                                                        $eventStatus = $event['status'] ?? '';
                                                        $eventLower = strtolower($eventStatus);
                                                        $isDeliveredEvent = str_contains($eventLower, 'delivered') || str_contains($eventLower, 'settled');
                                                    @endphp
                                                    <tr class="{{ $isLatest ? 'table-primary table-opacity-10' : '' }}">
                                                        <td class="ps-4 text-muted font-monospace small">
                                                            {{ count($post->tracking_history) - $idx }}
                                                        </td>
                                                        <td>
                                                            <div class="font-monospace fw-semibold {{ $isLatest ? 'text-primary' : 'text-dark' }} small">
                                                                <i class="bi bi-calendar-event me-1.5 text-muted"></i>{{ $event['date_time'] ?? ($event['received_date'] ?? 'N/A') }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                @if($isDeliveredEvent)
                                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                                @elseif($isLatest)
                                                                    <i class="bi bi-geo-alt-fill text-primary fs-5"></i>
                                                                @else
                                                                    <i class="bi bi-arrow-right-circle text-muted"></i>
                                                                @endif
                                                                <span class="fw-semibold {{ $isLatest ? 'text-primary fw-bold' : 'text-dark' }}">
                                                                    {{ $event['location'] ?? ($event['delivery_po'] ?? ($post->delivery_po ?: 'Post Office')) }}
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="text-end pe-4">
                                                            @if($isDeliveredEvent)
                                                                <span class="badge bg-success px-2.5 py-1 rounded-pill">Delivered / Settled</span>
                                                            @elseif($isLatest)
                                                                <span class="badge bg-info px-2.5 py-1 rounded-pill">Latest Scan</span>
                                                            @else
                                                                <span class="badge bg-light text-muted border px-2 py-0.5 rounded-pill">Processed</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="bi bi-mailbox text-muted" style="font-size: 2.5rem;"></i>
                                        <p class="text-dark fw-semibold mt-2 mb-1">No checkpoint events recorded yet on Sri Lanka Post</p>
                                        <small class="text-muted">Click "Refresh Live" above to query BePost directly.</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer border-0 bg-white p-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i> Data saved in table <code>post_order_details</code>. Auto-refresh runs via scheduled pipeline until marked Delivered or Settled.
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closePostModal">
                                Close
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .spin { animation: spin 0.8s linear infinite; display: inline-block; }
</style>
@endpush

@push('scripts')
<script>
    function copyWaLink() {
        const input = document.getElementById('generatedWaLinkInput');
        if (input) {
            input.select();
            navigator.clipboard.writeText(input.value);
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'WhatsApp checkout link copied to clipboard.',
                timer: 1800,
                showConfirmButton: false
            });
        }
    }

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('show-toast', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                icon: data.type || 'info',
                title: data.message || '',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        });
    });
</script>
@endpush
