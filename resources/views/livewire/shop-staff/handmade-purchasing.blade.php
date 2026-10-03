<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-hammer text-warning me-2"></i> Local Handmade Purchasing & Workshop
            </h3>
            <p class="text-muted mb-0">Manage raw castings, stone setting, plating workshop pipeline, and POS stock conversion</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button wire:click="openCreateSupplierModal" class="btn btn-outline-dark fw-semibold shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Add Local Supplier
            </button>
            <button wire:click="openPurchaseModal" class="btn btn-warning text-dark fw-bold shadow-sm">
                <i class="bi bi-bag-plus-fill me-1"></i> New Handmade Purchase
            </button>
        </div>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Total Purchased</div>
                    <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($stats['total_items']) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-boxes me-1"></i>Handmade pieces</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Raw In-Drawer</div>
                    <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($stats['raw_in_stock']) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-inbox me-1"></i>Awaiting work</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Stone Setting</div>
                    <div class="fs-4 fw-bold text-info mt-1">{{ number_format($stats['stone_setting']) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-gem me-1"></i>Gemstone work</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Plating Workshop</div>
                    <div class="fs-4 fw-bold text-primary mt-1">{{ number_format($stats['plating'] + $stats['ready_for_stock']) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-magic me-1"></i>With Ramesh / Sanker</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Active in POS Stock</div>
                    <div class="fs-4 fw-bold text-success mt-1">{{ number_format($stats['in_pos_stock']) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-cart-check me-1"></i>Plated & sellable</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Supplier Payables</div>
                    <div class="fs-5 fw-bold text-danger mt-1">Rs. {{ number_format($stats['total_supplier_payable'], 0) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-person-fill-exclamation me-1"></i>Due to local smiths</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Local Suppliers Summary Section --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-truck text-warning me-2"></i> Local Artisan Suppliers & Accounts
                </h6>
                <small class="text-muted">Maintain balances, purchases, and payout vouchers for local goldsmiths and suppliers</small>
            </div>
            <button wire:click="openCreateSupplierModal" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-plus-lg me-1"></i> New Supplier
            </button>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                @forelse($suppliers as $supplier)
                <div class="col-lg-4 col-md-6">
                    <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                                        {{ $supplier->name }}
                                        <button wire:click="openEditSupplierModal({{ $supplier->id }})" class="btn btn-sm text-muted p-0" title="Edit Opening Balance">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </h6>
                                    <small class="text-muted">{{ $supplier->phone ?: 'No phone' }} @if($supplier->address) • {{ $supplier->address }} @endif</small>
                                </div>
                                <span class="badge {{ $supplier->outstanding_balance > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} fs-6">
                                    Due: Rs. {{ number_format($supplier->outstanding_balance, 0) }}
                                </span>
                            </div>
                            <div class="row g-1 text-center small my-2 py-1.5 bg-white border rounded">
                                <div class="col-4">
                                    <span class="text-muted d-block" style="font-size: 10px;">Purchases</span>
                                    <span class="fw-bold">Rs. {{ number_format($supplier->total_purchased, 0) }}</span>
                                </div>
                                <div class="col-4 border-start">
                                    <span class="text-muted d-block" style="font-size: 10px;">Paid</span>
                                    <span class="fw-bold text-success">Rs. {{ number_format($supplier->total_paid, 0) }}</span>
                                </div>
                                <div class="col-4 border-start">
                                    <span class="text-muted d-block" style="font-size: 10px;">Past Debt</span>
                                    <span class="fw-bold text-secondary">Rs. {{ number_format($supplier->opening_payable_balance, 0) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <button wire:click="openSupplierLedgerModal({{ $supplier->id }})" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-journal-text me-1"></i> Ledger
                            </button>
                            <button wire:click="openSupplierPaymentModal({{ $supplier->id }})" class="btn btn-sm btn-warning text-dark fw-bold">
                                <i class="bi bi-cash me-1"></i> Pay
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-3 text-muted">
                    No local handmade suppliers registered yet. Click "Add Local Supplier" to add your local smiths.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Filters Toolbar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search item, code, supplier...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select wire:model.live="typeFilter" class="form-select fw-semibold">
                        <option value="all">All 3 Product Types</option>
                        <option value="raw_product">1. Raw Product (Needs Stone + Plating)</option>
                        <option value="raw_with_stone">2. Raw with Stone (Needs Plating)</option>
                        <option value="final_sale_plated">3. Final Plated (Direct to Stock)</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="all">All Workshop Stages</option>
                        <option value="raw_in_stock">Raw in Drawer (Waiting)</option>
                        <option value="stone_setting">At Stone Setting</option>
                        <option value="plating">At Plating Workshop</option>
                        <option value="ready_for_stock">Plated (Ready for POS Stock)</option>
                        <option value="in_pos_stock">Active in POS Stock</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select wire:model.live="supplierFilter" class="form-select">
                        <option value="all">All Local Suppliers</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} (Due: Rs. {{ number_format($s->outstanding_balance, 0) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Handmade Items Inventory Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 py-3">Item Code & Date</th>
                            <th>Item Name</th>
                            <th>Local Supplier</th>
                            <th>Product Type</th>
                            <th class="text-end">Cost Breakdown</th>
                            <th class="text-end">Final Cost / Selling Price</th>
                            <th class="text-center">Stage & Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="ps-3">
                                <span class="fw-bold text-dark d-block">{{ $item->item_code }}</span>
                                <small class="text-muted">{{ $item->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark d-block">{{ $item->item_name }}</span>
                                @if($item->category)
                                    <span class="badge bg-light text-muted border text-uppercase" style="font-size: 10px;">{{ $item->category->category_name }}</span>
                                @endif
                                @if($item->weight)
                                    <small class="text-muted ms-1">{{ $item->weight }}g</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-medium text-dark d-block">{{ $item->supplier->name ?? 'N/A' }}</span>
                                <small class="text-muted">Batch: {{ $item->purchase->purchase_code ?? '-' }}</small>
                            </td>
                            <td>
                                @if($item->product_type === 'raw_product')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        <i class="bi bi-box-seam me-1"></i>Raw Product
                                    </span>
                                    <small class="text-muted d-block mt-0.5" style="font-size: 10px;">Needs Stone + Plating</small>
                                @elseif($item->product_type === 'raw_with_stone')
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                        <i class="bi bi-gem me-1"></i>Raw with Stone
                                    </span>
                                    <small class="text-muted d-block mt-0.5" style="font-size: 10px;">Needs Plating</small>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i>Final Plated Sale
                                    </span>
                                    <small class="text-muted d-block mt-0.5" style="font-size: 10px;">Direct to Stock</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="small d-block text-secondary">Buy: Rs. {{ number_format($item->purchase_cost, 0) }}</span>
                                @if($item->stone_cost > 0)
                                    <span class="small d-block text-info">+ Stone: Rs. {{ number_format($item->stone_cost, 0) }}</span>
                                @endif
                                @if($item->plating_cost > 0)
                                    <span class="small d-block text-warning">+ Plate: Rs. {{ number_format($item->plating_cost, 0) }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-dark d-block">Cost: Rs. {{ number_format($item->total_accumulated_cost, 2) }}</span>
                                @if($item->selling_price)
                                    <span class="text-success fw-bold small d-block">Sell: Rs. {{ number_format($item->selling_price, 2) }}</span>
                                @else
                                    <small class="text-muted">Price T.B.D.</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->status === 'in_pos_stock')
                                    <span class="badge bg-success px-2 py-1 text-uppercase" style="font-size: 11px;">
                                        <i class="bi bi-cart-check me-1"></i>In POS Stock
                                    </span>
                                    @if($item->productDetail)
                                        <small class="text-muted d-block mt-0.5" style="font-size: 10px;">BC: {{ $item->productDetail->barcode }}</small>
                                    @endif
                                @elseif($item->status === 'ready_for_stock')
                                    <span class="badge bg-info text-dark px-2 py-1 text-uppercase" style="font-size: 11px;">
                                        <i class="bi bi-bell-fill me-1"></i>Ready for Stock
                                    </span>
                                @elseif($item->status === 'plating')
                                    <span class="badge bg-primary px-2 py-1 text-uppercase" style="font-size: 11px;">
                                        <i class="bi bi-magic me-1"></i>At Plating
                                    </span>
                                    @if($item->platingWorker)
                                        <small class="text-dark d-block fw-semibold" style="font-size: 10px;">{{ $item->platingWorker->name }}</small>
                                    @endif
                                @elseif($item->status === 'stone_setting')
                                    @if($item->stone_cost > 0)
                                        <span class="badge bg-warning text-dark px-2 py-1 text-uppercase" style="font-size: 11px;">
                                            <i class="bi bi-gem me-1"></i>Stone Set
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-dark border px-2 py-1 text-uppercase" style="font-size: 11px;">
                                            <i class="bi bi-skip-forward me-1"></i>No Stone
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary px-2 py-1 text-uppercase" style="font-size: 11px;">
                                        <i class="bi bi-inbox me-1"></i>Raw In-Drawer
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    {{-- Stage Progression Actions --}}
                                    @if($item->status !== 'in_pos_stock')
                                        @if($item->product_type === 'raw_product' && $item->status === 'raw_in_stock')
                                            <button wire:click="openStoneSettingModal({{ $item->id }})" class="btn btn-warning text-dark fw-bold" title="Stage 1: Add Stone Setting Cost">
                                                <i class="bi bi-gem me-1"></i> Set Stone
                                            </button>
                                            <button wire:click="skipStoneSetting({{ $item->id }})" class="btn btn-outline-secondary" title="No stones needed for plain piece - skip directly to Plating">
                                                <i class="bi bi-skip-forward me-1"></i> Skip Stone
                                            </button>
                                        @elseif($item->product_type === 'raw_product' && $item->status === 'stone_setting')
                                            <button wire:click="openPlatingModal({{ $item->id }})" class="btn btn-primary text-white fw-bold" title="Stage 2: Send to Plating Workshop">
                                                <i class="bi bi-magic me-1"></i> Send Plating
                                            </button>
                                            <button wire:click="openStoneSettingModal({{ $item->id }})" class="btn btn-outline-secondary" title="Add / Edit Stone Details">
                                                <i class="bi bi-gem"></i>
                                            </button>
                                        @elseif($item->product_type === 'raw_with_stone' && $item->status === 'raw_in_stock')
                                            <button wire:click="openPlatingModal({{ $item->id }})" class="btn btn-primary text-white fw-bold" title="Stage: Send to Plating Workshop">
                                                <i class="bi bi-magic me-1"></i> Send Plating
                                            </button>
                                        @elseif(in_array($item->status, ['ready_for_stock', 'plating']) || ($item->product_type === 'final_sale_plated' && $item->status !== 'in_pos_stock'))
                                            <button wire:click="openAddToStockModal({{ $item->id }})" class="btn btn-success fw-bold" title="Convert to POS Sellable Stock">
                                                <i class="bi bi-box-arrow-in-down me-1"></i> Add to POS Stock
                                            </button>
                                        @endif
                                        <button wire:click="confirmDeleteItem({{ $item->id }})" class="btn btn-outline-danger" title="Delete Item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        <button type="button" 
                                            class="btn btn-dark fw-semibold px-2 py-1 shadow-sm" 
                                            onclick="printProductTag('{{ $item->item_code }}', '{{ number_format($item->selling_price ?: $item->total_accumulated_cost, 2, '.', '') }}', '{{ addslashes($item->item_name) }}')"
                                            title="Print Jewelry QR Tag">
                                            <i class="bi bi-printer me-1"></i> Print Tag
                                        </button>
                                        <span class="btn btn-sm btn-outline-success disabled">
                                            <i class="bi bi-check2-all me-1"></i> Live in POS
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-hammer fs-1 d-block mb-2 text-secondary"></i>
                                No handmade items found matching your filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="p-3 border-top">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- New Handmade Purchase Intake Modal (Multi-Item Batch) --}}
    @if($showPurchaseModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.65);" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-2xl rounded-3">
                <div class="modal-header bg-warning text-dark py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark text-warning p-2"><i class="bi bi-boxes fs-6"></i></span>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">New Local Handmade Jewelry Purchase Batch</h5>
                            <small class="text-dark-50 fw-semibold">Add all purchased products from the artisan in a single purchase batch</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" wire:click="closePurchaseModal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <form wire:submit.prevent="savePurchase">
                        {{-- 1. Batch Header (Supplier, Date & Ref) --}}
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-body p-3 bg-white">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">
                                            <i class="bi bi-person-badge text-warning me-1"></i> Local Supplier / Goldsmith <span class="text-danger">*</span>
                                        </label>
                                        <select wire:model="purchase_supplier_id" class="form-select fw-semibold @error('purchase_supplier_id') is-invalid @enderror">
                                            <option value="">-- Select Local Supplier --</option>
                                            @foreach($suppliers as $s)
                                                <option value="{{ $s->id }}">{{ $s->name }} (Current Due: Rs. {{ number_format($s->outstanding_balance, 0) }})</option>
                                            @endforeach
                                        </select>
                                        @error('purchase_supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">
                                            <i class="bi bi-calendar-event text-warning me-1"></i> Purchase Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" wire:model="purchase_date" class="form-control fw-semibold @error('purchase_date') is-invalid @enderror">
                                        @error('purchase_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">
                                            <i class="bi bi-card-text text-warning me-1"></i> Batch / Invoice Reference
                                        </label>
                                        <input type="text" wire:model="purchase_notes" class="form-control" placeholder="e.g. Inv #892, Weekly batch...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Purchased Items List --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-gem text-warning me-1"></i> Products in this Purchase ({{ count($purchase_items) }})
                                </h6>
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle fw-bold">
                                    Batch Total: Rs. {{ number_format($this->totalPurchaseAmount, 2) }}
                                </span>
                            </div>
                            <button type="button" wire:click="addPurchaseItem" class="btn btn-sm btn-warning text-dark fw-bold px-3 shadow-sm">
                                <i class="bi bi-plus-lg me-1"></i> + Add Another Product
                            </button>
                        </div>

                        <div class="space-y-3 mb-3">
                            @foreach($purchase_items as $idx => $item)
                            <div class="card border rounded-3 shadow-sm bg-white overflow-hidden mb-3">
                                {{-- Item Top Bar --}}
                                <div class="p-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-dark text-white fw-bold px-2 py-1">Item #{{ $idx + 1 }}</span>
                                        @php
                                            $rawName = trim($item['item_name'] ?? '');
                                            $cleanSlug = strtoupper(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $rawName), '-'));
                                            if (strlen($cleanSlug) > 25) {
                                                $cleanSlug = rtrim(substr($cleanSlug, 0, 25), '-');
                                            }
                                            $previewCode = 'SL-' . ($cleanSlug ?: 'PRODUCT') . '-001';
                                        @endphp
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-2 py-1 font-monospace" title="Generated Item Code Pattern">
                                            <i class="bi bi-tag-fill me-1"></i>{{ $previewCode }}
                                        </span>
                                        
                                        {{-- Product Type Selector --}}
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" wire:model.live="purchase_items.{{ $idx }}.product_type" value="raw_product" id="type_raw_{{ $idx }}" autocomplete="off">
                                            <label class="btn {{ ($item['product_type'] ?? '') === 'raw_product' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}" for="type_raw_{{ $idx }}">
                                                1. Raw Product
                                            </label>

                                            <input type="radio" class="btn-check" wire:model.live="purchase_items.{{ $idx }}.product_type" value="raw_with_stone" id="type_stone_{{ $idx }}" autocomplete="off">
                                            <label class="btn {{ ($item['product_type'] ?? '') === 'raw_with_stone' ? 'btn-info text-white fw-bold' : 'btn-outline-secondary' }}" for="type_stone_{{ $idx }}">
                                                2. Raw with Stone
                                            </label>

                                            <input type="radio" class="btn-check" wire:model.live="purchase_items.{{ $idx }}.product_type" value="final_sale_plated" id="type_plated_{{ $idx }}" autocomplete="off">
                                            <label class="btn {{ ($item['product_type'] ?? '') === 'final_sale_plated' ? 'btn-success text-white fw-bold' : 'btn-outline-secondary' }}" for="type_plated_{{ $idx }}">
                                                3. Final Plated Sale
                                            </label>
                                        </div>

                                        @if(($item['product_type'] ?? '') === 'raw_product')
                                        <div class="form-check form-switch mb-0 ms-2">
                                            <input class="form-check-input" type="checkbox" id="skip_stone_{{ $idx }}" wire:model.live="purchase_items.{{ $idx }}.skip_stone_setting">
                                            <label class="form-check-label small fw-semibold text-dark" for="skip_stone_{{ $idx }}">
                                                <i class="bi bi-shield-check text-warning"></i> Plain piece (Skip stone works)
                                            </label>
                                        </div>
                                        @endif
                                    </div>

                                    <div>
                                        @if(count($purchase_items) > 1)
                                        <button type="button" wire:click="removePurchaseItem({{ $idx }})" class="btn btn-sm btn-outline-danger py-0 px-2 fw-semibold" title="Remove this item">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Item Inputs Body --}}
                                <div class="card-body p-3">
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-muted mb-1">Item Name / Description <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                class="form-control form-control-sm fw-semibold @error('purchase_items.'.$idx.'.item_name') is-invalid @enderror" 
                                                wire:model.live.debounce.250ms="purchase_items.{{ $idx }}.item_name" 
                                                placeholder="e.g. KODI Chain, Peacock Ring...">
                                            <div class="form-text text-muted mt-1" style="font-size: 11px;">
                                                <i class="bi bi-info-circle text-primary me-1"></i>Code generated as: <strong class="text-primary font-monospace">{{ $previewCode }}</strong>
                                            </div>
                                            @error('purchase_items.'.$idx.'.item_name') <div class="text-danger small mt-1" style="font-size: 11px;">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-muted mb-1">Category</label>
                                            <select wire:model="purchase_items.{{ $idx }}.category_id" class="form-select form-select-sm">
                                                <option value="">-- General --</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label small fw-bold text-muted mb-1">Qty <span class="text-danger">*</span></label>
                                            <input type="number" min="1" 
                                                class="form-control form-control-sm fw-bold text-center @error('purchase_items.'.$idx.'.quantity') is-invalid @enderror" 
                                                wire:model.live="purchase_items.{{ $idx }}.quantity">
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label small fw-bold text-muted mb-1">Weight</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" 
                                                    class="form-control form-control-sm px-1 text-center" 
                                                    wire:model="purchase_items.{{ $idx }}.weight" 
                                                    placeholder="0.0">
                                                <span class="input-group-text px-1" style="font-size: 10px;">g</span>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-muted mb-1">Cost/Unit (Rs.) <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text px-1 text-muted" style="font-size: 10px;">Rs.</span>
                                                <input type="number" step="0.01" 
                                                    class="form-control form-control-sm fw-bold text-end @error('purchase_items.'.$idx.'.purchase_cost') is-invalid @enderror" 
                                                    wire:model.live="purchase_items.{{ $idx }}.purchase_cost" 
                                                    placeholder="0.00">
                                            </div>
                                            @error('purchase_items.'.$idx.'.purchase_cost') <div class="text-danger small mt-1" style="font-size: 11px;">{{ $message }}</div> @enderror
                                        </div>

                                        @if(($item['product_type'] ?? '') === 'final_sale_plated')
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-success mb-1">POS Selling Price <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text px-1 bg-success text-white" style="font-size: 10px;">Rs.</span>
                                                <input type="number" step="0.01" 
                                                    class="form-control form-control-sm fw-bold text-success text-end @error('purchase_items.'.$idx.'.selling_price') is-invalid @enderror" 
                                                    wire:model="purchase_items.{{ $idx }}.selling_price" 
                                                    placeholder="Retail">
                                            </div>
                                            @error('purchase_items.'.$idx.'.selling_price') <div class="text-danger small mt-1" style="font-size: 11px;">{{ $message }}</div> @enderror
                                        </div>
                                        @else
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold text-muted mb-1">Subtotal</label>
                                            <div class="form-control form-control-sm bg-light fw-bold text-end border-0 text-dark">
                                                Rs. {{ number_format(intval($item['quantity'] ?? 1) * floatval($item['purchase_cost'] ?? 0), 2) }}
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    @if(($item['product_type'] ?? '') === 'final_sale_plated')
                                    <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                                        <small class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Will create sellable POS stock with auto barcode</small>
                                        <span class="fw-bold small text-dark">
                                            Subtotal: Rs. {{ number_format(intval($item['quantity'] ?? 1) * floatval($item['purchase_cost'] ?? 0), 2) }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Add Item Button Bar --}}
                        <div class="text-center mb-4">
                            <button type="button" wire:click="addPurchaseItem" class="btn btn-outline-warning text-dark fw-bold px-4 py-2 border-2 shadow-sm bg-white">
                                <i class="bi bi-plus-circle me-1 text-warning"></i> + Click Here to Add Another Product to this Purchase
                            </button>
                        </div>

                        {{-- 3. Payment & Totals Summary --}}
                        <div class="card border-0 shadow-sm rounded-3 bg-white mb-3">
                            <div class="card-body p-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3">
                                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Purchase Amount</span>
                                            <span class="fs-4 fw-black text-dark">Rs. {{ number_format($this->totalPurchaseAmount, 2) }}</span>
                                            <small class="text-muted d-block mt-1">{{ count($purchase_items) }} product line(s)</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Paid to Supplier Now (Rs.)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted">Rs.</span>
                                            <input type="number" step="0.01" wire:model.live="paid_amount" class="form-control form-control-lg fw-bold text-success" placeholder="0.00">
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            Remaining Due: <strong class="text-danger">Rs. {{ number_format(max(0, $this->totalPurchaseAmount - floatval($paid_amount ?? 0)), 2) }}</strong>
                                        </small>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Payment Method</label>
                                        <select wire:model="payment_method" class="form-select form-select-lg fw-semibold">
                                            <option value="cash">Cash (Counter Cash)</option>
                                            <option value="bank_transfer">Bank Transfer / Online</option>
                                            <option value="credit">Credit / Add to Supplier Due</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closePurchaseModal">Cancel</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold px-5 py-2 shadow">
                                <i class="bi bi-check2-circle me-1"></i> Register Purchase Batch ({{ count($purchase_items) }} Products)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stage 1: Stone Setting Work Modal --}}
    @if($showStoneSettingModal && $stoneItem)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-warning text-dark py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-gem me-2"></i> Stage 1: Stone Setting Work
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeStoneSettingModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Item:</span>
                            <span class="fw-bold text-dark">{{ $stoneItem->item_code }} - {{ $stoneItem->item_name }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Initial Raw Purchase Cost:</span>
                            <span class="fw-bold">Rs. {{ number_format($stoneItem->purchase_cost, 2) }}</span>
                        </div>
                    </div>

                    <form wire:submit.prevent="saveStoneSetting">
                        <div class="alert alert-light border py-2 px-3 small d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted"><i class="bi bi-info-circle me-1"></i> Plain piece with no stones?</span>
                            <button type="button" wire:click="skipStoneSetting({{ $stoneItem->id }})" class="btn btn-sm btn-outline-secondary py-1">
                                <i class="bi bi-skip-forward me-1"></i> Skip Stone Work
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Stone & Setting Labor Cost (Rs.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rs.</span>
                                <input type="number" step="0.01" wire:model="stone_cost" class="form-control form-control-lg fw-bold @error('stone_cost') is-invalid @enderror" placeholder="e.g. 1500.00">
                            </div>
                            @error('stone_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Stone Setter / Gem Details</label>
                            <input type="text" wire:model="stone_worker_notes" class="form-control" placeholder="e.g. Ruby 6pcs, zircon stone set by Mani">
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeStoneSettingModal">Cancel</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                                <i class="bi bi-check2-circle me-1"></i> Save & Accumulate Cost
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stage 2: Plating Workshop Modal --}}
    @if($showPlatingModal && $platingItem)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-magic me-2"></i> Stage 2: Plating & Workshop Work
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closePlatingModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Item:</span>
                            <span class="fw-bold text-dark">{{ $platingItem->item_code }} - {{ $platingItem->item_name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Purchase Cost:</span>
                            <span>Rs. {{ number_format($platingItem->purchase_cost, 2) }}</span>
                        </div>
                        @if($platingItem->stone_cost > 0)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Stone Cost:</span>
                            <span class="text-info">Rs. {{ number_format($platingItem->stone_cost, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    <form wire:submit.prevent="savePlating">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assign Workshop Craftsman</label>
                            <select wire:model="plating_worker_id" class="form-select">
                                <option value="">-- Select Craftsman (Ramesh / Sanker) --</option>
                                @foreach($workers as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }} (Payable: Rs. {{ number_format($w->outstanding_balance, 0) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Plating & Buffing Charge (Rs.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rs.</span>
                                <input type="number" step="0.01" wire:model="plating_cost" class="form-control form-control-lg fw-bold text-primary @error('plating_cost') is-invalid @enderror" placeholder="Workshop fee">
                            </div>
                            @error('plating_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closePlatingModal">Cancel</button>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-check2-circle me-1"></i> Save & Ready for Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stage 3: Finish & Add Directly to POS Stock Modal --}}
    @if($showAddToStockModal && $stockItem)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-box-seam me-2"></i> Finish & Add Directly to POS Stock
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeAddToStockModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                            <span>{{ $stockItem->item_name }}</span>
                            <span class="badge bg-secondary font-monospace">{{ $stockItem->item_code }}</span>
                        </h6>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">1. Raw Purchase Cost:</span>
                            <span class="fw-semibold">Rs. {{ number_format($stockItem->purchase_cost, 2) }}</span>
                        </div>
                        @if($stockItem->stone_cost > 0)
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">2. Stone Setting Cost:</span>
                            <span class="text-info fw-semibold">+ Rs. {{ number_format($stockItem->stone_cost, 2) }}</span>
                        </div>
                        @endif
                        @if($stockItem->plating_cost > 0)
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">3. Workshop Plating Cost:</span>
                            <span class="text-warning fw-semibold">+ Rs. {{ number_format($stockItem->plating_cost, 2) }}</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <span class="fw-bold text-dark">Total Accumulated Cost:</span>
                            <span class="fw-bold fs-5 text-dark">Rs. {{ number_format($stockItem->total_accumulated_cost, 2) }}</span>
                        </div>
                    </div>

                    <form wire:submit.prevent="confirmAddToStock">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">Decide Final Selling Price (Rs.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white fw-bold">Rs.</span>
                                <input type="number" step="0.01" wire:model="final_selling_price" class="form-control form-control-lg fw-bold text-success @error('final_selling_price') is-invalid @enderror">
                            </div>
                            <small class="text-muted">This product will immediately be available in POS Store Billing with a scannable barcode.</small>
                            @error('final_selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeAddToStockModal">Cancel</button>
                            <button type="submit" class="btn btn-success fw-bold px-4">
                                <i class="bi bi-cart-plus-fill me-1"></i> Add to POS Stock Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Local Supplier Create / Edit Modal --}}
    @if($showSupplierModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-badge me-2"></i> {{ $manageSupplierId ? 'Edit Local Supplier' : 'Register New Local Supplier' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeSupplierModal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="saveSupplier">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Supplier / Artisan Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="supplier_name" class="form-control @error('supplier_name') is-invalid @enderror" placeholder="e.g. Kandiah Goldsmith">
                            @error('supplier_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" wire:model="supplier_phone" class="form-control" placeholder="07X XXXXXXX">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address / Workshop Location</label>
                            <input type="text" wire:model="supplier_address" class="form-control" placeholder="Town / Area">
                        </div>

                        <div class="mb-3 p-3 bg-light rounded-3">
                            <label class="form-label fw-semibold">Opening Payable Balance (Past Debt Owed)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rs.</span>
                                <input type="number" step="0.01" wire:model="supplier_opening_balance" class="form-control fw-bold text-danger" placeholder="0.00">
                            </div>
                            <small class="text-muted">Enter any prior money we already owe this artisan</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <input type="text" wire:model="supplier_notes" class="form-control" placeholder="Specialty items, casting, etc.">
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeSupplierModal">Cancel</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                                <i class="bi bi-save me-1"></i> Save Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Local Supplier Payout Modal --}}
    @if($showSupplierPaymentModal && $payoutSupplier)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-warning text-dark py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-cash-stack me-2"></i> Record Payment to {{ $payoutSupplier->name }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeSupplierPaymentModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted small d-block">Current Outstanding Payable:</span>
                            <span class="fw-bold fs-5 text-danger">Rs. {{ number_format($payoutSupplier->outstanding_balance, 2) }}</span>
                        </div>
                        <span class="badge bg-warning text-dark px-3 py-2">Local Artisan</span>
                    </div>

                    <form wire:submit.prevent="saveSupplierPayment">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Amount (Rs.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rs.</span>
                                <input type="number" step="0.01" wire:model="payoutAmount" class="form-control form-control-lg fw-bold text-success @error('payoutAmount') is-invalid @enderror">
                            </div>
                            @error('payoutAmount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select wire:model="payoutMethod" class="form-select">
                                    <option value="cash">Cash (Counter Cash)</option>
                                    <option value="bank_transfer">Bank Transfer / Online</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="payoutDate" class="form-control @error('payoutDate') is-invalid @enderror">
                                @error('payoutDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <input type="text" wire:model="payoutNotes" class="form-control" placeholder="e.g. Settlement for recent handmade castings">
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeSupplierPaymentModal">Cancel</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                                <i class="bi bi-check2-circle me-1"></i> Save Payment Voucher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Local Supplier Ledger Modal --}}
    @if($showSupplierLedgerModal && $ledgerSupplier)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">Supplier Account Ledger: {{ $ledgerSupplier->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeSupplierLedgerModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block">Past Opening Debt</span>
                                <span class="fs-5 fw-bold text-secondary">Rs. {{ number_format($ledgerSupplier->opening_payable_balance, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block">Total Purchases Bought</span>
                                <span class="fs-5 fw-bold text-primary">Rs. {{ number_format($ledgerSupplier->total_purchased, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block">Total Payments Made</span>
                                <span class="fs-5 fw-bold text-success">Rs. {{ number_format($ledgerSupplier->total_paid, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded-3 text-center border border-danger-subtle">
                                <span class="text-danger small fw-semibold d-block">Outstanding Payable</span>
                                <span class="fs-5 fw-bold text-danger">Rs. {{ number_format($ledgerSupplier->outstanding_balance, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="card border rounded-3 h-100">
                                <div class="card-header bg-light py-2 fw-bold text-dark">
                                    Purchases Bought ({{ $ledgerSupplier->purchases->count() }})
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 380px;">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light small text-uppercase">
                                                <tr>
                                                    <th class="ps-3 py-2">Invoice Code</th>
                                                    <th>Date</th>
                                                    <th class="text-end">Total</th>
                                                    <th class="text-end">Paid</th>
                                                    <th class="text-end pe-3">Due</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($ledgerSupplier->purchases as $pur)
                                                <tr>
                                                    <td class="ps-3 py-2 fw-semibold">{{ $pur->purchase_code }}</td>
                                                    <td>{{ $pur->purchase_date ? $pur->purchase_date->format('d/m/Y') : '' }}</td>
                                                    <td class="text-end fw-bold">Rs. {{ number_format($pur->total_amount, 2) }}</td>
                                                    <td class="text-end text-success">Rs. {{ number_format($pur->paid_amount, 2) }}</td>
                                                    <td class="text-end pe-3 text-danger">Rs. {{ number_format($pur->due_amount, 2) }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted small">No purchases found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card border rounded-3 h-100">
                                <div class="card-header bg-light py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                                    <span>Payments Given ({{ $ledgerSupplier->payments->count() }})</span>
                                    <button wire:click="openSupplierPaymentModal({{ $ledgerSupplier->id }})" class="btn btn-xs btn-warning text-dark fw-bold py-0 px-2" style="font-size: 11px;">
                                        + Pay
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 380px;">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light small text-uppercase">
                                                <tr>
                                                    <th class="ps-3 py-2">Date</th>
                                                    <th>Method</th>
                                                    <th class="text-end pe-3">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($ledgerSupplier->payments as $pay)
                                                <tr>
                                                    <td class="ps-3 py-2">
                                                        <span class="fw-semibold text-dark d-block">{{ $pay->payment_date ? $pay->payment_date->format('d M Y') : '' }}</span>
                                                        @if($pay->notes) <small class="text-muted text-truncate d-block" style="max-width: 140px;">{{ $pay->notes }}</small> @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 10px;">{{ $pay->payment_method }}</span>
                                                    </td>
                                                    <td class="text-end pe-3 fw-bold text-success">
                                                        Rs. {{ number_format($pay->amount, 2) }}
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-4 text-muted small">No payments found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                    <button type="button" class="btn btn-secondary px-4" wire:click="closeSupplierLedgerModal">Close</button>
                    <button type="button" class="btn btn-warning text-dark fw-bold px-4" wire:click="openSupplierPaymentModal({{ $ledgerSupplier->id }})">
                        <i class="bi bi-cash me-1"></i> Give Payment to {{ $ledgerSupplier->name }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Item Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-trash me-2"></i> Delete Handmade Item</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelDeleteItem"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="mb-0">Are you sure you want to delete handmade item <strong>{{ $deletingItemCode }}</strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                    <button type="button" class="btn btn-secondary px-4" wire:click="cancelDeleteItem">Cancel</button>
                    <button type="button" class="btn btn-danger fw-bold px-4" wire:click="deleteItem">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stock Success Print QR Modal --}}
    @if($showStockSuccessModal && $stockSuccessData)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.65);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-check-circle-fill me-2"></i> Added to POS Stock Successfully!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeStockSuccessModal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <span class="badge bg-primary text-white font-monospace px-3 py-1.5 fs-6 shadow-sm">
                            <i class="bi bi-tag-fill me-1"></i>{{ $stockSuccessData['barcode'] }}
                        </span>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">{{ $stockSuccessData['name'] }}</h5>
                    <div class="text-success fw-bold fs-5 mb-3">
                        Rs. {{ $stockSuccessData['price'] }}
                    </div>

                    {{-- QR Code Canvas --}}
                    <div class="d-inline-flex p-3 border rounded-3 bg-white shadow-sm mb-3">
                        <canvas id="stock-success-qr" width="120" height="120" style="width: 120px; height: 120px;"></canvas>
                    </div>

                    <p class="text-muted small mb-0">
                        This handmade product is now live in Store Billing. You can print its jewelry QR tag now or anytime from the catalog.
                    </p>
                </div>
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                    <button type="button" class="btn btn-secondary px-4" wire:click="closeStockSuccessModal">Close</button>
                    <button type="button" 
                        class="btn btn-dark fw-bold px-4 shadow-sm"
                        onclick="printProductTag('{{ $stockSuccessData['barcode'] }}', '{{ $stockSuccessData['price'] }}', '{{ addslashes($stockSuccessData['name']) }}')">
                        <i class="bi bi-printer me-1"></i> Print QR Tag Now
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bwip-js@3.0.4/dist/bwip-js-min.js"></script>
    <script>
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

        function buildLabelCSS(s) {
            const qrMM   = s.showQR ? Math.min(s.qrSize, s.height) : 0;
            const textW  = Math.max(s.textWidth, 4);
            const qrSecW = Math.max(s.width - textW, qrMM);
            const pad    = Math.min(s.padding, textW / 4);
            return `
                * { margin:0; padding:0; box-sizing:border-box; }
                body { font-family:'${s.fontFamily || 'Courier New'}',monospace,sans-serif; margin:0; padding:0; background:white; }
                .label-wrapper {
                    width:${s.width}mm; height:${s.height}mm;
                    display:flex; align-items:stretch; overflow:hidden;
                    page-break-inside:avoid;
                }
                .text-section {
                    width:${textW}mm; flex-shrink:0;
                    display:flex; flex-direction:column; justify-content:center;
                    padding:0 ${pad}mm; overflow:hidden;
                }
                .info-shop {
                    font-size:${s.fontShop}pt; font-weight:bold; color:#000;
                    white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.2;
                    text-transform:uppercase;
                    display:${s.showShop ? 'block' : 'none'};
                }
                .info-price {
                    font-size:${s.fontPrice}pt; font-weight:bold; color:#000; line-height:1.2;
                }
                .info-barcode {
                    font-size:${s.fontBarcode}pt; color:#333; line-height:1.2;
                    display:${s.showBarcodeText ? 'block' : 'none'};
                }
                .qr-section {
                    width:${qrSecW}mm; flex-shrink:0;
                    display:${s.showQR ? 'flex' : 'none'};
                    align-items:center; justify-content:center;
                }
                .qr-section canvas, .qr-section img {
                    width:${qrMM}mm !important; height:${qrMM}mm !important;
                    max-width:100%; max-height:100%; object-fit:contain;
                }
                @media print {
                    body { margin:0; padding:0; }
                    @page { size:${s.width}mm ${s.height}mm; margin:0; }
                }
            `;
        }

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

        function renderStockSuccessQR() {
            const canvas = document.getElementById('stock-success-qr');
            if (!canvas || typeof bwipjs === 'undefined') return;
            @if($stockSuccessData)
            const barcode = '{{ $stockSuccessData['barcode'] ?? '' }}';
            if (!barcode) return;
            try {
                bwipjs.toCanvas(canvas, {
                    bcid: 'qrcode',
                    text: barcode,
                    scale: 3,
                    padding: 0,
                    eclevel: 'M'
                });
            } catch (e) {
                console.error(e);
            }
            @endif
        }

        document.addEventListener('DOMContentLoaded', () => setTimeout(renderStockSuccessQR, 200));
        document.addEventListener('livewire:navigated', () => setTimeout(renderStockSuccessQR, 200));
        Livewire.hook('morph.updated', () => setTimeout(renderStockSuccessQR, 100));
    </script>
    @endpush
</div>
