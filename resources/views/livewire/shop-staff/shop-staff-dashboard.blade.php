<div>
    @push('styles')
        <style>
            /* Refined Dashboard Styles - Matching Admin Design */
            .btn-jg-blue {
                background-color: var(--primary);
                color: white;
                border: none;
                transition: all 0.3s;
            }

            .btn-jg-blue:hover {
                background-color: var(--primary-600);
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(22, 27, 151, 0.2);
            }

            .stat-card {
                position: relative;
                overflow: hidden;
                background-color: #ffffff !important;
                border-radius: var(--radius-lg);
                border: 1px solid var(--border);
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
                padding: 1.5rem;
                height: 100%;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                flex-direction: column;
            }

            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
                border-color: var(--primary-100);
            }

            .stat-card .icon-bg {
                position: absolute;
                right: -15px;
                bottom: -15px;
                font-size: 5rem;
                opacity: 0.04;
                transform: rotate(-15deg);
                pointer-events: none;
                color: var(--primary);
            }

            .stat-value {
                font-size: 1.85rem;
                font-weight: 700;
                color: var(--text-main);
                margin-bottom: 0.5rem;
                letter-spacing: -0.01em;
            }

            .stat-label {
                color: var(--text-muted);
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.7rem;
                letter-spacing: 0.08em;
                margin-bottom: 0.75rem;
            }

            .chart-card,
            .widget-container {
                background-color: #ffffff !important;
                border-radius: var(--radius-lg);
                border: 1px solid var(--border);
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
                padding: 1.5rem;
                height: 100%;
            }

            .chart-header {
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid var(--border-light);
            }

            .inventory-item {
                padding: 14px;
                border-radius: 12px;
                transition: all 0.2s;
                border: 1px solid var(--border-light);
                background-color: #fcfcfc;
            }

            .inventory-item:hover {
                background-color: #ffffff;
                border-color: var(--primary-100);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            }

            .status-badge {
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .in-stock {
                background: #e0f2fe;
                color: #0369a1;
            }

            .low-stock {
                background: #fff7ed;
                color: #9a3412;
            }

            .out-of-stock {
                background: #fee2e2;
                color: #991b1b;
            }

            .progress {
                height: 8px;
                border-radius: 4px;
                background: #f1f5f9;
            }

            .fw-600 {
                font-weight: 600;
            }

            .fw-700 {
                font-weight: 700;
            }

            .chart-container {
                position: relative;
                height: 350px;
                padding: 1rem;
            }
        </style>
    @endpush

    <!-- Overview Content -->
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <h3 class="fw-bold text-dark mb-1">
                    <i class="bi bi-speedometer2 text-jg-blue me-2"></i> Shop Staff Dashboard
                </h3>
                <p class="text-muted mb-0">Overview of your sales performance and current inventory.</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('shop-staff.store-billing') }}"
                    class="btn btn-jg-blue px-4 py-2 rounded-3 d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-cart-plus fs-5"></i>
                    <span class="fw-600">New Billing</span>
                </a>
            </div>
        </div>

        <!-- Stats Cards Row -->
        <div class="row mb-4">
            <!-- Card 1: Today's Sale -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-graph-up-arrow icon-bg"></i>
                    <div class="stat-label mb-2">Today's Sale</div>
                    <div class="stat-value mb-3">Rs.{{ number_format($todaySales, 0) }}</div>

                    <div class="mt-auto">
                        <button wire:click="openTodaySummary" class="btn btn-sm btn-jg-blue w-100 rounded-2 py-2">
                            <i class="bi bi-eye me-1"></i> View Daily Entry Book
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card 2: Today's Cash -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-currency-dollar icon-bg"></i>
                    <div class="stat-label mb-2">Cash Amount (Today)</div>
                    <div class="stat-value mb-3">Rs.{{ number_format($todayCashAmount, 0) }}</div>

                    <div class="mt-auto">
                        <div class="text-muted small py-2 border-top">
                            <i class="bi bi-cash-stack me-1"></i> Actual cash collected today
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Stock -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-boxes icon-bg"></i>
                    <div class="stat-label mb-2">System Stock</div>
                    <div class="stat-value mb-3">{{ number_format($availableStock) }} <span
                            class="fs-6 text-muted fw-normal">units</span></div>

                    <div class="progress mb-2">
                        <div class="progress-bar bg-info" style="width: {{ $availablePercentage }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Total Capacity</small>
                        <span
                            class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                            {{ number_format($totalStock) }} units
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Recent Activity (Invoices) -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-receipt icon-bg"></i>
                    <div class="stat-label mb-2">Recent Invoices</div>
                    <div class="stat-value mb-3">{{ count($recentSales) }}</div>

                    <div class="text-muted small mt-auto">
                        <i class="bi bi-clock-history me-1"></i> Showing last 5 sales
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('shop-staff.products') }}"
                            class="btn btn-sm btn-outline-jg-blue w-100 rounded-2">
                            View All Products
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Section: Low Stock -->
        <div class="row">
            <!-- Low Stock Alerts -->
            <div class="col-lg-12 mb-4">
                <div class="widget-container h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Low Stock Alerts</h6>
                            <p class="text-muted small mb-0">Items needing attention</p>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded-circle">
                            <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
                        </div>
                    </div>

                    <div class="inventory-container custom-scrollbar">
                        @forelse($productInventory as $product)
                            @php
                                $stockPercentage = $product->total_stock > 0 ? round(($product->available_stock / $product->total_stock) * 100, 2) : 0;
                                $statusClass = $product->available_stock == 0 ? 'out-of-stock' : 'low-stock';
                                $statusText = $product->available_stock == 0 ? 'Out of Stock' : 'Low Stock';
                                $progressClass = $product->available_stock == 0 ? 'bg-danger' : 'bg-warning';
                            @endphp
                            <div class="inventory-item mb-2">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                        <div class="text-muted small">Code: {{ $product->code }} | Brand:
                                            {{ $product->brand ?? 'N/A' }}</div>
                                    </div>
                                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar {{ $progressClass }}"
                                            style="width: {{ max($stockPercentage, 5) }}%;"></div>
                                    </div>
                                    <small class="text-muted fw-500"
                                        style="min-width: 45px;">{{ $product->available_stock }}/{{ $product->total_stock }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-check2-circle text-success fs-1 mb-3 d-block"></i>
                                <p class="text-muted">All stock levels are healthy.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales Table -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="widget-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-dark mb-0">Your Recent Sales</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-3 py-3">Invoice</th>
                                    <th class="border-0 py-3">Customer</th>
                                    <th class="border-0 py-3">Date</th>
                                    <th class="border-0 py-3">Amount</th>
                                    <th class="border-0 py-3">Due</th>
                                    <th class="border-0 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td class="px-3">
                                            <span class="fw-bold">#{{ $sale->invoice_number }}</span>
                                        </td>
                                        <td>{{ $sale->customer_name }}</td>
                                        <td>{{ Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i A') }}</td>
                                        <td class="fw-bold">Rs.{{ number_format($sale->total_amount, 2) }}</td>
                                        <td class="text-danger">Rs.{{ number_format($sale->due_amount, 2) }}</td>
                                        <td>
                                            <span
                                                class="badge rounded-pill {{ $sale->payment_status == 'paid' ? 'bg-success' : ($sale->payment_status == 'partial' ? 'bg-warning' : 'bg-danger') }} bg-opacity-10 {{ $sale->payment_status == 'paid' ? 'text-success' : ($sale->payment_status == 'partial' ? 'text-warning' : 'text-danger') }} border {{ $sale->payment_status == 'paid' ? 'border-success' : ($sale->payment_status == 'partial' ? 'border-warning' : 'border-danger') }} border-opacity-25 px-3">
                                                {{ ucfirst($sale->payment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No sales found for today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today Summary Modal --}}
    @if($showTodaySummary && $summaryData)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); z-index: 1050;">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #161b97 0%, #12167d 100%);">
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-journal-text me-2"></i> Daily Entry Book Summary
                    </h5>
                    <button type="button" wire:click="downloadDailySummary" class="btn btn-sm btn-light rounded-pill px-3 ms-auto me-3 d-flex align-items-center gap-1.5 fw-bold" style="color: #161b97; border: none; font-size: 12px; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                    </button>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeTodaySummary"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- 1. Incomes Section --}}
                    <div class="mb-4">
                        <h6 class="fw-bold text-success mb-3">
                            <i class="bi bi-arrow-down-left-circle-fill me-2"></i> 1. INCOMES & STARTING BALANCE
                        </h6>
                        <div class="border border-success border-opacity-25 rounded-3 overflow-hidden bg-white">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-success table-opacity-10">
                                    <tr class="small text-uppercase fw-bold text-success">
                                        <th class="ps-3">Source / Type</th>
                                        <th class="text-end pe-3">Amount (Rs.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Opening Balance --}}
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-safe text-info fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Today's Opening Balance</span>
                                                    <span class="text-muted d-block small">Initial drawer cash</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-info">
                                            Rs. {{ number_format($summaryData['openingBalance'], 2) }}
                                        </td>
                                    </tr>
                                    {{-- Cash Sales --}}
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-cash-coin text-success fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Cash Sales</span>
                                                    <span class="text-muted d-block small">Cash collected from sales</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-success">
                                            Rs. {{ number_format($summaryData['cashSale'], 2) }}
                                        </td>
                                    </tr>
                                    {{-- Online Sales --}}
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-phone text-primary fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Online Sales</span>
                                                    <span class="text-muted d-block small">Card/Online payments for sales</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-primary">
                                            Rs. {{ number_format($summaryData['onlineSale'], 2) }}
                                        </td>
                                    </tr>
                                    {{-- Bank Sales --}}
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-bank text-secondary fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Bank Transfer Sales</span>
                                                    <span class="text-muted d-block small">Direct deposits for sales</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-secondary">
                                            Rs. {{ number_format($summaryData['bankSale'], 2) }}
                                        </td>
                                    </tr>
                                    {{-- Received Due (Cash) --}}
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-journal-check text-success fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Received Due (Cash)</span>
                                                    <span class="text-muted d-block small">Past customer dues paid in cash</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-success">
                                            Rs. {{ number_format($summaryData['receivedDueCash'], 2) }}
                                        </td>
                                    </tr>
                                    {{-- Received Due (Online) --}}
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-journal-check text-primary fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Received Due (Online)</span>
                                                    <span class="text-muted d-block small">Past customer dues paid online</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-primary">
                                            Rs. {{ number_format($summaryData['receivedDueOnline'], 2) }}
                                        </td>
                                    </tr>
                                    {{-- Received Due (Bank) --}}
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-journal-check text-secondary fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Received Due (Bank)</span>
                                                    <span class="text-muted d-block small">Past customer dues paid via bank</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-secondary">
                                            Rs. {{ number_format($summaryData['receivedDueBank'], 2) }}
                                        </td>
                                    </tr>
                                    {{-- Plating / Services --}}
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-gem text-warning fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Gold Plating / Services (Cash)</span>
                                                    <span class="text-muted d-block small">Advance & balance payments collected in cash</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-warning">
                                            Rs. {{ number_format($summaryData['platingCash'] ?? 0, 2) }}
                                        </td>
                                    </tr>
                                    @if(($summaryData['platingOnline'] ?? 0) > 0)
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-gem text-primary fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Gold Plating / Services (Online)</span>
                                                    <span class="text-muted d-block small">Service payments collected online</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-primary">
                                            Rs. {{ number_format($summaryData['platingOnline'] ?? 0, 2) }}
                                        </td>
                                    </tr>
                                    @endif
                                    {{-- Total Income Subtotal --}}
                                    <tr class="table-success border-top-2">
                                        <td class="ps-3 py-3 fw-bold text-success">
                                            TOTAL INCOME (With Starting Balance)
                                        </td>
                                        <td class="text-end pe-3 py-3 fs-5 fw-bold text-success">
                                            Rs. {{ number_format($summaryData['totalIncomeWithOpening'], 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 2. Expenses Section --}}
                    <div class="mb-4">
                        <h6 class="fw-bold text-danger mb-3">
                            <i class="bi bi-arrow-up-right-circle-fill me-2"></i> 2. EXPENSES
                        </h6>
                        <div class="border border-danger border-opacity-25 rounded-3 overflow-hidden bg-white">
                            <table class="table align-middle mb-0">
                                <thead class="table-danger table-opacity-10">
                                    <tr class="small text-uppercase fw-bold text-danger">
                                        <th class="ps-3">Expense Details</th>
                                        <th class="text-end pe-3">Amount (Rs.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-3 py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-wallet2 text-danger fs-5"></i>
                                                <div>
                                                    <span class="fw-600 text-dark">Staff Expenses</span>
                                                    <span class="text-muted d-block small">Approved petty-cash staff expenses</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 fw-bold text-danger">
                                            Rs. {{ number_format($summaryData['todayExpenses'], 2) }}
                                        </td>
                                    </tr>
                                    <tr class="table-danger border-top-2">
                                        <td class="ps-3 py-3 fw-bold text-danger">
                                            TOTAL EXPENSES
                                        </td>
                                        <td class="text-end pe-3 py-3 fs-5 fw-bold text-danger">
                                            Rs. {{ number_format($summaryData['todayExpenses'], 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 3. Net Income Summary Section --}}
                    <div class="p-4 rounded-3 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #172554 100%);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h5 class="fw-bold text-white mb-0">
                                    <i class="bi bi-calculator-fill me-2"></i> NET INCOME
                                </h5>
                                <span class="text-white-50 small">Formula: Total Income - Total Expenses</span>
                            </div>
                            <span class="fw-bold" style="font-size: 2.25rem;">
                                Rs. {{ number_format($summaryData['netIncome'], 2) }}
                            </span>
                        </div>
                    </div>

                </div>
                
                @php
                    $waText = "📊 *Daily Entry Book Summary (" . date('Y-m-d') . ")*\n\n" .
                              "💰 *Opening Balance:* Rs. " . number_format($summaryData['openingBalance'], 2) . "\n\n" .
                              "📥 *INCOMES*\n" .
                              "   💵 Cash Sales: Rs. " . number_format($summaryData['cashSale'], 2) . "\n" .
                              "   📱 Online Sales: Rs. " . number_format($summaryData['onlineSale'], 2) . "\n" .
                              "   🏦 Bank Sales: Rs. " . number_format($summaryData['bankSale'], 2) . "\n" .
                              "   💳 Received Dues (Cash): Rs. " . number_format($summaryData['receivedDueCash'], 2) . "\n" .
                              "   💳 Received Dues (Online): Rs. " . number_format($summaryData['receivedDueOnline'], 2) . "\n" .
                              "   💳 Received Dues (Bank): Rs. " . number_format($summaryData['receivedDueBank'], 2) . "\n" .
                              ((($summaryData['platingCash'] ?? 0) > 0) ? ("   ✨ Plating Services (Cash): Rs. " . number_format($summaryData['platingCash'], 2) . "\n") : "") .
                              ((($summaryData['platingOnline'] ?? 0) > 0) ? ("   💳 Plating Services (Online): Rs. " . number_format($summaryData['platingOnline'], 2) . "\n") : "") .
                              "   ➕ *Total Income (with Opening Balance):* Rs. " . number_format($summaryData['totalIncomeWithOpening'], 2) . "\n\n" .
                              "📤 *EXPENSES*\n" .
                              "   💸 Staff Expenses: Rs. " . number_format($summaryData['todayExpenses'], 2) . "\n" .
                              "   ➖ *Total Expenses:* Rs. " . number_format($summaryData['todayExpenses'], 2) . "\n\n" .
                              "🧮 *NET INCOME (Total Income - Expenses):* Rs. " . number_format($summaryData['netIncome'], 2);
                    $waUrl = "https://wa.me/94752919654?text=" . urlencode($waText);
                @endphp

                <div class="modal-footer border-top-0 pt-0 pb-3 px-4 d-flex justify-content-between">
                    <a href="{{ $waUrl }}" target="_blank" class="btn btn-success fw-bold rounded-pill px-4 py-2 me-auto" style="background-color: #25D366; border-color: #25D366; box-shadow: 0 4px 6px rgba(37, 211, 102, 0.2);">
                        <i class="bi bi-whatsapp me-2"></i> Send to WhatsApp
                    </a>
                    <button type="button" class="btn btn-light border-2 rounded-pill px-4 fw-bold" wire:click="closeTodaySummary">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif


</div>