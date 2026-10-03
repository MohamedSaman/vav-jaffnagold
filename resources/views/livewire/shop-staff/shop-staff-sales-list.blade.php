<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-receipt text-primary me-2"></i> Sale List
            </h3>
            <p class="text-muted mb-0">View, print and manage your store sales</p>
        </div>
        <a href="{{ route('shop-staff.store-billing') }}" class="btn btn-primary">
            <i class="bi bi-cart-plus me-2"></i> New Sale
        </a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search invoice/customer...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="paymentTypeFilter" class="form-select">
                        <option value="">All Payments</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card Payment</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="multiple">Multiple</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" wire:model.live="dateFilter" class="form-control" placeholder="Filter by date">
                </div>
                <div class="col-md-2">
                    @if($search || $statusFilter || $dateFilter || $paymentTypeFilter)
                    <button wire:click="$set('search', ''); $set('statusFilter', ''); $set('dateFilter', ''); $set('paymentTypeFilter', '')" class="btn btn-outline-secondary w-100" title="Clear Filters">
                        <i class="bi bi-x-lg me-1"></i> Clear
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%);">
                <div class="card-body text-center py-3 text-white">
                    <h4 class="fw-bold mb-0">Rs. {{ number_format($summarySales, 2) }}</h4>
                    <small class="opacity-75">
                        @if($dateFilter)
                            Sales Total for {{ \Carbon\Carbon::parse($dateFilter)->format('d M, Y') }}
                        @else
                            Total Sales
                        @endif
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-success mb-0">{{ $summaryCount }}</h4>
                    <small class="text-muted">
                        @if($dateFilter)
                            Transactions on {{ \Carbon\Carbon::parse($dateFilter)->format('d M, Y') }}
                        @else
                            Total Transactions
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Sales Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Invoice #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $sale->invoice_number }}</span>
                            </td>
                            <td>
                                @if($sale->walking_customer_name)
                                    {{ $sale->walking_customer_name }}
                                @elseif($sale->customer && $sale->customer->name !== 'Walking Customer')
                                    {{ $sale->customer->name }}
                                @else
                                    <span class="text-muted">Walk-in</span>
                                @endif
                            </td>
                            <td class="fw-semibold">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-success">Rs. {{ number_format($sale->payments->sum('amount') ?? 0, 2) }}</td>
                            <td class="text-danger">
                                @if(($sale->due_amount ?? 0) > 0)
                                    Rs. {{ number_format($sale->due_amount, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @php
                                    $paymentMethods = $sale->payments->pluck('payment_method')->unique();
                                    $displayType = 'credit';
                                    
                                    if ($paymentMethods->count() > 1) {
                                        $displayType = 'multiple';
                                    } elseif ($paymentMethods->count() === 1) {
                                        $displayType = $paymentMethods->first();
                                    } elseif ($sale->due_amount > 0) {
                                        $displayType = 'credit';
                                    }

                                    $typeClass = match($displayType) {
                                        'cash' => 'text-success',
                                        'bank_transfer' => 'text-primary',
                                        'online', 'card' => 'text-info',
                                        'cheque' => 'text-warning',
                                        'multiple' => 'text-secondary',
                                        'credit' => 'text-danger',
                                        default => 'text-muted'
                                    };

                                    $typeLabel = match($displayType) {
                                        'cash' => 'CASH',
                                        'bank_transfer' => 'BANK TRANSFER',
                                        'online', 'card' => 'CARD PAYMENT',
                                        default => strtoupper(str_replace('_', ' ', $displayType))
                                    };
                                @endphp
                                <small class="fw-bold {{ $typeClass }}">{{ $typeLabel }}</small>
                            </td>
                            <td>
                                @if($sale->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($sale->payment_status === 'partial')
                                    <span class="badge bg-warning text-dark">Partial</span>
                                @else
                                    <span class="badge bg-danger">Pending</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $sale->created_at->format('d M, Y') }}</td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button wire:click="printInvoice({{ $sale->id }})" class="btn btn-sm btn-outline-secondary" title="Print Invoice">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    <button wire:click="openDummyBillModal({{ $sale->id }})" class="btn btn-sm btn-outline-info" title="Create Dummy Bill">
                                        <i class="bi bi-receipt-cutoff"></i>
                                    </button>
                                    <button wire:click="viewDetails({{ $sale->id }})" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button wire:click="editSale({{ $sale->id }})" class="btn btn-sm btn-outline-warning" title="Edit Sale">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No sales found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    {{-- View Details Modal --}}
    @if($showDetailsModal && $selectedSale)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-receipt me-2"></i>Sale Details - {{ $selectedSale->invoice_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDetailsModal"></button>
                </div>
                <div class="modal-body">
                    {{-- Sale Info --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Customer:</strong> 
                                @if($selectedSale->walking_customer_name)
                                    {{ $selectedSale->walking_customer_name }}
                                    @if($selectedSale->walking_customer_phone) ({{ $selectedSale->walking_customer_phone }}) @endif
                                @elseif($selectedSale->customer && $selectedSale->customer->name !== 'Walking Customer')
                                    {{ $selectedSale->customer->name }}
                                    @if($selectedSale->customer->phone) ({{ $selectedSale->customer->phone }}) @endif
                                @else
                                    Walking Customer
                                @endif
                            </p>
                            <p class="mb-1"><strong>Date:</strong> {{ $selectedSale->created_at->format('M d, Y h:i A') }}</p>
                            <p class="mb-0"><strong>Payment Status:</strong>
                                @if($selectedSale->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($selectedSale->payment_status === 'partial')
                                    <span class="badge bg-warning text-dark">Partial</span>
                                @else
                                    <span class="badge bg-danger">Pending</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Invoice:</strong> {{ $selectedSale->invoice_number }}</p>
                            <p class="mb-1"><strong>Payment Type:</strong> {{ ucfirst(str_replace('_', ' ', $selectedSale->payment_type ?? 'N/A')) }}</p>
                            @if(($selectedSale->due_amount ?? 0) > 0)
                            <p class="mb-0 text-danger"><strong>Due Amount:</strong> Rs. {{ number_format($selectedSale->due_amount, 2) }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Items --}}
                    <h6 class="fw-bold mb-2">Sale Items</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedSale->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end text-danger">
                                        @if(($item->discount_per_unit ?? 0) > 0)
                                            Rs. {{ number_format($item->discount_per_unit, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">Rs. {{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end">Rs. {{ number_format($selectedSale->subtotal, 2) }}</td>
                                </tr>
                                @if(($selectedSale->discount_amount ?? 0) > 0)
                                <tr>
                                    <td colspan="4" class="text-end text-danger">Discount:</td>
                                    <td class="text-end text-danger">- Rs. {{ number_format($selectedSale->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                    <td class="text-end fw-bold">Rs. {{ number_format($selectedSale->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Returns Section --}}
                    @if($saleReturns && count($saleReturns) > 0)
                    <h6 class="fw-bold mb-2 text-danger"><i class="bi bi-arrow-return-left me-2"></i>Returns</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered border-danger">
                            <thead class="table-danger">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Returned Qty</th>
                                    <th class="text-end">Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($saleReturns as $return)
                                <tr>
                                    <td>{{ $return->product->name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $return->return_quantity }}</td>
                                    <td class="text-end">Rs. {{ number_format($return->total_amount, 2) }}</td>
                                    <td class="text-muted">{{ $return->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    {{-- Payments --}}
                    @if($selectedSale->payments && count($selectedSale->payments) > 0)
                    <h6 class="fw-bold mb-2 text-success"><i class="bi bi-cash-coin me-2"></i>Payment Records</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-success">
                                <tr>
                                    <th>Method</th>
                                    <th class="text-end">Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedSale->payments as $payment)
                                <tr>
                                    <td>
                                        @if($payment->payment_method === 'card' || $payment->payment_method === 'online')
                                            Card Payment
                                        @else
                                            {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                                        @endif
                                    </td>
                                    <td class="text-end">Rs. {{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-muted">{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button wire:click="printInvoice({{ $selectedSale->id }})" class="btn btn-outline-secondary">
                        <i class="bi bi-printer me-2"></i>Print Invoice
                    </button>
                    <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Dummy Bill Generator Modal --}}
    @if($showDummyModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 1055;">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); border-bottom: 2px solid #D4AF37;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.3);">
                            <i class="bi bi-receipt-cutoff fs-4" style="color: #D4AF37;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0">Dummy Bill Generator</h5>
                            <small class="text-white-50">
                                Invoice #<strong>{{ $dummyInvoiceNumber }}</strong> &bull; 
                                Customer: <strong>{{ $dummyCustomerName ?: 'Walking Customer' }}</strong> &bull; 
                                Original Total: <strong class="text-warning">Rs. {{ number_format($dummyOriginalTotal, 2) }}</strong>
                            </small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDummyModal"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    {{-- Target Total Controller Banner --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #ffffff; border-left: 5px solid #D4AF37 !important;">
                        <div class="card-body p-3">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-5 col-md-6">
                                    <label class="form-label text-uppercase fw-bold text-muted small mb-1">
                                        <i class="bi bi-bullseye text-primary me-1"></i> How much total are you looking for?
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light fw-bold text-muted">Rs.</span>
                                        <input type="number" step="any"
                                               class="form-control fw-bold text-dark fs-4"
                                               wire:model.live.debounce.300ms="dummyTargetTotal"
                                               placeholder="e.g. 200000">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label text-uppercase fw-bold text-muted small mb-1">Quick Presets</label>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold rounded-pill" wire:click="$set('dummyTargetTotal', 200000); $wire.applyTargetTotal()">200,000</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold rounded-pill" wire:click="$set('dummyTargetTotal', 150000); $wire.applyTargetTotal()">150,000</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold rounded-pill" wire:click="$set('dummyTargetTotal', 100000); $wire.applyTargetTotal()">100,000</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold rounded-pill" wire:click="$set('dummyTargetTotal', Math.round({{ $dummyOriginalTotal }} * 0.7)); $wire.applyTargetTotal()">-30%</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold rounded-pill" wire:click="$set('dummyTargetTotal', Math.round({{ $dummyOriginalTotal }} * 0.5)); $wire.applyTargetTotal()">-50%</button>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-12 text-lg-end">
                                    <button type="button" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" wire:click="applyTargetTotal">
                                        <i class="bi bi-calculator me-1"></i> Auto-Reduce Prices
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Edit View Bill --}}
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-3">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-pencil-square text-warning me-1"></i> Edit View Bill (Recheck &amp; Adjust Prices)
                                </h6>
                                <small class="text-muted">All original products are kept. Prices are reduced proportionally so the bill equals your target.</small>
                            </div>
                            <div>
                                <span class="badge {{ abs($this->calculatedDummyTotal - (float)$dummyTargetTotal) < 0.01 ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 fs-6">
                                    Total: Rs. {{ number_format($this->calculatedDummyTotal, 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase text-muted" style="font-size: 11px;">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th>Product Name &amp; Code</th>
                                        <th style="width: 90px;" class="text-center">Qty</th>
                                        <th style="width: 140px;" class="text-end">Original Price</th>
                                        <th style="width: 190px;" class="text-end">New Unit Price (Editable)</th>
                                        <th style="width: 150px;" class="text-end">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dummyItems as $idx => $dItem)
                                    <tr>
                                        <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $dItem['product_name'] }}</div>
                                            <div class="small text-muted">
                                                @if(!empty($dItem['product_code']))
                                                    <span class="badge bg-light text-dark border">{{ $dItem['product_code'] }}</span>
                                                @endif
                                                @if(!empty($dItem['product_model']))
                                                    &bull; {{ $dItem['product_model'] }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold">{{ $dItem['quantity'] }}</td>
                                        <td class="text-end text-muted">
                                            <del>Rs. {{ number_format($dItem['original_unit_price'], 2) }}</del>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-muted">Rs.</span>
                                                <input type="number" step="any"
                                                       class="form-control form-control-sm text-end fw-bold text-primary"
                                                       wire:model.live.debounce.300ms="dummyItems.{{ $idx }}.unit_price"
                                                       wire:change="recalculateItemTotal({{ $idx }})">
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-6">
                                            Rs. {{ number_format($dItem['total'], 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end text-uppercase fw-bold">Dummy Grand Total:</th>
                                        <th class="text-end fs-5 fw-bold text-primary">
                                            Rs. {{ number_format($this->calculatedDummyTotal, 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Status message / match alert --}}
                    @if(abs($this->calculatedDummyTotal - (float)$dummyTargetTotal) > 0.01)
                    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between py-2 px-3 mb-0 rounded-3 gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                            <div>
                                Line items sum is <strong>Rs. {{ number_format($this->calculatedDummyTotal, 2) }}</strong>, but target is <strong>Rs. {{ number_format((float)$dummyTargetTotal, 2) }}</strong> (Difference: Rs. {{ number_format($dummyTargetTotal - $this->calculatedDummyTotal, 2) }}).
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-warning fw-bold text-dark shadow-sm" wire:click="applyTargetTotal">
                            <i class="bi bi-magic me-1"></i> Auto-Match Target Exactly
                        </button>
                    </div>
                    @else
                    <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-0 rounded-3">
                        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                        <div>
                            <strong>Verified Exact Match!</strong> All product prices are reduced properly. Total equals exactly <strong>Rs. {{ number_format($this->calculatedDummyTotal, 2) }}</strong>. You can now confirm and print.
                        </div>
                    </div>
                    @endif
                </div>

                <div class="modal-footer bg-white px-4 py-3 d-flex flex-wrap justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-bold" wire:click="closeDummyModal">
                        Cancel
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-dark px-4 fw-bold d-flex align-items-center gap-2 shadow-sm" wire:click="confirmAndPrintDummyThermal">
                            <i class="bi bi-printer-fill text-warning"></i> Confirm &amp; Print Thermal Receipt
                        </button>
                        <button type="button" class="btn btn-primary px-4 fw-bold d-flex align-items-center gap-2 shadow-sm" wire:click="confirmAndPrintDummyA4">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Confirm &amp; Print A4 Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

