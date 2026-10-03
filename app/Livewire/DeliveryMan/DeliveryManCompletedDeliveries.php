<?php

namespace App\Livewire\DeliveryMan;

use App\Models\DeliverySale;
use App\Models\Payment;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Completed Deliveries - Domex & Post')]
#[Layout('components.layouts.delivery-man')]
class DeliveryManCompletedDeliveries extends Component
{
    use WithPagination;

    // Filters
    public $search = '';

    public $courierTab = 'all'; // all, domex, post, direct

    public $statusFilter = 'all'; // all, delivered, completed, due, settled

    public $paymentFilter = 'all'; // all, paid, due

    public $dateFilter = 'all'; // all, today, yesterday, week, month, custom

    public $dateFrom = '';

    public $dateTo = '';

    public $dateField = 'delivered_at'; // delivered_at, created_at

    public $perPage = 15;

    // Order Details Modal
    public $showDetailsModal = false;

    public $selectedSale = null;

    // Payment Collection / Remittance Modal
    public $showPaymentModal = false;

    public $paymentSaleId = null;

    public $paymentSale = null;

    public $paymentAmount = 0;

    public $paymentMethod = 'cash';

    public $paymentNotes = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        if (request()->has('courier')) {
            $this->courierTab = strtolower(request('courier'));
        }
        if (request()->has('status')) {
            $this->statusFilter = strtolower(request('status'));
        }
        if (request()->has('date')) {
            $this->setQuickDate(request('date'));
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCourierTab()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function updatedPaymentFilter()
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

    public function applyDateRange()
    {
        $this->dateFilter = 'custom';
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function setCourierTab($tab)
    {
        $this->courierTab = strtolower($tab);
        $this->resetPage();
    }

    public function setDateFilter($preset)
    {
        $this->setQuickDate($preset);
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

    public function resetFilters()
    {
        $this->search = '';
        $this->courierTab = 'all';
        $this->paymentFilter = 'all';
        $this->dateFilter = 'all';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->dateField = 'delivered_at';
        $this->resetPage();
    }

    // --- Order Details Modal ---
    public function viewDetails($saleId)
    {
        $this->selectedSale = Sale::with(['customer', 'deliverySale', 'items.product', 'payments', 'user', 'approvedBy'])
            ->find($saleId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedSale = null;
    }

    // --- Payment Collection / Remittance Modal ---
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

            $sale->delivery_status = 'completed';
            if (! $sale->delivered_at) {
                $sale->delivered_at = now();
            }
            $sale->delivered_by = Auth::id();
            if ($sale->deliverySale) {
                $sale->deliverySale->update(['status' => 'Completed']);
            }

            $sale->save();

            DB::commit();

            $this->closePaymentModal();
            $this->dispatch('show-toast', type: 'success', message: 'Courier remittance payment recorded & order marked as Completed!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error recording payment: '.$e->getMessage());
        }
    }

    /**
     * Mark an order as completed manually (with optional auto-settlement of courier remittance).
     */
    public function markAsCompleted($saleId, $autoSettle = false)
    {
        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($saleId);
            $sale->delivery_status = 'completed';
            if (! $sale->delivered_at) {
                $sale->delivered_at = now();
            }
            $sale->delivered_by = Auth::id();

            if ($autoSettle && (float) $sale->due_amount > 0) {
                $courier = $sale->deliverySale->delivery_method ?? 'Courier';
                $isCourier = in_array(strtolower($courier), ['domex', 'post']);

                Payment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'amount' => (float) $sale->due_amount,
                    'payment_method' => $isCourier ? 'bank_transfer' : 'cash',
                    'is_completed' => true,
                    'payment_date' => now(),
                    'status' => 'paid',
                    'collected_by' => Auth::id(),
                    'notes' => "Courier COD remittance payout received from {$courier}",
                ]);

                $sale->due_amount = 0;
                $sale->payment_status = 'paid';
            }

            $sale->save();

            if ($sale->deliverySale) {
                $sale->deliverySale->update(['status' => 'Completed']);
            }

            try {
                \App\Services\SmsService::notifyOrderDelivered($sale);
            } catch (\Throwable $e) {
            }

            DB::commit();

            $this->dispatch('show-toast', type: 'success', message: 'Order #'.$sale->invoice_number.' marked as Completed!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error completing order: '.$e->getMessage());
        }
    }

    /**
     * Base query for delivered and completed orders.
     */
    protected function getBaseCompletedQuery()
    {
        $currentUser = Auth::user();
        $isDeliveryMan = $currentUser && ($currentUser->staff_type === 'delivery_man' || $currentUser->role === 'delivery_man');

        return Sale::query()
            ->whereIn('delivery_status', ['delivered', 'completed'])
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

    public function render()
    {
        $baseQuery = $this->getBaseCompletedQuery();
        $allSaleIds = (clone $baseQuery)->pluck('id');

        // Overall stats
        $totalCompletedCount = (clone $baseQuery)->count();
        $totalCompletedAmount = (clone $baseQuery)->sum('total_amount');
        $totalDeliveryCharges = DeliverySale::whereIn('sale_id', $allSaleIds)->sum('delivery_charge');

        $todayCompletedCount = (clone $baseQuery)->whereDate('delivered_at', today())->count();
        $todayCompletedAmount = (clone $baseQuery)->whereDate('delivered_at', today())->sum('total_amount');

        // Domex Completed Stats
        $domexBaseQuery = (clone $baseQuery)->whereHas('deliverySale', function ($dq) {
            $dq->whereRaw('LOWER(delivery_method) = ?', ['domex']);
        });
        $domexSaleIds = (clone $domexBaseQuery)->pluck('id');
        $domexCompletedCount = (clone $domexBaseQuery)->count();
        $domexCompletedAmount = (clone $domexBaseQuery)->sum('total_amount');
        $domexTodayCount = (clone $domexBaseQuery)->whereDate('delivered_at', today())->count();
        $domexTodayAmount = (clone $domexBaseQuery)->whereDate('delivered_at', today())->sum('total_amount');
        $domexDueAmount = (clone $domexBaseQuery)->sum('due_amount');
        $domexCollectedAmount = Payment::whereIn('sale_id', $domexSaleIds)->sum('amount');
        $domexDeliveryCharges = DeliverySale::whereIn('sale_id', $domexSaleIds)->sum('delivery_charge');

        // Post Completed Stats
        $postBaseQuery = (clone $baseQuery)->whereHas('deliverySale', function ($dq) {
            $dq->whereRaw('LOWER(delivery_method) = ?', ['post']);
        });
        $postSaleIds = (clone $postBaseQuery)->pluck('id');
        $postCompletedCount = (clone $postBaseQuery)->count();
        $postCompletedAmount = (clone $postBaseQuery)->sum('total_amount');
        $postTodayCount = (clone $postBaseQuery)->whereDate('delivered_at', today())->count();
        $postTodayAmount = (clone $postBaseQuery)->whereDate('delivered_at', today())->sum('total_amount');
        $postDueAmount = (clone $postBaseQuery)->sum('due_amount');
        $postCollectedAmount = Payment::whereIn('sale_id', $postSaleIds)->sum('amount');
        $postDeliveryCharges = DeliverySale::whereIn('sale_id', $postSaleIds)->sum('delivery_charge');

        // Direct / In-House Completed Stats
        $directBaseQuery = (clone $baseQuery)->where(function ($q) {
            $q->whereDoesntHave('deliverySale')
                ->orWhereHas('deliverySale', function ($dq) {
                    $dq->whereRaw('LOWER(delivery_method) NOT IN (?, ?)', ['domex', 'post']);
                });
        });
        $directSaleIds = (clone $directBaseQuery)->pluck('id');
        $directCompletedCount = (clone $directBaseQuery)->count();
        $directCompletedAmount = (clone $directBaseQuery)->sum('total_amount');
        $directTodayCount = (clone $directBaseQuery)->whereDate('delivered_at', today())->count();
        $directTodayAmount = (clone $directBaseQuery)->whereDate('delivered_at', today())->sum('total_amount');
        $directDueAmount = (clone $directBaseQuery)->sum('due_amount');
        $directCollectedAmount = Payment::whereIn('sale_id', $directSaleIds)->sum('amount');

        // Overall COD payments
        $totalCollected = Payment::whereIn('sale_id', $allSaleIds)->sum('amount');
        $totalDue = (clone $baseQuery)->sum('due_amount');

        // Filtered Query
        $tableQuery = (clone $baseQuery)->with(['customer', 'deliverySale', 'items.product', 'payments', 'user']);

        // Courier Tab Filter (All, Domex, Post, Direct)
        if ($this->courierTab && $this->courierTab !== 'all') {
            if ($this->courierTab === 'domex') {
                $tableQuery->whereHas('deliverySale', function ($dq) {
                    $dq->whereRaw('LOWER(delivery_method) = ?', ['domex']);
                });
            } elseif ($this->courierTab === 'post') {
                $tableQuery->whereHas('deliverySale', function ($dq) {
                    $dq->whereRaw('LOWER(delivery_method) = ?', ['post']);
                });
            } elseif ($this->courierTab === 'direct') {
                $tableQuery->where(function ($q) {
                    $q->whereDoesntHave('deliverySale')
                        ->orWhereHas('deliverySale', function ($dq) {
                            $dq->whereRaw('LOWER(delivery_method) NOT IN (?, ?)', ['domex', 'post']);
                        });
                });
            }
        }

        // Completion & Payment Status Filter
        if ($this->statusFilter && $this->statusFilter !== 'all') {
            if ($this->statusFilter === 'completed') {
                $tableQuery->where('delivery_status', 'completed');
            } elseif ($this->statusFilter === 'delivered') {
                $tableQuery->where('delivery_status', 'delivered');
            } elseif ($this->statusFilter === 'due') {
                $tableQuery->where('due_amount', '>', 0);
            } elseif ($this->statusFilter === 'settled') {
                $tableQuery->where('due_amount', '<=', 0);
            }
        }

        // Payment status filter
        if ($this->paymentFilter && $this->paymentFilter !== 'all') {
            if ($this->paymentFilter === 'paid') {
                $tableQuery->where('due_amount', '<=', 0);
            } elseif ($this->paymentFilter === 'due') {
                $tableQuery->where('due_amount', '>', 0);
            }
        }

        // Date Filter
        $targetField = ($this->dateField === 'created_at') ? 'created_at' : 'delivered_at';

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

        // Filtered summary calculations
        $filteredSaleIds = (clone $tableQuery)->pluck('id');
        $filteredTotalCount = (clone $tableQuery)->count();
        $filteredTotalAmount = (float) (clone $tableQuery)->sum('total_amount');
        $filteredTotalDue = (float) (clone $tableQuery)->sum('due_amount');
        $filteredTotalCollected = max(0, $filteredTotalAmount - $filteredTotalDue);

        $filteredDeliveryCharge = (float) DeliverySale::whereIn('sale_id', $filteredSaleIds)->sum('delivery_charge');
        $filteredReceivableAmount = max(0, $filteredTotalAmount - $filteredDeliveryCharge);

        // Filtered Domex
        $fDomexQuery = (clone $tableQuery)->whereHas('deliverySale', fn ($dq) => $dq->whereRaw('LOWER(delivery_method) = ?', ['domex']));
        $filteredDomexCount = (clone $fDomexQuery)->count();
        $filteredDomexAmount = (clone $fDomexQuery)->sum('total_amount');

        // Filtered Post
        $fPostQuery = (clone $tableQuery)->whereHas('deliverySale', fn ($dq) => $dq->whereRaw('LOWER(delivery_method) = ?', ['post']));
        $filteredPostCount = (clone $fPostQuery)->count();
        $filteredPostAmount = (clone $fPostQuery)->sum('total_amount');

        // Filtered Direct
        $fDirectQuery = (clone $tableQuery)->where(function ($q) {
            $q->whereDoesntHave('deliverySale')
                ->orWhereHas('deliverySale', fn ($dq) => $dq->whereRaw('LOWER(delivery_method) NOT IN (?, ?)', ['domex', 'post']));
        });
        $filteredDirectCount = (clone $fDirectQuery)->count();
        $filteredDirectAmount = (clone $fDirectQuery)->sum('total_amount');

        $sales = $tableQuery->orderBy('delivered_at', 'desc')->paginate($this->perPage);

        return view('livewire.delivery-man.delivery-man-completed-deliveries', [
            'sales' => $sales,
            'stats' => [
                'totalCompletedCount' => $totalCompletedCount,
                'totalCompletedAmount' => $totalCompletedAmount,
                'totalDeliveryCharges' => $totalDeliveryCharges,
                'totalReceivableAmount' => max(0, $totalCompletedAmount - $totalDeliveryCharges),
                'todayCompletedCount' => $todayCompletedCount,
                'todayCompletedAmount' => $todayCompletedAmount,
                'domexCompletedCount' => $domexCompletedCount,
                'domexCompletedAmount' => $domexCompletedAmount,
                'domexTodayCount' => $domexTodayCount,
                'domexTodayAmount' => $domexTodayAmount,
                'domexDueAmount' => $domexDueAmount,
                'domexCollectedAmount' => $domexCollectedAmount,
                'domexDeliveryCharges' => $domexDeliveryCharges,
                'postCompletedCount' => $postCompletedCount,
                'postCompletedAmount' => $postCompletedAmount,
                'postTodayCount' => $postTodayCount,
                'postTodayAmount' => $postTodayAmount,
                'postDueAmount' => $postDueAmount,
                'postCollectedAmount' => $postCollectedAmount,
                'postDeliveryCharges' => $postDeliveryCharges,
                'directCompletedCount' => $directCompletedCount,
                'directCompletedAmount' => $directCompletedAmount,
                'directTodayCount' => $directTodayCount,
                'directTodayAmount' => $directTodayAmount,
                'directDueAmount' => $directDueAmount,
                'directCollectedAmount' => $directCollectedAmount,
                'totalCollected' => $totalCollected,
                'totalDue' => $totalDue,
            ],
            'filteredSummary' => [
                'count' => $filteredTotalCount,
                'amount' => $filteredTotalAmount,
                'deliveryCharge' => $filteredDeliveryCharge,
                'receivableAmount' => $filteredReceivableAmount,
                'due' => $filteredTotalDue,
                'collected' => $filteredTotalCollected,
                'domexCount' => $filteredDomexCount,
                'domexAmount' => $filteredDomexAmount,
                'postCount' => $filteredPostCount,
                'postAmount' => $filteredPostAmount,
                'directCount' => $filteredDirectCount,
                'directAmount' => $filteredDirectAmount,
            ],
        ]);
    }
}
