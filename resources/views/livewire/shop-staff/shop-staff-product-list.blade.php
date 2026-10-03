<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-box-seam text-primary me-2"></i> Products & Local Handmade Catalog
            </h3>
            <p class="text-muted mb-0 small">Browse store inventory, track local handmade creations, check live availability, and print QR jewelry tags.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('shop-staff.handmade-purchasing') }}" class="btn btn-outline-warning text-dark fw-semibold shadow-sm">
                <i class="bi bi-gem text-warning me-1"></i> Handmade Purchasing & Workshop
            </a>
            <a href="{{ route('shop-staff.dashboard') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Main Navigation Tabs --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill gap-2">
                <li class="nav-item">
                    <button type="button" 
                        wire:click="setTab('all')" 
                        class="nav-link py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2 {{ $activeTab === 'all' ? 'active bg-primary text-white shadow-sm' : 'text-muted bg-light' }}"
                        style="border-radius: 8px;">
                        <i class="bi bi-grid-3x3-gap"></i>
                        <span>All Store Products</span>
                        <span class="badge {{ $activeTab === 'all' ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-2">
                            {{ $totalCatalogCount }}
                        </span>
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" 
                        wire:click="setTab('handmade')" 
                        class="nav-link py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2 {{ $activeTab === 'handmade' ? 'active bg-warning text-dark shadow-sm' : 'text-muted bg-light' }}"
                        style="border-radius: 8px;">
                        <i class="bi bi-gem"></i>
                        <span>Handmade Local Products</span>
                        <span class="badge {{ $activeTab === 'handmade' ? 'bg-dark text-white' : 'bg-warning-subtle text-dark' }} rounded-pill px-2">
                            {{ $totalHandmadeCount }}
                        </span>
                        @if($handmadeInStockCount > 0)
                            <span class="badge bg-success text-white rounded-pill px-2" style="font-size: 10px;">
                                {{ $handmadeInStockCount }} Live in POS
                            </span>
                        @endif
                    </button>
                </li>
            </ul>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- TAB 1: ALL STORE PRODUCTS                  --}}
    {{-- ========================================== --}}
    @if($activeTab === 'all')
        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name, code, or barcode...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select wire:model.live="stockFilter" class="form-select">
                            <option value="all">All Stock Levels</option>
                            <option value="in_stock">In Stock (>10)</option>
                            <option value="low_stock">Low Stock (1-10)</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Grid --}}
        <div class="row g-3">
            @forelse($products as $product)
            @php
                $imageUrl = null;
                $defaultImage = asset('images/product.jpg');
                
                if (!empty($product['image'])) {
                    $imagePath = $product['image'];
                    if (strpos($imagePath, 'storage/images/') === 0) {
                        $imgFilename = substr($imagePath, strlen('storage/images/'));
                        $imageUrl = url('/product-image-serve/' . $imgFilename);
                    } else {
                        $imageUrl = asset($imagePath);
                    }
                }
                $barcodeVal = $product['barcode'] ?: $product['code'];
            @endphp
            
            {{-- Product Card - 5 per row --}}
            <div class="col-6 col-md-4 col-lg-3 col-xl-2-4">
                <div class="card border-0 shadow-sm h-100 product-card position-relative overflow-hidden" style="transition: all 0.3s ease;">
                    {{-- Stock Badge --}}
                    <div class="position-absolute top-0 start-0 m-2 d-flex flex-column gap-1" style="z-index: 10;">
                        @if($product['stock'] <= 0)
                            <span class="badge bg-danger text-white shadow-sm" style="font-size: 9px;">Out of Stock</span>
                        @elseif($product['stock'] <= 10)
                            <span class="badge bg-warning text-dark shadow-sm" style="font-size: 9px;">Low Stock</span>
                        @else
                            <span class="badge bg-success text-white shadow-sm" style="font-size: 9px;">In Stock</span>
                        @endif
                        @if($product['variant_value'])
                            <span class="badge bg-primary text-white shadow-sm" style="font-size: 9px;">Variant</span>
                        @endif
                        @if(!empty($product['is_handmade']))
                            <span class="badge bg-warning text-dark fw-bold border border-warning shadow-sm" style="font-size: 9px;">
                                <i class="bi bi-gem me-0.5"></i>Handmade
                            </span>
                        @endif
                    </div>

                    {{-- Product Image --}}
                    <div style="height: 160px; overflow: hidden; background-color: #f8f9fa;" class="position-relative d-flex align-items-center justify-content-center">
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" class="w-100 h-100" alt="{{ $product['name'] }}" style="object-fit: cover; object-position: center;" onerror="this.src='{{ $defaultImage }}'">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                <i class="bi bi-box" style="font-size: 2.8rem;"></i>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Product Info --}}
                    <div class="card-body p-2.5 pb-2 bg-white d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="font-monospace text-muted fw-bold" style="font-size: 9px;">{{ $barcodeVal }}</span>
                                <span class="badge bg-info-subtle text-info-emphasis" style="font-size: 8.5px;">{{ $product['category'] }}</span>
                            </div>
                            <h6 class="card-title fw-bold mb-1 text-truncate" style="font-size: 11px; line-height: 1.3; color: #1e293b;" title="{{ $product['name'] }}">
                                {{ $product['name'] }}
                            </h6>

                            <div class="border-top pt-1.5 mt-1.5">
                                <div class="d-flex justify-content-between mb-0.5" style="font-size: 11.5px;">
                                    <span class="text-muted">Retail:</span>
                                    <span class="fw-bold text-success">Rs. {{ number_format($product['retail_price'], 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 11px;">
                                    <span class="text-muted">Stock:</span>
                                    <span class="fw-bold {{ $product['stock'] <= 10 ? 'text-warning' : 'text-success' }}">
                                        {{ $product['stock'] }} avail
                                        @if($product['pending'] > 0)
                                            <span class="text-muted">({{ $product['pending'] }} pend)</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- QR Code & Print Button --}}
                        <div class="border-top pt-2 mt-2 text-center">
                            <div class="d-flex justify-content-center mb-1.5">
                                <canvas class="product-qr-canvas" data-barcode="{{ $barcodeVal }}" width="55" height="55" style="width: 55px; height: 55px;"></canvas>
                            </div>
                            <button type="button" 
                                class="btn btn-sm btn-outline-dark w-100 py-1 fw-semibold shadow-none" 
                                style="font-size: 10.5px;"
                                onclick="printProductTag('{{ $barcodeVal }}', '{{ number_format($product['retail_price'], 2, '.', '') }}', '{{ addslashes($product['name']) }}')">
                                <i class="bi bi-printer me-1"></i> Print QR Tag
                            </button>
                            <a href="{{ url('/product-image/' . $product['id']) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary w-100 py-1 fw-semibold shadow-none mt-1" 
                                style="font-size: 10.5px;">
                                <i class="bi bi-camera me-1"></i> Upload Photos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                        </div>
                        <p class="text-muted mb-0 text-uppercase fw-bold" style="font-size: 12px; letter-spacing: 2px;">No products found</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- TAB 2: HANDMADE LOCAL PRODUCTS             --}}
    {{-- ========================================== --}}
    @if($activeTab === 'handmade')
        {{-- Summary Stats Widgets --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle p-2.5 bg-warning-subtle text-warning me-3">
                            <i class="bi bi-gem fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Handmade Pieces</span>
                            <h4 class="fw-bold mb-0 text-dark">{{ $totalHandmadeCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle p-2.5 bg-success-subtle text-success me-3">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Live in POS Stock</span>
                            <h4 class="fw-bold mb-0 text-success">{{ $handmadeInStockCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle p-2.5 bg-info-subtle text-info me-3">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">In Craft / Workshop</span>
                            <h4 class="fw-bold mb-0 text-info">{{ $handmadeInProgressCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle p-2.5 bg-primary-subtle text-primary me-3">
                            <i class="bi bi-cash-coin fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Craft Accumulated Cost</span>
                            <h5 class="fw-bold mb-0 text-dark">Rs. {{ number_format($handmadeTotalCostValue, 0) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Handmade Filters --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-lg-4 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search item code (SL-...), name, supplier...">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <select wire:model.live="handmadeStageFilter" class="form-select">
                            <option value="all">All Lifecycle Stages</option>
                            <option value="in_pos_stock">🟢 Live in POS Stock</option>
                            <option value="ready_for_stock">🟡 Ready for Stock</option>
                            <option value="plating">🔵 Plating Workshop</option>
                            <option value="stone_setting">🟣 Stone Setting</option>
                            <option value="raw_in_stock">⚪ Raw in Stock</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <select wire:model.live="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-12">
                        <select wire:model.live="stockFilter" class="form-select">
                            <option value="all">All Availability</option>
                            <option value="in_stock">Available in Stock</option>
                            <option value="out_of_stock">Not Live in Stock</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Handmade Items Cards Grid --}}
        <div class="row g-3">
            @forelse($handmadeItems as $hm)
            @php
                $isLiveInStock = ($hm->status === 'in_pos_stock');
                $posStockQty = $hm->productDetail ? ($hm->productDetail->stock->available_stock ?? $hm->quantity) : 0;
                $sellingPrice = (float) ($hm->selling_price ?: $hm->total_accumulated_cost);
                $costPrice = (float) $hm->total_accumulated_cost;
                $margin = $sellingPrice > $costPrice ? ($sellingPrice - $costPrice) : 0;
                $marginPercent = $costPrice > 0 ? round(($margin / $costPrice) * 100, 1) : 0;
            @endphp
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border rounded-3 shadow-sm h-100 bg-white overflow-hidden" style="transition: all 0.25s ease;">
                    {{-- Card Header --}}
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white font-monospace px-2.5 py-1.5 shadow-sm" style="font-size: 11.5px; letter-spacing: 0.5px;">
                                <i class="bi bi-tag-fill me-1"></i>{{ $hm->item_code }}
                            </span>
                            @if($hm->weight)
                                <span class="badge bg-white text-dark border font-monospace" style="font-size: 10.5px;">
                                    {{ $hm->weight }}g
                                </span>
                            @endif
                        </div>

                        <div>
                            @if($hm->status === 'in_pos_stock')
                                <span class="badge bg-success text-white px-2 py-1 shadow-sm">
                                    <i class="bi bi-check-circle-fill me-1"></i> Live in POS
                                </span>
                            @elseif($hm->status === 'ready_for_stock')
                                <span class="badge bg-warning text-dark px-2 py-1 shadow-sm">
                                    <i class="bi bi-box-seam me-1"></i> Ready for Stock
                                </span>
                            @elseif($hm->status === 'plating')
                                <span class="badge bg-info text-white px-2 py-1 shadow-sm">
                                    <i class="bi bi-brush me-1"></i> Plating Stage
                                </span>
                            @elseif($hm->status === 'stone_setting')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-gem me-1"></i> Stone Setting
                                </span>
                            @else
                                <span class="badge bg-secondary text-white px-2 py-1">
                                    <i class="bi bi-archive me-1"></i> Raw Piece
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-start mb-3">
                            {{-- QR Code Box --}}
                            <div class="col-auto text-center">
                                <div class="p-2 border rounded-2 bg-white d-flex flex-column align-items-center justify-content-center shadow-sm" style="width: 90px; height: 90px;">
                                    <canvas class="product-qr-canvas" data-barcode="{{ $hm->item_code }}" width="72" height="72" style="width: 72px; height: 72px;"></canvas>
                                </div>
                                <span class="d-block font-monospace text-muted mt-1" style="font-size: 8.5px;">Scan QR Tag</span>
                            </div>

                            {{-- Product Information --}}
                            <div class="col">
                                <h5 class="fw-bold mb-1 text-dark" style="font-size: 15px;">{{ $hm->item_name }}</h5>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-light text-muted border text-uppercase" style="font-size: 9.5px;">
                                        {{ $hm->category->category_name ?? 'General' }}
                                    </span>
                                    <span class="text-muted small" style="font-size: 11px;">
                                        <i class="bi bi-person me-0.5"></i>{{ $hm->supplier->name ?? 'Local Smith' }}
                                    </span>
                                </div>

                                {{-- Craft Cost Breakdown --}}
                                <div class="bg-light p-2 rounded-2 small mb-2" style="font-size: 11px;">
                                    <div class="d-flex justify-content-between text-muted">
                                        <span>Raw Cost:</span>
                                        <span class="fw-semibold text-dark">Rs. {{ number_format($hm->purchase_cost, 2) }}</span>
                                    </div>
                                    @if($hm->stone_cost > 0)
                                    <div class="d-flex justify-content-between text-muted">
                                        <span>Stone Setting:</span>
                                        <span class="text-info fw-semibold">+ Rs. {{ number_format($hm->stone_cost, 2) }}</span>
                                    </div>
                                    @endif
                                    @if($hm->plating_cost > 0)
                                    <div class="d-flex justify-content-between text-muted">
                                        <span>Workshop Plating:</span>
                                        <span class="text-warning-emphasis fw-semibold">+ Rs. {{ number_format($hm->plating_cost, 2) }}</span>
                                    </div>
                                    @endif
                                    <div class="d-flex justify-content-between border-top pt-1 mt-1 fw-bold text-dark">
                                        <span>Accumulated Cost:</span>
                                        <span>Rs. {{ number_format($hm->total_accumulated_cost, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Selling Price & Availability Status Box --}}
                        <div class="p-2.5 rounded-2 {{ $isLiveInStock ? 'bg-success-subtle border border-success-subtle' : 'bg-light border' }}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-muted">Retail Selling Price:</span>
                                <span class="fw-bold fs-6 text-success">
                                    Rs. {{ number_format($sellingPrice, 2) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center" style="font-size: 11.5px;">
                                <span class="text-muted">Stock Availability:</span>
                                @if($isLiveInStock)
                                    <span class="fw-bold text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>{{ $posStockQty }} Available in POS Billing
                                    </span>
                                @else
                                    <span class="fw-semibold text-warning-emphasis">
                                        <i class="bi bi-hourglass-split me-1"></i>{{ $hm->quantity }} in Workshop (Not yet in POS)
                                    </span>
                                @endif
                            </div>
                            @if($isLiveInStock && $margin > 0)
                            <div class="d-flex justify-content-between align-items-center mt-1 pt-1 border-top" style="font-size: 10.5px;">
                                <span class="text-muted">Estimated Margin:</span>
                                <span class="fw-bold text-primary">+Rs. {{ number_format($margin, 2) }} ({{ $marginPercent }}%)</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="p-2.5 bg-light border-top d-flex gap-2">
                        <button type="button" 
                            class="btn btn-sm btn-dark flex-grow-1 fw-semibold d-flex align-items-center justify-content-center gap-1 shadow-sm"
                            onclick="printProductTag('{{ $hm->item_code }}', '{{ number_format($sellingPrice, 2, '.', '') }}', '{{ addslashes($hm->item_name) }}')">
                            <i class="bi bi-printer"></i> Print QR Tag
                        </button>

                        <button type="button" 
                            class="btn btn-sm btn-outline-primary fw-semibold px-2.5 shadow-none"
                            wire:click="viewHandmadeDetail({{ $hm->id }})"
                            title="View Full Lifecycle & Costs">
                            <i class="bi bi-eye me-1"></i> Details
                        </button>

                        @if($hm->status === 'ready_for_stock')
                            <a href="{{ route('shop-staff.handmade-purchasing') }}" class="btn btn-sm btn-warning text-dark fw-bold px-2.5 shadow-sm" title="Add Directly to POS Stock">
                                <i class="bi bi-box-arrow-in-down"></i> Add Stock
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-gem fs-1"></i>
                        </div>
                        <h6 class="fw-bold text-dark">No Handmade Products Found</h6>
                        <p class="text-muted small mb-3">No handmade local pieces match your selected filters. Register new handmade pieces in the purchasing module.</p>
                        <a href="{{ route('shop-staff.handmade-purchasing') }}" class="btn btn-sm btn-warning text-dark fw-bold px-3">
                            <i class="bi bi-plus-lg me-1"></i> Register Handmade Purchase
                        </a>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- DETAIL MODAL FOR HANDMADE PIECE            --}}
    {{-- ========================================== --}}
    @if($showHandmadeDetailModal && $selectedHandmadeItem)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark font-monospace fw-bold px-2.5 py-1">
                            {{ $selectedHandmadeItem->item_code }}
                        </span>
                        <h5 class="modal-title fw-bold mb-0">{{ $selectedHandmadeItem->item_name }}</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeHandmadeDetailModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        {{-- Large QR Preview --}}
                        <div class="col-md-4 text-center">
                            <div class="p-3 border rounded-3 bg-white d-flex flex-column align-items-center justify-content-center shadow-sm">
                                <canvas class="product-qr-canvas mb-2" data-barcode="{{ $selectedHandmadeItem->item_code }}" width="130" height="130" style="width: 130px; height: 130px;"></canvas>
                                <span class="font-monospace fw-bold fs-6 text-dark">{{ $selectedHandmadeItem->item_code }}</span>
                                <small class="text-muted">Standard Jewelry 2D Tag</small>
                            </div>
                            <button type="button" 
                                class="btn btn-dark w-100 fw-bold mt-2 shadow-sm"
                                onclick="printProductTag('{{ $selectedHandmadeItem->item_code }}', '{{ number_format($selectedHandmadeItem->selling_price ?: $selectedHandmadeItem->total_accumulated_cost, 2, '.', '') }}', '{{ addslashes($selectedHandmadeItem->item_name) }}')">
                                <i class="bi bi-printer me-1"></i> Print Jewelry Tag
                            </button>
                        </div>

                        {{-- Details & Costs --}}
                        <div class="col-md-8">
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light text-muted w-40">Category</th>
                                            <td class="fw-semibold">{{ $selectedHandmadeItem->category->category_name ?? 'General' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Weight</th>
                                            <td class="fw-semibold">{{ $selectedHandmadeItem->weight ? $selectedHandmadeItem->weight . 'g' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Local Smith Supplier</th>
                                            <td class="fw-semibold">{{ $selectedHandmadeItem->supplier->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Purchase Batch Code</th>
                                            <td class="font-monospace">{{ $selectedHandmadeItem->purchase->purchase_code ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Current Status</th>
                                            <td>
                                                <span class="badge {{ $selectedHandmadeItem->status === 'in_pos_stock' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ strtoupper(str_replace('_', ' ', $selectedHandmadeItem->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Raw Purchase Cost</th>
                                            <td>Rs. {{ number_format($selectedHandmadeItem->purchase_cost, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Stone Works Cost</th>
                                            <td>Rs. {{ number_format($selectedHandmadeItem->stone_cost, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">Plating Workshop Fee</th>
                                            <td>Rs. {{ number_format($selectedHandmadeItem->plating_cost, 2) }}</td>
                                        </tr>
                                        <tr class="table-primary fw-bold">
                                            <th>Total Accumulated Cost</th>
                                            <td>Rs. {{ number_format($selectedHandmadeItem->total_accumulated_cost, 2) }}</td>
                                        </tr>
                                        <tr class="table-success fw-bold">
                                            <th>POS Selling Price</th>
                                            <td>Rs. {{ number_format($selectedHandmadeItem->selling_price ?: $selectedHandmadeItem->total_accumulated_cost, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" wire:click="closeHandmadeDetailModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Styling & Custom Scripts --}}
    @push('styles')
    <style>
        .col-xl-2-4 {
            flex: 0 0 auto;
            width: 20%;
        }
        @media (max-width: 1200px) {
            .col-xl-2-4 {
                width: 25%;
            }
        }
        @media (max-width: 992px) {
            .col-xl-2-4 {
                width: 33.333%;
            }
        }
        @media (max-width: 768px) {
            .col-xl-2-4 {
                width: 50%;
            }
        }
        .product-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
            transform: translateY(-2px);
        }
        .product-qr-canvas {
            image-rendering: pixelated;
        }
    </style>
    @endpush

    @push('scripts')
    {{-- Load BWIP-JS for Barcode and QR Code Tag Generation --}}
    <script src="https://cdn.jsdelivr.net/npm/bwip-js@3.0.4/dist/bwip-js-min.js"></script>
    <script>
        // Label Settings loaded from Database
        function loadPrintSettings() {
            return {
                width: {{ $labelSettings['label_width'] ?? 48 }},
                height: {{ $labelSettings['label_height'] ?? 12 }},
                padding: {{ $labelSettings['label_padding'] ?? 1 }},
                textWidth: {{ $labelSettings['label_text_width'] ?? 35 }},
                fontFamily: '{{ $labelSettings['label_font_family'] ?? 'Courier New' }}',
                fontShop: {{ $labelSettings['label_font_shop'] ?? 6 }},
                fontPrice: {{ $labelSettings['label_font_price'] ?? 8 }},
                fontBarcode: {{ $labelSettings['label_font_barcode'] ?? 5 }},
                qrSize: {{ $labelSettings['label_qr_size'] ?? 11 }},
                barcodeType: '{{ $labelSettings['label_barcode_type'] ?? 'qrcode_m' }}',
                showShop: {{ ($labelSettings['label_show_shop'] ?? true) ? 'true' : 'false' }},
                showBarcodeText: {{ ($labelSettings['label_show_barcode_text'] ?? true) ? 'true' : 'false' }},
                showQR: {{ ($labelSettings['label_show_qr'] ?? true) ? 'true' : 'false' }}
            };
        }

        // Build Print Label CSS
        function buildLabelCSS(s) {
            const qrMM   = s.showQR ? Math.min(s.qrSize, s.height) : 0;
            const textW  = Math.max(s.textWidth, 4);
            const qrSecW = Math.max(s.width - textW, qrMM);
            const pad    = Math.min(s.padding, textW / 4);
            return `
                * { margin:0; padding:0; box-sizing:border-box; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
                body { font-family: Arial, Helvetica, sans-serif; margin:0; padding:0; background:white; font-weight:700; color:#000; }
                .label-wrapper {
                    width:${s.width}mm; height:${s.height}mm;
                    display:flex; align-items:stretch; overflow:hidden;
                    page-break-inside:avoid;
                    font-family: Arial, Helvetica, sans-serif;
                }
                .text-section {
                    width:${textW}mm; flex-shrink:0;
                    display:flex; flex-direction:column; justify-content:center;
                    padding:0 ${pad}mm; overflow:hidden;
                }
                .info-shop {
                    font-size:${s.fontShop}pt; font-weight:800; color:#000;
                    white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.2;
                    text-transform:uppercase;
                    display:${s.showShop ? 'block' : 'none'};
                }
                .info-price {
                    font-size:${s.fontPrice}pt; font-weight:800; color:#000; line-height:1.2;
                }
                .info-barcode {
                    font-size:${s.fontBarcode}pt; font-weight:700; color:#000; line-height:1.2;
                    display:${s.showBarcodeText ? 'block' : 'none'};
                }
                .qr-section {
                    width:${qrSecW}mm; flex-shrink:0;
                    display:${s.showQR ? 'flex' : 'none'};
                    align-items:center; justify-content:center;
                }
                .qr-section canvas, .qr-section img, .qr-section svg {
                    width:${qrMM}mm !important; height:${qrMM}mm !important;
                    max-width:100%; max-height:100%; object-fit:contain;
                    image-rendering: pixelated;
                    image-rendering: -webkit-optimize-contrast;
                    shape-rendering: crispEdges;
                }
                @media print {
                    body { margin:0; padding:0; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
                    @page { size:${s.width}mm ${s.height}mm; margin:0; }
                    .qr-section canvas, .qr-section img, .qr-section svg {
                        image-rendering: pixelated;
                        image-rendering: -webkit-optimize-contrast;
                        shape-rendering: crispEdges;
                    }
                }
            `;
        }

        // Single Label Print Window Trigger
        window.printProductTag = function(barcode, retailPrice, productName) {
            if (!barcode) {
                alert('No barcode or QR code available for this product.');
                return;
            }
            const s = loadPrintSettings();
            const printWindow = window.open('', '_blank', 'width=460,height=340');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print Tag - ${barcode}</title>
                    <script src="https://cdn.jsdelivr.net/npm/bwip-js@3.0.4/dist/bwip-js-min.js"><\/script>
                    <style>${buildLabelCSS(s)}<\/style>
                </head>
                <body>
                    <div class="label-wrapper">
                        <div class="text-section">
                            <div class="info-shop">Jaffna Gold</div>
                            <div class="info-price">Rs.${retailPrice}</div>
                            <div class="info-barcode">${barcode}</div>
                        </div>
                        <div class="qr-section">
                            <canvas id="qrcode"></canvas>
                        </div>
                    </div>
                    <script>
                        if (${s.showQR}) {
                            let bcid = '${s.barcodeType === 'code128' ? 'code128' : (s.barcodeType === 'datamatrix' ? 'datamatrix' : 'qrcode')}';
                            let opts = { text: '${barcode}', bcid: bcid, scale: 3, padding: 0 };
                            if ('${s.barcodeType}' === 'qrcode_l') opts.eclevel = 'L';
                            if ('${s.barcodeType}' === 'qrcode_m') opts.eclevel = 'M';
                            try { bwipjs.toCanvas(document.getElementById('qrcode'), opts); } catch(e) { console.error(e); }
                        }
                        setTimeout(() => { window.print(); }, 700);
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        };

        // Render QR Codes into all canvas elements
        function renderAllQRCodes() {
            if (typeof bwipjs === 'undefined') return;
            document.querySelectorAll('canvas.product-qr-canvas[data-barcode]').forEach(function(canvas) {
                const barcode = canvas.getAttribute('data-barcode');
                if (!barcode) return;
                try {
                    bwipjs.toCanvas(canvas, {
                        bcid: 'qrcode',
                        text: barcode,
                        scale: 2,
                        padding: 0,
                        eclevel: 'M'
                    });
                } catch (e) {
                    console.error('Error rendering QR for ' + barcode, e);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(renderAllQRCodes, 150);
        });

        // Livewire re-render hook
        document.addEventListener('livewire:navigated', function() {
            setTimeout(renderAllQRCodes, 150);
        });

        // Mutation observer to re-render when tabs or pagination changes
        const observer = new MutationObserver(function(mutations) {
            let shouldRerender = false;
            mutations.forEach(function(m) {
                if (m.addedNodes.length > 0) shouldRerender = true;
            });
            if (shouldRerender) {
                setTimeout(renderAllQRCodes, 100);
            }
        });

        const container = document.querySelector('.container-fluid');
        if (container) {
            observer.observe(container, { childList: true, subtree: true });
        }
    </script>
    @endpush
</div>
