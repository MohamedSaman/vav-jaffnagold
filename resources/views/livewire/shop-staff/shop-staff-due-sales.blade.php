<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-exclamation-triangle text-danger me-2"></i> Due Sales List
            </h3>
            <p class="text-muted mb-0">Manage customer dues and receive payments</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by invoice or customer...">
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="date" wire:model.live="dateFilter" class="form-control" placeholder="Filter by date">
                </div>
                <div class="col-md-1">
                    @if($search || $dateFilter)
                    <button wire:click="$set('search', ''); $set('dateFilter', '')" class="btn btn-outline-secondary w-100" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                <div class="card-body py-3 text-white d-flex justify-content-between align-items-center px-5">
                    <div>
                        <h6 class="text-white-50 mb-0">Total Outstanding Amount</h6>
                        <h2 class="fw-bold mb-0">Rs. {{ number_format($totalDue, 2) }}</h2>
                    </div>
                    <i class="bi bi-wallet2 fs-1 text-white-50"></i>
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
                            <th>Total Sale</th>
                            <th>Amount Paid</th>
                            <th>Due Amount</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Action</th>
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
                                    @if($sale->walking_customer_phone)<br><small class="text-muted">{{ $sale->walking_customer_phone }}</small>@endif
                                @elseif($sale->customer && $sale->customer->name !== 'Walking Customer')
                                    {{ $sale->customer->name }}
                                    @if($sale->customer->phone)<br><small class="text-muted">{{ $sale->customer->phone }}</small>@endif
                                @else
                                    Walking Customer
                                @endif
                            </td>
                            <td class="fw-semibold">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-success fw-semibold">Rs. {{ number_format($sale->total_amount - $sale->due_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-danger fs-6">Rs. {{ number_format($sale->due_amount, 2) }}</span>
                            </td>
                            <td class="text-muted">{{ $sale->created_at->format('M d, Y') }}<br><small>{{ $sale->created_at->format('h:i A') }}</small></td>
                            <td class="text-end pe-4">
                                <button wire:click="openPaymentModal({{ $sale->id }})" class="btn btn-sm btn-primary">
                                    <i class="bi bi-cash-stack me-1"></i> Receive
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success"></i>
                                No due sales found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sales->hasPages())
                <div class="p-3 border-top">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Payment Modal --}}
    @if($showPaymentModal && $this->selectedSale)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1050;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Receive Payment</h5>
                    <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between text-muted mb-1">
                            <span>Invoice:</span>
                            <span class="text-dark fw-semibold">{{ $this->selectedSale->invoice_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted mb-3">
                            <span>Current Due:</span>
                            <span class="text-danger fw-bold fs-5">Rs. {{ number_format($this->selectedSale->due_amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn flex-fill {{ $paymentMethod === 'cash' ? 'btn-success' : 'btn-outline-secondary' }}" wire:click="$set('paymentMethod', 'cash')">
                                <i class="bi bi-cash-coin me-1"></i> Cash
                            </button>
                            <button type="button" class="btn flex-fill {{ $paymentMethod === 'online' ? 'btn-success' : 'btn-outline-secondary' }}" wire:click="$set('paymentMethod', 'online')">
                                <i class="bi bi-phone me-1"></i> Online
                            </button>
                            <button type="button" class="btn flex-fill {{ $paymentMethod === 'bank_transfer' ? 'btn-success' : 'btn-outline-secondary' }}" wire:click="$set('paymentMethod', 'bank_transfer')">
                                <i class="bi bi-bank me-1"></i> Bank
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount to Receive</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold border-2">Rs.</span>
                            <input type="number" wire:model="paymentAmount" class="form-control border-2 shadow-sm rounded-end-3" step="0.01" max="{{ $this->selectedSale->due_amount }}">
                        </div>
                        @error('paymentAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    @if($paymentMethod === 'bank_transfer' || $paymentMethod === 'online')
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ $paymentMethod === 'online' ? 'Platform' : 'Bank Name' }}</label>
                            <input type="text" wire:model="bankName" class="form-control border-2 shadow-sm rounded-3" placeholder="{{ $paymentMethod === 'online' ? 'e.g. PayHere, FriMi' : 'e.g. BOC, HNB' }}">
                            @error('bankName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reference Number <span class="text-danger">*</span></label>
                            <input type="text" wire:model="bankReference" class="form-control border-2 shadow-sm rounded-3" placeholder="Transaction ID">
                            @error('bankReference') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" wire:click="closePaymentModal">Cancel</button>
                    <button type="button" class="btn btn-success rounded-pill px-4" wire:click="processPayment" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="processPayment">
                            <i class="bi bi-check2-circle me-1"></i> Confirm Payment
                        </span>
                        <span wire:loading wire:target="processPayment">
                            <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
