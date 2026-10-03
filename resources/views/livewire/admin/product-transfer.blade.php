<div>
    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- HEADER --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1a1a2e;">
                <i class="bi bi-box-arrow-right text-info"></i> Product Transfer
            </h4>
            <p class="text-muted mb-0 small">Transfer stock to your other shops & branches</p>
        </div>
        <div class="d-flex gap-2">
            <div class="px-3 py-2 rounded-3 text-center" style="background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; min-width: 100px;">
                <div class="fw-bold fs-5">{{ $totalTransfers }}</div>
                <small>Total Transfers</small>
            </div>
            <div class="px-3 py-2 rounded-3 text-center" style="background: linear-gradient(135deg, #f093fb, #f5576c); color: #fff; min-width: 100px;">
                <div class="fw-bold fs-5">{{ $totalItemsTransferred }}</div>
                <small>Items Moved</small>
            </div>
            <div class="px-3 py-2 rounded-3 text-center" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: #fff; min-width: 100px;">
                <div class="fw-bold fs-5">{{ $totalShops }}</div>
                <small>Shops</small>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- TABS --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <ul class="nav nav-pills mb-4 gap-1" role="tablist">
        <li class="nav-item">
            <button wire:click="$set('activeTab', 'create')" class="nav-link px-4 {{ $activeTab === 'create' ? 'active' : '' }}" style="{{ $activeTab === 'create' ? 'background: linear-gradient(135deg, #667eea, #764ba2); border: none;' : '' }}">
                <i class="bi bi-plus-circle me-1"></i> Create Transfer
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('activeTab', 'history')" class="nav-link px-4 {{ $activeTab === 'history' ? 'active' : '' }}" style="{{ $activeTab === 'history' ? 'background: linear-gradient(135deg, #667eea, #764ba2); border: none;' : '' }}">
                <i class="bi bi-clock-history me-1"></i> Transfer History
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('activeTab', 'shops')" class="nav-link px-4 {{ $activeTab === 'shops' ? 'active' : '' }}" style="{{ $activeTab === 'shops' ? 'background: linear-gradient(135deg, #667eea, #764ba2); border: none;' : '' }}">
                <i class="bi bi-shop me-1"></i> Manage Shops
            </button>
        </li>
    </ul>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- TAB: CREATE TRANSFER --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if($activeTab === 'create')
    <div class="row g-4">
        {{-- LEFT: Transfer Form --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-3 pb-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-truck text-primary me-1"></i> Transfer Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        {{-- Destination Shop --}}
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-muted">Destination Shop</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-shop"></i></span>
                                <select wire:model.live="selectedShopId" class="form-select">
                                    <option value="">-- Select Shop --</option>
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->id }}">
                                            {{ $shop->name }}
                                            @if(!empty($shop->db_database ?? ''))
                                                (Auto-Sync)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <button wire:click="openAddShopModal" class="btn btn-outline-primary" type="button" title="Add New Shop">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        {{-- Transfer Date --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Transfer Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                <input type="date" wire:model="transferDate" class="form-control">
                            </div>
                        </div>
                        {{-- Notes --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Notes (Optional)</label>
                            <input type="text" wire:model="transferNotes" class="form-control" placeholder="Transfer notes...">
                        </div>
                    </div>

                    {{-- Product Search --}}
                    <div class="position-relative mb-4">
                        <label class="form-label small fw-semibold text-muted">Search & Add Products</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="productSearch" 
                                class="form-control" 
                                placeholder="Search by name, code, or barcode..."
                                autocomplete="off"
                            >
                        </div>

                        {{-- Search Results Dropdown --}}
                        @if(!empty($searchResults))
                        <div class="position-absolute w-100 mt-1 shadow-lg rounded-3 bg-white border" style="z-index: 1050; max-height: 350px; overflow-y: auto;">
                            @foreach($searchResults as $result)
                            <div 
                                wire:click="addToCart({{ $result['id'] }})"
                                class="d-flex align-items-center px-3 py-2 border-bottom cursor-pointer"
                                style="cursor: pointer;"
                                onmouseenter="this.style.background='#f0f4ff'" 
                                onmouseleave="this.style.background='white'"
                            >
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small">{{ $result['name'] }}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        Code: {{ $result['code'] ?: 'N/A' }} | Barcode: {{ $result['barcode'] ?: 'N/A' }}
                                    </div>
                                </div>
                                <div class="text-end ms-3">
                                    <span class="badge {{ $result['available_stock'] > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                        Stock: {{ $result['available_stock'] }}
                                    </span>
                                    <div class="small text-muted mt-1">Rs. {{ number_format($result['selling_price'], 2) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Cart Table --}}
                    @if(count($cart) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th class="small fw-semibold text-muted" style="width: 40px;">#</th>
                                    <th class="small fw-semibold text-muted">Product</th>
                                    <th class="small fw-semibold text-muted text-center" style="width: 100px;">Stock</th>
                                    <th class="small fw-semibold text-muted text-center" style="width: 150px;">Transfer Qty</th>
                                    <th class="small fw-semibold text-muted text-end" style="width: 120px;">Unit Price</th>
                                    <th class="small fw-semibold text-muted text-end" style="width: 120px;">Subtotal</th>
                                    <th class="small fw-semibold text-muted text-center" style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart as $index => $item)
                                <tr>
                                    <td class="text-muted small">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold small">{{ $item['name'] }}</div>
                                        <div class="text-muted" style="font-size: 11px;">{{ $item['code'] }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $item['available_stock'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="input-group input-group-sm mx-auto" style="max-width: 130px;">
                                            <button wire:click="decrementQty({{ $index }})" class="btn btn-outline-secondary" type="button">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input 
                                                type="number" 
                                                wire:change="updateQuantity({{ $index }}, $event.target.value)" 
                                                value="{{ $item['quantity'] }}" 
                                                class="form-control text-center" 
                                                min="1" 
                                                max="{{ $item['available_stock'] }}"
                                            >
                                            <button wire:click="incrementQty({{ $index }})" class="btn btn-outline-secondary" type="button">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-end small">Rs. {{ number_format($item['selling_price'], 2) }}</td>
                                    <td class="text-end small fw-semibold">Rs. {{ number_format($item['quantity'] * $item['selling_price'], 2) }}</td>
                                    <td class="text-center">
                                        <button wire:click="removeFromCart({{ $index }})" class="btn btn-sm btn-outline-danger border-0" title="Remove">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="mt-2 mb-0">Search and add products to begin transfer</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT: Transfer Summary --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 80px;">
                <div class="card-header border-0 pt-3 pb-2" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px 16px 0 0;">
                    <h6 class="fw-bold mb-0 text-white"><i class="bi bi-receipt me-1"></i> Transfer Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Items in Cart</span>
                        <span class="fw-bold">{{ count($cart) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Total Quantity</span>
                        <span class="fw-bold">{{ $this->cartTotalQuantity }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Total Cost Price</span>
                        <span class="fw-semibold text-secondary">Rs. {{ number_format($this->cartTotalCost, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 mb-3">
                        <span class="text-muted small">Total Retail Value</span>
                        <span class="fw-bold text-success fs-5">Rs. {{ number_format($this->cartTotalValue, 2) }}</span>
                    </div>

                    @if(!empty($selectedShopId))
                        @php
                            $selShop = collect($shops)->firstWhere('id', $selectedShopId);
                        @endphp
                        @if($selShop)
                        <div class="alert alert-info py-2 mb-3 small">
                            <i class="bi bi-shop me-1"></i> <strong>To:</strong> {{ $selShop->name }}
                            @if(!empty($selShop->db_database))
                                <br><i class="bi bi-database me-1"></i> Auto-sync: <code>{{ $selShop->db_database }}</code>
                            @endif
                        </div>
                        @endif
                    @endif

                    <button 
                        wire:click="executeTransfer" 
                        wire:loading.attr="disabled"
                        class="btn btn-lg w-100 fw-bold text-white"
                        style="background: linear-gradient(135deg, #667eea, #764ba2); border: none; border-radius: 12px;"
                        {{ count($cart) === 0 || empty($selectedShopId) ? 'disabled' : '' }}
                    >
                        <span wire:loading.remove wire:target="executeTransfer">
                            <i class="bi bi-check2-circle me-1"></i> Confirm & Transfer
                        </span>
                        <span wire:loading wire:target="executeTransfer">
                            <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                        </span>
                    </button>

                    @if(count($cart) > 0)
                    <button wire:click="clearCart" class="btn btn-outline-danger btn-sm w-100 mt-2 rounded-pill">
                        <i class="bi bi-x-circle me-1"></i> Clear Cart
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- TAB: TRANSFER HISTORY --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if($activeTab === 'history')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-1"></i> Transfer History</h6>
            <div class="input-group" style="max-width: 280px;">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="historySearch" class="form-control form-control-sm" placeholder="Search transfers...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th class="small fw-semibold text-muted">Transfer #</th>
                            <th class="small fw-semibold text-muted">Date</th>
                            <th class="small fw-semibold text-muted">Destination</th>
                            <th class="small fw-semibold text-muted text-center">Items</th>
                            <th class="small fw-semibold text-muted text-center">Qty</th>
                            <th class="small fw-semibold text-muted text-end">Value</th>
                            <th class="small fw-semibold text-muted text-center">Synced</th>
                            <th class="small fw-semibold text-muted text-center">Status</th>
                            <th class="small fw-semibold text-muted text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                        <tr>
                            <td>
                                <span class="fw-semibold small text-primary">{{ $transfer->transfer_number }}</span>
                            </td>
                            <td class="small">{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d M Y') }}</td>
                            <td class="small fw-semibold">{{ $transfer->destination_shop_name }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $transfer->total_items }}</span></td>
                            <td class="text-center"><span class="badge bg-info text-dark">{{ $transfer->total_quantity }}</span></td>
                            <td class="text-end small fw-semibold">Rs. {{ number_format($transfer->total_value, 2) }}</td>
                            <td class="text-center">
                                @if($transfer->is_synced)
                                    <span class="badge bg-success rounded-pill"><i class="bi bi-check-circle"></i> Yes</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($transfer->status === 'completed')
                                    <span class="badge bg-success rounded-pill">Completed</span>
                                @elseif($transfer->status === 'cancelled')
                                    <span class="badge bg-danger rounded-pill">Cancelled</span>
                                @else
                                    <span class="badge bg-warning rounded-pill">{{ ucfirst($transfer->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button wire:click="viewTransfer({{ $transfer->id }})" class="btn btn-outline-primary border-0" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if($transfer->status !== 'cancelled')
                                    <button 
                                        wire:click="cancelTransfer({{ $transfer->id }})" 
                                        wire:confirm="Are you sure you want to cancel this transfer? Stock will be restored to source."
                                        class="btn btn-outline-danger border-0" 
                                        title="Cancel & Restore Stock"
                                    >
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 36px; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">No transfers found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($transfers, 'links'))
            <div class="p-3">
                {{ $transfers->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- TAB: MANAGE SHOPS --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if($activeTab === 'shops')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-shop text-primary me-1"></i> Destination Shops</h6>
            <button wire:click="openAddShopModal" class="btn btn-sm btn-primary rounded-pill">
                <i class="bi bi-plus-lg me-1"></i> Add Shop
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th class="small fw-semibold text-muted">Shop Name</th>
                            <th class="small fw-semibold text-muted">Code</th>
                            <th class="small fw-semibold text-muted">Phone</th>
                            <th class="small fw-semibold text-muted">Address</th>
                            <th class="small fw-semibold text-muted">DB Sync</th>
                            <th class="small fw-semibold text-muted text-center">Status</th>
                            <th class="small fw-semibold text-muted text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shops as $shop)
                        <tr>
                            <td class="fw-semibold small">{{ $shop->name }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $shop->code }}</span></td>
                            <td class="small">{{ $shop->phone ?? '-' }}</td>
                            <td class="small text-muted">{{ $shop->address ?? '-' }}</td>
                            <td>
                                @if(!empty($shop->db_database ?? ''))
                                    <span class="badge bg-info rounded-pill"><i class="bi bi-database me-1"></i>{{ $shop->db_database ?? '' }}</span>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($shop->is_active)
                                    <span class="badge bg-success rounded-pill">Active</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button 
                                    wire:click="toggleShopActive({{ $shop->id }})" 
                                    class="btn btn-sm {{ ($shop->is_active) ? 'btn-outline-warning' : 'btn-outline-success' }} border-0"
                                    title="{{ ($shop->is_active) ? 'Deactivate' : 'Activate' }}"
                                >
                                    <i class="bi {{ ($shop->is_active) ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-shop" style="font-size: 36px; opacity: 0.3;"></i>
                                <p class="mt-2 mb-0">No destination shops configured yet. Add one to start transferring!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODAL: ADD NEW SHOP --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if($showAddShopModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title text-white fw-bold"><i class="bi bi-shop me-1"></i> Add Destination Shop</h5>
                    <button wire:click="closeAddShopModal" class="btn-close btn-close-white"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Shop Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="newShopName" class="form-control" placeholder="e.g. Sewanagala Branch">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Code</label>
                            <input type="text" wire:model="newShopCode" class="form-control" placeholder="e.g. SWN">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Phone</label>
                            <input type="text" wire:model="newShopPhone" class="form-control" placeholder="Phone number">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Address</label>
                        <input type="text" wire:model="newShopAddress" class="form-control" placeholder="Shop address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Database Name (for Auto-Sync)</label>
                        <input type="text" wire:model="newShopDbDatabase" class="form-control" placeholder="e.g. sewanagala (leave blank if no sync)">
                        <div class="form-text small">If specified, stock will be automatically synced to this database when transferring.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Notes</label>
                        <textarea wire:model="newShopNotes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button wire:click="closeAddShopModal" class="btn btn-light rounded-pill">Cancel</button>
                    <button wire:click="saveNewShop" class="btn text-white rounded-pill px-4" style="background: linear-gradient(135deg, #667eea, #764ba2); border: none;">
                        <i class="bi bi-check-lg me-1"></i> Save Shop
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODAL: VIEW TRANSFER DETAILS --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    @if($showViewModal && $viewTransferId)
    @php
        $viewTransfer = \Illuminate\Support\Facades\DB::table('product_transfers')->find($viewTransferId);
    @endphp
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bi bi-receipt me-1"></i> Transfer {{ $viewTransfer->transfer_number ?? '' }}
                    </h5>
                    <button wire:click="closeViewModal" class="btn-close btn-close-white"></button>
                </div>
                <div class="modal-body">
                    @if($viewTransfer)
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Date</small>
                            <span class="fw-semibold">{{ \Carbon\Carbon::parse($viewTransfer->transfer_date)->format('d M Y') }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Destination</small>
                            <span class="fw-semibold">{{ $viewTransfer->destination_shop_name }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge {{ $viewTransfer->status === 'completed' ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                {{ ucfirst($viewTransfer->status) }}
                            </span>
                        </div>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th class="small fw-semibold text-muted">#</th>
                                    <th class="small fw-semibold text-muted">Product</th>
                                    <th class="small fw-semibold text-muted">Code</th>
                                    <th class="small fw-semibold text-muted text-center">Qty</th>
                                    <th class="small fw-semibold text-muted text-end">Unit Price</th>
                                    <th class="small fw-semibold text-muted text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($viewTransferItems as $i => $tItem)
                                <tr>
                                    <td class="small text-muted">{{ $i + 1 }}</td>
                                    <td class="small fw-semibold">{{ $tItem->product_name }}</td>
                                    <td class="small">{{ $tItem->product_code }}</td>
                                    <td class="text-center"><span class="badge bg-info text-dark">{{ $tItem->quantity }}</span></td>
                                    <td class="text-end small">Rs. {{ number_format($tItem->unit_price, 2) }}</td>
                                    <td class="text-end small fw-semibold">Rs. {{ number_format($tItem->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if($viewTransfer)
                            <tfoot style="background: #f0f4ff;">
                                <tr>
                                    <td colspan="3" class="fw-bold text-end">Total</td>
                                    <td class="text-center fw-bold">{{ $viewTransfer->total_quantity }}</td>
                                    <td></td>
                                    <td class="text-end fw-bold text-success">Rs. {{ number_format($viewTransfer->total_value, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                    @if($viewTransfer && $viewTransfer->notes)
                    <div class="alert alert-light mt-3 py-2 small">
                        <i class="bi bi-sticky me-1"></i> <strong>Notes:</strong> {{ $viewTransfer->notes }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button wire:click="closeViewModal" class="btn btn-light rounded-pill">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
