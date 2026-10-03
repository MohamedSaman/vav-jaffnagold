<div>
    @push('styles')
    <style>
        /* Modern card styling */
        .barcode-print-page .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .barcode-print-page .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .barcode-print-page .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
        }

        /* Stats cards */
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
        }

        .stats-card .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .stats-card .stats-label {
            font-size: 0.85rem;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* QR code display in table */
        .qr-display {
            background: #fff;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 6px;
            text-align: center;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Checkbox styling */
        .form-check-input:checked {
            background-color: #4361ee;
            border-color: #4361ee;
        }

        .form-check-input {
            width: 1.2rem;
            height: 1.2rem;
            cursor: pointer;
        }

        /* Table styling */
        .barcode-print-page .table th {
            border-top: none;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .barcode-print-page .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        /* Selected row highlight */
        .barcode-print-page .table tbody tr.selected-row {
            background-color: rgba(67, 97, 238, 0.08);
        }

        /* Action buttons */
        .btn-print-all {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-print-all:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .stats-card .stats-number {
                font-size: 1.8rem;
            }
        }
    </style>
    @endpush

    <div class="container-fluid p-3 barcode-print-page">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-2">
                    <i class="bi bi-upc-scan text-primary me-2"></i> Barcode Print Center
                </h3>
                <p class="text-muted mb-0">Print QR code labels for newly generated products</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-sliders me-2"></i> Print Setup
                </a>
                <button class="btn btn-print-all" wire:click="openLookupModal">
                    <i class="bi bi-search me-2"></i> Instant Barcode Print
                </button>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stats-number">{{ $totalUnprinted }}</div>
                            <div class="stats-label mt-1">Barcodes Pending Print</div>
                        </div>
                        <div>
                            <i class="bi bi-printer fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stats-number">{{ count($selectedProducts) }}</div>
                            <div class="stats-label mt-1">Selected for Print</div>
                        </div>
                        <div>
                            <i class="bi bi-check2-square fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-stretch">
                <div class="card w-100 border-0 shadow-sm d-flex justify-content-center" style="border-radius: 12px;">
                    <div class="card-body d-flex flex-column justify-content-center gap-2 p-3">
                        <button class="btn btn-print-all w-100" onclick="printSelectedLabels()"
                            {{ count($selectedProducts) === 0 ? 'disabled' : '' }}>
                            <i class="bi bi-printer me-2"></i> Print Selected ({{ count($selectedProducts) }})
                        </button>
                        <button class="btn btn-outline-success w-100" wire:click="markAsPrinted"
                            {{ count($selectedProducts) === 0 ? 'disabled' : '' }}>
                            <i class="bi bi-check-circle me-2"></i> Mark as Printed
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Table --}}
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-list-ul text-primary me-2"></i> Products Waiting for Barcode Print
                    </h5>
                    <p class="text-muted small mb-0">Select products and click Print to generate QR code labels</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group" style="width: 280px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" wire:model.live="search"
                            placeholder="Search products...">
                    </div>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model.live="selectAll"
                                            id="selectAllCheckbox">
                                    </div>
                                </th>
                                <th>No</th>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Product Code</th>
                                <th>Barcode</th>
                                <th>QR Preview</th>
                                <th>Retail Price</th>
                                <th>Available Stock</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $index => $product)
                            @php
                                $imagePath = $product->image;
                                $defaultImage = asset('images/product.jpg');
                                if ($imagePath && strpos($imagePath, 'storage/images/') === 0) {
                                    $imgFilename = substr($imagePath, strlen('storage/images/'));
                                    $imageUrl = url('/product-image-serve/' . $imgFilename);
                                } elseif ($imagePath) {
                                    $imageUrl = asset($imagePath);
                                } else {
                                    $imageUrl = null;
                                }
                            @endphp
                            <tr wire:key="barcode-{{ $product->id }}"
                                data-size="{{ $product->size ?? '' }}"
                                class="{{ in_array((string) $product->id, $selectedProducts) ? 'selected-row' : '' }}">
                                <td class="ps-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            wire:model.live="selectedProducts"
                                            value="{{ $product->id }}"
                                            id="product-{{ $product->id }}">
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium text-dark">{{ $products->firstItem() + $index }}</span>
                                </td>
                                <td>
                                    @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                                        class="img-thumbnail" style="width: 45px; height: 45px; object-fit: cover;"
                                        onerror="this.src='{{ $defaultImage }}'">
                                    @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                        style="width: 45px; height: 45px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-medium text-dark">{{ $product->name }}</span>
                                    @if(!empty($product->size))
                                    <span class="badge bg-purple-subtle text-primary border border-primary-subtle py-0 px-1 ms-1" style="font-size: 0.72rem;">
                                        SZ: {{ $product->size }}
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $product->code }}</span>
                                </td>
                                <td>
                                    <code class="fs-6">{{ $product->barcode }}</code>
                                </td>
                                <td>
                                    <div class="qr-display">
                                        <div class="product-qr" id="qr-{{ $product->id }}"
                                            data-barcode="{{ $product->barcode }}"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">Rs.{{ number_format($product->retail_price ?? 0, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ ($product->available_stock ?? 0) > 0 ? 'bg-success' : 'bg-danger' }}">{{ $product->available_stock ?? 0 }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $product->created_at->format('d M Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="printSingleLabel('{{ $product->barcode }}', '{{ $product->code }}', '{{ number_format($product->retail_price ?? 0, 2) }}', '{{ addslashes($product->size ?? '') }}')"
                                            title="Print this label">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success"
                                            wire:click="markSingleAsPrinted({{ $product->id }})"
                                            wire:confirm="Mark this barcode as printed?"
                                            title="Mark as printed">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                        <h5 class="mt-3 fw-bold text-dark">All Barcodes Printed!</h5>
                                        <p class="text-muted">No products with unprinted barcodes found.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $products->links('livewire.custom-pagination') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Instant Barcode Print Modal --}}
    @if($showLookupModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title text-white fw-bold">
                        <i class="bi bi-upc-scan me-2"></i> Instant Barcode Print
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeLookupModal"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Search Input --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Enter Product Name, Code, or Barcode</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search text-primary"></i></span>
                            <input type="text" id="lookup-code-input" class="form-control form-control-lg"
                                wire:model="lookupCode"
                                wire:keydown.enter="searchProduct"
                                onpaste="setTimeout(() => { this.focus(); this.select(); }, 40);"
                                onfocus="this.select();"
                                onclick="this.select();"
                                placeholder="e.g. Necklace, PRD-001 or barcode number" autofocus autocomplete="off">
                            <button class="btn btn-primary px-4" wire:click="searchProduct">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                        </div>
                    </div>

                    {{-- Products List Details --}}
                    @if(!empty($lookupProducts))
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">
                            Found <strong class="text-primary">{{ count($lookupProducts) }}</strong> matching product{{ count($lookupProducts) > 1 ? 's' : '' }}
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-3" style="max-height: 55vh; overflow-y: auto; padding-right: 4px;">
                        @foreach($lookupProducts as $product)
                        <div class="border rounded-3 p-3 bg-light shadow-sm">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-start gap-3">
                                {{-- QR Preview --}}
                                <div class="flex-shrink-0 align-self-center align-self-sm-start">
                                    <div class="qr-display" style="width: 80px; height: 80px;">
                                        <div class="lookup-qr" id="lookup-qr-{{ $product['id'] }}"
                                            data-barcode="{{ $product['barcode'] }}"></div>
                                    </div>
                                </div>
                                {{-- Details --}}
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-dark mb-1">{{ $product['name'] }}</h6>
                                    <table class="table table-sm table-borderless mb-0" style="font-size: 0.88rem;">
                                        <tr>
                                            <td class="text-muted py-0" style="width: 110px;">Product Code</td>
                                            <td class="py-0 fw-medium">{{ $product['code'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0">Barcode</td>
                                            <td class="py-0"><code class="fw-bold">{{ $product['barcode'] }}</code></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0">Retail Price</td>
                                            <td class="py-0 fw-bold text-success">Rs.{{ $product['retail_price'] }}</td>
                                        </tr>
                                        @if(!empty($product['size']))
                                        <tr>
                                            <td class="text-muted py-0">Size</td>
                                            <td class="py-0 fw-bold text-dark">{{ $product['size'] }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="text-muted py-0">Available Stock</td>
                                            <td class="py-0">
                                                <span class="badge {{ $product['available_stock'] > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $product['available_stock'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                {{-- Action Button --}}
                                <div class="flex-shrink-0 d-flex flex-column justify-content-center mt-2 mt-sm-0">
                                    <button class="btn btn-print-all btn-sm px-3 py-2 text-nowrap"
                                        onclick="printSingleLabel('{{ $product['barcode'] }}', '{{ $product['code'] }}', '{{ $product['retail_price'] }}', '{{ addslashes($product['size'] ?? '') }}')">
                                        <i class="bi bi-printer me-1"></i> Print Label
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bwip-js@3.0.4/dist/bwip-js-min.js"></script>
    <script>
        // ─── Settings from database (set by PHP / Livewire) ───────────────────
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

        function loadPrintSettings() { return LABEL_SETTINGS; }

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

        // ─── Build label CSS from settings ────────────────────────────────────
        // ─── Build label CSS from settings ────────────────────────────────────
        function buildLabelCSS(s) {
            const qrMM   = s.showQR ? Math.min(s.qrSize, 9.5) : 0;
            return `
                * { margin:0; padding:0; box-sizing:border-box; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
                body { font-family: Arial, Helvetica, sans-serif; margin:0; padding:0; background:white; font-weight:700; color:#000; }
                .label-wrapper {
                    width:${s.width}mm; height:${s.height}mm;
                    display:flex; flex-direction:row; align-items:stretch; overflow:hidden;
                    page-break-inside:avoid;
                    font-family: Arial, Helvetica, sans-serif;
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

        // ─── Print single label ───────────────────────────────────────────────
        function printSingleLabel(barcode, productCode, retailPrice, size) {
            const s = loadPrintSettings();
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
            setTimeout(() => {
                const lookupInput = document.getElementById('lookup-code-input');
                if (lookupInput) {
                    lookupInput.focus();
                    lookupInput.select();
                }
            }, 300);
        }

        // ─── Print selected labels (bulk) ─────────────────────────────────────
        function printSelectedLabels() {
            const selectedCheckboxes = document.querySelectorAll('input[type="checkbox"][wire\\:model\\.live="selectedProducts"]:checked');
            if (selectedCheckboxes.length === 0) {
                Swal.fire({icon:'warning', title:'No Selection', text:'Please select at least one product.', timer:2000, showConfirmButton:false});
                return;
            }

            const labelData = [];
            selectedCheckboxes.forEach(function(cb) {
                const row = cb.closest('tr');
                if (row) {
                    const cells = row.querySelectorAll('td');
                    const barcode     = cells[5] ? cells[5].textContent.trim() : '';
                    let retailPrice   = cells[7] ? cells[7].textContent.trim() : '0.00';
                    retailPrice       = retailPrice.replace(/^Rs\.?\s*/i, '');
                    const size        = row.dataset.size ? row.dataset.size.trim() : '';
                    labelData.push({ barcode, price: retailPrice, size });
                }
            });

            if (labelData.length === 0) return;

            const s = loadPrintSettings();

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
                        <canvas id="qr-${idx}"></canvas>
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
                        bwipjs.toCanvas(document.getElementById('qr-${idx}'), opts_${idx});
                    } catch(e) { console.error(e); }
                `;
            }).join('\n') : '';

            const printWindow = window.open('', '_blank', 'width=600,height=500');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Labels (${labelData.length})</title>
                    <script src="https://cdn.jsdelivr.net/npm/bwip-js@3.0.4/dist/bwip-js-min.js"><\/script>
                    <style>
                        ${buildLabelCSS(s)}
                        .labels-grid { display:flex; flex-direction:column; gap:0; }
                    <\/style>
                </head>
                <body>
                    <div class="labels-grid">
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

        // ─── QR/Barcode rendering in table ────────────────────────────────────
        function renderQRCodes() {
            document.querySelectorAll('.product-qr[data-barcode]').forEach(function(el) {
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

        function renderLookupQR() {
            document.querySelectorAll('.lookup-qr[data-barcode]').forEach(function(el) {
                const barcode = el.dataset.barcode ? el.dataset.barcode.trim() : '';
                if (!barcode) {
                    el.innerHTML = '';
                    el.dataset.renderedBarcode = '';
                    return;
                }
                if (el.dataset.renderedBarcode === barcode && el.querySelector('canvas')) return;
                el.innerHTML = '';
                el.dataset.renderedBarcode = barcode;
                const canvas = document.createElement('canvas');
                el.appendChild(canvas);
                renderBarcodeToCanvas(canvas, barcode, LABEL_SETTINGS.barcodeType);
            });
        }

        // ─── Init ─────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            renderQRCodes();
            const tableBody = document.querySelector('.barcode-print-page tbody');
            if (tableBody) {
                const observer = new MutationObserver(function(mutations) {
                    if (mutations.some(m => m.addedNodes.length > 0)) setTimeout(renderQRCodes, 200);
                });
                observer.observe(tableBody, { childList: true, subtree: true });
            }
        });

        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morph.updated', () => {
                setTimeout(renderQRCodes, 150);
                setTimeout(renderLookupQR, 150);
                setTimeout(() => {
                    const lookupInput = document.getElementById('lookup-code-input');
                    if (lookupInput) {
                        lookupInput.focus();
                        lookupInput.select();
                    }
                }, 80);
            });
        }
    </script>
    @endpush
</div>
