<?php

namespace App\Livewire\ShopStaff;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Sale;

#[Layout('components.layouts.shop-staff')]
#[Title('Delivery Packing')]
class DeliveryPacking extends Component
{
    public $sales;
    public $search = '';
    public $todayOnly = false;
    public $filterStatus = 'pending';

    // overview counts
    public $totalCount = 0;
    public $pendingCount = 0;
    public $packedCount = 0;
    public $deliveredCount = 0;
    public $completedCount = 0;

    // Modal state (only for pending orders packing view)
    public $showModal = false;
    public $modalSale = null;

    // Confirmation modal state
    public $showConfirmModal = false;
    public $confirmAction = '';
    public $confirmTitle = '';
    public $confirmMessage = '';
    public $confirmSaleId = null;

    public function mount()
    {
        $this->loadOverview();
        $this->loadSales();
    }

    public function updatedSearch()
    {
        $this->loadSales();
    }

    public function updatedTodayOnly()
    {
        $this->loadSales();
    }

    public function loadSales()
    {
        $query = Sale::query()
            ->where(function ($q) {
                $q->whereHas('deliverySale')
                  ->orWhereIn('order_source', ['whatsapp', 'ecommerce', 'delivery_man']);
            });

        // Filter by delivery status
        if ($this->filterStatus && $this->filterStatus !== 'all') {
            $query->where('delivery_status', $this->filterStatus);
        } else {
            if (!$this->filterStatus) {
                $query->where('delivery_status', 'pending');
            }
        }

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sale_id', 'like', $searchTerm)
                    ->orWhere('invoice_number', 'like', $searchTerm)
                    ->orWhere('walking_customer_name', 'like', $searchTerm)
                    ->orWhereHas('customer', function ($cq) use ($searchTerm) {
                        $cq->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('deliverySale', function ($dq) use ($searchTerm) {
                        $dq->where('delivery_barcode', 'like', $searchTerm);
                    });
            });
        }

        if ($this->todayOnly) {
            $query->whereDate('created_at', now()->toDateString());
        }

        $this->sales = $query->with(['items.product', 'customer', 'deliverySale'])->orderBy('created_at', 'desc')->get();
    }

    protected function loadOverview()
    {
        $base = Sale::where(function ($q) {
            $q->whereHas('deliverySale')
              ->orWhereIn('order_source', ['whatsapp', 'ecommerce', 'delivery_man']);
        });

        $this->totalCount = (clone $base)->whereIn('delivery_status', ['pending', 'packed', 'delivered', 'completed'])->count();
        $this->pendingCount = (clone $base)->where('delivery_status', 'pending')->count();
        $this->packedCount = (clone $base)->where('delivery_status', 'packed')->count();
        $this->deliveredCount = (clone $base)->where('delivery_status', 'delivered')->count();
        $this->completedCount = (clone $base)->where('delivery_status', 'completed')->count();
    }

    public function setFilter($status)
    {
        $this->filterStatus = $status;
        $this->loadSales();
    }

    /**
     * Show packing modal (only for pending orders)
     */
    public function showPackingModal($saleId)
    {
        $this->modalSale = Sale::with(['items.product', 'customer', 'deliverySale'])->find($saleId);
        $this->showModal = true;
    }

    /**
     * Get display customer name for a sale.
     */
    public function getCustomerDisplayName($sale)
    {
        if ($sale->customer && $sale->customer->name && $sale->customer->name !== 'Walking Customer') {
            return $sale->customer->name;
        }
        if (!empty($sale->walking_customer_name)) {
            return $sale->walking_customer_name;
        }
        if ($sale->deliverySale && !empty($sale->deliverySale->customer_details)) {
            $details = $sale->deliverySale->customer_details;
            if (preg_match('/Customer:\s*([^|]+)/i', $details, $matches)) {
                return trim($matches[1]);
            }
            return $details;
        }
        if ($sale->customer && $sale->customer->name === 'Walking Customer') {
            return 'Walk-in Customer';
        }
        return 'Walk-in Customer';
    }

    /**
     * Get customer phone for a sale
     */
    public function getCustomerPhone($sale)
    {
        if ($sale->customer && $sale->customer->phone) {
            return $sale->customer->phone;
        }
        if (!empty($sale->walking_customer_phone)) {
            return $sale->walking_customer_phone;
        }
        if ($sale->deliverySale && !empty($sale->deliverySale->customer_details)) {
            if (preg_match('/Phone:\s*([^\|\n]+)/i', $sale->deliverySale->customer_details, $matches)) {
                return trim($matches[1]);
            }
        }
        return null;
    }

    /**
     * Get delivery barcode for display
     */
    public function getDeliveryBarcode($sale)
    {
        return $sale->deliverySale->delivery_barcode ?? $sale->sale_id ?? 'N/A';
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->modalSale = null;
    }

    /**
     * Show confirmation dialog before status change.
     * Works from both modal (for pending) and directly from card (for packed/delivered).
     */
    public function confirmStatusChange($action, $saleId = null)
    {
        $this->confirmAction = $action;
        $this->confirmSaleId = $saleId;

        switch ($action) {
            case 'packed':
                $this->confirmTitle = 'Mark as Packed?';
                $this->confirmMessage = 'Are you sure this order is fully packed and ready for delivery?';
                break;
            case 'delivered':
                $this->confirmTitle = 'Mark as Delivered?';
                $this->confirmMessage = 'Are you sure this order has been delivered to the customer?';
                break;
            case 'completed':
                $this->confirmTitle = 'Complete this Order?';
                $this->confirmMessage = 'Are you sure you want to mark this order as completed? This is the final step.';
                break;
        }

        $this->showConfirmModal = true;
    }

    public function cancelConfirm()
    {
        $this->showConfirmModal = false;
        $this->confirmAction = '';
        $this->confirmSaleId = null;
    }

    /**
     * Execute the confirmed status change
     */
    public function executeStatusChange()
    {
        $action = $this->confirmAction;

        // Get the sale - either from modal (pending) or directly by ID (packed/delivered)
        $sale = null;
        if ($this->confirmSaleId) {
            $sale = Sale::find($this->confirmSaleId);
        } elseif ($this->modalSale) {
            $sale = $this->modalSale;
        }

        if (!$sale || !$action) {
            $this->cancelConfirm();
            return;
        }

        $this->showConfirmModal = false;
        $this->confirmAction = '';
        $this->confirmSaleId = null;

        switch ($action) {
            case 'packed':
                $sale->delivery_status = 'packed';
                $sale->save();
                session()->flash('success', 'Order marked as packed successfully!');
                break;

            case 'delivered':
                $sale->delivery_status = 'delivered';
                $sale->delivered_at = now();
                $sale->delivered_by = auth()->id();
                $sale->save();
                session()->flash('success', 'Order marked as delivered successfully!');
                break;

            case 'completed':
                $sale->delivery_status = 'completed';
                $sale->save();
                session()->flash('success', 'Order completed successfully!');
                break;
        }

        $this->closeModal();
        $this->loadOverview();
        $this->loadSales();
    }

    public function render()
    {
        return view('livewire.shop-staff.delivery-packing', [
            'sales' => $this->sales,
            'showModal' => $this->showModal,
            'modalSale' => $this->modalSale
        ]);
    }
}
