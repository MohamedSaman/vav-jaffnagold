<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-clipboard-check-fill text-success me-2"></i> Goods Received Notes (GRN)
            </h3>
            <p class="text-muted mb-0">Process and track incoming supplier orders and shipments</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('admin.purchase-order-list') }}" class="btn btn-primary">
                <i class="bi bi-journal-bookmark me-1"></i> Purchase Orders List
            </a>
            <a href="{{ route('admin.fast-product-create') }}" class="btn btn-outline-success">
                <i class="bi bi-lightning-charge me-1"></i> Fast Product Entry
            </a>
        </div>
    </div>

    <div class="container-fluid p-4">
        <!-- Summary Cards -->
        <div class="row mb-2 g-3">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card summary-card awaiting h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-warning bg-opacity-10 me-3">
                                <i class="bi bi-box-seam text-warning fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Receipt Status</p>
                                <h5 class="fw-bold mb-0">
                                    <span class="text-success">{{ $this->getOrderCounts()['complete'] }} Recv</span>
                                    <span class="text-muted fs-6">/ {{ $this->getOrderCounts()['received'] }} Pending</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card summary-card h-100" style="border-left-color: #0dcaf0;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-info bg-opacity-10 me-3">
                                <i class="bi bi-receipt text-info fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Supplier Cost</p>
                                <h5 class="fw-bold mb-0 text-info">Rs. {{ number_format($this->getOrderCounts()['total_supplier_amount'], 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card summary-card h-100" style="border-left-color: #6610f2;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-primary bg-opacity-10 me-3">
                                <i class="bi bi-cash-stack text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total System Cost</p>
                                <h5 class="fw-bold mb-0 text-primary">Rs. {{ number_format($this->getOrderCounts()['total_system_amount'], 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card summary-card h-100" style="border-left-color: #198754;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-container bg-success bg-opacity-10 me-3">
                                <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Expected Selling Value</p>
                                <h5 class="fw-bold mb-0 text-success">Rs. {{ number_format($this->getOrderCounts()['total_selling_amount'], 2) }}</h5>
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    Est. Profit: <strong class="text-success">Rs. {{ number_format($this->getOrderCounts()['total_estimated_profit'], 2) }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Orders Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-list-task text-primary me-2"></i> Purchase Orders
                    </h5>
                    <p class="text-muted small mb-0">Process incoming orders from suppliers</p>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="width: 50%; margin: auto">
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table" style="color: black !important;">
                            <tr>
                                <th class="ps-4">Supplier</th>
                                <th class="text-center">Total Orders</th>
                                <th>Latest Received Date</th>
                                <th class="text-end">Total Supplier Cost</th>
                                <th class="text-end">Total System Cost</th>
                                <th class="text-end pe-4">Expected Selling Total</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrders as $po)
                            <tr class="cursor-pointer" wire:click="viewGRN({{ $po->supplier_id }})">
                                <td class="ps-4">
                                    <span class="fw-bold text-dark fs-6 d-block">{{ $po->supplier->name ?? 'N/A' }}</span>
                                    @if(!empty($po->supplier?->phone))
                                        <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $po->supplier->phone }}</small>
                                    @elseif(!empty($po->supplier?->businessname))
                                        <small class="text-muted">{{ $po->supplier->businessname }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6 px-3 py-1">{{ $po->po_count }} {{ Str::plural('Order', $po->po_count) }}</span>
                                </td>
                                <td>
                                    <span class="fw-medium text-dark">
                                        <i class="bi bi-calendar3 me-1 text-primary"></i>{{ $po->received_date ? \Carbon\Carbon::parse($po->received_date)->format('M d, Y') : '-' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold text-info">Rs. {{ number_format($po->total_supplier_sum ?? $po->total_sum, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-dark">Rs. {{ number_format($po->total_system_sum ?? $po->total_sum, 2) }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="fw-bold text-success">Rs. {{ number_format($po->total_selling_sum ?? 0, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">Received</span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" wire:click.stop="viewGRN({{ $po->supplier_id }})">
                                        <i class="bi bi-eye"></i> View Orders
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
               <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $purchaseOrders->links('livewire.custom-pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View GRN Details Modal -->
    <div wire:ignore.self class="modal fade" id="viewGrnModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable" style="max-width: 96vw; width: 96vw; margin: 1rem auto;">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <div class="d-flex align-items-center gap-2">
                        @if($viewMode === 'single_order')
                            <button type="button" class="btn btn-sm btn-outline-light me-2" wire:click="backToOrdersList">
                                <i class="bi bi-arrow-left"></i> Back to Orders
                            </button>
                            <h5 class="modal-title fw-bold text-white mb-0">
                                Order Details: <span class="text-warning">{{ $selectedSingleOrder?->order_code }}</span> ({{ $selectedSupplier?->name }})
                            </h5>
                        @else
                            <i class="bi bi-truck fs-4 text-white"></i>
                            <div>
                                <h5 class="modal-title fw-bold text-white mb-0">
                                    Supplier Orders: <span class="text-warning">{{ $selectedSupplier?->name }}</span> ({{ count($groupedPOs) }} {{ Str::plural('Order', count($groupedPOs)) }})
                                </h5>
                                <small class="text-white-50">View each purchase order separately or inspect full item breakdowns</small>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($selectedSupplier)
                    <div class="row mb-3 bg-light p-3 rounded align-items-center g-2 shadow-sm border">
                        <div class="col-md-3 border-end">
                            <p class="mb-1 text-muted small text-uppercase fw-bold">Supplier</p>
                            <p class="mb-0 fw-bold fs-6 text-dark">{{ $selectedSupplier->name }}</p>
                            @if(!empty($selectedSupplier->phone))
                                <small class="text-muted d-block"><i class="bi bi-telephone me-1"></i>{{ $selectedSupplier->phone }}</small>
                            @endif
                            @if(!empty($selectedSupplier->businessname))
                                <small class="text-muted d-block">{{ $selectedSupplier->businessname }}</small>
                            @endif
                        </div>
                        <div class="col-md-3 border-end">
                            <p class="mb-1 text-muted small text-uppercase fw-bold">Rates & Multipliers</p>
                            <p class="mb-0">
                                <span class="badge bg-info-subtle text-info border border-info-subtle me-1">Cost: {{ $selectedSupplier->cost_multiplier ?? 1 }}x</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Sell: {{ $selectedSupplier->sell_multiplier ?? 0 }}x</span>
                            </p>
                        </div>
                        <div class="col-md-3 border-end text-center">
                            <p class="mb-1 text-muted small text-uppercase fw-bold">Total Orders Count</p>
                            <h5 class="fw-bold mb-0 text-primary">{{ count($groupedPOs) }} Separate {{ Str::plural('Order', count($groupedPOs)) }}</h5>
                        </div>
                        <div class="col-md-3 text-end">
                            <p class="mb-1 text-muted small text-uppercase fw-bold">Combined System Cost</p>
                            <h5 class="fw-bold mb-0 text-dark">Rs. {{ number_format($groupedPOs->sum(fn($o) => $o->total_system_amount > 0 ? $o->total_system_amount : $o->total_amount), 2) }}</h5>
                        </div>
                    </div>

                    @if($viewMode !== 'single_order')
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm {{ $viewMode === 'orders_list' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setViewMode('orders_list')">
                                <i class="bi bi-collection me-1"></i> Separate Orders ({{ count($groupedPOs) }})
                            </button>
                            <button type="button" class="btn btn-sm {{ $viewMode === 'combined_items' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setViewMode('combined_items')">
                                <i class="bi bi-boxes me-1"></i> All Items Combined ({{ count($grnItems) }})
                            </button>
                        </div>
                        <div>
                            <span class="text-muted small">Click <strong>View Order</strong> on any order to see its separate details & items</span>
                        </div>
                    </div>
                    @endif

                    {{-- VIEW 1: SEPARATE ORDERS LIST --}}
                    @if($viewMode === 'orders_list')
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>Order Code</th>
                                    <th>Order Date</th>
                                    <th>Received Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Items Count</th>
                                    <th class="text-end">Supplier Cost</th>
                                    <th class="text-end">System Cost</th>
                                    <th class="text-end">Expected Selling</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedPOs as $idx => $order)
                                @php
                                    $orderSupplierTotal = $order->total_supplier_amount > 0 ? $order->total_supplier_amount : $order->total_amount;
                                    $orderSystemTotal = $order->total_system_amount > 0 ? $order->total_system_amount : $order->total_amount;
                                    $orderSellingTotal = $order->total_selling_amount > 0 ? $order->total_selling_amount : 0;
                                @endphp
                                <tr>
                                    <td class="text-center fw-bold text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <button type="button" class="btn btn-link p-0 fw-bold text-primary text-decoration-none d-inline-flex align-items-center gap-1" wire:click="viewSingleOrder({{ $order->id }})">
                                            <i class="bi bi-box-arrow-up-right small"></i> {{ $order->order_code }}
                                        </button>
                                    </td>
                                    <td>
                                        <span class="text-dark"><i class="bi bi-calendar3 me-1 text-primary"></i>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark"><i class="bi bi-calendar-check me-1 text-success"></i>{{ $order->received_date ? \Carbon\Carbon::parse($order->received_date)->format('M d, Y') : '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($order->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($order->status == 'complete')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($order->status == 'received')
                                            <span class="badge bg-info">Received</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            {{ $order->items->count() }} items ({{ $order->items->sum('quantity') }} units)
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold text-info">
                                        Rs. {{ number_format($orderSupplierTotal, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        Rs. {{ number_format($orderSystemTotal, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Rs. {{ number_format($orderSellingTotal, 2) }}
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-primary" wire:click="viewSingleOrder({{ $order->id }})">
                                            <i class="bi bi-eye"></i> View Order
                                        </button>
                                        <a href="{{ route('admin.fast-product-create', ['order_id' => $order->id]) }}" class="btn btn-sm btn-outline-warning ms-1" title="Edit Batch Order in Fast Entry">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" wire:click="downloadOrderPDF({{ $order->id }})" title="Download PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="5" class="text-end">Combined Orders Total ({{ count($groupedPOs) }} Orders):</td>
                                    <td class="text-center">{{ $groupedPOs->sum(fn($o) => $o->items->count()) }} items</td>
                                    <td class="text-end text-info">Rs. {{ number_format($groupedPOs->sum(fn($o) => $o->total_supplier_amount > 0 ? $o->total_supplier_amount : $o->total_amount), 2) }}</td>
                                    <td class="text-end text-dark">Rs. {{ number_format($groupedPOs->sum(fn($o) => $o->total_system_amount > 0 ? $o->total_system_amount : $o->total_amount), 2) }}</td>
                                    <td class="text-end text-success">Rs. {{ number_format($groupedPOs->sum(fn($o) => $o->total_selling_amount > 0 ? $o->total_selling_amount : 0), 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif

                    {{-- VIEW 2: SINGLE ORDER DETAILS VIEW --}}
                    @if($viewMode === 'single_order' && $selectedSingleOrder)
                    <div class="card bg-light border-0 mb-3 shadow-sm">
                        <div class="card-body p-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-3 border-end">
                                    <span class="text-muted small text-uppercase fw-semibold d-block">Order Code</span>
                                    <span class="fs-6 fw-bold text-primary">{{ $selectedSingleOrder->order_code }}</span>
                                </div>
                                <div class="col-md-3 border-end">
                                    <span class="text-muted small text-uppercase fw-semibold d-block">Order Dates</span>
                                    <small class="d-block text-dark"><strong>Ordered:</strong> {{ $selectedSingleOrder->order_date ? \Carbon\Carbon::parse($selectedSingleOrder->order_date)->format('M d, Y') : '-' }}</small>
                                    <small class="d-block text-dark"><strong>Received:</strong> {{ $selectedSingleOrder->received_date ? \Carbon\Carbon::parse($selectedSingleOrder->received_date)->format('M d, Y') : '-' }}</small>
                                </div>
                                <div class="col-md-3 border-end">
                                    <span class="text-muted small text-uppercase fw-semibold d-block">Status & Cost Multiplier</span>
                                    <span class="badge bg-success me-1">{{ ucfirst($selectedSingleOrder->status) }}</span>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Multiplier: {{ $selectedSingleOrder->cost_multiplier ?? ($selectedSingleOrder->supplier?->cost_multiplier ?? 1) }}x</span>
                                </div>
                                <div class="col-md-3 text-end">
                                    <span class="text-muted small text-uppercase fw-semibold d-block">Items in this Order</span>
                                    <span class="fw-bold text-dark fs-6">{{ count($selectedSingleOrderItems) }} items ({{ collect($selectedSingleOrderItems)->sum('received_qty') }} units)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-box-seam me-1 text-primary"></i> Order Items Breakdown - {{ $selectedSingleOrder->order_code }}
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.fast-product-create', ['order_id' => $selectedSingleOrder->id]) }}" class="btn btn-sm btn-warning text-dark fw-semibold shadow-sm">
                                <i class="bi bi-pencil-square me-1"></i> Edit Order in Fast Entry
                            </a>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">Supplier Cost = Invoice</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">System Cost</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Selling Price</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
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
                                    $singleSupplierTotal = 0;
                                    $singleSystemTotal = 0;
                                    $singleSellingTotal = 0;
                                @endphp
                                @foreach($selectedSingleOrderItems as $idx => $sItem)
                                @php
                                    $qty = floatval($sItem['received_qty'] > 0 ? $sItem['received_qty'] : $sItem['ordered_qty']);
                                    $suppTotal = $qty * floatval($sItem['supplier_cost']);
                                    $sysTotal = $qty * floatval($sItem['system_cost']);
                                    $sellTotal = $qty * floatval($sItem['selling_price']);
                                    $singleSupplierTotal += $suppTotal;
                                    $singleSystemTotal += $sysTotal;
                                    $singleSellingTotal += $sellTotal;
                                @endphp
                                <tr>
                                    <td class="text-center fw-bold text-muted">{{ $idx + 1 }}</td>
                                    <td><code>{{ $sItem['code'] }}</code></td>
                                    <td><span class="fw-medium text-dark">{{ $sItem['name'] }}</span></td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ ucfirst($sItem['status'] ?? 'received') }}</span>
                                    </td>
                                    <td class="text-center">{{ $sItem['ordered_qty'] }}</td>
                                    <td class="text-center fw-bold text-success">{{ $sItem['received_qty'] }}</td>
                                    <td class="text-end text-info">Rs. {{ number_format($sItem['supplier_cost'], 2) }}</td>
                                    <td class="text-end text-dark fw-bold">Rs. {{ number_format($sItem['system_cost'], 2) }}</td>
                                    <td class="text-end text-success fw-bold">Rs. {{ number_format($sItem['selling_price'], 2) }}</td>
                                    <td class="text-end text-info fw-semibold">Rs. {{ number_format($suppTotal, 2) }}</td>
                                    <td class="text-end text-dark fw-bold">Rs. {{ number_format($sysTotal, 2) }}</td>
                                    <td class="text-end text-success fw-bold">Rs. {{ number_format($sellTotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end">Order Totals:</td>
                                    <td class="text-center">{{ collect($selectedSingleOrderItems)->sum('ordered_qty') }}</td>
                                    <td class="text-center text-success">{{ collect($selectedSingleOrderItems)->sum('received_qty') }}</td>
                                    <td colspan="3" class="text-end">Grand Totals:</td>
                                    <td class="text-end text-info fs-6">Rs. {{ number_format($singleSupplierTotal, 2) }}</td>
                                    <td class="text-end text-dark fs-6">Rs. {{ number_format($singleSystemTotal, 2) }}</td>
                                    <td class="text-end text-success fs-6">Rs. {{ number_format($singleSellingTotal, 2) }}</td>
                                </tr>
                                <tr class="table-light">
                                    <td colspan="9" class="text-end fw-bold">Estimated Order Profit:</td>
                                    <td colspan="3" class="text-end text-success fw-bold fs-5">+ Rs. {{ number_format(max(0, $singleSellingTotal - $singleSystemTotal), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif

                    {{-- VIEW 3: COMBINED ITEMS BREAKDOWN --}}
                    @if($viewMode === 'combined_items')
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0">Received Items ({{ count($grnItems) }})</h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                Supplier Cost = Raw Invoice Rate
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                System Cost = Converted Rate
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                Total Selling = Revenue if all sold
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>PO #</th>
                                    <th>Product</th>
                                    <th class="text-center">Ordered Qty</th>
                                    <th class="text-center">Received Qty</th>
                                    <th class="text-end">Supplier Cost</th>
                                    <th class="text-end">System Cost</th>
                                    <th class="text-end">Selling Price</th>
                                    <th class="text-end">Total Supplier</th>
                                    <th class="text-end">Total System</th>
                                    <th class="text-end">Total Selling</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grandSupplierTotal = 0;
                                    $grandSystemTotal = 0;
                                    $grandSellingTotal = 0;
                                    $totalOrderedQty = 0;
                                    $totalReceivedQty = 0;
                                @endphp
                                @foreach($grnItems as $index => $item)
                                @php
                                    $supplierItemTotal = $this->calculateSupplierItemTotal($item);
                                    $systemItemTotal = $this->calculateSystemItemTotal($item);
                                    $sellingItemTotal = $this->calculateSellingItemTotal($item);
                                    $grandSupplierTotal += $supplierItemTotal;
                                    $grandSystemTotal += $systemItemTotal;
                                    $grandSellingTotal += $sellingItemTotal;
                                    $totalOrderedQty += floatval($item['ordered_qty'] ?? 0);
                                    $totalReceivedQty += floatval($item['received_qty'] ?? 0);
                                @endphp
                                <tr class="
                                    @if(strtolower($item['status'] ?? '') === 'received') table-success
                                    @elseif(strtolower($item['status'] ?? '') === 'notreceived') table-danger
                                    @endif
                                ">
                                    <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                    <td>
                                        <button type="button" class="btn btn-link p-0 badge bg-light text-dark fw-bold border" wire:click="viewSingleOrder({{ $item['order_id'] ?? 0 }})">
                                            {{ $item['order_code'] }}
                                        </button>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $item['name'] }}</span>
                                    </td>
                                    <td class="text-center">{{ $item['ordered_qty'] ?? 0 }}</td>
                                    <td class="text-center fw-bold">{{ $item['received_qty'] ?? 0 }}</td>
                                    <td class="text-end text-info fw-medium">
                                        Rs. {{ number_format($item['supplier_cost'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-end text-dark fw-bold">
                                        Rs. {{ number_format($item['system_cost'] ?? 0, 2) }}
                                        @if(($item['cost_multiplier'] ?? 1) > 1)
                                            <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size: 0.7rem;">{{ $item['cost_multiplier'] }}x</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-success fw-bold">
                                        Rs. {{ number_format($item['selling_price'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-end fw-semibold text-info">
                                        Rs. {{ number_format($supplierItemTotal, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        Rs. {{ number_format($systemItemTotal, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Rs. {{ number_format($sellingItemTotal, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge 
                                            @if(strtolower($item['status'] ?? '') === 'received') bg-success
                                            @elseif(strtolower($item['status'] ?? '') === 'notreceived') bg-danger
                                            @else bg-warning
                                            @endif
                                        ">
                                            {{ ucfirst($item['status'] ?? 'pending') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3" class="text-end">Totals:</td>
                                    <td class="text-center">{{ $totalOrderedQty }}</td>
                                    <td class="text-center">{{ $totalReceivedQty }}</td>
                                    <td colspan="3" class="text-end">Grand Totals:</td>
                                    <td class="text-end text-info fs-6">
                                        Rs. {{ number_format($grandSupplierTotal, 2) }}
                                    </td>
                                    <td class="text-end text-dark fs-6">
                                        Rs. {{ number_format($grandSystemTotal, 2) }}
                                    </td>
                                    <td class="text-end text-success fs-6">
                                        Rs. {{ number_format($grandSellingTotal, 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="table-light">
                                    <td colspan="7" class="text-end fw-bold">Estimated Total Profit (Selling Total - System Cost):</td>
                                    <td colspan="4" class="text-end text-success fw-bold fs-5">
                                        + Rs. {{ number_format(max(0, $grandSellingTotal - $grandSystemTotal), 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif

                    @endif
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        @if($viewMode === 'single_order' && $selectedSingleOrder)
                            <button type="button" class="btn btn-outline-secondary me-2" wire:click="backToOrdersList">
                                <i class="bi bi-arrow-left me-1"></i> Back to Orders List
                            </button>
                            <button type="button" class="btn btn-outline-danger" wire:click="downloadOrderPDF({{ $selectedSingleOrder->id }})">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Download Order PDF
                            </button>
                        @endif
                    </div>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        
    .container-fluid,
    .card,
    .modal-content {
        font-size: 13px !important;
    }

    .table th,
    .table td {
        font-size: 12px !important;
        padding: 0.35rem 0.5rem !important;
    }
     .modal-header{
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        margin-bottom: 0.25rem !important;
     }
   
    .modal-footer,
    .card-header,
    .card-body,
    .row,
    .col-md-6,
    .col-md-4,
    .col-md-2,
    .col-md-12 {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        margin-top: 0.25rem !important;
        margin-bottom: 0.25rem !important;
    }

    .form-control,
    .form-select {
        font-size: 12px !important;
        padding: 0.35rem 0.5rem !important;
    }

    .btn,
    .btn-sm,
    .btn-primary,
    .btn-secondary,
    .btn-outline-danger,
    .btn-outline-secondary {
        font-size: 12px !important;
        padding: 0.25rem 0.5rem !important;
    }

    .badge {
        font-size: 11px !important;
        padding: 0.25em 0.5em !important;
    }

    .list-group-item,
    .dropdown-item {
        font-size: 12px !important;
        padding: 0.35rem 0.5rem !important;
    }

    .summary-card,
    .card {
        border-radius: 8px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
    }

    .icon-container {
        width: 36px !important;
        height: 36px !important;
        font-size: 1.1rem !important;
    }
        .summary-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        .summary-card.awaiting {
            border-left-color: #ffc107;
        }

        .summary-card.received {
            border-left-color: #28a745;
        }

        .icon-container {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            
            
            
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }

        .summary-card.total {
            border-left-color: #2a83df;
        }

        .btn-link {
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-link:hover {
            transform: scale(1.1);
        }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            border-color: #4361ee;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
        }

        .btn-primary:hover {
            background-color: #3f37c9;
            border-color: #3f37c9;
            transform: translateY(-2px);
        }

        /* Product Search Dropdown Styling */
        .product-search {
            position: relative;
        }

        .list-group-item-action:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .list-group {
            border-radius: 8px;
            overflow: hidden;
        }

        .list-group-item {
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .list-group-item:hover {
            background-color: #e3f2fd;
            transform: translateX(5px);
        }

        /* Full-width GRN modal styling with sticky header */
        #viewGrnModal .modal-dialog {
            max-width: 96vw !important;
            width: 96vw !important;
            margin: 1rem auto !important;
        }

        #viewGrnModal .modal-content {
            width: 100% !important;
            max-height: 94vh !important;
        }

        #viewGrnModal .modal-body {
            overflow-y: auto !important;
            max-height: calc(94vh - 120px) !important;
            padding: 1.25rem !important;
        }

        #viewGrnModal .table-responsive {
            max-height: 65vh !important;
            overflow-y: auto !important;
            overflow-x: auto !important;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        #viewGrnModal .table {
            width: 100% !important;
            min-width: 100% !important;
            margin-bottom: 0 !important;
        }

        #viewGrnModal .table thead th {
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

        #viewGrnModal .table tfoot tr {
            position: sticky !important;
            bottom: 0 !important;
            z-index: 20 !important;
            background-color: #f8fafc !important;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.08) !important;
        }

        #viewGrnModal .table tfoot td {
            background-color: #f8fafc !important;
        }

        #viewGrnModal .table td {
            white-space: nowrap;
        }

        #viewGrnModal .table td:nth-child(2) {
            white-space: normal;
            min-width: 250px;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Handle success alerts
        window.addEventListener('alert', event => {
            Swal.fire('Success', event.detail.message, 'success');
            var modalEl = document.getElementById('grnModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
        });

        // Open GRN modal after data is loaded
        Livewire.on('open-grn-modal', () => {
            var modalEl = document.getElementById('grnModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        });

        // Open View GRN modal after data is loaded
        Livewire.on('open-view-grn-modal', () => {
            var modalEl = document.getElementById('viewGrnModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    </script>
    @endpush
</div>