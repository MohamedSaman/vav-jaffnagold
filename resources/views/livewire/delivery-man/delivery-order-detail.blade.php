<div class="container-fluid py-3 px-2 px-md-4">
    @php
        $custName = $sale->customer_display_name ?: 'Valued Customer';
        $custPhone = $sale->customer_display_phone ?: '';
        $custAddress = $sale->customer_display_address ?: '';
        $courierMethod = $sale->deliverySale->delivery_method ?? 'Domex';
        $trackingNumber = $sale->deliverySale->tracking_number ?? '';
        $isConfirmed = ($sale->status === 'confirm');
        $deliveryStatus = $sale->delivery_status ?? 'pending';
        $isCod = ($sale->due_amount > 0 || ($sale->deliverySale->payment_method ?? '') === 'Cash on Delivery');
    @endphp

    <!-- Top Navigation & Actions Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 bg-white p-3 rounded-4 shadow-sm border">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('delivery.dashboard') }}" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center border" style="width: 42px; height: 42px;" title="Back to Orders">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="mb-0 fw-bold text-dark">{{ $sale->invoice_number }}</h4>
                    <span class="badge bg-light text-secondary border font-monospace">{{ $sale->sale_id }}</span>
                    @if($sale->order_source === 'ecommerce')
                        <span class="badge bg-info text-dark"><i class="bi bi-globe me-1"></i> Website Store</span>
                    @elseif($sale->order_source === 'whatsapp')
                        <span class="badge bg-success"><i class="bi bi-whatsapp me-1"></i> WhatsApp</span>
                    @else
                        <span class="badge bg-secondary"><i class="bi bi-person-badge me-1"></i> Direct Entry</span>
                    @endif

                    @if(!$isConfirmed)
                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending Confirmation</span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i> Confirmed</span>
                    @endif

                    @if($deliveryStatus === 'pending')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Ready to Pack</span>
                    @elseif($deliveryStatus === 'packed')
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Packed</span>
                    @elseif($deliveryStatus === 'in_transit')
                        <span class="badge bg-info text-white">In Transit</span>
                    @elseif($deliveryStatus === 'delivered')
                        <span class="badge bg-success text-white">Delivered</span>
                    @elseif(in_array($deliveryStatus, ['returned', 'cancelled']))
                        <span class="badge bg-danger text-white">{{ ucfirst($deliveryStatus) }}</span>
                    @endif
                </div>
                <small class="text-muted">Placed on {{ $sale->created_at->format('M d, Y • h:i A') }}</small>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('delivery.print.delivery-label', $sale->id) }}" target="_blank" class="btn btn-dark rounded-pill px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-printer-fill text-warning"></i>
                <span class="fw-semibold">Print 6x4 Label</span>
            </a>
            <a href="{{ route('delivery.print.sale', $sale->id) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-receipt"></i>
                <span>Invoice / Receipt</span>
            </a>
            @if($sale->whatsapp_url)
                <a href="{{ $sale->whatsapp_url }}" target="_blank" class="btn btn-success rounded-pill px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
                    <i class="bi bi-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
            @endif
            @if($custPhone)
                <a href="tel:{{ $custPhone }}" class="btn btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-telephone-fill"></i>
                    <span>Call</span>
                </a>
                <button type="button" wire:click="openSmsModal" class="btn btn-outline-info rounded-pill px-3 d-inline-flex align-items-center gap-1.5 shadow-sm" title="Send SMS via SMSlenz">
                    <i class="bi bi-chat-text-fill"></i>
                    <span>Send SMS</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Step-by-Step Delivery Tracker Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-truck text-primary"></i> Order & Delivery Lifecycle Tracker
            </h6>
            @if(!in_array($deliveryStatus, ['delivered', 'returned', 'cancelled']))
                <button type="button" wire:click="openCancelModal(false)" class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="bi bi-x-circle me-1"></i> Cancel Order
                </button>
            @elseif($deliveryStatus === 'delivered')
                <button type="button" wire:click="openCancelModal(true)" class="btn btn-sm btn-outline-warning rounded-pill text-dark">
                    <i class="bi bi-arrow-return-left me-1"></i> Mark as Returned
                </button>
            @endif
        </div>
        <div class="card-body p-4">
            <!-- Visual Stepper Progress -->
            <div class="position-relative mb-4 px-2 px-md-5">
                <div class="progress" style="height: 4px;">
                    @php
                        $progressWidth = 0;
                        if ($isConfirmed) $progressWidth = 25;
                        if ($deliveryStatus === 'packed') $progressWidth = 50;
                        if ($deliveryStatus === 'in_transit') $progressWidth = 75;
                        if ($deliveryStatus === 'delivered') $progressWidth = 100;
                    @endphp
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressWidth }}%; transition: width 0.4s ease;"></div>
                </div>
                <div class="d-flex justify-content-between position-absolute top-50 start-0 end-0 translate-middle-y px-2 px-md-5">
                    <!-- Step 1: Order Confirmed -->
                    <div class="text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm {{ $isConfirmed ? 'bg-success text-white' : 'bg-white border border-2 border-secondary text-secondary' }}" style="width: 38px; height: 38px;">
                            <i class="bi {{ $isConfirmed ? 'bi-check-lg' : 'bi-1-circle' }} fs-5"></i>
                        </div>
                        <div class="mt-2 fw-semibold small {{ $isConfirmed ? 'text-success' : 'text-muted' }}">1. Confirmed</div>
                    </div>

                    <!-- Step 2: Packed -->
                    <div class="text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm {{ in_array($deliveryStatus, ['packed', 'in_transit', 'delivered']) ? 'bg-success text-white' : 'bg-white border border-2 border-secondary text-secondary' }}" style="width: 38px; height: 38px;">
                            <i class="bi {{ in_array($deliveryStatus, ['packed', 'in_transit', 'delivered']) ? 'bi-check-lg' : 'bi-box-seam' }} fs-5"></i>
                        </div>
                        <div class="mt-2 fw-semibold small {{ in_array($deliveryStatus, ['packed', 'in_transit', 'delivered']) ? 'text-success' : 'text-muted' }}">2. Packed</div>
                    </div>

                    <!-- Step 3: In Transit -->
                    <div class="text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm {{ in_array($deliveryStatus, ['in_transit', 'delivered']) ? 'bg-success text-white' : 'bg-white border border-2 border-secondary text-secondary' }}" style="width: 38px; height: 38px;">
                            <i class="bi {{ in_array($deliveryStatus, ['in_transit', 'delivered']) ? 'bi-check-lg' : 'bi-truck' }} fs-5"></i>
                        </div>
                        <div class="mt-2 fw-semibold small {{ in_array($deliveryStatus, ['in_transit', 'delivered']) ? 'text-success' : 'text-muted' }}">3. In Transit</div>
                    </div>

                    <!-- Step 4: Delivered -->
                    <div class="text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm {{ $deliveryStatus === 'delivered' ? 'bg-success text-white' : 'bg-white border border-2 border-secondary text-secondary' }}" style="width: 38px; height: 38px;">
                            <i class="bi {{ $deliveryStatus === 'delivered' ? 'bi-check-all' : 'bi-house-check' }} fs-5"></i>
                        </div>
                        <div class="mt-2 fw-semibold small {{ $deliveryStatus === 'delivered' ? 'text-success' : 'text-muted' }}">4. Delivered</div>
                    </div>
                </div>
            </div>

            <!-- Current Stage Action Panel -->
            <div class="mt-5 p-3 p-md-4 rounded-3 border" style="background: #f8fafc;">
                @if(!$isConfirmed)
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-exclamation-circle-fill text-warning me-2"></i> Order Needs Confirmation</h6>
                            <p class="text-muted small mb-0">Stock has <strong>not</strong> been deducted yet. Confirming this order verifies product availability and immediately deducts items from inventory stock.</p>
                        </div>
                        <button type="button" wire:click="confirmOrder" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold text-nowrap shadow-sm">
                            <i class="bi bi-check2-circle me-1.5"></i> Confirm Order & Deduct Stock
                        </button>
                    </div>
                @elseif($deliveryStatus === 'pending')
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-box-seam text-primary me-2"></i> Ready for Packaging</h6>
                            <p class="text-muted small mb-0">Inventory stock is allocated. Print the 6x4 shipping label and pack the jewelry securely into delivery parcel.</p>
                        </div>
                        <button type="button" wire:click="markPacked" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold text-nowrap shadow-sm">
                            <i class="bi bi-box-seam-fill me-1.5"></i> Mark Order as Packed
                        </button>
                    </div>
                @elseif($deliveryStatus === 'packed')
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-send text-info me-2"></i> Handover to Courier ({{ $courierMethod }})</h6>
                            <p class="text-muted small mb-0">Order is packed. Enter the courier tracking number and mark parcel as In Transit.</p>
                        </div>
                        <button type="button" wire:click="openCourierModal" class="btn btn-info text-white rounded-pill px-4 py-2 fw-semibold text-nowrap shadow-sm">
                            <i class="bi bi-truck me-1.5"></i> Dispatch & Set Tracking
                        </button>
                    </div>
                @elseif($deliveryStatus === 'in_transit')
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt-fill text-primary me-2"></i> In Transit with {{ $courierMethod }}</h6>
                            <p class="text-muted small mb-0">Tracking: <strong>{{ $trackingNumber ?: 'Pending Tracking #' }}</strong>. Once customer receives parcel, mark as Delivered.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" wire:click="openCourierModal" class="btn btn-outline-secondary rounded-pill px-3 py-2 text-nowrap">
                                <i class="bi bi-pencil me-1"></i> Edit Tracking
                            </button>
                            <button type="button" wire:click="markDelivered" class="btn btn-success rounded-pill px-4 py-2 fw-semibold text-nowrap shadow-sm">
                                <i class="bi bi-check-all me-1.5"></i> Mark as Delivered
                            </button>
                        </div>
                    </div>
                @elseif($deliveryStatus === 'delivered')
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h6 class="fw-bold text-success mb-1"><i class="bi bi-check-circle-fill me-2"></i> Delivery Completed</h6>
                            <p class="text-muted small mb-0">Delivered on {{ $sale->delivered_at ? $sale->delivered_at->format('M d, Y • h:i A') : 'N/A' }}.</p>
                        </div>
                        @if($sale->due_amount > 0)
                            <button type="button" wire:click="openPaymentModal" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark shadow-sm">
                                <i class="bi bi-cash-coin me-1.5"></i> Collect COD Due (LKR {{ number_format($sale->due_amount, 2) }})
                            </button>
                        @else
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-7">
                                <i class="bi bi-check-all me-1"></i> Payment Fully Settled
                            </span>
                        @endif
                    </div>
                @elseif(in_array($deliveryStatus, ['returned', 'cancelled']))
                    <div class="alert alert-danger mb-0 rounded-3">
                        <h6 class="fw-bold mb-1"><i class="bi bi-x-octagon-fill me-2"></i> Order {{ ucfirst($deliveryStatus) }}</h6>
                        <p class="small mb-0">Reason: {{ $sale->deliverySale->return_reason ?? 'Not specified' }}. Allocated items have been returned to available stock.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content: Two Columns -->
    <div class="row g-4">
        <!-- LEFT: Items Ordered & Financial Summary -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-gem me-2 text-warning"></i> Ordered Products ({{ count($sale->items) }} items)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">Item</th>
                                <th>Product Details</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                                @php
                                    $p = $item->product;
                                    $imgUrl = $p ? $p->all_image_urls[0] : asset('images/product.jpg');
                                @endphp
                                <tr>
                                    <td>
                                        <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" class="rounded-3 border object-fit-cover" width="54" height="54">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                        <div class="small text-muted font-monospace">
                                            Code: {{ $item->product_code ?: ($p->code ?? 'N/A') }}
                                            @if($item->variant_value)
                                                <span class="badge bg-light text-dark border ms-1">{{ $item->variant_value }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 fs-7 fw-bold">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="text-end font-monospace">
                                        LKR {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-dark">
                                        LKR {{ number_format($item->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Financial Breakdown Footer -->
                <div class="card-footer bg-light p-4 border-top">
                    <div class="row justify-content-end">
                        <div class="col-md-6 col-lg-5">
                            <div class="d-flex justify-content-between mb-2 text-muted small">
                                <span>Subtotal</span>
                                <span class="font-monospace">LKR {{ number_format($sale->subtotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-muted small">
                                <span>Delivery Fee</span>
                                <span class="font-monospace">
                                    @if(($sale->deliverySale->delivery_charge ?? 0) > 0)
                                        LKR {{ number_format($sale->deliverySale->delivery_charge, 2) }}
                                    @else
                                        <span class="text-success">Free</span>
                                    @endif
                                </span>
                            </div>
                            @if($sale->discount_amount > 0)
                                <div class="d-flex justify-content-between mb-2 text-danger small">
                                    <span>Discount</span>
                                    <span class="font-monospace">- LKR {{ number_format($sale->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <hr class="my-2">
                            <div class="d-flex justify-content-between mb-2 fs-6 fw-bold text-dark">
                                <span>Total Amount</span>
                                <span class="font-monospace">LKR {{ number_format($sale->total_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-3 {{ $sale->due_amount > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                <span class="fw-bold small">{{ $isCod ? 'Cash on Delivery (Due)' : 'Balance Due' }}:</span>
                                <span class="fw-bold font-monospace fs-6">LKR {{ number_format($sale->due_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            @if($sale->notes)
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-card-text text-muted me-2"></i> Order Notes</h6>
                    <div class="text-secondary small" style="white-space: pre-wrap;">{{ $sale->notes }}</div>
                </div>
            @endif
        </div>

        <!-- RIGHT: Customer, Shipping & Payment Information -->
        <div class="col-lg-4">
            <!-- Customer & Delivery Address Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-2"></i> Delivery Destination</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Recipient Name</small>
                        <span class="fs-6 fw-bold text-dark">{{ $custName }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Phone Number</small>
                        <span class="fs-6 font-monospace fw-bold text-dark">{{ $custPhone ?: 'N/A' }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Delivery Address</small>
                        <p class="text-secondary mb-0 small" style="line-height: 1.5;">{{ $custAddress ?: 'No address specified' }}</p>
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Courier Partner</small>
                        <span class="badge bg-dark px-3 py-1.5 rounded-pill">{{ strtoupper($courierMethod) }}</span>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Tracking Number</small>
                            @if($trackingNumber)
                                @if(strcasecmp($courierMethod, 'Domex') === 0)
                                    <button type="button" class="btn btn-sm btn-outline-warning text-dark py-0 px-2 rounded-pill fw-semibold"
                                            wire:click="syncDomexStatus" title="Check live Domex status">
                                        <i class="bi bi-arrow-repeat me-1" wire:loading.class="spin" wire:target="syncDomexStatus"></i> Sync Domex
                                    </button>
                                @elseif(strcasecmp($courierMethod, 'Post') === 0)
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold"
                                            wire:click="syncPostStatus" title="Check live Sri Lanka Post (BePost) status">
                                        <i class="bi bi-arrow-repeat me-1" wire:loading.class="spin" wire:target="syncPostStatus"></i> Sync Post
                                    </button>
                                @endif
                            @endif
                        </div>
                        @if($trackingNumber)
                            @if(strcasecmp($courierMethod, 'Domex') === 0)
                                <a href="https://domex.lk/Order-Details.php?wbno={{ $trackingNumber }}" target="_blank"
                                   class="font-monospace fw-bold text-success text-decoration-none d-inline-flex align-items-center"
                                   title="Open live Domex tracking page">
                                    <i class="bi bi-truck me-1"></i> {{ $trackingNumber }}
                                    <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                </a>
                            @elseif(strcasecmp($courierMethod, 'Post') === 0)
                                <a href="https://bepost.lk/p/Search/" target="_blank"
                                   class="font-monospace fw-bold text-primary text-decoration-none d-inline-flex align-items-center"
                                   title="Open live Sri Lanka Post (BePost) tracking page">
                                    <i class="bi bi-mailbox me-1"></i> {{ $trackingNumber }}
                                    <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                </a>
                            @else
                                <span class="font-monospace fw-bold text-primary">{{ $trackingNumber }}</span>
                            @endif

                            @if($sale->deliverySale?->courier_status_detail)
                                <div class="mt-2 pt-2 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted fw-semibold" style="font-size: 0.7rem;">Live {{ $courierMethod ?: 'Courier' }} Status:</small>
                                        @php
                                            $isFinalTrack = ($sale->domexOrderDetail?->is_final || $sale->postOrderDetail?->is_final);
                                        @endphp
                                        @if($isFinalTrack)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 9px;">
                                                <i class="bi bi-shield-check"></i> Finalized
                                            </span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" style="font-size: 9px;">
                                                <i class="bi bi-arrow-repeat"></i> Auto-Tracking
                                            </span>
                                        @endif
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle text-wrap text-start mt-0.5">
                                        <i class="bi bi-broadcast me-1"></i>{{ $sale->deliverySale->courier_status_detail }}
                                    </span>
                                    @if($sale->deliverySale->courier_last_synced_at)
                                        <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                            Synced {{ $sale->deliverySale->courier_last_synced_at->diffForHumans() }}
                                        </small>
                                    @endif
                                </div>
                            @endif

                            {{-- Full Domex Order Details Breakdown --}}
                            @if($sale->domexOrderDetail)
                                @php $dmx = $sale->domexOrderDetail; @endphp
                                <div class="mt-3 pt-2 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">
                                            <i class="bi bi-file-earmark-ruled text-primary me-1"></i> Domex Waybill Data
                                        </small>
                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 9px;">
                                            {{ $dmx->waybill_number }}
                                        </span>
                                    </div>
                                    <div class="bg-light p-2.5 rounded-3 border small" style="font-size: 11px;">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Pickup Date:</span>
                                            <strong>{{ $dmx->pickup_date ?: 'N/A' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Item / Pieces:</span>
                                            <strong>{{ $dmx->item_type ?: 'Parcel' }} ({{ $dmx->pieces ?: '1' }} pcs, {{ $dmx->weight ?: '0g' }})</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Sender:</span>
                                            <span class="text-truncate" style="max-width: 170px;" title="{{ $dmx->sender_name }}">{{ $dmx->sender_name ?: 'JAFFNA GOLD' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Receiver:</span>
                                            <span class="text-truncate" style="max-width: 170px;" title="{{ $dmx->receiver_name }}">{{ $dmx->receiver_name }} ({{ $dmx->destination_city }})</span>
                                        </div>
                                        @if($dmx->delivered_to || $dmx->delivered_by)
                                            <div class="d-flex justify-content-between pt-1 border-top mt-1">
                                                <span class="text-muted">Delivered To:</span>
                                                <strong class="text-success">{{ $dmx->delivered_to ?: 'Pending' }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Full Post Order Details Breakdown --}}
                            @if($sale->postOrderDetail)
                                @php $pst = $sale->postOrderDetail; @endphp
                                <div class="mt-3 pt-2 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">
                                            <i class="bi bi-mailbox text-primary me-1"></i> Sri Lanka Post Data
                                        </small>
                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 9px;">
                                            {{ $pst->barcode }}
                                        </span>
                                    </div>
                                    <div class="bg-light p-2.5 rounded-3 border small" style="font-size: 11px;">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Acceptance PO:</span>
                                            <strong>{{ $pst->acceptance_po ?: 'Department of Posts' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Delivery PO:</span>
                                            <strong class="text-primary">{{ $pst->delivery_po ?: 'Local Post Office' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Received Date:</span>
                                            <strong>{{ $pst->received_date ?: 'Pending' }}</strong>
                                        </div>
                                        @if($pst->cod_amount)
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">COD Amount:</span>
                                                <strong class="text-success">Rs. {{ $pst->cod_amount }}</strong>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between pt-1 border-top mt-1">
                                            <span class="text-muted">Tracking State:</span>
                                            <strong class="{{ $pst->is_final ? 'text-success' : 'text-warning' }}">
                                                {{ $pst->is_final ? 'Completed / Delivered' : 'In Transit' }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($sale->deliverySale?->courier_tracking_history))
                                <div class="mt-3 pt-2 border-top">
                                    <small class="text-muted d-block fw-semibold mb-2" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock-history me-1 text-primary"></i> {{ $courierMethod ?: 'Courier' }} Tracking Checkpoints ({{ count($sale->deliverySale->courier_tracking_history) }}):
                                    </small>
                                    <div class="p-2.5 rounded-3 bg-light" style="max-height: 220px; overflow-y: auto;">
                                        @foreach(array_reverse($sale->deliverySale->courier_tracking_history) as $idx => $checkpoint)
                                            <div class="d-flex justify-content-between align-items-start mb-2 pb-1 border-bottom border-light">
                                                <div class="fw-semibold text-dark small" style="font-size: 11px;">
                                                    <i class="bi {{ $idx === 0 ? 'bi-geo-alt-fill text-primary' : 'bi-check-circle-fill text-success' }} me-1"></i>
                                                    {{ $checkpoint['status'] ?? '' }}
                                                </div>
                                                <div class="text-muted font-monospace" style="font-size: 10px; white-space: nowrap;">
                                                    {{ $checkpoint['date_time'] ?? '' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <span class="text-muted small italic">Not assigned yet</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment & Settlement Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-wallet2 text-primary me-2"></i> Payment Info</h6>
                    @if($sale->due_amount > 0)
                        <button type="button" wire:click="openPaymentModal" class="btn btn-sm btn-primary rounded-pill">
                            + Collect Cash
                        </button>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Payment Method:</span>
                        <span class="fw-semibold text-dark">{{ $sale->deliverySale->payment_method ?? ($isCod ? 'Cash on Delivery' : 'Prepaid') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Payment Status:</span>
                        <span class="badge {{ $sale->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($sale->payment_status) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-muted">Total Paid:</span>
                        <span class="fw-bold font-monospace text-success">
                            LKR {{ number_format(max(0, $sale->total_amount - $sale->due_amount), 2) }}
                        </span>
                    </div>

                    <!-- Payment Records History -->
                    @if(count($sale->payments) > 0)
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted d-block mb-2 fw-semibold text-uppercase" style="font-size: 0.7rem;">Payment Records</small>
                            @foreach($sale->payments as $pRec)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded-2 small">
                                    <div>
                                        <div class="fw-semibold text-capitalize">{{ $pRec->payment_method }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">{{ $pRec->created_at->format('d/m/Y h:i A') }}</small>
                                    </div>
                                    <div class="fw-bold font-monospace text-success">
                                        LKR {{ number_format($pRec->amount, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Courier Tracking Dispatch -->
    @if($showCourierModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold"><i class="bi bi-truck text-primary me-2"></i> Courier Handover & Dispatch</h5>
                        <button type="button" class="btn-close" wire:click="closeCourierModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Courier Method</label>
                            <select wire:model="courierMethod" class="form-select rounded-3">
                                <option value="Domex">Domex Courier</option>
                                <option value="Post">Sri Lanka Post / EMS</option>
                                <option value="Prompt">Prompt Express</option>
                                <option value="Koombiyo">Koombiyo Delivery</option>
                                <option value="In-House">Direct In-House Rider</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tracking / Waybill Number</label>
                            <input type="text" wire:model="courierTrackingNumber" placeholder="e.g. DMX-89342984" class="form-control rounded-3 font-monospace">
                            <small class="text-muted">Given by Domex / Post upon parcel handover.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeCourierModal">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" wire:click="markInTransit">
                            <i class="bi bi-send me-1"></i> Save & Mark In Transit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Record COD Payment -->
    @if($showPaymentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold"><i class="bi bi-cash-coin text-success me-2"></i> Collect Cash on Delivery (COD)</h5>
                        <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 bg-light rounded-3 mb-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Total Due:</span>
                            <span class="fs-5 fw-bold font-monospace text-danger">LKR {{ number_format($sale->due_amount, 2) }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Amount Collected (LKR)</label>
                            <input type="number" step="0.01" wire:model="paymentAmount" class="form-control rounded-3 font-monospace fs-5">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select wire:model="paymentMethod" class="form-select rounded-3">
                                <option value="cash">Cash (Rider Collected)</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="online">Online / Card</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes (Optional)</label>
                            <input type="text" wire:model="paymentNotes" placeholder="e.g. Received from customer at doorstep" class="form-control rounded-3">
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closePaymentModal">Cancel</button>
                        <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" wire:click="recordPayment">
                            <i class="bi bi-check-circle me-1"></i> Confirm Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Cancel or Return Order -->
    @if($showCancelModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $isReturnAction ? 'Mark Order as Returned' : 'Cancel Delivery Order' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCancelModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted small">
                            {{ $isReturnAction ? 'Marking this order as Returned will reverse inventory stock back to store available stock.' : 'Cancelling this order will restore any deducted inventory back to stock.' }}
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Reason for {{ $isReturnAction ? 'Return' : 'Cancellation' }} <span class="text-danger">*</span></label>
                            <textarea wire:model="cancelReason" rows="3" placeholder="e.g. Customer unreachable, customer refused at doorstep, changed mind" class="form-control rounded-3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeCancelModal">Keep Order</button>
                        <button type="button" class="btn btn-danger rounded-pill px-4 fw-semibold" wire:click="executeCancelOrReturn">
                            Confirm {{ $isReturnAction ? 'Return' : 'Cancellation' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal: Send SMS to Customer -->
    @if($showSmsModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom bg-info-subtle">
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-chat-text-fill text-info"></i> Send SMS Notification
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeSmsModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small">Recipient:</span>
                                <strong class="text-dark">{{ $custName }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Phone (SMSlenz):</span>
                                <strong class="font-monospace text-primary">{{ $custPhone }}</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Quick Message Templates</label>
                            <div class="d-flex flex-wrap gap-1.5 mb-2">
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" 
                                    wire:click="$set('smsCustomMessage', 'Dear {{ $custName }}, your Jaffna Gold order #{{ $sale->invoice_number }} is confirmed and being prepared.')">
                                    Confirmed
                                </button>
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" 
                                    wire:click="$set('smsCustomMessage', 'Dear {{ $custName }}, your order #{{ $sale->invoice_number }} is out for delivery via {{ $courierMethod }}. Tracking: {{ $trackingNumber }}. Jaffna Gold.')">
                                    In Transit
                                </button>
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" 
                                    wire:click="$set('smsCustomMessage', 'Dear {{ $custName }}, our delivery rider is arriving shortly with your Jaffna Gold package. Total: LKR {{ number_format($sale->due_amount, 2) }}.')">
                                    Arriving Shortly
                                </button>
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5" 
                                    wire:click="$set('smsCustomMessage', 'Dear {{ $custName }}, your Jaffna Gold order #{{ $sale->invoice_number }} has been delivered. Thank you for shopping with us!')">
                                    Delivered
                                </button>
                            </div>
                            <label class="form-label small fw-semibold">SMS Message Body</label>
                            <textarea wire:model="smsCustomMessage" rows="4" class="form-control rounded-3" placeholder="Type your SMS message here..."></textarea>
                            <small class="text-muted mt-1 d-block">
                                <i class="bi bi-info-circle me-1"></i> Dispatched via SMSlenz API gateway (Sender: {{ config('sms.sender_id') }}).
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeSmsModal">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" wire:click="sendCustomSms">
                            <span wire:loading.remove wire:target="sendCustomSms"><i class="bi bi-send-fill me-1"></i> Send SMS</span>
                            <span wire:loading wire:target="sendCustomSms"><span class="spinner-border spinner-border-sm me-1"></span> Sending...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
