<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-gem text-warning me-2"></i> Gold Plating & Workshop Services
            </h3>
            <p class="text-muted mb-0">Track old jewelry plating, workshop craftsmen accounts, costs and customer collections</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button wire:click="openCreateWorkerModal" class="btn btn-outline-dark fw-semibold shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Add Workshop Worker
            </button>
            <button wire:click="openCreateModal" class="btn btn-warning text-dark fw-bold shadow-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> New Service Order
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

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Total Orders</div>
                    <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($stats['total_orders']) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-journal-text me-1"></i>All time records</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">In Progress</div>
                    <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($stats['in_progress']) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-hourglass-split me-1"></i>At workshop</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Ready For Pickup</div>
                    <div class="fs-4 fw-bold text-info mt-1">{{ number_format($stats['ready']) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-bell-fill me-1"></i>Notify customer</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Delivered</div>
                    <div class="fs-4 fw-bold text-success mt-1">{{ number_format($stats['delivered']) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-check-all me-1"></i>Completed jobs</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Total Revenue</div>
                    <div class="fs-5 fw-bold text-primary mt-1">Rs. {{ number_format($stats['total_revenue'], 0) }}</div>
                    <div class="small text-success fw-semibold mt-1">
                        Profit: Rs. {{ number_format($stats['total_profit'], 0) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-semibold">Worker Payables</div>
                    <div class="fs-5 fw-bold text-danger mt-1">Rs. {{ number_format($stats['total_worker_payable'], 0) }}</div>
                    <div class="small text-muted mt-1"><i class="bi bi-person-gear me-1"></i>Due to Ramesh & Sanker</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Workshop Workers Accounts Section --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-people-fill text-warning me-2"></i> Workshop Craftsmen Accounts & Outstanding Payables
                </h6>
                <small class="text-muted">Maintain workshop costs earned by workers and payouts given to them</small>
            </div>
            <button wire:click="openCreateWorkerModal" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-plus-lg me-1"></i> New Worker
            </button>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                @forelse($workers as $worker)
                <div class="col-lg-6">
                    <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-warning text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 16px;">
                                    {{ strtoupper(substr($worker->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                        {{ $worker->name }}
                                        <button wire:click="openEditWorkerModal({{ $worker->id }})" class="btn btn-sm text-muted p-0" title="Edit Worker & Opening Balance">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </h6>
                                    <small class="text-muted">
                                        {{ $worker->phone ?: 'No phone added' }}
                                        @if($worker->notes) • {{ $worker->notes }} @endif
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $worker->outstanding_balance > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} fs-6 px-2.5 py-1.5">
                                    Payable: Rs. {{ number_format($worker->outstanding_balance, 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="row g-2 text-center my-2 py-2 border-top border-bottom border-secondary border-opacity-10 bg-white rounded">
                            <div class="col-3">
                                <span class="text-muted small d-block">Jobs Done</span>
                                <span class="fw-bold text-dark">{{ $worker->completed_orders_count }} / {{ $worker->assigned_orders_count }}</span>
                            </div>
                            <div class="col-3 border-start">
                                <span class="text-muted small d-block">Past Due</span>
                                <span class="fw-bold text-secondary">Rs. {{ number_format($worker->opening_payable_balance, 0) }}</span>
                            </div>
                            <div class="col-3 border-start">
                                <span class="text-muted small d-block">Earned Cost</span>
                                <span class="fw-bold text-primary">Rs. {{ number_format($worker->total_earned, 0) }}</span>
                            </div>
                            <div class="col-3 border-start">
                                <span class="text-muted small d-block">Paid Out</span>
                                <span class="fw-bold text-success">Rs. {{ number_format($worker->total_paid, 0) }}</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <button wire:click="openWorkerLedgerModal({{ $worker->id }})" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-earmark-text me-1"></i> View Ledger
                            </button>
                            <button wire:click="openWorkerPaymentModal({{ $worker->id }})" class="btn btn-sm btn-warning text-dark fw-bold">
                                <i class="bi bi-cash me-1"></i> Pay Workshop
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-3 text-muted">
                    No workshop workers registered yet. Click "Add Workshop Worker" to create accounts for Ramesh and Sanker.
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search order code, customer, item...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select wire:model.live="categoryFilter" class="form-select">
                        <option value="all">All Sources</option>
                        <option value="customer">Customer Services</option>
                        <option value="shop">Our Shop Work</option>
                        <option value="handmade">Handmade Stock</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select wire:model.live="workerFilter" class="form-select">
                        <option value="all">All Workers</option>
                        @foreach($workers as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} (Payable: Rs. {{ number_format($w->outstanding_balance, 0) }})</option>
                        @endforeach
                        <option value="unassigned">Unassigned</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="all">All Statuses</option>
                        <option value="received">Received (New)</option>
                        <option value="in_progress">In Progress</option>
                        <option value="partial">Partially Received</option>
                        <option value="ready">Ready for Pickup</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select wire:model.live="serviceFilter" class="form-select">
                        <option value="all">All Services</option>
                        <option value="gold_plating">Gold Plating</option>
                        <option value="cleaning">Cleaning / Washing</option>
                        <option value="polishing">Polishing</option>
                        <option value="repair">Repairing / Solder</option>
                        <option value="other">Other Service</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-3 col-sm-6">
                    <select wire:model.live="dateFilter" class="form-select">
                        <option value="">All</option>
                        <option value="today">Today</option>
                        <option value="this_week">Week</option>
                        <option value="this_month">Month</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 py-3">Order Code & Date</th>
                            <th>Customer</th>
                            <th>Service & Item</th>
                            <th>Assigned Worker</th>
                            <th class="text-end">Our Cost</th>
                            <th class="text-end">Charge</th>
                            <th class="text-end">Advance & Balance</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-bold text-dark">{{ $order->order_code }}</span>
                                    @if($order->order_category === 'handmade')
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle" style="font-size: 10px;">
                                            <i class="bi bi-hammer me-0.5"></i>Handmade
                                        </span>
                                    @elseif($order->order_category === 'shop')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">
                                            <i class="bi bi-shop me-0.5"></i>Our Shop
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border" style="font-size: 10px;">
                                            <i class="bi bi-person me-0.5"></i>Customer
                                        </span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $order->received_date ? $order->received_date->format('d M Y') : $order->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                @if($order->order_category === 'handmade')
                                    <span class="fw-semibold text-dark d-block">Shop Stock (Handmade)</span>
                                    <small class="text-muted"><i class="bi bi-box-seam me-1"></i>{{ $order->customer_phone ?: 'Shop Inventory' }}</small>
                                @elseif($order->order_category === 'shop')
                                    <span class="fw-semibold text-dark d-block"><i class="bi bi-shop me-1 text-primary"></i>{{ $order->customer_name }}</span>
                                    <small class="text-muted"><i class="bi bi-tag me-1"></i>{{ $order->customer_phone ?: 'Internal Shop Work' }}</small>
                                @else
                                    <span class="fw-semibold text-dark d-block">{{ $order->customer_name }}</span>
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $order->customer_phone }}</small>
                                @endif
                            </td>
                            <td>
                                @if($order->order_category === 'handmade')
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle text-capitalize mb-1">Handmade Plating</span>
                                @elseif($order->order_category === 'shop')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-capitalize mb-1">Our Shop Plating</span>
                                @else
                                    <span class="badge bg-light text-dark border text-capitalize mb-1">{{ str_replace('_', ' ', $order->service_type) }}</span>
                                @endif
                                <div class="text-dark small fw-medium text-truncate" style="max-width: 180px;" title="{{ $order->item_description }}">
                                    {{ $order->item_description }}
                                </div>
                                @if($order->total_items_count > 0)
                                    <div class="mt-1">
                                        @if($order->status === 'partial')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning" style="font-size: 10px; cursor: pointer;" wire:click="openReceiveModal({{ $order->id }})" title="Click to receive remaining items">
                                                <i class="bi bi-box-seam me-1"></i>{{ $order->received_items_count }}/{{ $order->total_items_count }} Received
                                            </span>
                                        @elseif($order->status === 'ready' || $order->status === 'delivered')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">
                                                <i class="bi bi-check2-all me-1"></i>All {{ $order->total_items_count }} Received
                                            </span>
                                        @elseif($order->status === 'in_progress')
                                            <span class="badge bg-light text-secondary border" style="font-size: 10px; cursor: pointer;" wire:click="openReceiveModal({{ $order->id }})" title="Click to receive items from worker">
                                                <i class="bi bi-hourglass-split me-1"></i>0/{{ $order->total_items_count }} at Workshop
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($order->worker)
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1">
                                        <i class="bi bi-person-gear me-1"></i>{{ $order->worker->name }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1">Unassigned</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($order->our_cost)
                                    <span class="fw-semibold text-secondary">Rs. {{ number_format($order->our_cost, 2) }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($order->order_category === 'handmade')
                                    <span class="badge bg-light text-secondary border">Shop Stock</span>
                                    <small class="d-block text-muted" style="font-size: 10px;">Cost accumulated</small>
                                @elseif($order->order_category === 'shop')
                                    <span class="badge bg-light text-primary border">Shop Work</span>
                                    <small class="d-block text-muted" style="font-size: 10px;">Internal cost</small>
                                @elseif($order->is_charge_tbd || is_null($order->customer_charge))
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">T.B.D.</span>
                                @else
                                    <span class="fw-bold text-dark">Rs. {{ number_format($order->customer_charge, 2) }}</span>
                                    @if($order->profit !== null)
                                        <small class="d-block text-success" style="font-size: 11px;">
                                             Profit: Rs. {{ number_format($order->profit, 2) }}
                                        </small>
                                    @endif
                                @endif
                            </td>
                            <td class="text-end">
                                @if($order->order_category === 'handmade' || $order->order_category === 'shop')
                                    <span class="text-muted small">-</span>
                                @elseif($order->is_charge_tbd)
                                    <span class="small text-muted d-block">Adv: Rs. {{ number_format($order->advance_amount, 2) }}</span>
                                    <span class="text-muted small">Bal: T.B.D.</span>
                                @elseif($order->balance_amount <= 0 && $order->customer_charge > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                                @else
                                    @if($order->advance_amount > 0)
                                        <span class="text-success small d-block">Adv: Rs. {{ number_format($order->advance_amount, 2) }}</span>
                                    @endif
                                    <span class="text-danger fw-semibold small d-block">Bal: Rs. {{ number_format($order->balance_amount, 2) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $statusBadge = match($order->status) {
                                        'received' => 'bg-secondary',
                                        'in_progress' => 'bg-warning text-dark',
                                        'partial' => 'bg-warning-subtle text-warning-emphasis border border-warning fw-bold',
                                        'ready' => 'bg-info text-dark',
                                        'delivered' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $statusLabel = match($order->status) {
                                        'partial' => 'PARTIAL (' . $order->received_items_count . '/' . $order->total_items_count . ')',
                                        default => str_replace('_', ' ', $order->status)
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }} text-uppercase px-2 py-1" style="font-size: 11px;">
                                    {{ $statusLabel }}
                                </span>
                                @if($order->ready_sms_sent_at)
                                    <div class="mt-1">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 10px;" title="Ready SMS sent: {{ $order->ready_sms_sent_at->format('d M Y, h:i A') }} (7-day collection notice)">
                                            <i class="bi bi-chat-check-fill me-1"></i> SMS Sent
                                        </span>
                                    </div>
                                @elseif($order->status === 'ready' && $order->order_category === 'customer')
                                    <div class="mt-1">
                                        <button type="button" wire:click="openSmsModal({{ $order->id }})" class="btn btn-link text-info p-0 text-decoration-none fw-bold" style="font-size: 10px;" title="Send 7-day pickup SMS notice">
                                            <i class="bi bi-chat-dots-fill me-0.5"></i> Send SMS
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('shop-staff.print.gold-plating', $order->id) }}" target="_blank" class="btn btn-outline-secondary" title="Print Receipt">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <button wire:click="viewDetails({{ $order->id }})" class="btn btn-outline-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button wire:click="openEditModal({{ $order->id }})" class="btn btn-outline-primary" title="Edit Order / Assign Worker">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="confirmDeleteOrder({{ $order->id }})" class="btn btn-outline-danger" title="Delete Order">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                    {{-- Quick Status Next Step Button --}}
                                    @if($order->status === 'received')
                                    <button wire:click="updateStatus({{ $order->id }}, 'in_progress')" class="btn btn-warning text-dark" title="Send to Workshop / In Progress">
                                        <i class="bi bi-play-fill"></i>
                                    </button>
                                    @elseif($order->status === 'in_progress' || $order->status === 'partial')
                                    <button wire:click="openReceiveModal({{ $order->id }})" class="btn {{ $order->status === 'partial' ? 'btn-warning text-dark fw-bold' : 'btn-info text-dark fw-semibold' }}" title="Check & Receive Products One by One">
                                        <i class="bi bi-check2-square me-1"></i> Receive ({{ $order->received_items_count }}/{{ $order->total_items_count }})
                                    </button>
                                    @elseif($order->status === 'ready')
                                        @if($order->order_category === 'handmade')
                                        <button wire:click="openAddToStockModal({{ $order->id }})" class="btn btn-success text-white fw-bold shadow-sm" title="Finish & Add to POS Stock">
                                            <i class="bi bi-box-seam me-1"></i> Add to Stock
                                        </button>
                                        @elseif($order->order_category === 'shop')
                                        <button wire:click="markShopOrderCompleted({{ $order->id }})" class="btn btn-success text-white fw-bold shadow-sm" title="Finish Shop Plating & Return to Shop">
                                            <i class="bi bi-check2-circle me-1"></i> Finish Shop Job
                                        </button>
                                        @else
                                        <button wire:click="openSmsModal({{ $order->id }})" class="btn {{ $order->ready_sms_sent_at ? 'btn-outline-info' : 'btn-info text-dark fw-bold' }}" title="{{ $order->ready_sms_sent_at ? 'Ready SMS Sent (' . $order->ready_sms_sent_at->format('d M, h:i A') . ') - Click to Resend' : 'Send Ready SMS to customer (7 Days Pickup Notice)' }}">
                                            <i class="bi {{ $order->ready_sms_sent_at ? 'bi-chat-check-fill' : 'bi-chat-dots-fill' }} me-1"></i>
                                            <span class="d-none d-lg-inline">{{ $order->ready_sms_sent_at ? 'SMS Sent' : 'Send SMS' }}</span>
                                        </button>
                                        <button wire:click="openDeliverModal({{ $order->id }})" class="btn btn-success" title="Deliver & Collect Balance">
                                            <i class="bi bi-box-arrow-right me-1"></i> Deliver
                                        </button>
                                        @endif
                                        @if($order->total_items_count > 1)
                                        <button wire:click="openReceiveModal({{ $order->id }})" class="btn btn-outline-success" title="View Checked Items">
                                            <i class="bi bi-check2-all"></i>
                                        </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                No gold plating or service orders found matching your criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
            <div class="p-3 border-top">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Create Service Order Modal --}}
    @if($showCreateModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-warning text-dark py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-gem me-2"></i> New Service / Gold Plating Order
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeCreateModal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="saveOrder">
                        {{-- Our Shop Work Checkbox --}}
                        <div class="card bg-warning bg-opacity-10 border border-warning rounded-3 mb-3 p-3">
                            <div class="form-check form-switch mb-0 d-flex align-items-center gap-3">
                                <input class="form-check-input fs-5 mt-0" type="checkbox" id="shopWorkCheck" wire:model.live="is_shop_work">
                                <div>
                                    <label class="form-check-label fw-bold text-dark fs-6 cursor-pointer" for="shopWorkCheck">
                                        <i class="bi bi-shop text-primary me-1"></i> For Our Shop Work (Shop Jewelry Plating)
                                    </label>
                                    <div class="text-muted small">
                                        Check this if plating our own store jewelry/stock. Customer billing and advance payment will be skipped, and only workshop costs will be tracked.
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($is_shop_work)
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Shop Tag / Department <span class="text-danger">*</span></label>
                                <input type="text" wire:model="customer_name" class="form-control @error('customer_name') is-invalid @enderror" placeholder="e.g. Our Shop / Showcase">
                                @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Internal Reference</label>
                                <input type="text" wire:model="customer_phone" class="form-control" placeholder="e.g. Internal / Counter 1 (Optional)">
                            </div>
                        </div>
                        @else
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="customer_name" class="form-control @error('customer_name') is-invalid @enderror" placeholder="Customer full name">
                                @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer Phone <span class="text-danger">*</span></label>
                                <input type="text" wire:model="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" placeholder="07X XXXXXXX">
                                @error('customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Customer Address</label>
                            <input type="text" wire:model="customer_address" class="form-control" placeholder="City / Address (Optional)">
                        </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Service Type <span class="text-danger">*</span></label>
                                <select wire:model="service_type" class="form-select">
                                    <option value="gold_plating">Gold Plating</option>
                                    <option value="cleaning">Cleaning / Washing</option>
                                    <option value="polishing">Polishing</option>
                                    <option value="repair">Repairing / Solder</option>
                                    <option value="other">Other Service</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Assign Workshop Worker</label>
                                <select wire:model="worker_id" class="form-select @error('worker_id') is-invalid @enderror">
                                    <option value="">-- Select Worker (Ramesh / Sanker) --</option>
                                    @foreach($workers as $w)
                                        <option value="{{ $w->id }}">
                                             {{ $w->name }} (Payable: Rs. {{ number_format($w->outstanding_balance, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('worker_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($worker_id)
                                    @php $selectedW = $workers->firstWhere('id', $worker_id); @endphp
                                    @if($selectedW)
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i>Current payable to {{ $selectedW->name }}: <strong class="text-danger">Rs. {{ number_format($selectedW->outstanding_balance, 2) }}</strong>
                                    </small>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0 text-dark">
                                    <i class="bi bi-list-check me-1 text-primary"></i> {{ $is_shop_work ? 'Shop Jewelry Pieces & Workshop Costs' : 'Service Products & Fees (ITEMS & FEE)' }} <span class="text-danger">*</span>
                                </label>
                                <button type="button" wire:click="addServiceItem" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" style="font-size: 11px;">
                                    <i class="bi bi-plus-lg me-1"></i> + Add Item
                                </button>
                            </div>

                            <div class="table-responsive border rounded-3 bg-white shadow-sm">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="small text-secondary text-uppercase fw-bold" style="font-size: 11px;">
                                            <th style="width: 38px;" class="text-center">#</th>
                                            <th>ITEMS (Product Description) <span class="text-danger">*</span></th>
                                            <th style="width: 170px;" class="text-end">{{ $is_shop_work ? 'EST. COST (RS.)' : 'FEE (RS.)' }}</th>
                                            <th style="width: 45px;" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($serviceItems as $idx => $item)
                                        <tr>
                                            <td class="text-center text-muted small fw-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <input type="text" 
                                                    class="form-control form-control-sm fw-semibold" 
                                                    wire:model="serviceItems.{{ $idx }}.item_name"
                                                    placeholder="{{ $is_shop_work ? 'e.g. 5 kodi chains, 3 rings...' : 'e.g. kodi chain, ring, necklace...' }}">
                                            </td>
                                            <td>
                                                @if($is_shop_work)
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text py-0 px-2 text-muted" style="font-size: 11px;">Cost</span>
                                                    <input type="number" step="0.01" 
                                                        class="form-control form-control-sm text-end fw-bold" 
                                                        wire:model.live="serviceItems.{{ $idx }}.cost"
                                                        placeholder="0.00">
                                                </div>
                                                @else
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text py-0 px-2 text-muted" style="font-size: 11px;">Rs.</span>
                                                    <input type="number" step="0.01" 
                                                        class="form-control form-control-sm text-end fw-bold" 
                                                        wire:model.live="serviceItems.{{ $idx }}.fee"
                                                        placeholder="0.00">
                                                </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(count($serviceItems) > 1)
                                                    <button type="button" wire:click="removeServiceItem({{ $idx }})" class="btn btn-sm btn-link text-danger p-0" title="Remove item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted small"><i class="bi bi-lock"></i></span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @error('serviceItems') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>

                        @if($is_shop_work)
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Our Workshop Cost (Rs.)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" wire:model="our_cost" class="form-control" placeholder="Amount owed to worker for plating our jewelry">
                                </div>
                                <small class="text-muted">Total amount credited to worker when job is completed</small>
                            </div>
                        </div>
                        <div class="p-3 bg-light rounded-3 text-muted small border mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle text-primary fs-5"></i>
                            <div>
                                <strong>Internal Shop Work:</strong> Customer charge & advance payment are disabled. Only craftsman workshop cost is recorded.
                            </div>
                        </div>
                        @else
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Our Workshop Cost (Rs.)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" wire:model="our_cost" class="form-control" placeholder="Amount owed to worker">
                                </div>
                                <small class="text-muted">Will be credited to worker when job is completed</small>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold mb-0">Customer Charge (Rs.)</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="tbdCheck" wire:model.live="is_charge_tbd">
                                        <label class="form-check-label small fw-semibold text-muted" for="tbdCheck">T.B.D. (To Be Decided)</label>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" wire:model="customer_charge" class="form-control @error('customer_charge') is-invalid @enderror" placeholder="{{ $is_charge_tbd ? 'Charge decided later' : 'Final charge' }}" @disabled($is_charge_tbd)>
                                </div>
                                @error('customer_charge') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3 p-3 bg-light rounded-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Advance Amount Collected (Rs.)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success-subtle text-success fw-bold">Rs.</span>
                                    <input type="number" step="0.01" wire:model="advance_amount" class="form-control fw-bold" placeholder="0.00">
                                </div>
                                <small class="text-muted">Recorded immediately in Today's Cash Drawer</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Advance Payment Method</label>
                                <select wire:model="advance_payment_method" class="form-select">
                                    <option value="cash">Cash (Counter Drawer)</option>
                                    <option value="online">Online Transfer</option>
                                    <option value="card">Card / POS Terminal</option>
                                </select>
                            </div>
                        </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Expected Ready Date</label>
                                <input type="date" wire:model="expected_delivery_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Internal Notes</label>
                                <input type="text" wire:model="notes" class="form-control" placeholder="Special internal instructions">
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeCreateModal">Cancel</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                                <i class="bi bi-check2-circle me-1"></i> {{ $is_shop_work ? 'Register Shop Plating Order' : 'Register & Print Receipt' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit Service Order Modal --}}
    @if($showEditModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i> Edit Service Order Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="updateOrder">
                        @if($edit_is_shop_work)
                        <div class="card bg-primary bg-opacity-10 border border-primary rounded-3 mb-3 p-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shop text-primary fs-5"></i>
                                <div>
                                    <div class="fw-bold text-primary">Our Shop Work Order</div>
                                    <small class="text-muted">This is internal store jewelry plating. Customer charges and balances are not applicable.</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($edit_is_shop_work)
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Shop Tag / Department <span class="text-danger">*</span></label>
                                <input type="text" wire:model="edit_customer_name" class="form-control @error('edit_customer_name') is-invalid @enderror">
                                @error('edit_customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Internal Reference</label>
                                <input type="text" wire:model="edit_customer_phone" class="form-control @error('edit_customer_phone') is-invalid @enderror">
                                @error('edit_customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        @else
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="edit_customer_name" class="form-control @error('edit_customer_name') is-invalid @enderror">
                                @error('edit_customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer Phone <span class="text-danger">*</span></label>
                                <input type="text" wire:model="edit_customer_phone" class="form-control @error('edit_customer_phone') is-invalid @enderror">
                                @error('edit_customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Customer Address</label>
                            <input type="text" wire:model="edit_customer_address" class="form-control">
                        </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Service Type <span class="text-danger">*</span></label>
                                <select wire:model="edit_service_type" class="form-select">
                                    <option value="gold_plating">Gold Plating</option>
                                    <option value="cleaning">Cleaning / Washing</option>
                                    <option value="polishing">Polishing</option>
                                    <option value="repair">Repairing / Solder</option>
                                    <option value="other">Other Service</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Assigned Worker</label>
                                <select wire:model="edit_worker_id" class="form-select">
                                    <option value="">-- Select Worker (Ramesh / Sanker) --</option>
                                    @foreach($workers as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }} (Payable: Rs. {{ number_format($w->outstanding_balance, 0) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Order Status <span class="text-danger">*</span></label>
                                <select wire:model="edit_status" class="form-select fw-semibold">
                                    <option value="received">Received (New)</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="partial">Partially Received</option>
                                    <option value="ready">Ready for Pickup</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0 text-dark">
                                    <i class="bi bi-list-check me-1 text-primary"></i> {{ $edit_is_shop_work ? 'Shop Jewelry Pieces & Workshop Costs' : 'Service Products & Fees (ITEMS & FEE)' }} <span class="text-danger">*</span>
                                </label>
                                <button type="button" wire:click="addEditServiceItem" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" style="font-size: 11px;">
                                    <i class="bi bi-plus-lg me-1"></i> + Add Item
                                </button>
                            </div>

                            <div class="table-responsive border rounded-3 bg-white shadow-sm">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="small text-secondary text-uppercase fw-bold" style="font-size: 11px;">
                                            <th style="width: 38px;" class="text-center">#</th>
                                            <th>ITEMS (Product Description) <span class="text-danger">*</span></th>
                                            <th style="width: 170px;" class="text-end">{{ $edit_is_shop_work ? 'EST. COST (RS.)' : 'FEE (RS.)' }}</th>
                                            <th style="width: 45px;" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($edit_serviceItems as $idx => $item)
                                        <tr>
                                            <td class="text-center text-muted small fw-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <input type="text" 
                                                    class="form-control form-control-sm fw-semibold" 
                                                    wire:model="edit_serviceItems.{{ $idx }}.item_name"
                                                    placeholder="{{ $edit_is_shop_work ? 'e.g. 5 kodi chains, 3 rings...' : 'e.g. kodi chain, ring...' }}">
                                            </td>
                                            <td>
                                                @if($edit_is_shop_work)
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text py-0 px-2 text-muted" style="font-size: 11px;">Cost</span>
                                                    <input type="number" step="0.01" 
                                                        class="form-control form-control-sm text-end fw-bold" 
                                                        wire:model.live="edit_serviceItems.{{ $idx }}.cost"
                                                        placeholder="0.00">
                                                </div>
                                                @else
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text py-0 px-2 text-muted" style="font-size: 11px;">Rs.</span>
                                                    <input type="number" step="0.01" 
                                                        class="form-control form-control-sm text-end fw-bold" 
                                                        wire:model.live="edit_serviceItems.{{ $idx }}.fee"
                                                        placeholder="0.00">
                                                </div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(count($edit_serviceItems) > 1)
                                                    <button type="button" wire:click="removeEditServiceItem({{ $idx }})" class="btn btn-sm btn-link text-danger p-0" title="Remove item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted small"><i class="bi bi-lock"></i></span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @error('edit_serviceItems') <div class="text-danger small mt-1 fw-bold">{{ $message }}</div> @enderror
                        </div>

                        @if($edit_is_shop_work)
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Our Workshop Cost (Rs.)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" wire:model="edit_our_cost" class="form-control" placeholder="Workshop fee">
                                </div>
                                <small class="text-muted">Total amount credited to worker when job is completed</small>
                            </div>
                        </div>
                        @else
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Our Workshop Cost (Rs.)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" wire:model="edit_our_cost" class="form-control" placeholder="Workshop fee">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold mb-0">Customer Charge (Rs.)</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="editTbdCheck" wire:model.live="edit_is_charge_tbd">
                                        <label class="form-check-label small fw-semibold text-muted" for="editTbdCheck">T.B.D.</label>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" wire:model="edit_customer_charge" class="form-control @error('edit_customer_charge') is-invalid @enderror" @disabled($edit_is_charge_tbd)>
                                </div>
                                @error('edit_customer_charge') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Expected Ready Date</label>
                                <input type="date" wire:model="edit_expected_delivery_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Notes</label>
                                <input type="text" wire:model="edit_notes" class="form-control">
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeEditModal">Cancel</button>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Order Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete Order
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelDeleteOrder"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="avatar bg-danger-subtle text-danger rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 28px;">
                        <i class="bi bi-trash"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Delete Order {{ $deletingOrderCode }}?</h5>
                    <p class="text-muted mb-0">
                        Are you sure you want to permanently delete this service order? Any recorded payments associated with this order will also be deleted. This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                    <button type="button" class="btn btn-secondary px-4" wire:click="cancelDeleteOrder">Cancel</button>
                    <button type="button" class="btn btn-danger fw-bold px-4" wire:click="deleteOrder">
                        <i class="bi bi-trash me-1"></i> Yes, Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Worker Payout Modal --}}
    @if($showWorkerPaymentModal && $workerPaymentWorker)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-warning text-dark py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-cash-stack me-2"></i> Record Payment to {{ $workerPaymentWorker->name }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeWorkerPaymentModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted small d-block">Current Outstanding Payable:</span>
                            <span class="fw-bold fs-5 text-danger">Rs. {{ number_format($workerPaymentWorker->outstanding_balance, 2) }}</span>
                        </div>
                        <span class="badge bg-warning text-dark px-3 py-2">Workshop Worker</span>
                    </div>

                    <form wire:submit.prevent="saveWorkerPayment">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Amount (Rs.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rs.</span>
                                <input type="number" step="0.01" wire:model="workerPaymentAmount" class="form-control form-control-lg fw-bold text-success @error('workerPaymentAmount') is-invalid @enderror">
                            </div>
                            @error('workerPaymentAmount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                                <select wire:model="workerPaymentMethod" class="form-select">
                                    <option value="cash">Cash (Counter Cash)</option>
                                    <option value="bank_transfer">Bank Transfer / Online</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="workerPaymentDate" class="form-control @error('workerPaymentDate') is-invalid @enderror">
                                @error('workerPaymentDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes / Receipt Ref</label>
                            <input type="text" wire:model="workerPaymentNotes" class="form-control" placeholder="e.g. Paid for last week polishing batches">
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeWorkerPaymentModal">Cancel</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                                <i class="bi bi-check2-circle me-1"></i> Confirm & Save Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Worker Ledger Modal --}}
    @if($showWorkerLedgerModal && $ledgerWorker)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar bg-warning text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            {{ strtoupper(substr($ledgerWorker->name, 0, 2)) }}
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Worker Account Ledger: {{ $ledgerWorker->name }}</h5>
                            <small class="text-white-50">Complete record of workshop jobs assigned, earned charges, and payouts</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeWorkerLedgerModal"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Summary Box --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block">Opening Debt / Past Due</span>
                                <span class="fs-5 fw-bold text-secondary">Rs. {{ number_format($ledgerWorker->opening_payable_balance, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block">Completed Jobs Cost</span>
                                <span class="fs-5 fw-bold text-primary">Rs. {{ number_format($ledgerWorker->total_earned, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block">Total Paid Out</span>
                                <span class="fs-5 fw-bold text-success">Rs. {{ number_format($ledgerWorker->total_paid, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded-3 text-center border border-danger-subtle">
                                <span class="text-danger small fw-semibold d-block">Outstanding Payable</span>
                                <span class="fs-5 fw-bold text-danger">Rs. {{ number_format($ledgerWorker->outstanding_balance, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- Jobs List --}}
                        <div class="col-lg-7">
                            <div class="card border rounded-3 h-100">
                                <div class="card-header bg-light py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-gem me-1 text-warning"></i> Assigned Workshop Jobs ({{ $ledgerWorker->orders->count() }})</span>
                                    <small class="text-muted">Earned upon Ready / Delivered</small>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 380px;">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light small text-uppercase">
                                                <tr>
                                                    <th class="ps-3 py-2">Order</th>
                                                    <th>Item & Service</th>
                                                    <th>Status</th>
                                                    <th class="text-end pe-3">Cost Earned</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($ledgerWorker->orders as $ord)
                                                <tr>
                                                    <td class="ps-3 py-2">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <span class="fw-semibold text-dark">{{ $ord->order_code }}</span>
                                                            @if($ord->order_category === 'handmade')
                                                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle" style="font-size: 9px;">Handmade</span>
                                                            @endif
                                                        </div>
                                                        <small class="text-muted">{{ $ord->received_date ? $ord->received_date->format('d/m/y') : '' }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate small" style="max-width: 170px;">{{ $ord->item_description }}</div>
                                                        <span class="badge bg-light text-muted border text-capitalize" style="font-size: 10px;">{{ $ord->order_category === 'handmade' ? 'Handmade Plating' : str_replace('_', ' ', $ord->service_type) }}</span>
                                                    </td>
                                                    <td>
                                                        @if(in_array($ord->status, ['ready', 'delivered']))
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">Earned</span>
                                                        @else
                                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 10px;">{{ $ord->status }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <span class="fw-bold {{ in_array($ord->status, ['ready', 'delivered']) ? 'text-primary' : 'text-muted' }}">
                                                            Rs. {{ number_format($ord->our_cost ?? 0, 2) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted small">No jobs assigned yet.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Payouts List --}}
                        <div class="col-lg-5">
                            <div class="card border rounded-3 h-100">
                                <div class="card-header bg-light py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-cash-stack me-1 text-success"></i> Payments Given ({{ $ledgerWorker->payments->count() }})</span>
                                    <button wire:click="openWorkerPaymentModal({{ $ledgerWorker->id }})" class="btn btn-xs btn-warning text-dark fw-bold py-0 px-2" style="font-size: 11px;">
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
                                                @forelse($ledgerWorker->payments as $p)
                                                <tr>
                                                    <td class="ps-3 py-2">
                                                        <span class="fw-semibold text-dark d-block">{{ $p->payment_date ? $p->payment_date->format('d M Y') : '' }}</span>
                                                        @if($p->notes) <small class="text-muted d-block text-truncate" style="max-width: 140px;">{{ $p->notes }}</small> @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 10px;">{{ $p->payment_method }}</span>
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <span class="fw-bold text-success">Rs. {{ number_format($p->amount, 2) }}</span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-4 text-muted small">No payments recorded yet.</td>
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
                    <button type="button" class="btn btn-secondary px-4" wire:click="closeWorkerLedgerModal">Close</button>
                    <button type="button" class="btn btn-warning text-dark fw-bold px-4" wire:click="openWorkerPaymentModal({{ $ledgerWorker->id }})">
                        <i class="bi bi-cash me-1"></i> Give Payment to {{ $ledgerWorker->name }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Manage Worker / Add or Edit Modal --}}
    @if($showWorkerModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-gear me-2"></i> {{ $manageWorkerId ? 'Edit Workshop Worker & Opening Balance' : 'Register New Workshop Worker' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeWorkerModal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="saveWorker">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Worker Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="worker_name" class="form-control @error('worker_name') is-invalid @enderror" placeholder="e.g. Ramesh or Sanker">
                            @error('worker_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" wire:model="worker_phone" class="form-control" placeholder="07X XXXXXXX (Optional)">
                        </div>

                        <div class="mb-3 p-3 bg-light rounded-3">
                            <label class="form-label fw-semibold">Opening Payable Balance (Past Due Owed to Worker)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Rs.</span>
                                <input type="number" step="0.01" wire:model="worker_opening_balance" class="form-control fw-bold text-danger @error('worker_opening_balance') is-invalid @enderror" placeholder="0.00">
                            </div>
                            <small class="text-muted">Enter any prior money we already owe this worker before starting</small>
                            @error('worker_opening_balance') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes / Workshop Specialty</label>
                            <input type="text" wire:model="worker_notes" class="form-control" placeholder="e.g. Gold Plating & Polishing craftsman">
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeWorkerModal">Cancel</button>
                            <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                                <i class="bi bi-save me-1"></i> Save Worker
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Deliver Modal --}}
    @if($showDeliverModal && $deliveringOrder)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-box-seam me-2"></i> Deliver Item & Collect Balance
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDeliverModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Order Code:</span>
                            <span class="fw-bold text-dark">{{ $deliveringOrder->order_code }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Customer:</span>
                            <span class="fw-semibold text-dark">{{ $deliveringOrder->customer_name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Item:</span>
                            <span class="text-dark">{{ $deliveringOrder->item_description }}</span>
                        </div>
                        @if($deliveringOrder->worker)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Craftsman:</span>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle">{{ $deliveringOrder->worker->name }}</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <span class="text-muted">Customer Total Charge:</span>
                            <span class="fw-bold text-dark">
                                {{ $deliveringOrder->customer_charge ? 'Rs. ' . number_format($deliveringOrder->customer_charge, 2) : 'T.B.D.' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Already Paid (Advance):</span>
                            <span class="text-success fw-semibold">Rs. {{ number_format($deliveringOrder->payments->sum('amount'), 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2 fs-6">
                            <span class="fw-bold text-danger">Pending Balance Due:</span>
                            <span class="fw-bold text-danger">Rs. {{ number_format($collectAmount, 2) }}</span>
                        </div>
                    </div>

                    <form wire:submit.prevent="completeDelivery">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Collect Amount (Rs.) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-success-subtle text-success fw-bold">Rs.</span>
                                <input type="number" step="0.01" wire:model="collectAmount" class="form-control form-control-lg fw-bold text-success @error('collectAmount') is-invalid @enderror">
                            </div>
                            @error('collectAmount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select wire:model="collectPaymentMethod" class="form-select">
                                <option value="cash">Cash (Counter Cash Drawer)</option>
                                <option value="online">Online Transfer</option>
                                <option value="card">Card / POS Terminal</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Delivery Notes</label>
                            <input type="text" wire:model="deliveryNotes" class="form-control" placeholder="Received by customer, signature, etc.">
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeDeliverModal">Cancel</button>
                            <button type="submit" class="btn btn-success fw-bold px-4">
                                <i class="bi bi-check-circle me-1"></i> Deliver & Print Final Receipt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Add Handmade Plating Order to POS Stock Modal --}}
    @if($showAddToStockModal && $stockOrder)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.65);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-box-seam me-2"></i> Add Handmade Piece to POS Stock
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeAddToStockModal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="confirmAddToStock">
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0">{{ $stockItem ? $stockItem->item_name : $stockOrder->item_description }}</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Plating Completed</span>
                            </div>
                            <div class="small text-muted space-y-1">
                                <div><strong>Job Ref:</strong> {{ $stockOrder->order_code }}</div>
                                @if($stockOrder->worker)
                                <div><strong>Craftsman:</strong> {{ $stockOrder->worker->name }} (Plating Fee: Rs. {{ number_format($stockOrder->our_cost, 2) }})</div>
                                @endif
                                <div><strong>Quantity:</strong> {{ $stockItem ? $stockItem->quantity : 1 }} unit(s)</div>
                                @if($stockItem && $stockItem->weight)
                                <div><strong>Weight:</strong> {{ $stockItem->weight }} g</div>
                                @endif
                            </div>
                        </div>

                        {{-- Total Accumulated Cost Info --}}
                        <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small d-block">Total Accumulated Cost:</span>
                                    <span class="fs-5 fw-bold text-dark">
                                        Rs. {{ number_format($stockItem ? $stockItem->total_accumulated_cost : ($stockOrder->our_cost ?? 0), 2) }}
                                    </span>
                                </div>
                                <div class="text-end small text-muted">
                                    @if($stockItem)
                                    <div>Buy: Rs. {{ number_format($stockItem->purchase_cost, 0) }}</div>
                                    @if($stockItem->stone_cost > 0)
                                    <div>Stone: Rs. {{ number_format($stockItem->stone_cost, 0) }}</div>
                                    @endif
                                    <div>Plate: Rs. {{ number_format($stockItem->plating_cost, 0) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Final POS Selling Price Input --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">
                                Retail Selling Price (Rs.) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white fw-bold">Rs.</span>
                                <input type="number" step="0.01" wire:model="final_selling_price" class="form-control form-control-lg fw-bold text-success @error('final_selling_price') is-invalid @enderror" placeholder="Customer Selling Price">
                            </div>
                            <small class="text-muted d-block mt-1">This product will immediately become active and sellable on the POS Store Billing screen with a unique barcode.</small>
                            @error('final_selling_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="modal-footer px-0 pb-0 border-top-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-light px-4" wire:click="closeAddToStockModal">Cancel</button>
                            <button type="submit" class="btn btn-success fw-bold px-4">
                                <i class="bi bi-check2-circle me-1"></i> Add to POS Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Check & Receive Products From Worker Modal --}}
    @if($showReceiveModal && $receivingOrder)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">
                            <i class="bi bi-check2-square text-warning me-2"></i> Check & Receive Products from Workshop
                        </h5>
                        <small class="text-white-50">
                            Order: <strong class="text-warning">{{ $receivingOrder->order_code }}</strong> 
                            • Customer: <strong>{{ $receivingOrder->customer_name }}</strong>
                            • Craftsman: <strong class="text-info">{{ $receivingOrder->worker ? $receivingOrder->worker->name : 'Unassigned' }}</strong>
                        </small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeReceiveModal"></button>
                </div>

                <div class="modal-body p-4">
                    {{-- Progress Summary Banner --}}
                    @php
                        $receivedCount = $receivingOrder->items->where('status', 'received')->count();
                        $totalCount = $receivingOrder->items->count();
                        $percent = $totalCount > 0 ? round(($receivedCount / $totalCount) * 100) : 0;
                        $remainingCount = max(0, $totalCount - $receivedCount);
                    @endphp

                    <div class="p-3 mb-4 rounded-3 {{ $receivedCount === $totalCount ? 'bg-success-subtle border border-success-subtle' : 'bg-warning-subtle border border-warning-subtle' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="fw-bold mb-0 {{ $receivedCount === $totalCount ? 'text-success' : 'text-dark' }}">
                                    @if($receivedCount === $totalCount)
                                        <i class="bi bi-check-circle-fill me-1 text-success"></i> All Products Received & Verified ({{ $receivedCount }}/{{ $totalCount }})
                                    @else
                                        <i class="bi bi-hourglass-split me-1 text-warning-emphasis"></i> Partial Receiving Progress: {{ $receivedCount }} of {{ $totalCount }} products received
                                    @endif
                                </h6>
                                <small class="text-muted">
                                    @if($receivedCount === $totalCount)
                                        All items from craftsman are now checked into the shop. This order is marked <strong>READY FOR PICKUP</strong>.
                                    @elseif($receivedCount > 0)
                                        {{ $remainingCount }} product(s) still remaining at workshop with {{ $receivingOrder->worker ? $receivingOrder->worker->name : 'craftsman' }}.
                                    @else
                                        All {{ $totalCount }} products are currently at the workshop. Check each item below as it arrives.
                                    @endif
                                </small>
                            </div>
                            <span class="fs-5 fw-bold {{ $receivedCount === $totalCount ? 'text-success' : 'text-dark' }}">{{ $percent }}%</span>
                        </div>

                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $receivedCount === $totalCount ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    {{-- Checklist Table --}}
                    <div class="table-responsive border rounded-3 bg-white shadow-sm mb-3">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-secondary text-uppercase fw-bold" style="font-size: 11px;">
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>PRODUCT / ITEM DESCRIPTION</th>
                                    <th style="width: 130px;" class="text-end">FEE (RS.)</th>
                                    <th style="width: 200px;">RECEIVING STATUS</th>
                                    <th style="width: 150px;" class="text-center">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($receivingOrder->items as $idx => $it)
                                <tr class="{{ $it->isReceived() ? 'bg-success bg-opacity-10' : '' }}">
                                    <td class="text-center fw-bold {{ $it->isReceived() ? 'text-success' : 'text-muted' }}">
                                        @if($it->isReceived())
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        @else
                                            {{ $idx + 1 }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">{{ $it->item_name }}</div>
                                        @if($it->notes)
                                            <small class="text-muted"><i class="bi bi-chat-left-text me-1"></i>{{ $it->notes }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-secondary">
                                        @if($receivingOrder->is_charge_tbd && $it->fee == 0)
                                            <span class="badge bg-light text-muted border">T.B.D.</span>
                                        @else
                                            Rs. {{ number_format($it->fee, 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($it->isReceived())
                                            <span class="badge bg-success text-white px-2 py-1 mb-1">
                                                <i class="bi bi-check2 me-1"></i> Checked & In Shop
                                            </span>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <i class="bi bi-clock me-1"></i>{{ $it->received_at ? $it->received_at->format('d M, h:i A') : 'Received' }}
                                                @if($it->receivedBy)
                                                    <span class="d-block text-secondary">By: {{ $it->receivedBy->name }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1">
                                                <i class="bi bi-hourglass-split me-1"></i> With Craftsman
                                            </span>
                                            <small class="d-block text-muted mt-1" style="font-size: 11px;">Pending delivery</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($it->isReceived())
                                            <button type="button" wire:click="unreceiveSingleItem({{ $it->id }})" class="btn btn-sm btn-outline-secondary" title="Revert item to workshop">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Undo
                                            </button>
                                        @else
                                            <button type="button" wire:click="receiveSingleItem({{ $it->id }})" class="btn btn-sm btn-success fw-bold px-3 shadow-sm">
                                                <i class="bi bi-check-lg me-1"></i> Receive
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No items recorded for this service order.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Info Box --}}
                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            <i class="bi bi-info-circle-fill text-primary me-1"></i>
                            Receiving items one by one keeps track of what the craftsman has returned. When all items arrive, the order automatically transitions to <strong>Ready</strong>.
                        </div>
                        @if($remainingCount > 0 && $totalCount > 1)
                        <button type="button" wire:click="receiveAllItems({{ $receivingOrder->id }})" class="btn btn-sm btn-outline-primary text-nowrap ms-2 fw-semibold">
                            <i class="bi bi-check-all me-1"></i> Receive All Remaining ({{ $remainingCount }})
                        </button>
                        @endif
                    </div>

                    @if($receivedCount === $totalCount && $receivingOrder->order_category === 'customer')
                    <div class="alert alert-info border border-info-subtle shadow-sm rounded-3 mt-3 mb-0 p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-chat-text-fill fs-4 text-info"></i>
                            <div>
                                <div class="fw-bold text-dark">Ready Pickup SMS Notification</div>
                                <div class="small text-muted">
                                    @if($receivingOrder->ready_sms_sent_at)
                                        <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>SMS dispatched on {{ $receivingOrder->ready_sms_sent_at->format('d M, h:i A') }}</span> (Please collect within 7 days notice sent).
                                    @else
                                        All items verified! Customer {{ $receivingOrder->customer_name }} ({{ $receivingOrder->customer_phone }}) can be notified to collect within 7 days.
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button type="button" wire:click="openSmsModal({{ $receivingOrder->id }})" class="btn btn-sm btn-info text-dark fw-bold px-3 shadow-sm">
                            <i class="bi bi-send-fill me-1"></i> {{ $receivingOrder->ready_sms_sent_at ? 'Resend Ready SMS' : 'Send Ready SMS' }}
                        </button>
                    </div>
                    @endif
                </div>

                <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                    <a href="{{ route('shop-staff.print.gold-plating', $receivingOrder->id) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-printer me-1"></i> Print Slip
                    </a>
                    <button type="button" class="btn btn-dark px-4 fw-semibold" wire:click="closeReceiveModal">
                        Done / Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Details Modal --}}
    @if($showDetailsModal && $selectedOrder)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-info-circle me-2"></i> Service Order {{ $selectedOrder->order_code }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDetailsModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                @if($selectedOrder->order_category === 'shop')
                                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-2"><i class="bi bi-shop me-1"></i> Internal Shop Information</h6>
                                    <p class="mb-1"><strong>Shop Tag:</strong> {{ $selectedOrder->customer_name }}</p>
                                    <p class="mb-1"><strong>Reference:</strong> {{ $selectedOrder->customer_phone ?: 'Internal' }}</p>
                                    <p class="mb-0"><strong>Category:</strong> <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Our Shop Work</span></p>
                                @elseif($selectedOrder->order_category === 'handmade')
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-hammer me-1 text-warning"></i> Handmade Inventory Information</h6>
                                    <p class="mb-1"><strong>Source:</strong> {{ $selectedOrder->customer_name }}</p>
                                    <p class="mb-1"><strong>Supplier/Phone:</strong> {{ $selectedOrder->customer_phone ?: 'N/A' }}</p>
                                    <p class="mb-0"><strong>Category:</strong> <span class="badge bg-warning-subtle text-dark border border-warning-subtle">Handmade Inventory</span></p>
                                @else
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-person me-1"></i> Customer Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> {{ $selectedOrder->customer_name }}</p>
                                    <p class="mb-1"><strong>Phone:</strong> {{ $selectedOrder->customer_phone }}</p>
                                    <p class="mb-0"><strong>Address:</strong> {{ $selectedOrder->customer_address ?: 'N/A' }}</p>
                                    @if($selectedOrder->ready_sms_sent_at)
                                        <p class="mb-0 text-success small mt-2 pt-2 border-top">
                                            <i class="bi bi-chat-check-fill me-1"></i><strong>Ready SMS Sent:</strong> {{ $selectedOrder->ready_sms_sent_at->format('d M Y, h:i A') }} (7-day pickup notice)
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-info-square me-1"></i> Order & Workshop Details</h6>
                                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary text-uppercase">{{ str_replace('_', ' ', $selectedOrder->status) }}</span></p>
                                <p class="mb-1">
                                    <strong>Assigned Worker:</strong> 
                                    @if($selectedOrder->worker)
                                        <span class="badge bg-warning text-dark">{{ $selectedOrder->worker->name }}</span>
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </p>
                                <p class="mb-1"><strong>Received:</strong> {{ $selectedOrder->received_date ? $selectedOrder->received_date->format('d M Y') : $selectedOrder->created_at->format('d M Y') }}</p>
                                <p class="mb-0"><strong>Expected:</strong> {{ $selectedOrder->expected_delivery_date ? $selectedOrder->expected_delivery_date->format('d M Y') : 'Not specified' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border rounded-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-gem me-1 text-warning"></i> Service & Item Breakdown</h6>
                            <span class="badge bg-light text-dark border text-capitalize">{{ str_replace('_', ' ', $selectedOrder->service_type) }}</span>
                        </div>

                        @if($selectedOrder->items && $selectedOrder->items->count() > 0)
                        <div class="table-responsive mb-2">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="small text-secondary text-uppercase fw-bold" style="font-size: 11px;">
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th>PRODUCT / ITEM</th>
                                        <th style="width: 180px;">STATUS</th>
                                        <th class="text-end" style="width: 140px;">{{ $selectedOrder->order_category === 'shop' ? 'WORKSHOP COST' : 'FEE (RS.)' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedOrder->items as $idx => $it)
                                    <tr>
                                        <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $it->item_name }}</div>
                                            @if($it->notes)
                                                <small class="text-muted"><i class="bi bi-chat-left-text me-1"></i>{{ $it->notes }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($it->isReceived())
                                                <span class="badge bg-success text-white" style="font-size: 10px;">
                                                    <i class="bi bi-check2 me-1"></i>Checked & In Shop
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-dark border border-warning" style="font-size: 10px;">
                                                    <i class="bi bi-hourglass-split me-1"></i>At Workshop
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            @if($selectedOrder->order_category === 'shop')
                                                Rs. {{ number_format($it->cost ?: 0, 2) }}
                                            @elseif($selectedOrder->is_charge_tbd && $it->fee == 0)
                                                <span class="badge bg-secondary-subtle text-secondary">T.B.D.</span>
                                            @else
                                                Rs. {{ number_format($it->fee, 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light border-top">
                                    <tr>
                                        <th colspan="3" class="text-end text-uppercase" style="font-size: 11px;">{{ $selectedOrder->order_category === 'shop' ? 'Total Workshop Cost:' : 'Total Charge:' }}</th>
                                        <th class="text-end fw-bold {{ $selectedOrder->order_category === 'shop' ? 'text-secondary' : 'text-primary' }}">
                                            @if($selectedOrder->order_category === 'shop')
                                                Rs. {{ number_format($selectedOrder->our_cost ?: 0, 2) }}
                                            @else
                                                {{ $selectedOrder->is_charge_tbd ? 'T.B.D.' : 'Rs. ' . number_format($selectedOrder->customer_charge, 2) }}
                                            @endif
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="col-12 mt-2">
                            <strong>Description:</strong>
                            <p class="text-muted mb-0">{{ $selectedOrder->item_description }}</p>
                        </div>
                        @endif

                        @if($selectedOrder->notes)
                        <div class="mt-2 pt-2 border-top small text-muted">
                            <strong>Notes:</strong> {{ $selectedOrder->notes }}
                        </div>
                        @endif
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-calculator me-1"></i> Financial Breakdown</h6>
                        @if($selectedOrder->order_category === 'shop')
                        <div class="row g-2">
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Workshop Craftsman Cost:</span>
                                <span class="fw-bold text-secondary fs-6">{{ $selectedOrder->our_cost ? 'Rs. ' . number_format($selectedOrder->our_cost, 2) : '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Order Nature:</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1.5 px-2">Internal Store Jewelry (No customer billing)</span>
                            </div>
                        </div>
                        @else
                        <div class="row g-2">
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Our Cost (Worker Charge):</span>
                                <span class="fw-bold text-secondary fs-6">{{ $selectedOrder->our_cost ? 'Rs. ' . number_format($selectedOrder->our_cost, 2) : '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Customer Charge:</span>
                                <span class="fw-bold text-dark fs-6">{{ $selectedOrder->customer_charge ? 'Rs. ' . number_format($selectedOrder->customer_charge, 2) : 'T.B.D.' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Total Paid:</span>
                                <span class="fw-bold text-success fs-6">Rs. {{ number_format($selectedOrder->payments->sum('amount'), 2) }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Balance Due:</span>
                                <span class="fw-bold text-danger fs-6">{{ $selectedOrder->is_charge_tbd ? 'T.B.D.' : 'Rs. ' . number_format($selectedOrder->balance_amount, 2) }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-receipt me-1"></i> Payment History</h6>
                    <div class="table-responsive border rounded-3 mb-3">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Method</th>
                                    <th class="text-end">Amount</th>
                                    <th>Received By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($selectedOrder->payments as $payment)
                                <tr>
                                    <td class="text-muted small">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : $payment->created_at->format('d M Y') }}</td>
                                    <td><span class="badge bg-light text-dark border text-uppercase">{{ $payment->payment_type }}</span></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary text-uppercase">{{ $payment->payment_method }}</span></td>
                                    <td class="text-end fw-bold text-success">Rs. {{ number_format($payment->amount, 2) }}</td>
                                    <td class="small">{{ $payment->user->name ?? 'Staff' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">No payments recorded.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($selectedOrder->notes)
                    <div class="p-3 bg-warning-subtle text-dark rounded-3">
                        <strong>Notes:</strong> {{ $selectedOrder->notes }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                    <div class="d-flex gap-2">
                        <a href="{{ route('shop-staff.print.gold-plating', $selectedOrder->id) }}" target="_blank" class="btn btn-outline-dark">
                            <i class="bi bi-printer me-1"></i> Print Receipt
                        </a>
                        @if($selectedOrder->order_category === 'customer' && in_array($selectedOrder->status, ['ready', 'partial', 'in_progress', 'received']))
                        <button type="button" wire:click="openSmsModal({{ $selectedOrder->id }})" class="btn {{ $selectedOrder->ready_sms_sent_at ? 'btn-outline-info' : 'btn-info text-dark fw-semibold' }}">
                            <i class="bi {{ $selectedOrder->ready_sms_sent_at ? 'bi-chat-check-fill' : 'bi-chat-text-fill' }} me-1"></i>
                            {{ $selectedOrder->ready_sms_sent_at ? 'Resend Ready SMS' : 'Send Ready SMS' }}
                        </button>
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary px-4" wire:click="closeDetailsModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Send Ready SMS Modal --}}
    @if($showSmsModal && $smsOrder)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-info text-dark py-3">
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-chat-text-fill me-2"></i> Send Ready SMS Notification
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeSmsModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded-3 border">
                        <div>
                            <div class="small text-muted">Order Code:</div>
                            <strong class="text-dark">{{ $smsOrder->order_code }}</strong>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">Customer:</div>
                            <strong class="text-dark">{{ $smsOrder->customer_name }}</strong>
                        </div>
                    </div>

                    @if($smsOrder->ready_sms_sent_at)
                    <div class="alert alert-info py-2 px-3 small mb-3 border-0 rounded-2 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill text-info fs-5"></i>
                        <div>
                            Ready SMS was previously sent on <strong>{{ $smsOrder->ready_sms_sent_at->format('d M Y, h:i A') }}</strong>. You can re-send below if needed.
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-secondary">Recipient Phone Number (SMSlenz) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone-fill text-muted"></i></span>
                            <input type="text" class="form-control fw-semibold @error('smsCustomerPhone') is-invalid @enderror" wire:model="smsCustomerPhone" placeholder="07XXXXXXXX or +947XXXXXXXX">
                        </div>
                        @error('smsCustomerPhone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        <small class="text-muted" style="font-size: 11px;">Automatically converted to Sri Lankan standard +94 format</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold small text-uppercase text-secondary mb-0">SMS Message Body <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" wire:click="resetSmsTemplate('default')" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 11px;">Standard</button>
                                <button type="button" wire:click="resetSmsTemplate('urgent')" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 11px;">Urgent Reminder</button>
                            </div>
                        </div>
                        <textarea class="form-control @error('smsMessageText') is-invalid @enderror" wire:model.live="smsMessageText" rows="4" style="font-size: 13px;"></textarea>
                        @error('smsMessageText') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                        <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 11px;">
                            <span>
                                <i class="bi bi-shield-check text-success me-1"></i> Includes 7-day collection term & shop contacts
                            </span>
                            <span>
                                {{ strlen($smsMessageText) }} characters ({{ ceil(max(1, strlen($smsMessageText)) / 160) }} SMS)
                            </span>
                        </div>
                    </div>

                    <div class="p-2.5 bg-light rounded-2 border small text-muted">
                        <i class="bi bi-shop me-1 text-primary"></i> <strong>Shop Contacts included:</strong> {{ config('shop.name', 'Jaffna Gold') }} ({{ config('shop.branch', 'Vavuniya Branch') }}), {{ config('shop.address', '2nd Cross Street, Vavuniya') }}. Tel: {{ config('shop.phone', '0761919650') }} / {{ config('shop.hotline', '0212228589') }}
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 d-flex justify-content-between p-3">
                    <button type="button" class="btn btn-light px-3" wire:click="closeSmsModal">Cancel</button>
                    <button type="button" class="btn btn-info text-dark fw-bold px-4 shadow-sm" wire:click="sendSmsNotification" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendSmsNotification">
                            <i class="bi bi-send-fill me-1"></i> Send Ready SMS
                        </span>
                        <span wire:loading wire:target="sendSmsNotification">
                            <span class="spinner-border spinner-border-sm me-1"></span> Sending SMS...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
