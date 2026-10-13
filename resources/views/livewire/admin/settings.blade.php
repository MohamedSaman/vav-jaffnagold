<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-gear-fill text-success fs-2"></i>
        <div class="ms-3">
            <h1 class="h3 fw-bold mb-0">System Settings</h1>
            <p class="text-muted mb-0">Manage all system configurations.</p>
        </div>
    </div>

    {{-- Accordion --}}
    <div class="accordion" id="settingsAccordion">

        {{-- Staff Type Permissions Accordion --}}
        <div class="accordion-item border-0 mb-4 shadow-sm rounded-4">
            <h2 class="accordion-header" id="headingStaffPermissions">
                <button class="accordion-button fw-semibold bg-white text-dark rounded-4"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseStaffPermissions" aria-expanded="true"
                    aria-controls="collapseStaffPermissions">
                    <i class="bi bi-person-gear fs-5 me-3 text-primary"></i>
                    Staff Type Permissions
                </button>
            </h2>
            <div id="collapseStaffPermissions" class="accordion-collapse collapse show"
                aria-labelledby="headingStaffPermissions" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    {{-- Staff Type Selector --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Staff Type</label>
                            <select wire:model.live="selectedStaffType" class="form-select">
                                @foreach($this->staffTypes as $typeKey => $typeName)
                                    <option value="{{ $typeKey }}">{{ $typeName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button class="btn btn-outline-secondary me-2" wire:click="resetStaffTypePermissions">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Defaults
                            </button>
                            <button class="btn btn-primary" wire:click="saveStaffTypePermissions">
                                <i class="bi bi-check-circle me-1"></i> Save Permissions
                            </button>
                        </div>
                    </div>

                    {{-- Staff Type Info Card --}}
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>{{ $this->staffTypes[$selectedStaffType] ?? 'Staff' }}:</strong>
                        @if($selectedStaffType === 'salesman')
                            Salesmen can create sales orders, view their own sales, create returns, view customer dues (no payment collection), and add expenses.
                        @elseif($selectedStaffType === 'delivery_man')
                            Delivery men can view pending/completed deliveries, confirm deliveries, collect payments, view customer dues, and add expenses.
                        @elseif($selectedStaffType === 'shop_staff')
                            Shop staff can view products (without cost prices), only wholesale and retail prices are visible.
                        @endif
                    </div>

                    {{-- Permissions Grid --}}
                    <div class="row">
                        @foreach($this->permissionCategories as $category => $permissions)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="bi bi-folder2-open me-2 text-primary"></i>{{ $category }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @foreach($permissions as $permKey)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               id="perm_{{ $permKey }}"
                                               {{ in_array($permKey, $staffTypePermissions) ? 'checked' : '' }}
                                               wire:click="togglePermission('{{ $permKey }}')">
                                        <label class="form-check-label" for="perm_{{ $permKey }}">
                                            {{ $this->availablePermissions[$permKey] ?? $permKey }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Pricing Visibility Note --}}
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Pricing Visibility:</strong> 
                        <ul class="mb-0 mt-2">
                            <li><strong>Salesman & Delivery Man:</strong> Should only see distributor prices.</li>
                            <li><strong>Shop Staff:</strong> Can see wholesale and retail prices, but NOT cost prices.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expense Categories Management Accordion --}}
        <div class="accordion-item border-0 mb-4 shadow-sm rounded-4">
            <h2 class="accordion-header" id="headingExpenseCategories">
                <button class="accordion-button fw-semibold bg-white text-dark rounded-4 collapsed"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseExpenseCategories" aria-expanded="false"
                    aria-controls="collapseExpenseCategories">
                    <i class="bi bi-tag fs-5 me-3 text-info"></i>
                    Expense Categories & Types
                </button>
            </h2>
            <div id="collapseExpenseCategories" class="accordion-collapse collapse"
                aria-labelledby="headingExpenseCategories" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    {{-- Add Button --}}
                    <div class="mb-3 d-flex justify-content-end">
                        <button class="btn btn-info shadow-sm" wire:click="openAddCategoryModal">
                            <i class="bi bi-plus-circle"></i> Add Expense Category/Type
                        </button>
                    </div>
                    @php
                        $allCategories = \App\Models\ExpenseCategory::orderBy('expense_category')->orderBy('type')->get()->groupBy('expense_category');
                    @endphp

                    @if($allCategories->isNotEmpty())
                    <div class="row">
                        @foreach($allCategories as $categoryName => $items)
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-info bg-opacity-10">
                                    <h6 class="mb-0 fw-bold text-dark">
                                        <i class="bi bi-folder2-open me-2"></i>{{ $categoryName }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        @foreach($items as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="bi bi-tag me-2 text-muted"></i>{{ $item->type }}</span>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    wire:click="confirmDeleteCategoryType({{ $item->id }})"
                                                    title="Delete this type">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                        No expense categories found.
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- POS Settings Accordion --}}
        <div class="accordion-item border-0 mb-4 shadow-sm rounded-4">
            <h2 class="accordion-header" id="headingPOSSettings">
                <button class="accordion-button fw-semibold bg-white text-dark rounded-4 collapsed"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsePOSSettings" aria-expanded="false"
                    aria-controls="collapsePOSSettings">
                    <i class="bi bi-cart-check fs-5 me-3 text-warning"></i>
                    POS & Warranty Settings
                </button>
            </h2>
            <div id="collapsePOSSettings" class="accordion-collapse collapse"
                aria-labelledby="headingPOSSettings" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2 text-primary"></i>Warranty Configuration</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Minimum Amount for 6-Month Warranty</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rs.</span>
                                            <input type="number" wire:model="warranty_min_amount" class="form-control @error('warranty_min_amount') is-invalid @enderror" placeholder="1000">
                                            @error('warranty_min_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="text-muted mt-1 d-block">Products with a final price (after discount) equal to or greater than this amount will automatically get a 6-month warranty.</small>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-warning text-white" wire:click="savePOSSettings">
                                            <i class="bi bi-save me-1"></i> Save POS Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Billing Price Guide & Helper Accordion --}}
        <div class="accordion-item border-0 mb-4 shadow-sm rounded-4">
            <h2 class="accordion-header" id="headingPriceHelper">
                <button class="accordion-button fw-semibold bg-white text-dark rounded-4 collapsed"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsePriceHelper" aria-expanded="false"
                    aria-controls="collapsePriceHelper">
                    <i class="bi bi-tag-fill fs-5 me-3 text-warning"></i>
                    Billing Price Guide & Helper
                </button>
            </h2>
            <div id="collapsePriceHelper" class="accordion-collapse collapse"
                aria-labelledby="headingPriceHelper" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="bi bi-cash-stack me-2 text-primary"></i>Reference Price Ranges</h6>
                            <p class="text-muted small mb-0">Configure default price ranges displayed in POS Store Billing when staff click the Price Helper button.</p>
                        </div>
                        <button class="btn btn-warning text-dark fw-bold shadow-sm" wire:click="openAddPriceHelperModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Price Guide Entry
                        </button>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            @if(count($priceHelpers) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Item / Product Name</th>
                                            <th>Category</th>
                                            <th class="text-end">Min Price</th>
                                            <th class="text-end">Max Price</th>
                                            <th>Price Range</th>
                                            <th>Notes / Details</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-end pe-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($priceHelpers as $ph)
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark">{{ $ph->item_name }}</td>
                                            <td>
                                                @if($ph->category)
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">{{ $ph->category }}</span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end font-monospace text-dark fw-semibold">Rs. {{ number_format($ph->min_price, 2) }}</td>
                                            <td class="text-end font-monospace text-dark fw-semibold">Rs. {{ number_format($ph->max_price, 2) }}</td>
                                            <td>
                                                <span class="badge bg-warning bg-opacity-20 text-dark border border-warning border-opacity-50 fw-bold px-2 py-1">
                                                    Rs. {{ number_format($ph->min_price, 0) }} - {{ number_format($ph->max_price, 0) }}
                                                </span>
                                            </td>
                                            <td class="small text-muted">{{ $ph->notes ?: '—' }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm {{ $ph->is_active ? 'btn-success' : 'btn-outline-secondary' }} py-0 px-2 rounded-pill text-xs"
                                                    wire:click="togglePriceHelperStatus({{ $ph->id }})" title="Click to toggle active status">
                                                    {{ $ph->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </td>
                                            <td class="text-end pe-3">
                                                <button class="btn btn-sm btn-outline-primary me-1" wire:click="editPriceHelper({{ $ph->id }})" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" wire:click="deletePriceHelper({{ $ph->id }})"
                                                    wire:confirm="Are you sure you want to delete this price guide entry?" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-tag fs-2 d-block mb-2 text-warning"></i>
                                <p class="mb-0">No Price Guide entries yet. Click "Add Price Guide Entry" to create one.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Print Label Settings Accordion (no data-bs-parent so it stays open independently) --}}
        <div class="accordion-item border-0 mb-4 shadow-sm rounded-4">
            <h2 class="accordion-header" id="headingPrintLabel">
                <button class="accordion-button fw-semibold bg-white text-dark rounded-4"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsePrintLabel" aria-expanded="true"
                    aria-controls="collapsePrintLabel">
                    <i class="bi bi-printer fs-5 me-3 text-danger"></i>
                    Print Label Settings
                </button>
            </h2>
            <div id="collapsePrintLabel" class="accordion-collapse collapse show"
                aria-labelledby="headingPrintLabel">
                <div class="accordion-body">
                    <div class="row g-4">

                        {{-- Label Dimensions --}}
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-aspect-ratio me-2 text-primary"></i> Label Dimensions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <label class="form-label fw-semibold small">Printable Body Width (mm)
                                            <span class="text-muted fw-normal">— main body, no tail</span>
                                        </label>
                                        <input type="number" id="lbl_width" value="{{ $label_width }}"
                                            class="form-control" min="10" max="300" step="0.5"
                                            oninput="renderSettingsPreview()">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label fw-semibold small">Label Height (mm)
                                            <span class="text-muted fw-normal">— feed direction</span>
                                        </label>
                                        <input type="number" id="lbl_height" value="{{ $label_height }}"
                                            class="form-control" min="5" max="100" step="0.5"
                                            oninput="renderSettingsPreview()">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label fw-semibold small">Inner Padding (mm)</label>
                                        <input type="number" id="lbl_padding" value="{{ $label_padding }}"
                                            class="form-control" min="0" max="10" step="0.1"
                                            oninput="renderSettingsPreview()">
                                    </div>
                                    <hr class="my-2">
                                    <p class="small fw-semibold mb-2 text-primary"><i class="bi bi-layout-split me-1"></i>Section Layout</p>
                                    <div class="mb-2">
                                        <label class="form-label fw-semibold small">Text Section Width (mm)
                                            <span class="text-muted fw-normal">— fold line position</span>
                                        </label>
                                        <input type="number" id="lbl_text_width" value="{{ $label_text_width }}"
                                            class="form-control" min="5" max="290" step="0.5"
                                            oninput="renderSettingsPreview()">
                                    </div>
                                    <hr class="my-2">
                                    <p class="small fw-semibold mb-2 text-muted"><i class="bi bi-eye me-1"></i>Tail (preview only)</p>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold small">Tail Length (mm)</label>
                                            <input type="number" id="lbl_tail_width" value="{{ $label_tail_width }}"
                                                class="form-control form-control-sm" min="0" max="100" step="0.5"
                                                oninput="renderSettingsPreview()">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold small">Tail Height (mm)</label>
                                            <input type="number" id="lbl_tail_height" value="{{ $label_tail_height }}"
                                                class="form-control form-control-sm" min="0" max="30" step="0.5"
                                                oninput="renderSettingsPreview()">
                                        </div>
                                    </div>
                                    <small class="text-muted">Tail = string hole strip (right side, not printed)</small>
                                </div>
                            </div>
                        </div>

                        {{-- Font Sizes --}}
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-type me-2 text-success"></i> Font Sizes & Visibility</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Shop Name Font (pt)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="lbl_font_shop" value="{{ $label_font_shop }}"
                                                class="form-control" min="1" max="30" step="0.5"
                                                oninput="renderSettingsPreview()">
                                            <div class="input-group-text">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="lbl_show_shop" {{ $label_show_shop ? 'checked' : '' }}
                                                        onchange="renderSettingsPreview()">
                                                    <label class="form-check-label small" for="lbl_show_shop">Show</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Price Font (pt)</label>
                                        <input type="number" id="lbl_font_price" value="{{ $label_font_price }}"
                                            class="form-control" min="1" max="30" step="0.5"
                                            oninput="renderSettingsPreview()">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Barcode Text Font (pt)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="lbl_font_barcode" value="{{ $label_font_barcode }}"
                                                class="form-control" min="1" max="20" step="0.5"
                                                oninput="renderSettingsPreview()">
                                            <div class="input-group-text">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="lbl_show_barcode" {{ $label_show_barcode_text ? 'checked' : '' }}
                                                        onchange="renderSettingsPreview()">
                                                    <label class="form-check-label small" for="lbl_show_barcode">Show</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">QR Code Size (mm)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="lbl_qr_size" value="{{ $label_qr_size }}"
                                                class="form-control" min="2" max="50" step="0.5"
                                                oninput="renderSettingsPreview()">
                                            <div class="input-group-text">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="lbl_show_qr" {{ $label_show_qr ? 'checked' : '' }}
                                                        onchange="renderSettingsPreview()">
                                                    <label class="form-check-label small" for="lbl_show_qr">Show</label>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted">Max = label height ({{ $label_height }}mm). QR sits in right section.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Barcode / 2D Code Type</label>
                                        <select id="lbl_barcode_type" class="form-select form-select-sm" onchange="renderSettingsPreview()">
                                            <option value="datamatrix" {{ $label_barcode_type === 'datamatrix' ? 'selected' : '' }}>
                                                🥇 Data Matrix (ECC 200) — Best for Small Jewelry Tags
                                            </option>
                                            <option value="qrcode_l" {{ $label_barcode_type === 'qrcode_l' ? 'selected' : '' }}>
                                                🥈 QR Code (Level L) — Bold & Large Dots
                                            </option>
                                            <option value="qrcode_m" {{ $label_barcode_type === 'qrcode_m' ? 'selected' : '' }}>
                                                QR Code (Level M Standard)
                                            </option>
                                            <option value="code128" {{ $label_barcode_type === 'code128' ? 'selected' : '' }}>
                                                1D Linear Barcode (Code 128)
                                            </option>
                                        </select>
                                        <small class="text-muted">Data Matrix requires 60% less space than QR and scans reliably on tiny tags.</small>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label fw-semibold small">Font Style</label>
                                        <select id="lbl_font_family" class="form-select form-select-sm" onchange="renderSettingsPreview()">
                                            <option value="Courier New"    {{ $label_font_family === 'Courier New'    ? 'selected' : '' }}>Courier New — monospace (label style)</option>
                                            <option value="Consolas"       {{ $label_font_family === 'Consolas'       ? 'selected' : '' }}>Consolas — monospace (OCR-B style)</option>
                                            <option value="Lucida Console" {{ $label_font_family === 'Lucida Console' ? 'selected' : '' }}>Lucida Console — monospace</option>
                                            <option value="OCR A Extended" {{ $label_font_family === 'OCR A Extended' ? 'selected' : '' }}>OCR A Extended — scanner font</option>
                                            <option value="Arial Narrow"   {{ $label_font_family === 'Arial Narrow'   ? 'selected' : '' }}>Arial Narrow — condensed</option>
                                            <option value="Arial"          {{ $label_font_family === 'Arial'          ? 'selected' : '' }}>Arial — sans-serif</option>
                                            <option value="Verdana"        {{ $label_font_family === 'Verdana'        ? 'selected' : '' }}>Verdana — wide & readable</option>
                                            <option value="Roboto Mono"    {{ $label_font_family === 'Roboto Mono'    ? 'selected' : '' }}>Roboto Mono — technical monospace</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Live Preview --}}
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-eye me-2 text-warning"></i> Live Preview
                                        <span class="badge bg-secondary ms-1" style="font-size:9px;">actual label shape</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="settingsPreviewOuter" style="background:#c8c8c8; border-radius:6px; padding:14px; width:100%; overflow:hidden;">
                                        <div id="settingsLabelPreview"></div>
                                    </div>
                                    <div id="settingsPreviewDims" class="text-muted mt-2" style="font-size:10px; text-align:center;"></div>
                                    <div id="settingsWarnings" class="mt-1" style="font-size:11px;"></div>
                                    <div class="mt-3 d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="applyRecommended()" title="Reset to smart defaults for current label size">
                                            <i class="bi bi-magic me-1"></i> Recommended
                                        </button>
                                        <button class="btn btn-danger flex-grow-1" onclick="saveLabelSettingsJS()">
                                            <i class="bi bi-save me-1"></i> Save Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="alert alert-info mt-4 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>How to use:</strong> After saving, go to <strong>Barcode Print Center</strong> and click Print.
                        In the browser print dialog set <strong>Paper size = Custom {{ $label_width }}×{{ $label_height }}mm</strong>
                        and <strong>Margins = None</strong>. Disable headers/footers. The tail strip is not printed — only the <strong>{{ $label_width }}mm body</strong> prints.
                    </div>
                </div>
            </div>
        </div>

        {{-- SMS Gateway, Usage & Billing Settings Accordion --}}
        <div class="accordion-item border-0 mb-4 shadow-sm rounded-4">
            <h2 class="accordion-header" id="headingSmsSettings">
                <button class="accordion-button fw-semibold bg-white text-dark rounded-4 collapsed"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseSmsSettings" aria-expanded="false"
                    aria-controls="collapseSmsSettings">
                    <i class="bi bi-chat-dots-fill fs-5 me-3 text-primary"></i>
                    SMS Gateway, Usage & Billing Settings
                    @if($smsStats['isFinished'])
                        <span class="badge bg-danger ms-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>Balance Finished</span>
                    @elseif($smsStats['isLow'])
                        <span class="badge bg-warning text-dark ms-2"><i class="bi bi-exclamation-circle-fill me-1"></i>Low Balance</span>
                    @else
                        <span class="badge bg-success ms-2"><i class="bi bi-check-circle-fill me-1"></i>Active</span>
                    @endif
                </button>
            </h2>
            <div id="collapseSmsSettings" class="accordion-collapse collapse"
                aria-labelledby="headingSmsSettings" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">
                    {{-- Low / Finished Balance Alert Box --}}
                    @if($smsStats['isLow'] || $smsStats['isFinished'])
                    <div class="alert {{ $smsStats['isFinished'] ? 'alert-danger' : 'alert-warning' }} border-0 shadow-sm rounded-3 mb-4 p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill fs-2 me-3 {{ $smsStats['isFinished'] ? 'text-danger' : 'text-warning' }}"></i>
                            <div>
                                <h6 class="fw-bold mb-1 {{ $smsStats['isFinished'] ? 'text-danger' : 'text-dark' }}">
                                    ⚠️ SMS Balance {{ $smsStats['isFinished'] ? 'Exhausted (Rs. 0.00)' : 'Running Low (Rs. ' . number_format($smsStats['balance'], 2) . ')' }}
                                </h6>
                                <p class="mb-0 small text-muted">
                                    Configured rate is <strong>Rs. {{ number_format($smsStats['rate'], 2) }} / SMS</strong>.
                                    <span class="text-danger fw-bold">If you continue sending messages with low/exhausted balance, double charge (Rs. {{ number_format($smsStats['rate'] * 2, 2) }}) applies.</span>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" wire:click="requestSmsTopup" class="btn btn-primary shadow-sm fw-semibold">
                                <i class="bi bi-chat-text-fill me-1"></i> Request Top-Up via SMS ({{ $smsStats['contact'] }})
                            </button>
                            <a href="tel:0759037101" class="btn btn-outline-dark shadow-sm fw-semibold">
                                <i class="bi bi-telephone me-1"></i> Call Provider (0759037101)
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Top KPI Cards --}}
                    <div class="row g-3 mb-4">
                        {{-- 1. Balance --}}
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-3 bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small fw-semibold text-uppercase">Account Balance</span>
                                        <div class="p-2 rounded-circle {{ $smsStats['isFinished'] ? 'bg-danger text-white' : ($smsStats['isLow'] ? 'bg-warning text-dark' : 'bg-success text-white') }}">
                                            <i class="bi bi-wallet2 fs-5"></i>
                                        </div>
                                    </div>
                                    <h3 class="fw-bold mb-1 {{ $smsStats['isFinished'] ? 'text-danger' : ($smsStats['isLow'] ? 'text-warning' : 'text-success') }}">
                                        Rs. {{ number_format($smsStats['balance'], 2) }}
                                    </h3>
                                    <div class="small text-muted d-flex justify-content-between">
                                        <span>Est. ~{{ number_format($smsStats['remainingEstimate']) }} SMS left</span>
                                        <span class="fw-semibold">Total: Rs. {{ number_format($smsStats['totalTopup'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Total SMS Sent --}}
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-3 bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small fw-semibold text-uppercase">Total SMS Sent</span>
                                        <div class="p-2 rounded-circle bg-primary text-white">
                                            <i class="bi bi-send-check fs-5"></i>
                                        </div>
                                    </div>
                                    <h3 class="fw-bold mb-1 text-primary">
                                        {{ number_format($smsStats['sentCount']) }}
                                    </h3>
                                    <div class="small text-muted">
                                        <span>Total message parts delivered</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Per SMS Charge --}}
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-3 bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small fw-semibold text-uppercase">Per SMS Charge</span>
                                        <div class="p-2 rounded-circle bg-warning text-dark">
                                            <i class="bi bi-tag-fill fs-5"></i>
                                        </div>
                                    </div>
                                    <h3 class="fw-bold mb-1 text-dark">
                                        Rs. {{ number_format($smsStats['rate'], 2) }}
                                    </h3>
                                    <div class="small text-muted">
                                        <span>Double charge: Rs. {{ number_format($smsStats['rate'] * 2, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Total Spent Amount --}}
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100 rounded-3 bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small fw-semibold text-uppercase">Total Cost Spent</span>
                                        <div class="p-2 rounded-circle bg-info text-white">
                                            <i class="bi bi-cash-stack fs-5"></i>
                                        </div>
                                    </div>
                                    <h3 class="fw-bold mb-1 text-info">
                                        Rs. {{ number_format($smsStats['totalCost'], 2) }}
                                    </h3>
                                    <div class="small text-muted">
                                        <span>Cumulative spent on SMS</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Bar --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 p-3 bg-light rounded-3">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" wire:click="requestSmsTopup" class="btn btn-primary shadow-sm fw-semibold">
                                <i class="bi bi-chat-text-fill me-1"></i> Request Top-Up via SMS ({{ $smsStats['contact'] }})
                            </button>
                            <a href="tel:0759037101" class="btn btn-outline-dark shadow-sm fw-semibold">
                                <i class="bi bi-telephone me-1"></i> Call Provider (0759037101)
                            </a>
                            <button type="button" class="btn btn-outline-primary shadow-sm" wire:click="openTestSmsModal">
                                <i class="bi bi-chat-dots me-1"></i> Send Test SMS
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" wire:click="checkGatewayStatus">
                                <i class="bi bi-cloud-arrow-down me-1"></i> Check SMSlenz Gateway
                            </button>
                            @if($sms_gateway_balance !== null)
                                <span class="badge bg-primary px-2 py-1">Gateway: Rs. {{ $sms_gateway_balance }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        {{-- SMS Service Provider & Pricing Policy Card (Locked & Managed by Provider) --}}
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100 rounded-3">
                                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-shield-lock-fill me-2 text-primary"></i>SMS Service Provider & Pricing
                                    </h6>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-lock-fill me-1"></i>Locked by Provider
                                    </span>
                                </div>
                                <div class="card-body">
                                    {{-- Service Provider Notice --}}
                                    <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-3 p-3 mb-3">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-info-circle-fill text-primary fs-5 mt-1"></i>
                                            <div class="small">
                                                <div class="fw-bold text-dark mb-1">Service Provided Exclusively by 0759037101</div>
                                                <div class="text-muted">
                                                    SMS rates, balance deposits, and gateway settings are configured and managed directly by service provider <strong>0759037101</strong>. Store users cannot edit rates or self-topup. Contact the provider below for credit recharge.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Read-Only Rate & Settings Grid --}}
                                    <div class="row g-3 mb-3">
                                        <div class="col-sm-6">
                                            <div class="p-3 rounded-3 bg-light border h-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted small fw-semibold">Per Message Charge</span>
                                                    <span class="badge bg-white text-muted border small"><i class="bi bi-lock me-1"></i>Fixed</span>
                                                </div>
                                                <div class="fs-5 fw-bold text-dark">Rs. {{ number_format($smsStats['rate'], 2) }}</div>
                                                <div class="text-muted" style="font-size: 11px;">Fixed charge deducted per single SMS message part.</div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="p-3 rounded-3 bg-light border h-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted small fw-semibold">Double Charge Rate</span>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle small">Policy</span>
                                                </div>
                                                <div class="fs-5 fw-bold text-danger">Rs. {{ number_format($smsStats['rate'] * 2, 2) }}</div>
                                                <div class="text-muted" style="font-size: 11px;">Applies when sending with balance ≤ Rs. {{ number_format($smsStats['threshold'] ?? 50, 2) }}.</div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="p-3 rounded-3 bg-light border h-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted small fw-semibold">Low Balance Threshold</span>
                                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle small">Warning</span>
                                                </div>
                                                <div class="fs-5 fw-bold text-dark">Rs. {{ number_format($smsStats['threshold'] ?? 50, 2) }}</div>
                                                <div class="text-muted" style="font-size: 11px;">Triggers low balance warning popup and provider topup prompt.</div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="p-3 rounded-3 bg-light border h-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted small fw-semibold">Sender ID</span>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle small">Verified</span>
                                                </div>
                                                <div class="fs-5 fw-bold text-dark">{{ $sms_sender_id ?: 'Jaffna Gold' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">Registered SMS gateway sender mask header.</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Service Provider Contact & Quick Actions --}}
                                    <div class="p-3 rounded-3 border bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <span class="text-muted small fw-semibold">Direct Service Provider</span>
                                                <div class="fs-5 fw-bold text-success">
                                                    <i class="bi bi-person-check-fill me-1"></i> 0759037101
                                                </div>
                                            </div>
                                            <span class="badge bg-success text-white px-2 py-1">
                                                <i class="bi bi-patch-check-fill me-1"></i>Official Provider
                                            </span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 pt-2">
                                            <button type="button" wire:click="requestSmsTopup" class="btn btn-primary flex-grow-1 shadow-sm fw-semibold">
                                                <i class="bi bi-chat-text-fill me-1"></i> Request Top-Up via SMS
                                            </button>
                                            <a href="tel:0759037101" class="btn btn-outline-dark shadow-sm fw-semibold">
                                                <i class="bi bi-telephone me-1"></i> Call Provider
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Recent Top-Ups List --}}
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100 rounded-3">
                                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-clock-history me-2 text-success"></i>Recent Balance Top-Ups
                                    </h6>
                                    <span class="badge bg-light text-muted border small">
                                        <i class="bi bi-shield-check me-1 text-success"></i>Credited by Provider
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    @if($smsTopups->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3">Date</th>
                                                    <th>Amount</th>
                                                    <th>Notes</th>
                                                    <th>Credited By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($smsTopups as $topup)
                                                <tr>
                                                    <td class="ps-3 small text-muted">{{ $topup->created_at->format('M d, Y h:i A') }}</td>
                                                    <td class="fw-bold text-success">+Rs. {{ number_format($topup->amount, 2) }}</td>
                                                    <td class="small text-muted">{{ $topup->notes ?: '—' }}</td>
                                                    <td class="small">
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                            {{ $topup->user ? $topup->user->name : 'Provider 0759037101' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="text-center py-4 text-muted small">
                                        No top-up records found. Balance deposits are credited by service provider <strong>0759037101</strong> upon request.
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SMS Logs / History Table --}}
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-table me-2 text-primary"></i>SMS Dispatch History & Charges
                                    </h6>
                                </div>
                                <div class="col-md-8">
                                    <div class="row g-2 justify-content-end">
                                        <div class="col-sm-5">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                                <input type="text" wire:model.live.debounce.300ms="smsSearch" class="form-control" placeholder="Search phone or text...">
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <select wire:model.live="smsStatusFilter" class="form-select form-select-sm">
                                                <option value="">All Statuses</option>
                                                <option value="sent">Sent</option>
                                                <option value="failed">Failed</option>
                                                <option value="mocked">Mocked</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="date" wire:model.live="smsDateFilter" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($smsLogs->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3" style="width: 170px;">Date & Time</th>
                                            <th style="width: 150px;">Recipient</th>
                                            <th>Message</th>
                                            <th class="text-center" style="width: 80px;">Parts</th>
                                            <th class="text-end" style="width: 120px;">Charge (Rs.)</th>
                                            <th class="text-center" style="width: 100px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($smsLogs as $log)
                                        <tr>
                                            <td class="ps-3 small text-muted">
                                                {{ $log->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="fw-semibold text-dark">
                                                {{ $log->recipient }}
                                            </td>
                                            <td class="small text-muted" style="max-width: 380px;">
                                                <span title="{{ $log->message }}">
                                                    {{ \Illuminate\Support\Str::limit($log->message, 85) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary rounded-pill">{{ $log->sms_count }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold {{ $log->is_double_charge ? 'text-danger' : 'text-dark' }}">
                                                    Rs. {{ number_format($log->total_cost, 2) }}
                                                </div>
                                                @if($log->is_double_charge)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 9px;">Double Charge</span>
                                                @else
                                                    <small class="text-muted" style="font-size: 10px;">@ Rs. {{ number_format($log->cost_per_sms, 2) }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($log->status === 'sent')
                                                    <span class="badge bg-success">Sent</span>
                                                @elseif($log->status === 'mocked')
                                                    <span class="badge bg-info text-dark">Mocked</span>
                                                @else
                                                    <span class="badge bg-danger" title="{{ $log->response }}">Failed</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                                <span class="small text-muted">
                                    Showing {{ $smsLogs->firstItem() ?? 0 }} to {{ $smsLogs->lastItem() ?? 0 }} of {{ $smsLogs->total() }} entries
                                </span>
                                <div>
                                    {{ $smsLogs->links() }}
                                </div>
                            </div>
                            @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-chat-square-text display-4 d-block mb-3 text-muted"></i>
                                No SMS dispatch history found.
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- System Configurations Accordion --}}
        <div class="accordion-item border-0 mb-4 shadow-sm rounded-4">
            <h2 class="accordion-header" id="headingSystemConfigs">
                <button class="accordion-button fw-semibold bg-white text-dark rounded-4 collapsed"
                    type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseSystemConfigs" aria-expanded="false"
                    aria-controls="collapseSystemConfigs">
                    <i class="bi bi-sliders fs-5 me-3 text-success"></i>
                    System Configurations
                </button>
            </h2>
            <div id="collapseSystemConfigs" class="accordion-collapse collapse"
                aria-labelledby="headingSystemConfigs" data-bs-parent="#settingsAccordion">
                <div class="accordion-body">

                    {{-- Add Button inside accordion --}}
                    <div class="mb-3 d-flex justify-content-end">
                        <button class="btn btn-primary shadow-sm" wire:click="openAddModal">
                            <i class="bi bi-plus-circle"></i> Add Configuration
                        </button>
                    </div>

                    {{-- Existing Configurations --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            @if($settings->isNotEmpty())
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-dark fw-bold">Key</th>
                                        <th class="text-dark fw-bold">Value</th>
                                        <th class="text-center text-dark fw-bold" style="width: 180px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($settings as $setting)
                                    <tr>
                                        <td class="text-dark">{{ $setting->key }}</td>
                                        <td class="text-dark">{{ $setting->value }}</td>
                                        <td class="text-center">
    <div class="dropdown">
        <button class="btn btn-sm btn-light border-0 dropdown-toggle" 
                type="button" 
                data-bs-toggle="dropdown" 
                aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <a class="dropdown-item text-primary" 
                   href="#" 
                   wire:click.prevent="openEditModal({{ $setting->id }})">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a>
            </li>
            <li>
                <a class="dropdown-item text-danger" 
                   href="#" 
                   wire:click.prevent="confirmDelete({{ $setting->id }})">
                    <i class="bi bi-trash me-2"></i>Delete
                </a>
            </li>
        </ul>
    </div>
</td>


                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                No configurations found. <br>
                                <small>Click "Add Configuration" to create your first setting.</small>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Add/Edit --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:key="modal-{{ $isEdit ? 'edit' : 'add' }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        @if($isEdit)
                        <i class="bi bi-pencil-square"></i> Edit Configuration
                        @else
                        <i class="bi bi-plus-circle"></i> Add Configuration
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>

                <form wire:submit.prevent="{{ $isEdit ? 'updateConfiguration' : 'saveConfiguration' }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Key</label>
                            <input type="text" wire:model="key"
                                class="form-control @error('key') is-invalid @enderror"
                                placeholder="Enter configuration key">
                            @error('key')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Value</label>
                            <input type="text" wire:model="value"
                                class="form-control @error('value') is-invalid @enderror"
                                placeholder="Enter configuration value">
                            @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary shadow-sm" wire:click="closeModal" wire:loading.attr="disabled">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success shadow-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-check-circle"></i>
                                @if($isEdit)
                                Update Configuration
                                @else
                                Save Configuration
                                @endif
                            </span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Expense Modal --}}
    @if($showExpenseModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:key="expense-modal-{{ $isEditExpense ? 'edit' : 'add' }}">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-warning text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        @if($isEditExpense)
                        <i class="bi bi-pencil-square"></i> Edit Expense
                        @else
                        <i class="bi bi-plus-circle"></i> Add New Expense
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeExpenseModal"></button>
                </div>

                <form wire:submit.prevent="{{ $isEditExpense ? 'updateExpense' : 'saveExpense' }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select wire:model.live="expenseCategory"
                                    class="form-select @error('expenseCategory') is-invalid @enderror">
                                    <option value="">Select Category</option>
                                    @foreach($expenseCategories as $category)
                                        <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                                @error('expenseCategory')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Expense Type <span class="text-danger">*</span></label>
                                <select wire:model="expenseType"
                                    class="form-select @error('expenseType') is-invalid @enderror"
                                    {{ empty($expenseCategory) ? 'disabled' : '' }}>
                                    <option value="">Select Type</option>
                                    @foreach($expenseTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('expenseType')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(empty($expenseCategory))
                                    <small class="text-muted">Please select a category first</small>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" wire:model="expenseAmount"
                                        class="form-control @error('expenseAmount') is-invalid @enderror"
                                        placeholder="0.00">
                                    @error('expenseAmount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="expenseDate"
                                    class="form-control @error('expenseDate') is-invalid @enderror">
                                @error('expenseDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select wire:model="expenseStatus"
                                class="form-select @error('expenseStatus') is-invalid @enderror">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            @error('expenseStatus')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea wire:model="expenseDescription"
                                class="form-control @error('expenseDescription') is-invalid @enderror"
                                rows="3"
                                placeholder="Enter expense description or notes..."></textarea>
                            @error('expenseDescription')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary shadow-sm" wire:click="closeExpenseModal" wire:loading.attr="disabled">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-warning text-white shadow-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-check-circle"></i>
                                @if($isEditExpense)
                                Update Expense
                                @else
                                Save Expense
                                @endif
                            </span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Expense Category Modal --}}
    @if($showCategoryModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:key="category-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-info text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle"></i> Add Expense Category/Type
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeCategoryModal"></button>
                </div>

                <form wire:submit.prevent="saveCategoryType">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Expense Category <span class="text-danger">*</span></label>
                            <select wire:model="newExpenseCategory"
                                class="form-select @error('newExpenseCategory') is-invalid @enderror">
                                <option value="">Select Existing Category</option>
                                <option value="Monthly Expenses">Monthly Expenses</option>
                                <option value="Daily Expenses">Daily Expenses</option>
                                <option value="__new__">+ Create New Category</option>
                            </select>
                            @error('newExpenseCategory')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($newExpenseCategory === '__new__')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Category Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="customExpenseCategory"
                                class="form-control @error('customExpenseCategory') is-invalid @enderror"
                                placeholder="e.g., Annual Expenses, Weekly Expenses">
                            @error('customExpenseCategory')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Expense Type <span class="text-danger">*</span></label>
                            <input type="text" wire:model="newExpenseType"
                                class="form-control @error('newExpenseType') is-invalid @enderror"
                                placeholder="e.g., Snacks, Electricity Bill, Rent">
                            @error('newExpenseType')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter the type of expense for the selected category</small>
                        </div>

                        <div class="alert alert-warning border-0 shadow-sm">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Tip:</strong> Category groups types together (e.g., "Monthly Expenses" can have types like "Rent", "Electricity Bill").
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary shadow-sm" wire:click="closeCategoryModal" wire:loading.attr="disabled">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-info text-white shadow-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-check-circle"></i> Save Category/Type
                            </span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Price Helper Modal --}}
    @if($showPriceHelperModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:key="modal-price-helper">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-warning text-dark rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-tag-fill me-2"></i>
                        {{ $isEditPriceHelper ? 'Edit Price Guide Entry' : 'Add Price Guide Entry' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closePriceHelperModal"></button>
                </div>

                <form wire:submit.prevent="savePriceHelper">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Item / Product Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="priceHelperItemName"
                                class="form-control @error('priceHelperItemName') is-invalid @enderror"
                                placeholder="e.g. 5kan kodi, 6kan kodi, Traditional Chain">
                            @error('priceHelperItemName')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="text" wire:model="priceHelperCategory"
                                class="form-control @error('priceHelperCategory') is-invalid @enderror"
                                placeholder="e.g. Kodi, Chain, Ring, Bangles">
                            @error('priceHelperCategory')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Min Price (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" wire:model="priceHelperMinPrice"
                                    class="form-control @error('priceHelperMinPrice') is-invalid @enderror"
                                    placeholder="14500">
                                @error('priceHelperMinPrice')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Max Price (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" wire:model="priceHelperMaxPrice"
                                    class="form-control @error('priceHelperMaxPrice') is-invalid @enderror"
                                    placeholder="19500">
                                @error('priceHelperMaxPrice')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes / Details <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="text" wire:model="priceHelperNotes"
                                class="form-control @error('priceHelperNotes') is-invalid @enderror"
                                placeholder="e.g. Typical weight 8g - 12g, 22K plated">
                            @error('priceHelperNotes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sort Order</label>
                                <input type="number" wire:model="priceHelperSortOrder"
                                    class="form-control @error('priceHelperSortOrder') is-invalid @enderror"
                                    placeholder="0">
                            </div>
                            <div class="col-md-6 d-flex align-items-center pt-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="priceHelperIsActiveSwitch" wire:model="priceHelperIsActive">
                                    <label class="form-check-label fw-semibold" for="priceHelperIsActiveSwitch">Active (Show in POS)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary shadow-sm" wire:click="closePriceHelperModal">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold shadow-sm">
                            <i class="bi bi-check-circle"></i>
                            {{ $isEditPriceHelper ? 'Update Entry' : 'Save Entry' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif



    {{-- Modal: Send Test SMS --}}
    @if($showTestSmsModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:key="sms-test-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-chat-dots me-2"></i>Send Test SMS Message
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeTestSmsModal"></button>
                </div>
                <form wire:submit.prevent="sendTestSms(false)">
                    <div class="modal-body p-4">
                        @if($smsStats['isFinished'])
                            <div class="alert alert-danger p-2 small mb-3">
                                <strong>⚠️ SMS Balance Finished:</strong> Current balance is Rs. {{ number_format($smsStats['balance'], 2) }}.
                                Continuing will apply double charge (Rs. {{ number_format($smsStats['rate'] * 2, 2) }}).
                            </div>
                        @elseif($smsStats['isLow'])
                            <div class="alert alert-warning p-2 small mb-3">
                                <strong>⚠️ SMS Balance Low:</strong> Current balance is Rs. {{ number_format($smsStats['balance'], 2) }}.
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Recipient Phone Number <span class="text-danger">*</span></label>
                            <input type="text" wire:model="testSmsPhone" class="form-control @error('testSmsPhone') is-invalid @enderror" placeholder="0771234567 or +94771234567">
                            @error('testSmsPhone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter any Sri Lankan mobile number (e.g. 077..., 071..., 076...).</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold">Message Content <span class="text-danger">*</span></label>
                            <textarea wire:model="testSmsMessage" rows="3" class="form-control @error('testSmsMessage') is-invalid @enderror" placeholder="Enter message text..."></textarea>
                            @error('testSmsMessage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="d-flex justify-content-between mt-1 text-muted small">
                                <span>Length: {{ strlen($testSmsMessage) }} chars</span>
                                <span>Est. Charge: Rs. {{ number_format($smsStats['rate'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top p-3">
                        <button type="button" class="btn btn-secondary shadow-sm" wire:click="closeTestSmsModal">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">
                            <i class="bi bi-send me-1"></i> Send Test SMS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    .list-group-item {
        background-color: #fff;
        transition: all 0.2s ease-in-out;
        border: 1px solid #dee2e6;
    }

    .list-group-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
    }

    .modal.fade.show {
        display: block !important;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }

    .table-bordered {
        border-color: #dee2e6;
    }

    .accordion-button:not(.collapsed) {
        background-color: #fff;
        color: #000;
        box-shadow: none;
    }

    .accordion-button:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .accordion-body {
        padding: 1.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ─── Print Label Live Preview (reads directly from inputs — no Livewire needed) ──
    function getV(id, fallback) {
        const el = document.getElementById(id);
        return el ? (parseFloat(el.value) || fallback) : fallback;
    }
    function getC(id) {
        const el = document.getElementById(id);
        return el ? el.checked : true;
    }

    // ─── Live preview: horizontal hang-tag shape ─────────────────────────────
    function renderSettingsPreview() {
        const el    = document.getElementById('settingsLabelPreview');
        const outer = document.getElementById('settingsPreviewOuter');
        const dims  = document.getElementById('settingsPreviewDims');
        if (!el || !outer) return;

        const W    = getV('lbl_width',      48);
        const H    = getV('lbl_height',     12);
        const P    = getV('lbl_padding',     1);
        const TW   = getV('lbl_text_width', 35);   // fold line / text section
        const tailW = getV('lbl_tail_width', 22);  // tail length (preview)
        const tailH = getV('lbl_tail_height', 4);  // tail strip height (preview)
        const fs   = getV('lbl_font_shop',    6);
        const fp   = getV('lbl_font_price',   8);
        const fb   = getV('lbl_font_barcode', 5);
        const qr   = getV('lbl_qr_size',     11);
        const ff   = (document.getElementById('lbl_font_family') || {value:'Courier New'}).value;
        const bType = (document.getElementById('lbl_barcode_type') || {value:'datamatrix'}).value;
        const showShop    = getC('lbl_show_shop');
        const showBarcode = getC('lbl_show_barcode');
        const showQR      = getC('lbl_show_qr');

        // Auto-scale to fit container (total label incl. tail)
        const totalMM = W + tailW;
        const availW  = outer.clientWidth - 28;
        const scale   = Math.max(2, Math.min(12, Math.floor(availW / totalMM)));

        // Pixel sizes
        const Wpx    = W    * scale;
        const Hpx    = H    * scale;
        const TWpx   = Math.min(TW, W) * scale;   // fold line pixel
        const tailWpx = tailW * scale;
        const tailHpx = tailH * scale;
        const tailY   = (Hpx - tailHpx) / 2;      // center tail vertically
        const Ppx     = Math.min(P, TW / 4) * scale;

        // QR / 2D Code: capped to label height (square or rect)
        const qrMM  = showQR ? Math.min(qr, H) : 0;
        const qrPx  = qrMM * scale;
        const qrSecW = Wpx - TWpx;  // QR section pixel width
        const qrX    = TWpx + (qrSecW - qrPx) / 2;
        const qrY    = (Hpx - qrPx) / 2;

        // Font px: 1pt = 0.353mm
        const ptPx = scale * 0.353;
        const fsPx = Math.max(fs * ptPx, 5);
        const fpPx = Math.max(fp * ptPx, 5);
        const fbPx = Math.max(fb * ptPx, 4);

        // Barcode / 2D finder-pattern SVG
        let qrSvg = '';
        if (showQR && qrPx >= 4) {
            if (bType === 'datamatrix') {
                // Data Matrix ECC 200: solid L border on left & bottom, alternating timing on top & right
                const n = 10;
                const cell = qrPx / n;
                let cells = '';
                for (let r = 0; r < n; r++) {
                    for (let c = 0; c < n; c++) {
                        let isSolid = false;
                        if (c === 0 || r === n - 1) isSolid = true; // Solid 'L'
                        else if (r === 0) isSolid = (c % 2 === 0);   // Top clock track
                        else if (c === n - 1) isSolid = (r % 2 === 1); // Right clock track
                        else isSolid = ((r * 7 + c * 13 + 3) % 5 === 0); // Data cells
                        if (isSolid) {
                            cells += `<rect x="${qrX + c * cell}" y="${qrY + r * cell}" width="${cell}" height="${cell}" fill="#000"/>`;
                        }
                    }
                }
                qrSvg = `<rect x="${qrX}" y="${qrY}" width="${qrPx}" height="${qrPx}" fill="white" stroke="#bbb" stroke-width="0.5"/>${cells}`;
            } else if (bType === 'code128') {
                // 1D Linear Barcode representation
                const barH = qrPx * 0.7;
                const barY = qrY + (qrPx - barH) / 2;
                let bars = '';
                const pattern = [2,1,1,2,3,1,1,1,2,2,1,3,1,2,1,1,2,1,1,2,2];
                let bx = qrX + 2;
                pattern.forEach((w, i) => {
                    if (i % 2 === 0) {
                        bars += `<rect x="${bx}" y="${barY}" width="${w * (qrPx / 35)}" height="${barH}" fill="#000"/>`;
                    }
                    bx += w * (qrPx / 35);
                });
                qrSvg = `<rect x="${qrX}" y="${qrY}" width="${qrPx}" height="${qrPx}" fill="white" stroke="#bbb" stroke-width="0.5"/>${bars}`;
            } else {
                // QR code pattern with 3 finder patterns
                const cell = qrPx / 7;
                const pat  = [[1,1,1,1,1,1,1],[1,0,0,0,0,0,1],[1,0,1,1,1,0,1],[1,0,1,0,1,0,1],[1,0,1,1,1,0,1],[1,0,0,0,0,0,1],[1,1,1,1,1,1,1]];
                let cells  = '';
                pat.forEach((row,r) => row.forEach((v,c) => {
                    if (v) cells += `<rect x="${qrX+c*cell}" y="${qrY+r*cell}" width="${cell}" height="${cell}" fill="#000"/>`;
                }));
                qrSvg = `<rect x="${qrX}" y="${qrY}" width="${qrPx}" height="${qrPx}" fill="white" stroke="#ccc" stroke-width="0.5"/>${cells}`;
            }
        }

        // Text in left section
        let textY = Hpx * 0.28;
        let textSvg = '';
        if (showShop) {
            textSvg += `<text x="${Ppx + 1}" y="${textY}" font-size="${fsPx}" font-weight="bold" font-family="'${ff}',monospace" fill="#000">JAFFNA GOLD</text>`;
            textY += fsPx * 1.3;
        }
        textSvg += `<text x="${Ppx + 1}" y="${textY + fpPx*0.1}" font-size="${fpPx}" font-weight="bold" font-family="'${ff}',monospace" fill="#000">Rs.1,800</text>`;
        textY += fpPx * 1.3;
        if (showBarcode) {
            textSvg += `<text x="${Ppx + 1}" y="${textY + fbPx*0.1}" font-size="${fbPx}" font-family="'${ff}',monospace" fill="#333">1234567890</text>`;
        }

        const totalPx = Wpx + tailWpx;
        const svgH    = Hpx + 18;  // extra for labels below

        el.innerHTML = `
        <svg width="${totalPx}" height="${svgH}" xmlns="http://www.w3.org/2000/svg" style="display:block;overflow:visible;">
            <defs>
                <filter id="lbl-shadow" x="-10%" y="-20%" width="120%" height="150%">
                    <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="rgba(0,0,0,0.3)"/>
                </filter>
            </defs>

            <!-- Main body background -->
            <rect x="0" y="0" width="${Wpx}" height="${Hpx}" fill="white" rx="2" filter="url(#lbl-shadow)"/>

            <!-- QR section subtle bg -->
            ${showQR ? `<rect x="${TWpx}" y="0" width="${Wpx - TWpx}" height="${Hpx}" fill="#f8f8f8" rx="0"/>` : ''}

            <!-- Text content -->
            ${textSvg}

            <!-- QR code -->
            ${qrSvg}

            <!-- Fold / section divider (dashed vertical line) -->
            <line x1="${TWpx}" y1="0" x2="${TWpx}" y2="${Hpx}"
                  stroke="#aaa" stroke-width="0.8" stroke-dasharray="3,2"/>

            <!-- Outer border -->
            <rect x="0.5" y="0.5" width="${Wpx-1}" height="${Hpx-1}"
                  fill="none" stroke="#555" stroke-width="1" rx="1.5"/>

            <!-- Tail strip — narrower, centered vertically, right side -->
            ${tailWpx > 0 ? `
            <rect x="${Wpx}" y="${tailY}" width="${tailWpx}" height="${tailHpx}"
                  fill="#d6d0c8" rx="2" filter="url(#lbl-shadow)"/>
            <rect x="${Wpx+0.5}" y="${tailY+0.5}" width="${tailWpx-1}" height="${tailHpx-1}"
                  fill="none" stroke="#aaa" stroke-width="0.8" rx="1.5"/>
            <!-- String hole -->
            <circle cx="${Wpx + tailWpx * 0.65}" cy="${tailY + tailHpx/2}"
                    r="${Math.max(tailHpx * 0.28, 1.5)}" fill="white" stroke="#888" stroke-width="0.8"/>
            ` : ''}

            <!-- Dimension labels below SVG -->
            <text x="${TWpx/2}" y="${Hpx+12}" text-anchor="middle" font-size="8" font-family="Arial" fill="#444">Text ${TW}mm</text>
            <text x="${TWpx + (Wpx-TWpx)/2}" y="${Hpx+12}" text-anchor="middle" font-size="8" font-family="Arial" fill="#444">QR ${W-TW}mm</text>
            ${tailWpx > 0 ? `<text x="${Wpx + tailWpx/2}" y="${Hpx+12}" text-anchor="middle" font-size="8" font-family="Arial" fill="#888">tail ${tailW}mm</text>` : ''}

            <!-- Arrow line under main body -->
            <line x1="0" y1="${Hpx+16}" x2="${Wpx}" y2="${Hpx+16}" stroke="#888" stroke-width="0.8" marker-end="url(#arr)"/>
            <text x="${Wpx/2}" y="${Hpx+15}" text-anchor="middle" font-size="7" font-family="Arial" fill="#666">← body ${W}mm →</text>
        </svg>`;

        if (dims) {
            const qrSec = Math.round((W - TW) * 10) / 10;
            dims.innerHTML = `Body: <b>${W}×${H}mm</b> &nbsp;|&nbsp; Text: <b>${TW}mm</b> &nbsp;|&nbsp; QR sec: <b>${qrSec}mm</b> &nbsp;|&nbsp; QR size: <b>${qrMM}mm</b> &nbsp;|&nbsp; ×${scale}`;
        }

        // Warnings
        const warn = document.getElementById('settingsWarnings');
        if (warn) {
            const msgs = [];
            if (TW >= W)
                msgs.push(`⚠️ Text width ${TW}mm leaves no room for QR`);
            if (qr > H)
                msgs.push(`⚠️ QR ${qr}mm capped to label height ${H}mm`);
            if (qrMM > (W - TW))
                msgs.push(`⚠️ QR ${qrMM}mm wider than QR section (${W-TW}mm) — will overflow`);
            if (fp < 3)
                msgs.push(`ℹ️ Price font ${fp}pt may print very small`);
            warn.innerHTML = msgs.map(m => `<div class="text-warning fw-semibold">${m}</div>`).join('');
        }
    }

    // ─── Apply recommended defaults based on label size ───────────────────
    function applyRecommended() {
        const H  = getV('lbl_height', 12);
        const W  = getV('lbl_width',  48);

        // QR: 90% of height (square), fits in right section
        const recQR       = Math.min(Math.floor(H * 0.90 * 2) / 2, H);
        // Text section = 70% of width; remaining 30% for QR
        const recTextW    = Math.round(W * 0.70 * 2) / 2;
        // Fonts scale with height
        const recFontPrice = Math.max(Math.round(H * 0.50 * 2) / 2, 2);
        const recFontShop  = Math.max(Math.round(H * 0.38 * 2) / 2, 2);
        const recFontBar   = Math.max(Math.round(H * 0.28 * 2) / 2, 1.5);
        const recPad       = Math.min(1, H * 0.08);

        const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
        set('lbl_padding',    recPad);
        set('lbl_text_width', recTextW);
        set('lbl_font_shop',  recFontShop);
        set('lbl_font_price', recFontPrice);
        set('lbl_font_barcode', recFontBar);
        set('lbl_qr_size',    recQR);
        renderSettingsPreview();
    }

    // ─── Save: collect all values in ONE single Livewire call ────────────────
    function saveLabelSettingsJS() {
        @this.call('saveLabelSettings', {
            width:           getV('lbl_width',      48),
            height:          getV('lbl_height',     12),
            padding:         getV('lbl_padding',     1),
            textWidth:       getV('lbl_text_width', 35),
            tailWidth:       getV('lbl_tail_width', 22),
            tailHeight:      getV('lbl_tail_height', 4),
            fontShop:        getV('lbl_font_shop',   6),
            fontPrice:       getV('lbl_font_price',  8),
            fontBarcode:     getV('lbl_font_barcode',5),
            qrSize:          getV('lbl_qr_size',    11),
            barcodeType:     (document.getElementById('lbl_barcode_type') || {value:'datamatrix'}).value,
            fontFamily:      (document.getElementById('lbl_font_family') || {value:'Courier New'}).value,
            showShop:        getC('lbl_show_shop'),
            showBarcodeText: getC('lbl_show_barcode'),
            showQR:          getC('lbl_show_qr')
        });
    }

    // ─── Accordion state preservation across Livewire re-renders ─────────────
    let _openAccordions = [];
    document.addEventListener('DOMContentLoaded', function() {
        renderSettingsPreview();
        // Track which accordion panels are open
        document.querySelectorAll('.accordion-collapse').forEach(function(panel) {
            panel.addEventListener('shown.bs.collapse',  () => {
                if (!_openAccordions.includes(panel.id)) _openAccordions.push(panel.id);
                // Re-render preview now that the container has a real width
                if (panel.id === 'collapsePrintLabel') setTimeout(renderSettingsPreview, 50);
            });
            panel.addEventListener('hidden.bs.collapse', () => { _openAccordions = _openAccordions.filter(id => id !== panel.id); });
        });
        window.addEventListener('resize', renderSettingsPreview);
    });

    if (typeof Livewire !== 'undefined') {
        // Before Livewire updates: snapshot open panels
        Livewire.hook('morph.updating', () => {
            _openAccordions = [];
            document.querySelectorAll('.accordion-collapse.show').forEach(p => _openAccordions.push(p.id));
        });
        // After Livewire updates: restore open panels & refresh preview
        Livewire.hook('morph.updated', () => {
            _openAccordions.forEach(function(id) {
                const el = document.getElementById(id);
                if (el && !el.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                    bsCollapse.show();
                }
            });
            setTimeout(renderSettingsPreview, 50);
        });
    }
</script>
<script>
    // SweetAlert for delete confirmation (System Configurations)
    window.addEventListener('swal:confirm-delete', event => {
        Swal.fire({
            title: 'Are you sure?',
            text: "This configuration will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('deleteConfirmed', {
                    id: event.detail.id
                });
            }
        });
    });

    // SweetAlert for delete confirmation (Expenses)
    window.addEventListener('swal:confirm-delete-expense', event => {
        Swal.fire({
            title: 'Are you sure?',
            text: "This expense will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('deleteExpenseConfirmed', {
                    id: event.detail.id
                });
            }
        });
    });

    // SweetAlert for delete confirmation (Expense Category/Type)
    window.addEventListener('swal:confirm-delete-category-type', event => {
        Swal.fire({
            title: 'Are you sure?',
            text: "This expense category/type will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('deleteCategoryTypeConfirmed', {
                    id: event.detail.id
                });
            }
        });
    });
</script>
@endpush