<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductSupplier;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentAllocation;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title("List Supplier Receipt")]
class ListSupplierReceipt extends Component
{
    use WithDynamicLayout;
    use WithPagination;

    public $showDetailModal = false;
    public $selectedGroupSupplier = '';
    public $selectedGroupDate = '';
    public $selectedGroupTotalCash = 0;
    public $selectedGroupItems = []; // Added this to store products
    public $selectedGroupOrderSummary = []; // Renamed from selectedGroupOrders for better clarity if needed, or just update the map

    // Confirm Pay modal
    public $showConfirmPayModal = false;
    public $confirmPaySupplierId = null;
    public $confirmPayDate = '';
    public $confirmPayAmount = 0;
    public $confirmPaySupplierName = '';
    public $confirmPayOrderCount = 0;

    // Filters
    public $filterSupplier = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $searchOrderNumber = '';

    // Order search results (kept from original)
    public $searchedOrder = null;
    public $orderPayments = [];

    public function updatingFilterSupplier()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    /**
     * Get supplier payments grouped by received_date and supplier_id.
     * For each group, calculate total cash paid from purchase_payments.
     */
    public function getGroupedPaymentsProperty()
    {
        $query = PurchaseOrder::select(
                'purchase_orders.supplier_id',
                'purchase_orders.received_date',
                DB::raw('COUNT(purchase_orders.id) as order_count'),
                DB::raw('SUM(purchase_orders.total_amount) as total_purchase_amount'),
                DB::raw('GROUP_CONCAT(purchase_orders.id) as order_ids')
            )
            ->join('product_suppliers', 'product_suppliers.id', '=', 'purchase_orders.supplier_id')
            ->whereNotNull('purchase_orders.received_date')
            ->groupBy('purchase_orders.supplier_id', 'purchase_orders.received_date');

        // Apply supplier filter
        if ($this->filterSupplier) {
            $query->where('product_suppliers.name', 'LIKE', '%' . $this->filterSupplier . '%');
        }

        // Apply date filters
        if ($this->filterDateFrom) {
            $query->where('purchase_orders.received_date', '>=', $this->filterDateFrom);
        }
        if ($this->filterDateTo) {
            $query->where('purchase_orders.received_date', '<=', $this->filterDateTo);
        }

        $query->orderByDesc('purchase_orders.received_date');

        $groups = $query->paginate(20);

        // For each group, calculate the total cash paid
        $groups->getCollection()->transform(function ($group) {
            $orderIds = explode(',', $group->order_ids);
            $supplier = ProductSupplier::find($group->supplier_id);
            $group->supplier_name = $supplier ? $supplier->name : 'Unknown';

            // Get total cash payments for these orders via purchase_payment_allocations
            $totalCashPaid = PurchasePayment::where('supplier_id', $group->supplier_id)
                ->where('payment_method', 'cash')
                ->whereHas('allocations', function ($q) use ($orderIds) {
                    $q->whereIn('purchase_order_id', $orderIds);
                })
                ->sum('amount');

            // Also get cash payments directly linked to orders (via purchase_order_id column)
            $directCashPaid = PurchasePayment::where('supplier_id', $group->supplier_id)
                ->where('payment_method', 'cash')
                ->whereIn('purchase_order_id', $orderIds)
                ->doesntHave('allocations')
                ->sum('amount');

            $group->total_cash_paid = $totalCashPaid + $directCashPaid;

            // Get total payments (all methods) for these orders
            $totalAllPaid = PurchasePayment::where('supplier_id', $group->supplier_id)
                ->whereHas('allocations', function ($q) use ($orderIds) {
                    $q->whereIn('purchase_order_id', $orderIds);
                })
                ->sum('amount');

            $directAllPaid = PurchasePayment::where('supplier_id', $group->supplier_id)
                ->whereIn('purchase_order_id', $orderIds)
                ->doesntHave('allocations')
                ->sum('amount');

            $group->total_all_paid = $totalAllPaid + $directAllPaid;

            return $group;
        });

        return $groups;
    }

    /**
     * Show detail modal for a specific group (supplier + date)
     */
    public function showGroupDetail($supplierId, $receivedDate)
    {
        $supplier = ProductSupplier::find($supplierId);
        $this->selectedGroupSupplier = $supplier ? $supplier->name : 'Unknown';
        $this->selectedGroupDate = $receivedDate;

        // Get all orders for this supplier on this date with their items and products
        $orders = PurchaseOrder::with(['items.product'])
            ->where('supplier_id', $supplierId)
            ->where('received_date', $receivedDate)
            ->get();

        $orderIds = $orders->pluck('id')->toArray();

        // Get cash payments for these orders
        $cashPayments = PurchasePayment::where('supplier_id', $supplierId)
            ->where('payment_method', 'cash')
            ->where(function ($q) use ($orderIds) {
                $q->whereHas('allocations', function ($sub) use ($orderIds) {
                    $sub->whereIn('purchase_order_id', $orderIds);
                })->orWhere(function ($sub) use ($orderIds) {
                    $sub->whereIn('purchase_order_id', $orderIds)->doesntHave('allocations');
                });
            })
            ->sum('amount');

        $this->selectedGroupTotalCash = $cashPayments;

        // Flatten items from all orders in this group
        $this->selectedGroupItems = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $this->selectedGroupItems[] = [
                    'order_code' => $order->order_code,
                    'product_name' => $item->product ? $item->product->name : 'N/A',
                    'product_code' => $item->product ? $item->product->code : 'N/A',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->quantity * $item->unit_price,
                ];
            }
        }

        // Keep order summary for the header totals
        $this->selectedGroupOrderSummary = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'total_amount' => $order->total_amount,
                'due_amount' => $order->due_amount,
            ];
        })->toArray();

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedGroupItems = [];
        $this->selectedGroupOrderSummary = [];
        $this->selectedGroupSupplier = '';
        $this->selectedGroupDate = '';
        $this->selectedGroupTotalCash = 0;
    }

    public function clearFilters()
    {
        $this->filterSupplier = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    /**
     * Open the confirm pay modal for a grouped row
     */
    public function openConfirmPay($supplierId, $receivedDate)
    {
        $supplier = ProductSupplier::find($supplierId);
        $this->confirmPaySupplierId = $supplierId;
        $this->confirmPayDate = $receivedDate;
        $this->confirmPaySupplierName = $supplier ? $supplier->name : 'Unknown';

        // Get all orders for this supplier on this date
        $orders = PurchaseOrder::where('supplier_id', $supplierId)
            ->where('received_date', $receivedDate)
            ->get();

        $this->confirmPayOrderCount = $orders->count();
        $this->confirmPayAmount = $orders->sum('total_amount');
        $this->showConfirmPayModal = true;
    }

    public function closeConfirmPay()
    {
        $this->showConfirmPayModal = false;
        $this->confirmPaySupplierId = null;
        $this->confirmPayDate = '';
        $this->confirmPayAmount = 0;
        $this->confirmPaySupplierName = '';
        $this->confirmPayOrderCount = 0;
    }

    /**
     * Confirm and create cash payment record(s) for the group
     */
    public function confirmMarkAsPaid()
    {
        $supplierId = $this->confirmPaySupplierId;
        $receivedDate = $this->confirmPayDate;

        // Get all orders for this supplier on this date
        $orders = PurchaseOrder::where('supplier_id', $supplierId)
            ->where('received_date', $receivedDate)
            ->get();

        if ($orders->isEmpty()) {
            $this->closeConfirmPay();
            return;
        }

        $totalAmount = $orders->sum('total_amount');

        DB::beginTransaction();

        try {
            if ($orders->count() === 1) {
                // Single order — create one payment linked directly
                $order = $orders->first();
                $payment = PurchasePayment::create([
                    'supplier_id' => $supplierId,
                    'purchase_order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'payment_method' => 'cash',
                    'payment_date' => $receivedDate,
                    'status' => 'paid',
                    'is_completed' => true,
                ]);

                // Also create allocation
                PurchasePaymentAllocation::create([
                    'purchase_payment_id' => $payment->id,
                    'purchase_order_id' => $order->id,
                    'allocated_amount' => $order->total_amount,
                ]);

                // Update due amount on the order
                $order->update([
                    'due_amount' => max(0, $order->due_amount - $order->total_amount),
                ]);
            } else {
                // Multiple orders — create one payment, allocate to each order
                $payment = PurchasePayment::create([
                    'supplier_id' => $supplierId,
                    'purchase_order_id' => null,
                    'amount' => $totalAmount,
                    'payment_method' => 'cash',
                    'payment_date' => $receivedDate,
                    'status' => 'paid',
                    'is_completed' => true,
                ]);

                foreach ($orders as $order) {
                    PurchasePaymentAllocation::create([
                        'purchase_payment_id' => $payment->id,
                        'purchase_order_id' => $order->id,
                        'allocated_amount' => $order->total_amount,
                    ]);

                    // Update due amount on each order
                    $order->update([
                        'due_amount' => max(0, $order->due_amount - $order->total_amount),
                    ]);
                }
            }

            DB::commit();
            $this->closeConfirmPay();
            session()->flash('success', 'Payment marked as paid successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to mark payment: ' . $e->getMessage());
        }
    }

    // Keep original order search functionality
    public function updatedSearchOrderNumber()
    {
        if (trim($this->searchOrderNumber) === '') {
            $this->searchedOrder = null;
            $this->orderPayments = [];
            return;
        }

        $this->searchedOrder = PurchaseOrder::with('supplier')
            ->where('order_code', 'LIKE', '%' . $this->searchOrderNumber . '%')
            ->first();

        if ($this->searchedOrder) {
            $this->orderPayments = PurchasePayment::with(['allocations.order'])
                ->whereHas('allocations', function ($q) {
                    $q->where('purchase_order_id', $this->searchedOrder->id);
                })
                ->orderByDesc('payment_date')
                ->get();
        } else {
            $this->orderPayments = [];
        }
    }

    public function clearSearch()
    {
        $this->searchOrderNumber = '';
        $this->searchedOrder = null;
        $this->orderPayments = [];
    }

    public function render()
    {
        return view('livewire.admin.list-supplier-receipt', [
            'groupedPayments' => $this->groupedPayments,
        ])->layout($this->layout);
    }
}
