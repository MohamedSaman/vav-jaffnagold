<?php

namespace App\Livewire\DeliveryMan;

use App\Models\EcommerceOrder;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Delivery Order Processing')]
#[Layout('components.layouts.delivery-man')]
class DeliveryOrderDetail extends Component
{
    public $saleId;

    public $sale;

    // Modals & form inputs
    public $showCourierModal = false;

    public $courierTrackingNumber = '';

    public $courierMethod = 'Domex';

    public $showPaymentModal = false;

    public $paymentAmount = 0;

    public $paymentMethod = 'cash';

    public $paymentNotes = '';

    public $showCancelModal = false;

    public $cancelReason = '';

    public $isReturnAction = false;

    public $showSmsModal = false;

    public $smsCustomMessage = '';

    public function mount($id)
    {
        $this->saleId = $id;
        $this->loadSale();

        if (! $this->sale) {
            abort(404, 'Delivery Order not found.');
        }

        $this->courierTrackingNumber = $this->sale->deliverySale->tracking_number ?? '';
        $this->courierMethod = $this->sale->deliverySale->delivery_method ?? 'Domex';
        $this->paymentAmount = $this->sale->due_amount;
    }

    public function loadSale()
    {
        $this->sale = Sale::with([
            'customer',
            'deliverySale',
            'domexOrderDetail',
            'postOrderDetail',
            'items.product',
            'payments',
            'user',
        ])->find($this->saleId);
    }

    /**
     * 1. Confirm order and deduct stock from inventory
     */
    public function confirmOrder()
    {
        if (! $this->sale) {
            return;
        }

        if ($this->sale->status === 'confirm') {
            $this->dispatch('show-toast', type: 'info', message: 'Order is already confirmed.');

            return;
        }

        try {
            DB::beginTransaction();

            $deducted = $this->sale->confirmAndDeductStock();

            DB::commit();

            $this->loadSale();

            if ($deducted) {
                // Send automated SMS to customer
                try {
                    \App\Services\SmsService::notifyOrderConfirmed($this->sale);
                } catch (\Throwable $e) {
                }

                $this->dispatch('show-toast', type: 'success', message: 'Order confirmed! Stock deducted & customer notified via SMS.');
            } else {
                $this->dispatch('show-toast', type: 'info', message: 'Order was already confirmed.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Failed to confirm order: '.$e->getMessage());
        }
    }

    /**
     * 2. Mark order as packed
     */
    public function markPacked()
    {
        if (! $this->sale) {
            return;
        }

        // Auto-confirm if not confirmed yet
        if ($this->sale->status !== 'confirm') {
            $this->sale->confirmAndDeductStock();
            try {
                \App\Services\SmsService::notifyOrderConfirmed($this->sale);
            } catch (\Throwable $e) {
            }
        }

        $this->sale->delivery_status = 'packed';
        $this->sale->save();

        if ($this->sale->deliverySale) {
            $this->sale->deliverySale->update(['status' => 'Packed']);
        }

        $this->loadSale();
        $this->dispatch('show-toast', type: 'success', message: 'Order packed! Ready for courier handover.');
    }

    /**
     * 3. Open Courier Dispatch modal
     */
    public function openCourierModal()
    {
        $this->courierTrackingNumber = $this->sale->deliverySale->tracking_number ?? '';
        $this->courierMethod = $this->sale->deliverySale->delivery_method ?? 'Domex';
        $this->showCourierModal = true;
    }

    public function closeCourierModal()
    {
        $this->showCourierModal = false;
    }

    /**
     * Mark In Transit with tracking number
     */
    public function markInTransit()
    {
        if (! $this->sale) {
            return;
        }

        if ($this->sale->status !== 'confirm') {
            $this->sale->confirmAndDeductStock();
        }

        $this->sale->delivery_status = 'in_transit';
        $this->sale->delivered_by = Auth::id();
        $this->sale->save();

        if ($this->sale->deliverySale) {
            $this->sale->deliverySale->update([
                'status' => 'In Transit',
                'delivery_method' => $this->courierMethod ?: 'Domex',
                'tracking_number' => trim($this->courierTrackingNumber) ?: $this->sale->deliverySale->tracking_number,
            ]);
        }

        $this->showCourierModal = false;
        $this->loadSale();

        // Send In-Transit SMS notification to customer with tracking details
        try {
            \App\Services\SmsService::notifyOrderInTransit(
                $this->sale,
                $this->courierMethod,
                trim($this->courierTrackingNumber)
            );
        } catch (\Throwable $e) {
        }

        $this->dispatch('show-toast', type: 'success', message: 'Order dispatched with courier & tracking SMS sent to customer.');
    }

    /**
     * Fetch live status from Domex for this order
     */
    public function syncDomexStatus()
    {
        if (! $this->sale || ! $this->sale->deliverySale || empty($this->sale->deliverySale->tracking_number)) {
            $this->dispatch('show-toast', type: 'warning', message: 'No tracking number found for this order.');

            return;
        }

        $service = new \App\Services\DomexTrackingService;
        $res = $service->syncOrder($this->sale);

        $this->loadSale();

        if ($res['updated']) {
            $this->dispatch('show-toast', type: 'success', message: 'Domex Status: '.($res['status_detail'] ?? ucfirst($res['new_status'])));
        } else {
            $this->dispatch('show-toast', type: 'info', message: $res['message'] ?? 'Checked Domex tracking.');
        }
    }

    /**
     * Fetch live status from Sri Lanka Post (BePost) for this order
     */
    public function syncPostStatus()
    {
        if (! $this->sale || ! $this->sale->deliverySale || empty($this->sale->deliverySale->tracking_number)) {
            $this->dispatch('show-toast', type: 'warning', message: 'No tracking number found for this order.');

            return;
        }

        $service = new \App\Services\PostTrackingService;
        $res = $service->syncOrder($this->sale);

        $this->loadSale();

        if ($res['updated']) {
            $this->dispatch('show-toast', type: 'success', message: 'Post Status: '.($res['status_detail'] ?? ucfirst($res['new_status'])));
        } else {
            $this->dispatch('show-toast', type: 'info', message: $res['message'] ?? 'Checked Post tracking.');
        }
    }

    /**
     * 4. Mark as Delivered
     */
    public function markDelivered()
    {
        if (! $this->sale) {
            return;
        }

        if ($this->sale->status !== 'confirm') {
            $this->sale->confirmAndDeductStock();
        }

        $this->sale->delivery_status = 'delivered';
        $this->sale->delivered_at = now();
        $this->sale->delivered_by = Auth::id();
        $this->sale->save();

        if ($this->sale->deliverySale) {
            $this->sale->deliverySale->update(['status' => 'Delivered']);
        }

        $this->loadSale();

        // Send Delivered SMS notification to customer
        try {
            \App\Services\SmsService::notifyOrderDelivered($this->sale);
        } catch (\Throwable $e) {
        }

        if ($this->sale->due_amount > 0) {
            $this->openPaymentModal();
            $this->dispatch('show-toast', type: 'info', message: 'Order marked Delivered & SMS sent. Please record COD collection.');

            return;
        }

        $this->dispatch('show-toast', type: 'success', message: 'Delivery completed & customer notified via SMS!');
    }

    /**
     * Open manual SMS modal
     */
    public function openSmsModal()
    {
        $custName = $this->sale->customer_display_name ?: 'Valued Customer';
        $invoice = $this->sale->invoice_number ?: 'N/A';
        $branchName = config('shop.branch', 'Vavuniya Branch');
        $this->smsCustomMessage = "Dear {$custName}, update regarding your Jaffna Gold ({$branchName}) order #{$invoice}: ";
        $this->showSmsModal = true;
    }

    public function closeSmsModal()
    {
        $this->showSmsModal = false;
        $this->smsCustomMessage = '';
    }

    /**
     * Send custom SMS from delivery order detail page
     */
    public function sendCustomSms()
    {
        $phone = $this->sale->customer_display_phone;
        if (empty($phone)) {
            $this->dispatch('show-toast', type: 'error', message: 'Customer has no phone number recorded.');

            return;
        }

        if (empty(trim($this->smsCustomMessage))) {
            $this->dispatch('show-toast', type: 'error', message: 'Please write a message to send.');

            return;
        }

        $result = \App\Services\SmsService::sendSms($phone, trim($this->smsCustomMessage));

        if ($result['success']) {
            $this->closeSmsModal();
            $this->dispatch('show-toast', type: 'success', message: 'SMS sent to customer ('.$phone.') successfully!');
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'SMS failed: '.($result['message'] ?? 'Provider error'));
        }
    }

    /**
     * 5. COD Payment Collection
     */
    public function openPaymentModal()
    {
        $this->paymentAmount = $this->sale->due_amount;
        $this->paymentMethod = 'cash';
        $this->paymentNotes = 'COD Cash collected upon delivery';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
    }

    public function recordPayment()
    {
        if (! $this->sale) {
            return;
        }

        $amount = (float) $this->paymentAmount;
        if ($amount <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Please enter a valid payment amount.');

            return;
        }

        try {
            DB::beginTransaction();

            $sale = $this->sale;
            $newDue = max(0, (float) $sale->due_amount - $amount);
            $newPaymentStatus = ($newDue <= 0) ? 'paid' : 'partial';

            Payment::create([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'amount' => $amount,
                'amount_tendered' => $amount,
                'change_due' => 0,
                'payment_method' => $this->paymentMethod,
                'payment_date' => now(),
                'status' => 'paid',
                'created_by' => Auth::id(),
                'notes' => $this->paymentNotes,
            ]);

            $sale->due_amount = $newDue;
            $sale->payment_status = $newPaymentStatus;
            $sale->save();

            DB::commit();

            $this->closePaymentModal();
            $this->loadSale();
            $this->dispatch('show-toast', type: 'success', message: 'COD Payment recorded successfully! Due balance updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Failed to record payment: '.$e->getMessage());
        }
    }

    /**
     * 6. Cancel or Return Order (restores stock if confirmed)
     */
    public function openCancelModal($isReturn = false)
    {
        $this->isReturnAction = $isReturn;
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->cancelReason = '';
    }

    public function executeCancelOrReturn()
    {
        if (! $this->sale) {
            return;
        }

        try {
            DB::beginTransaction();

            $actionStatus = $this->isReturnAction ? 'returned' : 'cancelled';
            $deliverySaleStatus = $this->isReturnAction ? 'Returned' : 'Cancelled';

            // Restore stock if previously confirmed
            $this->sale->restoreStock();

            $this->sale->delivery_status = $actionStatus;
            $this->sale->status = $this->isReturnAction ? 'rejected' : 'cancelled';
            $this->sale->save();

            if ($this->sale->deliverySale) {
                $this->sale->deliverySale->update([
                    'status' => $deliverySaleStatus,
                    'returned_at' => now(),
                    'return_reason' => $this->cancelReason ?: 'Customer cancelled/returned',
                ]);
            }

            if ($this->sale->ecommerce_order_id) {
                $eOrder = EcommerceOrder::find($this->sale->ecommerce_order_id);
                if ($eOrder) {
                    $eOrder->order_status = 'cancelled';
                    $eOrder->save();
                }
            }

            DB::commit();

            // Notify customer via SMS of cancellation
            try {
                \App\Services\SmsService::notifyOrderCancelled($this->sale, $this->cancelReason);
            } catch (\Throwable $e) {
            }

            $this->closeCancelModal();
            $this->loadSale();

            $msg = $this->isReturnAction ? 'Order marked as Returned. Stock restored & customer notified via SMS.' : 'Order cancelled. Stock restored & customer notified via SMS.';
            $this->dispatch('show-toast', type: 'warning', message: $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Action failed: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.delivery-man.delivery-order-detail', [
            'sale' => $this->sale,
        ]);
    }
}
