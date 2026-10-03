<div>
    @push('styles')
    <style>
        /* Refined Dashboard Styles */
        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card .icon-bg {
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 8rem;
            opacity: 0.05;
            transform: rotate(-15deg);
            pointer-events: none;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        .stat-label {
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .chart-card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .chart-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .widget-container {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
        }

        .inventory-item {
            padding: 12px;
            border-radius: 12px;
            transition: background 0.2s;
            border: 1px solid transparent;
        }

        .inventory-item:hover {
            background: var(--border-light);
            border-color: var(--border);
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .in-stock { background: #ffffff; color: var(--primary); border: 1px solid var(--primary); }
        .low-stock { background: #fef2f2; color: var(--danger); border: 1px solid var(--danger); }
        .out-of-stock { background: #000000; color: #ffffff; }

        .progress {
            height: 6px;
            border-radius: 3px;
            background: var(--border-light);
        }
    </style>
    @endpush

    <!-- Overview Content -->
    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h3 class="fw-bold text-dark mb-2">
                    <i class="bi bi-speedometer2 text-jg-blue me-2"></i> Overview
                </h3>
                <p class="text-muted mb-0">Get a complete view of your product performance and stock activity.</p>
            </div>
            <div>
                <button wire:click="openTodaySummary" class="btn bg-jg-blue text-white rounded-pill px-4 shadow-sm fw-bold border-0">
                    <i class="bi bi-clipboard-data me-2"></i> View Today Summary
                </button>
            </div>
        </div>
        <!-- Stats Cards Row - Updated to 4 cards -->
        <div class="row mb-4">
            <!-- Card 1: Total Sale -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-graph-up-arrow icon-bg"></i>
                    <div class="stat-label mb-2">Total Sale</div>
                    <div class="stat-value mb-3">Rs.{{ number_format($totalSales, 0) }}</div>
                    
                    <div class="progress mb-2">
                        <div class="progress-bar bg-jg-blue" style="width: {{ $revenuePercentage }}%;"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Total Revenue</small>
                        <span class="badge bg-white text-jg-blue border border-jg-blue border-opacity-25 px-2 py-1">
                            Rs.{{ number_format($totalRevenue, 0) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Card 2: Total Delivery Sales -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-truck icon-bg"></i>
                    <div class="stat-label mb-2">Total Delivery Sales</div>
                    <div class="stat-value mb-3">Rs.{{ number_format($totalDeliverySales, 0) }}</div>
                    
                    @php
                        $deliveryRevenuePercentage = $totalDeliverySales > 0 ? round(($deliverySalesRevenue / $totalDeliverySales) * 100, 1) : 0;
                    @endphp
                    
                    <div class="progress mb-2">
                        <div class="progress-bar bg-primary" style="width: {{ $deliveryRevenuePercentage }}%;"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Delivery Revenue</small>
                        <span class="badge  bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                            Rs.{{ number_format($deliverySalesRevenue, 0) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Card 3: Total Stock -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-boxes icon-bg"></i>
                    <div class="stat-label mb-2">Total Stock</div>
                    <div class="stat-value mb-3">{{ number_format($totalStock) }} <span class="fs-6 text-muted fw-normal">units</span></div>
                    
                    <div class="progress mb-2">
                        <div class="progress-bar bg-info" style="width: {{ $availablePercentage }}%;"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Available Stock</small>
                        <span class="badge  bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                            {{ number_format($availableStock) }} units
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Card 4: Total Expense -->
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <i class="bi bi-receipt icon-bg"></i>
                    <div class="stat-label mb-2">Total Expense</div>
                    <div class="stat-value mb-3">Rs.{{ number_format($totalExpenses, 0) }}</div>
                    
                    @php
                        $todayExpensePercentage = $totalExpenses > 0 ? round(($todayTotal / $totalExpenses) * 100, 1) : 0;
                    @endphp
                    
                    <div class="progress mb-2">
                        <div class="progress-bar bg-danger" style="width: {{ min($todayExpensePercentage, 100) }}%;"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Today Expense</small>
                        <span class="badge  bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                            Rs.{{ number_format($todayTotal, 0) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equal Size Cards Section -->
        <div class="row">
            <!-- Sales Overview By Daily Trend Card -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="chart-card">
                    <div class="chart-header d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-mobile-2">
                            <h6 class="mb-1">Daily Sales Trend</h6>
                            <p class="text-muted mb-0 small">Sales performance over the last 7 days</p>
                        </div>
                       
                    </div>
                    <!-- Add scrollable wrapper for the chart -->
                    <div class="chart-scroll-container">
                        <div class="chart-container" style="min-width: 300px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Status Card -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="widget-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Inventory Status</h6>
                            <p class="text-muted small mb-0">Current stock levels and alerts</p>
                        </div>
                        <a href="{{ route('admin.Product-stock-details') }}" class="btn btn-sm btn-outline-primary border-0 bg-transparent text-primary">
                            <i class="bi bi-arrow-right-circle fs-5"></i>
                        </a>
                    </div>

                    <!-- Scrollable container -->
                    <div class="inventory-container custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
                        @forelse($ProductInventory as $Product)
                        @php
                        $stockPercentage = $Product->total_stock > 0 ?
                        round(($Product->available_stock / $Product->total_stock) * 100, 2) : 0;

                        if ($Product->available_stock == 0) {
                            $statusClass = 'out-of-stock';
                            $statusText = 'Out of Stock';
                            $progressClass = 'bg-danger';
                        } elseif ($stockPercentage <= 25) { 
                            $statusClass='low-stock'; 
                            $statusText='Low Stock';
                            $progressClass='bg-warning'; 
                        } else { 
                            $statusClass='in-stock'; 
                            $statusText='In Stock';
                            $progressClass='bg-success'; 
                        } 
                        @endphp 
                        <div class="inventory-item mb-2">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-bold text-dark">{{ $Product->name }}</div>
                                    <div class="text-muted small">SKU: {{ $Product->code }}</div>
                                </div>
                                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar {{ $progressClass }}" style="width: {{ $stockPercentage }}%;"></div>
                                </div>
                                <small class="text-muted fw-500" style="min-width: 45px;">{{ $Product->available_stock }}/{{ $Product->total_stock }}</small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="bi bi-box-seam text-muted fs-1 mb-3 d-block"></i>
                            <p class="text-muted">No Product inventory data available.</p>
                        </div>
                        @endforelse
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
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-clipboard-data me-2"></i> Today's Summary
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeTodaySummary"></button>
                </div>
                <div class="modal-body p-4">

                    {{-- Opening Balance --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-safe text-info me-2"></i>
                                <span class="fw-bold text-dark">Total Opening Balance</span>
                            </div>
                            <span class="fs-5 fw-bold text-info">Rs. {{ number_format($summaryData['openingBalance'], 2) }}</span>
                        </div>
                    </div>

                    {{-- Total Sale --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <i class="bi bi-cart-check text-success me-2"></i>
                                <span class="fw-bold text-dark">Total Sale</span>
                                <span class="badge bg-success bg-opacity-10 text-success ms-2">{{ $summaryData['totalSaleCount'] }} orders</span>
                            </div>
                            <span class="fs-5 fw-bold text-success">Rs. {{ number_format($summaryData['totalSale'], 2) }}</span>
                        </div>
                        <hr class="my-2 opacity-25">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="text-center p-2 rounded-2" style="background: rgba(255,255,255,0.7);">
                                    <i class="bi bi-cash-coin text-success d-block mb-1"></i>
                                    <small class="text-muted d-block">Cash</small>
                                    <span class="fw-bold text-dark">Rs. {{ number_format($summaryData['cashSale'], 2) }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 rounded-2" style="background: rgba(255,255,255,0.7);">
                                    <i class="bi bi-phone text-primary d-block mb-1"></i>
                                    <small class="text-muted d-block">Online</small>
                                    <span class="fw-bold text-dark">Rs. {{ number_format($summaryData['onlineSale'], 2) }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 rounded-2" style="background: rgba(255,255,255,0.7);">
                                    <i class="bi bi-bank text-info d-block mb-1"></i>
                                    <small class="text-muted d-block">Bank</small>
                                    <span class="fw-bold text-dark">Rs. {{ number_format($summaryData['bankSale'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Expenses --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 1px solid #fecaca;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-wallet2 text-danger me-2"></i>
                                <span class="fw-bold text-dark">All Expenses</span>
                            </div>
                            <span class="fs-5 fw-bold text-danger">- Rs. {{ number_format($summaryData['todayExpenses'], 2) }}</span>
                        </div>
                    </div>

                    {{-- Due Amount --}}
                    @if($summaryData['todayDue'] > 0)
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                <span class="fw-bold text-dark">Due Amount</span>
                            </div>
                            <span class="fs-5 fw-bold text-warning">Rs. {{ number_format($summaryData['todayDue'], 2) }}</span>
                        </div>
                    </div>
                    @endif

                    <hr class="my-3">

                    {{-- Revenue --}}
                    <div class="p-3 rounded-3 mb-3" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 1px solid #ddd6fe;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-cash-stack me-2" style="color: #7c3aed;"></i>
                                <span class="fw-bold text-dark">Today Revenue</span>
                                <small class="text-muted ms-1">(Sale - Expenses)</small>
                            </div>
                            <span class="fs-5 fw-bold" style="color: #7c3aed;">Rs. {{ number_format($summaryData['todayRevenue'], 2) }}</span>
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-calculator me-2"></i>Profit Calculation</h6>

                    {{-- Product Cost --}}
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 rounded-2 mb-2" style="background: #f8fafc;">
                        <span class="text-muted"><i class="bi bi-box-seam me-2"></i>Product Cost (Supplier Price)</span>
                        <span class="fw-bold text-dark">Rs. {{ number_format($summaryData['totalCost'], 2) }}</span>
                    </div>

                    {{-- Gross Profit --}}
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 rounded-2 mb-2" style="background: #f0fdf4;">
                        <span class="text-muted"><i class="bi bi-graph-up me-2"></i>Gross Profit (Sale - Cost)</span>
                        <span class="fw-bold {{ $summaryData['grossProfit'] >= 0 ? 'text-success' : 'text-danger' }}">Rs. {{ number_format($summaryData['grossProfit'], 2) }}</span>
                    </div>

                    {{-- Net Profit --}}
                    <div class="p-3 rounded-3 mt-3" style="background: linear-gradient(135deg, {{ $summaryData['netProfit'] >= 0 ? '#059669' : '#dc2626' }} 0%, {{ $summaryData['netProfit'] >= 0 ? '#047857' : '#b91c1c' }} 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-trophy text-white me-2"></i>
                                <span class="fw-bold text-white">Net Profit</span>
                                <small class="text-white-50 ms-1">(Gross Profit - Expenses)</small>
                            </div>
                            <span class="fs-4 fw-bold text-white">Rs. {{ number_format($summaryData['netProfit'], 2) }}</span>
                        </div>
                    </div>

                </div>
                
                @php
                    $waText = "📈 *ADMIN TODAY SUMMARY (" . date('Y-m-d') . ")*\n\n" .
                              "💰 *Opening Balance:* Rs. " . number_format($summaryData['openingBalance'], 2) . "\n" .
                              "🛒 *Total Sale (" . $summaryData['totalSaleCount'] . " orders):* Rs. " . number_format($summaryData['totalSale'], 2) . "\n" .
                              "   💵 Cash: Rs. " . number_format($summaryData['cashSale'], 2) . "\n" .
                              "   📱 Online: Rs. " . number_format($summaryData['onlineSale'], 2) . "\n" .
                              "   🏦 Bank: Rs. " . number_format($summaryData['bankSale'], 2) . "\n\n" .
                              "💸 *Expenses:* Rs. " . number_format($summaryData['todayExpenses'], 2) . "\n" .
                              "⚠️ *Due Amount:* Rs. " . number_format($summaryData['todayDue'], 2) . "\n\n" .
                              "📈 *Today Revenue:* Rs. " . number_format($summaryData['todayRevenue'], 2) . "\n" .
                              "🛍️ *Product Cost:* Rs. " . number_format($summaryData['totalCost'], 2) . "\n" .
                              "💹 *Gross Profit:* Rs. " . number_format($summaryData['grossProfit'], 2) . "\n" .
                              "🏆 *Net Profit:* Rs. " . number_format($summaryData['netProfit'], 2);
                    $waUrl = "https://wa.me/94761265772?text=" . urlencode($waText);
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

    @push('scripts')
    <script>
        // Prepare data from PHP
        const dailyLabels = @json(collect($dailySales)->pluck('date'));
        const dailyTotals = @json(collect($dailySales)->pluck('total_sales'));

        // Chart instance
        let salesChartInstance = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize daily sales chart
            initializeDailySalesChart();
        });

        function initializeDailySalesChart() {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;
            
            salesChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Daily Sales (Rs.)',
                        backgroundColor: 'rgba(22, 27, 151, 0.1)',
                        borderColor: '#161b97',
                        borderWidth: 3,
                        pointBackgroundColor: '#161b97',
                        pointBorderColor: '#000',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        data: dailyTotals,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 20
                        }
                    },
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            labels: {
                                font: { size: 13, weight: '500' },
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: { 
                            backgroundColor: '#1f2937',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Rs. ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { 
                                color: '#f3f4f6',
                                drawBorder: false 
                            },
                            ticks: {
                                font: { size: 12 },
                                color: '#6b7280',
                                callback: function(value) {
                                    if (value >= 1000) return 'Rs.' + (value / 1000) + 'k';
                                    return 'Rs.' + value;
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 12, weight: '500' },
                                color: '#6b7280'
                            }
                        }
                    }
                }
            });
        }

        // Handle window resize for chart
        window.addEventListener('resize', function() {
            if (salesChartInstance) {
                salesChartInstance.update();
            }
        });
    </script>
    @endpush
</div>