<div class="container-fluid py-4">
    <!-- Header Block -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark font-inter">E-Commerce Orders</h1>
            <p class="text-muted mb-0">Manage customer orders, update delivery statuses, and track payments.</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-6 col-lg-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Search by Order #, Name, Phone, Email, or City..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Order Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Main List / Details Layout -->
    <div class="row g-4">
        <div class="{{ $selectedOrder ? 'col-lg-7' : 'col-lg-12' }}">
            <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold mb-0 text-dark">Order History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-muted" style="letter-spacing: 0.05em;">
                            <tr>
                                <th class="ps-4">Order Info</th>
                                <th>Customer Details</th>
                                <th>Total Price</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr class="{{ $selectedOrderId == $order->id ? 'table-primary-subtle' : '' }}" style="cursor: pointer;" wire:click="selectOrder({{ $order->id }})">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark font-inter">{{ $order->order_number }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $order->customer_name }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ $order->customer_phone }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-jg-blue">LKR {{ number_format($order->total_amount, 2) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $order->payment_method }}</span>
                                    </td>
                                    <td>
                                        <!-- Order Status -->
                                        @php
                                            $orderBadgeClass = match($order->order_status) {
                                                'pending' => 'bg-warning text-dark',
                                                'processing' => 'bg-info text-white',
                                                'shipped' => 'bg-primary text-white',
                                                'delivered' => 'bg-success text-white',
                                                'cancelled' => 'bg-danger text-white',
                                                default => 'bg-secondary text-white'
                                            };
                                            $paymentBadgeClass = match($order->payment_status) {
                                                'paid' => 'bg-success text-white',
                                                'failed' => 'bg-danger text-white',
                                                default => 'bg-warning text-dark'
                                            };
                                        @endphp
                                        <span class="badge {{ $orderBadgeClass }} text-uppercase mb-1" style="font-size: 0.7rem; display: block; width: fit-content;">
                                            {{ $order->order_status }}
                                        </span>
                                        <span class="badge {{ $paymentBadgeClass }} text-uppercase" style="font-size: 0.65rem; display: block; width: fit-content;">
                                            {{ $order->payment_status }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-outline-secondary btn-sm" wire:click.prevent.stop="selectOrder({{ $order->id }})">
                                            <i class="bi bi-eye"></i> Details
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-receipt-cutoff fs-2 mb-2 d-block"></i>
                                        No e-commerce orders found matching your filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($orders->hasPages())
                    <div class="card-footer bg-white border-top py-3">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Detail Panel -->
        @if ($selectedOrder)
            <div class="col-lg-5">
                <div class="card border shadow-sm sticky-top" style="border-radius: 12px; top: 20px;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold mb-0 text-dark">Order Details</h5>
                        <button type="button" class="btn-close" aria-label="Close" wire:click="closeDetails"></button>
                    </div>
                    <div class="card-body p-4">
                        <!-- Summary Card Info -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fs-5 fw-bold font-inter text-jg-blue">{{ $selectedOrder->order_number }}</span>
                            <span class="text-muted small">{{ $selectedOrder->created_at->format('M d, Y h:i A') }}</span>
                        </div>

                        <!-- Status Modifiers -->
                        <div class="row g-2 mb-4 p-3 bg-light rounded" style="border: 1px solid var(--border);">
                            <div class="col-6">
                                <label class="form-label small text-muted text-uppercase fw-bold mb-1">Order Status</label>
                                <select class="form-select form-select-sm" wire:change="updateOrderStatus({{ $selectedOrder->id }}, $event.target.value)">
                                    <option value="pending" {{ $selectedOrder->order_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $selectedOrder->order_status == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ $selectedOrder->order_status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ $selectedOrder->order_status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $selectedOrder->order_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted text-uppercase fw-bold mb-1">Payment Status</label>
                                <select class="form-select form-select-sm" wire:change="updatePaymentStatus({{ $selectedOrder->id }}, $event.target.value)">
                                    <option value="pending" {{ $selectedOrder->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ $selectedOrder->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="failed" {{ $selectedOrder->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Customer & Shipping Block -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark text-uppercase small mb-2" style="letter-spacing: 0.05em;">Customer Info</h6>
                            <div class="border p-3 rounded" style="background-color: #fafafa;">
                                <div class="mb-1"><strong>Name:</strong> {{ $selectedOrder->customer_name }}</div>
                                <div class="mb-1"><strong>Phone:</strong> {{ $selectedOrder->customer_phone }}</div>
                                <div class="mb-1"><strong>Email:</strong> {{ $selectedOrder->customer_email }}</div>
                                <div class="mb-1"><strong>Shipping Address:</strong></div>
                                <div class="text-muted">{{ $selectedOrder->shipping_address }}, {{ $selectedOrder->city }} {{ $selectedOrder->postal_code }}</div>
                            </div>
                        </div>

                        <!-- Ordered Items -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark text-uppercase small mb-2" style="letter-spacing: 0.05em;">Items Ordered</h6>
                            <div class="list-group rounded border">
                                @foreach ($selectedOrder->items as $item)
                                    <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                                        <div style="flex: 1; padding-right: 10px;">
                                            <div class="fw-semibold text-dark">{{ $item->product ? $item->product->name : 'Deleted Product' }}</div>
                                            <div class="small text-muted">
                                                LKR {{ number_format($item->price, 2) }} x {{ $item->quantity }}
                                            </div>
                                        </div>
                                        <div class="fw-bold text-dark text-end">
                                            LKR {{ number_format($item->subtotal, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Calculations Block -->
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span>LKR {{ number_format($selectedOrder->subtotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Shipping Charge:</span>
                                <span>LKR {{ number_format($selectedOrder->shipping_charge, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5 text-dark border-top pt-2">
                                <span>Total:</span>
                                <span class="text-jg-blue">LKR {{ number_format($selectedOrder->total_amount, 2) }}</span>
                            </div>
                        </div>

                        @if ($selectedOrder->notes)
                            <div class="mt-3 p-2 bg-light rounded text-muted small" style="border-left: 3px solid var(--accent);">
                                <strong>Order Notes:</strong> {{ $selectedOrder->notes }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
