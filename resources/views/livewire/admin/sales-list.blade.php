<div class="container-fluid py-3">
    {{-- ==================== PAGE HEADER ==================== --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-truck text-success me-2"></i> Delivery Sales Management
            </h3>
            <p class="text-muted mb-0">View and manage delivery sales</p>
        </div>
        <div>
            <a href="{{ route('admin.sales-system') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> New Delivery Sale
            </a>
        </div>
    </div>

    {{-- ==================== STATISTICS CARDS ==================== --}}
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Admin Sales</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['total_sales'] }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-cart-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">Rs.{{ number_format($stats['total_amount'], 2) }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-currency-dollar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Payments</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">Rs.{{ number_format($stats['pending_payments'], 2) }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-clock-history fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-info border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Today's Sales</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['today_sales'] }}</div>
                        </div>
                        <div class="col-auto"><i class="bi bi-calendar-day fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== FILTERS ==================== --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search (Barcode / Invoice / Customer)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" class="form-control"
                            placeholder="Scan barcode or search..."
                            wire:model.live="search">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Payment Status</label>
                    <select class="form-select" wire:model.live="paymentStatusFilter">
                        <option value="all">All</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Delivery Status</label>
                    <select class="form-select" wire:model.live="deliveryStatusFilter">
                        <option value="all">All</option>
                        <option value="Processing">Processing</option>
                        <option value="Packed">Packed</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" wire:model.live="dateFilter">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold invisible">Actions</label>
                    <button class="btn btn-outline-secondary w-100"
                        wire:click="$set('dateFilter', ''); $set('deliveryStatusFilter', 'all'); $set('paymentStatusFilter', 'all'); $set('search', '')">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset All
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== SALES TABLE ==================== --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-list-ul text-primary me-2"></i> Delivery Sales List
                </h5>
                <span class="badge bg-primary">{{ $sales->total() }} records</span>
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
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Invoice</th>
                            <th>Delivery Barcode</th>
                            <th>Customer Details</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Amount</th>
                            <th class="text-center">Delivery</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Delivery Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr wire:key="sale-{{ $sale->id }}">
                            <td class="ps-4" wire:click="viewSale({{ $sale->id }})" style="cursor:pointer">
                                <div class="fw-bold text-primary">{{ $sale->invoice_number }}</div>
                                <small class="text-muted">#{{ $sale->sale_id }}</small>
                            </td>
                            <td wire:click="viewSale({{ $sale->id }})" style="cursor:pointer">
                                @if($sale->deliverySale)
                                <span class="badge text-dark">{{ $sale->deliverySale->delivery_barcode }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td wire:click="viewSale({{ $sale->id }})" style="cursor:pointer">
                                @if($sale->deliverySale && $sale->deliverySale->customer_details)
                                <div class="text-truncate" style="max-width: 180px;" title="{{ $sale->deliverySale->customer_details }}">
                                    {{ $sale->deliverySale->customer_details }}
                                </div>
                                @elseif($sale->customer)
                                <div class="fw-medium">{{ $sale->customer->name }}</div>
                                <small class="text-muted">{{ $sale->customer->phone }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center" wire:click="viewSale({{ $sale->id }})" style="cursor:pointer">
                                {{ $sale->created_at->format('M d, Y') }}
                            </td>
                            <td class="text-center fw-bold" wire:click="viewSale({{ $sale->id }})" style="cursor:pointer">
                                Rs.{{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td class="text-center" wire:click="viewSale({{ $sale->id }})" style="cursor:pointer">
                                @if($sale->deliverySale)
                                <span class="badge bg-secondary">{{ $sale->deliverySale->delivery_method }}</span>
                                @endif
                            </td>
                            <td class="text-center" wire:click="viewSale({{ $sale->id }})" style="cursor:pointer">
                                @if($sale->deliverySale)
                                <span class="badge  text-dark">{{ $sale->deliverySale->payment_method }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($sale->deliverySale)
                                @php
                                    $statusColors = [
                                        'Processing' => 'warning',
                                        'Packed' => 'primary',
                                        'Delivered' => 'success',
                                        'Cancelled' => 'danger',
                                    ];
                                    $color = $statusColors[$sale->deliverySale->status] ?? 'secondary';
                                    $isLocked = in_array($sale->deliverySale->status, ['Delivered', 'Cancelled']);
                                @endphp
                                @if($isLocked)
                                    {{-- Locked status — show static badge with lock icon --}}
                                    <span class="badge bg-{{ $color }} px-3 py-2">
                                        <i class="bi bi-lock-fill me-1"></i> {{ $sale->deliverySale->status }}
                                    </span>
                                @else
                                    {{-- Changeable status — show dropdown --}}
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-{{ $color }} dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            {{ $sale->deliverySale->status }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><button class="dropdown-item" wire:click="updateDeliveryStatus({{ $sale->id }}, 'Processing')"><i class="bi bi-clock text-warning me-2"></i>Processing</button></li>
                                            <li><button class="dropdown-item" wire:click="updateDeliveryStatus({{ $sale->id }}, 'Packed')"><i class="bi bi-truck text-primary me-2"></i>Packed</button></li>
                                            <li><button class="dropdown-item" wire:click="updateDeliveryStatus({{ $sale->id }}, 'Delivered')"><i class="bi bi-check-circle text-success me-2"></i>Delivered</button></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><button class="dropdown-item" wire:click="updateDeliveryStatus({{ $sale->id }}, 'Cancelled')"><i class="bi bi-x-circle text-danger me-2"></i>Cancelled</button></li>
                                        </ul>
                                    </div>
                                @endif
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-gear-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button class="dropdown-item" wire:click="viewSale({{ $sale->id }})"><i class="bi bi-eye text-primary me-2"></i>View</button></li>
                                        @if(!$sale->deliverySale || !in_array($sale->deliverySale->status, ['Delivered', 'Cancelled']))
                                        <li><button class="dropdown-item" wire:click="editSale({{ $sale->id }})"><i class="bi bi-pencil-square text-info me-2"></i>Edit</button></li>
                                        <li><hr class="dropdown-divider"></li>
                                        @endif
                                        <li><button class="dropdown-item" wire:click="printInvoice({{ $sale->id }})"><i class="bi bi-printer text-success me-2"></i>Print</button></li>
                                        <li><button class="dropdown-item" wire:click="downloadInvoice({{ $sale->id }})"><i class="bi bi-download text-success me-2"></i>Download PDF</button></li>
                                        @if(!$sale->deliverySale || !in_array($sale->deliverySale->status, ['Delivered', 'Cancelled']))
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button class="dropdown-item" wire:click="deleteSale({{ $sale->id }})"><i class="bi bi-trash text-danger me-2"></i>Delete</button></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                                No delivery sales found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($sales->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $sales->links('livewire.custom-pagination') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ==================== VIEW SALE MODAL (Updated to match POS sales) ==================== --}}
    <div wire:ignore.self class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="printableInvoice">
                {{-- Screen Only Header (visible on screen, hidden on print) --}}
                <div class="screen-only-header p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        {{-- Left: Logo --}}
                        <div style="flex: 0 0 150px;">
                            <img src="{{ asset('images/jg.png') }}" alt="Logo" class="img-fluid" style="max-height:80px;">
                        </div>

                        {{-- Center: Company Name --}}
                        <div class="text-center" style="flex: 1;">
                            <h2 class="mb-0 fw-bold" style="font-size: 2rem; letter-spacing: 2px;">{{ config('shop.name', 'JAFFNA GOLD COVERING') }}</h2>
                            <div class="fw-bold" style="font-size: 1.15rem; color: #111; letter-spacing: 1px; text-transform: uppercase; margin-top: 2px;">{{ config('shop.branch', 'VAVUNIYA BRANCH') }}</div>
                            <p class="mb-0 text-muted small">{{ config('shop.tagline', 'Handcrafted 22K Gold & 925 Sterling Silver Jewelry') }}</p>
                        </div>

                        {{-- Right:  & Invoice --}}
                        <div class="text-end" style="flex: 0 0 150px;">
                            <h5 class="mb-0 fw-bold"></h5>
                            <h6 class="mb-0 text-muted">INVOICE</h6>
                        </div>
                    </div>
                    <hr class="my-2" style="border-top: 2px solid #000;">
                </div>

                @if($selectedSale)
                <div class="modal-body">
                    {{-- ==================== CUSTOMER + INVOICE INFO ==================== --}}
                    <div class="row mb-3">
                        <div class="col-6">
                        
                            @if($selectedSale->deliverySale && $selectedSale->deliverySale->customer_details)
                            
                            <strong>Delivery Customer Info:</strong><br>
                            <span style="white-space: pre-wrap;">{{ $selectedSale->deliverySale->customer_details }}</span>
                            @endif
                            
                            <br>
                        
                            <strong>Created By :</strong>
                            <span style="white-space: pre-wrap;">{{ $selectedSale->user->name ?? 'System' }}</span>
                        </div>
                        <div class="col-6 text-end">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Branch</strong></td>
                                    <td>{{ config('shop.branch', 'Vavuniya Branch') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Invoice #</strong></td>
                                    <td>{{ $selectedSale->invoice_number }}</td>
                                </tr>
                                
                                <tr>
                                    <td><strong>Date</strong></td>
                                    <td>{{ $selectedSale->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                @if($selectedSale->deliverySale)
                                <tr>
                                    <td><strong>Barcode</strong></td>
                                    <td><span class="badge bg-dark font-monospace">{{ $selectedSale->deliverySale->delivery_barcode }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Delivery</strong></td>
                                    <td><span class="badge bg-secondary">{{ $selectedSale->deliverySale->delivery_method }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Payment</strong></td>
                                    <td><span class="badge bg-info text-dark">{{ $selectedSale->deliverySale->payment_method }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>
                                        @php
                                            $sc = ['Processing'=>'warning','Shipped'=>'primary','Delivered'=>'success','Cancelled'=>'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $sc[$selectedSale->deliverySale->status] ?? 'secondary' }}">{{ $selectedSale->deliverySale->status }}</span>
                                    </td>
                                </tr>
                                @else
                                <tr>
                                    <td><strong>Sale Type</strong></td>
                                    <td><span class="badge bg-warning">{{ strtoupper($selectedSale->sale_type) }}</span></td>
                                </tr>
                                @endif
                                
                            </table>
                        </div>
                    </div>

                    {{-- ==================== ITEMS TABLE ==================== --}}
                    <div class="table-responsive mb-3" style="min-height: 10px; overflow-x: visible !important;">
                        <table class="table table-bordered table-sm" style="min-width: 100%; width: 100%;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 25%;">Product</th>
                                    
                                    <th class="text-center" style="width: 12%;">Quantity</th>
                                    <th class="text-end" style="width: 15%;">Unit Price</th>
                                    <th class="text-end" style="width: 15%;">Discount</th>
                                    <th class="text-end" style="width: 18%;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedSale->items as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rs.{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($item->discount_per_unit * $item->quantity, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                                @if($selectedSale->items->count() == 0)
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No items found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- ==================== TOTALS (right-aligned) ==================== --}}
                    <div class="row">
                        <div class="col-7"></div>
                        <div class="col-5">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Subtotal</strong></td>
                                    <td class="text-end">Rs.{{ number_format($selectedSale->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Discount</strong></td>
                                    <td class="text-end">- Rs.{{ number_format($selectedSale->discount_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Grand Total</strong></td>
                                    <td class="text-end fw-bold">Rs.{{ number_format($selectedSale->total_amount, 2) }}</td>
                                </tr>
                                @if($selectedSale->due_amount > 0)
                                <tr>
                                    <td><strong class="text-danger">Due Amount</strong></td>
                                    <td class="text-end fw-bold text-danger">Rs.{{ number_format($selectedSale->due_amount, 2) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- ==================== RETURNED ITEMS TABLE (Only show if returns exist) ==================== --}}
                    @if(isset($selectedSale->returns) && count($selectedSale->returns) > 0)
                    <h6 class="text-muted mb-3 mt-4">RETURNED ITEMS</h6>
                    <div class="table-responsive mb-4" style="min-height: 10px;">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Return Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $returnAmount = 0; @endphp
                                @foreach($selectedSale->returns as $rIndex => $return)
                                @php $returnAmount += $return->total_amount; @endphp
                                <tr>
                                    <td>{{ $rIndex + 1 }}</td>
                                    <td>{{ $return->product?->name ?? '-' }}</td>
                                    <td class="text-center">{{ $return->product?->code ?? '-' }}</td>
                                    <td class="text-center">{{ $return->return_quantity }}</td>
                                    <td class="text-end">Rs.{{ number_format($return->selling_price, 2) }}</td>
                                    <td class="text-end">Rs.{{ number_format($return->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Return Amount:</strong></td>
                                    <td class="text-end">- Rs.@php echo number_format($returnAmount, 2); @endphp</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Net Amount:</strong></td>
                                    <td class="text-end fw-bold">
                                        Rs.@php echo number_format(($selectedSale->subtotal - $selectedSale->discount_amount) - $returnAmount, 2); @endphp
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif

                    {{-- ==================== GRAND TOTAL & DUE AMOUNT ==================== --}}
                    @if(isset($selectedSale->returns) && count($selectedSale->returns) > 0)
                    <div class="row">
                        <div class="col-7"></div>
                        <div class="col-5">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Grand Total</strong></td>
                                    <td class="text-end">Rs.{{ number_format($selectedSale->total_amount, 2) }}</td>
                                </tr>
                                
                                <tr>
                                    <td><strong>Net Amount</strong></td>
                                    <td class="text-end fw-bold">
                                        Rs.@php
                                        $returnAmount = 0;
                                        foreach($selectedSale->returns as $return) {
                                        $returnAmount += $return->total_amount;
                                        }
                                        echo number_format(($selectedSale->subtotal - $selectedSale->discount_amount) - $returnAmount, 2);
                                        @endphp
                                    </td>
                                </tr>
                               
                                <tr>
                                    <td><strong>Due Amount</strong></td>
                                    <td class="text-end">Rs.{{ number_format($selectedSale->due_amount, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                     @endif

                    @if($selectedSale->notes)
                    <h6 class="text-muted mb-2">NOTES</h6>
                    <div class="card bg-light">
                        <div class="card-body">
                            <p class="mb-0">{{ $selectedSale->notes }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Footer Note --}}
                    <div class="invoice-footer mt-4">
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <p class=""><strong>.............................</strong></p>
                                <p class="mb-2"><strong>Checked By</strong></p>
                                
                            </div>
                            <div class="col-4">
                                <p class=""><strong>.............................</strong></p>
                                <p class="mb-2"><strong>Authorized Officer</strong></p>
                                
                            </div>
                            <div class="col-4">
                                <p class=""><strong>.............................</strong></p>
                                <p class="mb-2"><strong>Customer Stamp</strong></p>
                                
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <p class="text-center mb-0"><strong>BRANCH :</strong> {{ strtoupper(config('shop.branch', 'VAVUNIYA BRANCH')) }} | <strong>ADDRESS :</strong> {{ config('shop.address', '2nd Cross Street, Vavuniya') }}</p>
                            <p class="text-center mb-0"><strong>TEL :</strong> {{ config('shop.phone', '0761919650') }}, <strong>HOTLINE :</strong> {{ config('shop.hotline', '021 222 85 89') }}, <strong>EMAIL :</strong> {{ config('shop.email', 'JaffnaGoldlanka@gmail.com') }}</p>
                        </div>
                    </div>
                    @endif
                    {{-- ==================== FOOTER BUTTONS ==================== --}}
                    <div class="modal-footer bg-light justify-content-between">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">
                            <i class="bi bi-x-circle me-1"></i> Close
                        </button>
                        @if($selectedSale)
                        <div>
                            <button type="button" class="btn btn-success me-2" wire:click="downloadInvoice({{ $selectedSale->id }})">
                                <i class="bi bi-download me-1"></i> Download PDF
                            </button>
                            <button type="button" class="btn btn-outline-primary" wire:click="printInvoice({{ $selectedSale->id }})" wire:loading.attr="disabled" wire:target="printInvoice({{ $selectedSale->id }})">
                                <span wire:loading wire:target="printInvoice({{ $selectedSale->id }})">
                                    <i class="spinner-border spinner-border-sm me-1"></i> Printing...
                                </span>
                                <span wire:loading.remove wire:target="printInvoice({{ $selectedSale->id }})">
                                    <i class="bi bi-printer me-1"></i> Print
                                </span>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== EDIT SALE MODAL ==================== --}}
        <div wire:ignore.self class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil-square me-2"></i> Edit Delivery Sale
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            {{-- Delivery Status --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Delivery Status</label>
                                <select class="form-select" wire:model.live="editDeliveryStatus">
                                    <option value="Processing">Processing</option>
                                    <option value="Packed">Packed</option>
                                    <option value="Delivered">Delivered</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>

                            {{-- Delivery Charge --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Delivery Charge</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" class="form-control" wire:model.live="editDeliveryCharge" min="0" step="0.01">
                                </div>
                            </div>

                            {{-- Live Sale Total (computed from items + delivery charge) --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Sale Total</label>
                                <input type="text" class="form-control fw-bold text-primary" value="Rs. {{ number_format(collect($editSaleItems)->sum('total') + floatval($editDeliveryCharge), 2) }}" readonly>
                            </div>

                            {{-- Customer Details --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Customer Details</label>
                                <textarea class="form-control" wire:model="editCustomerDetails" rows="2" placeholder="Customer name, address, phone..."></textarea>
                            </div>

                            {{-- ===== Product Search & Add ===== --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-plus-circle me-1"></i> Add Product
                                </label>
                                <div class="position-relative">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control"
                                            wire:model.live.debounce.300ms="editProductSearch"
                                            placeholder="Search by product name, code, or barcode..."
                                            autocomplete="off">
                                    </div>

                                    {{-- Search Results Dropdown --}}
                                    @if(count($editProductResults) > 0)
                                    <div class="position-absolute w-100 bg-white border rounded-bottom shadow-lg" style="z-index: 1050; max-height: 250px; overflow-y: auto;">
                                        @foreach($editProductResults as $result)
                                        <button type="button"
                                            class="d-flex align-items-center w-100 border-0 bg-white px-3 py-2 text-start product-search-item"
                                            wire:click="addEditProduct({{ $result['id'] }})"
                                            style="cursor: pointer;"
                                            onmouseover="this.style.backgroundColor='#f0f4ff'" onmouseout="this.style.backgroundColor='#fff'">
                                            <div class="me-3">
                                                <i class="bi bi-box-seam text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $result['name'] }}</div>
                                                <small class="text-muted">Code: {{ $result['code'] }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="fw-bold text-success">Rs. {{ number_format($result['selling_price'], 2) }}</span>
                                                @if($result['discount_price'] > 0)
                                                <br><small class="text-danger">Disc: Rs. {{ number_format($result['selling_price'] - $result['discount_price'], 2) }}</small>
                                                @endif
                                            </div>
                                        </button>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- ===== Product Items Table (Editable) ===== --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-2">
                                    <i class="bi bi-box-seam me-1"></i> Product Items
                                    <span class="badge bg-primary ms-1">{{ count($editSaleItems) }}</span>
                                </label>
                                <div class="table-responsive border rounded">
                                    <table class="table table-hover table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3" style="width: 5%;">#</th>
                                                <th style="width: 28%;">Product</th>
                                                <th class="text-center" style="width: 12%;">Qty</th>
                                                <th class="text-center" style="width: 16%;">Unit Price</th>
                                                <th class="text-end" style="width: 13%;">Discount</th>
                                                <th class="text-end" style="width: 16%;">Total</th>
                                                <th class="text-center" style="width: 10%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($editSaleItems as $idx => $item)
                                            <tr wire:key="edit-item-{{ $idx }}">
                                                <td class="ps-3 text-muted">{{ $idx + 1 }}</td>
                                                <td>
                                                    <div class="fw-semibold small">{{ $item['product_name'] }}</div>
                                                    <small class="text-muted">{{ $item['product_code'] }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number"
                                                        class="form-control form-control-sm text-center"
                                                        style="width: 70px; margin: 0 auto;"
                                                        value="{{ $item['quantity'] }}"
                                                        min="1"
                                                        wire:change="updateEditItemQty({{ $idx }}, $event.target.value)">
                                                </td>
                                                <td class="text-center">
                                                    <input type="number"
                                                        class="form-control form-control-sm text-end"
                                                        style="width: 110px; margin: 0 auto;"
                                                        value="{{ $item['unit_price'] }}"
                                                        min="0"
                                                        step="0.01"
                                                        wire:change="updateEditItemPrice({{ $idx }}, $event.target.value)">
                                                </td>
                                                <td class="text-end text-danger small">
                                                    - Rs. {{ number_format($item['discount'], 2) }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    Rs. {{ number_format($item['total'], 2) }}
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        wire:click="removeEditItem({{ $idx }})"
                                                        wire:confirm="Remove this product from the sale?"
                                                        title="Remove item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>
                                                    No items. Use search above to add products.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if(count($editSaleItems) > 0)
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="5" class="text-end fw-bold py-2">Subtotal:</td>
                                                <td class="text-end fw-bold py-2">
                                                    Rs. {{ number_format(collect($editSaleItems)->sum(fn($i) => $i['unit_price'] * $i['quantity']), 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="text-end fw-bold text-danger py-2">Total Discount:</td>
                                                <td class="text-end fw-bold text-danger py-2">
                                                    - Rs. {{ number_format(collect($editSaleItems)->sum('discount'), 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr class="table-primary">
                                                <td colspan="5" class="text-end fw-bold py-2 fs-6">Items Total:</td>
                                                <td class="text-end fw-bold py-2 fs-6">
                                                    Rs. {{ number_format(collect($editSaleItems)->sum('total'), 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="text-end fw-bold py-2">Delivery Charge:</td>
                                                <td class="text-end fw-bold py-2">
                                                    + Rs. {{ number_format(floatval($editDeliveryCharge), 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr class="table-success">
                                                <td colspan="5" class="text-end fw-bold py-2 fs-5">Grand Total:</td>
                                                <td class="text-end fw-bold py-2 fs-5 text-success">
                                                    Rs. {{ number_format(collect($editSaleItems)->sum('total') + floatval($editDeliveryCharge), 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="updateSale"
                            wire:loading.attr="disabled" wire:target="updateSale">
                            <span wire:loading wire:target="updateSale">
                                <i class="spinner-border spinner-border-sm me-1"></i> Saving...
                            </span>
                            <span wire:loading.remove wire:target="updateSale">
                                <i class="bi bi-check-circle me-1"></i> Update Sale
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== DELETE CONFIRM MODAL ==================== --}}
        <div wire:ignore.self class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-exclamation-triangle me-2"></i> Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body">
                        @if($selectedSale)
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Warning!</h6>
                            <p class="mb-0">You are about to delete the following sale. This action cannot be undone and will restore product stock.</p>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <p><strong>Invoice:</strong> {{ $selectedSale->invoice_number }}</p>
                                <p><strong>Customer:</strong> {{ $selectedSale->customer->name ?? 'Walk-in Customer' }}</p>
                                <p><strong>Amount:</strong> Rs.{{ number_format($selectedSale->total_amount, 2) }}</p>
                                <p><strong>Date:</strong> {{ $selectedSale->created_at->format('M d, Y') }}</p>
                                <p><strong>Items:</strong> {{ $selectedSale->items->count() }} products</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="confirmDelete">
                            <i class="bi bi-trash me-1"></i> Delete Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TOAST ==================== --}}
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div id="livewire-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Notification</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body"></div>
            </div>
        </div>
    </div>

    {{-- ==================== STYLES ==================== --}}
    @push('styles')
    <style>
        .closebtn {
            top: 3%;
            right: 3%;
            position: absolute;
        }

        .btn-group-sm>.btn {
            padding: 0.25rem 0.5rem;
        }

        .modal-header {
            border-bottom: 1px solid #dee2e6;
            background: linear-gradient(90deg, #000000, #000000);
            color: #fff;
        }

        .badge {
            font-size: 0.75em;
        }

        /* Hover effects */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.025);
        }

        .table td {
            vertical-align: middle;
        }

        /* Ensure table columns are visible in screen view */
        .table .text-end {
            display: table-cell !important;
            visibility: visible !important;
            text-align: right !important;
        }

        /* Print styles */
        @page {
            size: A4;
            margin: 0;
        }

        @media print {

            /* Remove browser header/footer */
            @page {
                margin: 0mm;
            }

            /* Hide everything except the invoice */
            body * {
                visibility: hidden;
            }

            #printableInvoice,
            #printableInvoice * {
                visibility: visible;
            }

            /* Position the invoice */
            #printableInvoice {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 10mm 10mm 20mm 15mm !important;
                background: #fff !important;
                font-size: 10pt !important;
                color: #000 !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }

            /* Reset modal styles for print */
            .modal,
            .modal-dialog,
            .modal-content {
                all: unset !important;
                display: block !important;
                width: 100% !important;
                height: auto !important;
                position: static !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Hide modal chrome */
            .modal-footer,
            .btn,
            .btn-close,
            .closebtn {
                display: none !important;
            }

            /* Header styles - Fixed at top */
            .modal-header {
                border: none !important;
                padding: 0 0 10px 0 !important;
                text-align: center !important;
                margin-bottom: 15px !important;
                background: transparent !important;
                border-bottom: 2px solid #000000 !important;
            }

            .modal-header img {
                max-height: 100px !important;
                margin-bottom: 5px !important;
            }

            .modal-header h4 {
                margin: 5px 0 !important;
                font-size: 1rem !important;
                color: #000 !important;
                font-weight: bold !important;
            }

            .modal-header p {
                margin: 2px 0 !important;
                font-size: 0.8rem !important;
                color: #000 !important;
            }

            /* Body content */
            .modal-body {
                padding: 0 !important;
                margin: 0 !important;
                max-height: none !important;
                overflow: visible !important;
            }

            /* Layout fixes */
            .row {
                display: flex !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
            }

            .row>.col-6 {
                page-break-inside: avoid !important;
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }

            .row>.col-6:first-child {
                text-align: left !important;
            }

            .row>.col-6:last-child {
                text-align: right !important;
            }

            .row>.col-7 {
                display: none !important;
            }

            .row>.col-5 {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }

            /* Table styles */
            .table {
                border-collapse: collapse !important;
                width: 100% !important;
                margin-bottom: 10px !important;
                font-size: 9pt !important;
                table-layout: auto !important;
            }

            .table th,
            .table td {
                border: 1px solid #999 !important;
                padding: 4px 6px !important;
                color: #000 !important;
                background: transparent !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }

            .table-light th,
            .table-light td,
            tfoot.table-light tr,
            tfoot.table-light td {
                background: #e9ecef !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .table-sm {
                font-size: 8pt !important;
            }

            /* Show all table columns */
            .table th,
            .table td {
                display: table-cell !important;
                visibility: visible !important;
                min-width: 30px !important;
            }

            .table-borderless td {
                border: none !important;
                padding: 2px 4px !important;
            }

            .table-borderless strong {
                min-width: 110px !important;
                display: inline-block !important;
            }

            /* Compact spacing */
            h6 {
                color: #000 !important;
                margin: 10px 0 5px 0 !important;
                font-weight: bold !important;
                font-size: 11pt !important;
            }

            /* Badge and color fixes */
            .badge {
                border: 1px solid #000 !important;
                padding: 2px 6px !important;
                border-radius: 3px !important;
                color: #000 !important;
                background: transparent !important;
            }

            .fw-bold,
            strong {
                font-weight: bold !important;
                color: #000 !important;
            }

            .text-danger {
                color: #dc3545 !important;
            }

            .text-success {
                color: #198754 !important;
            }

            .text-muted {
                font-size: 8pt !important;
                color: #666 !important;
            }

            /* Card styles */
            .card {
                border: 1px solid #ddd !important;
                page-break-inside: avoid !important;
                margin-bottom: 10px !important;
            }

            .card-body {
                padding: 8px !important;
            }

            .bg-light {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Remove extra spacing */
            .mb-3,
            .mb-4 {
                margin-bottom: 8px !important;
            }

            .mt-4 {
                margin-top: 15px !important;
            }

            /* Prevent page breaks */
            .table-responsive {
                page-break-inside: avoid !important;
                overflow: visible !important;
            }

            /* Ensure single page */
            html,
            body {
                height: 297mm !important;
                width: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
        }
    </style>
    @endpush

    {{-- ==================== SCRIPTS ==================== --}}
    @push('scripts')
    <script>
        // Print function
        function printInvoice() {
            window.print();
        }

        document.addEventListener('livewire:initialized', () => {
            // Modal management
            Livewire.on('showModal', (modalId) => {
                console.log('Showing modal:', modalId);
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();

                    // Close modal when hidden
                    modalElement.addEventListener('hidden.bs.modal', function() {
                        Livewire.dispatch('closeModals');
                    });
                }
            });

            Livewire.on('hideModal', (modalId) => {
                console.log('Hiding modal:', modalId);
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
            });

            // Toast notifications
            Livewire.on('showToast', (event) => {
                const toastElement = document.getElementById('livewire-toast');
                if (toastElement) {
                    const toastBody = toastElement.querySelector('.toast-body');
                    const toastHeader = toastElement.querySelector('.toast-header');

                    if (toastBody) toastBody.textContent = event.message;
                    if (toastHeader) {
                        // Remove existing color classes
                        toastHeader.className = 'toast-header text-white';
                        // Add new color class
                        toastHeader.classList.add('bg-' + event.type);
                    }

                    const toast = new bootstrap.Toast(toastElement);
                    toast.show();
                }
            });

            // Close modals when escape key is pressed
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    Livewire.dispatch('closeModals');
                }
            });
        });

        // Handle download button state
        document.addEventListener('livewire:request-start', (event) => {
            const buttons = document.querySelectorAll('[wire\\:click*="downloadInvoice"]');
            buttons.forEach(button => {
                button.disabled = true;
                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = 'bi bi-hourglass-split me-1';
                }
            });
        });

        document.addEventListener('livewire:request-finish', (event) => {
            const buttons = document.querySelectorAll('[wire\\:click*="downloadInvoice"]');
            buttons.forEach(button => {
                button.disabled = false;
                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = 'bi bi-download me-1';
                }
            });
        });
    </script>
    @endpush