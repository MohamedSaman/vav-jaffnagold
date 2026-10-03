<?php

namespace App\Livewire\DeliveryMan;

use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\Payment;
use App\Models\ProductDetail;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\WhatsAppOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Delivery Orders & Courier Hub')]
#[Layout('components.layouts.delivery-man')]
class DeliveryManDashboard extends Component
{
    use WithPagination;

    // Filters & Pagination
    public $search = '';

    public $statusFilter = 'all'; // all, pending, packed, in_transit, delivered, returned, cancelled

    public $sourceFilter = 'all'; // all, whatsapp, ecommerce, delivery_man

    public $courierFilter = 'all'; // all, Post, Domex

    public $paymentFilter = 'all'; // all, Cash on Delivery, Online Payment

    public $dateFilter = 'all'; // all, today, yesterday, week, month, custom

    public $dateFrom = '';

    public $dateTo = '';

    public $perPage = 15;

    // Modals - Order Details
    public $showDetailsModal = false;

    public $selectedSale = null;

    // Modals - Edit Order
    public $showEditModal = false;

    public $editSaleId = null;

    public $editCustomerName = '';

    public $editCustomerPhone = '';

    public $editCustomerAddress = '';

    public $editDeliveryMethod = 'Domex';

    public $editTrackingNumber = '';

    public $editPaymentMethod = 'Cash on Delivery';

    public $editDeliveryCharge = 450;

    public $editNotes = '';

    // Modals - Status Change
    public $showStatusModal = false;

    public $statusSaleId = null;

    public $statusSale = null;

    public $targetStatus = '';

    public $courierTrackingNumber = '';

    public $returnReason = '';

    // Modals - COD Payment
    public $showPaymentModal = false;

    public $paymentSaleId = null;

    public $paymentSale = null;

    public $paymentAmount = 0;

    public $paymentMethod = 'cash';

    public $paymentNotes = '';

    // Modals - Delete
    public $showDeleteModal = false;

    public $deleteSaleId = null;

    public $deleteSale = null;

    // Modals - WhatsApp Order Link Generator
    public $showWhatsAppLinkModal = false;

    public $productSearch = '';

    public $selectedWhatsAppItems = []; // [['product_id', 'name', 'code', 'image', 'quantity', 'unit_price', 'discount', 'subtotal', 'is_custom']]

    public $waCustomerName = '';

    public $waCustomerPhone = '';

    public $waDeliveryCharge = 450;

    public $waOrderDiscount = 0;

    public $waNotes = '';

    public $generatedWhatsAppLink = null;

    public $generatedWhatsAppOrder = null;

    public $showWaCustomProductForm = false;

    public $waCustomItemName = '';

    public $waCustomItemPrice = '';

    public $waCustomItemCode = '';

    public $waCustomItemQty = 1;

    public $waCustomItemDiscount = 0;

    // Modals - View WhatsApp Links Manager
    public $showWhatsAppManagerModal = false;

    // Modals - Direct Create Delivery Sale
    public $showCreateSaleModal = false;

    public $directSaleItems = [];

    public $directCustomerName = '';

    public $directCustomerPhone = '';

    public $directCustomerAddress = '';

    public $directDeliveryMethod = 'Domex';

    public $directPaymentMethod = 'Cash on Delivery';

    public $directDeliveryCharge = 450;

    public $directOrderDiscount = 0;

    public $directNotes = '';

    public $showCustomProductForm = false;

    public $customItemName = '';

    public $customItemPrice = '';

    public $customItemCode = '';

    public $customItemQty = 1;

    public $customItemDiscount = 0;

    // Domex Live Courier Sync
    public $isSyncingDomex = false;

    public $lastSyncedDomexAt = null;

    public $showDomexModal = false;

    public $selectedDomexDetail = null;

    public $selectedDomexSale = null;

    // Post (BePost) Live Courier Sync
    public $isSyncingPost = false;

    public $lastSyncedPostAt = null;

    public $showPostModal = false;

    public $selectedPostDetail = null;

    public $selectedPostSale = null;

    // Delivery statistics properties
    public $pendingDeliveries = 0;

    public $deliveredDeliveries = 0;

    public $completedDeliveries = 0;

    public $todaysDeliveries = 0;

    public $pendingPayments = 0;

    public $collectedAmount = 0.0;

    public $recentDeliveries = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');

        if (request()->has('status')) {
            $this->statusFilter = request('status');
        }
        if (request()->has('source')) {
            $this->sourceFilter = request('source');
        }

        $userId = Auth::id();
        $this->pendingDeliveries = Sale::where('status', 'confirm')
            ->where('delivery_status', 'pending')
            ->count();

        $userDeliverySales = Sale::where(function ($q) use ($userId) {
            $q->where('delivered_by', $userId)
                ->orWhere('user_id', $userId);
        });

        $this->deliveredDeliveries = (clone $userDeliverySales)->where('delivery_status', 'delivered')->count();
        $this->completedDeliveries = (clone $userDeliverySales)->where('delivery_status', 'completed')->count();
        $this->todaysDeliveries = (clone $userDeliverySales)->where('delivery_status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();
        $this->pendingPayments = (clone $userDeliverySales)->where('delivery_status', 'delivered')
            ->where('due_amount', '>', 0)
            ->count();
        $this->collectedAmount = (float) ((clone $userDeliverySales)->where('delivery_status', 'delivered')
            ->selectRaw('SUM(total_amount - due_amount) as collected')
            ->value('collected') ?? 0);
        $this->recentDeliveries = (clone $userDeliverySales)->where('delivery_status', '!=', 'completed')->orderBy('created_at', 'desc')->take(10)->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSourceFilter()
    {
        $this->resetPage();
    }

    public function updatedCourierFilter()
    {
        $this->resetPage();
    }

    public function updatedPaymentFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function setSourceFilter($source)
    {
        $this->sourceFilter = $source;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->sourceFilter = 'all';
        $this->courierFilter = 'all';
        $this->paymentFilter = 'all';
        $this->dateFilter = 'all';
        $this->resetPage();
    }

    // ─── WhatsApp Order Link Generator ──────────────────────────────────────────

    public function openWhatsAppLinkModal()
    {
        $this->selectedWhatsAppItems = [];
        $this->productSearch = '';
        $this->waCustomerName = '';
        $this->waCustomerPhone = '';
        $this->waDeliveryCharge = 450;
        $this->waOrderDiscount = 0;
        $this->waNotes = '';
        $this->generatedWhatsAppLink = null;
        $this->generatedWhatsAppOrder = null;
        $this->showWhatsAppLinkModal = true;
    }

    public function closeWhatsAppLinkModal()
    {
        $this->showWhatsAppLinkModal = false;
        $this->selectedWhatsAppItems = [];
        $this->productSearch = '';
        $this->waOrderDiscount = 0;
        $this->generatedWhatsAppLink = null;
        $this->generatedWhatsAppOrder = null;
    }

    public function addProductToWhatsAppOrder($productId)
    {
        $product = ProductDetail::with(['price', 'stock'])->find($productId);
        if (! $product) {
            return;
        }

        // Check if already in items
        foreach ($this->selectedWhatsAppItems as $index => $item) {
            if ($item['product_id'] == $productId) {
                $this->selectedWhatsAppItems[$index]['quantity'] += 1;
                $unitPrice = (float) $this->selectedWhatsAppItems[$index]['unit_price'];
                $discount = (float) ($this->selectedWhatsAppItems[$index]['discount'] ?? 0);
                $qty = (int) $this->selectedWhatsAppItems[$index]['quantity'];
                $this->selectedWhatsAppItems[$index]['subtotal'] = max(0, ($unitPrice - $discount)) * $qty;

                return;
            }
        }

        $unitPrice = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0);
        $imagePath = $product->image ?: '';

        $this->selectedWhatsAppItems[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'image' => $imagePath,
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'discount' => 0,
            'subtotal' => $unitPrice,
        ];

        $this->productSearch = '';
    }

    public function removeProductFromWhatsAppOrder($index)
    {
        if (isset($this->selectedWhatsAppItems[$index])) {
            unset($this->selectedWhatsAppItems[$index]);
            $this->selectedWhatsAppItems = array_values($this->selectedWhatsAppItems);
        }
    }

    public function updateWhatsAppItemQty($index, $qty)
    {
        $qty = max(1, (int) $qty);
        if (isset($this->selectedWhatsAppItems[$index])) {
            $this->selectedWhatsAppItems[$index]['quantity'] = $qty;
            $unitPrice = (float) $this->selectedWhatsAppItems[$index]['unit_price'];
            $discount = (float) ($this->selectedWhatsAppItems[$index]['discount'] ?? 0);
            $this->selectedWhatsAppItems[$index]['subtotal'] = max(0, ($unitPrice - $discount)) * $qty;
        }
    }

    public function updateWhatsAppItemPrice($index, $price)
    {
        $price = max(0, (float) $price);
        if (isset($this->selectedWhatsAppItems[$index])) {
            $this->selectedWhatsAppItems[$index]['unit_price'] = $price;
            $qty = (int) $this->selectedWhatsAppItems[$index]['quantity'];
            $discount = (float) ($this->selectedWhatsAppItems[$index]['discount'] ?? 0);
            $this->selectedWhatsAppItems[$index]['subtotal'] = max(0, ($price - $discount)) * $qty;
        }
    }

    public function updateWhatsAppItemDiscount($index, $discount)
    {
        $discount = max(0, (float) $discount);
        if (isset($this->selectedWhatsAppItems[$index])) {
            $this->selectedWhatsAppItems[$index]['discount'] = $discount;
            $unitPrice = (float) $this->selectedWhatsAppItems[$index]['unit_price'];
            $qty = (int) $this->selectedWhatsAppItems[$index]['quantity'];
            $this->selectedWhatsAppItems[$index]['subtotal'] = max(0, ($unitPrice - $discount)) * $qty;
        }
    }

    public function getWhatsAppGrossSubtotalProperty()
    {
        $total = 0;
        foreach ($this->selectedWhatsAppItems as $item) {
            $total += ((float) ($item['unit_price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }

        return $total;
    }

    public function getWhatsAppTotalItemDiscountProperty()
    {
        $total = 0;
        foreach ($this->selectedWhatsAppItems as $item) {
            $total += ((float) ($item['discount'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }

        return $total;
    }

    public function getWhatsAppTotalDiscountProperty()
    {
        return $this->whatsAppTotalItemDiscount + (float) $this->waOrderDiscount;
    }

    public function getWhatsAppSubtotalProperty()
    {
        return array_sum(array_column($this->selectedWhatsAppItems, 'subtotal'));
    }

    public function getWhatsAppTotalProperty()
    {
        $netAfterOrderDiscount = max(0, $this->whatsAppSubtotal - (float) $this->waOrderDiscount);

        return $netAfterOrderDiscount + (float) $this->waDeliveryCharge;
    }

    public function addCustomProductToWhatsAppOrder()
    {
        $this->validate([
            'waCustomItemName' => 'required|string|min:2|max:150',
            'waCustomItemPrice' => 'required|numeric|min:0',
            'waCustomItemQty' => 'required|integer|min:1',
            'waCustomItemDiscount' => 'nullable|numeric|min:0',
        ]);

        $name = trim($this->waCustomItemName);
        $code = trim($this->waCustomItemCode) ?: ('CUST-'.rand(100, 999));
        $price = (float) $this->waCustomItemPrice;
        $qty = (int) $this->waCustomItemQty;
        $discount = (float) ($this->waCustomItemDiscount ?: 0);
        $subtotal = max(0, ($price - $discount)) * $qty;

        $this->selectedWhatsAppItems[] = [
            'product_id' => null, // Custom instant item
            'name' => $name,
            'code' => $code,
            'image' => '',
            'quantity' => $qty,
            'unit_price' => $price,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'is_custom' => true,
        ];

        // Reset custom input fields
        $this->waCustomItemName = '';
        $this->waCustomItemPrice = '';
        $this->waCustomItemCode = '';
        $this->waCustomItemQty = 1;
        $this->waCustomItemDiscount = 0;
        $this->showWaCustomProductForm = false;

        $this->dispatch('show-toast', type: 'success', message: 'Custom item added to order!');
    }

    public function generateWhatsAppOrderLink()
    {
        if (empty($this->selectedWhatsAppItems)) {
            $this->dispatch('show-toast', type: 'error', message: 'Please select at least one product for the order link.');

            return;
        }

        try {
            $waOrder = WhatsAppOrder::create([
                'created_by' => Auth::id(),
                'customer_name' => trim($this->waCustomerName) ?: null,
                'customer_phone' => trim($this->waCustomerPhone) ?: null,
                'items' => $this->selectedWhatsAppItems,
                'subtotal' => $this->whatsAppGrossSubtotal,
                'discount_amount' => $this->whatsAppTotalDiscount,
                'delivery_charge' => (float) $this->waDeliveryCharge,
                'total_amount' => $this->whatsAppTotal,
                'notes' => trim($this->waNotes) ?: null,
                'status' => 'pending_customer',
                'expires_at' => now()->addDays(3),
            ]);

            $this->generatedWhatsAppLink = $waOrder->checkout_url;
            $this->generatedWhatsAppOrder = $waOrder;

            $this->dispatch('show-toast', type: 'success', message: 'WhatsApp Order Link generated successfully! Ready to send to customer.');
        } catch (\Exception $e) {
            Log::error('Error generating WhatsApp order link: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Failed to generate link: '.$e->getMessage());
        }
    }

    public function openWhatsAppManager()
    {
        $this->showWhatsAppManagerModal = true;
    }

    public function closeWhatsAppManager()
    {
        $this->showWhatsAppManagerModal = false;
    }

    // ─── Direct Create Delivery Sale ─────────────────────────────────────────────

    public function openCreateSaleModal()
    {
        $this->directSaleItems = [];
        $this->productSearch = '';
        $this->directCustomerName = '';
        $this->directCustomerPhone = '';
        $this->directCustomerAddress = '';
        $this->directDeliveryMethod = 'Domex';
        $this->directPaymentMethod = 'Cash on Delivery';
        $this->directDeliveryCharge = 450;
        $this->directOrderDiscount = 0;
        $this->directNotes = '';
        $this->showCustomProductForm = false;
        $this->customItemName = '';
        $this->customItemPrice = '';
        $this->customItemCode = '';
        $this->customItemQty = 1;
        $this->customItemDiscount = 0;
        $this->showCreateSaleModal = true;
    }

    public function closeCreateSaleModal()
    {
        $this->showCreateSaleModal = false;
        $this->directSaleItems = [];
        $this->directOrderDiscount = 0;
        $this->showCustomProductForm = false;
    }

    public function addProductToDirectSale($productId)
    {
        $product = ProductDetail::with(['price', 'stock'])->find($productId);
        if (! $product) {
            return;
        }

        foreach ($this->directSaleItems as $index => $item) {
            if ($item['product_id'] == $productId) {
                $this->directSaleItems[$index]['quantity'] += 1;
                $unitPrice = (float) $this->directSaleItems[$index]['unit_price'];
                $discount = (float) ($this->directSaleItems[$index]['discount'] ?? 0);
                $qty = (int) $this->directSaleItems[$index]['quantity'];
                $this->directSaleItems[$index]['subtotal'] = max(0, ($unitPrice - $discount)) * $qty;
                $this->productSearch = '';

                return;
            }
        }

        $unitPrice = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0);

        $this->directSaleItems[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'discount' => 0,
            'subtotal' => $unitPrice,
        ];

        $this->productSearch = '';
    }

    public function addCustomProductToDirectSale()
    {
        $this->validate([
            'customItemName' => 'required|string|min:2|max:150',
            'customItemPrice' => 'required|numeric|min:0',
            'customItemQty' => 'required|integer|min:1',
            'customItemDiscount' => 'nullable|numeric|min:0',
        ]);

        $name = trim($this->customItemName);
        $code = trim($this->customItemCode) ?: ('CUST-'.rand(100, 999));
        $price = (float) $this->customItemPrice;
        $qty = (int) $this->customItemQty;
        $discount = (float) ($this->customItemDiscount ?: 0);
        $subtotal = max(0, ($price - $discount)) * $qty;

        $this->directSaleItems[] = [
            'product_id' => null, // Custom instant item
            'name' => $name,
            'code' => $code,
            'quantity' => $qty,
            'unit_price' => $price,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'is_custom' => true,
        ];

        // Reset custom input fields
        $this->customItemName = '';
        $this->customItemPrice = '';
        $this->customItemCode = '';
        $this->customItemQty = 1;
        $this->customItemDiscount = 0;
        $this->showCustomProductForm = false;

        $this->dispatch('show-toast', type: 'success', message: 'Custom item added to sale!');
    }

    public function removeProductFromDirectSale($index)
    {
        if (isset($this->directSaleItems[$index])) {
            unset($this->directSaleItems[$index]);
            $this->directSaleItems = array_values($this->directSaleItems);
        }
    }

    public function updateDirectItemQty($index, $qty)
    {
        $qty = max(1, (int) $qty);
        if (isset($this->directSaleItems[$index])) {
            $this->directSaleItems[$index]['quantity'] = $qty;
            $unitPrice = (float) $this->directSaleItems[$index]['unit_price'];
            $discount = (float) ($this->directSaleItems[$index]['discount'] ?? 0);
            $this->directSaleItems[$index]['subtotal'] = max(0, ($unitPrice - $discount)) * $qty;
        }
    }

    public function updateDirectItemPrice($index, $price)
    {
        $price = max(0, (float) $price);
        if (isset($this->directSaleItems[$index])) {
            $this->directSaleItems[$index]['unit_price'] = $price;
            $qty = (int) $this->directSaleItems[$index]['quantity'];
            $discount = (float) ($this->directSaleItems[$index]['discount'] ?? 0);
            $this->directSaleItems[$index]['subtotal'] = max(0, ($price - $discount)) * $qty;
        }
    }

    public function updateDirectItemDiscount($index, $discount)
    {
        $discount = max(0, (float) $discount);
        if (isset($this->directSaleItems[$index])) {
            $this->directSaleItems[$index]['discount'] = $discount;
            $unitPrice = (float) $this->directSaleItems[$index]['unit_price'];
            $qty = (int) $this->directSaleItems[$index]['quantity'];
            $this->directSaleItems[$index]['subtotal'] = max(0, ($unitPrice - $discount)) * $qty;
        }
    }

    public function getDirectGrossSubtotalProperty()
    {
        $total = 0;
        foreach ($this->directSaleItems as $item) {
            $total += ((float) ($item['unit_price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }

        return $total;
    }

    public function getDirectTotalItemDiscountProperty()
    {
        $total = 0;
        foreach ($this->directSaleItems as $item) {
            $total += ((float) ($item['discount'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }

        return $total;
    }

    public function getDirectTotalDiscountProperty()
    {
        return $this->directTotalItemDiscount + (float) $this->directOrderDiscount;
    }

    public function getDirectSubtotalProperty()
    {
        return array_sum(array_column($this->directSaleItems, 'subtotal'));
    }

    public function getDirectTotalProperty()
    {
        $netAfterOrderDiscount = max(0, $this->directSubtotal - (float) $this->directOrderDiscount);

        return $netAfterOrderDiscount + (float) $this->directDeliveryCharge;
    }

    public function saveDirectDeliverySale()
    {
        $this->validate([
            'directCustomerName' => 'required|string|min:2|max:150',
            'directCustomerPhone' => 'required|string|min:9|max:20',
            'directCustomerAddress' => 'required|string|min:5|max:255',
            'directDeliveryMethod' => 'required|string|in:Post,Domex',
            'directPaymentMethod' => 'required|string|in:Cash on Delivery,Online Payment',
            'directDeliveryCharge' => 'required|numeric|min:0',
        ]);

        if (empty($this->directSaleItems)) {
            $this->dispatch('show-toast', type: 'error', message: 'Please select at least one product.');

            return;
        }

        try {
            DB::beginTransaction();

            $cleanPhone = preg_replace('/[^0-9+]/', '', $this->directCustomerPhone);

            // Customer Profile
            $customer = Customer::where('phone', $cleanPhone)->first();
            if (! $customer) {
                $customer = Customer::create([
                    'name' => trim($this->directCustomerName),
                    'phone' => $cleanPhone,
                    'type' => 'retail',
                    'address' => trim($this->directCustomerAddress),
                ]);
            }

            $isCod = ($this->paymentMethod === 'Cash on Delivery' || $this->directPaymentMethod === 'Cash on Delivery');
            $totalAmount = $this->directTotal;
            $dueAmount = $isCod ? $totalAmount : 0;
            $paymentStatus = $isCod ? 'pending' : 'paid';

            // Create Sale
            $sale = Sale::create([
                'sale_id' => Sale::generateSaleId(),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $customer->id,
                'walking_customer_name' => trim($this->directCustomerName),
                'walking_customer_phone' => $cleanPhone,
                'customer_type' => 'retail',
                'subtotal' => $this->directGrossSubtotal,
                'discount_amount' => $this->directTotalDiscount,
                'total_amount' => $totalAmount,
                'due_amount' => $dueAmount,
                'payment_type' => $dueAmount > 0 ? 'partial' : 'full',
                'payment_status' => $paymentStatus,
                'status' => 'confirm',
                'sale_type' => 'staff',
                'order_source' => 'delivery_man',
                'user_id' => Auth::id(),
                'delivery_status' => 'pending', // Pending Pack
                'notes' => $this->directNotes,
            ]);

            // Create Sale Items & Deduct Stock
            foreach ($this->directSaleItems as $it) {
                $itemDiscount = (float) ($it['discount'] ?? 0);
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $it['product_id'],
                    'product_name' => $it['name'],
                    'product_code' => $it['code'],
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'discount_per_unit' => $itemDiscount,
                    'total_discount' => $itemDiscount * $it['quantity'],
                    'total' => $it['subtotal'],
                ]);

                if ($it['product_id']) {
                    $stock = ProductStock::where('product_id', $it['product_id'])->first();
                    if ($stock) {
                        $stock->available_stock = max(0, $stock->available_stock - $it['quantity']);
                        $stock->sold_count += $it['quantity'];
                        $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                        $stock->save();
                    }
                }
            }

            // Create Delivery Sale with Barcode
            $customerDetails = 'Customer: '.trim($this->directCustomerName)."\n"
                .'Phone: '.$cleanPhone."\n"
                .'Address: '.trim($this->directCustomerAddress);

            $deliverySale = DeliverySale::create([
                'sale_id' => $sale->id,
                'delivery_method' => $this->directDeliveryMethod,
                'payment_method' => $this->directPaymentMethod,
                'delivery_charge' => (float) $this->directDeliveryCharge,
                'status' => 'Processing',
                'customer_details' => $customerDetails,
            ]);

            DB::commit();

            // Notify Customer via SMS that order is confirmed
            try {
                $sale->load(['customer', 'deliverySale']);
                \App\Services\SmsService::notifyOrderConfirmed($sale);
            } catch (\Throwable $e) {
                Log::error('[DeliveryManDashboard] Failed to send order confirmed SMS: '.$e->getMessage());
            }

            $this->closeCreateSaleModal();
            $this->dispatch('show-toast', type: 'success', message: 'Delivery Sale created successfully! Parcel is ready for packing.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating direct delivery sale: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Failed to create delivery sale: '.$e->getMessage());
        }
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function getCustomerDisplayName($sale)
    {
        if ($sale->customer && $sale->customer->name && $sale->customer->name !== 'Walking Customer') {
            return $sale->customer->name;
        }
        if (! empty($sale->walking_customer_name)) {
            return $sale->walking_customer_name;
        }
        if ($sale->deliverySale && ! empty($sale->deliverySale->customer_details)) {
            if (preg_match('/Customer:\s*([^|\n]+)/i', $sale->deliverySale->customer_details, $m)) {
                return trim($m[1]);
            }
        }

        return 'Online Customer';
    }

    public function getCustomerPhone($sale)
    {
        if ($sale->customer && $sale->customer->phone && $sale->customer->phone !== 'xxxxx') {
            return $sale->customer->phone;
        }
        if (! empty($sale->walking_customer_phone)) {
            return $sale->walking_customer_phone;
        }
        if ($sale->deliverySale && ! empty($sale->deliverySale->customer_details)) {
            if (preg_match('/Phone:\s*([^|\n]+)/i', $sale->deliverySale->customer_details, $m)) {
                return trim($m[1]);
            }
        }

        return null;
    }

    public function getCustomerAddress($sale)
    {
        if ($sale->deliverySale && ! empty($sale->deliverySale->customer_details)) {
            if (preg_match('/Address:\s*(.+)/is', $sale->deliverySale->customer_details, $m)) {
                return trim($m[1]);
            }
        }
        if ($sale->customer && $sale->customer->address && $sale->customer->address !== 'xxxxx') {
            return $sale->customer->address;
        }

        return 'No address provided';
    }

    public function getWhatsAppLink($phone)
    {
        if (! $phone) {
            return null;
        }
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '94'.substr($clean, 1);
        } elseif (! str_starts_with($clean, '94') && strlen($clean) === 9) {
            $clean = '94'.$clean;
        }

        return 'https://wa.me/'.$clean;
    }

    public function viewOrder($saleId)
    {
        $this->selectedSale = Sale::with([
            'customer',
            'items.product',
            'deliverySale',
            'payments.collectedBy',
            'user',
        ])->find($saleId);

        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedSale = null;
    }

    // ─── Domex Live Tracking Sync ──────────────────────────────────────────────

    /**
     * Auto-sync active Domex orders when the dashboard loads (via wire:init)
     */
    public function autoSyncDomexStatuses()
    {
        $this->syncDomexOrders(false);
    }

    /**
     * Manual sync button clicked by user
     */
    public function syncAllDomexStatuses()
    {
        $this->syncDomexOrders(true);
    }

    /**
     * Core batch sync method for active Domex orders
     */
    public function syncDomexOrders(bool $notify = true)
    {
        $this->isSyncingDomex = true;

        try {
            // Find active delivery orders with Domex courier and a waybill number
            $sales = Sale::query()
                ->whereIn('order_source', ['whatsapp', 'ecommerce', 'delivery_man'])
                ->whereHas('deliverySale', function ($q) {
                    $q->where('delivery_method', 'Domex')
                        ->whereNotNull('tracking_number')
                        ->where('tracking_number', '!=', '');
                })
                ->whereIn('delivery_status', ['pending', 'packed', 'in_transit'])
                ->with(['deliverySale'])
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get();

            if ($sales->isEmpty()) {
                if ($notify) {
                    $this->dispatch('show-toast', type: 'info', message: 'No active Domex parcels waiting for status update.');
                }
                $this->isSyncingDomex = false;

                return;
            }

            $service = new \App\Services\DomexTrackingService;
            $result = $service->syncMultipleOrders($sales);

            $this->lastSyncedDomexAt = now()->format('h:i A');

            if ($notify) {
                if ($result['delivered'] > 0) {
                    $this->dispatch('show-toast', type: 'success', message: "Domex Synced: {$result['synced']} parcels checked, {$result['delivered']} marked as DELIVERED!");
                } else {
                    $this->dispatch('show-toast', type: 'success', message: "Domex Synced: {$result['synced']} parcels checked, {$result['updated']} statuses updated.");
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Domex sync error in dashboard: '.$e->getMessage());
            if ($notify) {
                $this->dispatch('show-toast', type: 'warning', message: 'Domex sync completed with warnings.');
            }
        } finally {
            $this->isSyncingDomex = false;
        }
    }

    /**
     * Sync single order by ID
     */
    public function syncSingleDomex($saleId)
    {
        $sale = Sale::with(['deliverySale', 'domexOrderDetail'])->find($saleId);
        if (! $sale || ! $sale->deliverySale || empty($sale->deliverySale->tracking_number)) {
            $this->dispatch('show-toast', type: 'warning', message: 'No tracking number found for this order.');

            return;
        }

        $service = new \App\Services\DomexTrackingService;
        $res = $service->syncOrder($sale);

        if ($res['updated']) {
            $statusText = $res['status_detail'] ?? ucfirst($res['new_status']);
            $this->dispatch('show-toast', type: 'success', message: "Domex [{$res['waybill']}]: {$statusText}");
        } else {
            $this->dispatch('show-toast', type: 'info', message: $res['message'] ?? 'Domex tracking checked.');
        }

        if ($this->showDetailsModal && $this->selectedSale && $this->selectedSale->id == $saleId) {
            $this->selectedSale->refresh();
        }

        if ($this->showDomexModal && $this->selectedDomexSale && $this->selectedDomexSale->id == $saleId) {
            $this->selectedDomexSale->refresh();
            $this->selectedDomexDetail = $this->selectedDomexSale->domexOrderDetail;
        }
    }

    /**
     * Open Domex full details modal
     */
    public function openDomexModal($saleId)
    {
        $sale = Sale::with(['deliverySale', 'domexOrderDetail', 'customer'])->find($saleId);
        if (! $sale || ! $sale->deliverySale || empty($sale->deliverySale->tracking_number)) {
            $this->dispatch('show-toast', type: 'warning', message: 'No Domex tracking number found for this order.');

            return;
        }

        $this->selectedDomexSale = $sale;
        $this->selectedDomexDetail = $sale->domexOrderDetail;

        // If not synced yet or no detail record, fetch live from Domex
        if (! $this->selectedDomexDetail) {
            $service = new \App\Services\DomexTrackingService;
            $service->syncOrder($sale);
            $sale->refresh();
            $this->selectedDomexSale = $sale;
            $this->selectedDomexDetail = $sale->domexOrderDetail;
        }

        $this->showDomexModal = true;
    }

    /**
     * Close Domex full details modal
     */
    public function closeDomexModal()
    {
        $this->showDomexModal = false;
        $this->selectedDomexDetail = null;
        $this->selectedDomexSale = null;
    }

    /**
     * Refresh Domex details directly inside the modal
     */
    public function refreshModalDomex()
    {
        if (! $this->selectedDomexSale) {
            return;
        }

        $service = new \App\Services\DomexTrackingService;
        $res = $service->syncOrder($this->selectedDomexSale);
        $this->selectedDomexSale->refresh();
        $this->selectedDomexDetail = $this->selectedDomexSale->domexOrderDetail;

        if ($res['updated']) {
            $this->dispatch('show-toast', type: 'success', message: 'Domex details refreshed successfully!');
        } else {
            $this->dispatch('show-toast', type: 'info', message: $res['message'] ?? 'Domex details checked.');
        }
    }

    // ─── Sri Lanka Post (BePost) Live Tracking Sync ──────────────────────────────

    /**
     * Auto-sync active Post orders when dashboard loads
     */
    public function autoSyncPostStatuses()
    {
        $this->syncPostOrders(false);
    }

    /**
     * Manual button click to sync all active Post orders
     */
    public function syncAllPostStatuses()
    {
        $this->syncPostOrders(true);
    }

    /**
     * Batch sync active Post shipments from BePost
     */
    public function syncPostOrders(bool $notify = true)
    {
        $this->isSyncingPost = true;

        try {
            $sales = Sale::query()
                ->whereIn('order_source', ['whatsapp', 'ecommerce', 'delivery_man'])
                ->whereHas('deliverySale', function ($q) {
                    $q->whereRaw('LOWER(delivery_method) = ?', ['post'])
                        ->whereNotNull('tracking_number')
                        ->where('tracking_number', '!=', '');
                })
                ->whereIn('delivery_status', ['pending', 'packed', 'in_transit'])
                ->with(['deliverySale', 'postOrderDetail'])
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get();

            if ($sales->isEmpty()) {
                if ($notify) {
                    $this->dispatch('show-toast', type: 'info', message: 'No active Post parcels waiting for status update.');
                }
                $this->isSyncingPost = false;

                return;
            }

            $service = new \App\Services\PostTrackingService;
            $result = $service->syncMultipleOrders($sales, 2);

            $this->lastSyncedPostAt = now()->format('h:i A');

            if ($notify) {
                if ($result['delivered'] > 0) {
                    $this->dispatch('show-toast', type: 'success', message: "Post Synced: {$result['synced']} parcels checked, {$result['delivered']} marked as DELIVERED!");
                } else {
                    $this->dispatch('show-toast', type: 'success', message: "Post Synced: {$result['synced']} parcels checked, {$result['updated']} statuses updated.");
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Post sync error in dashboard: '.$e->getMessage());
            if ($notify) {
                $this->dispatch('show-toast', type: 'warning', message: 'Post sync completed with warnings.');
            }
        } finally {
            $this->isSyncingPost = false;
        }
    }

    /**
     * Sync single Post order by ID
     */
    public function syncSinglePost($saleId)
    {
        $sale = Sale::with(['deliverySale', 'postOrderDetail'])->find($saleId);
        if (! $sale || ! $sale->deliverySale || empty($sale->deliverySale->tracking_number)) {
            $this->dispatch('show-toast', type: 'warning', message: 'No Post tracking number found for this order.');

            return;
        }

        $service = new \App\Services\PostTrackingService;
        $res = $service->syncOrder($sale);

        if ($res['updated']) {
            $statusText = $res['status_detail'] ?? ucfirst($res['new_status']);
            $this->dispatch('show-toast', type: 'success', message: "Post [{$res['barcode']}]: {$statusText}");
        } else {
            $this->dispatch('show-toast', type: 'info', message: $res['message'] ?? 'Post tracking checked.');
        }

        if ($this->showDetailsModal && $this->selectedSale && $this->selectedSale->id == $saleId) {
            $this->selectedSale->refresh();
        }

        if ($this->showPostModal && $this->selectedPostSale && $this->selectedPostSale->id == $saleId) {
            $this->selectedPostSale->refresh();
            $this->selectedPostDetail = $this->selectedPostSale->postOrderDetail;
        }
    }

    /**
     * Open Post full details modal
     */
    public function openPostModal($saleId)
    {
        $sale = Sale::with(['deliverySale', 'postOrderDetail', 'customer'])->find($saleId);
        if (! $sale || ! $sale->deliverySale || empty($sale->deliverySale->tracking_number)) {
            $this->dispatch('show-toast', type: 'warning', message: 'No Post tracking number found for this order.');

            return;
        }

        $this->selectedPostSale = $sale;
        $this->selectedPostDetail = $sale->postOrderDetail;

        // If not synced yet or no detail record, fetch live from BePost
        if (! $this->selectedPostDetail) {
            $service = new \App\Services\PostTrackingService;
            $service->syncOrder($sale);
            $sale->refresh();
            $this->selectedPostSale = $sale;
            $this->selectedPostDetail = $sale->postOrderDetail;
        }

        $this->showPostModal = true;
    }

    /**
     * Close Post full details modal
     */
    public function closePostModal()
    {
        $this->showPostModal = false;
        $this->selectedPostDetail = null;
        $this->selectedPostSale = null;
    }

    /**
     * Refresh Post details directly inside the modal
     */
    public function refreshModalPost()
    {
        if (! $this->selectedPostSale) {
            return;
        }

        $service = new \App\Services\PostTrackingService;
        $res = $service->syncOrder($this->selectedPostSale);
        $this->selectedPostSale->refresh();
        $this->selectedPostDetail = $this->selectedPostSale->postOrderDetail;

        if ($res['updated']) {
            $this->dispatch('show-toast', type: 'success', message: 'Post details refreshed successfully!');
        } else {
            $this->dispatch('show-toast', type: 'info', message: $res['message'] ?? 'No updates from Post.');
        }
    }

    // ─── Edit Order Modal ───────────────────────────────────────────────────────

    public function openEditOrder($saleId)
    {
        $sale = Sale::with(['customer', 'deliverySale'])->find($saleId);
        if (! $sale) {
            $this->dispatch('show-toast', type: 'error', message: 'Order not found');

            return;
        }

        $this->editSaleId = $sale->id;
        $this->editCustomerName = $this->getCustomerDisplayName($sale);
        $this->editCustomerPhone = $this->getCustomerPhone($sale) ?? '';
        $this->editCustomerAddress = $this->getCustomerAddress($sale);
        $this->editDeliveryMethod = $sale->deliverySale->delivery_method ?? 'Domex';
        $this->editTrackingNumber = $sale->deliverySale->tracking_number ?? '';
        $this->editPaymentMethod = $sale->deliverySale->payment_method ?? ($sale->due_amount > 0 ? 'Cash on Delivery' : 'Online Payment');
        $this->editDeliveryCharge = $sale->deliverySale->delivery_charge ?? 450;
        $this->editNotes = $sale->notes ?? '';

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editSaleId = null;
    }

    public function saveEditOrder()
    {
        $this->validate([
            'editCustomerName' => 'required|string|max:255',
            'editCustomerPhone' => 'required|string|max:50',
            'editCustomerAddress' => 'required|string',
            'editDeliveryMethod' => 'required|string|max:50',
            'editTrackingNumber' => 'nullable|string|max:100',
            'editPaymentMethod' => 'required|string|max:50',
            'editDeliveryCharge' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::with(['customer', 'deliverySale'])->findOrFail($this->editSaleId);

            $newDetails = 'Customer: '.trim($this->editCustomerName)."\nPhone: ".trim($this->editCustomerPhone)."\nAddress: ".trim($this->editCustomerAddress);

            $sale->walking_customer_name = trim($this->editCustomerName);
            $sale->walking_customer_phone = trim($this->editCustomerPhone);
            $sale->notes = $this->editNotes;

            $oldDeliveryCharge = $sale->deliverySale->delivery_charge ?? 0;
            $newDeliveryCharge = (float) $this->editDeliveryCharge;
            $chargeDiff = $newDeliveryCharge - $oldDeliveryCharge;

            if ($chargeDiff != 0) {
                $sale->total_amount += $chargeDiff;
                if ($sale->due_amount > 0 || $this->editPaymentMethod === 'Cash on Delivery') {
                    $sale->due_amount = max(0, $sale->due_amount + $chargeDiff);
                }
            }

            if ($this->editPaymentMethod === 'Cash on Delivery' && $sale->due_amount <= 0 && $sale->payment_status !== 'paid') {
                $sale->due_amount = $sale->total_amount;
                $sale->payment_status = 'pending';
            }

            $sale->save();

            if ($sale->deliverySale) {
                $sale->deliverySale->update([
                    'delivery_method' => $this->editDeliveryMethod,
                    'payment_method' => $this->editPaymentMethod,
                    'delivery_charge' => $newDeliveryCharge,
                    'tracking_number' => $this->editTrackingNumber ?: null,
                    'customer_details' => $newDetails,
                ]);
            }

            if ($sale->customer && $sale->customer->name !== 'Walking Customer') {
                $sale->customer->update([
                    'phone' => trim($this->editCustomerPhone),
                    'address' => trim($this->editCustomerAddress),
                ]);
            }

            DB::commit();

            $this->closeEditModal();
            $this->dispatch('show-toast', type: 'success', message: 'Delivery order updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating delivery order: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Failed to update order: '.$e->getMessage());
        }
    }

    // ─── Status Lifecycle Execution ─────────────────────────────────────────────

    public function openStatusModal($saleId, $targetStatus)
    {
        $this->statusSale = Sale::with(['deliverySale', 'items'])->find($saleId);
        if (! $this->statusSale) {
            return;
        }

        $this->statusSaleId = $saleId;
        $this->targetStatus = $targetStatus;
        $this->courierTrackingNumber = $this->statusSale->deliverySale->tracking_number ?? '';
        $this->returnReason = '';
        $this->showStatusModal = true;
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->statusSaleId = null;
        $this->statusSale = null;
        $this->targetStatus = '';
        $this->courierTrackingNumber = '';
        $this->returnReason = '';
    }

    public function confirmOrder($saleId)
    {
        $sale = Sale::with('items')->find($saleId);
        if (! $sale) {
            $this->dispatch('show-toast', type: 'error', message: 'Order not found.');

            return;
        }

        if ($sale->status === 'confirm') {
            $this->dispatch('show-toast', type: 'info', message: 'Order is already confirmed.');

            return;
        }

        $result = $sale->confirmAndDeductStock();
        if ($result) {
            try {
                \App\Services\SmsService::notifyOrderConfirmed($sale);
            } catch (\Throwable $e) {
            }

            $this->dispatch('show-toast', type: 'success', message: "Order #{$sale->invoice_number} confirmed! Stock deducted & customer notified via SMS.");
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'Failed to confirm order.');
        }
    }

    public function executeStatusChange()
    {
        if (! $this->statusSale || ! $this->targetStatus) {
            $this->closeStatusModal();

            return;
        }

        try {
            DB::beginTransaction();

            $sale = $this->statusSale;
            $action = $this->targetStatus;

            switch ($action) {
                case 'packed':
                    if ($sale->status !== 'confirm') {
                        $sale->confirmAndDeductStock();
                        try {
                            \App\Services\SmsService::notifyOrderConfirmed($sale);
                        } catch (\Throwable $e) {
                        }
                    }
                    $sale->delivery_status = 'packed';
                    $sale->save();
                    if ($sale->deliverySale) {
                        $sale->deliverySale->update(['status' => 'Packed']);
                    }
                    $this->dispatch('show-toast', type: 'success', message: 'Order marked as Packed! Ready for courier.');
                    break;

                case 'in_transit':
                    if ($sale->status !== 'confirm') {
                        $sale->confirmAndDeductStock();
                    }
                    $sale->delivery_status = 'in_transit';
                    $sale->delivered_by = Auth::id();
                    $sale->save();
                    $tracking = $this->courierTrackingNumber ?: ($sale->deliverySale->tracking_number ?? '');
                    if ($sale->deliverySale) {
                        $sale->deliverySale->update([
                            'status' => 'In Transit',
                            'tracking_number' => $tracking,
                        ]);
                    }
                    try {
                        \App\Services\SmsService::notifyOrderInTransit($sale, $sale->deliverySale->delivery_method ?? 'Courier', $tracking);
                    } catch (\Throwable $e) {
                    }

                    $this->dispatch('show-toast', type: 'success', message: 'Handed over to courier! In-transit SMS sent to customer.');
                    break;

                case 'delivered':
                    $sale->delivery_status = 'delivered';
                    $sale->delivered_at = now();
                    $sale->delivered_by = Auth::id();
                    $sale->save();

                    if ($sale->deliverySale) {
                        $sale->deliverySale->update(['status' => 'Delivered']);
                    }

                    try {
                        \App\Services\SmsService::notifyOrderDelivered($sale);
                    } catch (\Throwable $e) {
                    }

                    $this->dispatch('show-toast', type: 'success', message: 'Order marked as Delivered (Awaiting courier payment settlement)!');
                    break;

                case 'completed':
                    $sale->delivery_status = 'completed';
                    if (! $sale->delivered_at) {
                        $sale->delivered_at = now();
                    }
                    $sale->delivered_by = Auth::id();
                    $sale->save();

                    if ($sale->deliverySale) {
                        $sale->deliverySale->update(['status' => 'Completed']);
                    }

                    $this->dispatch('show-toast', type: 'success', message: 'Order status changed to Completed!');
                    break;

                case 'returned':
                case 'cancelled':
                    $newStatus = ($action === 'returned') ? 'returned' : 'cancelled';
                    $deliverySaleStatus = ($action === 'returned') ? 'Returned' : 'Cancelled';

                    // 1. Restore product inventory stock back to store
                    if (! in_array($sale->delivery_status, ['returned', 'cancelled'])) {
                        $sale->restoreStock();
                    }

                    $sale->delivery_status = $newStatus;
                    $sale->status = ($action === 'returned') ? 'rejected' : 'cancelled';
                    $sale->save();

                    if ($sale->deliverySale) {
                        $sale->deliverySale->update([
                            'status' => $deliverySaleStatus,
                            'returned_at' => now(),
                            'return_reason' => $this->returnReason ?: ($action === 'cancelled' ? 'Order cancelled by delivery staff' : 'Customer refused/not reachable'),
                        ]);
                    }

                    if ($sale->ecommerce_order_id) {
                        \App\Models\EcommerceOrder::where('id', $sale->ecommerce_order_id)->update(['order_status' => 'cancelled']);
                    }

                    try {
                        \App\Services\SmsService::notifyOrderCancelled($sale, $this->returnReason);
                    } catch (\Throwable $e) {
                    }

                    $msg = ($action === 'returned')
                        ? "Order #{$sale->invoice_number} marked as Returned. Stock restored back to inventory."
                        : "Order #{$sale->invoice_number} cancelled. All items restored back to inventory stock.";
                    $this->dispatch('show-toast', type: 'warning', message: $msg);
                    break;
            }

            DB::commit();
            $this->closeStatusModal();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error changing delivery status: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Status update failed: '.$e->getMessage());
        }
    }

    // ─── COD Payment Collection ─────────────────────────────────────────────────

    public function openPaymentModal($saleId)
    {
        $sale = Sale::with(['customer', 'deliverySale'])->find($saleId);
        if (! $sale) {
            return;
        }

        $this->paymentSaleId = $sale->id;
        $this->paymentSale = $sale;
        $this->paymentAmount = $sale->due_amount > 0 ? $sale->due_amount : $sale->total_amount;
        $this->paymentMethod = 'cash';
        $this->paymentNotes = 'COD remittance from courier';
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
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentMethod' => 'required|string|in:cash,bank_transfer',
        ]);

        if (! $this->paymentSale) {
            return;
        }

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

            $newDue = max(0, $sale->due_amount - $amount);
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
            $this->dispatch('show-toast', type: 'success', message: 'COD Payment recorded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording payment: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Payment failed: '.$e->getMessage());
        }
    }

    // ─── Delete Sale ────────────────────────────────────────────────────────────

    public function confirmDelete($saleId)
    {
        $this->deleteSale = Sale::with(['items', 'deliverySale'])->find($saleId);
        $this->deleteSaleId = $saleId;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteSaleId = null;
        $this->deleteSale = null;
    }

    public function executeDelete()
    {
        if (! $this->deleteSale) {
            return;
        }

        try {
            DB::beginTransaction();

            $sale = $this->deleteSale;

            if (! in_array($sale->delivery_status, ['returned', 'cancelled'])) {
                $sale->restoreStock();
            }

            Payment::where('sale_id', $sale->id)->delete();
            DeliverySale::where('sale_id', $sale->id)->delete();
            SaleItem::where('sale_id', $sale->id)->delete();
            $sale->delete();

            DB::commit();

            $this->closeDeleteModal();
            $this->dispatch('show-toast', type: 'success', message: 'Order deleted and stock restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting sale: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Deletion failed: '.$e->getMessage());
        }
    }

    // ─── Render ─────────────────────────────────────────────────────────────────

    public function render()
    {
        $currentUser = Auth::user();
        $isDeliveryMan = $currentUser && ($currentUser->staff_type === 'delivery_man' || $currentUser->role === 'delivery_man');

        // 1. Base Query with Strict Scoping:
        // EXCLUSIVELY shows genuine active delivery sales (WhatsApp, Website Store, Delivery Man created)!
        // Completely excludes shop staff POS counter walk-in sales and completed orders.
        $baseQuery = Sale::query()
            ->where('delivery_status', '!=', 'completed')
            ->whereIn('order_source', ['whatsapp', 'ecommerce', 'delivery_man'])
            ->where('order_source', '!=', 'pos')
            ->where(function ($q) use ($currentUser, $isDeliveryMan) {
                if ($isDeliveryMan) {
                    // For logged-in Delivery Man:
                    // 1. Sales assigned to this delivery man
                    // 2. Sales created by this delivery man
                    // 3. Online incoming parcels (WhatsApp / Ecommerce) waiting for delivery
                    $q->where('user_id', $currentUser->id)
                        ->orWhere('delivered_by', $currentUser->id)
                        ->orWhereNull('delivered_by')
                        ->orWhereIn('order_source', ['whatsapp', 'ecommerce']);
                }
            });

        // 2. KPI Metrics
        $totalOrders = (clone $baseQuery)->count();
        $totalOrdersAmount = (clone $baseQuery)->sum('total_amount');
        $pendingPacking = (clone $baseQuery)->where('delivery_status', 'pending')->count();
        $pendingPackingAmount = (clone $baseQuery)->where('delivery_status', 'pending')->sum('total_amount');
        $packedCount = (clone $baseQuery)->where('delivery_status', 'packed')->count();
        $packedAmount = (clone $baseQuery)->where('delivery_status', 'packed')->sum('total_amount');
        $inTransitCount = (clone $baseQuery)->where('delivery_status', 'in_transit')->count();
        $inTransitAmount = (clone $baseQuery)->where('delivery_status', 'in_transit')->sum('total_amount');
        $inTransitCodValue = (clone $baseQuery)->where('delivery_status', 'in_transit')->sum('due_amount');
        $deliveredCount = (clone $baseQuery)->where('delivery_status', 'delivered')->count();
        $deliveredAmount = (clone $baseQuery)->where('delivery_status', 'delivered')->sum('total_amount');
        $returnedCount = (clone $baseQuery)->whereIn('delivery_status', ['returned', 'cancelled'])->count();
        $returnedAmount = (clone $baseQuery)->whereIn('delivery_status', ['returned', 'cancelled'])->sum('total_amount');
        $todayOrders = (clone $baseQuery)->whereDate('created_at', today())->count();

        $whatsappOrdersCount = (clone $baseQuery)->where('order_source', 'whatsapp')->count();
        $ecommerceOrdersCount = (clone $baseQuery)->where('order_source', 'ecommerce')->count();
        $directDeliveryCount = (clone $baseQuery)->where('order_source', 'delivery_man')->count();

        // COD collected metrics
        $todayCollectedCod = Payment::whereIn('sale_id', (clone $baseQuery)->pluck('id'))
            ->whereDate('payment_date', today())
            ->sum('amount');

        $totalCollectedCod = Payment::whereIn('sale_id', (clone $baseQuery)->pluck('id'))
            ->sum('amount');

        // 3. Main Query with Filters
        $query = (clone $baseQuery)->with(['customer', 'deliverySale', 'domexOrderDetail', 'postOrderDetail', 'items.product', 'payments', 'user']);

        // Filter by Status
        if ($this->statusFilter && $this->statusFilter !== 'all') {
            if ($this->statusFilter === 'returned_cancelled') {
                $query->whereIn('delivery_status', ['returned', 'cancelled']);
            } else {
                $query->where('delivery_status', $this->statusFilter);
            }
        }

        // Filter by Order Source
        if ($this->sourceFilter && $this->sourceFilter !== 'all') {
            $query->where('order_source', $this->sourceFilter);
        }

        // Filter by Courier Method
        if ($this->courierFilter && $this->courierFilter !== 'all') {
            $query->whereHas('deliverySale', function ($dq) {
                $dq->where('delivery_method', $this->courierFilter);
            });
        }

        // Filter by Payment Method
        if ($this->paymentFilter && $this->paymentFilter !== 'all') {
            $query->whereHas('deliverySale', function ($dq) {
                $dq->where('payment_method', $this->paymentFilter);
            });
        }

        // Filter by Date
        if ($this->dateFilter === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($this->dateFilter === 'yesterday') {
            $query->whereDate('created_at', today()->subDay());
        } elseif ($this->dateFilter === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->dateFilter === 'month') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($this->dateFilter === 'custom' && $this->dateFrom && $this->dateTo) {
            $query->whereBetween('created_at', [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59']);
        }

        // Search
        if ($this->search) {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
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

        $orders = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        // Fetch products for WhatsApp / Direct Sale picker ONLY when user types in search box
        $availableProducts = [];
        if (($this->showWhatsAppLinkModal || $this->showCreateSaleModal) && ! empty(trim($this->productSearch))) {
            $pTerm = '%'.trim($this->productSearch).'%';
            $availableProducts = ProductDetail::with(['price', 'stock'])
                ->where(function ($q) use ($pTerm) {
                    $q->where('name', 'like', $pTerm)
                        ->orWhere('code', 'like', $pTerm)
                        ->orWhere('barcode', 'like', $pTerm);
                })
                ->take(15)
                ->get();
        }

        // Fetch WhatsApp Order links for WhatsApp manager
        $allWhatsAppOrders = [];
        if ($this->showWhatsAppManagerModal) {
            $allWhatsAppOrders = WhatsAppOrder::with(['creator', 'sale'])
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get();
        }

        return view('livewire.delivery-man.delivery-man-dashboard', [
            'orders' => $orders,
            'availableProducts' => $availableProducts,
            'allWhatsAppOrders' => $allWhatsAppOrders,
            'stats' => [
                'totalOrders' => $totalOrders,
                'totalOrdersAmount' => $totalOrdersAmount,
                'pendingPacking' => $pendingPacking,
                'pendingPackingAmount' => $pendingPackingAmount,
                'packedCount' => $packedCount,
                'packedAmount' => $packedAmount,
                'inTransitCount' => $inTransitCount,
                'inTransitAmount' => $inTransitAmount,
                'inTransitCodValue' => $inTransitCodValue,
                'deliveredCount' => $deliveredCount,
                'deliveredAmount' => $deliveredAmount,
                'returnedCount' => $returnedCount,
                'returnedAmount' => $returnedAmount,
                'todayOrders' => $todayOrders,
                'whatsappOrdersCount' => $whatsappOrdersCount,
                'ecommerceOrdersCount' => $ecommerceOrdersCount,
                'directDeliveryCount' => $directDeliveryCount,
                'todayCollectedCod' => $todayCollectedCod,
                'totalCollectedCod' => $totalCollectedCod,
            ],
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'sourceFilter' => $this->sourceFilter,
            'courierFilter' => $this->courierFilter,
            'paymentFilter' => $this->paymentFilter,
            'dateFilter' => $this->dateFilter,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'showDetailsModal' => $this->showDetailsModal,
            'selectedSale' => $this->selectedSale,
            'showEditModal' => $this->showEditModal,
            'showStatusModal' => $this->showStatusModal,
            'showPaymentModal' => $this->showPaymentModal,
            'showDeleteModal' => $this->showDeleteModal,
            'showWhatsAppLinkModal' => $this->showWhatsAppLinkModal,
            'showWhatsAppManagerModal' => $this->showWhatsAppManagerModal,
            'showCreateSaleModal' => $this->showCreateSaleModal,
        ]);
    }
}
