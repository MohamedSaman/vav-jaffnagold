<?php

namespace App\Livewire\Ecommerce\Admin;

use App\Models\EcommerceOrder;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('E-Commerce Order Management')]
#[Layout('components.layouts.admin')]
class OrderManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $selectedOrderId = null;
    public $selectedOrder = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function selectOrder($orderId)
    {
        $this->selectedOrderId = $orderId;
        $this->selectedOrder = EcommerceOrder::with('items.product')->find($orderId);
    }

    public function closeDetails()
    {
        $this->selectedOrderId = null;
        $this->selectedOrder = null;
    }

    public function updateOrderStatus($orderId, $status)
    {
        $order = EcommerceOrder::find($orderId);
        if ($order) {
            $order->order_status = $status;
            $order->save();

            $sale = Sale::with('items')->where('ecommerce_order_id', $orderId)->first();
            if ($sale) {
                if (in_array($status, ['confirmed', 'confirm'])) {
                    $sale->confirmAndDeductStock();
                    try {
                        \App\Services\SmsService::notifyOrderConfirmed($sale, $order);
                    } catch (\Throwable $e) {}
                } elseif (in_array($status, ['cancelled', 'returned'])) {
                    $sale->restoreStock();
                    $sale->update(['status' => 'rejected', 'delivery_status' => $status]);
                    try {
                        \App\Services\SmsService::notifyOrderCancelled($sale);
                    } catch (\Throwable $e) {}
                } elseif ($status === 'in_transit') {
                    try {
                        \App\Services\SmsService::notifyOrderInTransit($sale);
                    } catch (\Throwable $e) {}
                } elseif ($status === 'delivered') {
                    try {
                        \App\Services\SmsService::notifyOrderDelivered($sale);
                    } catch (\Throwable $e) {}
                }
            }

            $this->dispatch('showToast', message: 'Order status updated to: ' . $status . ' (Customer notified via SMS)');
            if ($this->selectedOrderId == $orderId) {
                $this->selectOrder($orderId);
            }
        }
    }

    public function updatePaymentStatus($orderId, $status)
    {
        $order = EcommerceOrder::find($orderId);
        if ($order) {
            $order->payment_status = $status;
            $order->save();
            $this->dispatch('showToast', message: 'Payment status updated to: ' . $status);
            if ($this->selectedOrderId == $orderId) {
                $this->selectOrder($orderId);
            }
        }
    }

    public function render()
    {
        $orders = EcommerceOrder::query()
            ->when($this->statusFilter, function ($query) {
                $query->where('order_status', $this->statusFilter);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('order_number', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_email', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_phone', 'like', '%' . $this->search . '%')
                      ->orWhere('city', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.ecommerce.admin.order-management', [
            'orders' => $orders,
        ]);
    }
}
