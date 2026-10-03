<div>
    {{-- Inline Scoped Styling for P&L Dashboard --}}
    <style>
        .npl-container {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 3rem;
        }

        .npl-header-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .npl-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .npl-subtitle {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        /* Preset Pills */
        .preset-pills-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed #e2e8f0;
        }

        .btn-preset {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-preset:hover {
            background: #e2e8f0;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .btn-preset.active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.25);
        }

        /* Metric Cards */
        .npl-kpi-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            padding: 1.25rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .npl-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
        }

        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }

        .kpi-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .icon-sales { background: #dcfce7; color: #166534; }
        .icon-cogs { background: #fef3c7; color: #b45309; }
        .icon-purchases { background: #e0f2fe; color: #0369a1; }
        .icon-expenses { background: #fee2e2; color: #991b1b; }
        .icon-deposits { background: #ccfbf1; color: #0f766e; }
        .icon-stock { background: #fef9c3; color: #854d0e; }
        .icon-profit-pos { background: #d1fae5; color: #065f46; }
        .icon-profit-neg { background: #ffe4e6; color: #9f1239; }

        .header-stock-banner {
            background: linear-gradient(135deg, #fbfcfe 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.85rem 1.25rem;
        }

        .stock-banner-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #fef3c7;
            color: #b45309;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .kpi-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        .kpi-value {
            font-size: 1.7rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 0.35rem;
        }

        .kpi-subtext {
            font-size: 0.76rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            margin: 0;
        }

        /* Profit Highlight Card */
        .card-profit-highlight {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            border-color: #334155;
        }

        .card-profit-highlight .kpi-label {
            color: #94a3b8;
        }

        .card-profit-highlight .kpi-value {
            color: #ffffff;
        }

        .card-profit-highlight .kpi-subtext {
            color: #cbd5e1;
        }

        /* Navigation Tabs */
        .npl-tabs-bar {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }

        .npl-tab-btn {
            background: transparent;
            border: none;
            padding: 0.85rem 1.25rem;
            font-size: 0.92rem;
            font-weight: 600;
            color: #64748b;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .npl-tab-btn:hover {
            color: #0f172a;
        }

        .npl-tab-btn.active {
            color: #0f172a;
            border-bottom-color: #0f172a;
            font-weight: 700;
        }

        .tab-badge {
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            background: #e2e8f0;
            color: #334155;
        }

        .npl-tab-btn.active .tab-badge {
            background: #0f172a;
            color: #ffffff;
        }

        /* Tables & Content Cards */
        .npl-content-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .npl-card-header {
            padding: 1.15rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }

        .npl-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-npl th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-npl td {
            padding: 0.85rem 1rem;
            font-size: 0.86rem;
            color: #1e293b;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-npl tr:hover td {
            background-color: #f8fafc;
        }

        /* Print Media Style */
        @media print {
            .no-print, .preset-pills-wrap, .npl-tabs-bar, .btn {
                display: none !important;
            }
            .npl-container {
                background: white !important;
                padding: 0 !important;
            }
            .npl-content-card, .npl-header-card, .npl-kpi-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }
        }
    </style>

    <div class="npl-container container-fluid py-4">
        {{-- ===== HEADER & FILTER BAR ===== --}}
        <div class="npl-header-card">
            <div class="row align-items-center g-3">
                <div class="col-lg-5">
                    <h1 class="npl-title">
                        <i class="bi bi-graph-up-arrow text-primary"></i> Profit &amp; Loss (NPL) Performance
                    </h1>
                    <p class="npl-subtitle mb-0">
                        Real-time Net Profit, Purchasing Values, Sales Records, and Operating Expenses.
                    </p>
                </div>

                {{-- Date Range Inputs --}}
                <div class="col-lg-7">
                    <div class="row g-2 align-items-end justify-content-lg-end no-print">
                        <div class="col-auto">
                            <label class="form-label small fw-bold text-muted mb-1">From Date</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="startDate">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small fw-bold text-muted mb-1">To Date</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="endDate">
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-primary px-3" wire:click="loadData" title="Refresh Calculations">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3 ms-1" wire:click="resetFilters" title="Reset Filters">
                                Reset
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-dark px-3 ms-1" onclick="window.print()" title="Print P&L Statement">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Header Total Stock Value In Hand Showcase --}}
            <div class="header-stock-banner my-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stock-banner-icon">
                        <i class="bi bi-gem"></i>
                    </div>
                    <div>
                        <div class="small fw-bold text-uppercase text-muted">
                            <i class="bi bi-box-seam text-warning me-1"></i> Total Stock Value In Hand
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-0">
                            Rs. {{ number_format($totalStockCostValue, 2) }}
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle ms-2 fs-7 fw-semibold">Cost Valuation</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="bg-white border rounded px-3 py-2 text-start">
                        <div class="text-muted small">Retail / Selling Value</div>
                        <div class="fw-bold text-success">Rs. {{ number_format($totalStockSellingValue, 2) }}</div>
                    </div>
                    <div class="bg-white border rounded px-3 py-2 text-start">
                        <div class="text-muted small">Physical Available Stock</div>
                        <div class="fw-bold text-dark">{{ number_format($availableStockUnits) }} <span class="fw-normal small text-muted">units ({{ number_format($totalStockUnits) }} total)</span></div>
                    </div>
                </div>
            </div>

            {{-- Quick Date Preset Buttons (e.g. Sep 1 to Today) --}}
            <div class="preset-pills-wrap no-print">
                <span class="small fw-bold text-muted d-flex align-items-center me-2">
                    <i class="bi bi-calendar3 me-1"></i> Quick Ranges:
                </span>
                <button type="button" class="btn-preset {{ $preset === 'this_month' ? 'active' : '' }}" wire:click="setDatePreset('this_month')">
                    ⚡ This Month (1st to Today)
                </button>
                <button type="button" class="btn-preset {{ $preset === 'today' ? 'active' : '' }}" wire:click="setDatePreset('today')">
                    Today
                </button>
                <button type="button" class="btn-preset {{ $preset === 'yesterday' ? 'active' : '' }}" wire:click="setDatePreset('yesterday')">
                    Yesterday
                </button>
                <button type="button" class="btn-preset {{ $preset === 'last_7_days' ? 'active' : '' }}" wire:click="setDatePreset('last_7_days')">
                    Last 7 Days
                </button>
                <button type="button" class="btn-preset {{ $preset === 'last_30_days' ? 'active' : '' }}" wire:click="setDatePreset('last_30_days')">
                    Last 30 Days
                </button>
                <button type="button" class="btn-preset {{ $preset === 'last_month' ? 'active' : '' }}" wire:click="setDatePreset('last_month')">
                    Last Month
                </button>
                <button type="button" class="btn-preset {{ $preset === 'this_year' ? 'active' : '' }}" wire:click="setDatePreset('this_year')">
                    This Year
                </button>
                <button type="button" class="btn-preset {{ $preset === 'all_time' ? 'active' : '' }}" wire:click="setDatePreset('all_time')">
                    All Time
                </button>
            </div>

            {{-- Current Period Info Badge --}}
            <div class="mt-3 pt-2 d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                <div>
                    <span class="badge bg-light text-dark border px-2 py-1">
                        <i class="bi bi-clock-history me-1 text-primary"></i> Active Period:
                        @if($startDate && $endDate)
                            <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
                            ({{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }} Days)
                        @elseif($startDate)
                            From <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> onwards
                        @elseif($endDate)
                            Up to <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
                        @else
                            <strong>All Historical Records (Full Lifetime)</strong>
                        @endif
                    </span>
                </div>
                <div wire:loading class="text-primary small fw-semibold">
                    <span class="spinner-border spinner-border-sm me-1"></span> Calculating metrics...
                </div>
            </div>
        </div>

        {{-- ===== TOP 6 KPI CARDS ===== --}}
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-6 g-3 mb-4">
            {{-- 1. Total Sales Value --}}
            <div class="col">
                <div class="npl-kpi-card">
                    <div>
                        <div class="kpi-header">
                            <span class="kpi-label">Total Sales Revenue</span>
                            <div class="kpi-icon-wrap icon-sales">
                                <i class="bi bi-cart-check"></i>
                            </div>
                        </div>
                        <div class="kpi-value text-success">
                            Rs. {{ number_format($grossSales, 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext">
                        <span><i class="bi bi-receipt"></i> {{ $totalSalesCount }} Invoices</span>
                        @if($totalDiscounts > 0)
                            <span class="ms-auto text-muted">(-Rs. {{ number_format($totalDiscounts, 0) }} disc)</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 2. Total Purchasing Value & COGS --}}
            <div class="col">
                <div class="npl-kpi-card">
                    <div>
                        <div class="kpi-header">
                            <span class="kpi-label">Total Purchasing Value</span>
                            <div class="kpi-icon-wrap icon-purchases">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                        <div class="kpi-value text-primary">
                            Rs. {{ number_format($totalPurchasingValue, 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext">
                        <span><i class="bi bi-truck"></i> {{ $totalPurchasingCount }} Orders</span>
                        <span class="ms-auto text-muted small">Items Cost: Rs. {{ number_format($totalCOGS, 0) }}</span>
                    </div>
                </div>
            </div>

            {{-- 3. Total Operating Expenses (Excluding Retained Savings/Deposits) --}}
            <div class="col">
                <div class="npl-kpi-card">
                    <div>
                        <div class="kpi-header">
                            <span class="kpi-label">Operating Expenses</span>
                            <div class="kpi-icon-wrap icon-expenses">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                        <div class="kpi-value text-danger">
                            Rs. {{ number_format($totalExpenses, 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext">
                        <span><i class="bi bi-journal-text"></i> {{ $totalExpensesCount }} Vouchers</span>
                        <span class="ms-auto text-muted">{{ count($expenseCategoriesList) }} Cats</span>
                    </div>
                </div>
            </div>

            {{-- 4. Net Profit & Loss (NPL) --}}
            <div class="col">
                <div class="npl-kpi-card card-profit-highlight">
                    <div>
                        <div class="kpi-header">
                            <span class="kpi-label">Net Profit &amp; Loss (NPL)</span>
                            <div class="kpi-icon-wrap {{ $netProfit >= 0 ? 'icon-profit-pos' : 'icon-profit-neg' }}">
                                <i class="bi bi-{{ $netProfit >= 0 ? 'trophy' : 'exclamation-diamond' }}"></i>
                            </div>
                        </div>
                        <div class="kpi-value {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            Rs. {{ number_format($netProfit, 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext">
                        <span class="badge {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }} ({{ $netProfitPercentage }}%)
                        </span>
                        <span class="ms-auto small opacity-75" title="Net Profit = Sales - (Items Cost + Operating Expenses)">
                            <i class="bi bi-calculator"></i> Formula
                        </span>
                    </div>
                </div>
            </div>

            {{-- 5. Retained Savings & Deposits (Excluded from Expenses) --}}
            <div class="col">
                <div class="npl-kpi-card border-success-subtle">
                    <div>
                        <div class="kpi-header">
                            <span class="kpi-label text-success-emphasis">Savings &amp; Deposits</span>
                            <div class="kpi-icon-wrap icon-deposits">
                                <i class="bi bi-piggy-bank"></i>
                            </div>
                        </div>
                        <div class="kpi-value text-success">
                            Rs. {{ number_format($totalDeposits, 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext">
                        <span><i class="bi bi-safe2"></i> Seedu: Rs. {{ number_format($seeduSavings, 0) }}</span>
                        <span class="ms-auto text-muted">Undiyal: Rs. {{ number_format($undiyalSavings, 0) }}</span>
                    </div>
                </div>
            </div>

            {{-- 6. Total Stock Value In Hand --}}
            <div class="col">
                <div class="npl-kpi-card border-warning-subtle">
                    <div>
                        <div class="kpi-header">
                            <span class="kpi-label text-warning-emphasis">Stock Value In Hand</span>
                            <div class="kpi-icon-wrap icon-stock">
                                <i class="bi bi-gem"></i>
                            </div>
                        </div>
                        <div class="kpi-value text-dark">
                            Rs. {{ number_format($totalStockCostValue, 2) }}
                        </div>
                    </div>
                    <div class="kpi-subtext">
                        <span><i class="bi bi-tag-fill text-success"></i> Retail: Rs. {{ number_format($totalStockSellingValue, 0) }}</span>
                        <span class="ms-auto text-muted">({{ number_format($availableStockUnits) }} units)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== INTERACTIVE TABS BAR ===== --}}
        <div class="npl-tabs-bar no-print">
            <button type="button" class="npl-tab-btn {{ $activeTab === 'summary' ? 'active' : '' }}" wire:click="switchTab('summary')">
                <i class="bi bi-bar-chart-line"></i> P&L Financial Statement
            </button>
            <button type="button" class="npl-tab-btn {{ $activeTab === 'purchasing' ? 'active' : '' }}" wire:click="switchTab('purchasing')">
                <i class="bi bi-box-arrow-in-down"></i> Purchasing Details
                <span class="tab-badge">{{ count($this->purchasingRecords) }}</span>
            </button>
            <button type="button" class="npl-tab-btn {{ $activeTab === 'sales' ? 'active' : '' }}" wire:click="switchTab('sales')">
                <i class="bi bi-receipt"></i> Sale Data
                <span class="tab-badge">{{ count($this->salesRecords) }}</span>
            </button>
            <button type="button" class="npl-tab-btn {{ $activeTab === 'expenses' ? 'active' : '' }}" wire:click="switchTab('expenses')">
                <i class="bi bi-wallet2"></i> Expenses Vouchers
                <span class="tab-badge">{{ count($this->expenseRecords) }}</span>
            </button>
            <button type="button" class="npl-tab-btn {{ $activeTab === 'deposits' ? 'active' : '' }}" wire:click="switchTab('deposits')">
                <i class="bi bi-piggy-bank text-teal"></i> Daily Savings &amp; Deposits
                <span class="tab-badge">{{ count($this->depositRecords) }}</span>
            </button>
        </div>

        {{-- ======================================================== --}}
        {{-- TAB 1: EXECUTIVE P&L STATEMENT                           --}}
        {{-- ======================================================== --}}
        @if($activeTab === 'summary')
            <div class="row g-4">
                {{-- Left: Formal Financial Statement Table --}}
                <div class="col-lg-8">
                    <div class="npl-content-card">
                        <div class="npl-card-header">
                            <h2 class="npl-card-title">
                                <i class="bi bi-file-earmark-text text-primary"></i> Formal P&L Statement
                            </h2>
                            <span class="badge bg-light text-dark border">
                                LKR (Sri Lankan Rupee)
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-npl table-bordered mb-0">
                                <tbody>
                                    {{-- 1. REVENUE SECTION --}}
                                    <tr class="table-light">
                                        <td colspan="2" class="fw-bold text-dark fs-6">
                                            <i class="bi bi-arrow-up-right-circle text-success me-1"></i> 1. OPERATING REVENUE (INCOME)
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-6">Amount (Rs.)</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Gross Sales (Confirmed Customer Invoices)</td>
                                        <td class="text-center text-muted small">{{ $totalSalesCount }} orders</td>
                                        <td class="text-end fw-semibold text-success">{{ number_format($grossSales, 2) }}</td>
                                    </tr>
                                    @if($totalDiscounts > 0)
                                        <tr>
                                            <td class="ps-4 text-muted">Less: Promotional Discounts Given</td>
                                            <td class="text-center text-muted small">Discounts</td>
                                            <td class="text-end text-muted">- {{ number_format($totalDiscounts, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($totalReturns > 0)
                                        <tr>
                                            <td class="ps-4 text-danger">Less: Customer Product Returns</td>
                                            <td class="text-center text-muted small">Returns</td>
                                            <td class="text-end text-danger">- {{ number_format($totalReturns, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-success-subtle">
                                        <td class="ps-4 fw-bold text-success">NET SALES REVENUE</td>
                                        <td></td>
                                        <td class="text-end fw-bold text-success fs-6">Rs. {{ number_format($netRevenue, 2) }}</td>
                                    </tr>

                                    {{-- 2. DIRECT PURCHASES & COGS --}}
                                    <tr class="table-light">
                                        <td colspan="2" class="fw-bold text-dark fs-6 pt-3">
                                            <i class="bi bi-arrow-down-left-circle text-primary me-1"></i> 2. COST OF SALES & INVENTORY PURCHASES
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-6 pt-3">Amount (Rs.)</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Cost of Goods Sold (COGS on Delivered Sales)</td>
                                        <td class="text-center text-muted small">Standard Cost</td>
                                        <td class="text-end text-primary fw-semibold">{{ number_format($totalCOGS, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Total Inventory Purchases In Period (PO & Artisan Stock)</td>
                                        <td class="text-center text-muted small">{{ $totalPurchasingCount }} acquisitions</td>
                                        <td class="text-end text-primary fw-semibold">{{ number_format($totalPurchasingValue, 2) }}</td>
                                    </tr>
                                    <tr class="bg-primary-subtle">
                                        <td class="ps-4 fw-bold text-primary">GROSS TRADING PROFIT (Net Sales – COGS)</td>
                                        <td class="text-center fw-bold text-primary">{{ $grossProfitPercentage }}% Margin</td>
                                        <td class="text-end fw-bold text-primary fs-6">Rs. {{ number_format($grossProfit, 2) }}</td>
                                    </tr>

                                    {{-- 3. OPERATING EXPENSES --}}
                                    <tr class="table-light">
                                        <td colspan="2" class="fw-bold text-dark fs-6 pt-3">
                                            <i class="bi bi-dash-circle text-danger me-1"></i> 3. OPERATING EXPENSES (EXCLUDING RETAINED SAVINGS/DEPOSITS)
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-6 pt-3">Amount (Rs.)</td>
                                    </tr>
                                    @forelse($expenseBreakdown as $catName => $catTotal)
                                        <tr>
                                            <td class="ps-4 text-muted">{{ $catName }}</td>
                                            <td class="text-center text-muted small">
                                                {{ $totalExpenses > 0 ? number_format(($catTotal / $totalExpenses) * 100, 1) : 0 }}%
                                            </td>
                                            <td class="text-end text-danger">{{ number_format($catTotal, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-2 small">No operational expenses recorded in this date range.</td>
                                        </tr>
                                    @endforelse
                                    <tr class="bg-danger-subtle">
                                        <td class="ps-4 fw-bold text-danger">TOTAL OPERATING EXPENSES</td>
                                        <td class="text-center text-muted small">{{ $totalExpensesCount }} vouchers</td>
                                        <td class="text-end fw-bold text-danger fs-6">- Rs. {{ number_format($totalExpenses, 2) }}</td>
                                    </tr>

                                    {{-- 4. RETAINED SAVINGS & DEPOSITS (NOT DEDUCTED FROM PROFIT) --}}
                                    <tr class="table-light">
                                        <td colspan="2" class="fw-bold text-dark fs-6 pt-3">
                                            <i class="bi bi-piggy-bank text-success me-1"></i> 4. RETAINED SAVINGS &amp; CASH DEPOSITS (RETAINED CASH ASSETS)
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-6 pt-3">Amount (Rs.)</td>
                                    </tr>
                                    @forelse($depositBreakdown as $dMethod => $dData)
                                        <tr>
                                            <td class="ps-4 text-muted">{{ $dMethod }}</td>
                                            <td class="text-center text-muted small">{{ $dData['count'] }} entries</td>
                                            <td class="text-end text-success fw-semibold">Rs. {{ number_format($dData['amount'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-2 small">No savings/deposits recorded in this date range.</td>
                                        </tr>
                                    @endforelse
                                    <tr class="bg-success-subtle">
                                        <td class="ps-4 fw-bold text-success">TOTAL RETAINED SAVINGS &amp; DEPOSITS</td>
                                        <td class="text-center text-muted small">{{ $totalDepositsCount }} deposits</td>
                                        <td class="text-end fw-bold text-success fs-6">Rs. {{ number_format($totalDeposits, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="small text-muted py-2 ps-3 bg-light">
                                            <i class="bi bi-shield-check me-1 text-success"></i> <strong>Retained Asset Notice:</strong> Seedu (சீட்டு), Undiyal (உண்டியல்), and Bank Deposits represent retained savings/assets, not business expenses. They are <strong>excluded from Operating Expenses</strong> and <strong>not deducted</strong> from Net Profit.
                                        </td>
                                    </tr>

                                    {{-- 5. FINAL BOTTOMLINE NPL --}}
                                    <tr class="{{ $netProfit >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                        <td class="ps-3 py-3 fw-bold fs-5 text-white">
                                            <i class="bi bi-award me-1"></i> NET PROFIT & LOSS (NPL)
                                        </td>
                                        <td class="text-center py-3 fw-bold text-white fs-6">
                                            {{ $netProfit >= 0 ? 'NET PROFIT' : 'NET LOSS' }} ({{ $netProfitPercentage }}%)
                                        </td>
                                        <td class="text-end py-3 fw-bold fs-5 text-white">
                                            Rs. {{ number_format($netProfit, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="small text-muted py-2 ps-3 bg-light">
                                            <i class="bi bi-info-circle me-1 text-primary"></i> <strong>Formula:</strong> Net Profit = Total Sales (Rs. {{ number_format($grossSales, 2) }}) &minus; [Total Sale Items Cost (Rs. {{ number_format($totalCOGS, 2) }}) + Total Operating Expenses (Rs. {{ number_format($totalExpenses, 2) }})]
                                        </td>
                                    </tr>

                                    {{-- 6. STOCK VALUATION IN HAND --}}
                                    <tr class="table-light">
                                        <td colspan="2" class="fw-bold text-dark fs-6 pt-3">
                                            <i class="bi bi-box-seam text-warning me-1"></i> 6. CURRENT ASSET: STOCK VALUATION IN HAND
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-6 pt-3">Valuation (Rs.)</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Total Stock In Hand (Cost Value / Supplier Cost)</td>
                                        <td class="text-center text-muted small">{{ number_format($availableStockUnits) }} available units</td>
                                        <td class="text-end text-dark fw-bold">Rs. {{ number_format($totalStockCostValue, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4 text-muted">Estimated Retail / Selling Value of Stock In Hand</td>
                                        <td class="text-center text-muted small">{{ number_format($totalStockUnits) }} total units</td>
                                        <td class="text-end text-success fw-semibold">Rs. {{ number_format($totalStockSellingValue, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Right: Sales Channels & Monthly Trend Highlights --}}
                <div class="col-lg-4">
                    {{-- Channel Distribution --}}
                    <div class="npl-content-card mb-4">
                        <div class="npl-card-header">
                            <h3 class="npl-card-title fs-6">
                                <i class="bi bi-pie-chart text-success"></i> Revenue by Channel
                            </h3>
                        </div>
                        <div class="p-3">
                            @forelse($revenueBreakdown as $channel => $cData)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small fw-bold mb-1">
                                        <span>{{ $channel }} ({{ $cData['count'] }} sales)</span>
                                        <span class="text-success">Rs. {{ number_format($cData['amount'], 2) }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $grossSales > 0 ? ($cData['amount'] / $grossSales) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3 small">No channel revenue in this date range.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Payment Methods Breakdown --}}
                    <div class="npl-content-card mb-4">
                        <div class="npl-card-header">
                            <h3 class="npl-card-title fs-6">
                                <i class="bi bi-wallet text-primary"></i> Payment Modes
                            </h3>
                        </div>
                        <div class="p-3">
                            @forelse($paymentMethodWiseRevenue as $method => $amt)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                                    <span class="fw-semibold text-dark"><i class="bi bi-check2-circle text-primary me-1"></i> {{ $method }}</span>
                                    <span class="fw-bold text-dark">Rs. {{ number_format($amt, 2) }}</span>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3 small">No payments recorded.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Cashflow Summary --}}
                    <div class="npl-content-card bg-light border-2">
                        <div class="p-3">
                            <h4 class="small fw-bold text-uppercase text-muted mb-2">Net Cash Position (Inflow - Outflow)</h4>
                            <div class="d-flex justify-content-between mb-1 small">
                                <span>Total Sales Collected:</span>
                                <strong class="text-success">+ Rs. {{ number_format($grossSales, 2) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1 small">
                                <span>Purchases Paid to Suppliers:</span>
                                <strong class="text-primary">- Rs. {{ number_format($totalPurchasingPaid, 2) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small">
                                <span>Expenses Paid Out:</span>
                                <strong class="text-danger">- Rs. {{ number_format($totalExpenses, 2) }}</strong>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Net Cash Flow:</span>
                                <span class="{{ ($grossSales - $totalPurchasingPaid - $totalExpenses) >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rs. {{ number_format($grossSales - $totalPurchasingPaid - $totalExpenses, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- TAB 2: ALL PURCHASING DETAILS (EXPLICIT USER REQUEST)    --}}
        {{-- ======================================================== --}}
        @if($activeTab === 'purchasing')
            <div class="npl-content-card">
                <div class="npl-card-header flex-wrap gap-2">
                    <div>
                        <h2 class="npl-card-title">
                            <i class="bi bi-box-seam text-primary"></i> All Purchasing Details
                        </h2>
                        <span class="small text-muted">Complete stock purchase orders and artisan acquisitions in selected period.</span>
                    </div>

                    {{-- Search & Type Filter --}}
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" placeholder="Search PO or Supplier..." wire:model.live.debounce.300ms="purchaseSearch" style="width: 220px;">
                        <select class="form-select form-select-sm" wire:model.live="purchaseTypeFilter" style="width: 150px;">
                            <option value="all">All Types</option>
                            <option value="regular">Stock PO</option>
                            <option value="handmade">Handmade</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-npl mb-0">
                        <thead>
                            <tr>
                                <th>PO / Bill #</th>
                                <th>Date</th>
                                <th>Supplier Name</th>
                                <th>Type</th>
                                <th class="text-center">Items</th>
                                <th class="text-end">Total Value (Rs.)</th>
                                <th class="text-end">Paid (Rs.)</th>
                                <th class="text-end">Due Balance (Rs.)</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->purchasingRecords as $pRecord)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $pRecord['code'] }}</td>
                                    <td>{{ $pRecord['date'] }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $pRecord['supplier_name'] }}</div>
                                        @if($pRecord['supplier_phone'])
                                            <small class="text-muted">{{ $pRecord['supplier_phone'] }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $pRecord['type'] === 'Handmade Craftsman' ? 'bg-warning text-dark' : 'bg-info-subtle text-info border' }}">
                                            {{ $pRecord['type'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $pRecord['items_count'] }} items</span>
                                    </td>
                                    <td class="text-end fw-bold text-dark">{{ number_format($pRecord['total_amount'], 2) }}</td>
                                    <td class="text-end text-success fw-semibold">{{ number_format($pRecord['paid_amount'], 2) }}</td>
                                    <td class="text-end {{ $pRecord['due_amount'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ number_format($pRecord['due_amount'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $pRecord['status'] === 'received' || $pRecord['status'] === 'paid' ? 'success' : 'warning' }}-subtle text-{{ $pRecord['status'] === 'received' || $pRecord['status'] === 'paid' ? 'success' : 'dark' }} border">
                                            {{ ucfirst($pRecord['status']) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        No purchase orders or artisan bills found for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="5" class="ps-3 text-uppercase">Total Purchasing Summary:</td>
                                <td class="text-end text-primary fs-6">Rs. {{ number_format($totalPurchasingValue, 2) }}</td>
                                <td class="text-end text-success">Rs. {{ number_format($totalPurchasingPaid, 2) }}</td>
                                <td class="text-end text-danger">Rs. {{ number_format($totalPurchasingDue, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- TAB 3: ALL SALE DATA (EXPLICIT USER REQUEST)              --}}
        {{-- ======================================================== --}}
        @if($activeTab === 'sales')
            <div class="npl-content-card">
                <div class="npl-card-header flex-wrap gap-2">
                    <div>
                        <h2 class="npl-card-title">
                            <i class="bi bi-cart-check text-success"></i> Customer Sales Records
                        </h2>
                        <span class="small text-muted">Every confirmed invoice, retail pos, and ecommerce order in selected date range.</span>
                    </div>

                    {{-- Search & Channel Filter --}}
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" placeholder="Search Invoice or Customer..." wire:model.live.debounce.300ms="saleSearch" style="width: 240px;">
                        <select class="form-select form-select-sm" wire:model.live="saleTypeFilter" style="width: 150px;">
                            <option value="all">All Channels</option>
                            <option value="pos">Store POS</option>
                            <option value="ecommerce">E-Commerce</option>
                            <option value="custom">Custom Order</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-npl mb-0">
                        <thead>
                            <tr>
                                <th>Invoice / Sale #</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Channel</th>
                                <th>Payment Method</th>
                                <th class="text-end">Subtotal (Rs.)</th>
                                <th class="text-end">Discount (Rs.)</th>
                                <th class="text-end">Net Total (Rs.)</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->salesRecords as $sale)
                                <tr>
                                    <td class="fw-bold text-dark">
                                        {{ $sale->invoice_number ?: $sale->sale_id }}
                                    </td>
                                    <td>{{ $sale->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($sale->customer)
                                            <div class="fw-bold text-dark">{{ $sale->customer->name }}</div>
                                            <small class="text-muted">{{ $sale->customer->phone }}</small>
                                        @else
                                            <div class="fw-bold text-dark">{{ $sale->walking_customer_name ?: 'Walk-in Customer' }}</div>
                                            @if($sale->walking_customer_phone)
                                                <small class="text-muted">{{ $sale->walking_customer_phone }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ ucfirst($sale->sale_type ?: ($sale->order_source ?: 'POS')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            {{ ucwords(str_replace('_', ' ', $sale->payment_type ?: 'Cash')) }}
                                        </span>
                                    </td>
                                    <td class="text-end text-muted">{{ number_format($sale->subtotal, 2) }}</td>
                                    <td class="text-end text-muted">{{ number_format($sale->discount_amount, 2) }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($sale->total_amount, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $sale->payment_status === 'paid' ? 'success' : 'warning' }}-subtle text-{{ $sale->payment_status === 'paid' ? 'success' : 'dark' }} border">
                                            {{ ucfirst($sale->payment_status ?: 'paid') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        No sales records found for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="7" class="ps-3 text-uppercase">Total Sales Revenue:</td>
                                <td class="text-end text-success fs-6">Rs. {{ number_format($grossSales, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- TAB 4: ALL EXPENSES & DEPOSIT VOUCHERS                   --}}
        {{-- ======================================================== --}}
        @if($activeTab === 'expenses')
            <div class="npl-content-card">
                <div class="npl-card-header flex-wrap gap-2">
                    <div>
                        <h2 class="npl-card-title">
                            <i class="bi bi-wallet2 text-danger"></i> Operating Expenses &amp; Deposit Vouchers
                        </h2>
                        <span class="small text-muted">Shop operational expenses and retained savings (Seedu / Undiyal). Retained savings do not reduce Net Profit.</span>
                    </div>

                    {{-- Search & Category Filter --}}
                    <div class="d-flex flex-wrap gap-2">
                        <select class="form-select form-select-sm" wire:model.live="expenseTypeFilter" style="width: 200px;">
                            <option value="all">All Vouchers (Expenses &amp; Savings)</option>
                            <option value="operating_only">Operating Expenses Only</option>
                            <option value="deposits_only">Retained Savings Only (Seedu/Undiyal)</option>
                        </select>
                        <input type="text" class="form-control form-control-sm" placeholder="Search Voucher..." wire:model.live.debounce.300ms="expenseSearch" style="width: 180px;">
                        <select class="form-select form-select-sm" wire:model.live="expenseCategoryFilter" style="width: 170px;">
                            <option value="all">All Categories</option>
                            @foreach($expenseCategoriesList as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-npl mb-0">
                        <thead>
                            <tr>
                                <th>Voucher #</th>
                                <th>Date</th>
                                <th>Classification</th>
                                <th>Category</th>
                                <th>Description / Purpose</th>
                                <th>Source</th>
                                <th>Recorded By</th>
                                <th class="text-end">Amount (Rs.)</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->expenseRecords as $exp)
                                <tr class="{{ !empty($exp['is_deposit']) ? 'table-light' : '' }}">
                                    <td class="fw-bold text-muted">{{ $exp['id'] }}</td>
                                    <td>{{ $exp['date'] }}</td>
                                    <td>
                                        @if(!empty($exp['is_deposit']))
                                            <span class="badge bg-success-subtle text-success border">
                                                <i class="bi bi-piggy-bank me-1"></i> Retained Saving
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border">
                                                <i class="bi bi-dash-circle me-1"></i> Operating Expense
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($exp['is_deposit']))
                                            <span class="badge bg-success-subtle text-success border">
                                                {{ $exp['category'] }}
                                            </span>
                                            @if(!empty($exp['saving_method']))
                                                <span class="badge bg-info-subtle text-info border ms-1">{{ ucfirst($exp['saving_method']) }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border">
                                                {{ $exp['category'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $exp['description'] }}</span>
                                        @if(!empty($exp['is_deposit']))
                                            <div class="small text-success fst-italic">
                                                <i class="bi bi-shield-check"></i> Retained business cash &mdash; Excluded from P&amp;L expense deduction
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ !empty($exp['is_deposit']) ? 'bg-success-subtle text-success' : 'bg-light text-dark' }} border">{{ $exp['source'] }}</span>
                                    </td>
                                    <td class="text-muted">{{ $exp['recorded_by'] }}</td>
                                    <td class="text-end fw-bold {{ !empty($exp['is_deposit']) ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($exp['amount'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border">
                                            {{ ucfirst($exp['status']) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        No expense vouchers found for this date range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="7" class="ps-3 text-uppercase">Operating Expenses (Deducted from Net Profit):</td>
                                <td class="text-end text-danger fs-6">- Rs. {{ number_format($totalExpenses, 2) }}</td>
                                <td></td>
                            </tr>
                            <tr class="table-success text-success">
                                <td colspan="7" class="ps-3 text-uppercase">Retained Savings &amp; Deposits (Excluded from Expenses / Cash Asset):</td>
                                <td class="text-end text-success fs-6">Rs. {{ number_format($totalDeposits, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- ======================================================== --}}
        {{-- TAB 5: DAILY SAVINGS & DEPOSITS (RETAINED ASSETS)        --}}
        {{-- ======================================================== --}}
        @if($activeTab === 'deposits')
            <div class="npl-content-card">
                <div class="npl-card-header flex-wrap gap-2">
                    <div>
                        <h2 class="npl-card-title">
                            <i class="bi bi-piggy-bank text-success"></i> Daily Savings &amp; Cash Deposits
                        </h2>
                        <span class="small text-muted">Retained cash assets: Seedu (சீட்டு), Undiyal (உண்டியல்), Direct Bank Transfers, and Card POS deposits.</span>
                    </div>

                    {{-- Search & Method Filter --}}
                    <div class="d-flex flex-wrap gap-2">
                        <select class="form-select form-select-sm" wire:model.live="depositMethodFilter" style="width: 180px;">
                            <option value="all">All Saving Methods</option>
                            <option value="seedu">Seedu (சீட்டு)</option>
                            <option value="undiyal">Undiyal (உண்டியல்)</option>
                            <option value="bank_deposit">Bank Deposit</option>
                            <option value="card_deposit">Card / POS Deposit</option>
                        </select>
                        <input type="text" class="form-control form-control-sm" placeholder="Search Deposit..." wire:model.live.debounce.300ms="depositSearch" style="width: 220px;">
                    </div>
                </div>

                {{-- Summary Pills --}}
                <div class="p-3 bg-light border-bottom d-flex flex-wrap gap-3 align-items-center">
                    <div>
                        <span class="text-muted small">Total Deposits:</span>
                        <strong class="text-success ms-1">Rs. {{ number_format($totalDeposits, 2) }}</strong>
                    </div>
                    <div class="border-start ps-3">
                        <span class="text-muted small">Seedu (சீட்டு):</span>
                        <strong class="text-dark ms-1">Rs. {{ number_format($seeduSavings, 2) }}</strong>
                    </div>
                    <div class="border-start ps-3">
                        <span class="text-muted small">Undiyal (உண்டியல்):</span>
                        <strong class="text-dark ms-1">Rs. {{ number_format($undiyalSavings, 2) }}</strong>
                    </div>
                    <div class="border-start ps-3">
                        <span class="text-muted small">Bank Transfers:</span>
                        <strong class="text-primary ms-1">Rs. {{ number_format($bankSavings, 2) }}</strong>
                    </div>
                    <div class="border-start ps-3">
                        <span class="text-muted small">Card / POS:</span>
                        <strong class="text-info ms-1">Rs. {{ number_format($cardSavings, 2) }}</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-npl mb-0">
                        <thead>
                            <tr>
                                <th>Reference #</th>
                                <th>Date</th>
                                <th>Saving Method</th>
                                <th>Source of Funds</th>
                                <th>Description / Purpose</th>
                                <th>Recorded By</th>
                                <th class="text-end">Amount (Rs.)</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->depositRecords as $dep)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $dep->reference_no ?: 'DEP-'.$dep->id }}</span>
                                        @if(str_starts_with($dep->reference_no ?? '', 'EXP-'))
                                            <span class="badge bg-secondary-subtle text-secondary border ms-1" title="Synced from Expense Voucher">From Expense</span>
                                        @endif
                                    </td>
                                    <td>{{ $dep->date ? $dep->date->format('d M Y') : ($dep->created_at ? $dep->created_at->format('d M Y') : '-') }}</td>
                                    <td>
                                        @if($dep->saving_method === 'seedu')
                                            <span class="badge bg-success-subtle text-success border">
                                                <i class="bi bi-safe2 me-1"></i> Seedu (சீட்டு)
                                            </span>
                                        @elseif($dep->saving_method === 'undiyal')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border">
                                                <i class="bi bi-box2 me-1"></i> Undiyal (உண்டியல்)
                                            </span>
                                        @elseif($dep->saving_method === 'bank_deposit')
                                            <span class="badge bg-primary-subtle text-primary border">
                                                <i class="bi bi-bank me-1"></i> Bank Transfer
                                            </span>
                                        @elseif($dep->saving_method === 'card_deposit')
                                            <span class="badge bg-info-subtle text-info border">
                                                <i class="bi bi-credit-card me-1"></i> Card / POS
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark border">
                                                {{ ucwords(str_replace('_', ' ', $dep->saving_method ?: 'General')) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ ucwords(str_replace('_', ' ', $dep->source_of_funds ?: 'Cash Sale')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark">{{ $dep->description ?: 'Daily Retained Saving' }}</span>
                                    </td>
                                    <td class="text-muted">{{ $dep->user->name ?? 'Admin / Staff' }}</td>
                                    <td class="text-end fw-bold text-success fs-6">
                                        {{ number_format($dep->amount, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border">
                                            {{ ucfirst($dep->status ?: 'completed') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-piggy-bank fs-3 d-block mb-1"></i>
                                        No deposit or savings records found for this date range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr class="table-success text-success">
                                <td colspan="6" class="ps-3 text-uppercase">Total Retained Savings &amp; Deposits:</td>
                                <td class="text-end text-success fs-5">Rs. {{ number_format($totalDeposits, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
