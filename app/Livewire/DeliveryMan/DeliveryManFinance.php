<?php

namespace App\Livewire\DeliveryMan;

use App\Models\DeliverySale;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\StaffExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Delivery Finance & Sales Analytics')]
#[Layout('components.layouts.delivery-man')]
class DeliveryManFinance extends Component
{
    use WithPagination;

    // Filters
    public $search = '';

    public $statusFilter = 'all'; // all, delivered, in_transit, packed, pending, returned

    public $paymentFilter = 'all'; // all, paid, due, partial

    public $courierFilter = 'all'; // all, Domex, Post, etc.

    public $dateFilter = 'all'; // all, today, yesterday, week, month, custom

    public $dateFrom = '';

    public $dateTo = '';

    public $dateField = 'created_at'; // created_at, delivered_at

    public $perPage = 15;

    // Chart timeframe: 7_days, 14_days, 30_days, this_month
    public $chartPeriod = '7_days';

    // Payment collection modal
    public $showPaymentModal = false;

    public $paymentSaleId = null;

    public $paymentSale = null;

    public $paymentAmount = 0;

    public $paymentMethod = 'cash';

    public $paymentNotes = 'COD collection from customer';

    // Order Details modal
    public $showDetailsModal = false;

    public $selectedSale = null;

    // Daily Settlement / Handover modal
    public $showSettlementModal = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        if (request()->has('status')) {
            $this->statusFilter = request('status');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPaymentFilter()
    {
        $this->resetPage();
    }

    public function updatedCourierFilter()
    {
        $this->resetPage();
    }

    public function updatedDateField()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        if ($this->dateFilter === 'today') {
            $this->dateFrom = now()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        } elseif ($this->dateFilter === 'yesterday') {
            $this->dateFrom = now()->subDay()->format('Y-m-d');
            $this->dateTo = now()->subDay()->format('Y-m-d');
        } elseif ($this->dateFilter === 'week') {
            $this->dateFrom = now()->startOfWeek()->format('Y-m-d');
            $this->dateTo = now()->endOfWeek()->format('Y-m-d');
        } elseif ($this->dateFilter === 'month') {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        } elseif ($this->dateFilter === 'all') {
            $this->dateFrom = '';
            $this->dateTo = '';
        }
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->dateFilter = 'custom';
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->dateFilter = 'custom';
        $this->resetPage();
    }

    public function setQuickDate($preset)
    {
        $this->dateFilter = $preset;
        if ($preset === 'today') {
            $this->dateFrom = now()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        } elseif ($preset === 'yesterday') {
            $this->dateFrom = now()->subDay()->format('Y-m-d');
            $this->dateTo = now()->subDay()->format('Y-m-d');
        } elseif ($preset === 'week') {
            $this->dateFrom = now()->startOfWeek()->format('Y-m-d');
            $this->dateTo = now()->endOfWeek()->format('Y-m-d');
        } elseif ($preset === 'month') {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        } elseif ($preset === 'all') {
            $this->dateFrom = '';
            $this->dateTo = '';
        }
        $this->resetPage();
    }

    public function setSameDate($date)
    {
        $this->dateFilter = 'custom';
        $this->dateFrom = $date;
        $this->dateTo = $date;
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function setChartPeriod($period)
    {
        $this->chartPeriod = $period;
        $this->dispatch('update-sales-chart', data: $this->getChartData());
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->paymentFilter = 'all';
        $this->courierFilter = 'all';
        $this->dateFilter = 'all';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->dateField = 'created_at';
        $this->resetPage();
    }

    // --- Order Details Modal ---
    public function openDetailsModal($saleId)
    {
        $this->selectedSale = Sale::with(['customer', 'deliverySale', 'items.product', 'payments', 'user'])
            ->find($saleId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedSale = null;
    }

    public function openPaymentModal($saleId)
    {
        $sale = Sale::with(['customer', 'deliverySale'])->find($saleId);
        if (! $sale) {
            return;
        }

        $courier = $sale->deliverySale->delivery_method ?? 'Courier';
        $isCourier = in_array(strtolower($courier), ['domex', 'post']);

        $this->paymentSaleId = $sale->id;
        $this->paymentSale = $sale;
        $this->paymentAmount = $sale->due_amount > 0 ? (float) $sale->due_amount : (float) $sale->total_amount;
        $this->paymentMethod = $isCourier ? 'bank_transfer' : 'cash';
        $this->paymentNotes = $isCourier
            ? "COD remittance payout received from {$courier} service center"
            : 'COD settlement collected by delivery personnel';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentSaleId = null;
        $this->paymentSale = null;
        $this->paymentAmount = 0;
        $this->paymentNotes = '';
    }

    public function savePayment()
    {
        if (! $this->paymentSale) {
            return;
        }

        $maxDue = max(0.01, (float) ($this->paymentSale->due_amount ?: $this->paymentSale->total_amount));
        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'min:0.01', "max:{$maxDue}"],
            'paymentMethod' => 'required|string|in:cash,bank_transfer',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($this->paymentSaleId);
            $amount = (float) $this->paymentAmount;

            Payment::create([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'amount' => $amount,
                'payment_method' => $this->paymentMethod,
                'is_completed' => true,
                'payment_date' => now(),
                'status' => 'paid',
                'collected_by' => Auth::id(),
                'notes' => $this->paymentNotes,
            ]);

            $newDue = max(0, (float) $sale->due_amount - $amount);
            $sale->due_amount = $newDue;
            $sale->payment_status = ($newDue <= 0) ? 'paid' : 'partial';

            if (in_array($sale->delivery_status, ['pending', 'packed', 'in_transit'])) {
                $sale->delivery_status = 'delivered';
                $sale->delivered_at = now();
                $sale->delivered_by = Auth::id();
                if ($sale->deliverySale) {
                    $sale->deliverySale->update(['status' => 'Delivered']);
                }
            }

            $sale->save();

            DB::commit();

            $this->closePaymentModal();
            $this->dispatch('show-toast', type: 'success', message: 'Payment recorded successfully!');
            $this->dispatch('update-sales-chart', data: $this->getChartData());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error recording payment: '.$e->getMessage());
        }
    }

    // --- Settlement Modal ---
    public function openSettlementModal()
    {
        $this->showSettlementModal = true;
    }

    public function closeSettlementModal()
    {
        $this->showSettlementModal = false;
    }

    /**
     * Get base delivery orders query scoped to the logged in delivery man.
     */
    protected function getDeliveryQuery()
    {
        $currentUser = Auth::user();
        $isDeliveryMan = $currentUser && ($currentUser->staff_type === 'delivery_man' || $currentUser->role === 'delivery_man');

        return Sale::query()
            ->whereIn('order_source', ['whatsapp', 'ecommerce', 'delivery_man'])
            ->where('order_source', '!=', 'pos')
            ->where(function ($q) use ($currentUser, $isDeliveryMan) {
                if ($isDeliveryMan) {
                    $q->where('user_id', $currentUser->id)
                        ->orWhere('delivered_by', $currentUser->id)
                        ->orWhereNull('delivered_by')
                        ->orWhereIn('order_source', ['whatsapp', 'ecommerce']);
                }
            });
    }

    public function setCourierFilter($courier)
    {
        $this->courierFilter = $courier;
        $this->resetPage();
    }

    /**
     * Compile chart time series data.
     */
    public function getChartData()
    {
        $daysCount = 7;
        if ($this->chartPeriod === '14_days') {
            $daysCount = 14;
        } elseif ($this->chartPeriod === '30_days') {
            $daysCount = 30;
        } elseif ($this->chartPeriod === 'this_month') {
            $daysCount = (int) now()->format('j');
        }

        $startDate = now()->subDays($daysCount - 1)->startOfDay();
        $endDate = now()->endOfDay();

        $base = $this->getDeliveryQuery();
        $saleIds = (clone $base)->pluck('id');

        // Eager aggregate sales and counts by date in single SQL queries
        $salesByDate = (clone $base)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as order_date, SUM(total_amount) as total_sales')
            ->groupBy('order_date')
            ->pluck('total_sales', 'order_date');

        // Domex Sales by Date
        $domexSalesByDate = (clone $base)
            ->whereHas('deliverySale', function ($dq) {
                $dq->whereRaw('LOWER(delivery_method) = ?', ['domex']);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as order_date, SUM(total_amount) as total_sales')
            ->groupBy('order_date')
            ->pluck('total_sales', 'order_date');

        // Post Sales by Date
        $postSalesByDate = (clone $base)
            ->whereHas('deliverySale', function ($dq) {
                $dq->whereRaw('LOWER(delivery_method) = ?', ['post']);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as order_date, SUM(total_amount) as total_sales')
            ->groupBy('order_date')
            ->pluck('total_sales', 'order_date');

        $countsByDate = (clone $base)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as order_date, COUNT(*) as order_count')
            ->groupBy('order_date')
            ->pluck('order_count', 'order_date');

        // Eager aggregate payments by date in single SQL query
        $paymentsByDate = Payment::whereIn('sale_id', $saleIds)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('DATE(payment_date) as pay_date, SUM(amount) as total_collected')
            ->groupBy('pay_date')
            ->pluck('total_collected', 'pay_date');

        $labels = [];
        $salesSeries = [];
        $collectionsSeries = [];
        $ordersCountSeries = [];
        $domexSeries = [];
        $postSeries = [];

        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $dateObj = now()->subDays($i);
            $dateStr = $dateObj->toDateString();
            $label = $dateObj->format('M d');

            $labels[] = $label;
            $salesSeries[] = (float) ($salesByDate[$dateStr] ?? 0);
            $collectionsSeries[] = (float) ($paymentsByDate[$dateStr] ?? 0);
            $ordersCountSeries[] = (int) ($countsByDate[$dateStr] ?? 0);
            $domexSeries[] = (float) ($domexSalesByDate[$dateStr] ?? 0);
            $postSeries[] = (float) ($postSalesByDate[$dateStr] ?? 0);
        }

        return [
            'labels' => $labels,
            'sales' => $salesSeries,
            'collections' => $collectionsSeries,
            'counts' => $ordersCountSeries,
            'domex' => $domexSeries,
            'post' => $postSeries,
        ];
    }

    public function render()
    {
        $baseQuery = $this->getDeliveryQuery();
        $allSaleIds = (clone $baseQuery)->pluck('id');

        // 1. Overall & Period KPI Metrics
        // Total delivery volume
        $totalDeliverySales = (clone $baseQuery)->sum('total_amount');
        $totalDeliveryOrdersCount = (clone $baseQuery)->count();

        // Delivery Charges collected / earned
        $totalDeliveryCharges = DeliverySale::whereIn('sale_id', $allSaleIds)->sum('delivery_charge');

        // Completed deliveries
        $completedDeliveriesCount = (clone $baseQuery)->where('delivery_status', 'delivered')->count();
        $completedDeliveriesAmount = (clone $baseQuery)->where('delivery_status', 'delivered')->sum('total_amount');

        // Pending deliveries (pending, packed, in_transit)
        $pendingDeliveriesCount = (clone $baseQuery)->whereIn('delivery_status', ['pending', 'packed', 'in_transit'])->count();
        $pendingCodAmount = (clone $baseQuery)->whereIn('delivery_status', ['pending', 'packed', 'in_transit'])->sum('due_amount');

        // Returned / cancelled deliveries
        $returnedDeliveriesCount = (clone $baseQuery)->whereIn('delivery_status', ['returned', 'cancelled'])->count();
        $returnedDeliveriesAmount = (clone $baseQuery)->whereIn('delivery_status', ['returned', 'cancelled'])->sum('total_amount');

        // Collections (COD & online payments)
        $totalCollectedCod = Payment::whereIn('sale_id', $allSaleIds)->sum('amount');
        $todayCollectedCod = Payment::whereIn('sale_id', $allSaleIds)
            ->whereDate('payment_date', today())
            ->sum('amount');

        // Cash vs Bank collections
        $cashCollected = Payment::whereIn('sale_id', $allSaleIds)
            ->where('payment_method', 'cash')
            ->sum('amount');
        $bankCollected = Payment::whereIn('sale_id', $allSaleIds)
            ->where('payment_method', '!=', 'cash')
            ->sum('amount');

        // Staff Expenses (approved/non-rejected only)
        $currentUser = Auth::user();
        $isDeliveryMan = $currentUser && ($currentUser->staff_type === 'delivery_man' || $currentUser->role === 'delivery_man');

        $expenseQuery = StaffExpense::query()->where('status', '!=', 'rejected');
        if ($isDeliveryMan) {
            $expenseQuery->where('staff_id', $currentUser->id);
        }

        $totalExpenses = (clone $expenseQuery)->sum('amount');
        $todayExpenses = (clone $expenseQuery)->whereDate('expense_date', today())->sum('amount');

        // Net Cash in Hand to Remit: (Cash Collected - Approved Expenses)
        $netCashInHand = max(0, $cashCollected - $totalExpenses);
        $todayNetCashInHand = max(0, $todayCollectedCod - $todayExpenses);

        // --- Courier Breakdown & Separate Financial Metrics ---
        // Domex Metrics
        $domexBaseQuery = (clone $baseQuery)->whereHas('deliverySale', function ($dq) {
            $dq->whereRaw('LOWER(delivery_method) = ?', ['domex']);
        });
        $domexSaleIds = (clone $domexBaseQuery)->pluck('id');
        $domexOrdersCount = (clone $domexBaseQuery)->count();
        $domexSalesAmount = (clone $domexBaseQuery)->sum('total_amount');
        $domexDueAmount = (clone $domexBaseQuery)->sum('due_amount');
        $domexDeliveryCharges = DeliverySale::whereIn('sale_id', $domexSaleIds)->sum('delivery_charge');
        $domexDeliveredCount = (clone $domexBaseQuery)->where('delivery_status', 'delivered')->count();
        $domexDeliveredAmount = (clone $domexBaseQuery)->where('delivery_status', 'delivered')->sum('total_amount');
        $domexInTransitCount = (clone $domexBaseQuery)->whereIn('delivery_status', ['in_transit', 'packed', 'pending'])->count();
        $domexInTransitAmount = (clone $domexBaseQuery)->whereIn('delivery_status', ['in_transit', 'packed', 'pending'])->sum('due_amount');
        $domexCollectedAmount = Payment::whereIn('sale_id', $domexSaleIds)->sum('amount');
        $domexTodaySales = (clone $domexBaseQuery)->whereDate('created_at', today())->sum('total_amount');
        $domexTodayCollected = Payment::whereIn('sale_id', $domexSaleIds)->whereDate('payment_date', today())->sum('amount');

        // Post (Postal Service) Metrics
        $postBaseQuery = (clone $baseQuery)->whereHas('deliverySale', function ($dq) {
            $dq->whereRaw('LOWER(delivery_method) = ?', ['post']);
        });
        $postSaleIds = (clone $postBaseQuery)->pluck('id');
        $postOrdersCount = (clone $postBaseQuery)->count();
        $postSalesAmount = (clone $postBaseQuery)->sum('total_amount');
        $postDueAmount = (clone $postBaseQuery)->sum('due_amount');
        $postDeliveryCharges = DeliverySale::whereIn('sale_id', $postSaleIds)->sum('delivery_charge');
        $postDeliveredCount = (clone $postBaseQuery)->where('delivery_status', 'delivered')->count();
        $postDeliveredAmount = (clone $postBaseQuery)->where('delivery_status', 'delivered')->sum('total_amount');
        $postInTransitCount = (clone $postBaseQuery)->whereIn('delivery_status', ['in_transit', 'packed', 'pending'])->count();
        $postInTransitAmount = (clone $postBaseQuery)->whereIn('delivery_status', ['in_transit', 'packed', 'pending'])->sum('due_amount');
        $postCollectedAmount = Payment::whereIn('sale_id', $postSaleIds)->sum('amount');
        $postTodaySales = (clone $postBaseQuery)->whereDate('created_at', today())->sum('total_amount');
        $postTodayCollected = Payment::whereIn('sale_id', $postSaleIds)->whereDate('payment_date', today())->sum('amount');

        // Direct / In-House Metrics
        $directBaseQuery = (clone $baseQuery)->where(function ($q) {
            $q->whereDoesntHave('deliverySale')
                ->orWhereHas('deliverySale', function ($dq) {
                    $dq->whereRaw('LOWER(delivery_method) NOT IN (?, ?)', ['domex', 'post']);
                });
        });
        $directSaleIds = (clone $directBaseQuery)->pluck('id');
        $directOrdersCount = (clone $directBaseQuery)->count();
        $directSalesAmount = (clone $directBaseQuery)->sum('total_amount');
        $directDueAmount = (clone $directBaseQuery)->sum('due_amount');
        $directDeliveryCharges = DeliverySale::whereIn('sale_id', $directSaleIds)->sum('delivery_charge');
        $directDeliveredCount = (clone $directBaseQuery)->where('delivery_status', 'delivered')->count();
        $directDeliveredAmount = (clone $directBaseQuery)->where('delivery_status', 'delivered')->sum('total_amount');
        $directCollectedAmount = Payment::whereIn('sale_id', $directSaleIds)->sum('amount');
        $directTodaySales = (clone $directBaseQuery)->whereDate('created_at', today())->sum('total_amount');
        $directTodayCollected = Payment::whereIn('sale_id', $directSaleIds)->whereDate('payment_date', today())->sum('amount');

        // 2. Filtered Table Query
        $tableQuery = (clone $baseQuery)->with(['customer', 'deliverySale', 'items.product', 'payments', 'user']);

        // Filter by Delivery Status
        if ($this->statusFilter && $this->statusFilter !== 'all') {
            if ($this->statusFilter === 'returned') {
                $tableQuery->whereIn('delivery_status', ['returned', 'cancelled']);
            } elseif ($this->statusFilter === 'pending') {
                $tableQuery->whereIn('delivery_status', ['pending', 'packed']);
            } else {
                $tableQuery->where('delivery_status', $this->statusFilter);
            }
        }

        // Filter by Payment Status
        if ($this->paymentFilter && $this->paymentFilter !== 'all') {
            if ($this->paymentFilter === 'paid') {
                $tableQuery->where('due_amount', '<=', 0);
            } elseif ($this->paymentFilter === 'due') {
                $tableQuery->where('due_amount', '>', 0);
            } elseif ($this->paymentFilter === 'partial') {
                $tableQuery->where('payment_status', 'partial');
            }
        }

        // Filter by Courier Method
        if ($this->courierFilter && $this->courierFilter !== 'all') {
            if (strtolower($this->courierFilter) === 'direct') {
                $tableQuery->where(function ($q) {
                    $q->whereDoesntHave('deliverySale')
                        ->orWhereHas('deliverySale', function ($dq) {
                            $dq->whereRaw('LOWER(delivery_method) NOT IN (?, ?)', ['domex', 'post']);
                        });
                });
            } else {
                $courier = $this->courierFilter;
                $tableQuery->whereHas('deliverySale', function ($dq) use ($courier) {
                    $dq->whereRaw('LOWER(delivery_method) = ?', [strtolower($courier)]);
                });
            }
        }

        // Filter by Date
        $targetField = ($this->dateField === 'delivered_at') ? 'delivered_at' : 'created_at';

        if ($this->dateFilter === 'today') {
            $tableQuery->whereDate($targetField, today());
        } elseif ($this->dateFilter === 'yesterday') {
            $tableQuery->whereDate($targetField, today()->subDay());
        } elseif ($this->dateFilter === 'week') {
            $tableQuery->whereBetween($targetField, [now()->startOfWeek()->startOfDay(), now()->endOfWeek()->endOfDay()]);
        } elseif ($this->dateFilter === 'month') {
            $tableQuery->whereBetween($targetField, [now()->startOfMonth()->startOfDay(), now()->endOfMonth()->endOfDay()]);
        } elseif ($this->dateFilter === 'custom' || (! empty($this->dateFrom) || ! empty($this->dateTo))) {
            if (! empty($this->dateFrom) && ! empty($this->dateTo)) {
                if ($this->dateFrom === $this->dateTo) {
                    $tableQuery->whereDate($targetField, $this->dateFrom);
                } else {
                    $start = Carbon::parse($this->dateFrom)->startOfDay();
                    $end = Carbon::parse($this->dateTo)->endOfDay();
                    if ($start->gt($end)) {
                        [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                    }
                    $tableQuery->whereBetween($targetField, [$start, $end]);
                }
            } elseif (! empty($this->dateFrom)) {
                $tableQuery->where($targetField, '>=', Carbon::parse($this->dateFrom)->startOfDay());
            } elseif (! empty($this->dateTo)) {
                $tableQuery->where($targetField, '<=', Carbon::parse($this->dateTo)->endOfDay());
            }
        }

        // Search
        if ($this->search) {
            $term = '%'.trim($this->search).'%';
            $tableQuery->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', $term)
                    ->orWhere('sale_id', 'like', $term)
                    ->orWhere('walking_customer_name', 'like', $term)
                    ->orWhere('walking_customer_phone', 'like', $term)
                    ->orWhereHas('customer', function ($cq) use ($term) {
                        $cq->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhere('address', 'like', $term);
                    })
                    ->orWhereHas('deliverySale', function ($dq) use ($term) {
                        $dq->where('delivery_barcode', 'like', $term)
                            ->orWhere('tracking_number', 'like', $term)
                            ->orWhere('customer_details', 'like', $term);
                    });
            });
        }

        // Filtered period metrics
        $filteredTotalAmount = (clone $tableQuery)->sum('total_amount');
        $filteredTotalOrders = (clone $tableQuery)->count();
        $filteredTotalDue = (clone $tableQuery)->sum('due_amount');
        $filteredTotalCollected = max(0, $filteredTotalAmount - $filteredTotalDue);

        // Filtered courier breakdown
        $filteredDomexQuery = (clone $tableQuery)->whereHas('deliverySale', function ($dq) {
            $dq->whereRaw('LOWER(delivery_method) = ?', ['domex']);
        });
        $filteredDomexSaleIds = (clone $filteredDomexQuery)->pluck('id');
        $filteredDomexAmount = (clone $filteredDomexQuery)->sum('total_amount');
        $filteredDomexCount = (clone $filteredDomexQuery)->count();
        $filteredDomexDue = (clone $filteredDomexQuery)->sum('due_amount');
        $filteredDomexCollected = Payment::whereIn('sale_id', $filteredDomexSaleIds)->sum('amount');
        $filteredDomexCharges = DeliverySale::whereIn('sale_id', $filteredDomexSaleIds)->sum('delivery_charge');

        $filteredPostQuery = (clone $tableQuery)->whereHas('deliverySale', function ($dq) {
            $dq->whereRaw('LOWER(delivery_method) = ?', ['post']);
        });
        $filteredPostSaleIds = (clone $filteredPostQuery)->pluck('id');
        $filteredPostAmount = (clone $filteredPostQuery)->sum('total_amount');
        $filteredPostCount = (clone $filteredPostQuery)->count();
        $filteredPostDue = (clone $filteredPostQuery)->sum('due_amount');
        $filteredPostCollected = Payment::whereIn('sale_id', $filteredPostSaleIds)->sum('amount');
        $filteredPostCharges = DeliverySale::whereIn('sale_id', $filteredPostSaleIds)->sum('delivery_charge');

        $filteredDirectQuery = (clone $tableQuery)->where(function ($q) {
            $q->whereDoesntHave('deliverySale')
                ->orWhereHas('deliverySale', function ($dq) {
                    $dq->whereRaw('LOWER(delivery_method) NOT IN (?, ?)', ['domex', 'post']);
                });
        });
        $filteredDirectSaleIds = (clone $filteredDirectQuery)->pluck('id');
        $filteredDirectAmount = (clone $filteredDirectQuery)->sum('total_amount');
        $filteredDirectCount = (clone $filteredDirectQuery)->count();
        $filteredDirectDue = (clone $filteredDirectQuery)->sum('due_amount');
        $filteredDirectCollected = Payment::whereIn('sale_id', $filteredDirectSaleIds)->sum('amount');

        $orders = $tableQuery->orderBy('created_at', 'desc')->paginate($this->perPage);

        // Chart Data
        $chartData = $this->getChartData();

        return view('livewire.delivery-man.delivery-man-finance', [
            'orders' => $orders,
            'chartData' => $chartData,
            'filteredSummary' => [
                'totalAmount' => $filteredTotalAmount,
                'ordersCount' => $filteredTotalOrders,
                'totalDue' => $filteredTotalDue,
                'totalCollected' => $filteredTotalCollected,
                'domexAmount' => $filteredDomexAmount,
                'domexCount' => $filteredDomexCount,
                'domexDue' => $filteredDomexDue,
                'domexCollected' => $filteredDomexCollected,
                'domexCharges' => $filteredDomexCharges,
                'postAmount' => $filteredPostAmount,
                'postCount' => $filteredPostCount,
                'postDue' => $filteredPostDue,
                'postCollected' => $filteredPostCollected,
                'postCharges' => $filteredPostCharges,
                'directAmount' => $filteredDirectAmount,
                'directCount' => $filteredDirectCount,
                'directDue' => $filteredDirectDue,
                'directCollected' => $filteredDirectCollected,
            ],
            'stats' => [
                'totalDeliverySales' => $totalDeliverySales,
                'totalDeliveryOrdersCount' => $totalDeliveryOrdersCount,
                'totalDeliveryCharges' => $totalDeliveryCharges,
                'completedDeliveriesCount' => $completedDeliveriesCount,
                'completedDeliveriesAmount' => $completedDeliveriesAmount,
                'pendingDeliveriesCount' => $pendingDeliveriesCount,
                'pendingCodAmount' => $pendingCodAmount,
                'returnedDeliveriesCount' => $returnedDeliveriesCount,
                'returnedDeliveriesAmount' => $returnedDeliveriesAmount,
                'totalCollectedCod' => $totalCollectedCod,
                'todayCollectedCod' => $todayCollectedCod,
                'cashCollected' => $cashCollected,
                'bankCollected' => $bankCollected,
                'totalExpenses' => $totalExpenses,
                'todayExpenses' => $todayExpenses,
                'netCashInHand' => $netCashInHand,
                'todayNetCashInHand' => $todayNetCashInHand,

                // Domex Delivery Finance Stats
                'domexOrdersCount' => $domexOrdersCount,
                'domexSalesAmount' => $domexSalesAmount,
                'domexDueAmount' => $domexDueAmount,
                'domexDeliveryCharges' => $domexDeliveryCharges,
                'domexDeliveredCount' => $domexDeliveredCount,
                'domexDeliveredAmount' => $domexDeliveredAmount,
                'domexInTransitCount' => $domexInTransitCount,
                'domexInTransitAmount' => $domexInTransitAmount,
                'domexCollectedAmount' => $domexCollectedAmount,
                'domexTodaySales' => $domexTodaySales,
                'domexTodayCollected' => $domexTodayCollected,

                // Post Delivery Finance Stats
                'postOrdersCount' => $postOrdersCount,
                'postSalesAmount' => $postSalesAmount,
                'postDueAmount' => $postDueAmount,
                'postDeliveryCharges' => $postDeliveryCharges,
                'postDeliveredCount' => $postDeliveredCount,
                'postDeliveredAmount' => $postDeliveredAmount,
                'postInTransitCount' => $postInTransitCount,
                'postInTransitAmount' => $postInTransitAmount,
                'postCollectedAmount' => $postCollectedAmount,
                'postTodaySales' => $postTodaySales,
                'postTodayCollected' => $postTodayCollected,

                // Direct / In-House Delivery Stats
                'directOrdersCount' => $directOrdersCount,
                'directSalesAmount' => $directSalesAmount,
                'directDueAmount' => $directDueAmount,
                'directDeliveryCharges' => $directDeliveryCharges,
                'directDeliveredCount' => $directDeliveredCount,
                'directDeliveredAmount' => $directDeliveredAmount,
                'directCollectedAmount' => $directCollectedAmount,
                'directTodaySales' => $directTodaySales,
                'directTodayCollected' => $directTodayCollected,
            ],
        ]);
    }
}
