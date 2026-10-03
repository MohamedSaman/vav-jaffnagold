<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-cart-check-fill text-success me-2"></i> Purchase Order Management
            </h3>
            <p class="text-muted mb-0">Create and manage purchase orders from suppliers</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('admin.grn') }}" class="btn btn-outline-primary">
                <i class="bi bi-clipboard-check me-1"></i> Goods Received (GRN)
            </a>
            <a href="{{ route('admin.fast-product-create') }}" class="btn btn-outline-success">
                <i class="bi bi-lightning-charge me-1"></i> Fast Product Entry
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPurchaseOrderModal">
                <i class="bi bi-plus-circle me-1"></i> New Purchase Order
            </button>
        </div>
    </div>

    <div class="container-fluid p-4">
        {{-- Summary Cards --}}
        <div class="row mb-2">
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card summary-card pending h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-warning bg-opacity-10 me-3">
                                <i class="bi bi-hourglass-split text-warning fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Pending Orders</p>
                                <h4 class="fw-bold mb-0">{{ $pendingCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card summary-card completed h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-success bg-opacity-10 me-3">
                                <i class="bi bi-patch-check-fill text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Completed Orders</p>
                                <h4 class="fw-bold mb-0">{{ $completedCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-list-check text-primary me-2"></i> Purchase Orders
                    </h5>
                    <p class="text-muted small mb-0">View and manage all purchase orders</p>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 60%; margin: auto">
                <!-- 🔍 Search Bar -->
                    <div class="search-bar flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" wire:model.live="search"
                                placeholder="Search by order code or supplier name...">
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-sm text-muted fw-medium">Show</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                    </select>
                    <span class="text-sm text-muted">entries</span>
                </div>
            </div>
            <div class="card-body p-0 overflow-auto">
                <div class="table-responsive" style="overflow:visible !important;">
                    <table class="table table-hover mb-0" style="overflow:visible;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Order Code</th>
                                <th>Order Date</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>GRN Status</th>
                                <th class="text-center">Order Quantity</th>
                                <th class="text-center">Received Quantity</th>
                                <th>Total Amount</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            @php
                            // Calculate totals for this order
                            $totalQuantity = $order->items->sum('quantity');
                            $totalReceivedQty = $order->items->sum('received_quantity');
                            $totalAmount = $order->items->sum(function($item) {
                                $qty = floatval($item->quantity);
                                $unitPrice = floatval($item->unit_price > 0 ? $item->unit_price : ($item->system_cost ?? 0));
                                $discount = floatval($item->discount ?? 0);
                                $discountAmount = ($unitPrice * $discount) / 100;
                                $netUnitPrice = $unitPrice - $discountAmount;
                                return $qty * $netUnitPrice;
                            });
                            if ($totalAmount <= 0 && floatval($order->total_amount) > 0) {
                                $totalAmount = floatval($order->total_amount);
                            }
                            $ReceivedTotalAmount = $order->items->sum(function($item) {
                                $qty = floatval($item->received_quantity);
                                $unitPrice = floatval($item->unit_price > 0 ? $item->unit_price : ($item->system_cost ?? 0));
                                $discount = floatval($item->discount ?? 0);
                                $discountAmount = ($unitPrice * $discount) / 100;
                                $netUnitPrice = $unitPrice - $discountAmount;
                                return $qty * $netUnitPrice;
                            });
                            if ($ReceivedTotalAmount <= 0 && floatval($order->total_amount) > 0) {
                                $ReceivedTotalAmount = floatval($order->total_amount);
                            }

                            // Calculate GRN status
                            $totalItems = $order->items->count();
                            $receivedItems = $order->items->where('status', 'received')->count();
                            $notReceivedItems = $order->items->where('status', 'notreceived')->count();
                            $pendingItems = $order->items->whereNotIn('status', ['received', 'notreceived'])->count();

                            $grnStatus = 'Pending';
                            $grnBadge = 'bg-warning';
                            if ($receivedItems > 0 && $notReceivedItems == 0 && $pendingItems == 0) {
                                $grnStatus = 'Completed';
                                $grnBadge = 'bg-success';
                            } elseif ($notReceivedItems > 0) {
                                $grnStatus = 'Partial';
                                $grnBadge = 'bg-danger';
                            } elseif ($receivedItems > 0) {
                                $grnStatus = 'In Progress';
                                $grnBadge = 'bg-info';
                            }
                            @endphp
                            <tr wire:key="order-{{ $order->id }}">
                                <td class="ps-4">
                                    <button type="button" wire:click="viewOrder({{ $order->id }})" class="btn btn-link p-0 fw-bold text-primary text-decoration-none d-inline-flex align-items-center gap-1" title="Click to view details">
                                        <i class="bi bi-box-arrow-up-right small"></i> {{ $order->order_code }}
                                    </button>
                                </td>
                                <td>
                                    <span class="text-secondary small fw-medium">
                                        <i class="bi bi-calendar3 me-1 text-primary"></i>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold text-dark d-block">{{ $order->supplier->name ?? 'N/A' }}</span>
                                        @if(!empty($order->supplier?->phone))
                                            <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->supplier->phone }}</small>
                                        @elseif(!empty($order->supplier?->businessname))
                                            <small class="text-muted">{{ $order->supplier->businessname }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($order->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @elseif($order->status == 'complete' || ($order->status == 'received' && $grnStatus == 'Completed'))
                                    <span class="badge bg-success">Completed</span>
                                    @elseif($order->status == 'received')
                                    <span class="badge bg-info">Partial Receipt</span>
                                    @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $grnBadge }}">
                                        {{ $grnStatus }}
                                        @if($grnStatus != 'Pending' && $totalItems > 0)
                                        ({{ $receivedItems }}/{{ $totalItems }})
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center fw-medium">{{ $totalQuantity }}</td>
                                <td class="text-center fw-medium">{{ $totalReceivedQty }}</td>
                                @if($grnStatus == 'Pending')
                                <td><span class="fw-semibold text-dark">Rs. {{ number_format($totalAmount, 2) }}</span></td>
                                @else
                                <td><span class="fw-semibold text-dark">Rs. {{ number_format($ReceivedTotalAmount, 2) }}</span></td>
                                @endif
                                <td class="text-end pe-4 text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" wire:click="viewOrder({{ $order->id }})" title="View Details">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="bi bi-gear-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" style="z-index: 1060;">
                                            <!-- View Order -->
                                            <li>
                                                <button type="button" class="dropdown-item" wire:click="viewOrder({{ $order->id }})">
                                                    <i class="bi bi-eye text-primary me-2"></i> View Details
                                                </button>
                                            </li>

                                            <!-- Edit Order & Values (Always available for Pending, Received, Complete) -->
                                            <li>
                                                <a href="{{ route('admin.fast-product-create', ['order_id' => $order->id]) }}" class="dropdown-item fw-semibold text-dark">
                                                    <i class="bi bi-pencil-square text-warning me-2"></i> Edit Order in Fast Entry
                                                </a>
                                            </li>

                                            @if($order->status == 'pending')
                                            <!-- Convert to GRN -->
                                            <li>
                                                <button class="dropdown-item" wire:click="convertToGRN({{ $order->id }})">
                                                    <i class="bi bi-arrow-repeat text-info me-2"></i> Process GRN
                                                </button>
                                            </li>

                                            <!-- Cancel Order -->
                                            <li>
                                                <button class="dropdown-item" wire:click="confirmDelete({{ $order->id }})">
                                                    <i class="bi bi-x-circle text-danger me-2"></i> Cancel Order
                                                </button>
                                            </li>
                                            @endif

                                            @if($order->status == 'received' && $grnStatus !== 'Completed')
                                            <!-- Re-Process GRN for Partial Orders -->
                                            <li>
                                                <button class="dropdown-item" wire:click="reProcessGRN({{ $order->id }})">
                                                    <i class="bi bi-arrow-clockwise text-success me-2"></i> Re-Process GRN
                                                </button>
                                            </li>

                                            <!-- Force Complete Order - Mark pending items as not received -->
                                            <li>
                                                <button class="dropdown-item" wire:click="confirmForceComplete({{ $order->id }})">
                                                    <i class="bi bi-check-circle text-primary me-2"></i> Force Complete Order
                                                </button>
                                            </li>
                                            @endif

                                            <!-- Download PDF -->
                                            <li>
                                                <button class="dropdown-item" wire:click="downloadPDF({{ $order->id }})">
                                                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i> Download PDF
                                                </button>
                                            </li>

                                            <!-- Delete Order (for all statuses or only completed) -->
                                            <li>
                                                <button class="dropdown-item" wire:click="confirmPermanentDelete({{ $order->id }})">
                                                    <i class="bi bi-trash text-danger me-2"></i> Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $orders->links('livewire.custom-pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Order Modal --}}
    <div wire:ignore.self class="modal fade" id="addPurchaseOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle text-white me-2"></i> Create New Purchase Order
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Select Supplier <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="supplier_id">
                                <option value="">Choose supplier...</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 position-relative"
                            x-data="{ highlightIndex: -1 }"
                            x-on:product-added-to-po-order.window="highlightIndex = -1; $nextTick(() => { let q = document.querySelector('#poOrderQty-0'); if(q){ q.focus(); q.select(); } })"
                            x-on:qty-updated-po.window="$nextTick(() => { document.querySelector('#poSearchInput')?.focus(); })">
                            <label class="form-label fw-semibold">Search & Add Product</label>
                            <input type="text"
                                id="poSearchInput"
                                class="form-control"
                                wire:model.live.debounce.300ms="searchProduct"
                                placeholder="Type product name or code (min 2 characters)..."
                                autocomplete="off"
                                x-on:keydown.arrow-down.prevent="if (highlightIndex < {{ count($products ?? []) - 1 }}) { highlightIndex++; $nextTick(() => { document.querySelector(`[data-po-search-index='${highlightIndex}']`)?.scrollIntoView({block: 'nearest'}); }); }"
                                x-on:keydown.arrow-up.prevent="if (highlightIndex > 0) { highlightIndex--; $nextTick(() => { document.querySelector(`[data-po-search-index='${highlightIndex}']`)?.scrollIntoView({block: 'nearest'}); }); }"
                                x-on:keydown.enter.prevent="if (highlightIndex >= 0) { document.querySelector(`[data-po-search-index='${highlightIndex}']`)?.click(); }"
                                x-on:keydown.escape.prevent="highlightIndex = -1">
                            @if(!empty($products) && count($products) > 0)
                            <ul class="list-group mt-1 position-absolute w-100 z-3 shadow-lg" style="max-height: 300px; overflow-y: auto;">
                                @foreach($products as $sIndex => $product)
                                @if(is_array($product) && ($product['type'] ?? '') === 'variant')
                                <li class="list-group-item list-group-item-action p-2"
                                    data-po-search-result
                                    data-po-search-index="{{ $sIndex }}"
                                    wire:key="search-product-{{ $product['product_id'] }}-{{ str_replace(' ', '-', $product['variant_value']) }}"
                                    wire:click="selectProductVariant({{ $product['product_id'] }}, '{{ addslashes($product['variant_value']) }}')"
                                    :class="highlightIndex === {{ $sIndex }} ? 'bg-light border-primary border-2' : ''"
                                    style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product['image'] ? asset($product['image']) : asset('images/product.jpg') }}"
                                            alt="{{ $product['name'] }}"
                                            class="me-2"
                                            style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark">{{ $product['name'] }} - {{ $product['variant_name'] ?? 'Variant' }}: <strong>{{ $product['variant_value'] }}</strong></div>
                                            <small class="text-muted">
                                                Code: <span class="badge bg-secondary">{{ $product['code'] }}</span>
                                                | Available: <span class="badge {{ ($product['available_stock'] ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $product['available_stock'] ?? 0 }} units
                                                </span>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">Click to Add</span>
                                        </div>
                                    </div>
                                </li>
                                @else
                                @php $p = (object) $product; @endphp
                                <li class="list-group-item list-group-item-action p-2"
                                    data-po-search-result
                                    data-po-search-index="{{ $sIndex }}"
                                    wire:key="search-product-{{ $p->product_id }}"
                                    wire:click="selectProduct({{ $p->product_id }})"
                                    :class="highlightIndex === {{ $sIndex }} ? 'bg-light border-primary border-2' : ''"
                                    style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $p->image ? asset($p->image) : asset('images/product.jpg') }}"
                                            alt="{{ $p->name }}"
                                            class="me-2"
                                            style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark">{{ $p->name }}</div>
                                            <small class="text-muted">
                                                Code: <span class="badge bg-secondary">{{ $p->code }}</span>
                                                | Available: <span class="badge {{ ($p->available_stock ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $p->available_stock ?? 0 }} units
                                                </span>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">Click to Add</span>
                                        </div>
                                    </div>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                            @endif
                            @if(strlen($searchProduct) >= 1 && strlen($searchProduct) < 2)
                                <div class="text-muted small mt-1">
                                <i class="bi bi-info-circle"></i> Type at least 2 characters to search
                        </div>
                        @endif
                    </div>
                </div>

                <h5 class="mt-4 mb-3">
                    <i class="bi bi-cart3 me-2"></i>Order Items
                    <span class="badge bg-primary">{{ count($orderItems) }}</span>
                </h5>
                <div class="table-responsive ">
                    <table class="table table-bordered table-hover overflow-auto">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th style="width: 120px;">Code</th>
                                <th>Product Name</th>
                                <th style="width: 120px;">Order Quantity</th>
                                
                                <th style="width: 150px;">Supplier Price</th>
                                <th style="width: 150px;">Total Price</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orderItems as $index => $item)
                            <tr wire:key="order-item-{{ $item['product_id'] }}-{{ $index }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $item['code'] }}</span>
                                </td>
                                <td>
                                    <strong>{{ $item['name'] }}</strong>
                                </td>
                                <td>
                                    <input type="number"
                                        id="poOrderQty-{{ $index }}"
                                        class="form-control form-control-sm"
                                        wire:model.live.debounce.300ms="orderItems.{{ $index }}.quantity"
                                        wire:change="updateOrderItemQuantity({{ $index }}, $event.target.value)"
                                        x-on:keydown.enter="$wire.updateOrderItemQuantity({{ $index }}, $event.target.value)"
                                        min="1"
                                        style="width: 100%;">
                                </td>
                                <td>
                                    <input type="number"
                                        class="form-control form-control-sm"
                                        wire:model.live.debounce.300ms="orderItems.{{ $index }}.supplier_price"
                                        wire:change="updateOrderItemPrice({{ $index }}, $event.target.value)"
                                        min="0"
                                        step="0.01"
                                        style="width: 100%;">
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">Rs. {{ number_format($item['total_price'], 2) }}</strong>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger"
                                        wire:click="removeItem({{ $index }})"
                                        title="Remove item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                                    <p class="mb-0">No items added yet. Search and select products above to add them.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($orderItems) > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                                <td class="text-end">
                                    <strong class="text-primary fs-5">Rs. {{ number_format($grandTotal, 2) }}</strong>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" wire:click="saveOrder">
                    <i class="bi bi-save me-1"></i> Save Purchase Order
                </button>
            </div>
        </div>
    </div>
</div>

{{-- GRN Modal --}}
<div wire:ignore.self class="modal fade" id="grnModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clipboard-check text-white me-2"></i>
                    @if($selectedPO && $selectedPO->status == 'received')
                    Re-Process GRN for {{ $selectedPO->order_code }} (Pending Items Only)
                    @else
                    Create GRN for {{ $selectedPO?->order_code }}
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($selectedPO)
                <p><strong>Supplier:</strong> {{ $selectedPO->supplier->name }}</p>

                @if($selectedPO->status == 'received')
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Re-Processing:</strong> Only pending items from this order are shown below.
                </div>
                @endif

                <h5>
                    @if($selectedPO->status == 'received')
                    Pending Items to Receive
                    @else
                    Received Items
                    @endif
                </h5>
                <div class="table-responsive border rounded shadow-sm custom-grn-container mb-3" style="max-height: 500px; overflow-y: auto; overflow-x: auto; width: 100%;">
                    <table class="table table-bordered table-hover mb-0" style="min-width: 1400px; width: 100%;">
                        <thead class="table-light">
                            <tr class="align-middle text-uppercase small fw-bold">
                                <th style="min-width: 90px;">Code</th>
                                <th style="min-width: 250px;">Product</th>
                                <th class="text-center" style="width: 80px;">Ord Qty</th>
                                <th class="text-center" style="width: 100px;">Recv Qty</th>
                                <th class="text-end" style="width: 130px;">Supplier Price</th>
                                <th class="text-center" style="width: 100px;">Discount</th>
                                <th class="text-end" style="width: 130px;">Wholesale</th>
                                <th class="text-end" style="width: 130px;">Distributor</th>
                                <th class="text-end" style="width: 130px;">Retail Price</th>
                                <th class="text-end" style="width: 110px;">Cost</th>
                                <th class="text-end" style="width: 120px;">Total</th>
                                <th class="text-center" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grnItems as $index => $item)
                            @php
                            $statusClass = '';
                            $statusText = 'Pending';
                            $statusBadge = 'bg-warning';
                            if (strtolower($item['status'] ?? '') === 'received') {
                            $statusClass = 'table-success';
                            $statusText = 'Received';
                            $statusBadge = 'bg-success';
                            } elseif (strtolower($item['status'] ?? '') === 'notreceived') {
                            $statusClass = 'table-danger';
                            $statusText = 'Not Received';
                            $statusBadge = 'bg-danger';
                            }
                            @endphp
                            <tr wire:key="item-{{ $index }}" class="{{ $statusClass }}">
                                <td class="align-middle">
                                    @if($item['is_new'] ?? false)
                                    <input type="text"
                                        class="form-control form-control-sm"
                                        wire:model.live="grnItems.{{ $index }}.code"
                                        placeholder="Product Code">
                                    @else
                                    <span class="fw-medium text-dark">{{ $item['code'] ?? '' }}</span>
                                    @endif
                                </td>
                                <td class="position-relative align-middle">
                                    @if($item['is_new'] ?? false)
                                    <input type="text"
                                        class="form-control form-control-sm"
                                        wire:model.live="grnItems.{{ $index }}.name"
                                        placeholder="New Product Name"
                                        title="{{ $item['name'] ?? '' }}"
                                        style="min-width:260px;">

                                    @if(isset($searchResults[$index]) && count($searchResults[$index]) > 0)
                                    <ul class="list-group position-absolute z-10 shadow-lg mt-1" style="min-width: 350px; max-width: 450px; left: 0;">
                                        @foreach($searchResults[$index] as $product)
                                        <li class="list-group-item list-group-item-action p-2"
                                            wire:key="grn-search-{{ $index }}-{{ $product->id }}"
                                            wire:click="selectGRNProduct({{ $index }}, {{ $product->id }})"
                                            style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/product.jpg') }}"
                                                    alt="{{ $product->name }}"
                                                    class="me-2"
                                                    style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                                    <small class="text-muted">
                                                        Code: <span class="badge bg-secondary">{{ $product->code }}</span>
                                                        | Stock: <span class="badge {{ ($product->stock->total_stock ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">
                                                            {{ $product->stock->total_stock ?? 0 }} units
                                                        </span>
                                                    </small>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif

                                    @else
                                    <div class="text-wrap fw-bold text-dark" style="min-width:200px; line-height: 1.2;">{{ $item['name'] ?? '' }}</div>
                                    @endif
                                </td>
                                <td class="align-middle text-center">{{ $item['ordered_qty'] ?? 0 }}</td>
                                <td class="align-middle">
                                    <input type="number"
                                        class="form-control form-control-sm text-center fw-bold"
                                        wire:model.live="grnItems.{{ $index }}.received_quantity"
                                        min="0"
                                        wire:change="calculateGRNTotal({{ $index }})">
                                </td>
                                <td class="align-middle">
                                    <input type="number"
                                        class="form-control form-control-sm text-end supplier-price-input fw-semibold"
                                        wire:model.live.debounce.300ms="grnItems.{{ $index }}.unit_price"
                                        data-index="{{ $index }}"
                                        step="0.01"
                                        min="0"
                                        placeholder="0">
                                </td>
                                <td class="align-middle">
                                    <div class="discount-container">
                                        <div class="input-group input-group-sm">
                                            <input type="number"
                                                class="form-control discount-input text-center"
                                                wire:model.live.debounce.300ms="grnItems.{{ $index }}.discount"
                                                data-index="{{ $index }}"
                                                placeholder="0"
                                                min="0"
                                                step="0.1"
                                                autocomplete="off">
                                            <span class="input-group-text bg-light border-start-0 text-muted">%</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <input type="number"
                                        class="form-control form-control-sm text-end fw-semibold"
                                        wire:model.live="grnItems.{{ $index }}.wholesale_price"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00">
                                </td>
                                <td class="align-middle">
                                    <input type="number"
                                        class="form-control form-control-sm text-end fw-semibold"
                                        wire:model.live="grnItems.{{ $index }}.distributor_price"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00">
                                </td>
                                <td class="align-middle">
                                    <input type="number"
                                        class="form-control form-control-sm text-end fw-semibold"
                                        wire:model.live="grnItems.{{ $index }}.retail_price"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00">
                                </td>
                                <td class="text-end fw-bold text-success align-middle" style="white-space: nowrap;">
                                    {{ number_format($this->calculateCost($index), 2) }}
                                </td>
                                <td class="text-end fw-bold text-primary align-middle" style="white-space: nowrap;">
                                    {{ number_format($this->calculateGRNTotal($index), 2) }}
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        @if(strtolower($item['status'] ?? '') !== 'received')
                                        <button class="btn btn-sm btn-outline-success"
                                            wire:click="correctGRNItem({{ $index }})"
                                            title="Mark as Received">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="deleteGRNItem({{ $index }})"
                                            title="Remove Item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @if($selectedPO)
            <div class="grn-summary-footer border-top bg-light px-3 py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button class="btn btn-success btn-sm" wire:click="addNewRow">
                            <i class="bi bi-plus-circle"></i> Add New Item
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bold text-dark">Grand Total:</span>
                        <span class="fw-bold fs-5 text-primary">
                            {{ number_format($this->grnGrandTotal, 2) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" wire:click="saveGRN">Save GRN</button>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- View Order Modal --}}
<div wire:ignore.self class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 96vw; width: 96vw; margin: 1rem auto;">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-journal-text fs-4 text-white"></i>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">
                            Purchase Order Details: <span class="text-warning">{{ $selectedOrder?->order_code }}</span>
                        </h5>
                        <small class="text-white-50">Detailed breakdown of products, costs, prices and totals</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                @if($selectedOrder)
                <!-- Supplier and Order Information Card -->
                <div class="card bg-light border-0 mb-3 shadow-sm">
                    <div class="card-body p-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3 border-end">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Supplier</span>
                                <span class="fs-6 fw-bold text-dark d-block">{{ $selectedOrder->supplier->name ?? 'N/A' }}</span>
                                @if(!empty($selectedOrder->supplier?->phone))
                                    <small class="text-muted d-block"><i class="bi bi-telephone me-1"></i>{{ $selectedOrder->supplier->phone }}</small>
                                @endif
                                @if(!empty($selectedOrder->supplier?->businessname))
                                    <small class="text-muted d-block"><i class="bi bi-building me-1"></i>{{ $selectedOrder->supplier->businessname }}</small>
                                @endif
                            </div>
                            <div class="col-md-3 border-end">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Order Dates</span>
                                <div class="mt-1">
                                    <span class="d-block small text-dark"><i class="bi bi-calendar-event me-1 text-primary"></i><strong>Ordered:</strong> {{ $selectedOrder->order_date ? \Carbon\Carbon::parse($selectedOrder->order_date)->format('M d, Y') : '-' }}</span>
                                    <span class="d-block small text-dark mt-1"><i class="bi bi-calendar-check me-1 text-success"></i><strong>Received:</strong> {{ $selectedOrder->received_date ? \Carbon\Carbon::parse($selectedOrder->received_date)->format('M d, Y') : 'Not Received Yet' }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 border-end">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Status & Rates</span>
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @if($selectedOrder->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($selectedOrder->status == 'complete')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($selectedOrder->status == 'received')
                                        <span class="badge bg-info">Received</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($selectedOrder->status) }}</span>
                                    @endif

                                    @php
                                        $costMultiplier = floatval($selectedOrder->cost_multiplier ?? ($selectedOrder->supplier?->cost_multiplier ?? 1.0));
                                        $sellMultiplier = floatval($selectedOrder->sell_multiplier ?? ($selectedOrder->supplier?->sell_multiplier ?? 0));
                                    @endphp
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Cost: {{ $costMultiplier }}x</span>
                                    @if($sellMultiplier > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Sell: {{ $sellMultiplier }}x</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <span class="text-muted small text-uppercase fw-semibold d-block">Summary</span>
                                <h5 class="fw-bold text-dark mb-0 mt-1">
                                    {{ $selectedOrder->items->count() }} <span class="fs-6 fw-normal text-muted">items</span> / 
                                    {{ $selectedOrder->items->sum('quantity') }} <span class="fs-6 fw-normal text-muted">ordered</span>
                                </h5>
                                @if($selectedOrder->items->sum('received_quantity') > 0)
                                    <small class="text-success fw-semibold">({{ $selectedOrder->items->sum('received_quantity') }} received)</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam me-1 text-primary"></i> Order Items Breakdown</h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">Supplier Cost = Invoice Rate</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">System Cost = Converted Rate</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Selling = Retail Price</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Code</th>
                                <th>Product</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Order Qty</th>
                                <th class="text-center">Received Qty</th>
                                <th class="text-end">Supplier Cost</th>
                                <th class="text-end">System Cost</th>
                                <th class="text-end">Selling Price</th>
                                <th class="text-end">Total Supplier</th>
                                <th class="text-end">Total System</th>
                                <th class="text-end">Total Selling</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $modalSupplierTotal = 0;
                                $modalSystemTotal = 0;
                                $modalSellingTotal = 0;
                            @endphp
                            @foreach($selectedOrder->items as $index => $item)
                            @php
                                $costMult = floatval($item->cost_multiplier ?? ($item->product && $item->product->price ? $item->product->price->cost_multiplier : ($selectedOrder->cost_multiplier ?? ($selectedOrder->supplier?->cost_multiplier ?? 1.0))));
                                if ($costMult <= 0) $costMult = 1.0;

                                $supplierCost = $item->supplier_cost !== null && floatval($item->supplier_cost) > 0 
                                    ? floatval($item->supplier_cost) 
                                    : ($item->product && $item->product->price && floatval($item->product->price->supplier_price) > 0 
                                        ? floatval($item->product->price->supplier_price) 
                                        : floatval($item->unit_price));

                                if ($item->system_cost !== null && floatval($item->system_cost) > 0 && (abs(floatval($item->system_cost) - $supplierCost) > 0.01 || $costMult == 1.0)) {
                                    $systemCost = floatval($item->system_cost);
                                } elseif ($item->product && $item->product->price && floatval($item->product->price->system_cost) > 0) {
                                    $systemCost = floatval($item->product->price->system_cost);
                                } else {
                                    $systemCost = round($supplierCost * $costMult, 2);
                                }

                                $sellingPrice = 0;
                                if ($item->product && $item->product->price) {
                                    if (floatval($item->product->price->selling_price) > 0) {
                                        $sellingPrice = floatval($item->product->price->selling_price);
                                    } elseif (floatval($item->product->price->retail_price) > 0) {
                                        $sellingPrice = floatval($item->product->price->retail_price);
                                    }
                                }
                                if ($sellingPrice <= 0 && $item->selling_price !== null && floatval($item->selling_price) > 0) {
                                    $sellingPrice = floatval($item->selling_price);
                                }

                                $discount = floatval($item->discount ?? 0);
                                $discountAmount = ($systemCost * $discount) / 100;
                                $netSystemCost = $systemCost - $discountAmount;
                                
                                $qty = floatval($item->received_quantity ?? ($item->quantity ?? 0));
                                $itemSupplierTotal = $supplierCost * $qty;
                                $itemSystemTotal = $netSystemCost * $qty;
                                $itemSellingTotal = $sellingPrice * $qty;

                                $modalSupplierTotal += $itemSupplierTotal;
                                $modalSystemTotal += $itemSystemTotal;
                                $modalSellingTotal += $itemSellingTotal;
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                <td><code>{{ $item->product->code ?? 'N/A' }}</code></td>
                                <td title="{{ $item->display_name ?? ($item->product->name ?? 'N/A') }}">{{ $item->display_name ?? ($item->product->name ?? 'N/A') }}</td>
                                <td class="text-center">
                                    @if($item->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @elseif($item->status == 'received')
                                    <span class="badge bg-success">Received</span>
                                    @elseif($item->status == 'notreceived')
                                    <span class="badge bg-danger">Not Received</span>
                                    @else
                                    <span class="badge bg-secondary">{{ ucfirst($item->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">
                                    @if($item->status == 'received')
                                    <span class="text-success fw-bold">{{ $item->received_quantity }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end text-info fw-medium">
                                    Rs. {{ number_format($supplierCost, 2) }}
                                </td>
                                <td class="text-end text-dark fw-bold">
                                    <div>
                                        <span>Rs. {{ number_format($systemCost, 2) }}</span>
                                        @if(($item->cost_multiplier ?? 1) > 1)
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.68rem;">{{ $item->cost_multiplier }}x</span>
                                        @endif
                                        @if($discount > 0)
                                        <br><small class="text-danger">-{{ $discount }}%</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end text-success fw-bold">
                                    Rs. {{ number_format($sellingPrice, 2) }}
                                </td>
                                <td class="text-end text-info fw-semibold">
                                    Rs. {{ number_format($itemSupplierTotal, 2) }}
                                </td>
                                <td class="text-end text-dark fw-bold">
                                    Rs. {{ number_format($itemSystemTotal, 2) }}
                                </td>
                                <td class="text-end text-success fw-bold">
                                    Rs. {{ number_format($itemSellingTotal, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card bg-light mt-3">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="text-muted small">Total Supplier Cost:</span>
                                <strong class="text-info fs-6 ms-1">Rs. {{ number_format($modalSupplierTotal, 2) }}</strong>
                            </div>
                            <div>
                                <span class="text-muted small">Total System Cost:</span>
                                <strong class="text-dark fs-6 ms-1">Rs. {{ number_format($modalSystemTotal, 2) }}</strong>
                            </div>
                            <div>
                                <span class="text-muted small">Expected Selling Value:</span>
                                <strong class="text-success fs-5 ms-1">Rs. {{ number_format($modalSellingTotal, 2) }}</strong>
                            </div>
                            <div>
                                <span class="text-muted small">Est. Profit:</span>
                                <strong class="text-success fs-6 ms-1">+ Rs. {{ number_format(max(0, $modalSellingTotal - $modalSystemTotal), 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @if($selectedOrder)
            <div class="modal-footer bg-light d-flex justify-content-between py-2 px-4">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.fast-product-create', ['order_id' => $selectedOrder->id]) }}" class="btn btn-warning text-dark shadow-sm fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i> Edit Order in Fast Entry
                    </a>
                    <button type="button" class="btn btn-outline-danger" wire:click="downloadPDF({{ $selectedOrder->id }})">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                    </button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Edit Order Modal --}}
<div wire:ignore.self class="modal fade" id="editOrderModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-warning bg-opacity-25 py-2 px-3 border-bottom">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-warning"></i>
                        <span>Edit Purchase Order: <code class="text-primary">{{ $editOrderCode }}</code></span>
                        <span class="badge {{ $editOrderStatus === 'complete' ? 'bg-success' : ($editOrderStatus === 'received' ? 'bg-info text-dark' : 'bg-warning text-dark') }} fs-6">
                            {{ ucfirst($editOrderStatus) }}
                        </span>
                    </h5>
                    <small class="text-muted">
                        Supplier: <strong class="text-dark">{{ $editSupplierName }}</strong> · Cost Rate: <strong class="text-dark">{{ $editCostMultiplier }}x</strong> · Sell Rate: <strong class="text-dark">{{ $editSellingMultiplier }}x</strong>
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                {{-- Order Meta Details Row --}}
                <div class="row g-2 mb-3 bg-light p-2 rounded border align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-upc me-1"></i> Order Code</label>
                        <input type="text" class="form-control form-control-sm bg-white fw-bold text-primary" value="{{ $editOrderCode }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-calendar3 me-1"></i> Order Date</label>
                        <input type="date" class="form-control form-control-sm" wire:model="editOrderDate">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-calendar-check me-1"></i> Received Date</label>
                        <input type="date" class="form-control form-control-sm" wire:model="editReceivedDate">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-truck me-1"></i> Supplier</label>
                        <input type="text" class="form-control form-control-sm bg-white fw-bold text-dark" value="{{ $editSupplierName }}" readonly>
                    </div>
                </div>

                {{-- Search and Add Product Section --}}
                <div class="row mb-3">
                    <div class="col-md-12 position-relative"
                        x-data="{ highlightIndex: -1 }"
                        x-on:product-added-to-po-edit-order.window="highlightIndex = -1; $nextTick(() => { let q = document.querySelector('#poEditOrderQty-0'); if(q){ q.focus(); q.select(); } })"
                        x-on:qty-updated-po-edit.window="$nextTick(() => { document.querySelector('#poEditSearchInput')?.focus(); })">
                        <label class="form-label fw-semibold small text-muted mb-1"><i class="bi bi-search me-1"></i> Search & Add Additional Product to this Order</label>
                        <input type="text"
                            id="poEditSearchInput"
                            class="form-control form-control-sm"
                            wire:model.live.debounce.300ms="searchProduct"
                            placeholder="Type product name or code (min 2 characters)..."
                            autocomplete="off"
                            x-on:keydown.arrow-down.prevent="if (highlightIndex < {{ count($products ?? []) - 1 }}) { highlightIndex++; $nextTick(() => { document.querySelector(`[data-po-edit-search-index='${highlightIndex}']`)?.scrollIntoView({block: 'nearest'}); }); }"
                            x-on:keydown.arrow-up.prevent="if (highlightIndex > 0) { highlightIndex--; $nextTick(() => { document.querySelector(`[data-po-edit-search-index='${highlightIndex}']`)?.scrollIntoView({block: 'nearest'}); }); }"
                            x-on:keydown.enter.prevent="if (highlightIndex >= 0) { document.querySelector(`[data-po-edit-search-index='${highlightIndex}']`)?.click(); }"
                            x-on:keydown.escape.prevent="highlightIndex = -1">
                        @if(!empty($products) && count($products) > 0)
                        <ul class="list-group mt-1 position-absolute w-100 z-3 shadow-lg" style="max-height: 300px; overflow-y: auto;">
                            @foreach($products as $sIndex => $product)
                                @if(is_array($product) && ($product['type'] ?? '') === 'variant')
                                <li class="list-group-item list-group-item-action p-2"
                                    data-po-edit-search-result
                                    data-po-edit-search-index="{{ $sIndex }}"
                                    wire:key="edit-search-product-{{ $product['product_id'] }}-{{ str_replace(' ', '-', $product['variant_value']) }}"
                                    wire:click="addProductVariantToEdit({{ $product['product_id'] }}, '{{ addslashes($product['variant_value']) }}')"
                                    :class="highlightIndex === {{ $sIndex }} ? 'bg-light border-primary border-2' : ''"
                                    style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $product['image'] ? asset('storage/' . $product['image']) : asset('images/product.jpg') }}"
                                            alt="{{ $product['name'] }}"
                                            class="me-3"
                                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $product['name'] }} - {{ $product['variant_name'] ?? 'Variant' }}: <strong>{{ $product['variant_value'] }}</strong></div>
                                            <small class="text-muted">
                                                <span class="badge bg-secondary">{{ $product['code'] }}</span>
                                                <span class="ms-2 badge {{ ($product['available_stock'] ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">Available: {{ $product['available_stock'] ?? 0 }} units</span>
                                            </small>
                                        </div>
                                    </div>
                                </li>
                                @else
                                @php $p = is_array($product) ? (object) $product : $product; @endphp
                                <li class="list-group-item list-group-item-action p-2"
                                    data-po-edit-search-result
                                    data-po-edit-search-index="{{ $sIndex }}"
                                    wire:key="edit-search-product-{{ $p->id ?? $p->product_id }}"
                                    wire:click="addProductToEdit({{ $p->id ?? $p->product_id }})"
                                    :class="highlightIndex === {{ $sIndex }} ? 'bg-light border-primary border-2' : ''"
                                    style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ ($p->image ?? null) ? asset('storage/' . ($p->image ?? null)) : asset('images/product.jpg') }}"
                                            alt="{{ $p->name }}"
                                            class="me-3"
                                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $p->name }}</div>
                                            <small class="text-muted">
                                                <span class="badge bg-secondary">{{ $p->code }}</span>
                                                @if(!empty($p->available_stock) || (isset($p->stock) && $p->stock))
                                                <span class="ms-2 badge {{ (($p->available_stock ?? ($p->stock->available_stock ?? 0)) ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">Available: {{ $p->available_stock ?? ($p->stock->available_stock ?? 0) }} units</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                        @endif
                        @if(strlen($searchProduct) >= 1 && strlen($searchProduct) < 2)
                        <div class="text-muted small mt-1">
                            <i class="bi bi-info-circle"></i> Type at least 2 characters to search
                        </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-cart3 me-1 text-primary"></i> Order Items & Values
                        <span class="badge bg-primary ms-1">{{ count($editOrderItems) }}</span>
                    </h6>
                    <small class="text-muted">
                        Tip: You can edit <strong>Quantities</strong>, <strong>Received Status</strong>, and <strong>Prices</strong>. System Cost auto-calculates from Supplier Cost.
                    </small>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th style="min-width: 140px;">Product Name</th>
                                <th class="text-center" style="width: 130px;">Receipt Status</th>
                                <th class="text-center" style="width: 90px;">Order Qty</th>
                                <th class="text-center" style="width: 95px;">Received Qty</th>
                                <th style="width: 110px;">Supplier Cost</th>
                                <th style="width: 115px;">System Cost</th>
                                <th style="width: 115px;">Sell Price</th>
                                <th class="text-end" style="width: 120px;">Total Cost</th>
                                <th class="text-center" style="width: 60px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($editOrderItems as $index => $item)
                            @php
                                $itemStatus = $item['status'] ?? 'received';
                                $isMissed = ($itemStatus === 'notreceived');
                                $itemEffQty = floatval($item['received_quantity'] > 0 ? $item['received_quantity'] : $item['quantity']);
                                $itemSysCost = floatval($item['unit_price'] ?? 0);
                                $rowTotal = $isMissed ? 0 : ($itemEffQty * $itemSysCost);
                            @endphp
                            <tr wire:key="edit-item-{{ $item['product_id'] }}-{{ $index }}" class="{{ $isMissed ? 'table-danger bg-opacity-25' : '' }}">
                                <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $item['name'] }}</div>
                                    <small class="text-muted"><code>{{ $item['code'] ?? 'N/A' }}</code></small>
                                </td>
                                {{-- 1-Click Receipt Toggle --}}
                                <td class="text-center">
                                    @if($isMissed)
                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2 rounded-pill shadow-sm"
                                            wire:click="toggleEditItemStatus({{ $index }})"
                                            title="Click to mark as Received">
                                        <i class="bi bi-x-circle-fill me-1"></i> Not Received
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill shadow-sm"
                                            wire:click="toggleEditItemStatus({{ $index }})"
                                            title="Click to mark as Not Received / Missed">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i> Received
                                    </button>
                                    @endif
                                </td>
                                {{-- Order Qty --}}
                                <td>
                                    <input type="number"
                                        id="poEditOrderQty-{{ $index }}"
                                        class="form-control form-control-sm text-center fw-bold"
                                        min="1"
                                        value="{{ $item['quantity'] }}"
                                        wire:change="updateEditItemQty({{ $index }}, $event.target.value)">
                                </td>
                                {{-- Received Qty --}}
                                <td>
                                    <input type="number"
                                        class="form-control form-control-sm text-center fw-bold {{ $isMissed ? 'text-danger bg-light' : 'text-success' }}"
                                        min="0"
                                        value="{{ $item['received_quantity'] }}"
                                        wire:change="updateEditItemReceivedQty({{ $index }}, $event.target.value)">
                                </td>
                                {{-- Supplier Cost --}}
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text px-1 bg-white text-muted">Rs.</span>
                                        <input type="number" step="any"
                                            class="form-control form-control-sm fw-semibold text-info"
                                            value="{{ $item['supplier_cost'] }}"
                                            wire:change="updateEditItemSupplierCost({{ $index }}, $event.target.value)"
                                            title="Supplier Raw Cost">
                                    </div>
                                </td>
                                {{-- System Cost / Unit Price --}}
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text px-1 bg-white text-muted">Rs.</span>
                                        <input type="number" step="any"
                                            class="form-control form-control-sm fw-bold text-dark"
                                            wire:model.live.debounce.300ms="editOrderItems.{{ $index }}.unit_price"
                                            title="Converted System Cost to store">
                                    </div>
                                </td>
                                {{-- Selling Price --}}
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text px-1 bg-white text-muted">Rs.</span>
                                        <input type="number" step="any"
                                            class="form-control form-control-sm fw-bold text-success"
                                            wire:model.live.debounce.300ms="editOrderItems.{{ $index }}.selling_price"
                                            title="Retail Selling Price">
                                    </div>
                                </td>
                                {{-- Row Total --}}
                                <td class="text-end">
                                    <strong class="{{ $isMissed ? 'text-muted text-decoration-line-through' : 'text-dark' }}">
                                        Rs. {{ number_format($rowTotal, 2) }}
                                    </strong>
                                </td>
                                {{-- Action --}}
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle"
                                        wire:click="removeEditItem({{ $index }})"
                                        title="Remove item from order">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @if(empty($editOrderItems))
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-cart-x display-6 d-block mb-2"></i>
                                    <p class="mb-0">No items in this order. Search and add products above.</p>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                        @if(count($editOrderItems) > 0)
                        @php
                            $editTotalSupp = 0;
                            $editTotalSys = 0;
                            $editTotalSell = 0;
                            $editTotalOrderQty = 0;
                            $editTotalRecQty = 0;
                            $editMissedCount = 0;

                            foreach($editOrderItems as $ei) {
                                $st = $ei['status'] ?? 'received';
                                $editTotalOrderQty += floatval($ei['quantity'] ?? 0);
                                if ($st === 'notreceived') {
                                    $editMissedCount++;
                                } else {
                                    $rq = floatval($ei['received_quantity'] > 0 ? $ei['received_quantity'] : $ei['quantity']);
                                    $editTotalRecQty += $rq;
                                    $editTotalSupp += ($rq * floatval($ei['supplier_cost'] ?? 0));
                                    $editTotalSys += ($rq * floatval($ei['unit_price'] ?? 0));
                                    $editTotalSell += ($rq * floatval($ei['selling_price'] ?? 0));
                                }
                            }
                        @endphp
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Totals:</td>
                                <td class="text-center">{{ $editTotalOrderQty }}</td>
                                <td class="text-center text-success">{{ $editTotalRecQty }}</td>
                                <td class="text-end text-info small">Rs. {{ number_format($editTotalSupp, 2) }}</td>
                                <td class="text-end text-dark small">Rs. {{ number_format($editTotalSys, 2) }}</td>
                                <td class="text-end text-success small">Rs. {{ number_format($editTotalSell, 2) }}</td>
                                <td class="text-end text-primary fs-6">Rs. {{ number_format($editTotalSys, 2) }}</td>
                                <td></td>
                            </tr>
                            @if($editMissedCount > 0)
                            <tr class="bg-danger bg-opacity-10 text-danger small">
                                <td colspan="10" class="py-2">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <strong>{{ $editMissedCount }} item(s) marked as Not Received / Missed.</strong>
                                    These will not be added to active stock and the order status will be updated accordingly.
                                </td>
                            </tr>
                            @endif
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between py-2 px-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Close / Cancel
                </button>
                <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" wire:click="updateOrder" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="updateOrder">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes & Sync Stock
                    </span>
                    <span wire:loading wire:target="updateOrder">
                        <i class="spinner-border spinner-border-sm me-1"></i> Updating & Syncing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

</div>

@push('styles')
<style>
    /* Fix viewOrderModal z-index stacking */
    #viewOrderModal {
        z-index: 1060 !important;
    }
    #viewOrderModal ~ .modal-backdrop {
        z-index: 1055 !important;
    }
    
    /* Force viewOrderModal to be fully visible when shown */
    #viewOrderModal.show {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
    }
    #viewOrderModal .modal-dialog {
        max-width: 96vw !important;
        width: 96vw !important;
        margin: 1rem auto !important;
    }
    #viewOrderModal.show .modal-dialog {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translate(0, 0) !important;
        max-width: 96vw !important;
        width: 96vw !important;
    }
    #viewOrderModal.show .modal-content {
        opacity: 1 !important;
        visibility: visible !important;
        width: 100% !important;
        max-height: 94vh !important;
    }

    #viewOrderModal .table-responsive {
        max-height: 65vh !important;
        overflow-y: auto !important;
        overflow-x: auto !important;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    #viewOrderModal .table thead th {
        position: sticky !important;
        top: 0 !important;
        z-index: 25 !important;
        background-color: #f1f5f9 !important;
        color: #1e293b !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
        border-bottom: 2px solid #cbd5e1 !important;
        white-space: nowrap !important;
    }

    #viewOrderModal .table tfoot tr {
        position: sticky !important;
        bottom: 0 !important;
        z-index: 20 !important;
        background-color: #f8fafc !important;
        box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.08) !important;
    }

    #viewOrderModal .table tfoot td {
        background-color: #f8fafc !important;
    }
    
    .custom-grn-container::-webkit-scrollbar {
        height: 10px;
        width: 8px;
    }
    .custom-grn-container::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 10px;
    }
    .custom-grn-container::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 10px;
        border: 2px solid #f8f9fa;
    }
    .custom-grn-container::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }
    #grnModal .modal-xl {
        max-width: 98% !important;
        margin: 1rem auto;
    }
    #grnModal .modal-content {
        max-height: 95vh;
        display: flex;
        flex-direction: column;
    }
    #grnModal .modal-body {
        overflow-x: hidden;
        overflow-y: auto;
        flex: 1 1 auto;
        max-height: calc(95vh - 180px);
    }
    #grnModal .modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
        background: #fff;
    }
    #grnModal .grn-summary-footer {
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
    }
    .text-wrap {
        white-space: normal !important;
        word-break: break-all;
    }
</style>
@endpush

@push('scripts')
<script>
    // Handle discount input - no auto-calculation of selling price
    document.addEventListener('DOMContentLoaded', function() {
        // Use event delegation for dynamically added inputs
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('discount-input')) {
                const input = e.target;
                const index = input.dataset.index;

                // Calculate GRN total when discount changes
                setTimeout(() => {
                    @this.call('calculateGRNTotal', index);
                }, 150);
            }
            
            if (e.target.classList.contains('supplier-price-input')) {
                const input = e.target;
                const index = input.dataset.index;

                // Calculate GRN total when supplier price changes
                setTimeout(() => {
                    @this.call('calculateGRNTotal', index);
                }, 150);
            }
        });
    });
    
    // Auto-focus search input when modals open
    document.addEventListener('DOMContentLoaded', function() {
        const addModal = document.getElementById('addPurchaseOrderModal');
        if (addModal) {
            addModal.addEventListener('shown.bs.modal', function() {
                setTimeout(() => { document.querySelector('#poSearchInput')?.focus(); }, 100);
            });
        }
        const editModal = document.getElementById('editOrderModal');
        if (editModal) {
            editModal.addEventListener('shown.bs.modal', function() {
                setTimeout(() => { document.querySelector('#poEditSearchInput')?.focus(); }, 100);
            });
        }
    });

    // Listen for openViewOrderModal event
    document.addEventListener('livewire:initialized', () => {
        console.log('Livewire initialized - viewOrderModal listener ready');
        
        // Store original parent for viewOrderModal
        let viewOrderModalOriginalParent = null;
        
        Livewire.on('openViewOrderModal', () => {
            console.log('openViewOrderModal event received');
            
            // Close any open dropdowns first
            document.querySelectorAll('.dropdown-menu.show').forEach(d => {
                d.classList.remove('show');
            });
            document.querySelectorAll('.dropdown-toggle').forEach(d => d.classList.remove('show'));
            document.querySelectorAll('[aria-expanded=true]').forEach(d => d.setAttribute('aria-expanded', 'false'));
            
            // Remove any existing stray backdrops
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            
            // Close any open modals properly
            document.querySelectorAll('.modal.show').forEach(m => {
                const instance = bootstrap.Modal.getInstance(m);
                if (instance) {
                    instance.hide();
                }
                m.classList.remove('show');
                m.style.display = 'none';
                m.removeAttribute('aria-modal');
                m.removeAttribute('role');
            });
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            
            setTimeout(() => {
                const el = document.getElementById('viewOrderModal');
                console.log('viewOrderModal element found:', !!el);
                
                if (el) {
                    try {
                        // Dispose any existing instance
                        const existingInstance = bootstrap.Modal.getInstance(el);
                        if (existingInstance) {
                            existingInstance.dispose();
                        }
                        
                        // Store original parent and move modal to body
                        if (el.parentElement !== document.body) {
                            viewOrderModalOriginalParent = el.parentElement;
                            console.log('Moving modal to body');
                            document.body.appendChild(el);
                        }
                        
                        // Remove aria-hidden that might be lingering
                        el.removeAttribute('aria-hidden');
                        
                        // Create and show modal
                        const modal = new bootstrap.Modal(el, {
                            backdrop: true,
                            keyboard: true
                        });
                        
                        // Listen for modal hidden event to clean up
                        el.addEventListener('hidden.bs.modal', function onHidden() {
                            console.log('viewOrderModal hidden, cleaning up...');
                            
                            // Remove backdrop
                            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                            
                            // Remove modal-open from body
                            document.body.classList.remove('modal-open');
                            document.body.style.removeProperty('overflow');
                            document.body.style.removeProperty('padding-right');
                            
                            // Move modal back to original parent
                            if (viewOrderModalOriginalParent && el.parentElement === document.body) {
                                viewOrderModalOriginalParent.appendChild(el);
                                console.log('Modal moved back to original parent');
                            }
                            
                            // Remove this listener
                            el.removeEventListener('hidden.bs.modal', onHidden);
                        }, { once: true });
                        
                        modal.show();
                        console.log('Modal shown');
                    } catch (e) {
                        console.error('Error showing modal:', e);
                    }
                } else {
                    console.error('viewOrderModal element not found!');
                }
            }, 100);
        });
        
        // Helper function to clean up modals before opening new one
        function cleanupModals() {
            // Close any open dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(d => d.classList.remove('show'));
            document.querySelectorAll('.dropdown-toggle').forEach(d => d.classList.remove('show'));
            document.querySelectorAll('[aria-expanded=true]').forEach(d => d.setAttribute('aria-expanded', 'false'));
            
            // Remove stray backdrops
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            
            // Close any open modals
            document.querySelectorAll('.modal.show').forEach(m => {
                const instance = bootstrap.Modal.getInstance(m);
                if (instance) {
                    instance.hide();
                }
                m.classList.remove('show');
                m.style.display = 'none';
                m.removeAttribute('aria-modal');
                m.removeAttribute('role');
                m.removeAttribute('aria-hidden');
            });
            
            // Clean body state
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }
        
        // Listen for openEditOrderModal event
        Livewire.on('openEditOrderModal', () => {
            console.log('openEditOrderModal event received');
            cleanupModals();
            
            setTimeout(() => {
                const el = document.getElementById('editOrderModal');
                if (el) {
                    try {
                        const existingInstance = bootstrap.Modal.getInstance(el);
                        if (existingInstance) {
                            existingInstance.dispose();
                        }
                        el.removeAttribute('aria-hidden');
                        const modal = new bootstrap.Modal(el, {
                            backdrop: true,
                            keyboard: true
                        });
                        modal.show();
                        console.log('Edit modal shown');
                    } catch (e) {
                        console.error('Error showing edit modal:', e);
                    }
                }
            }, 150);
        });
        
        // Listen for openGRNModal event
        Livewire.on('openGRNModal', () => {
            console.log('openGRNModal event received');
            cleanupModals();
            
            setTimeout(() => {
                const el = document.getElementById('grnModal');
                if (el) {
                    try {
                        const existingInstance = bootstrap.Modal.getInstance(el);
                        if (existingInstance) {
                            existingInstance.dispose();
                        }
                        el.removeAttribute('aria-hidden');
                        const modal = new bootstrap.Modal(el, {
                            backdrop: true,
                            keyboard: true
                        });
                        modal.show();
                        console.log('GRN modal shown');
                    } catch (e) {
                        console.error('Error showing GRN modal:', e);
                    }
                }
            }, 150);
        });
    });
</script>
@endpush