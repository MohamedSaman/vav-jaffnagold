<div class="container-fluid p-3 fast-product-create-page" id="fast-product-create-container" data-unsaved-count="{{ $unsavedCount ?? 0 }}">
    @push('styles')
    <style>
        .fast-product-create-page .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        }

        /* Consistent heights for metadata row (Row 1) */
        .meta-field {
            height: 38px !important;
            font-size: 0.875rem;
            border-radius: 6px;
        }

        .meta-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            min-height: 20px;
        }

        /* Consistent heights and styling for rapid entry row (Row 2) */
        .rapid-entry-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem;
        }

        .rapid-label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            min-height: 22px;
        }

        .rapid-field {
            height: 48px !important;
            font-size: 1.15rem !important;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .rapid-input-group .input-group-text {
            height: 48px !important;
            font-size: 1rem !important;
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            font-weight: 700;
            color: #475569;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .rapid-input-group .form-control {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .qr-badge-preview {
            width: 48px;
            height: 48px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
        }

        .qr-badge-preview canvas, .qr-badge-preview img {
            width: 44px !important;
            height: 44px !important;
        }

        .table-row-saved {
            background-color: rgba(16, 185, 129, 0.08) !important;
        }

        .btn-gradient-success {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
        }

        .btn-gradient-success:hover {
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
            color: #ffffff;
        }

        .btn-gradient-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
        }

        .btn-gradient-primary:hover {
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
            color: #ffffff;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
    @endpush

    {{-- Compact Header Bar --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('admin.Productes') }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Back to Product List">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Fast Product Entry & Barcode Print
            </h4>
            <span id="draft-autosave-indicator" class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 d-none align-items-center gap-1 shadow-sm" style="font-size: 0.75rem;">
                <i class="bi bi-shield-check"></i>
                <span id="draft-autosave-text">Auto-saved Stage</span>
            </span>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#excelImportModal">
                <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
            </button>
            <a href="{{ route('admin.purchase-order-list') }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-cart-check me-1"></i> Purchase Orders
            </a>
            <a href="{{ route('admin.Productes') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-list-ul me-1"></i> Product List
            </a>
            <a href="{{ route('admin.barcode-print') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Barcode Print
            </a>
        </div>
    </div>

    {{-- Unsaved Staging Batch Recovery Banner (Survives power cuts & network loss) --}}
    <div id="draft-resume-banner" class="alert alert-warning border border-2 border-warning shadow-sm d-none mb-3 rounded-3" role="alert" style="background-color: #fffbeb;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="badge bg-warning text-dark p-2 rounded-3 fs-4 shadow-sm">
                    <i class="bi bi-lightning-charge-fill text-dark"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <span>Unsaved Staging Batch Found!</span>
                        <span class="badge bg-dark text-white rounded-pill" id="draft-banner-count">0 items</span>
                    </h6>
                    <div class="text-secondary small" id="draft-banner-details">
                        Auto-saved before power/session interrupt. Would you like to resume where you left off?
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 flex-shrink-0">
                <button type="button" class="btn btn-sm btn-primary fw-bold px-3 shadow-sm d-flex align-items-center gap-1" onclick="window.resumeFastProductDraft()">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Resume Previous Batch</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger px-3 d-flex align-items-center gap-1" onclick="window.discardFastProductDraft()">
                    <i class="bi bi-trash"></i>
                    <span>Discard Draft</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Edit Mode Active Banner --}}
    @if($isEditingOrder)
    <div class="alert alert-primary border border-2 border-primary shadow-sm mb-3 rounded-3" role="alert" style="background-color: #f0f7ff;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="badge bg-primary text-white p-2 rounded-3 fs-4 shadow-sm">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h6 class="fw-bold text-dark mb-0">Editing Purchase Order:</h6>
                        <code class="fs-6 fw-bold text-primary bg-white px-2 py-1 rounded border">{{ $purchasingNumber }}</code>
                        <span class="badge {{ $editingOrderStatus === 'complete' ? 'bg-success' : ($editingOrderStatus === 'received' ? 'bg-info text-dark' : 'bg-warning text-dark') }} fs-6">
                            {{ ucfirst($editingOrderStatus ?: 'Draft') }}
                        </span>
                        <span class="badge bg-white text-dark border shadow-sm">
                            {{ count($items) }} Items ({{ array_sum(array_column($items, 'quantity')) }} Units)
                        </span>
                    </div>
                    <div class="text-secondary small">
                        Updating this order will save all item changes and maintain order number <strong class="text-primary">{{ $purchasingNumber }}</strong> in Purchasing & GRN.
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 flex-shrink-0 align-items-center">
                <button type="button" class="btn btn-sm btn-gradient-success fw-bold px-3 shadow-sm d-flex align-items-center gap-1"
                        wire:click="saveBatch" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveBatch">
                        <i class="bi bi-check2-circle"></i> Update Order{{ !empty($purchasingNumber) ? ': ' . $purchasingNumber : '' }}
                    </span>
                    <span wire:loading wire:target="saveBatch">
                        <i class="spinner-border spinner-border-sm me-1"></i> Saving...
                    </span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3 d-flex align-items-center gap-1"
                        wire:click="resetToNewOrder" title="Exit editing and start a new purchase batch">
                    <i class="bi bi-plus-circle"></i>
                    <span>New Order</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Success Message Alert with Quick Navigation Links --}}
    @if(!empty($successMessage))
    <div class="alert alert-success border border-2 border-success shadow-sm rounded-3 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <span class="fw-bold text-dark">{{ $successMessage }}</span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('admin.purchase-order-list') }}" class="btn btn-sm btn-outline-success fw-semibold shadow-sm">
                <i class="bi bi-receipt me-1"></i> View in Purchasing List
            </a>
            <a href="{{ route('admin.grn') }}" class="btn btn-sm btn-outline-success fw-semibold shadow-sm">
                <i class="bi bi-box-arrow-in-down me-1"></i> View in GRN
            </a>
            <button type="button" class="btn-close ms-1" wire:click="$set('successMessage', '')"></button>
        </div>
    </div>
    @endif

    {{-- Streamlined Setup & Rapid Input Card --}}
    <div class="card mb-3">
        <div class="card-body p-3">
            {{-- Row 1: Category, Purchasing Number & Setup Metadata --}}
            <div class="row g-2 align-items-center mb-3">
                {{-- Purchasing Number / PO Code / Batch Name --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6" wire:key="main-purchasing-number-container">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="meta-label mb-0" for="purchasing-number-input">
                            <i class="bi bi-receipt-cutoff text-primary"></i> Purchasing / Batch Code:
                        </label>
                        <button type="button" class="btn btn-xs py-0 px-1 text-muted border-0 bg-transparent" wire:click="generateNewPurchasingNumber" title="Generate New Unique Code">
                            <i class="bi bi-arrow-clockwise"></i> Gen
                        </button>
                    </div>
                    <div class="input-group">
                        <input type="text" id="purchasing-number-input"
                               wire:key="main-purchasing-number-field"
                               class="form-control meta-field fw-bold text-primary border-primary bg-white shadow-sm"
                               wire:model.live.debounce.400ms="purchasingNumber"
                               placeholder="Enter Purchasing / Batch Code"
                               title="Click to enter or edit your Purchasing Code / Batch Name">
                        @if(!empty($purchasingNumber))
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2 meta-field d-flex align-items-center justify-content-center"
                                wire:click="$set('purchasingNumber', '')"
                                onclick="setTimeout(() => document.getElementById('purchasing-number-input')?.focus(), 50)"
                                title="Clear Code to Enter New">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Category Selector --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="meta-label">
                        <i class="bi bi-folder text-primary"></i> Category:
                    </label>
                    <select class="form-select meta-field" wire:model.live="selectedCategoryId">
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Product Name (Auto-fills from Category, editable) --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="meta-label mb-0">
                            <i class="bi bi-tag text-primary"></i> Product Name:
                        </label>
                        <button type="button"
                                class="btn btn-xs py-0 px-2 rounded-pill border {{ $showSizeField ? 'btn-primary text-white' : 'btn-outline-secondary bg-white' }}"
                                style="font-size: 0.7rem; line-height: 1.4;"
                                wire:click="$toggle('showSizeField')"
                                title="Toggle Bangle Size field">
                            <i class="bi bi-rulers me-1"></i> Size: {{ $showSizeField ? 'ON' : 'OFF' }}
                        </button>
                    </div>
                    <input type="text" class="form-control meta-field fw-medium"
                           wire:model.live="selectedItemName" placeholder="Product Name">
                </div>

                {{-- Supplier --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="meta-label">
                        <i class="bi bi-truck text-muted"></i> Supplier:
                    </label>
                    <select class="form-select meta-field" wire:model.live="selectedSupplierId">
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Multipliers: Cost (default 1) & Selling Multipliers (default 0) --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="meta-label">
                        <i class="bi bi-calculator text-primary"></i> Multipliers (Cost/Sell):
                    </label>
                    <div class="input-group">
                        <span class="input-group-text meta-field bg-light text-muted small fw-bold px-1" style="font-size: 0.75rem;" title="System Cost Multiplier">Cost</span>
                        <input type="number" step="any" class="form-control meta-field text-center fw-bold text-dark px-1"
                                wire:model.live.debounce.300ms="costMultiplier" placeholder="1" title="System Cost Multiplier (default: 1)">
                        <span class="input-group-text meta-field bg-light text-muted small fw-bold px-1" style="font-size: 0.75rem;" title="Selling Price Multiplier">Sell</span>
                        <input type="number" step="any" class="form-control meta-field text-center fw-bold text-success px-1"
                                wire:model.live.debounce.300ms="sellingMultiplier" placeholder="0" title="Selling Price Multiplier (default: 0)">
                    </div>
                </div>

                {{-- Purchase Date --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="meta-label">
                        <i class="bi bi-calendar text-muted"></i> Date:
                    </label>
                    <input type="date" class="form-control meta-field" wire:model="purchaseDate">
                </div>
            </div>

            {{-- Row 2: Continuous Keyboard Loop (Cost -> Enter -> Qty -> Enter -> Size -> Enter -> Add & Focus Cost) --}}
            <div class="rapid-entry-box">
                <div class="row g-3 align-items-center">
                    {{-- Cost Input --}}
                    <div class="{{ $showSizeField ? 'col-md-3' : 'col-md-4' }}">
                        <label class="rapid-label">
                            <span>Supplier Cost (Rs.)</span>
                            <span class="badge bg-warning text-dark py-1 px-2" style="font-size: 0.7rem;">Enter ↵</span>
                        </label>
                        <div class="input-group rapid-input-group">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" step="any" class="form-control rapid-field fw-bold text-dark border-primary"
                                   id="quick-cost-input"
                                   wire:model.live.debounce.150ms="quickCost"
                                   placeholder="0.00" autofocus>
                        </div>
                    </div>

                    {{-- Quantity Input --}}
                    <div class="col-md-2">
                        <label class="rapid-label">
                            <span>Quantity</span>
                            <span class="badge bg-info text-dark py-1 px-2" style="font-size: 0.7rem;">Enter ↵</span>
                        </label>
                        <input type="number" min="1" class="form-control rapid-field fw-bold text-center border-primary"
                               id="quick-qty-input"
                               wire:model.live="quickQuantity">
                    </div>

                    @if($showSizeField)
                    {{-- Size Input (Sticky across products until changed) --}}
                    <div class="col-md-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="rapid-label mb-0">
                                <span>Size</span>
                                <span class="badge bg-purple text-dark py-1 px-1" style="font-size: 0.65rem; background-color: #e9d5ff;">Enter ↵</span>
                            </label>
                            @if(!empty($quickSize))
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-0 px-1" style="font-size: 0.65rem;" title="Sticky size active across items">
                                <i class="bi bi-pin-fill"></i> Sticky
                            </span>
                            @endif
                        </div>
                        <div class="input-group rapid-input-group mt-1">
                            <input type="text" class="form-control rapid-field fw-bold text-center border-primary"
                                   id="quick-size-input"
                                   wire:model.live="quickSize"
                                   placeholder="e.g. 2.2">
                        </div>
                        {{-- Quick Size Pills --}}
                        <div class="d-flex gap-1 mt-1 justify-content-center flex-wrap">
                            @foreach(['2.2', '2.4', '2.6', '2.8', '2.10'] as $sz)
                            <button type="button"
                                    class="btn btn-xs py-0 px-1 rounded {{ $quickSize == $sz ? 'btn-primary text-white' : 'btn-outline-secondary bg-white' }}"
                                    style="font-size: 0.68rem; line-height: 1.2;"
                                    wire:click="$set('quickSize', '{{ $sz }}')">
                                {{ $sz }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- System Cost Preview --}}
                    <div class="{{ $showSizeField ? 'col-md-2' : 'col-md-2' }}">
                        <label class="rapid-label">
                            <span>System Cost ({{ $costMultiplier !== '' && $costMultiplier !== null ? $costMultiplier : 1 }}X)</span>
                        </label>
                        <input type="text" readonly
                               class="form-control rapid-field bg-light fw-bold text-secondary border"
                               value="Rs.{{ number_format($quickSystemCostPreview, 2) }}"
                               title="Saved in system as product cost base">
                    </div>

                    {{-- Selling Price Calculated Preview --}}
                    <div class="{{ $showSizeField ? 'col-md-2' : 'col-md-2' }}">
                        <label class="rapid-label">
                            <span>Selling Price ({{ $sellingMultiplier !== '' && $sellingMultiplier !== null ? $sellingMultiplier : 0 }}X)</span>
                        </label>
                        <input type="text" readonly
                               class="form-control rapid-field bg-white fw-bold text-success border border-success"
                               value="Rs.{{ number_format($quickSellingPricePreview, 2) }}"
                               title="Selling price shown on product and printed on Barcode/QR label">
                    </div>

                    {{-- Add Button --}}
                    <div class="{{ $showSizeField ? 'col-md-1' : 'col-md-2' }}">
                        <label class="rapid-label d-none d-md-block" style="visibility: hidden;">
                            <span>Action</span>
                        </label>
                        <button type="button" class="btn btn-gradient-primary rapid-field w-100 fw-bold d-flex align-items-center justify-content-center px-1"
                                id="quick-add-btn"
                                wire:click="addItemToBatch"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="addItemToBatch">
                                <i class="bi bi-plus-lg me-1"></i> Add
                            </span>
                            <span wire:loading wire:target="addItemToBatch">
                                <i class="spinner-border spinner-border-sm"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Inline Error Message Banner --}}
    @if(!empty($errorMessage))
    <div class="alert alert-danger alert-dismissible fade show mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
        <div>
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errorMessage }}
            @if($editingIndex !== null && str_contains($errorMessage, 'Duplicate Barcode'))
            <button type="button" class="btn btn-sm btn-light border ms-2 fw-semibold" wire:click="generateEditBarcode">
                <i class="bi bi-arrow-repeat me-1 text-primary"></i> Auto-Generate Unique Barcode
            </button>
            @endif
        </div>
        <button type="button" class="btn-close" wire:click="$set('errorMessage', '')" aria-label="Close"></button>
    </div>
    @endif

    {{-- Live Excel-Like Table Card --}}
    <div class="card">
        <div class="card-header bg-white py-2 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center flex-wrap gap-2">
                    <span><i class="bi bi-table text-primary me-1"></i> Batch Products List</span>
                    @if(!empty($purchasingNumber))
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-6">
                        <i class="bi bi-receipt-cutoff me-1"></i> Batch: <strong>{{ $purchasingNumber }}</strong>
                    </span>
                    @endif
                    <span class="badge {{ count($items) > 0 ? 'bg-primary' : 'bg-secondary' }}">{{ count($items) }} Total</span>
                    @if(($unsavedCount ?? 0) > 0)
                    <span class="badge bg-warning text-dark border border-warning shadow-sm">
                        <i class="bi bi-clock-history me-1"></i> {{ $unsavedCount }} Unsaved
                    </span>
                    @elseif(count($items) > 0 && $isSaved)
                    <span class="badge bg-success shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> All Saved
                    </span>
                    @endif
                    @if(($missedItemsCount ?? 0) > 0)
                    <span class="badge bg-danger shadow-sm cursor-pointer" wire:click="setFilter('notreceived')" title="Click to view missed items">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $missedItemsCount }} Missed
                    </span>
                    @endif
                </h6>
            </div>

            {{-- Header Search Bar (Search in batch or lookup created products from inventory) --}}
            <div class="position-relative flex-grow-1 mx-md-2" style="max-width: 320px; min-width: 240px;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                           class="form-control border-start-0 border-end-0 ps-0"
                           wire:model.live.debounce.200ms="searchQuery"
                           wire:keydown.enter="searchSubmit"
                           placeholder="Search Name, Code, QR...">
                    @if(!empty($searchQuery))
                    <button class="btn btn-outline-secondary border-start-0" type="button" wire:click="$set('searchQuery', '')" title="Clear search">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    @endif
                </div>

                {{-- Floating Inventory Matches Dropdown --}}
                @if(!empty($this->inventorySearchResults))
                <div class="position-absolute bg-white shadow-lg border rounded-3 p-2 mt-1 w-100" style="z-index: 1050; left: 0; top: 100%;">
                    <div class="small fw-bold text-muted px-2 py-1 border-bottom mb-1 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-box-seam text-primary me-1"></i> In Inventory (Click to load & edit):</span>
                        <span class="badge bg-light text-secondary">{{ count($this->inventorySearchResults) }}</span>
                    </div>
                    <div class="d-flex flex-column gap-1" style="max-height: 220px; overflow-y: auto;">
                        @foreach($this->inventorySearchResults as $inv)
                        <div class="d-flex justify-content-between align-items-center p-2 rounded hover-bg-light border-bottom">
                            <div class="small text-truncate me-2" style="max-width: 190px;">
                                <div class="fw-bold text-dark text-truncate">{{ $inv['name'] }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    <code>{{ $inv['code'] }}</code>
                                    @if($inv['barcode']) · <span class="text-primary fw-semibold">{{ $inv['barcode'] }}</span>@endif
                                    · <span class="text-success fw-semibold">Stock: {{ $inv['quantity'] }}</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0 py-0 px-2"
                                    style="font-size: 0.72rem;"
                                    wire:click="loadProductIntoBatch({{ $inv['id'] }})">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                {{-- Generate All QR Button --}}
                <button type="button" class="btn btn-sm btn-outline-primary"
                        wire:click="generateAllQRCodes"
                        {{ count($items) === 0 ? 'disabled' : '' }}>
                    <i class="bi bi-upc-scan me-1"></i> Generate All QR
                </button>

                {{-- Clear Saved Items Button --}}
                @if($this->savedItemsCount > 0)
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        wire:click="clearSavedItems"
                        title="Clear all saved products from this table view">
                    <i class="bi bi-stars text-success me-1"></i> Clear Saved ({{ $this->savedItemsCount }})
                </button>
                @endif

                {{-- Clear Batch Button --}}
                @if(count($items) > 0 && !$isSaved)
                <button type="button" class="btn btn-sm btn-outline-danger"
                        wire:click="clearBatch"
                        wire:confirm="Are you sure you want to clear all products from this batch? All entered items will be lost.">
                    <i class="bi bi-trash me-1"></i> Clear All
                </button>
                @endif

                {{-- Print All QR Button --}}
                <button type="button" class="btn btn-sm {{ $isSaved ? 'btn-gradient-primary' : 'btn-outline-secondary' }}"
                        onclick="printBatchLabels()"
                        {{ count($items) === 0 ? 'disabled' : '' }}>
                    <i class="bi bi-printer me-1"></i> Print All QR Labels
                </button>

                {{-- Save Received Only Button (when batch has both received and missed unsaved items) --}}
                @if($this->unsavedReceivedCount > 0 && $this->unsavedNotReceivedCount > 0)
                <button type="button" class="btn btn-sm btn-outline-success px-2"
                        wire:click="saveReceivedOnly"
                        wire:loading.attr="disabled"
                        title="Save only received products into active inventory">
                    <span wire:loading.remove wire:target="saveReceivedOnly">
                        <i class="bi bi-check2-circle me-1"></i> Save Received Only ({{ $this->unsavedReceivedCount }})
                    </span>
                    <span wire:loading wire:target="saveReceivedOnly">
                        <i class="spinner-border spinner-border-sm me-1"></i> Saving...
                    </span>
                </button>
                @endif

                {{-- Save All Products / Update Order Button --}}
                <button type="button" class="btn btn-sm btn-gradient-success px-3"
                        wire:click="saveBatch"
                        wire:loading.attr="disabled"
                        {{ ($isEditingOrder ? count($items) === 0 : $this->unsavedItemsCount === 0) ? 'disabled' : '' }}>
                    <span wire:loading.remove wire:target="saveBatch">
                        @if($isEditingOrder)
                        <i class="bi bi-check2-circle me-1"></i> Update Order{{ !empty($purchasingNumber) ? ': ' . $purchasingNumber : '' }} ({{ count($items) }})
                        @else
                        <i class="bi bi-check-all me-1"></i> Save Order{{ !empty($purchasingNumber) ? ': ' . $purchasingNumber : '' }} ({{ $this->unsavedItemsCount }})
                        @endif
                    </span>
                    <span wire:loading wire:target="saveBatch">
                        <i class="spinner-border spinner-border-sm me-1"></i> Saving...
                    </span>
                </button>
            </div>
        </div>

        @if(count($items) > 0)
        {{-- Stock Receipt Quick Filter & Action Toolbar --}}
        <div class="bg-light px-3 py-2 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="small fw-semibold text-muted me-1"><i class="bi bi-funnel me-1"></i>Filter:</span>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button"
                            wire:click="setFilter('all')"
                            class="btn {{ $filterStatus === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        All ({{ $this->filterCounts['all'] }})
                    </button>
                    <button type="button"
                            wire:click="setFilter('received')"
                            class="btn {{ $filterStatus === 'received' ? 'btn-success' : 'btn-outline-success' }}">
                        <i class="bi bi-check-circle-fill me-1"></i> Received ({{ $this->filterCounts['received'] }})
                    </button>
                    <button type="button"
                            wire:click="setFilter('notreceived')"
                            class="btn {{ $filterStatus === 'notreceived' ? 'btn-danger' : 'btn-outline-danger' }}">
                        <i class="bi bi-x-circle-fill me-1"></i> Missed / Not Received ({{ $this->filterCounts['notreceived'] }})
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">
                @if(($missedItemsCount ?? 0) > 0)
                <button type="button" class="btn btn-sm btn-outline-danger"
                        wire:click="downloadMissedProductsExcel"
                        title="Download Excel file of missed / not received products">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Export Missed (.xlsx)
                </button>
                @endif

                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-check2-square me-1"></i> Stock Check Tools
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <button class="dropdown-item text-success py-1" type="button" wire:click="markAllAsReceived">
                                <i class="bi bi-check-all me-1"></i> Mark All as Received
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item text-danger py-1" type="button" wire:click="markAllAsNotReceived">
                                <i class="bi bi-x-circle me-1"></i> Mark All as Not Received
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Missed Products Alert Banner --}}
        @if(($missedItemsCount ?? 0) > 0)
        <div class="alert alert-danger d-flex align-items-center justify-content-between py-2 px-3 mb-0 border-0 border-bottom rounded-0">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-octagon-fill fs-5 text-danger"></i>
                <div>
                    <strong>{{ $missedItemsCount }} Product(s) Not Received / Missed!</strong>
                    <span class="ms-1">(Total Missed Qty: <strong>{{ $missedQty }}</strong> | Value: <strong>Rs.{{ number_format($missedCost, 2) }}</strong>)</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if($filterStatus !== 'notreceived')
                <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" wire:click="setFilter('notreceived')">
                    <i class="bi bi-funnel me-1"></i> Show Missed Only
                </button>
                @else
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" wire:click="setFilter('all')">
                    <i class="bi bi-x me-1"></i> Clear Filter (Show All)
                </button>
                @endif
                <button type="button" class="btn btn-sm btn-danger py-1 px-2" wire:click="downloadMissedProductsExcel">
                    <i class="bi bi-download me-1"></i> Download Missed List (.xlsx)
                </button>
            </div>
        </div>
        @endif

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 40px;">No</th>
                            <th style="min-width: 150px;">Product Name</th>
                            @if($hasSizeColumn)
                            <th style="min-width: 80px;">Size</th>
                            @endif
                            <th style="min-width: 120px;">Supplier Cost</th>
                            <th style="min-width: 130px;">System Cost ({{ $costMultiplier !== '' && $costMultiplier !== null ? $costMultiplier : 1 }}X)</th>
                            <th style="min-width: 140px;">Sell Price ({{ $sellingMultiplier !== '' && $sellingMultiplier !== null ? $sellingMultiplier : 0 }}X)</th>
                            <th style="min-width: 70px;">Qty</th>
                            <th style="min-width: 110px;">Total Cost</th>
                            {{-- Receipt Check Column --}}
                            <th class="text-center" style="min-width: 135px;">
                                <span class="d-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-box-seam text-primary"></i> Receipt Check
                                </span>
                            </th>
                            {{-- Excel-Style Barcode / QR Column Filter Header --}}
                            <th style="min-width: 250px;">
                                <div class="dropdown d-inline-block w-100">
                                    <button class="btn btn-sm p-0 w-100 d-flex align-items-center justify-content-between text-dark fw-bold border-0 bg-transparent text-nowrap"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            data-bs-auto-close="true"
                                            aria-expanded="false"
                                            title="Click to filter by Barcode / QR / Print Status">
                                        <span class="d-flex align-items-center gap-1">
                                            <span>Barcode / QR</span>
                                            @if($filterStatus !== 'all')
                                            <span class="badge bg-primary text-white text-capitalize fw-normal" style="font-size: 0.7rem;">
                                                {{ str_replace('_', ' ', $filterStatus) }}
                                            </span>
                                            @endif
                                        </span>
                                        <span class="badge {{ $filterStatus !== 'all' ? 'bg-primary text-white' : 'bg-white text-secondary border' }} p-1 rounded ms-1 shadow-sm"
                                              style="font-size: 0.75rem; cursor: pointer;">
                                            <i class="bi {{ $filterStatus !== 'all' ? 'bi-funnel-fill' : 'bi-funnel' }}"></i>
                                            <i class="bi bi-caret-down-fill" style="font-size: 0.6rem;"></i>
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2 mt-1" style="border-radius: 10px; min-width: 230px; font-size: 0.85rem; z-index: 1060;">
                                        <li class="dropdown-header text-uppercase fw-bold text-muted small px-3 py-1">
                                            <i class="bi bi-box-seam me-1 text-primary"></i> Receipt Status Filter
                                        </li>
                                        <li>
                                            <button type="button"
                                                    wire:click="setFilter('received')"
                                                    class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $filterStatus === 'received' ? 'active fw-bold' : '' }}">
                                                <span><i class="bi bi-check-circle text-success me-2"></i> Received Stock</span>
                                                <span class="badge {{ $filterStatus === 'received' ? 'bg-white text-success' : 'bg-success' }}">{{ $this->filterCounts['received'] }}</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button"
                                                    wire:click="setFilter('notreceived')"
                                                    class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $filterStatus === 'notreceived' ? 'active fw-bold' : '' }}">
                                                <span><i class="bi bi-x-circle text-danger me-2"></i> Missed / Not Received</span>
                                                <span class="badge {{ $filterStatus === 'notreceived' ? 'bg-white text-danger' : 'bg-danger' }}">{{ $this->filterCounts['notreceived'] }}</span>
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li class="dropdown-header text-uppercase fw-bold text-muted small px-3 py-1">
                                            <i class="bi bi-qr-code me-1 text-primary"></i> QR / Barcode Filter
                                        </li>
                                        <li>
                                            <button type="button"
                                                    wire:click="setFilter('all')"
                                                    class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $filterStatus === 'all' ? 'active fw-bold' : '' }}">
                                                <span><i class="bi bi-check2-all me-2"></i> All Records</span>
                                                <span class="badge {{ $filterStatus === 'all' ? 'bg-white text-primary' : 'bg-secondary' }}">{{ $this->filterCounts['all'] }}</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button"
                                                    wire:click="setFilter('with_qr')"
                                                    class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $filterStatus === 'with_qr' ? 'active fw-bold' : '' }}">
                                                <span><i class="bi bi-qr-code text-success me-2"></i> With QR / Barcode</span>
                                                <span class="badge {{ $filterStatus === 'with_qr' ? 'bg-white text-success' : 'bg-success' }}">{{ $this->filterCounts['with_qr'] }}</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button"
                                                    wire:click="setFilter('no_qr')"
                                                    class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $filterStatus === 'no_qr' ? 'active fw-bold' : '' }}">
                                                <span><i class="bi bi-slash-circle text-warning me-2"></i> No QR (Empty)</span>
                                                <span class="badge {{ $filterStatus === 'no_qr' ? 'bg-white text-dark' : 'bg-warning text-dark' }}">{{ $this->filterCounts['no_qr'] }}</span>
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li class="dropdown-header text-uppercase fw-bold text-muted small px-3 py-1">
                                            <i class="bi bi-printer me-1 text-primary"></i> Print Status Filter
                                        </li>
                                        <li>
                                            <button type="button"
                                                    wire:click="setFilter('printed')"
                                                    class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $filterStatus === 'printed' ? 'active fw-bold' : '' }}">
                                                <span><i class="bi bi-printer-fill text-info me-2"></i> Printed</span>
                                                <span class="badge {{ $filterStatus === 'printed' ? 'bg-white text-info' : 'bg-info' }}">{{ $this->filterCounts['printed'] }}</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button"
                                                    wire:click="setFilter('not_printed')"
                                                    class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $filterStatus === 'not_printed' ? 'active fw-bold' : '' }}">
                                                <span><i class="bi bi-printer text-secondary me-2"></i> Non Printed</span>
                                                <span class="badge {{ $filterStatus === 'not_printed' ? 'bg-white text-dark' : 'bg-secondary' }}">{{ $this->filterCounts['not_printed'] }}</span>
                                            </button>
                                        </li>
                                        @if($filterStatus !== 'all')
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <button type="button"
                                                    wire:click="setFilter('all')"
                                                    class="dropdown-item text-danger py-1 text-center small fw-semibold">
                                                <i class="bi bi-x-circle me-1"></i> Clear Filter (Show All)
                                            </button>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </th>
                            <th style="min-width: 110px;">Status</th>
                            <th class="text-center" style="width: 60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->filteredItems as $item)
                        @php 
                            $index = $item['_original_index']; 
                            $isEditing = ($editingIndex === $index); 
                            $receiveStatus = $item['receive_status'] ?? 'received';
                            $isNotReceived = ($receiveStatus === 'notreceived');
                        @endphp
                        <tr wire:key="batch-row-{{ $item['temp_id'] }}"
                            data-barcode="{{ $item['barcode'] ?? '' }}"
                            data-price="{{ number_format($item['selling_price'], 2) }}"
                            data-qty="{{ $item['quantity'] }}"
                            data-size="{{ $item['size'] ?? '' }}"
                            data-index="{{ $index }}"
                            class="{{ $isEditing ? 'table-warning' : ($isNotReceived ? 'table-danger bg-opacity-25' : ($item['is_saved'] ? 'table-row-saved' : '')) }}">
                            <td class="ps-3 fw-bold text-muted">
                                {{ $index + 1 }}
                                @if($isEditing)
                                <span class="badge bg-primary text-white d-block mt-1" style="font-size: 0.65rem;">Editing</span>
                                @endif
                            </td>

                            {{-- Product Name --}}
                            <td>
                                @if($isEditing)
                                <input type="text" class="form-control form-control-sm fw-bold border-primary shadow-sm"
                                       wire:model="editName" placeholder="Product Name">
                                @elseif(!$item['is_saved'] || $isEditingOrder)
                                <input type="text" class="form-control form-control-sm fw-medium"
                                       value="{{ $item['name'] }}"
                                       wire:change="updateRowName({{ $index }}, $event.target.value)">
                                @else
                                <span class="fw-bold text-dark">{{ $item['name'] }}</span>
                                @endif
                            </td>

                            {{-- Size Column --}}
                            @if($hasSizeColumn)
                            <td>
                                @if($isEditing)
                                <input type="text" class="form-control form-control-sm text-center fw-bold border-primary shadow-sm"
                                       style="max-width: 85px;"
                                       wire:model="editSize" placeholder="Size">
                                @elseif(!$item['is_saved'] || $isEditingOrder)
                                <input type="text" class="form-control form-control-sm text-center fw-semibold"
                                       style="max-width: 85px;"
                                       value="{{ $item['size'] ?? '' }}"
                                       wire:change="updateRowSize({{ $index }}, $event.target.value)"
                                       placeholder="-">
                                @else
                                    @if(!empty($item['size']))
                                    <span class="badge bg-purple-subtle text-primary border border-primary-subtle fw-bold" style="font-size: 0.8rem;">
                                        {{ $item['size'] }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                @endif
                            </td>
                            @endif

                            {{-- Supplier Cost Entered --}}
                            <td>
                                @if($isEditing)
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white">Rs.</span>
                                    <input type="number" step="any" class="form-control form-control-sm fw-semibold border-primary"
                                           wire:model.live.debounce.250ms="editEnteredCost"
                                           placeholder="Cost">
                                </div>
                                @elseif(!$item['is_saved'] || $isEditingOrder)
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white">Rs.</span>
                                    <input type="number" step="any" class="form-control form-control-sm fw-semibold"
                                           value="{{ $item['entered_cost'] ?? round($item['cost_price'] / ($costMultiplier ?: 1), 2) }}"
                                           wire:change="updateRowCost({{ $index }}, $event.target.value)"
                                           title="Entered Supplier Cost">
                                </div>
                                @else
                                <span class="fw-semibold">Rs.{{ number_format($item['entered_cost'] ?? round($item['cost_price'] / ($costMultiplier ?: 1), 2), 2) }}</span>
                                @endif
                            </td>

                            {{-- System Cost --}}
                            <td>
                                @if($isEditing)
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-secondary">Rs.</span>
                                    <input type="number" step="any" class="form-control form-control-sm fw-bold text-dark border-primary"
                                           wire:model="editCostPrice">
                                </div>
                                @elseif(!$item['is_saved'] || $isEditingOrder)
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-secondary">Rs.</span>
                                    <input type="number" step="any" class="form-control form-control-sm fw-bold text-dark"
                                           value="{{ $item['cost_price'] }}"
                                           wire:change="updateRowSystemCost({{ $index }}, $event.target.value)"
                                           title="System Cost to Save">
                                </div>
                                @else
                                <span class="fw-bold text-dark">Rs.{{ number_format($item['cost_price'], 2) }}</span>
                                @endif
                            </td>

                            {{-- Selling Price --}}
                            <td>
                                @if($isEditing)
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white text-success fw-bold">Rs.</span>
                                    <input type="number" step="any" class="form-control form-control-sm fw-bold text-success border-success"
                                           wire:model="editSellingPrice">
                                </div>
                                @elseif(!$item['is_saved'] || $isEditingOrder)
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white text-success fw-bold">Rs.</span>
                                    <input type="number" step="any" class="form-control form-control-sm fw-bold text-success"
                                           value="{{ $item['selling_price'] }}"
                                           wire:change="updateRowSellingPrice({{ $index }}, $event.target.value)"
                                           title="Selling Price">
                                </div>
                                @else
                                <span class="fw-bold text-success">Rs.{{ number_format($item['selling_price'], 2) }}</span>
                                @endif
                            </td>

                            {{-- Quantity (Key Editable for Saved Items) --}}
                            <td>
                                @if($isEditing)
                                <input type="number" min="0" class="form-control form-control-sm text-center fw-bold border-primary shadow-sm"
                                       style="font-size: 1.05rem; background-color: #eff6ff; min-width: 65px;"
                                       wire:model="editQuantity" autofocus title="Edit Stock Quantity">
                                @elseif(!$item['is_saved'] || $isEditingOrder)
                                <input type="number" min="1" class="form-control form-control-sm text-center fw-bold"
                                       value="{{ $item['quantity'] }}"
                                       wire:change="updateRowQuantity({{ $index }}, $event.target.value)">
                                @else
                                <span class="badge bg-secondary fs-6">{{ $item['quantity'] }}</span>
                                @endif
                            </td>

                            {{-- Total Cost: Cost Price x Quantity --}}
                            <td>
                                <span class="fw-semibold text-dark">
                                    @if($isEditing)
                                    Rs.{{ number_format(floatval($editCostPrice ?: 0) * intval($editQuantity ?: 0), 2) }}
                                    @else
                                    Rs.{{ number_format(floatval($item['cost_price']) * intval($item['quantity']), 2) }}
                                    @endif
                                </span>
                            </td>

                            {{-- Receipt Check (Received vs Missed) --}}
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center gap-1">
                                    @if($isNotReceived)
                                        <button type="button"
                                                class="btn btn-sm btn-danger py-1 px-2 shadow-sm d-flex align-items-center gap-1 rounded-pill"
                                                wire:click="toggleReceiveStatus({{ $index }})"
                                                title="Click to toggle: Mark as Received">
                                            <i class="bi bi-x-circle-fill"></i>
                                            <span class="fw-bold" style="font-size: 0.72rem;">Not Received</span>
                                        </button>
                                        <span class="text-danger fw-semibold" style="font-size: 0.65rem;">
                                            <i class="bi bi-exclamation-triangle"></i> Missed
                                        </span>
                                    @else
                                        <button type="button"
                                                class="btn btn-sm btn-outline-success py-1 px-2 shadow-sm d-flex align-items-center gap-1 rounded-pill"
                                                wire:click="toggleReceiveStatus({{ $index }})"
                                                title="Click to toggle: Mark as Not Received / Missed">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <span class="fw-semibold text-dark" style="font-size: 0.72rem;">Received</span>
                                        </button>
                                    @endif
                                </div>
                            </td>

                            {{-- Barcode / QR --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if(!empty($item['barcode']))
                                    <div class="qr-badge-preview flex-shrink-0" title="QR Preview">
                                        <div class="batch-qr-code" id="batch-qr-{{ $index }}" data-barcode="{{ $item['barcode'] }}"></div>
                                    </div>
                                    @endif

                                    @if($isEditing)
                                    <div class="input-group input-group-sm flex-nowrap" style="min-width: 220px;">
                                        <input type="text" class="form-control form-control-sm font-monospace fw-bold border-primary"
                                               wire:model="editBarcode" placeholder="Scan/Type QR">
                                        <button type="button" class="btn btn-outline-primary px-2"
                                                wire:click="generateEditBarcode"
                                                title="Generate New Unique Barcode">
                                            <i class="bi bi-arrow-repeat"></i> Gen
                                        </button>
                                    </div>
                                    @elseif(!$item['is_saved'])
                                    <div class="input-group input-group-sm flex-nowrap" style="min-width: 210px;">
                                        <input type="text" class="form-control form-control-sm font-monospace fw-bold"
                                               placeholder="Scan / Type QR"
                                               value="{{ $item['barcode'] ?? '' }}"
                                               wire:change="updateRowBarcode({{ $index }}, $event.target.value)"
                                               title="Type or scan barcode manually">
                                        <button type="button" class="btn btn-outline-primary px-2 d-flex align-items-center gap-1"
                                                wire:click="generateSingleQR({{ $index }})"
                                                title="Auto-Generate QR code & Auto-Save product">
                                            <i class="bi bi-qr-code"></i> <span>Gen & Save</span>
                                        </button>
                                        @if(!empty($item['barcode']))
                                        <button type="button" class="btn btn-outline-secondary px-2"
                                                onclick="printSingleFastLabel('{{ $item['barcode'] }}', '{{ $item['code'] }}', '{{ number_format($item['selling_price'], 2) }}', {{ $index }}, '{{ addslashes($item['size'] ?? '') }}')"
                                                title="Print this label">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        @endif
                                    </div>
                                    @else
                                    <div>
                                        <code class="d-block small text-dark fw-bold">{{ $item['barcode'] }}</code>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-primary"
                                                onclick="printSingleFastLabel('{{ $item['barcode'] }}', '{{ $item['code'] }}', '{{ number_format($item['selling_price'], 2) }}', {{ $index }}, '{{ addslashes($item['size'] ?? '') }}')"
                                                title="Print this label">
                                            <i class="bi bi-printer"></i> Print
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Status: Saved/Draft & Printed/Unprinted --}}
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @if($isEditing)
                                    <span class="badge bg-warning text-dark border border-warning px-2 py-1">
                                        <i class="bi bi-pencil-fill me-1"></i> Editing
                                    </span>
                                    @elseif($item['is_saved'])
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Saved
                                    </span>
                                    @else
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-2 py-1">
                                        <i class="bi bi-pencil-fill me-1"></i> Draft
                                    </span>
                                    @endif

                                    @if(!empty($item['is_printed']))
                                    <button type="button"
                                            wire:click="togglePrintStatus({{ $index }})"
                                            class="btn btn-sm p-0 text-start border-0 bg-transparent"
                                            title="Click to mark as non-printed">
                                        <span class="badge bg-info bg-opacity-25 text-info border border-info px-2 py-1 w-100 text-start">
                                            <i class="bi bi-printer-fill me-1"></i> Printed
                                        </span>
                                    </button>
                                    @else
                                    <button type="button"
                                            wire:click="togglePrintStatus({{ $index }})"
                                            class="btn btn-sm p-0 text-start border-0 bg-transparent"
                                            title="Click to mark as printed">
                                        <span class="badge bg-light text-secondary border px-2 py-1 w-100 text-start">
                                            <i class="bi bi-printer me-1"></i> Non Printed
                                        </span>
                                    </button>
                                    @endif
                                </div>
                            </td>

                            {{-- Action --}}
                            <td class="text-center" style="min-width: 110px;">
                                @if($isEditing)
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-success px-2 py-1 shadow-sm"
                                            wire:click="saveEditedProduct" title="Save changes to database">
                                        <i class="bi bi-check-lg me-1"></i> Save
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1"
                                            wire:click="cancelEditing" title="Cancel edit">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                @elseif($item['is_saved'])
                                <div class="d-flex gap-1 justify-content-center align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1 shadow-sm"
                                            wire:click="startEditingSaved({{ $index }})" title="Edit product (Quantity, Price, Name)">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1"
                                            wire:click="removeItem({{ $index }})" title="Clear saved product from this list">
                                        <i class="bi bi-x-lg text-danger"></i>
                                    </button>
                                </div>
                                @else
                                <div class="d-flex gap-1 justify-content-center align-items-center">
                                    <button type="button" class="btn btn-sm btn-success px-2 py-1 shadow-sm d-flex align-items-center gap-1"
                                            wire:click="saveSingleProduct({{ $index }})"
                                            wire:loading.attr="disabled"
                                            title="Save this product to database">
                                        <i class="bi bi-check2-circle"></i>
                                        <span>Save</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle"
                                            wire:click="removeItem({{ $index }})" title="Remove / Clear Item from batch">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $hasSizeColumn ? 12 : 11 }}" class="text-center py-4 text-muted">
                                @if(count($items) === 0)
                                    <i class="bi bi-keyboard fs-3 d-block mb-1"></i>
                                    <span>No products in batch. Type <strong>Cost Price</strong> above and hit <kbd>Enter</kbd> to add.</span>
                                @elseif(!empty($searchQuery))
                                    <i class="bi bi-search fs-3 d-block mb-1 text-secondary"></i>
                                    <span>No products matching "<strong>{{ $searchQuery }}</strong>" in batch.</span>
                                    <div class="mt-2 d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="$set('searchQuery', '')">
                                            <i class="bi bi-x-lg me-1"></i> Clear Search
                                        </button>
                                    </div>
                                @else
                                    <i class="bi bi-funnel fs-3 d-block mb-1 text-secondary"></i>
                                    <span>No products match the selected filter <strong>{{ ucfirst(str_replace('_', ' ', $filterStatus)) }}</strong>.</span>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="setFilter('all')">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Show All Products
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Summary Totals Bar --}}
            @if(count($items) > 0)
            <div class="card-footer bg-light py-2 px-3 border-top">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                    <div><strong>Total Items:</strong> {{ $totalItems }} products</div>
                    <div><strong>Total Quantity:</strong> <span class="text-primary fw-bold">{{ $totalQty }} units</span></div>
                    <div><strong>Total System Cost ({{ $costMultiplier !== '' && $costMultiplier !== null ? $costMultiplier : 1 }}X):</strong> <span class="fw-bold text-dark">Rs.{{ number_format($totalCost, 2) }}</span></div>
                    <div><strong>Expected Retail Value ({{ $sellingMultiplier !== '' && $sellingMultiplier !== null ? $sellingMultiplier : 0 }}X):</strong> <span class="text-success fw-bold">Rs.{{ number_format($totalRetail, 2) }}</span></div>
                    <div><strong>Projected Gross Profit:</strong> <span class="text-info fw-bold">Rs.{{ number_format($totalRetail - $totalCost, 2) }}</span></div>
                </div>
                @if(($missedItemsCount ?? 0) > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 small pt-2 mt-2 border-top text-danger">
                    <div>
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        <span class="text-dark"><strong>Received Goods:</strong> {{ $receivedItemsCount }} items ({{ $receivedQty }} units) · <strong>Rs.{{ number_format($receivedCost, 2) }}</strong></span>
                    </div>
                    <div>
                        <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i>
                        <strong>Missed / Not Received:</strong> {{ $missedItemsCount }} items ({{ $missedQty }} units) · <strong>Rs.{{ number_format($missedCost, 2) }}</strong>
                    </div>
                    <div>
                        <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2" wire:click="downloadMissedProductsExcel">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export Missed Items (.xlsx)
                        </button>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Clean Excel Import Modal with Supplier-Specific Presets --}}
    <div wire:ignore.self class="modal fade" id="excelImportModal" tabindex="-1" aria-labelledby="excelImportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title fw-bold" id="excelImportModalLabel">
                        <i class="bi bi-file-earmark-excel me-1"></i> Import Supplier Excel / CSV
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    @php
                        $currentPreset = $this->getSupplierPreset($importSupplierId);
                    @endphp

                    {{-- Supplier Selector --}}
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-truck text-primary me-1"></i> Select Supplier:</span>
                            <span class="badge {{ $currentPreset['badge_class'] ?? 'bg-secondary text-white' }} px-2 py-1" style="font-size: 0.75rem;">
                                {{ $currentPreset['badge_text'] ?? 'Standard Format' }}
                            </span>
                        </label>
                        <select class="form-select form-select-sm" wire:model.live="importSupplierId">
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dynamic Template Download Row --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                        <div>
                            <div class="fw-bold small text-dark">{{ $currentPreset['name'] }} Template</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $currentPreset['description'] }}</div>
                        </div>
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-success" wire:click="downloadSupplierTemplate({{ $importSupplierId }})" title="Download {{ $currentPreset['name'] }} Excel Template">
                                <i class="bi bi-file-earmark-excel me-1"></i> Download Template (.xlsx)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="downloadSampleTemplate" title="Download Standard CSV">
                                <i class="bi bi-download me-1"></i> (.csv)
                            </button>
                        </div>
                    </div>

                    {{-- Dynamic Columns Box --}}
                    <div class="p-2 bg-light rounded border mb-3 small">
                        <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="bi bi-check-circle-fill text-success"></i> Expected Columns for {{ $currentPreset['name'] }}:
                        </div>
                        <ul class="mb-0 ps-3 text-secondary" style="font-size: 0.8rem;">
                            @foreach($currentPreset['columns'] as $col)
                            <li><code>{{ $col }}</code></li>
                            @endforeach
                        </ul>
                        <div class="text-muted mt-1" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle me-1"></i> The system automatically detects row headers and filters out invoice summary/tax footer rows.
                        </div>
                    </div>

                    {{-- File Upload Field & Purchasing Code / Batch Name --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="bi bi-file-earmark-spreadsheet text-success me-1"></i> Select Excel / CSV File:
                            </label>
                            <input type="file" id="excel-file-upload-input" class="form-control"
                                   wire:model="excelFile" accept=".xlsx,.xls,.csv"
                                   onchange="handleExcelFileNameAutoFill(this)">
                            <div wire:loading wire:target="excelFile" class="text-primary small mt-1">
                                <span class="spinner-border spinner-border-sm me-1"></span> Uploading file...
                            </div>
                            @error('excelFile') <span class="text-danger small mt-1 d-block">* {{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6" wire:key="modal-purchasing-code-container">
                            <label class="form-label small fw-bold text-dark mb-1 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-receipt-cutoff text-primary me-1"></i> Purchasing Code / Batch Name:</span>
                                <span class="text-muted" style="font-size: 0.72rem;">(Auto-filled from file name)</span>
                            </label>
                            <div class="input-group">
                                <input type="text" id="modal-purchasing-code-input"
                                       wire:key="modal-purchasing-code-field"
                                       class="form-control fw-bold text-primary"
                                       wire:model.live.debounce.400ms="purchasingNumber"
                                       placeholder="Enter Purchasing Code / Batch Name">
                                @if(!empty($purchasingNumber))
                                <button type="button" class="btn btn-outline-secondary" wire:click="$set('purchasingNumber', '')"
                                        onclick="setTimeout(() => document.getElementById('modal-purchasing-code-input')?.focus(), 50)"
                                        title="Clear Code">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-success px-3"
                            wire:click="importExcel"
                            wire:loading.attr="disabled"
                            wire:target="importExcel, excelFile"
                            {{ !$excelFile ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="importExcel">
                            <i class="bi bi-upload me-1"></i> Import to Table
                        </span>
                        <span wire:loading wire:target="importExcel">
                            <i class="spinner-border spinner-border-sm me-1"></i> Reading...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bwip-js@3.0.4/dist/bwip-js-min.js"></script>
    <script>
        const LABEL_SETTINGS = {
            width:       {{ $labelSettings['label_width'] }},
            height:      {{ $labelSettings['label_height'] }},
            padding:     {{ $labelSettings['label_padding'] }},
            textWidth:   {{ $labelSettings['label_text_width'] }},
            tailWidth:   {{ $labelSettings['label_tail_width'] }},
            tailHeight:  {{ $labelSettings['label_tail_height'] }},
            fontFamily:  '{{ addslashes($labelSettings['label_font_family']) }}',
            fontShop:    {{ $labelSettings['label_font_shop'] }},
            fontPrice:   {{ $labelSettings['label_font_price'] }},
            fontBarcode: {{ $labelSettings['label_font_barcode'] }},
            qrSize:      {{ $labelSettings['label_qr_size'] }},
            barcodeType: '{{ addslashes($labelSettings['label_barcode_type'] ?? 'datamatrix') }}',
            showShop:        {{ $labelSettings['label_show_shop'] ? 'true' : 'false' }},
            showBarcodeText: {{ $labelSettings['label_show_barcode_text'] ? 'true' : 'false' }},
            showQR:          {{ $labelSettings['label_show_qr'] ? 'true' : 'false' }}
        };

        function renderBarcodeToCanvas(target, text, type) {
            if (!text) return;
            const canvas = typeof target === 'string' ? document.getElementById(target) : target;
            if (!canvas) return;

            let bcid = 'datamatrix';
            let opts = {
                text: text,
                scale: 3,
                padding: 0,
            };

            if (type === 'qrcode_l') {
                bcid = 'qrcode';
                opts.eclevel = 'L';
            } else if (type === 'qrcode_m') {
                bcid = 'qrcode';
                opts.eclevel = 'M';
            } else if (type === 'code128') {
                bcid = 'code128';
                opts.height = 10;
                opts.includetext = false;
            } else {
                bcid = 'datamatrix'; // Default ECC 200
            }
            opts.bcid = bcid;

            try {
                bwipjs.toCanvas(canvas, opts);
            } catch (e) {
                console.error('bwipjs render error:', e);
            }
        }

        function buildLabelCSS(s) {
            const qrMM   = s.showQR ? Math.min(s.qrSize, 9.5) : 0;
            return `
                * { margin:0; padding:0; box-sizing:border-box; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
                body { font-family:'${s.fontFamily || 'Arial'}',Arial,sans-serif; margin:0; padding:0; background:white; font-weight:700; color:#000; }
                .label-wrapper {
                    width:${s.width}mm; height:${s.height}mm;
                    display:flex; flex-direction:row; align-items:stretch; overflow:hidden;
                    page-break-inside:avoid;
                }
                .text-section {
                    width:50%; flex:0 0 50%;
                    display:flex; flex-direction:column; justify-content:center;
                    padding-left:1.5mm; padding-right:1mm; overflow:hidden;
                }
                .info-shop {
                    font-size:${s.fontShop}pt; font-weight:800; color:#000;
                    white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.2;
                    text-transform:uppercase;
                    display:${s.showShop ? 'block' : 'none'};
                }
                .info-price {
                    font-size:${s.fontPrice}pt; font-weight:800; color:#000; line-height:1.2;
                    white-space:nowrap; overflow:hidden;
                }
                .info-barcode {
                    font-size:${s.fontBarcode}pt; font-weight:700; color:#000; line-height:1.2;
                    white-space:nowrap; overflow:hidden;
                    display:${s.showBarcodeText ? 'block' : 'none'};
                }
                .qr-section {
                    width:50%; flex:0 0 50%;
                    display:${s.showQR ? 'flex' : 'none'};
                    flex-direction:row;
                    align-items:center;
                    justify-content:center;
                    gap:1.5mm;
                }
                .info-qr-size {
                    font-size:6pt;
                    font-weight:bold;
                    color:#000;
                    white-space:nowrap;
                    line-height:1;
                    text-align:center;
                    flex-shrink:0;
                    letter-spacing:-0.2px;
                }
                .qr-section canvas, .qr-section img, .qr-section svg {
                    width:${qrMM}mm !important; height:${qrMM}mm !important;
                    flex-shrink:0;
                    object-fit:contain;
                    image-rendering: pixelated;
                    image-rendering: -webkit-optimize-contrast;
                    shape-rendering: crispEdges;
                }
                @media print {
                    body { margin:0; padding:0; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
                    @page { size:${s.width}mm ${s.height}mm; margin:0; }
                    .qr-section canvas, .qr-section img, .qr-section svg {
                        image-rendering: pixelated;
                        image-rendering: -webkit-optimize-contrast;
                        shape-rendering: crispEdges;
                    }
                }
            `;
        }

        function printSingleFastLabel(barcode, code, retailPrice, rowIndex, size) {
            if (typeof rowIndex !== 'undefined' && typeof @this !== 'undefined') {
                @this.call('markRowPrinted', rowIndex);
            }
            const s = LABEL_SETTINGS;
            const cleanSize = size ? String(size).trim() : '';
            const displaySize = cleanSize ? cleanSize.replace(/^(sz:?|size:?)\s*/i, '') : '';

            const printWindow = window.open('', '_blank', 'width=450,height=320');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Label</title>
                    <script src="https://cdn.jsdelivr.net/npm/bwip-js@3.0.4/dist/bwip-js-min.js"><\/script>
                    <style>${buildLabelCSS(s)}<\/style>
                </head>
                <body>
                    <div class="label-wrapper">
                        <div class="text-section">
                            <div class="info-shop">Jaffna Gold</div>
                            <div class="info-price">Rs.${retailPrice}</div>
                            <div class="info-barcode">${barcode}</div>
                        </div>
                        <div class="qr-section">
                            ${displaySize ? `<div class="info-qr-size">${displaySize}</div>` : ''}
                            <canvas id="qrcode"></canvas>
                        </div>
                    </div>
                    <script>
                        if (${s.showQR}) {
                            let bcid = '${s.barcodeType === 'qrcode_l' ? 'qrcode' : (s.barcodeType === 'qrcode_m' ? 'qrcode' : (s.barcodeType === 'code128' ? 'code128' : 'datamatrix'))}';
                            let opts = { text: '${barcode}', bcid: bcid, scale: 3, padding: 0 };
                            if ('${s.barcodeType}' === 'qrcode_l') opts.eclevel = 'L';
                            if ('${s.barcodeType}' === 'qrcode_m') opts.eclevel = 'M';
                            try { bwipjs.toCanvas(document.getElementById('qrcode'), opts); } catch(e) { console.error(e); }
                        }
                        setTimeout(() => { window.print(); }, 700);
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }

        function printBatchLabels() {
            if (typeof @this !== 'undefined') {
                @this.call('markAllBatchAsPrinted');
            }
            const rows = document.querySelectorAll('.fast-product-create-page tbody tr[data-barcode]');
            const labelData = [];

            rows.forEach(function(row) {
                const barcode = row.dataset.barcode ? row.dataset.barcode.trim() : '';
                if (barcode !== '') {
                    const price = row.dataset.price || '0.00';
                    const qty = parseInt(row.dataset.qty) || 1;
                    const size = row.dataset.size ? row.dataset.size.trim() : '';

                    for (let q = 0; q < qty; q++) {
                        labelData.push({ barcode: barcode, price: price, size: size });
                    }
                }
            });

            if (labelData.length === 0) {
                return;
            }

            const s = LABEL_SETTINGS;

            const labelsHtml = labelData.map((item, idx) => {
                const cleanSize = item.size ? String(item.size).trim() : '';
                const displaySize = cleanSize ? cleanSize.replace(/^(sz:?|size:?)\s*/i, '') : '';
                return `
                <div class="label-wrapper">
                    <div class="text-section">
                        <div class="info-shop">Jaffna Gold</div>
                        <div class="info-price">Rs.${item.price}</div>
                        <div class="info-barcode">${item.barcode}</div>
                    </div>
                    <div class="qr-section">
                        ${displaySize ? `<div class="info-qr-size">${displaySize}</div>` : ''}
                        <canvas id="batch-print-qr-${idx}"></canvas>
                    </div>
                </div>
                `;
            }).join('');

            const qrScripts = s.showQR ? labelData.map((item, idx) => {
                return `
                    try {
                        let opts_${idx} = { text: '${item.barcode}', bcid: '${s.barcodeType === 'qrcode_l' ? 'qrcode' : (s.barcodeType === 'qrcode_m' ? 'qrcode' : (s.barcodeType === 'code128' ? 'code128' : 'datamatrix'))}', scale: 3, padding: 0 };
                        if ('${s.barcodeType}' === 'qrcode_l') opts_${idx}.eclevel = 'L';
                        if ('${s.barcodeType}' === 'qrcode_m') opts_${idx}.eclevel = 'M';
                        bwipjs.toCanvas(document.getElementById('batch-print-qr-${idx}'), opts_${idx});
                    } catch(e) { console.error(e); }
                `;
            }).join('\n') : '';

            const printWindow = window.open('', '_blank', 'width=600,height=500');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Batch Labels (${labelData.length})</title>
                    <script src="https://cdn.jsdelivr.net/npm/bwip-js@3.0.4/dist/bwip-js-min.js"><\/script>
                    <style>
                        ${buildLabelCSS(s)}
                        .labels-container { display:flex; flex-direction:column; gap:0; }
                    </style>
                </head>
                <body>
                    <div class="labels-container">
                        ${labelsHtml}
                    </div>
                    <script>
                        ${qrScripts}
                        setTimeout(() => { window.print(); }, 900);
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }

        function renderQRCodes() {
            document.querySelectorAll('.batch-qr-code[data-barcode]').forEach(function(el) {
                const barcode = el.dataset.barcode ? el.dataset.barcode.trim() : '';
                if (!barcode) {
                    el.innerHTML = '';
                    el.dataset.renderedBarcode = '';
                    return;
                }
                if (el.dataset.renderedBarcode === barcode && el.querySelector('canvas')) {
                    return;
                }
                el.innerHTML = '';
                el.dataset.renderedBarcode = barcode;
                const canvas = document.createElement('canvas');
                el.appendChild(canvas);
                renderBarcodeToCanvas(canvas, barcode, LABEL_SETTINGS.barcodeType);
            });
        }

        function setupKeyboardListeners() {
            const costInput = document.getElementById('quick-cost-input');
            const qtyInput = document.getElementById('quick-qty-input');
            const sizeInput = document.getElementById('quick-size-input');
            const addBtn = document.getElementById('quick-add-btn');

            if (costInput && !costInput.hasAttribute('data-has-listener')) {
                costInput.setAttribute('data-has-listener', 'true');
                costInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (qtyInput) {
                            qtyInput.focus();
                            qtyInput.select();
                        }
                    }
                });
            }

            if (qtyInput && !qtyInput.hasAttribute('data-has-listener')) {
                qtyInput.setAttribute('data-has-listener', 'true');
                qtyInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const curSizeInput = document.getElementById('quick-size-input');
                        if (curSizeInput && curSizeInput.offsetParent !== null) {
                            curSizeInput.focus();
                            curSizeInput.select();
                        } else {
                            if (addBtn) addBtn.click();
                        }
                    }
                });
            }

            if (sizeInput && !sizeInput.hasAttribute('data-has-listener')) {
                sizeInput.setAttribute('data-has-listener', 'true');
                sizeInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (addBtn) addBtn.click();
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderQRCodes();
            setupKeyboardListeners();
        });

        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', () => {
                setTimeout(renderQRCodes, 100);
                setupKeyboardListeners();
            });
        }

        /* ==========================================================================
           Unsaved Changes Navigation Protection
           ========================================================================== */
        window.__bypassUnsavedWarning = false;

        function getUnsavedCount() {
            const container = document.getElementById('fast-product-create-container');
            return parseInt(container?.dataset?.unsavedCount || '0', 10);
        }

        function hasUnsavedProducts() {
            if (window.__bypassUnsavedWarning) return false;
            const unsavedInBatch = getUnsavedCount() > 0;
            const quickCostInput = document.getElementById('quick-cost-input');
            const hasPendingInput = quickCostInput && parseFloat(quickCostInput.value || '0') > 0;
            return unsavedInBatch || hasPendingInput;
        }

        function showUnsavedPrompt(targetUrl) {
            const count = getUnsavedCount();
            const productText = count === 1 ? '1 unsaved product' : `${count} unsaved products`;
            const countDisplay = count > 0 ? productText : 'entered product details';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Save changes before leaving?',
                    html: `
                        <div class="text-center py-1">
                            <div class="mb-3 text-warning">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                            </div>
                            <p class="fs-6 text-dark fw-semibold mb-1">
                                You have <span class="text-danger fw-bold">${countDisplay}</span> in this batch!
                            </p>
                            <p class="text-muted small mb-0">
                                If you leave without saving, all entered products will be lost. What would you like to do?
                            </p>
                        </div>
                    `,
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: '<i class="bi bi-check2-all me-1"></i> Save & Leave',
                    denyButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Discard & Leave',
                    cancelButtonText: '<i class="bi bi-x-lg me-1"></i> Stay on Page',
                    confirmButtonColor: '#10b981',
                    denyButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    focusCancel: true,
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0',
                        actions: 'd-flex gap-2 flex-wrap justify-content-center'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // User chose Save & Leave
                        Swal.fire({
                            title: 'Saving Products...',
                            html: `
                                <div class="py-3 text-center">
                                    <div class="spinner-border text-success mb-2" role="status"></div>
                                    <div class="small text-muted">Saving batch products and redirecting...</div>
                                </div>
                            `,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false
                        });

                        window.__bypassUnsavedWarning = true;
                        if (typeof @this !== 'undefined') {
                            @this.call('saveBatchAndRedirect', targetUrl).then((res) => {
                                // If save failed (returned false)
                                if (res === false) {
                                    window.__bypassUnsavedWarning = false;
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Save Failed',
                                        text: 'Could not save products. Please check error messages and try again.',
                                        confirmButtonColor: '#161b97'
                                    });
                                }
                            }).catch((err) => {
                                window.__bypassUnsavedWarning = false;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Save Error',
                                    text: err?.message || 'Error occurred while saving batch.',
                                    confirmButtonColor: '#161b97'
                                });
                            });
                        } else {
                            window.location.href = targetUrl;
                        }
                    } else if (result.isDenied) {
                        // User chose Discard & Leave
                        window.__bypassUnsavedWarning = true;
                        window.location.href = targetUrl;
                    } else {
                        // User chose Stay on Page: do nothing
                    }
                });
            } else {
                // Fallback to browser confirm if Swal is unavailable
                const save = confirm(`You have unsaved products! Click OK to save before leaving, or Cancel to discard.`);
                if (save) {
                    window.__bypassUnsavedWarning = true;
                    if (typeof @this !== 'undefined') {
                        @this.call('saveBatchAndRedirect', targetUrl);
                    } else {
                        window.location.href = targetUrl;
                    }
                } else {
                    const discard = confirm('Are you sure you want to discard unsaved products and leave?');
                    if (discard) {
                        window.__bypassUnsavedWarning = true;
                        window.location.href = targetUrl;
                    }
                }
            }
        }

        // Intercept all menu & navigation clicks in capture phase
        document.addEventListener('click', function(e) {
            if (window.__bypassUnsavedWarning) return;
            if (!hasUnsavedProducts()) return;

            const anchor = e.target.closest('a');
            if (!anchor) return;

            const href = anchor.getAttribute('href');
            if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) return;

            // Ignore Bootstrap toggles (dropdowns, accordion collapses, modals, offcanvas)
            if (anchor.hasAttribute('data-bs-toggle') || anchor.hasAttribute('data-bs-target')) return;
            if (anchor.getAttribute('role') === 'button' && href.startsWith('#')) return;

            // Ignore external new tab links and download links
            if (anchor.target === '_blank' || anchor.hasAttribute('download')) return;

            // Ignore same page hash links
            const currentUrlBase = window.location.href.split('#')[0];
            const targetUrl = anchor.href;
            if (targetUrl.split('#')[0] === currentUrlBase && targetUrl.includes('#')) return;

            // Block navigation and show prompt
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            showUnsavedPrompt(targetUrl);
        }, true);

        // Native browser unload protection (tab close, browser close, reload)
        window.addEventListener('beforeunload', function(e) {
            if (window.__bypassUnsavedWarning) return;
            if (hasUnsavedProducts()) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        // Livewire SPA navigation interception
        document.addEventListener('livewire:navigate', function(e) {
            if (window.__bypassUnsavedWarning) return;
            if (hasUnsavedProducts()) {
                e.preventDefault();
                const url = e.detail?.url;
                if (url) {
                    showUnsavedPrompt(url);
                }
            }
        });

        /* ==========================================================================
           Auto-Save Staging Stage & Crash / Power-Cut Recovery System
           ========================================================================== */
        const DRAFT_STORAGE_KEY = 'jaffnagold_fast_product_stage_draft';

        window.saveFastProductDraftToStorage = function(payload) {
            if (!payload || !payload.items || payload.items.length === 0) {
                window.clearFastProductDraftFromStorage();
                return;
            }
            try {
                localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(payload));

                const indicator = document.getElementById('draft-autosave-indicator');
                const text = document.getElementById('draft-autosave-text');
                if (indicator && text) {
                    indicator.classList.remove('d-none');
                    indicator.classList.add('d-inline-flex');
                    text.textContent = `Auto-saved Stage (${payload.total_items} items · ${payload.formatted_time || 'Just now'})`;
                }
            } catch (e) {
                console.warn('Could not save draft to localStorage:', e);
            }
        };

        window.clearFastProductDraftFromStorage = function() {
            try {
                localStorage.removeItem(DRAFT_STORAGE_KEY);
                const indicator = document.getElementById('draft-autosave-indicator');
                if (indicator) {
                    indicator.classList.add('d-none');
                    indicator.classList.remove('d-inline-flex');
                }
                const banner = document.getElementById('draft-resume-banner');
                if (banner) {
                    banner.classList.add('d-none');
                }
            } catch (e) {}
        };

        window.checkAndPromptDraftRecovery = function() {
            try {
                const draftRaw = localStorage.getItem(DRAFT_STORAGE_KEY);
                if (!draftRaw) return;
                const draft = JSON.parse(draftRaw);
                if (!draft || !draft.items || draft.items.length === 0) return;

                // Check if Livewire already has items loaded in current DOM
                const container = document.getElementById('fast-product-create-container');
                const currentUnsaved = parseInt(container?.dataset?.unsavedCount || '0', 10);
                const tableRows = document.querySelectorAll('.fast-product-create-page tbody tr[data-barcode]');

                if (tableRows.length > 0 || currentUnsaved > 0) {
                    return; // Batch already has active items
                }

                // Show top recovery banner
                const banner = document.getElementById('draft-resume-banner');
                const countBadge = document.getElementById('draft-banner-count');
                const detailsText = document.getElementById('draft-banner-details');

                if (banner && countBadge && detailsText) {
                    countBadge.textContent = `${draft.total_items || draft.items.length} items`;
                    const supplierInfo = draft.supplier_name ? ` · Supplier: <strong>${draft.supplier_name}</strong>` : '';
                    const costInfo = draft.total_cost ? ` · Total Cost: <strong>Rs.${Number(draft.total_cost).toLocaleString()}</strong>` : '';
                    detailsText.innerHTML = `Auto-saved on <strong>${draft.formatted_time || 'Previous Session'}</strong>${supplierInfo}${costInfo}. Click resume to restore all <strong>${draft.total_items || draft.items.length} products</strong> without losing any data.`;
                    banner.classList.remove('d-none');
                }

                // Show interactive prompt if Swal is available
                if (typeof Swal !== 'undefined' && !window.__draftPromptShown) {
                    window.__draftPromptShown = true;
                    Swal.fire({
                        title: 'Resume Unsaved Batch?',
                        html: `
                            <div class="text-center py-2">
                                <div class="mb-3 text-warning">
                                    <i class="bi bi-lightning-charge-fill" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-2">
                                    Found <span class="text-primary fw-bold">${draft.total_items || draft.items.length} products</span> from your previous session!
                                </h6>
                                <p class="text-muted small mb-0">
                                    Auto-saved on <strong>${draft.formatted_time || 'Previous Session'}</strong> before power or network interrupt. Would you like to resume where you left off?
                                </p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: `<i class="bi bi-arrow-repeat me-1"></i> Resume Batch (${draft.total_items || draft.items.length} Items)`,
                        cancelButtonText: '<i class="bi bi-trash me-1"></i> Discard Draft',
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#64748b',
                        allowOutsideClick: true,
                        customClass: {
                            popup: 'rounded-4 shadow-lg border-0'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.resumeFastProductDraft();
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            window.discardFastProductDraft();
                        }
                    });
                }
            } catch (e) {
                console.warn('Error checking draft recovery:', e);
            }
        };

        window.resumeFastProductDraft = function() {
            try {
                const draftRaw = localStorage.getItem(DRAFT_STORAGE_KEY);
                if (!draftRaw) return;
                const draft = JSON.parse(draftRaw);
                if (!draft || !draft.items) return;

                if (typeof @this !== 'undefined') {
                    @this.call('resumeDraft', draft).then(() => {
                        const banner = document.getElementById('draft-resume-banner');
                        if (banner) banner.classList.add('d-none');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Batch Resumed!',
                                text: `Successfully restored ${draft.total_items || draft.items.length} products.`,
                                timer: 2000,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        }
                    });
                }
            } catch (e) {
                console.error('Failed to resume draft:', e);
            }
        };

        window.discardFastProductDraft = function() {
            window.clearFastProductDraftFromStorage();
            if (typeof @this !== 'undefined') {
                @this.call('discardDraft');
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Draft Discarded',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        };

        // Instant Excel filename autofill handler for Purchasing Code / Batch Name
        window.handleExcelFileNameAutoFill = function(fileInput) {
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) return;
            const file = fileInput.files[0];
            if (!file || !file.name) return;

            // Strip extension (.xlsx, .xls, .csv, etc.)
            const baseName = file.name.replace(/\.[^/.]+$/, '').trim();
            if (!baseName) return;

            // Immediately update visual inputs without waiting for upload
            const mainInput = document.getElementById('purchasing-number-input');
            const modalInput = document.getElementById('modal-purchasing-code-input');
            if (mainInput) {
                mainInput.value = baseName;
                mainInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (modalInput) {
                modalInput.value = baseName;
                modalInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // Immediately update Livewire component state
            try {
                if (typeof @this !== 'undefined' && @this.set) {
                    @this.set('purchasingNumber', baseName);
                } else if (window.Livewire) {
                    const root = fileInput.closest('[wire\\:id]');
                    if (root) {
                        const comp = window.Livewire.find(root.getAttribute('wire:id'));
                        if (comp) comp.set('purchasingNumber', baseName);
                    }
                }
            } catch (err) {
                console.warn('Livewire auto-fill set failed:', err);
            }
        };

        // Livewire event listeners for auto-save staging
        window.addEventListener('draft-updated', function(e) {
            const payload = e.detail?.[0] || e.detail;
            window.saveFastProductDraftToStorage(payload);
        });

        window.addEventListener('draft-cleared', function() {
            window.clearFastProductDraftFromStorage();
        });

        window.addEventListener('draft-resumed', function(e) {
            const banner = document.getElementById('draft-resume-banner');
            if (banner) banner.classList.add('d-none');
        });

        // Run recovery check on initial load & after Livewire updates
        setTimeout(window.checkAndPromptDraftRecovery, 350);
    </script>
    @endpush
</div>

