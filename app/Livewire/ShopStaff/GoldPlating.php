<?php

namespace App\Livewire\ShopStaff;

use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\GoldPlatingOrder;
use App\Models\GoldPlatingOrderItem;
use App\Models\GoldPlatingPayment;
use App\Models\GoldPlatingWorker;
use App\Models\GoldPlatingWorkerPayment;
use App\Models\HandmadeItem;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Gold Plating & Services')]
#[Layout('components.layouts.shop-staff')]
class GoldPlating extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';

    public $statusFilter = 'all';

    public $serviceFilter = 'all';

    public $workerFilter = 'all';

    public $categoryFilter = 'all'; // all, customer, handmade

    public $dateFilter = '';

    // Create Modal State
    public $showCreateModal = false;

    public $is_shop_work = false;

    public $customer_name = '';

    public $customer_phone = '';

    public $customer_address = '';

    public $worker_id = null;

    public $service_type = 'gold_plating';

    public $item_description = '';

    public $is_charge_tbd = false;

    public $our_cost = null;

    public $customer_charge = null;

    public $advance_amount = 0;

    public $advance_payment_method = 'cash';

    public $expected_delivery_date = null;

    public $notes = '';

    public $serviceItems = []; // [['item_name' => '', 'fee' => '', 'cost' => '']]

    // Edit Modal State
    public $showEditModal = false;

    public $editingOrderId = null;

    public $edit_is_shop_work = false;

    public $edit_customer_name = '';

    public $edit_customer_phone = '';

    public $edit_customer_address = '';

    public $edit_worker_id = null;

    public $edit_service_type = 'gold_plating';

    public $edit_item_description = '';

    public $edit_is_charge_tbd = false;

    public $edit_our_cost = null;

    public $edit_customer_charge = null;

    public $edit_status = 'received';

    public $edit_expected_delivery_date = null;

    public $edit_notes = '';

    public $edit_serviceItems = [];

    // Delete Confirmation Modal State
    public $showDeleteModal = false;

    public $deletingOrderId = null;

    public $deletingOrderCode = '';

    // Ready SMS Modal State
    public $showSmsModal = false;

    public $smsOrderId = null;

    public $smsOrder = null;

    public $smsCustomerPhone = '';

    public $smsCustomerName = '';

    public $smsMessageText = '';

    // Deliver Modal State (Customer Orders)
    public $showDeliverModal = false;

    public $deliveringOrderId = null;

    public $deliveringOrder = null;

    public $collectAmount = 0;

    public $collectPaymentMethod = 'cash';

    public $deliveryNotes = '';

    // Add to POS Stock Modal State (Handmade Plating Orders)
    public $showAddToStockModal = false;

    public $stockOrderId = null;

    public $stockOrder = null;

    public $stockItem = null;

    public $final_selling_price = null;

    // View Details Modal State
    public $showDetailsModal = false;

    public $selectedOrder = null;

    // Check & Receive Items from Worker Modal State
    public $showReceiveModal = false;

    public $receivingOrderId = null;

    public $receivingOrder = null;

    public $receiveNotes = [];

    // Worker Payout Modal State
    public $showWorkerPaymentModal = false;

    public $workerPaymentWorkerId = null;

    public $workerPaymentWorker = null;

    public $workerPaymentAmount = null;

    public $workerPaymentMethod = 'cash';

    public $workerPaymentDate = null;

    public $workerPaymentNotes = '';

    // Worker Ledger Modal State
    public $showWorkerLedgerModal = false;

    public $ledgerWorker = null;

    // Worker Management Modal State (Add / Edit Worker)
    public $showWorkerModal = false;

    public $manageWorkerId = null;

    public $worker_name = '';

    public $worker_phone = '';

    public $worker_opening_balance = 0;

    public $worker_notes = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedServiceFilter()
    {
        $this->resetPage();
    }

    public function updatedWorkerFilter()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function updatedIsChargeTbd($value)
    {
        if ($value) {
            $this->customer_charge = null;
        }
    }

    public function updatedEditIsChargeTbd($value)
    {
        if ($value) {
            $this->edit_customer_charge = null;
        }
    }

    public function addServiceItem()
    {
        $this->serviceItems[] = ['item_name' => '', 'fee' => '', 'cost' => ''];
    }

    public function removeServiceItem($index)
    {
        if (count($this->serviceItems) > 1) {
            unset($this->serviceItems[$index]);
            $this->serviceItems = array_values($this->serviceItems);
            $this->recalculateItemTotals();
        }
    }

    public function updatedServiceItems()
    {
        $this->recalculateItemTotals();
    }

    public function recalculateItemTotals()
    {
        if (! $this->is_charge_tbd) {
            $totalFee = 0;
            $hasAnyFee = false;
            foreach ($this->serviceItems as $it) {
                if (isset($it['fee']) && is_numeric($it['fee']) && $it['fee'] > 0) {
                    $totalFee += floatval($it['fee']);
                    $hasAnyFee = true;
                }
            }
            if ($hasAnyFee) {
                $this->customer_charge = $totalFee;
            }
        }

        $totalCost = 0;
        $hasAnyCost = false;
        foreach ($this->serviceItems as $it) {
            if (isset($it['cost']) && is_numeric($it['cost']) && $it['cost'] > 0) {
                $totalCost += floatval($it['cost']);
                $hasAnyCost = true;
            }
        }
        if ($hasAnyCost) {
            $this->our_cost = $totalCost;
        }
    }

    public function addEditServiceItem()
    {
        $this->edit_serviceItems[] = ['item_name' => '', 'fee' => '', 'cost' => ''];
    }

    public function removeEditServiceItem($index)
    {
        if (count($this->edit_serviceItems) > 1) {
            unset($this->edit_serviceItems[$index]);
            $this->edit_serviceItems = array_values($this->edit_serviceItems);
            $this->recalculateEditItemTotals();
        }
    }

    public function updatedEditServiceItems()
    {
        $this->recalculateEditItemTotals();
    }

    public function recalculateEditItemTotals()
    {
        if (! $this->edit_is_charge_tbd) {
            $totalFee = 0;
            $hasAnyFee = false;
            foreach ($this->edit_serviceItems as $it) {
                if (isset($it['fee']) && is_numeric($it['fee']) && $it['fee'] > 0) {
                    $totalFee += floatval($it['fee']);
                    $hasAnyFee = true;
                }
            }
            if ($hasAnyFee) {
                $this->edit_customer_charge = $totalFee;
            }
        }

        $totalCost = 0;
        $hasAnyCost = false;
        foreach ($this->edit_serviceItems as $it) {
            if (isset($it['cost']) && is_numeric($it['cost']) && $it['cost'] > 0) {
                $totalCost += floatval($it['cost']);
                $hasAnyCost = true;
            }
        }
        if ($hasAnyCost) {
            $this->edit_our_cost = $totalCost;
        }
    }

    /* ----------------------------------------------------
       CREATE ORDER
    ---------------------------------------------------- */
    public function openCreateModal()
    {
        $this->resetCreateForm();
        $this->expected_delivery_date = now()->addDays(3)->format('Y-m-d');
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function updatedIsShopWork($value)
    {
        if ($value) {
            if (empty(trim($this->customer_name)) || $this->customer_name === 'Walking Customer') {
                $this->customer_name = 'Our Shop';
            }
            if (empty(trim($this->customer_phone))) {
                $this->customer_phone = 'Internal';
            }
            $this->customer_charge = null;
            $this->is_charge_tbd = false;
            $this->advance_amount = 0;
            foreach ($this->serviceItems as &$it) {
                $it['fee'] = 0;
            }
        } else {
            if ($this->customer_name === 'Our Shop') {
                $this->customer_name = '';
            }
            if ($this->customer_phone === 'Internal') {
                $this->customer_phone = '';
            }
        }
    }

    public function resetCreateForm()
    {
        $this->is_shop_work = false;
        $this->customer_name = '';
        $this->customer_phone = '';
        $this->customer_address = '';
        $this->worker_id = null;
        $this->service_type = 'gold_plating';
        $this->item_description = '';
        $this->is_charge_tbd = false;
        $this->our_cost = null;
        $this->customer_charge = null;
        $this->advance_amount = 0;
        $this->advance_payment_method = 'cash';
        $this->expected_delivery_date = null;
        $this->notes = '';
        $this->serviceItems = [
            ['item_name' => '', 'fee' => '', 'cost' => ''],
        ];
        $this->resetValidation();
    }

    public function saveOrder()
    {
        if ($this->is_shop_work) {
            if (empty(trim($this->customer_name))) {
                $this->customer_name = 'Our Shop';
            }
            if (empty(trim($this->customer_phone))) {
                $this->customer_phone = 'Internal';
            }

            $this->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'nullable|string|max:50',
                'customer_address' => 'nullable|string|max:500',
                'worker_id' => 'nullable|exists:gold_plating_workers,id',
                'service_type' => 'required|string|max:50',
                'our_cost' => 'nullable|numeric|min:0',
                'expected_delivery_date' => 'nullable|date',
                'notes' => 'nullable|string|max:1000',
            ]);
        } else {
            $this->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:50',
                'customer_address' => 'nullable|string|max:500',
                'worker_id' => 'nullable|exists:gold_plating_workers,id',
                'service_type' => 'required|string|max:50',
                'is_charge_tbd' => 'boolean',
                'our_cost' => 'nullable|numeric|min:0',
                'customer_charge' => $this->is_charge_tbd ? 'nullable' : 'required|numeric|min:0',
                'advance_amount' => 'nullable|numeric|min:0',
                'advance_payment_method' => 'required|string|in:cash,online,card',
                'expected_delivery_date' => 'nullable|date',
                'notes' => 'nullable|string|max:1000',
            ]);
        }

        $hasItem = false;
        foreach ($this->serviceItems as $it) {
            if (! empty(trim($it['item_name'] ?? ''))) {
                $hasItem = true;
                break;
            }
        }
        if (! $hasItem && empty(trim($this->item_description ?? ''))) {
            $this->addError('serviceItems', 'Please provide at least one item description (e.g. kodi chain, ring).');

            return;
        }

        // Process itemized list
        $validItems = [];
        foreach ($this->serviceItems as $it) {
            $name = trim($it['item_name'] ?? '');
            if (! empty($name)) {
                $validItems[] = [
                    'item_name' => $name,
                    'fee' => $this->is_shop_work ? 0 : (isset($it['fee']) && is_numeric($it['fee']) ? floatval($it['fee']) : 0),
                    'cost' => isset($it['cost']) && is_numeric($it['cost']) ? floatval($it['cost']) : 0,
                ];
            }
        }

        if (! empty($validItems)) {
            $descParts = [];
            foreach ($validItems as $vi) {
                if ($this->is_shop_work) {
                    $descParts[] = $vi['item_name'].($vi['cost'] > 0 ? ' (Cost: Rs. '.number_format($vi['cost'], 0).')' : '');
                } else {
                    $descParts[] = $vi['item_name'].($vi['fee'] > 0 ? ' (Rs. '.number_format($vi['fee'], 0).')' : '');
                }
            }
            $finalDescription = implode(', ', $descParts);
            if (! $this->is_shop_work && ! $this->is_charge_tbd && empty($this->customer_charge)) {
                $this->customer_charge = array_sum(array_column($validItems, 'fee'));
            }
            // Auto sum our_cost if item costs entered and our_cost was empty
            $totalItemCosts = array_sum(array_column($validItems, 'cost'));
            if ($totalItemCosts > 0 && empty($this->our_cost)) {
                $this->our_cost = $totalItemCosts;
            }
        } else {
            $finalDescription = trim($this->item_description ?: ($this->is_shop_work ? 'Shop jewelry plating' : 'Jewelry service'));
            $validItems[] = [
                'item_name' => $finalDescription,
                'fee' => $this->is_shop_work ? 0 : floatval($this->customer_charge ?? 0),
                'cost' => floatval($this->our_cost ?? 0),
            ];
        }

        $orderCategory = $this->is_shop_work ? 'shop' : 'customer';
        $advance = $this->is_shop_work ? 0 : (float) ($this->advance_amount ?? 0);
        $charge = ($this->is_shop_work || $this->is_charge_tbd) ? 0 : (float) $this->customer_charge;
        $balance = $this->is_shop_work ? 0 : ($this->is_charge_tbd ? null : max(0, $charge - $advance));

        $order = GoldPlatingOrder::create([
            'order_code' => GoldPlatingOrder::generateOrderCode(),
            'user_id' => Auth::id(),
            'worker_id' => $this->worker_id ?: null,
            'order_category' => $orderCategory,
            'customer_name' => trim($this->customer_name),
            'customer_phone' => trim($this->customer_phone),
            'customer_address' => $this->customer_address ? trim($this->customer_address) : null,
            'service_type' => $this->service_type,
            'item_description' => $finalDescription,
            'is_charge_tbd' => $this->is_shop_work ? false : $this->is_charge_tbd,
            'our_cost' => $this->our_cost ? (float) $this->our_cost : null,
            'customer_charge' => $charge,
            'advance_amount' => $advance,
            'advance_payment_method' => $this->advance_payment_method ?: 'cash',
            'balance_amount' => $balance,
            'status' => 'received',
            'received_date' => now(),
            'expected_delivery_date' => $this->expected_delivery_date ? $this->expected_delivery_date : null,
            'notes' => $this->notes ? trim($this->notes) : null,
        ]);

        // Save items
        foreach ($validItems as $vi) {
            GoldPlatingOrderItem::create([
                'gold_plating_order_id' => $order->id,
                'item_name' => $vi['item_name'],
                'fee' => $vi['fee'],
                'cost' => $vi['cost'],
            ]);
        }

        // Record advance payment if collected (only for customer orders)
        if (! $this->is_shop_work && $advance > 0) {
            GoldPlatingPayment::create([
                'gold_plating_order_id' => $order->id,
                'user_id' => Auth::id(),
                'amount' => $advance,
                'payment_type' => 'advance',
                'payment_method' => $this->advance_payment_method,
                'payment_date' => now(),
                'notes' => 'Advance payment received on order creation',
            ]);
        }

        $orderId = $order->id;
        $this->closeCreateModal();
        $label = $this->is_shop_work ? 'Shop plating order' : 'Service order';
        session()->flash('success', "{$label} {$order->order_code} registered successfully!");

        // Dispatch print window opening
        $this->dispatch('open-print-window', url: route('shop-staff.print.gold-plating', $orderId));
    }

    /* ----------------------------------------------------
       EDIT ORDER
    ---------------------------------------------------- */
    public function openEditModal($id)
    {
        $order = GoldPlatingOrder::with('items')->findOrFail($id);
        $this->editingOrderId = $order->id;
        $this->edit_is_shop_work = ($order->order_category === 'shop');
        $this->edit_customer_name = $order->customer_name;
        $this->edit_customer_phone = $order->customer_phone;
        $this->edit_customer_address = $order->customer_address;
        $this->edit_worker_id = $order->worker_id;
        $this->edit_service_type = $order->service_type;
        $this->edit_item_description = $order->item_description;
        $this->edit_is_charge_tbd = (bool) $order->is_charge_tbd;
        $this->edit_our_cost = $order->our_cost;
        $this->edit_customer_charge = $order->customer_charge;
        $this->edit_status = $order->status;
        $this->edit_expected_delivery_date = $order->expected_delivery_date ? $order->expected_delivery_date->format('Y-m-d') : null;
        $this->edit_notes = $order->notes;

        if ($order->items && $order->items->count() > 0) {
            $this->edit_serviceItems = $order->items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'item_name' => $it->item_name,
                    'fee' => $it->fee > 0 ? $it->fee : '',
                    'cost' => $it->cost > 0 ? $it->cost : '',
                    'status' => $it->status,
                ];
            })->toArray();
        } else {
            $this->edit_serviceItems = [
                [
                    'id' => null,
                    'item_name' => $order->item_description,
                    'fee' => $order->customer_charge > 0 ? $order->customer_charge : '',
                    'cost' => $order->our_cost > 0 ? $order->our_cost : '',
                    'status' => 'pending',
                ],
            ];
        }

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingOrderId = null;
        $this->edit_is_shop_work = false;
        $this->edit_serviceItems = [];
        $this->resetValidation();
    }

    public function updateOrder()
    {
        if ($this->edit_is_shop_work) {
            if (empty(trim($this->edit_customer_name))) {
                $this->edit_customer_name = 'Our Shop';
            }
            if (empty(trim($this->edit_customer_phone))) {
                $this->edit_customer_phone = 'Internal';
            }
            $this->validate([
                'edit_customer_name' => 'required|string|max:255',
                'edit_customer_phone' => 'nullable|string|max:50',
                'edit_customer_address' => 'nullable|string|max:500',
                'edit_worker_id' => 'nullable|exists:gold_plating_workers,id',
                'edit_service_type' => 'required|string|max:50',
                'edit_our_cost' => 'nullable|numeric|min:0',
                'edit_status' => 'required|string|in:received,in_progress,partial,ready,delivered,cancelled',
                'edit_expected_delivery_date' => 'nullable|date',
                'edit_notes' => 'nullable|string|max:1000',
            ]);
        } else {
            $this->validate([
                'edit_customer_name' => 'required|string|max:255',
                'edit_customer_phone' => 'required|string|max:50',
                'edit_customer_address' => 'nullable|string|max:500',
                'edit_worker_id' => 'nullable|exists:gold_plating_workers,id',
                'edit_service_type' => 'required|string|max:50',
                'edit_is_charge_tbd' => 'boolean',
                'edit_our_cost' => 'nullable|numeric|min:0',
                'edit_customer_charge' => $this->edit_is_charge_tbd ? 'nullable' : 'required|numeric|min:0',
                'edit_status' => 'required|string|in:received,in_progress,partial,ready,delivered,cancelled',
                'edit_expected_delivery_date' => 'nullable|date',
                'edit_notes' => 'nullable|string|max:1000',
            ]);
        }

        $hasEditItem = false;
        foreach ($this->edit_serviceItems as $it) {
            if (! empty(trim($it['item_name'] ?? ''))) {
                $hasEditItem = true;
                break;
            }
        }
        if (! $hasEditItem && empty(trim($this->edit_item_description ?? ''))) {
            $this->addError('edit_serviceItems', 'Please provide at least one item description (e.g. kodi chain, ring).');

            return;
        }

        $order = GoldPlatingOrder::with('payments')->findOrFail($this->editingOrderId);

        // Process edit items
        $validItems = [];
        $existingItemIds = [];
        foreach ($this->edit_serviceItems as $it) {
            $name = trim($it['item_name'] ?? '');
            if (! empty($name)) {
                $itemId = ! empty($it['id']) ? intval($it['id']) : null;
                if ($itemId) {
                    $existingItemIds[] = $itemId;
                }
                $validItems[] = [
                    'id' => $itemId,
                    'item_name' => $name,
                    'fee' => $this->edit_is_shop_work ? 0 : (isset($it['fee']) && is_numeric($it['fee']) ? floatval($it['fee']) : 0),
                    'cost' => isset($it['cost']) && is_numeric($it['cost']) ? floatval($it['cost']) : 0,
                    'status' => $it['status'] ?? 'pending',
                ];
            }
        }

        if (! empty($validItems)) {
            $descParts = [];
            foreach ($validItems as $vi) {
                if ($this->edit_is_shop_work) {
                    $descParts[] = $vi['item_name'].($vi['cost'] > 0 ? ' (Cost: Rs. '.number_format($vi['cost'], 0).')' : '');
                } else {
                    $descParts[] = $vi['item_name'].($vi['fee'] > 0 ? ' (Rs. '.number_format($vi['fee'], 0).')' : '');
                }
            }
            $finalDescription = implode(', ', $descParts);
            if (! $this->edit_is_shop_work && ! $this->edit_is_charge_tbd && empty($this->edit_customer_charge)) {
                $this->edit_customer_charge = array_sum(array_column($validItems, 'fee'));
            }
            $totalItemCosts = array_sum(array_column($validItems, 'cost'));
            if ($totalItemCosts > 0 && empty($this->edit_our_cost)) {
                $this->edit_our_cost = $totalItemCosts;
            }
        } else {
            $finalDescription = trim($this->edit_item_description ?: ($this->edit_is_shop_work ? 'Shop jewelry plating' : 'Jewelry service'));
            $validItems[] = [
                'id' => null,
                'item_name' => $finalDescription,
                'fee' => $this->edit_is_shop_work ? 0 : floatval($this->edit_customer_charge ?? 0),
                'cost' => floatval($this->edit_our_cost ?? 0),
                'status' => 'pending',
            ];
        }

        $totalPaid = $order->payments->sum('amount');
        $charge = ($this->edit_is_shop_work || $this->edit_is_charge_tbd) ? 0 : (float) $this->edit_customer_charge;
        $balance = $this->edit_is_shop_work ? 0 : ($this->edit_is_charge_tbd ? null : max(0, $charge - $totalPaid));

        $orderData = [
            'customer_name' => trim($this->edit_customer_name),
            'customer_phone' => trim($this->edit_customer_phone),
            'customer_address' => $this->edit_customer_address ? trim($this->edit_customer_address) : null,
            'worker_id' => $this->edit_worker_id ?: null,
            'service_type' => $this->edit_service_type,
            'item_description' => $finalDescription,
            'is_charge_tbd' => $this->edit_is_shop_work ? false : $this->edit_is_charge_tbd,
            'our_cost' => $this->edit_our_cost ? (float) $this->edit_our_cost : null,
            'customer_charge' => $charge,
            'balance_amount' => $balance,
            'status' => $this->edit_status,
            'expected_delivery_date' => $this->edit_expected_delivery_date ? $this->edit_expected_delivery_date : null,
            'notes' => $this->edit_notes ? trim($this->edit_notes) : null,
        ];

        if ($this->edit_status === 'delivered' && ! $order->actual_delivery_date) {
            $orderData['actual_delivery_date'] = now();
        }

        $previousStatus = $order->status;
        $order->update($orderData);

        // Sync items without deleting already received items unnecessarily
        $order->items()->whereNotIn('id', $existingItemIds)->delete();
        foreach ($validItems as $vi) {
            if ($vi['id']) {
                $order->items()->where('id', $vi['id'])->update([
                    'item_name' => $vi['item_name'],
                    'fee' => $vi['fee'],
                    'cost' => $vi['cost'],
                ]);
            } else {
                GoldPlatingOrderItem::create([
                    'gold_plating_order_id' => $order->id,
                    'item_name' => $vi['item_name'],
                    'fee' => $vi['fee'],
                    'cost' => $vi['cost'],
                    'status' => 'pending',
                ]);
            }
        }

        $smsNotice = '';
        if ($this->edit_status === 'ready' && $previousStatus !== 'ready' && $order->order_category === 'customer' && ! empty($order->customer_phone)) {
            $smsResult = SmsService::notifyGoldPlatingReady($order);
            if ($smsResult['success'] ?? false) {
                $smsNotice = " • Customer SMS pickup notice (7 days) sent to {$order->customer_phone}.";
            }
        }

        $this->closeEditModal();
        session()->flash('success', "Order {$order->order_code} updated successfully!".$smsNotice);
    }

    /* ----------------------------------------------------
       DELETE ORDER
    ---------------------------------------------------- */
    public function confirmDeleteOrder($id)
    {
        $order = GoldPlatingOrder::findOrFail($id);
        $this->deletingOrderId = $order->id;
        $this->deletingOrderCode = $order->order_code;
        $this->showDeleteModal = true;
    }

    public function cancelDeleteOrder()
    {
        $this->showDeleteModal = false;
        $this->deletingOrderId = null;
        $this->deletingOrderCode = '';
    }

    public function deleteOrder()
    {
        if ($this->deletingOrderId) {
            $order = GoldPlatingOrder::findOrFail($this->deletingOrderId);
            $code = $order->order_code;
            // Payments will cascade-delete via foreign key on delete cascade
            $order->delete();

            $this->cancelDeleteOrder();
            session()->flash('success', "Order {$code} has been permanently deleted.");
        }
    }

    /* ----------------------------------------------------
       STATUS PROGRESSION
    ---------------------------------------------------- */
    public function updateStatus($id, $newStatus)
    {
        $order = GoldPlatingOrder::with('items')->findOrFail($id);

        $validStatuses = ['received', 'in_progress', 'partial', 'ready', 'delivered', 'cancelled'];
        if (in_array($newStatus, $validStatuses)) {
            $updateData = ['status' => $newStatus];
            if ($newStatus === 'delivered' && ! $order->actual_delivery_date) {
                $updateData['actual_delivery_date'] = now();
            }
            $order->update($updateData);

            // If manually setting to ready or delivered, mark all pending items as received
            if (in_array($newStatus, ['ready', 'delivered'])) {
                foreach ($order->items as $it) {
                    if (! $it->isReceived()) {
                        $it->markReceived(Auth::id());
                    }
                }
            }

            // Sync with linked handmade item if any
            if ($order->handmade_item_id) {
                $handmadeStatus = match ($newStatus) {
                    'delivered' => 'in_pos_stock',
                    'ready' => 'ready_for_stock',
                    'in_progress', 'partial', 'received' => 'plating',
                    default => 'plating'
                };
                HandmadeItem::where('id', $order->handmade_item_id)->update(['status' => $handmadeStatus]);
            }

            $smsNotice = '';
            if ($newStatus === 'ready' && $order->order_category === 'customer' && ! empty($order->customer_phone)) {
                $smsResult = SmsService::notifyGoldPlatingReady($order);
                if ($smsResult['success'] ?? false) {
                    $smsNotice = " • Customer SMS notification sent to {$order->customer_phone} (7 days pickup notice).";
                } elseif (! empty($smsResult['message'])) {
                    $smsNotice = ' • (SMS Note: '.$smsResult['message'].')';
                }
            }

            session()->flash('success', "Order {$order->order_code} status updated to ".strtoupper(str_replace('_', ' ', $newStatus)).$smsNotice);
        }
    }

    public function markShopOrderCompleted($id)
    {
        $order = GoldPlatingOrder::with('items')->findOrFail($id);

        foreach ($order->items as $it) {
            if (! $it->isReceived()) {
                $it->markReceived(Auth::id());
            }
        }

        $order->update([
            'status' => 'delivered',
            'actual_delivery_date' => now(),
        ]);

        session()->flash('success', "Shop plating job {$order->order_code} completed and marked as returned to shop!");
    }

    /* ----------------------------------------------------
       CHECK & RECEIVE ITEMS FROM WORKER (ONE BY ONE)
    ---------------------------------------------------- */
    public function openReceiveModal($orderId)
    {
        $this->receivingOrderId = $orderId;
        $this->receivingOrder = GoldPlatingOrder::with(['items.receivedBy', 'worker', 'user'])->findOrFail($orderId);
        $this->receiveNotes = [];
        foreach ($this->receivingOrder->items as $it) {
            $this->receiveNotes[$it->id] = $it->received_notes ?? '';
        }
        $this->showReceiveModal = true;
    }

    public function closeReceiveModal()
    {
        $this->showReceiveModal = false;
        $this->receivingOrderId = null;
        $this->receivingOrder = null;
        $this->receiveNotes = [];
    }

    public function receiveSingleItem($itemId)
    {
        $item = GoldPlatingOrderItem::with('order.items')->findOrFail($itemId);
        $notes = isset($this->receiveNotes[$itemId]) ? trim($this->receiveNotes[$itemId]) : null;
        $item->markReceived(Auth::id(), $notes);

        $order = $item->order;
        $newStatus = $order->syncReceivingStatus();

        if ($this->receivingOrderId === $order->id) {
            $this->receivingOrder = GoldPlatingOrder::with(['items.receivedBy', 'worker', 'user'])->find($order->id);
        }

        if ($newStatus === 'ready') {
            $smsNotice = '';
            if ($order->order_category === 'customer' && ! empty($order->customer_phone) && ! $order->ready_sms_sent_at) {
                $smsResult = SmsService::notifyGoldPlatingReady($order);
                if ($smsResult['success'] ?? false) {
                    $smsNotice = " SMS pickup notice (7 days) sent to {$order->customer_phone}!";
                } elseif (! empty($smsResult['message'])) {
                    $smsNotice = ' (SMS: '.$smsResult['message'].')';
                }
            }
            session()->flash('success', "Item '{$item->item_name}' verified & received! All items received — Order {$order->order_code} is now READY for customer pickup.{$smsNotice}");
        } else {
            session()->flash('success', "Item '{$item->item_name}' received successfully! ({$order->received_items_count} of {$order->total_items_count} items received)");
        }
    }

    public function unreceiveSingleItem($itemId)
    {
        $item = GoldPlatingOrderItem::with('order.items')->findOrFail($itemId);
        $item->markPending();

        $order = $item->order;
        $order->syncReceivingStatus();

        if ($this->receivingOrderId === $order->id) {
            $this->receivingOrder = GoldPlatingOrder::with(['items.receivedBy', 'worker', 'user'])->find($order->id);
        }

        session()->flash('success', "Item '{$item->item_name}' reverted back to pending (at workshop).");
    }

    public function receiveAllItems($orderId)
    {
        $order = GoldPlatingOrder::with('items')->findOrFail($orderId);
        foreach ($order->items as $it) {
            if (! $it->isReceived()) {
                $notes = isset($this->receiveNotes[$it->id]) ? trim($this->receiveNotes[$it->id]) : null;
                $it->markReceived(Auth::id(), $notes);
            }
        }

        $newStatus = $order->syncReceivingStatus();

        if ($this->receivingOrderId === $order->id) {
            $this->receivingOrder = GoldPlatingOrder::with(['items.receivedBy', 'worker', 'user'])->find($order->id);
        }

        $smsNotice = '';
        if ($newStatus === 'ready' && $order->order_category === 'customer' && ! empty($order->customer_phone) && ! $order->ready_sms_sent_at) {
            $smsResult = SmsService::notifyGoldPlatingReady($order);
            if ($smsResult['success'] ?? false) {
                $smsNotice = " SMS pickup notice (7 days) sent to customer ({$order->customer_phone})!";
            } elseif (! empty($smsResult['message'])) {
                $smsNotice = ' (SMS: '.$smsResult['message'].')';
            }
        }

        session()->flash('success', "All items for {$order->order_code} marked as received! Order is now READY for pickup.{$smsNotice}");
    }

    /* ----------------------------------------------------
       ADD HANDMADE PLATING JOB TO POS STOCK
    ---------------------------------------------------- */
    public function openAddToStockModal($orderId)
    {
        $this->stockOrder = GoldPlatingOrder::with(['handmadeItem.supplier', 'worker'])->findOrFail($orderId);
        $this->stockOrderId = $orderId;
        $this->stockItem = $this->stockOrder->handmadeItem;

        $cost = (float) ($this->stockItem ? $this->stockItem->total_accumulated_cost : ($this->stockOrder->our_cost ?? 0));
        $this->final_selling_price = ($this->stockItem && $this->stockItem->selling_price) ? $this->stockItem->selling_price : round($cost * 1.5, 0);

        $this->showAddToStockModal = true;
    }

    public function closeAddToStockModal()
    {
        $this->showAddToStockModal = false;
        $this->stockOrderId = null;
        $this->stockOrder = null;
        $this->stockItem = null;
        $this->final_selling_price = null;
        $this->resetValidation();
    }

    public function confirmAddToStock()
    {
        $this->validate([
            'final_selling_price' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            $order = GoldPlatingOrder::with('handmadeItem')->findOrFail($this->stockOrderId);
            $item = $order->handmadeItem;

            $itemName = $item ? $item->item_name : $order->item_description;
            $categoryId = $item ? $item->category_id : null;
            $cost = (float) ($item ? $item->total_accumulated_cost : ($order->our_cost ?? 0));
            $qty = $item ? intval($item->quantity) : 1;

            // Create POS Product
            $posProduct = $this->createPosProductRecord(
                $itemName,
                $categoryId,
                $cost,
                (float) $this->final_selling_price,
                $qty,
                $item ? $item->item_code : null
            );

            if ($item) {
                $item->update([
                    'status' => 'in_pos_stock',
                    'product_detail_id' => $posProduct->id,
                    'selling_price' => (float) $this->final_selling_price,
                    'completed_at' => now(),
                ]);
            }

            $order->update([
                'status' => 'delivered',
                'actual_delivery_date' => now(),
            ]);

            DB::commit();

            $this->closeAddToStockModal();
            session()->flash('success', "Handmade piece '{$itemName}' added to POS Store Billing stock! Barcode: {$posProduct->barcode}");

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to add item to POS stock: '.$e->getMessage());
        }
    }

    private function createPosProductRecord($name, $categoryId, $cost, $sellingPrice, $qty, $itemCode = null): ProductDetail
    {
        if ($itemCode) {
            $code = $itemCode;
            if (ProductDetail::where('code', $code)->exists()) {
                $code = $itemCode.'-'.strtoupper(Str::random(3));
            }
            $barcode = $itemCode;
            if (ProductDetail::where('barcode', $barcode)->exists()) {
                $barcode = $itemCode.'-'.strtoupper(Str::random(3));
            }
        } else {
            $barcode = 'HM-'.strtoupper(Str::random(6));
            $code = 'PROD-'.strtoupper(Str::random(6));
        }

        $defaultBrandId = BrandList::first()->id ?? 1;
        $defaultCategoryId = CategoryList::first()->id ?? 1;

        $product = ProductDetail::create([
            'code' => $code,
            'name' => trim($name),
            'model' => 'Handmade Jewelry',
            'image' => null,
            'description' => 'Locally crafted handmade jewelry piece',
            'barcode' => $barcode,
            'barcode_printed' => 'No',
            'status' => 'active',
            'sale_bonus' => 0.00,
            'brand_id' => $defaultBrandId,
            'category_id' => $categoryId ?: $defaultCategoryId,
            'website_live' => false,
        ]);

        ProductPrice::create([
            'product_id' => $product->id,
            'variant_id' => null,
            'pricing_mode' => 'single',
            'supplier_price' => $cost,
            'selling_price' => $sellingPrice,
            'retail_price' => $sellingPrice,
            'wholesale_price' => $sellingPrice,
            'distributor_price' => null,
            'discount_price' => 0,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'variant_id' => null,
            'available_stock' => $qty,
            'damage_stock' => 0,
            'total_stock' => $qty,
            'sold_count' => 0,
            'restocked_quantity' => 0,
        ]);

        return $product;
    }

    /* ----------------------------------------------------
       DELIVER ITEM & COLLECT BALANCE
    ---------------------------------------------------- */
    public function openDeliverModal($id)
    {
        $this->deliveringOrder = GoldPlatingOrder::with(['payments', 'user', 'worker'])->findOrFail($id);
        $this->deliveringOrderId = $id;

        $totalPaid = $this->deliveringOrder->payments->sum('amount');
        if ($this->deliveringOrder->is_charge_tbd || is_null($this->deliveringOrder->customer_charge)) {
            $this->collectAmount = 0;
        } else {
            $this->collectAmount = max(0, (float) $this->deliveringOrder->customer_charge - $totalPaid);
        }

        $this->collectPaymentMethod = 'cash';
        $this->deliveryNotes = '';
        $this->showDeliverModal = true;
    }

    public function closeDeliverModal()
    {
        $this->showDeliverModal = false;
        $this->deliveringOrderId = null;
        $this->deliveringOrder = null;
    }

    public function completeDelivery()
    {
        $this->validate([
            'collectAmount' => 'required|numeric|min:0',
            'collectPaymentMethod' => 'required|string|in:cash,online,card',
            'deliveryNotes' => 'nullable|string|max:500',
        ]);

        $order = GoldPlatingOrder::with('payments')->findOrFail($this->deliveringOrderId);
        $amount = (float) $this->collectAmount;

        if ($amount > 0) {
            GoldPlatingPayment::create([
                'gold_plating_order_id' => $order->id,
                'user_id' => Auth::id(),
                'amount' => $amount,
                'payment_type' => 'balance',
                'payment_method' => $this->collectPaymentMethod,
                'payment_date' => now(),
                'notes' => $this->deliveryNotes ? trim($this->deliveryNotes) : 'Balance payment collected upon item delivery',
            ]);
        }

        $totalPaid = $order->payments()->sum('amount');
        $newBalance = ($order->is_charge_tbd || is_null($order->customer_charge)) ? 0 : max(0, (float) $order->customer_charge - $totalPaid);

        $order->update([
            'status' => 'delivered',
            'actual_delivery_date' => now(),
            'balance_amount' => $newBalance,
        ]);

        $orderId = $order->id;
        $this->closeDeliverModal();
        session()->flash('success', "Order {$order->order_code} marked as DELIVERED!");

        // Auto print delivery receipt
        $this->dispatch('open-print-window', url: route('shop-staff.print.gold-plating', $orderId));
    }

    /* ----------------------------------------------------
       SMS NOTIFICATION (READY FOR COLLECTION / 7 DAYS NOTICE)
    ---------------------------------------------------- */
    public function openSmsModal($orderId)
    {
        $this->smsOrderId = $orderId;
        $this->smsOrder = GoldPlatingOrder::findOrFail($orderId);
        $this->smsCustomerPhone = $this->smsOrder->customer_phone ?? '';
        $this->smsCustomerName = $this->smsOrder->customer_name ?? '';
        $this->smsMessageText = SmsService::formatGoldPlatingReadyMessage($this->smsOrder);
        $this->showSmsModal = true;
    }

    public function closeSmsModal()
    {
        $this->showSmsModal = false;
        $this->smsOrderId = null;
        $this->smsOrder = null;
        $this->smsCustomerPhone = '';
        $this->smsCustomerName = '';
        $this->smsMessageText = '';
        $this->resetValidation();
    }

    public function resetSmsTemplate($type = 'default')
    {
        if (! $this->smsOrder) {
            return;
        }

        if ($type === 'urgent') {
            $name = trim($this->smsOrder->customer_name ?: 'Valued Customer');
            $code = $this->smsOrder->order_code;
            $branchName = config('shop.branch', 'Vavuniya Branch');
            $shopAddress = config('shop.address', '2nd Cross Street, Vavuniya');
            $shopPhone = config('shop.phone', '0761919650');
            $shopHotline = config('shop.hotline', '021 222 85 89');
            $this->smsMessageText = "URGENT REMINDER: Dear {$name}, your jewelry service order #{$code} is READY for delivery. Please collect your item(s) within 7 days. Jaffna Gold ({$branchName}), {$shopAddress}. Tel: {$shopPhone} / {$shopHotline}.";
        } else {
            $this->smsMessageText = SmsService::formatGoldPlatingReadyMessage($this->smsOrder);
        }
    }

    public function sendSmsNotification()
    {
        if (! $this->smsOrderId) {
            return;
        }

        $this->validate([
            'smsCustomerPhone' => 'required|string|min:7|max:30',
            'smsMessageText' => 'required|string|min:10|max:500',
        ]);

        $order = GoldPlatingOrder::findOrFail($this->smsOrderId);
        $phone = trim($this->smsCustomerPhone);
        $message = trim($this->smsMessageText);

        if ($phone !== $order->customer_phone) {
            $order->update(['customer_phone' => $phone]);
        }

        $isLow = SmsService::isBalanceLow();
        $isFinished = SmsService::isBalanceFinished();
        $balance = SmsService::getBalance();
        $rate = SmsService::getPerSmsRate();
        $contact = SmsService::getTopupContact();

        if (($isLow || $isFinished) && ! request()->boolean('force_double', false)) {
            $balFmt = number_format($balance, 2);
            $doubleRate = number_format($rate * 2, 2);
            $regRate = number_format($rate, 2);
            $stateTitle = $isFinished ? 'Finished' : 'Low';
            $stateLabel = $isFinished ? 'exhausted' : 'low';

            $this->js(<<<JS
Swal.fire({
    title: '⚠️ SMS Balance {$stateTitle}!',
    html: `
        <div class="text-start">
            <p class="mb-2 fw-semibold text-danger">⚠️ SMS account balance is {$stateLabel}!</p>
            <div class="alert alert-warning p-2 mb-3 small">
                <div><strong>Current Balance:</strong> Rs. {$balFmt}</div>
                <div><strong>Regular Rate:</strong> Rs. {$regRate} per SMS</div>
                <div class="text-danger fw-bold mt-1">⚠️ If you continue, double charge (Rs. {$doubleRate}) will apply.</div>
            </div>
            <p class="mb-0 small text-muted">Click <strong>Top Up Request</strong> to request a credit recharge directly to <strong>{$contact}</strong> via SMS, or continue with double charge.</p>
        </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: '<i class="bi bi-send-exclamation me-1"></i> Continue (Double Charge)',
    denyButtonText: '<i class="bi bi-chat-text-fill me-1"></i> Top Up Request via SMS',
    cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
    confirmButtonColor: '#dc3545',
    denyButtonColor: '#0d6efd',
    cancelButtonColor: '#6c757d',
}).then((result) => {
    if (result.isConfirmed) {
        \$wire.sendCustomSmsWithDoubleCharge();
    } else if (result.isDenied) {
        \$wire.requestSmsTopup();
    }
});
JS);

            return;
        }

        $result = SmsService::sendSms($phone, $message);

        if ($result['success'] ?? false) {
            $order->update(['ready_sms_sent_at' => now()]);
            $sentPhone = $phone;
            $this->closeSmsModal();
            session()->flash('success', "SMS notification sent to {$sentPhone} successfully!");
        } else {
            $errorMsg = $result['message'] ?? 'SMS gateway returned an error.';
            $this->addError('smsMessageText', "Failed to send SMS: {$errorMsg}");
        }
    }

    public function requestSmsTopup()
    {
        $contact = SmsService::getTopupContact();
        $balance = SmsService::getBalance();
        $user = auth()->user()?->name ?? 'Staff';
        $smsMsg = 'Top-Up Request: Jaffna Gold (Vavuniya Branch) requested balance recharge (Current Bal: Rs. '.number_format($balance, 2).") by {$user}.";

        try {
            SmsService::sendSms($contact, $smsMsg, false, false);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[Vav] Top-up SMS request failed: '.$e->getMessage());
        }

        $this->js("Swal.fire('Request Sent via SMS!', 'Your SMS top-up request has been dispatched to helpline ({$contact}). Once credited, your balance will update automatically.', 'success')");
    }

    public function sendCustomSmsWithDoubleCharge()
    {
        $this->validate([
            'smsOrderId' => 'required',
            'smsCustomerPhone' => 'required|string|min:9',
            'smsMessageText' => 'required|string|min:10|max:500',
        ]);

        $order = GoldPlatingOrder::findOrFail($this->smsOrderId);
        $phone = trim($this->smsCustomerPhone);
        $message = trim($this->smsMessageText);

        if ($phone !== $order->customer_phone) {
            $order->update(['customer_phone' => $phone]);
        }

        $result = SmsService::sendSms($phone, $message, true);

        if ($result['success'] ?? false) {
            $order->update(['ready_sms_sent_at' => now()]);
            $sentPhone = $phone;
            $this->closeSmsModal();
            session()->flash('success', "SMS notification sent to {$sentPhone} successfully! (Double charge applied)");
        } else {
            $errorMsg = $result['message'] ?? 'SMS gateway returned an error.';
            $this->addError('smsMessageText', "Failed to send SMS: {$errorMsg}");
        }
    }

    /* ----------------------------------------------------
       VIEW ORDER DETAILS
    ---------------------------------------------------- */
    public function viewDetails($id)
    {
        $this->selectedOrder = GoldPlatingOrder::with(['payments.user', 'user', 'worker', 'items.receivedBy'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedOrder = null;
    }

    /* ----------------------------------------------------
       WORKER PAYOUT / PAYMENT MODAL
    ---------------------------------------------------- */
    public function openWorkerPaymentModal($workerId)
    {
        $this->workerPaymentWorker = GoldPlatingWorker::with(['orders', 'payments'])->findOrFail($workerId);
        $this->workerPaymentWorkerId = $workerId;
        $this->workerPaymentAmount = max(0, $this->workerPaymentWorker->outstanding_balance);
        $this->workerPaymentMethod = 'cash';
        $this->workerPaymentDate = now()->format('Y-m-d');
        $this->workerPaymentNotes = '';
        $this->showWorkerPaymentModal = true;
    }

    public function closeWorkerPaymentModal()
    {
        $this->showWorkerPaymentModal = false;
        $this->workerPaymentWorkerId = null;
        $this->workerPaymentWorker = null;
        $this->resetValidation();
    }

    public function saveWorkerPayment()
    {
        $this->validate([
            'workerPaymentAmount' => 'required|numeric|min:1',
            'workerPaymentMethod' => 'required|string|in:cash,bank_transfer,online',
            'workerPaymentDate' => 'required|date',
            'workerPaymentNotes' => 'nullable|string|max:500',
        ]);

        $worker = GoldPlatingWorker::findOrFail($this->workerPaymentWorkerId);

        GoldPlatingWorkerPayment::create([
            'worker_id' => $worker->id,
            'user_id' => Auth::id(),
            'amount' => (float) $this->workerPaymentAmount,
            'payment_method' => $this->workerPaymentMethod,
            'payment_date' => $this->workerPaymentDate,
            'notes' => $this->workerPaymentNotes ? trim($this->workerPaymentNotes) : "Workshop payment given to {$worker->name}",
        ]);

        $this->closeWorkerPaymentModal();
        session()->flash('success', 'Payment of Rs. '.number_format($this->workerPaymentAmount, 2)." recorded for {$worker->name} successfully!");
    }

    /* ----------------------------------------------------
       WORKER LEDGER MODAL
    ---------------------------------------------------- */
    public function openWorkerLedgerModal($workerId)
    {
        $this->ledgerWorker = GoldPlatingWorker::with([
            'orders' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'payments' => function ($q) {
                $q->orderBy('payment_date', 'desc')->orderBy('id', 'desc');
            },
        ])->findOrFail($workerId);

        $this->showWorkerLedgerModal = true;
    }

    public function closeWorkerLedgerModal()
    {
        $this->showWorkerLedgerModal = false;
        $this->ledgerWorker = null;
    }

    /* ----------------------------------------------------
       MANAGE WORKER / OPENING BALANCE MODAL
    ---------------------------------------------------- */
    public function openCreateWorkerModal()
    {
        $this->manageWorkerId = null;
        $this->worker_name = '';
        $this->worker_phone = '';
        $this->worker_opening_balance = 0;
        $this->worker_notes = '';
        $this->showWorkerModal = true;
    }

    public function openEditWorkerModal($workerId)
    {
        $worker = GoldPlatingWorker::findOrFail($workerId);
        $this->manageWorkerId = $worker->id;
        $this->worker_name = $worker->name;
        $this->worker_phone = $worker->phone;
        $this->worker_opening_balance = $worker->opening_payable_balance;
        $this->worker_notes = $worker->notes;
        $this->showWorkerModal = true;
    }

    public function closeWorkerModal()
    {
        $this->showWorkerModal = false;
        $this->manageWorkerId = null;
        $this->resetValidation();
    }

    public function saveWorker()
    {
        $this->validate([
            'worker_name' => 'required|string|max:255',
            'worker_phone' => 'nullable|string|max:50',
            'worker_opening_balance' => 'required|numeric|min:0',
            'worker_notes' => 'nullable|string|max:500',
        ]);

        if ($this->manageWorkerId) {
            $worker = GoldPlatingWorker::findOrFail($this->manageWorkerId);
            $worker->update([
                'name' => trim($this->worker_name),
                'phone' => $this->worker_phone ? trim($this->worker_phone) : null,
                'opening_payable_balance' => (float) $this->worker_opening_balance,
                'notes' => $this->worker_notes ? trim($this->worker_notes) : null,
            ]);
            $msg = "Worker {$worker->name} details updated successfully!";
        } else {
            $worker = GoldPlatingWorker::create([
                'name' => trim($this->worker_name),
                'phone' => $this->worker_phone ? trim($this->worker_phone) : null,
                'opening_payable_balance' => (float) $this->worker_opening_balance,
                'is_active' => true,
                'notes' => $this->worker_notes ? trim($this->worker_notes) : null,
            ]);
            $msg = "New workshop worker {$worker->name} registered successfully!";
        }

        $this->closeWorkerModal();
        session()->flash('success', $msg);
    }

    /* ----------------------------------------------------
       RENDER
    ---------------------------------------------------- */
    public function render()
    {
        $query = GoldPlatingOrder::with(['payments', 'user', 'worker', 'items.receivedBy']);

        if (! empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('item_description', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->serviceFilter !== 'all') {
            $query->where('service_type', $this->serviceFilter);
        }

        if ($this->workerFilter !== 'all') {
            if ($this->workerFilter === 'unassigned') {
                $query->whereNull('worker_id');
            } else {
                $query->where('worker_id', $this->workerFilter);
            }
        }

        if ($this->categoryFilter !== 'all') {
            $query->where('order_category', $this->categoryFilter);
        }

        if (! empty($this->dateFilter)) {
            if ($this->dateFilter === 'today') {
                $query->whereDate('received_date', now()->toDateString());
            } elseif ($this->dateFilter === 'this_week') {
                $query->whereBetween('received_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($this->dateFilter === 'this_month') {
                $query->whereMonth('received_date', now()->month)
                    ->whereYear('received_date', now()->year);
            }
        }

        // Active workers for dropdowns & cards
        $workers = GoldPlatingWorker::where('is_active', true)
            ->with(['orders', 'payments'])
            ->get();

        // Overall stats calculation
        $allOrders = GoldPlatingOrder::all();
        $stats = [
            'total_orders' => $allOrders->count(),
            'in_progress' => $allOrders->whereIn('status', ['received', 'in_progress', 'partial'])->count(),
            'partial' => $allOrders->where('status', 'partial')->count(),
            'ready' => $allOrders->where('status', 'ready')->count(),
            'delivered' => $allOrders->where('status', 'delivered')->count(),
            'total_revenue' => $allOrders->where('order_category', 'customer')->where('is_charge_tbd', false)->sum('customer_charge'),
            'total_cost' => $allOrders->where('is_charge_tbd', false)->sum('our_cost'),
            'total_profit' => $allOrders->where('order_category', 'customer')->where('is_charge_tbd', false)->sum(function ($o) {
                return (float) ($o->customer_charge - ($o->our_cost ?? 0));
            }),
            'pending_balance' => $allOrders->where('order_category', 'customer')->where('status', '!=', 'delivered')->where('is_charge_tbd', false)->sum('balance_amount'),
            'total_worker_payable' => $workers->sum(function ($w) {
                return $w->outstanding_balance;
            }),
        ];

        $orders = $query->latest()->paginate(15);

        return view('livewire.shop-staff.gold-plating', [
            'orders' => $orders,
            'workers' => $workers,
            'stats' => $stats,
        ]);
    }
}
