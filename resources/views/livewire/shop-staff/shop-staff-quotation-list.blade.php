<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-2">
                <i class="bi bi-file-earmark-text text-primary me-2"></i> My Quotations
            </h3>
            <p class="text-muted mb-0">View, create, and manage your quotations</p>
        </div>
        <a href="{{ route('shop-staff.quotation-billing') }}" class="btn btn-primary shadow-sm bg-[#161b97] hover:bg-[#12167d] border-0">
            <i class="bi bi-plus-circle me-2"></i> New Quotation
        </a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search quotation number, customer, or product code...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center gap-2 h-100 justify-content-end">
                        <label class="text-sm text-muted mb-0">Show</label>
                        <select wire:model.live="perPage" class="form-select" style="width: 100px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quotations Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Quotation No</th>
                            <th>Customer</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                            <th>Valid Until</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4" style="width: 240px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $index => $quotation)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $quotations->firstItem() + $index }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-[#161b97]">{{ $quotation->quotation_number }}</span>
                            </td>
                            <td>
                                <strong class="fw-medium text-dark d-block">{{ $quotation->customer_name }}</strong>
                                <span class="text-muted small">{{ $quotation->customer_phone }}</span>
                            </td>
                            <td class="text-end">Rs. {{ number_format($quotation->subtotal, 2) }}</td>
                            <td class="text-end text-danger">-Rs. {{ number_format($quotation->discount_amount, 2) }}</td>
                            <td class="text-end fw-bold text-success">Rs. {{ number_format($quotation->total_amount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M, Y') }}</td>
                            <td class="text-center">
                                @if($quotation->status === 'converted')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2.5 py-1.5 rounded-pill">Converted</span>
                                @elseif($quotation->status === 'draft')
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 px-2.5 py-1.5 rounded-pill">Draft</span>
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-2.5 py-1.5 rounded-pill">{{ ucfirst($quotation->status) }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1.5">
                                    {{-- Convert to sale --}}
                                    @if($quotation->status !== 'converted')
                                    <a href="{{ route('shop-staff.store-billing', ['convert_quotation' => $quotation->id]) }}" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1" title="Convert to Sale">
                                        <i class="bi bi-cart-check"></i>
                                        <span>Convert</span>
                                    </a>
                                    @else
                                    <button class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 disabled" disabled title="Quotation converted to sale">
                                        <i class="bi bi-check2-all"></i>
                                        <span>Sold</span>
                                    </button>
                                    @endif

                                    {{-- Print thermal receipt --}}
                                    <button wire:click="printQuotation({{ $quotation->id }})" class="btn btn-sm btn-outline-secondary" title="Print Quotation">
                                        <i class="bi bi-printer"></i>
                                    </button>

                                    {{-- Download PDF --}}
                                    <button wire:click="downloadQuotation({{ $quotation->id }})" class="btn btn-sm btn-outline-primary" title="Download PDF">
                                        <i class="bi bi-download"></i>
                                    </button>

                                    {{-- Delete --}}
                                    @if($quotation->status !== 'converted')
                                    <button onclick="confirm('Are you sure you want to delete this quotation?') || event.stopImmediatePropagation()" 
                                            wire:click="deleteQuotation({{ $quotation->id }})" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                                No quotations found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($quotations->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-center">
                    {{ $quotations->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
