<div>
    <style>
        .savings-container {
            padding: 0.5rem 0;
        }

        .savings-header-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .savings-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.25rem;
        }

        .savings-subtitle {
            font-size: 0.88rem;
            color: #64748b;
        }

        /* Payment breakdown strip */
        .breakdown-strip {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .breakdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .breakdown-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .icon-cash { background: #dcfce7; color: #166534; }
        .icon-bank { background: #e0f2fe; color: #0369a1; }
        .icon-card { background: #fef3c7; color: #b45309; }
        .icon-total { background: #f1f5f9; color: #0f172a; }

        /* KPI Cards */
        .kpi-saving-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 1.25rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .kpi-saving-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .kpi-saving-card.highlight-seedu {
            border-left: 4px solid #16a34a;
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
        }

        .kpi-saving-card.highlight-undiyal {
            border-left: 4px solid #f59e0b;
            background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
        }

        .kpi-saving-card.highlight-total {
            border-left: 4px solid #161b97;
            background: linear-gradient(135deg, #161b97 0%, #12167d 100%);
            color: #ffffff;
        }

        .kpi-saving-card.highlight-total .kpi-label {
            color: #cbd5e1;
        }

        .kpi-saving-card.highlight-total .kpi-value {
            color: #ffffff;
        }

        .kpi-saving-card.highlight-total .kpi-subtext {
            color: #e2e8f0;
        }

        .kpi-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.4rem;
        }

        .kpi-value {
            font-size: 1.55rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 0.35rem;
        }

        .kpi-subtext {
            font-size: 0.78rem;
            color: #64748b;
        }

        /* Preset Chips */
        .preset-chip {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .preset-chip:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        /* Table styles */
        .table-savings th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-savings td {
            padding: 0.85rem 1rem;
            font-size: 0.88rem;
            color: #1e293b;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-savings tr:hover td {
            background-color: #f8fafc;
        }

        .badge-seedu {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-weight: 600;
        }

        .badge-undiyal {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-weight: 600;
        }

        .badge-bank {
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
            font-weight: 600;
        }

        .badge-card {
            background: #ede9fe;
            color: #5b21b6;
            border: 1px solid #ddd6fe;
            font-weight: 600;
        }

        .badge-custom {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-weight: 600;
        }

        .badge-source-sale {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            font-weight: 600;
        }

        .badge-source-cash {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-weight: 600;
        }
    </style>

    <div class="savings-container container-fluid">
        {{-- ===== HEADER & ACTION BAR ===== --}}
        <div class="savings-header-card">
            <div class="row align-items-center g-3">
                <div class="col-lg-6">
                    <h1 class="savings-title">
                        <i class="bi bi-piggy-bank-fill text-warning"></i> Daily Savings &amp; Cash Deposits
                    </h1>
                    <p class="savings-subtitle mb-0">
                        Separate and track daily savings from cash sales: <strong>Seedu (சீட்டு)</strong>, <strong>Undiyal (உண்டியல்)</strong>, and Bank Deposits.
                    </p>
                </div>

                <div class="col-lg-6">
                    <div class="d-flex align-items-center justify-content-lg-end gap-2 flex-wrap">
                        {{-- Quick View Toggle: Today vs Month --}}
                        <div class="btn-group btn-group-sm shadow-sm" role="group">
                            <button type="button" 
                                    class="btn {{ $dateFilterMode === 'today' ? 'btn-primary fw-bold' : 'btn-outline-primary bg-white' }}" 
                                    wire:click="filterToday">
                                <i class="bi bi-clock-history me-1"></i> Today
                            </button>
                            <button type="button" 
                                    class="btn {{ $dateFilterMode === 'month' ? 'btn-primary fw-bold' : 'btn-outline-primary bg-white' }}" 
                                    wire:click="filterMonth">
                                <i class="bi bi-calendar-month me-1"></i> This Month
                            </button>
                        </div>

                        {{-- Month Selector --}}
                        <div class="d-flex align-items-center gap-1 bg-light border rounded px-2 py-1">
                            <span class="small fw-bold text-muted"><i class="bi bi-calendar-month"></i> Month:</span>
                            <input type="month" class="form-control form-control-sm border-0 bg-transparent fw-semibold" style="width: 145px;" wire:model.live.debounce.400ms="selectedMonth">
                        </div>

                        {{-- Specific Date Filter (Optional) --}}
                        <div class="d-flex align-items-center gap-1 bg-light border rounded px-2 py-1">
                            <span class="small fw-bold text-muted"><i class="bi bi-calendar-event"></i> Day:</span>
                            <input type="date" class="form-control form-control-sm border-0 bg-transparent" style="width: 135px;" wire:model.live="selectedDate" placeholder="All Days">
                            @if($selectedDate)
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1" wire:click="$set('selectedDate', null)" title="Clear Day Filter">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <button type="button" class="btn btn-primary btn-sm px-3 d-flex align-items-center gap-1 fw-bold shadow-sm" wire:click="openAddModal">
                            <i class="bi bi-plus-circle-fill fs-6"></i> Record Daily Deposit
                        </button>

                        <button type="button" class="btn btn-outline-secondary btn-sm px-2" wire:click="openMethodModal" title="Manage Saving Methods">
                            <i class="bi bi-gear-fill"></i> Methods
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SALES & PAYMENT BREAKDOWN (CASH vs BANK vs CARD) ===== --}}
        <div class="breakdown-strip shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h2 class="fs-6 fw-bold mb-0 text-dark">
                        <i class="bi bi-cash-stack text-success me-1"></i> Sales Revenue &amp; Payment Methods Breakdown
                        @if($dateFilterMode === 'today')
                            <span class="badge bg-primary text-white ms-2 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i> Today: {{ \Carbon\Carbon::now()->format('d M Y') }}
                            </span>
                        @elseif($dateFilterMode === 'month')
                            <span class="badge bg-info-subtle text-info-emphasis border border-info ms-2 fw-semibold">
                                <i class="bi bi-calendar-month me-1"></i> Month: {{ $this->getParsedMonth()->format('F Y') }}
                            </span>
                        @elseif($dateFilterMode === 'custom_date' && !empty($selectedDate))
                            <span class="badge bg-secondary text-white ms-2 fw-semibold">
                                <i class="bi bi-calendar-event me-1"></i> Date: {{ $this->getFormattedSelectedDate() }}
                            </span>
                        @else
                            <span class="badge bg-light text-muted border ms-2 fw-normal">
                                Period: {{ $this->getParsedMonth()->format('F Y') }}
                            </span>
                        @endif
                    </h2>
                    <small class="text-muted">Direct visibility of incoming Cash Sales, Bank Transfers, and Card Payments before saving deductions.</small>
                </div>

                {{-- Today Quick Drawer Note --}}
                <div class="bg-light border rounded px-3 py-1 small">
                    <span class="text-muted">Today's Cash Sales:</span>
                    <strong class="text-success ms-1">Rs. {{ number_format($todayCashSales, 2) }}</strong>
                    <span class="text-muted ms-2">| Cash Saved:</span>
                    <strong class="text-warning ms-1">Rs. {{ number_format($todayCashSavings, 2) }}</strong>
                    <span class="text-muted ms-2">| Drawer Cash Left:</span>
                    <strong class="text-primary ms-1">Rs. {{ number_format($todayRemainingCash, 2) }}</strong>
                    <span class="text-muted ms-2">| Direct Bank/Card Sales:</span>
                    <strong class="text-info ms-1">Rs. {{ number_format($todayDirectSaleDeposits, 2) }}</strong>
                </div>
            </div>

            <div class="row g-3">
                {{-- 1. Cash Sales (Primary Source) --}}
                <div class="col-md-3 col-sm-6">
                    <div class="breakdown-item p-2 rounded bg-light border">
                        <div class="breakdown-icon icon-cash">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Cash Sales (Drawer Cash)</div>
                            <div class="fs-5 fw-bold text-success">
                                Rs. {{ number_format($dateFilterMode === 'today' ? $todayCashSales : $monthCashSales, 2) }}
                            </div>
                            <small class="text-muted">{{ $dateFilterMode === 'today' ? "Today's physical cash received" : "Month physical cash received" }}</small>
                        </div>
                    </div>
                </div>

                {{-- 2. Bank Transfer Sales --}}
                <div class="col-md-3 col-sm-6">
                    <div class="breakdown-item p-2 rounded bg-light border">
                        <div class="breakdown-icon icon-bank">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Bank Transfers</div>
                            <div class="fs-5 fw-bold text-primary">
                                Rs. {{ number_format($dateFilterMode === 'today' ? $todayBankSales : $monthBankSales, 2) }}
                            </div>
                            <small class="text-muted">{{ $dateFilterMode === 'today' ? "Today's direct bank sales" : "Direct deposits to bank" }}</small>
                        </div>
                    </div>
                </div>

                {{-- 3. Card & Online Sales --}}
                <div class="col-md-3 col-sm-6">
                    <div class="breakdown-item p-2 rounded bg-light border">
                        <div class="breakdown-icon icon-card">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Card / Online POS</div>
                            <div class="fs-5 fw-bold text-warning">
                                Rs. {{ number_format($dateFilterMode === 'today' ? $todayCardSales : $monthCardSales, 2) }}
                            </div>
                            <small class="text-muted">{{ $dateFilterMode === 'today' ? "Today's card terminal sales" : "Card terminal & POS sales" }}</small>
                        </div>
                    </div>
                </div>

                {{-- 4. Total Sales Revenue --}}
                <div class="col-md-3 col-sm-6">
                    <div class="breakdown-item p-2 rounded bg-light border">
                        <div class="breakdown-icon icon-total">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                        <div>
                            <div class="small text-muted fw-bold text-uppercase">Total Sales Revenue</div>
                            <div class="fs-5 fw-bold text-dark">
                                Rs. {{ number_format($dateFilterMode === 'today' ? $todayTotalSales : $monthTotalSales, 2) }}
                            </div>
                            <small class="text-muted">{{ $dateFilterMode === 'today' ? "All today's payments combined" : "All month payment modes" }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== TOP SAVINGS & DIRECT DEPOSITS KPI CARDS ===== --}}
        <div class="row g-3 mb-3">
            {{-- 1. Total Cash Saved (From Drawer) --}}
            <div class="col-xl-3 col-md-6">
                <div class="kpi-saving-card highlight-total">
                    <div>
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="kpi-label">Cash Savings (Drawer)</span>
                            <span class="badge bg-white text-primary fw-bold">
                                @if($dateFilterMode === 'today')
                                    Today's Savings
                                @else
                                    {{ $savingsRatio }}% of Cash
                                @endif
                            </span>
                        </div>
                        <div class="kpi-value">
                            Rs. {{ number_format($dateFilterMode === 'today' ? $todayCashSavings : $monthCashSavings, 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext">
                        <i class="bi bi-wallet2 me-1"></i> Seedu ({{ number_format($dateFilterMode === 'today' ? $todaySeeduSavings : $monthSeeduSavings) }}) + Undiyal ({{ number_format($dateFilterMode === 'today' ? $todayUndiyalSavings : $monthUndiyalSavings) }})
                    </div>
                </div>
            </div>

            {{-- 2. Seedu Savings (சீட்டு) --}}
            <div class="col-xl-3 col-md-6">
                <div class="kpi-saving-card highlight-seedu">
                    <div>
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="kpi-label text-success">
                                <i class="bi bi-safe2 text-success me-1"></i> Seedu (சீட்டு சேமிப்பு)
                            </span>
                            <span class="badge badge-seedu">Chit Fund</span>
                        </div>
                        <div class="kpi-value text-success">
                            Rs. {{ number_format($dateFilterMode === 'today' ? $todaySeeduSavings : $monthSeeduSavings, 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext">
                        @if($dateFilterMode === 'today')
                            <span>{{ $todayCashSavings > 0 ? round(($todaySeeduSavings / $todayCashSavings) * 100, 1) : 0 }}% of today's savings</span>
                        @else
                            <span>{{ $monthCashSavings > 0 ? round(($monthSeeduSavings / $monthCashSavings) * 100, 1) : 0 }}% of cash savings</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 3. Undiyal Savings (உண்டியல்) --}}
            <div class="col-xl-3 col-md-6">
                <div class="kpi-saving-card highlight-undiyal">
                    <div>
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="kpi-label text-warning-emphasis">
                                <i class="bi bi-piggy-bank text-warning me-1"></i> Undiyal (உண்டியல்)
                            </span>
                            <span class="badge badge-undiyal">Cash Box</span>
                        </div>
                        <div class="kpi-value text-warning-emphasis">
                            Rs. {{ number_format($dateFilterMode === 'today' ? $todayUndiyalSavings : $monthUndiyalSavings, 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext">
                        @if($dateFilterMode === 'today')
                            <span>{{ $todayCashSavings > 0 ? round(($todayUndiyalSavings / $todayCashSavings) * 100, 1) : 0 }}% of today's savings</span>
                        @else
                            <span>{{ $monthCashSavings > 0 ? round(($monthUndiyalSavings / $monthCashSavings) * 100, 1) : 0 }}% of cash savings</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 4. Direct Bank & Card Sales Deposited --}}
            <div class="col-xl-3 col-md-6">
                <div class="kpi-saving-card" style="border-left: 4px solid #0284c7; background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);">
                    <div>
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="kpi-label text-info">
                                <i class="bi bi-credit-card-2-front text-info me-1"></i> Direct Bank &amp; Card Sales
                            </span>
                            <span class="badge badge-bank">Direct Sales</span>
                        </div>
                        <div class="kpi-value text-primary">
                            Rs. {{ number_format($dateFilterMode === 'today' ? ($todayDirectBankDeposits + $todayDirectCardDeposits) : ($monthDirectBankDeposits + $monthDirectCardDeposits), 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext d-flex justify-content-between small">
                        <span>Bank: Rs. {{ number_format($dateFilterMode === 'today' ? $todayDirectBankDeposits : $monthDirectBankDeposits, 2) }}</span>
                        <span>Card: Rs. {{ number_format($dateFilterMode === 'today' ? $todayDirectCardDeposits : $monthDirectCardDeposits, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- All Deposits Combined Summary Strip --}}
        <div class="alert alert-primary bg-white border border-primary-subtle shadow-sm rounded-3 py-2 px-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check fs-5 text-primary"></i>
                <div>
                    <span class="fw-bold text-dark">
                        {{ $dateFilterMode === 'today' ? "Today's Combined All Deposits" : "Month's Combined All Deposits" }} (Cash Saved + Direct Bank &amp; Card Sales):
                    </span>
                    <strong class="text-primary fs-5 ms-2">Rs. {{ number_format($dateFilterMode === 'today' ? $todayTotalAllDeposits : $monthTotalAllDeposits, 2) }}</strong>
                </div>
            </div>
            <div class="small text-muted">
                <span class="badge bg-success-subtle text-success border border-success-subtle me-1">
                    <i class="bi bi-cash me-1"></i> Drawer Cash Saved: Rs. {{ number_format($dateFilterMode === 'today' ? $todayCashSavings : $monthCashSavings, 2) }}
                </span>
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                    <i class="bi bi-credit-card me-1"></i> Direct Bank/Card Sales: Rs. {{ number_format($dateFilterMode === 'today' ? ($todayDirectBankDeposits + $todayDirectCardDeposits) : ($monthDirectBankDeposits + $monthDirectCardDeposits), 2) }}
                </span>
            </div>
        </div>

        {{-- ===== SAVINGS DEPOSIT HISTORY TABLE ===== --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="fs-6 fw-bold mb-0 text-dark">
                        <i class="bi bi-journal-bookmark-fill text-primary me-1"></i>
                        @if($dateFilterMode === 'today')
                            Today's Deposit Records ({{ \Carbon\Carbon::now()->format('d M Y') }})
                        @elseif($dateFilterMode === 'month')
                            Monthly Deposit Records ({{ $this->getParsedMonth()->format('F Y') }})
                        @elseif($dateFilterMode === 'custom_date' && !empty($selectedDate))
                            Deposit Records for {{ $this->getFormattedSelectedDate() }}
                        @else
                            Daily Deposit Records
                        @endif
                    </h3>
                    <span class="badge bg-light text-dark border">
                        {{ $deposits->total() }} Entries
                    </span>
                    @if($dateFilterMode === 'today')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="bi bi-check-circle me-1"></i> Default: Today
                        </span>
                    @endif
                </div>

                {{-- Filters: Method & Search --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    {{-- Method Filter Pills --}}
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn {{ $methodFilter === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}" wire:click="$set('methodFilter', 'all')">
                            All Deposits
                        </button>
                        <button type="button" class="btn {{ $methodFilter === 'cash_savings' ? 'btn-success' : 'btn-outline-secondary' }}" wire:click="$set('methodFilter', 'cash_savings')">
                            <i class="bi bi-cash me-1"></i> Cash Savings
                        </button>
                        <button type="button" class="btn {{ $methodFilter === 'direct_sales' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="$set('methodFilter', 'direct_sales')">
                            <i class="bi bi-credit-card me-1"></i> Direct Bank / Card Sales
                        </button>
                        <button type="button" class="btn {{ $methodFilter === 'seedu' ? 'btn-success' : 'btn-outline-secondary' }}" wire:click="$set('methodFilter', 'seedu')">
                            Seedu (சீட்டு)
                        </button>
                        <button type="button" class="btn {{ $methodFilter === 'undiyal' ? 'btn-warning' : 'btn-outline-secondary' }}" wire:click="$set('methodFilter', 'undiyal')">
                            Undiyal (உண்டியல்)
                        </button>
                        <button type="button" class="btn {{ $methodFilter === 'bank_deposit' ? 'btn-info' : 'btn-outline-secondary' }}" wire:click="$set('methodFilter', 'bank_deposit')">
                            Bank Deposit
                        </button>
                        <button type="button" class="btn {{ $methodFilter === 'card_deposit' ? 'btn-primary' : 'btn-outline-secondary' }}" wire:click="$set('methodFilter', 'card_deposit')">
                            Card / POS
                        </button>
                    </div>

                    {{-- Search Box --}}
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search reference, note..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-savings table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Date</th>
                            <th style="width: 140px;">Saving Method</th>
                            <th style="width: 150px;">Source</th>
                            <th>Reference / Particulars</th>
                            <th>Description / Note</th>
                            <th class="text-end" style="width: 140px;">Amount (Rs.)</th>
                            <th style="width: 140px;">Recorded By</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $deposit)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $deposit->date ? $deposit->date->format('d M Y') : '—' }}
                                    <div class="small text-muted">{{ $deposit->date ? $deposit->date->format('l') : '' }}</div>
                                </td>
                                <td>
                                    @php
                                        $methodName = strtolower($deposit->saving_method ?? 'seedu');
                                    @endphp
                                    @if($methodName === 'seedu')
                                        <span class="badge badge-seedu px-2 py-1">
                                            <i class="bi bi-safe2 me-1"></i> Seedu (சீட்டு)
                                        </span>
                                    @elseif($methodName === 'undiyal')
                                        <span class="badge badge-undiyal px-2 py-1">
                                            <i class="bi bi-piggy-bank me-1"></i> Undiyal (உண்டியல்)
                                        </span>
                                    @elseif($methodName === 'bank_deposit' || $methodName === 'bank')
                                        <span class="badge badge-bank px-2 py-1">
                                            <i class="bi bi-bank me-1"></i> Bank Deposit
                                        </span>
                                    @elseif($methodName === 'card_deposit' || $methodName === 'card' || $methodName === 'online')
                                        <span class="badge badge-card px-2 py-1">
                                            <i class="bi bi-credit-card-2-front me-1"></i> Card / POS
                                        </span>
                                    @else
                                        <span class="badge badge-custom px-2 py-1">
                                            <i class="bi bi-tag me-1"></i> {{ ucfirst(str_replace('_', ' ', $deposit->saving_method)) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($deposit->source_of_funds === 'sale_bank_transfer')
                                        <span class="badge badge-source-sale px-2 py-1 small">
                                            <i class="bi bi-bank text-primary me-1"></i> Direct Bank Sale
                                        </span>
                                    @elseif($deposit->source_of_funds === 'sale_card')
                                        <span class="badge badge-source-sale px-2 py-1 small">
                                            <i class="bi bi-credit-card text-primary me-1"></i> Direct Card Sale
                                        </span>
                                    @else
                                        <span class="badge badge-source-cash px-2 py-1 small">
                                            <i class="bi bi-wallet2 text-success me-1"></i> Cash Drawer
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $deposit->reference_no ?: '—' }}</strong>
                                </td>
                                <td class="text-muted small">
                                    {{ $deposit->description ?: 'Daily saving transfer' }}
                                </td>
                                <td class="text-end fw-bold fs-6 text-dark">
                                    Rs. {{ number_format($deposit->amount, 2) }}
                                </td>
                                <td class="small">
                                    <div class="fw-semibold text-dark">{{ $deposit->user ? $deposit->user->name : 'Staff / System' }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $deposit->created_at ? $deposit->created_at->format('h:i A') : '' }}</div>
                                </td>
                                <td class="text-center">
                                    @if(in_array($deposit->source_of_funds, ['sale_bank_transfer', 'sale_card']))
                                        <span class="badge bg-light text-muted border small" title="Auto-recorded from Sale POS #{{ $deposit->reference_no }}. Synchronized with billing system.">
                                            <i class="bi bi-lock-fill text-muted me-1"></i> Auto Sale
                                        </span>
                                    @else
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" wire:click="editDeposit({{ $deposit->id }})" title="Edit Deposit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" wire:click="confirmDelete({{ $deposit->id }})" title="Delete Record">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-piggy-bank display-6 d-block mb-2 text-muted opacity-50"></i>
                                    <h5>No Daily Deposits Found</h5>
                                    <p class="small text-muted mb-3">
                                        @if($dateFilterMode === 'today')
                                            No savings or cash deposits have been recorded for today ({{ \Carbon\Carbon::now()->format('d M Y') }}).
                                        @elseif($dateFilterMode === 'custom_date' && !empty($selectedDate))
                                            No savings or cash deposits recorded for {{ $this->getFormattedSelectedDate() }}.
                                        @else
                                            No savings or cash deposits have been recorded for {{ $this->getParsedMonth()->format('F Y') }}.
                                        @endif
                                    </p>
                                    <button type="button" class="btn btn-sm btn-primary" wire:click="openAddModal">
                                        <i class="bi bi-plus-circle me-1"></i> Record First Deposit
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($deposits->hasPages())
                <div class="card-footer bg-white py-2 border-top">
                    {{ $deposits->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===== MODAL: RECORD / EDIT DAILY DEPOSIT ===== --}}
    @if($showAddModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title fs-6 fw-bold">
                            <i class="bi bi-piggy-bank-fill me-2"></i>
                            {{ $editingDepositId ? 'Edit Daily Deposit / Saving' : 'Record Daily Deposit / Saving' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeAddModal"></button>
                    </div>

                    <form wire:submit.prevent="saveDeposit">
                        <div class="modal-body p-4">
                            {{-- Info Banner: Today's Available Cash Sales --}}
                            <div class="alert alert-light border d-flex align-items-center justify-content-between p-2 mb-3 rounded-2">
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="bi bi-cash-coin fs-5 text-success"></i>
                                    <div>
                                        <div class="text-muted">Today's Total Cash Sales:</div>
                                        <strong class="text-success fs-6">Rs. {{ number_format($todayCashSales, 2) }}</strong>
                                    </div>
                                </div>
                                <div class="text-end small">
                                    <span class="text-muted">Already Saved:</span>
                                    <div class="fw-bold text-warning">Rs. {{ number_format($todayDeposited, 2) }}</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                {{-- Date --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Deposit Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('depositDate') is-invalid @enderror" wire:model="depositDate" required>
                                    @error('depositDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Saving Method --}}
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold text-dark mb-0">Saving Method <span class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-primary small text-decoration-none" wire:click="openMethodModal">
                                            + New Method
                                        </button>
                                    </div>
                                    <select class="form-select @error('saving_method') is-invalid @enderror" wire:model="saving_method" required>
                                        @foreach($savingMethods as $m)
                                            <option value="{{ $m->code }}">{{ $m->name }} ({{ $m->description ?: $m->name }})</option>
                                        @endforeach
                                    </select>
                                    @error('saving_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Source of Funds --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Source of Funds <span class="text-danger">*</span></label>
                                    <select class="form-select @error('source_of_funds') is-invalid @enderror" wire:model="source_of_funds" required>
                                        <option value="cash_sale">Cash Sale (Daily Sales Cash)</option>
                                        <option value="drawer_cash">Drawer Opening Cash</option>
                                        <option value="owner_capital">Owner Capital / Personal</option>
                                        <option value="other">Other Source</option>
                                    </select>
                                    @error('source_of_funds') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Reference / Chit Particulars --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Reference / Chit # / Box</label>
                                    <input type="text" class="form-control @error('reference_no') is-invalid @enderror" wire:model="reference_no" placeholder="e.g. Chit #2, Counter Undiyal, Slip #">
                                    @error('reference_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Amount Input --}}
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark mb-1">Deposit Amount (Rs.) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold">Rs.</span>
                                        <input type="number" step="0.01" min="0.01" class="form-control form-control-lg fw-bold text-success @error('amount') is-invalid @enderror" wire:model="amount" placeholder="0.00" required>
                                    </div>
                                    @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                    {{-- Quick Presets --}}
                                    <div class="d-flex align-items-center gap-1 mt-2 flex-wrap">
                                        <span class="small text-muted me-1">Quick Add:</span>
                                        <button type="button" class="preset-chip" wire:click="setAmountPreset(5000)">Rs. 5,000</button>
                                        <button type="button" class="preset-chip" wire:click="setAmountPreset(10000)">Rs. 10,000</button>
                                        <button type="button" class="preset-chip" wire:click="setAmountPreset(20000)">Rs. 20,000</button>
                                        <button type="button" class="preset-chip" wire:click="setAmountPreset(26500)">Rs. 26,500</button>
                                        <button type="button" class="preset-chip" wire:click="setAmountPreset(50000)">Rs. 50,000</button>
                                        <button type="button" class="preset-chip" wire:click="setAmountPreset(100000)">Rs. 100,000</button>
                                    </div>
                                </div>

                                {{-- Description / Notes --}}
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark mb-1">Notes / Description (Optional)</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" rows="2" wire:model="description" placeholder="Any specific details regarding this deposit..."></textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm px-3" wire:click="closeAddModal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveDeposit">
                                    <i class="bi bi-check2-circle me-1"></i> {{ $editingDepositId ? 'Update Deposit' : 'Save Deposit' }}
                                </span>
                                <span wire:loading wire:target="saveDeposit">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL: CREATE NEW SAVING METHOD ===== --}}
    @if($showMethodModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-header bg-dark text-white py-2">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> New Saving Method
                        </h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeMethodModal"></button>
                    </div>

                    <form wire:submit.prevent="saveNewMethod">
                        <div class="modal-body p-3">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Method Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('newMethodName') is-invalid @enderror" wire:model="newMethodName" placeholder="e.g. Gold Saving, Chit Group B" required>
                                @error('newMethodName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Description (Optional)</label>
                                <input type="text" class="form-control form-control-sm @error('newMethodDescription') is-invalid @enderror" wire:model="newMethodDescription" placeholder="Short description">
                                @error('newMethodDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold">Badge Color</label>
                                <select class="form-select form-select-sm" wire:model="newMethodColor">
                                    <option value="primary">Blue</option>
                                    <option value="success">Green</option>
                                    <option value="warning">Amber / Gold</option>
                                    <option value="info">Cyan</option>
                                    <option value="danger">Red</option>
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm" wire:click="closeMethodModal">Cancel</button>
                            <button type="submit" class="btn btn-dark btn-sm px-3 fw-bold">Create Method</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL: CONFIRM DELETE ===== --}}
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content rounded-3 border-0 shadow">
                    <div class="modal-body p-4 text-center">
                        <i class="bi bi-exclamation-triangle-fill text-danger display-5 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark">Delete Deposit Record?</h6>
                        <p class="text-muted small mb-0">Are you sure you want to remove this deposit? This action will adjust today's session deposit total.</p>
                    </div>
                    <div class="modal-footer bg-light justify-content-center py-2">
                        <button type="button" class="btn btn-secondary btn-sm px-3" wire:click="closeDeleteModal">Cancel</button>
                        <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" wire:click="deleteDeposit">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
