<div>
    {{-- Header Section --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2.5 py-1 rounded-pill fw-semibold">
                    <i class="bi bi-file-earmark-excel-fill text-warning me-1"></i> Domex Reconciliation
                </span>
                <span class="text-muted small">Excel Export Import</span>
            </div>
            <h3 class="fw-bold text-dark mb-1 mt-1">Domex Remittance & Delivery Fee Reconciliation</h3>
            <p class="text-muted small mb-0">
                Upload Domex payment Excel exports, compare actual courier fees vs estimated delivery charges, track variances (+ / -), and confirm returns.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if(!empty($parsedRecords))
                <button type="button" class="btn btn-outline-danger btn-sm px-3 py-2 rounded-3 fw-semibold shadow-sm d-flex align-items-center gap-1.5"
                        wire:click="clearImport" wire:confirm="Are you sure you want to clear the current import?">
                    <i class="bi bi-arrow-counterclockwise"></i> Upload Another File
                </button>
            @endif

            <a href="{{ route('delivery.completed') }}" class="btn btn-outline-dark btn-sm px-3 py-2 rounded-3 fw-semibold shadow-sm d-flex align-items-center gap-1.5">
                <i class="bi bi-arrow-left"></i> Completed Orders
            </a>

            <a href="{{ route('delivery.dashboard') }}" class="btn btn-outline-primary btn-sm px-3 py-2 rounded-3 fw-semibold shadow-sm d-flex align-items-center gap-1.5">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- File Upload Card (When no file uploaded or user wants to upload) --}}
    @if(empty($parsedRecords))
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-body p-4 p-md-5 text-center">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 72px; height: 72px;">
                    <i class="bi bi-file-earmark-spreadsheet-fill fs-1"></i>
                </div>

                <h4 class="fw-bold text-dark mb-2">Upload Domex Remittance Excel File</h4>
                <p class="text-muted small max-w-xl mx-auto mb-4" style="max-width: 580px;">
                    Upload the official remittance report spreadsheet provided by Domex Courier (<code>.xlsx</code>, <code>.xls</code>, or <code>.csv</code>).
                    The system will automatically match trackings, compare delivery fees, calculate charge variances, and detect returned parcels with zero collected value.
                </p>

                <div class="p-4 border-2 border-dashed rounded-4 bg-light bg-opacity-50 mx-auto" style="max-width: 500px; border-style: dashed !important; border-color: #cbd5e1 !important;">
                    <input type="file" wire:model="excelFile" id="excelFileInput" class="d-none" accept=".xlsx,.xls,.csv">
                    <label for="excelFileInput" class="btn btn-primary px-4 py-2.5 rounded-pill shadow-sm fw-bold mb-2 cursor-pointer" style="cursor: pointer;">
                        <i class="bi bi-cloud-arrow-up-fill me-1.5"></i> Select Domex Excel File
                    </label>

                    <div wire:loading wire:target="excelFile" class="text-primary small fw-semibold mt-2">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span> Parsing and reconciling Excel file...
                    </div>

                    <div class="text-muted small mt-1" style="font-size: 11px;">
                        Supported: <code>Excel_Export.xlsx</code> (standard Domex payout export format)
                    </div>
                </div>

                @if(file_exists(base_path('Excel_Export.xlsx')))
                    <div class="mt-4 pt-3 border-top d-inline-block">
                        <span class="text-muted small me-2">Detected file in project:</span>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold"
                                wire:click="loadFromDiskPath('{{ base_path('Excel_Export.xlsx') }}', 'Excel_Export.xlsx')">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Load <code>Excel_Export.xlsx</code>
                        </button>
                    </div>
                @endif

                @if($errorMessage)
                    <div class="alert alert-danger mt-3 mx-auto" style="max-width: 600px;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errorMessage }}
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- KPIs Strip --}}
        <div class="row g-3 mb-4">
            {{-- 1. Total Rows & Matched --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                            <i class="bi bi-list-check me-1 text-primary"></i> File Summary
                        </span>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                            {{ $fileName }}
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-3 fw-bold text-dark">{{ $reconciliationKpis['total_rows'] ?? 0 }}</span>
                        <span class="text-muted small">Parcels</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top small" style="font-size: 11px;">
                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-0.5"></i> {{ $reconciliationKpis['matched_count'] ?? 0 }} Matched</span>
                        <span class="text-muted">•</span>
                        <span class="text-secondary fw-semibold">{{ $reconciliationKpis['unmatched_count'] ?? 0 }} Unmatched</span>
                    </div>
                </div>
            </div>

            {{-- 2. COD Collected (Domex Value) --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                            <i class="bi bi-cash-stack me-1 text-success"></i> COD Value Collected
                        </span>
                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                            Gross
                        </span>
                    </div>
                    <div class="fs-4 fw-bold text-dark font-monospace">
                        Rs. {{ number_format($reconciliationKpis['total_domex_value'] ?? 0, 2) }}
                    </div>
                    <div class="text-muted small mt-2 pt-2 border-top" style="font-size: 11px;">
                        Total cash collected from customers by Domex
                    </div>
                </div>
            </div>

            {{-- 3. Courier Fee & Variance --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                            <i class="bi bi-truck me-1 text-warning"></i> Domex Charges & Variance
                        </span>
                        @php
                            $netVar = $reconciliationKpis['total_net_variance'] ?? 0;
                        @endphp
                        <span class="badge {{ $netVar >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }} px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                            {{ $netVar >= 0 ? '+' : '' }}Rs. {{ number_format($netVar, 2) }} {{ $netVar >= 0 ? 'Saved' : 'Loss' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-4 fw-bold text-warning font-monospace">
                            Rs. {{ number_format($reconciliationKpis['total_domex_charge'] ?? 0, 2) }}
                        </span>
                        <span class="text-muted small">Actual Fee</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top small" style="font-size: 11px;">
                        <span class="text-muted">Our Estimated: <strong>Rs. {{ number_format($reconciliationKpis['total_system_charge'] ?? 0, 0) }}</strong></span>
                        <span class="{{ $netVar >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                            {{ $netVar >= 0 ? '✓ Store Profit' : '⚠ Undercharged' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- 4. Net Domex Payable & Returns --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                            <i class="bi bi-wallet2 me-1 text-primary"></i> Net Domex Payout
                        </span>
                        @if(($reconciliationKpis['returns_count'] ?? 0) > 0)
                            <span class="badge bg-danger text-white px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                                <i class="bi bi-arrow-return-left me-0.5"></i> {{ $reconciliationKpis['returns_count'] }} Returns
                            </span>
                        @endif
                    </div>
                    <div class="fs-4 fw-bold text-primary font-monospace">
                        Rs. {{ number_format($reconciliationKpis['total_domex_payable'] ?? 0, 2) }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top small" style="font-size: 11px;">
                        <span class="text-muted">Net Remittance Received</span>
                        <span class="text-muted">({{ $reconciliationKpis['already_completed_count'] ?? 0 }} already settled)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & Action Bar --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3 flex-wrap">
                    {{-- Tabs --}}
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $tabFilter === 'all' ? 'btn-dark' : 'btn-light border text-muted' }}"
                                wire:click="setTabFilter('all')">
                            All Parcels ({{ $reconciliationKpis['total_rows'] ?? 0 }})
                        </button>

                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $tabFilter === 'matched' ? 'btn-success text-white' : 'btn-light border text-muted' }}"
                                wire:click="setTabFilter('matched')">
                            <i class="bi bi-check-circle-fill me-1"></i> Matched ({{ $reconciliationKpis['matched_count'] ?? 0 }})
                        </button>

                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $tabFilter === 'returns' ? 'btn-danger text-white' : 'btn-light border text-danger' }}"
                                wire:click="setTabFilter('returns')">
                            <i class="bi bi-arrow-return-left me-1"></i> Possible Returns ({{ $reconciliationKpis['returns_count'] ?? 0 }})
                        </button>

                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $tabFilter === 'variance' ? 'btn-warning text-dark' : 'btn-light border text-muted' }}"
                                wire:click="setTabFilter('variance')">
                            <i class="bi bi-calculator me-1"></i> Fee Variances ({{ $reconciliationKpis['variance_count'] ?? 0 }})
                        </button>

                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $tabFilter === 'unmatched' ? 'btn-secondary text-white' : 'btn-light border text-muted' }}"
                                wire:click="setTabFilter('unmatched')">
                            Unmatched ({{ $reconciliationKpis['unmatched_count'] ?? 0 }})
                        </button>
                    </div>

                    {{-- Search Box --}}
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 320px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control form-control-sm border-start-0 bg-light"
                                   wire:model.live.debounce.250ms="search"
                                   placeholder="Search tracking, invoice, customer...">
                        </div>
                    </div>
                </div>

                {{-- Bulk Action Controls & Options --}}
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mt-3 pt-3 border-top flex-wrap">
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check form-switch mb-0" title="When settling, replace our estimated delivery charge (e.g. 450) with the actual Domex charge (e.g. 425)">
                            <input class="form-check-input" type="checkbox" id="updateDeliveryChargesSwitch" wire:model.live="updateDeliveryCharges">
                            <label class="form-check-label small fw-semibold text-dark" for="updateDeliveryChargesSwitch">
                                Update Order Delivery Charge to Actual Domex Fee
                            </label>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        {{-- Bulk Settle Matched Button --}}
                        <button type="button" class="btn btn-success btn-sm px-3 py-1.5 rounded-pill shadow-sm fw-bold d-flex align-items-center gap-1.5"
                                wire:click="applyBulkSettlement"
                                wire:confirm="Confirm bulk settlement for all matched orders? Outstanding COD will be recorded as paid and status marked Completed."
                                title="Bulk settle all matched orders">
                            <i class="bi bi-check-all fs-6"></i>
                            <span>Settle All Matched Orders</span>
                        </button>

                        {{-- Bulk Confirm Returns Button --}}
                        @if(($reconciliationKpis['returns_count'] ?? 0) > 0)
                            <button type="button" class="btn btn-danger btn-sm px-3 py-1.5 rounded-pill shadow-sm fw-bold d-flex align-items-center gap-1.5"
                                    wire:click="confirmBulkReturns"
                                    wire:confirm="Confirm return for all parcels where collected value is 0? Stock will be restored to inventory."
                                    title="Confirm all returns where collected value is 0">
                                <i class="bi bi-arrow-return-left"></i>
                                <span>Confirm All Returns</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Reconciliation Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                    <thead class="table-light text-muted small text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">
                        <tr>
                            <th class="ps-3 py-3" style="width: 50px;">#</th>
                            <th class="py-3" style="min-width: 140px;">Tracking No</th>
                            <th class="py-3" style="min-width: 180px;">Receiver & Date</th>
                            <th class="py-3" style="min-width: 180px;">Matched System Order</th>
                            <th class="text-end py-3" style="min-width: 110px;">Domex Value</th>
                            <th class="text-end py-3" style="min-width: 170px;">
                                Charge Comparison
                                <i class="bi bi-info-circle text-muted ms-0.5" title="Domex Actual Fee vs Our System Charge"></i>
                            </th>
                            <th class="text-end py-3" style="min-width: 120px;">
                                Variance
                                <i class="bi bi-info-circle text-muted ms-0.5" title="Store Saved (+) or Undercharged (-)"></i>
                            </th>
                            <th class="text-end py-3" style="min-width: 110px;">Net Payable</th>
                            <th class="text-center py-3" style="min-width: 160px;">Status & Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $rec)
                            <tr wire:key="domex-row-{{ $rec['tracking_no'] }}" class="{{ $rec['is_possible_return'] ? 'table-danger table-opacity-10' : ($rec['is_matched'] ? '' : 'table-light text-muted') }}">
                                {{-- 1. Row Num --}}
                                <td class="ps-3 text-muted font-monospace small">
                                    {{ $rec['excel_row_num'] }}
                                </td>

                                {{-- 2. Tracking No --}}
                                <td>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <a href="https://domex.lk/Order-Details.php?wbno={{ $rec['tracking_no'] }}" target="_blank"
                                           class="fw-bold font-monospace text-decoration-none text-primary"
                                           title="Open live Domex tracking page">
                                            {{ $rec['tracking_no'] }}
                                            <i class="bi bi-box-arrow-up-right text-muted ms-0.5" style="font-size: 10px;"></i>
                                        </a>
                                    </div>
                                    @if(!empty($rec['ref_no']))
                                        <div class="text-muted small" style="font-size: 10px;">Ref: {{ $rec['ref_no'] }}</div>
                                    @endif
                                </td>

                                {{-- 3. Receiver & Date --}}
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 180px;" title="{{ $rec['receiver_name'] }}">
                                        {{ $rec['receiver_name'] }}
                                    </div>
                                    @if(!empty($rec['receiver_address']))
                                        <div class="text-muted text-truncate" style="font-size: 11px; max-width: 180px;" title="{{ $rec['receiver_address'] }}">
                                            <i class="bi bi-geo-alt me-0.5"></i> {{ $rec['receiver_address'] }}
                                        </div>
                                    @endif
                                    @if(!empty($rec['invoiced_date']))
                                        <div class="text-muted small" style="font-size: 10px;">
                                            <i class="bi bi-calendar3 me-0.5"></i> {{ $rec['invoiced_date'] }}
                                        </div>
                                    @endif
                                </td>

                                {{-- 4. Matched Order --}}
                                <td>
                                    @if($rec['is_matched'])
                                        <div class="d-flex align-items-center gap-1.5">
                                            <a href="{{ route('delivery.order-detail', $rec['sale_id']) }}" target="_blank" class="fw-bold text-dark text-decoration-none font-monospace">
                                                #{{ $rec['invoice_number'] }}
                                                <i class="bi bi-box-arrow-up-right text-muted ms-0.5" style="font-size: 9px;"></i>
                                            </a>
                                            <span class="badge {{ $rec['delivery_status'] === 'completed' ? 'bg-success' : ($rec['delivery_status'] === 'delivered' ? 'bg-info' : 'bg-secondary') }} px-1.5 py-0.5 rounded-pill" style="font-size: 9px;">
                                                {{ ucfirst($rec['delivery_status']) }}
                                            </span>
                                        </div>
                                        <div class="text-muted text-truncate" style="font-size: 11px; max-width: 180px;">
                                            {{ $rec['customer_name'] }}
                                            @if(!empty($rec['customer_phone']))
                                                • {{ $rec['customer_phone'] }}
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size: 10px;">
                                            Order Total: <strong>Rs. {{ number_format($rec['system_total'], 2) }}</strong>
                                            @if($rec['system_due'] > 0)
                                                | Due: <span class="text-danger fw-bold">Rs. {{ number_format($rec['system_due'], 0) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill">
                                            <i class="bi bi-question-circle me-1"></i> Unmatched
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 10px;">Not found in database</div>
                                    @endif
                                </td>

                                {{-- 5. Domex Value --}}
                                <td class="text-end">
                                    @if($rec['domex_value'] > 0)
                                        <div class="fw-bold text-dark font-monospace">
                                            Rs. {{ number_format($rec['domex_value'], 2) }}
                                        </div>
                                        <div class="text-muted small" style="font-size: 10px;">COD Collected</div>
                                    @else
                                        <div class="fw-bold text-danger font-monospace">
                                            Rs. 0.00
                                        </div>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-1.5 py-0.5" style="font-size: 9px;">
                                            No COD Collected
                                        </span>
                                    @endif
                                </td>

                                {{-- 6. Charge Comparison --}}
                                <td class="text-end">
                                    <div class="font-monospace fw-bold text-warning">
                                        Domex: Rs. {{ number_format($rec['domex_charge'], 2) }}
                                    </div>
                                    @if($rec['is_matched'])
                                        <div class="text-muted small font-monospace" style="font-size: 11px;">
                                            Our Charge: Rs. {{ number_format($rec['system_charge'], 2) }}
                                        </div>
                                    @endif
                                </td>

                                {{-- 7. Variance --}}
                                <td class="text-end">
                                    @if($rec['is_matched'])
                                        @if($rec['variance'] > 0)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill font-monospace fw-bold" title="Store charged customer Rs. {{ number_format($rec['system_charge'], 2) }} but Domex only took Rs. {{ number_format($rec['domex_charge'], 2) }}">
                                                <i class="bi bi-arrow-up-circle-fill me-0.5"></i> +Rs. {{ number_format($rec['variance'], 2) }}
                                            </span>
                                            <div class="text-success small" style="font-size: 10px;">Store Saved</div>
                                        @elseif($rec['variance'] < 0)
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill font-monospace fw-bold" title="Domex charged Rs. {{ number_format($rec['domex_charge'], 2) }} which exceeds store charge of Rs. {{ number_format($rec['system_charge'], 2) }}">
                                                <i class="bi bi-arrow-down-circle-fill me-0.5"></i> -Rs. {{ number_format(abs($rec['variance']), 2) }}
                                            </span>
                                            <div class="text-danger small" style="font-size: 10px;">Undercharged</div>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill font-monospace">
                                                Rs. 0.00
                                            </span>
                                            <div class="text-muted small" style="font-size: 10px;">Exact Match</div>
                                        @endif
                                    @else
                                        <span class="text-muted font-monospace">-</span>
                                    @endif
                                </td>

                                {{-- 8. Net Domex Payable --}}
                                <td class="text-end">
                                    <div class="fw-bold font-monospace {{ $rec['domex_payable'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                        Rs. {{ number_format($rec['domex_payable'], 2) }}
                                    </div>
                                    <div class="text-muted small" style="font-size: 10px;">Net Payout</div>
                                </td>

                                {{-- 9. Status & Actions --}}
                                <td class="text-center pe-3">
                                    @if($rec['is_possible_return'])
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            @if($rec['is_already_returned'])
                                                <span class="badge bg-danger text-white px-2 py-1 rounded-pill small">
                                                    <i class="bi bi-check2 me-0.5"></i> Returned & Restocked
                                                </span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5 rounded-pill small fw-semibold">
                                                    <i class="bi bi-exclamation-triangle-fill me-0.5"></i> Possible Return
                                                </span>
                                                @if($rec['is_matched'])
                                                    <button type="button" class="btn btn-xs btn-outline-danger px-2 py-1 rounded-pill fw-bold"
                                                            wire:click="confirmSingleReturn('{{ $rec['tracking_no'] }}')"
                                                            wire:confirm="Confirm order #{{ $rec['invoice_number'] }} returned? Stock will be restored to inventory."
                                                            title="Confirm Return & Restore Stock">
                                                        <i class="bi bi-arrow-return-left me-1"></i> Confirm Return
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    @elseif($rec['is_already_completed'])
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-check2-all me-1"></i> Completed & Settled
                                        </span>
                                    @elseif($rec['is_matched'])
                                        <button type="button" class="btn btn-sm btn-success px-2.5 py-1 rounded-pill shadow-sm fw-bold d-inline-flex align-items-center gap-1"
                                                wire:click="applySingleSettlement('{{ $rec['tracking_no'] }}')"
                                                title="Record payment & complete order">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span>Settle Order</span>
                                        </button>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-muted border border-secondary border-opacity-25 px-2 py-1 rounded-pill small">
                                            No Action
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-funnel fs-1 d-block mb-2 opacity-50"></i>
                                        <h6 class="fw-semibold text-dark">No records match your filter</h6>
                                        <p class="small mb-3">Try switching tabs or clearing your search term.</p>
                                        <button class="btn btn-sm btn-outline-primary" wire:click="setTabFilter('all'); $set('search', '')">
                                            Show All Records
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- Table Summary Footer --}}
                    @if(count($records) > 0)
                        <tfoot class="table-light border-top border-dark border-opacity-25 fw-bold" style="font-size: 0.92rem;">
                            <tr class="bg-light">
                                <td colspan="4" class="ps-3 py-3 text-dark">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-dark px-2.5 py-1 rounded-pill text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Filtered Total</span>
                                        <span class="text-muted small text-uppercase">Showing {{ count($records) }} Parcels</span>
                                    </div>
                                </td>
                                {{-- Domex Value Total --}}
                                <td class="text-end py-3 font-monospace text-dark">
                                    <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Total Value</div>
                                    <div class="fs-6 fw-bold">Rs. {{ number_format(collect($records)->sum('domex_value'), 2) }}</div>
                                </td>
                                {{-- Domex Charge Total --}}
                                <td class="text-end py-3 font-monospace text-warning">
                                    <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Total Domex Fee</div>
                                    <div class="fs-6 fw-bold">Rs. {{ number_format(collect($records)->sum('domex_charge'), 2) }}</div>
                                </td>
                                {{-- Variance Total --}}
                                <td class="text-end py-3 font-monospace">
                                    @php
                                        $subVar = collect($records)->where('is_matched', true)->sum('variance');
                                    @endphp
                                    <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Net Variance</div>
                                    <div class="fs-6 fw-bold {{ $subVar >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $subVar >= 0 ? '+' : '' }}Rs. {{ number_format($subVar, 2) }}
                                    </div>
                                </td>
                                {{-- Net Payable Total --}}
                                <td class="text-end py-3 font-monospace text-primary">
                                    <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Net Payable</div>
                                    <div class="fs-6 fw-bold">Rs. {{ number_format(collect($records)->sum('domex_payable'), 2) }}</div>
                                </td>
                                <td class="pe-3 py-3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Bottom Financial Summary Bar --}}
            <div class="p-3 bg-white border-top">
                <div class="row g-2 align-items-stretch">
                    {{-- 1. Total COD Collected --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="h-100 p-2.5 rounded-3 border bg-light bg-opacity-50 d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-cash-stack me-1 text-primary"></i> Total COD Collected
                                </div>
                                <div class="fs-5 fw-bold text-dark mt-1 font-monospace">
                                    Rs. {{ number_format($reconciliationKpis['total_domex_value'] ?? 0, 2) }}
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">Customer Value in File</div>
                            </div>
                            <div class="badge bg-primary bg-opacity-10 text-primary p-2.5 rounded-circle fs-5">
                                <i class="bi bi-wallet2"></i>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Domex Courier Fees --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="h-100 p-2.5 rounded-3 border bg-light bg-opacity-50 d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-truck me-1 text-warning"></i> Domex Courier Fees
                                </div>
                                <div class="fs-5 fw-bold text-warning mt-1 font-monospace">
                                    Rs. {{ number_format($reconciliationKpis['total_domex_charge'] ?? 0, 2) }}
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">Actual Courier Charges</div>
                            </div>
                            <div class="badge bg-warning bg-opacity-10 text-warning p-2.5 rounded-circle fs-5">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Fee Variance --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        @php
                            $totVar = $reconciliationKpis['total_net_variance'] ?? 0;
                        @endphp
                        <div class="h-100 p-2.5 rounded-3 border {{ $totVar >= 0 ? 'border-success border-opacity-50 bg-success bg-opacity-10' : 'border-danger border-opacity-50 bg-danger bg-opacity-10' }} d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <div class="{{ $totVar >= 0 ? 'text-success' : 'text-danger' }} text-uppercase fw-bold" style="font-size: 11px;">
                                    <i class="bi bi-calculator me-1"></i> Delivery Charge Variance
                                </div>
                                <div class="fs-5 fw-bold {{ $totVar >= 0 ? 'text-success' : 'text-danger' }} mt-1 font-monospace">
                                    {{ $totVar >= 0 ? '+' : '' }}Rs. {{ number_format($totVar, 2) }}
                                </div>
                                <div class="{{ $totVar >= 0 ? 'text-success' : 'text-danger' }} text-opacity-75 small" style="font-size: 10px;">
                                    {{ $totVar >= 0 ? 'Store Saved (Our Fee > Domex Fee)' : 'Store Loss (Domex Fee > Our Fee)' }}
                                </div>
                            </div>
                            <div class="badge {{ $totVar >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }} p-2.5 rounded-circle fs-5 shadow-sm">
                                <i class="bi {{ $totVar >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Net Remittance Payout --}}
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="h-100 p-2.5 rounded-3 border bg-light bg-opacity-50 d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <div class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-bank me-1 text-info"></i> Net Domex Remittance
                                </div>
                                <div class="fs-5 fw-bold text-primary mt-1 font-monospace">
                                    Rs. {{ number_format($reconciliationKpis['total_domex_payable'] ?? 0, 2) }}
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">Net Payable Payout (Value - Fee)</div>
                            </div>
                            <div class="badge bg-info bg-opacity-10 text-info p-2.5 rounded-circle fs-5">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
