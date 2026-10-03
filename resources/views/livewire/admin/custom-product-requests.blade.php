<div class="container-fluid py-4">
    <!-- Header Block -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark font-inter">
                <i class="bi bi-gem text-warning me-2"></i> Custom Jewelry Requests
            </h1>
            <p class="text-muted mb-0">Manage customer bespoke orders, configure quotes, track crafting status, and communicate via WhatsApp & SMS.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.custom-products-manage') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <i class="bi bi-sliders2"></i>
                <span>Manage Products & Price Ranges</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b, #0f172a); color: white;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Total Requests</div>
                            <div class="h3 fw-bold mb-0 mt-1">{{ number_format($totalCount) }}</div>
                        </div>
                        <div class="rounded-circle p-2 bg-white bg-opacity-10 text-white">
                            <i class="bi bi-inbox fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #d97706, #b45309); color: white;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">New Inquiries</div>
                            <div class="h3 fw-bold mb-0 mt-1">{{ number_format($newCount) }}</div>
                        </div>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20 text-white">
                            <i class="bi bi-bell fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Quoted</div>
                            <div class="h3 fw-bold mb-0 mt-1">{{ number_format($quotedCount) }}</div>
                        </div>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20 text-white">
                            <i class="bi bi-file-earmark-text fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">In Crafting</div>
                            <div class="h3 fw-bold mb-0 mt-1">{{ number_format($inCraftingCount) }}</div>
                        </div>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20 text-white">
                            <i class="bi bi-hammer fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #059669, #047857); color: white;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Completed</div>
                            <div class="h3 fw-bold mb-0 mt-1">{{ number_format($completedCount) }}</div>
                        </div>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20 text-white">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card border shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Search by Inquiry #, Customer Name, Phone, City, or Item..." wire:model.live.debounce.300ms="search">
                        @if($search)
                            <button class="btn btn-outline-secondary border-start-0" type="button" wire:click="$set('search', '')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-3">
                    <select class="form-select" wire:model.live="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="kodi">Kodi (Chains)</option>
                        <option value="bangle">Bangles (SL Locks / Spring)</option>
                        <option value="ring">Rings (Thakar / Dollar)</option>
                        <option value="bridal_thali">Bridal Thali Sets</option>
                        <option value="necklace">Necklaces & Sets</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-3">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="new">New (Needs Action)</option>
                        <option value="contacted">Contacted</option>
                        <option value="quoted">Quoted</option>
                        <option value="in_crafting">In Crafting (Workshop)</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Table Card -->
    <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 text-dark">
                <i class="bi bi-list-stars text-primary me-2"></i> Custom Requests List
            </h5>
            <div class="text-muted small">
                Showing {{ $inquiries->firstItem() ?? 0 }} - {{ $inquiries->lastItem() ?? 0 }} of {{ $inquiries->total() }} requests
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-muted" style="letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-4">Inquiry Ref / Date</th>
                        <th>Customer</th>
                        <th>Requested Item</th>
                        <th>Pown & Weight</th>
                        <th>Price Estimate / Quote</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inquiry)
                        @php
                            $statusBadgeClass = match($inquiry->status) {
                                'new' => 'bg-warning text-dark',
                                'contacted' => 'bg-info text-dark',
                                'quoted' => 'bg-primary text-white',
                                'in_crafting' => 'bg-purple text-white',
                                'completed' => 'bg-success text-white',
                                'cancelled' => 'bg-danger text-white',
                                default => 'bg-secondary text-white'
                            };
                            $waUrl = $this->getWhatsAppUrl($inquiry);
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1 mb-1">{{ $inquiry->inquiry_number }}</span>
                                <div class="text-muted" style="font-size: 0.78rem;">
                                    <i class="bi bi-calendar2-date me-1"></i> {{ $inquiry->created_at->format('M d, Y') }}
                                    <span class="text-secondary">({{ $inquiry->created_at->diffForHumans() }})</span>
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $inquiry->customer_name }}</div>
                                <div class="text-muted small d-flex align-items-center gap-2 mt-1">
                                    <a href="tel:{{ $inquiry->customer_phone }}" class="text-decoration-none text-muted" title="Call Customer">
                                        <i class="bi bi-telephone text-primary"></i> {{ $inquiry->customer_phone }}
                                    </a>
                                    @if($inquiry->city)
                                        <span class="badge bg-light text-secondary border-0"><i class="bi bi-geo-alt"></i> {{ $inquiry->city }}</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($inquiry->customProduct && $inquiry->customProduct->image)
                                        <img src="{{ asset($inquiry->customProduct->image) }}" class="rounded border" style="width: 42px; height: 42px; object-fit: cover;" alt="">
                                    @else
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 42px; height: 42px;">
                                            <i class="bi bi-gem"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $inquiry->product_name }}</div>
                                        <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">{{ ucfirst($inquiry->category) }}</span>
                                        <span class="badge bg-warning bg-opacity-25 text-dark" style="font-size: 0.7rem;">{{ $inquiry->gold_purity }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold text-dark font-inter">
                                    {{ $inquiry->selected_pown }} Pown
                                </div>
                                <div class="text-muted small">
                                    {{ $inquiry->selected_grams }} Grams (8g/Pown)
                                </div>
                                @if($inquiry->size_specifications)
                                    <div class="text-secondary text-truncate" style="max-width: 170px; font-size: 0.75rem;" title="{{ $inquiry->size_specifications }}">
                                        <i class="bi bi-rulers"></i> {{ $inquiry->size_specifications }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if($inquiry->quoted_price)
                                    <div class="fw-bold text-success font-inter">
                                        LKR {{ number_format($inquiry->quoted_price, 0) }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        Est: LKR {{ number_format($inquiry->estimated_price, 0) }}
                                    </div>
                                @else
                                    <div class="fw-bold text-jg-blue font-inter">
                                        LKR {{ number_format($inquiry->estimated_price, 0) }}
                                    </div>
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">Auto Estimate</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $statusBadgeClass }} text-uppercase px-2 py-1 mb-1" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $inquiry->status) }}
                                </span>
                            </td>

                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" wire:click="openDetail({{ $inquiry->id }})">
                                        <i class="bi bi-eye"></i> Details
                                    </button>
                                    <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm btn-outline-success" title="Chat on WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-3 text-secondary"></i>
                                <div class="h5 fw-bold text-dark">No custom product inquiries found</div>
                                <p class="mb-0 text-muted">Customer requests submitted from the custom jewelry storefront will show up here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inquiries->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $inquiries->links() }}
            </div>
        @endif
    </div>

    <!-- Inquiry Details & Action Modal -->
    @if($showDetailModal && $selectedInquiry)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.6); z-index: 1055;" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <!-- Modal Header -->
                    <div class="modal-header bg-dark text-white py-3 px-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold mb-0 text-white font-inter">
                                    Inquiry #{{ $selectedInquiry->inquiry_number }}
                                </h5>
                                <span class="badge bg-warning text-dark">{{ ucfirst(str_replace('_', ' ', $selectedInquiry->status)) }}</span>
                            </div>
                            <small class="text-white-50">Received {{ $selectedInquiry->created_at->format('M d, Y h:i A') }}</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDetail" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Left: Customer & Requested Item Details -->
                            <div class="col-md-6 border-end">
                                <h6 class="text-uppercase text-muted fw-bold small mb-3" style="letter-spacing: 0.05em;">
                                    <i class="bi bi-person-circle text-primary me-1"></i> Customer Information
                                </h6>
                                <div class="bg-light p-3 rounded-3 mb-4">
                                    <div class="fw-bold fs-6 text-dark">{{ $selectedInquiry->customer_name }}</div>
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-telephone me-1 text-primary"></i> <strong>Phone:</strong> {{ $selectedInquiry->customer_phone }}
                                    </div>
                                    @if($selectedInquiry->customer_email)
                                        <div class="text-muted small mt-1">
                                            <i class="bi bi-envelope me-1 text-primary"></i> <strong>Email:</strong> {{ $selectedInquiry->customer_email }}
                                        </div>
                                    @endif
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-geo-alt me-1 text-danger"></i> <strong>Location:</strong> {{ $selectedInquiry->city ?? 'Not specified' }}
                                    </div>
                                </div>

                                <h6 class="text-uppercase text-muted fw-bold small mb-3" style="letter-spacing: 0.05em;">
                                    <i class="bi bi-gem text-warning me-1"></i> Jewelry Specifications
                                </h6>
                                <div class="p-3 border rounded-3">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        @if($selectedInquiry->customProduct && $selectedInquiry->customProduct->image)
                                            <img src="{{ asset($selectedInquiry->customProduct->image) }}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;" alt="">
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark">{{ $selectedInquiry->product_name }}</div>
                                            <div class="text-muted small">{{ ucfirst($selectedInquiry->category) }} • {{ $selectedInquiry->gold_purity }} Pure Gold</div>
                                        </div>
                                    </div>

                                    <ul class="list-group list-group-flush small">
                                        <li class="list-group-item d-flex justify-content-between px-0">
                                            <span class="text-muted">Selected Weight (Pown):</span>
                                            <strong class="text-dark">{{ $selectedInquiry->selected_pown }} Pown (பவுன்)</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between px-0">
                                            <span class="text-muted">Grams Equivalent (8g/Pown):</span>
                                            <strong class="text-dark">{{ $selectedInquiry->selected_grams }} Grams</strong>
                                        </li>
                                        @if($selectedInquiry->size_specifications)
                                            <li class="list-group-item px-0">
                                                <div class="text-muted mb-1">Custom Sizing / Dimensions:</div>
                                                <div class="bg-light p-2 rounded text-dark fw-semibold">{{ $selectedInquiry->size_specifications }}</div>
                                            </li>
                                        @endif
                                        <li class="list-group-item d-flex justify-content-between px-0">
                                            <span class="text-muted">Web Estimate:</span>
                                            <strong class="text-jg-blue">LKR {{ number_format($selectedInquiry->estimated_price, 0) }}</strong>
                                        </li>
                                    </ul>

                                    @if($selectedInquiry->customer_notes)
                                        <div class="mt-3">
                                            <div class="text-muted small fw-semibold mb-1">Customer Notes:</div>
                                            <div class="bg-light border p-2 rounded small text-dark fst-italic">
                                                "{{ $selectedInquiry->customer_notes }}"
                                            </div>
                                        </div>
                                    @endif

                                    @if($selectedInquiry->reference_image)
                                        <div class="mt-3">
                                            <div class="text-muted small fw-semibold mb-1"><i class="bi bi-image text-primary me-1"></i> Customer Reference Design:</div>
                                            <div class="p-2 border rounded bg-white text-center">
                                                <a href="{{ asset($selectedInquiry->reference_image) }}" target="_blank" title="Click to view full image">
                                                    <img src="{{ asset($selectedInquiry->reference_image) }}" class="rounded img-fluid" style="max-height: 180px; object-fit: contain;" alt="Customer Reference">
                                                </a>
                                                <div class="small text-muted mt-1">
                                                    <i class="bi bi-arrows-fullscreen"></i> Click image to open in full size
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Right: Admin Actions & Workshop Quotation -->
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted fw-bold small mb-3" style="letter-spacing: 0.05em;">
                                    <i class="bi bi-hammer text-primary me-1"></i> Workshop & Quotation Actions
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">Order Status</label>
                                    <select class="form-select" wire:model="updateStatus">
                                        <option value="new">New (Awaiting Review)</option>
                                        <option value="contacted">Contacted Customer</option>
                                        <option value="quoted">Quoted & Awaiting Customer Confirmation</option>
                                        <option value="in_crafting">In Crafting (Goldsmith Workshop)</option>
                                        <option value="completed">Completed & Ready / Delivered</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">Official Quoted Price (LKR)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted fw-bold">LKR</span>
                                        <input type="number" step="100" class="form-control" placeholder="e.g. 1250000" wire:model="quotePrice">
                                    </div>
                                    <small class="text-muted">Web initial estimate was LKR {{ number_format($selectedInquiry->estimated_price, 0) }}.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">Admin & Goldsmith Notes</label>
                                    <textarea class="form-control" rows="3" placeholder="Enter workshop notes, goldsmith assignment, wastage/making charges details..." wire:model="adminNotes"></textarea>
                                </div>

                                <button type="button" class="btn btn-primary w-100 py-2 mb-4 d-flex align-items-center justify-content-center gap-2" wire:click="saveQuoteAndNotes">
                                    <i class="bi bi-check2-circle"></i> Save Quote & Status
                                </button>

                                <hr class="my-3">

                                <h6 class="text-uppercase text-muted fw-bold small mb-3" style="letter-spacing: 0.05em;">
                                    <i class="bi bi-chat-dots text-success me-1"></i> Quick Customer Communication
                                </h6>

                                <div class="d-grid gap-2">
                                    <!-- WhatsApp Direct -->
                                    <a href="{{ $this->getWhatsAppUrl($selectedInquiry) }}" target="_blank" class="btn btn-success d-flex align-items-center justify-content-center gap-2 py-2">
                                        <i class="bi bi-whatsapp fs-5"></i>
                                        <span>Chat via WhatsApp (Prefilled Quote)</span>
                                    </a>

                                    <!-- SMS Trigger -->
                                    <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center gap-2 py-2" wire:click="sendSmsNotification" wire:loading.attr="disabled">
                                        <i class="bi bi-chat-text"></i>
                                        <span wire:loading.remove wire:target="sendSmsNotification">Send SMS Quotation to Customer</span>
                                        <span wire:loading wire:target="sendSmsNotification">Sending SMS...</span>
                                    </button>

                                    <!-- Direct Call -->
                                    <a href="tel:{{ $selectedInquiry->customer_phone }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 py-2">
                                        <i class="bi bi-telephone-outbound"></i>
                                        <span>Call Customer Directly ({{ $selectedInquiry->customer_phone }})</span>
                                    </a>
                                </div>

                                @if($smsSentMessage)
                                    <div class="alert alert-info py-2 px-3 small mt-3 mb-0">
                                        <i class="bi bi-info-circle me-1"></i> {{ $smsSentMessage }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light py-2 px-4" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetail">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
