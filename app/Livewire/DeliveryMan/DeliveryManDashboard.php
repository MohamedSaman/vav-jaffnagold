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
use App\Services\FIFOStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Delivery Orders & Courier Hub')]
#[Layout('components.layouts.delivery-man')]
class DeliveryManDashboard extends Component
{
    use WithPagination;

    // Filters & Pagination
    public $search = '';

    #[Url(as: 'status')]
    public $statusFilter = 'all'; // all, pending, packed, in_transit, delivered, completed, returned_cancelled, payment_due

    public $sourceFilter = 'all'; // all, whatsapp, ecommerce, delivery_man

    #[Url(as: 'courier')]
    public $courierFilter = 'all'; // all, Post, Domex, DHL

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

    public $editInvoiceNumber = '';

    public $editDeliveryStatus = '';

    public $editCustomerName = '';

    public $editCustomerPhone = '';

    public $editCustomerAddress = '';

    public $editDeliveryMethod = 'Domex';

    public $editTrackingNumber = '';

    public $editPaymentMethod = 'Cash on Delivery';

    public $editDeliveryCharge = 450;

    public $editNotes = '';

    public $editSaleItems = [];

    public $editProductSearch = '';

    public $editShowCustomProductForm = false;

    public $editCustomItemName = '';

    public $editCustomItemPrice = '';

    public $editCustomItemCode = '';

    public $editCustomItemQty = 1;

    public $editCustomItemDiscount = 0;

    public $editOrderDiscount = 0;

    public $editPaidAmount = 0;

    public $editPaidMethod = 'Cash';

    public $editPaymentReference = '';

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

    // Modals - Batch Delivered Orders Remittance Settlement
    public $showBatchPaymentModal = false;

    public $batchPaymentCourier = 'Post';

    public $batchPaymentDate = '';

    public $batchPaymentMethod = 'bank_transfer'; // cash, bank_transfer, cheque

    public $batchPaymentReference = '';

    public $batchPaymentNotes = '';

    public $batchReceivedAmount = 0;

    public $batchSelectedSaleIds = [];

    public $batchSelectAll = true;

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

    public $directPaidAmount = 0;

    public $directPaidMethod = 'Cash';

    public $directPaymentReference = '';

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

    public function mount($courier = null)
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');

        // Detect courier from route parameter, route name, or query parameter
        if ($courier) {
            $c = strtolower($courier);
            if ($c === 'domex') {
                $this->courierFilter = 'Domex';
            } elseif ($c === 'post') {
                $this->courierFilter = 'Post';
            } elseif ($c === 'dhl') {
                $this->courierFilter = 'DHL';
            }
        } elseif (request()->routeIs('delivery.orders.domex')) {
            $this->courierFilter = 'Domex';
        } elseif (request()->routeIs('delivery.orders.post')) {
            $this->courierFilter = 'Post';
        } elseif (request()->routeIs('delivery.orders.dhl')) {
            $this->courierFilter = 'DHL';
        } elseif (request()->has('courier')) {
            $cReq = request('courier');
            if (strtolower($cReq) === 'domex') {
                $this->courierFilter = 'Domex';
            } elseif (strtolower($cReq) === 'post') {
                $this->courierFilter = 'Post';
            } elseif (strtolower($cReq) === 'dhl') {
                $this->courierFilter = 'DHL';
            }
        }

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

    public function setCourierFilter($courier)
    {
        $this->courierFilter = $courier;
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
        if (!request()->routeIs('delivery.orders.*')) {
            $this->courierFilter = 'all';
        }
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

    public function openCreateSaleModal(?string $courier = null)
    {
        $this->directSaleItems = [];
        $this->productSearch = '';
        $this->directCustomerName = '';
        $this->directCustomerPhone = '';
        $this->directCustomerAddress = '';

        // Preset courier based on parameter, or active courier filter
        $chosenCourier = $courier;
        if (! $chosenCourier && $this->courierFilter && $this->courierFilter !== 'all') {
            $chosenCourier = $this->courierFilter;
        }
        if (! $chosenCourier || ! in_array($chosenCourier, ['Domex', 'Post', 'DHL'])) {
            $chosenCourier = 'Domex';
        }

        $this->directDeliveryMethod = $chosenCourier;
        if ($chosenCourier === 'DHL') {
            $this->directDeliveryCharge = 4500;
            $this->directPaymentMethod = 'Online Payment';
            $this->directPaidMethod = 'Bank Transfer';
        } else {
            $this->directDeliveryCharge = 450;
            $this->directPaymentMethod = 'Cash on Delivery';
            $this->directPaidMethod = 'Cash';
        }

        $this->directOrderDiscount = 0;
        $this->directPaidAmount = 0;
        $this->directPaymentReference = '';
        $this->directNotes = '';
        $this->showCustomProductForm = false;
        $this->customItemName = '';
        $this->customItemPrice = '';
        $this->customItemCode = '';
        $this->customItemQty = 1;
        $this->customItemDiscount = 0;
        $this->showCreateSaleModal = true;
    }

    public function updatedDirectDeliveryMethod($value)
    {
        if ($value === 'DHL') {
            $this->directDeliveryCharge = 4500;
            $this->directPaymentMethod = 'Online Payment';
            $this->directPaidMethod = 'Bank Transfer';
        } elseif ($value === 'Post') {
            $this->directDeliveryCharge = 450;
        } elseif ($value === 'Domex') {
            $this->directDeliveryCharge = 450;
        }
    }

    public function closeCreateSaleModal()
    {
        $this->showCreateSaleModal = false;
        $this->directSaleItems = [];
        $this->directOrderDiscount = 0;
        $this->directPaidAmount = 0;
        $this->directPaidMethod = 'Cash';
        $this->directPaymentReference = '';
        $this->showCustomProductForm = false;
    }

    public function setDirectPaidFull()
    {
        $this->directPaidAmount = (float) $this->directTotal;
    }

    public function setDirectPaidZero()
    {
        $this->directPaidAmount = 0;
    }

    public function updatedDirectPaymentMethod($value)
    {
        if ($value === 'Online Payment') {
            $this->directPaidAmount = (float) $this->directTotal;
            $this->directPaidMethod = 'Bank Transfer';
        } elseif ($value === 'Cash on Delivery') {
            if ((float) $this->directPaidAmount == (float) $this->directTotal) {
                $this->directPaidAmount = 0;
                $this->directPaidMethod = 'Cash';
            }
        }
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

    public function getDirectDueAmountProperty()
    {
        $paid = max(0, (float) $this->directPaidAmount);

        return max(0, $this->directTotal - $paid);
    }

    public function saveDirectDeliverySale()
    {
        $this->validate([
            'directCustomerName' => 'required|string|min:2|max:150',
            'directCustomerPhone' => 'required|string|min:9|max:20',
            'directCustomerAddress' => 'required|string|min:5|max:255',
            'directDeliveryMethod' => 'required|string|in:Post,Domex,DHL',
            'directPaymentMethod' => 'required|string|in:Cash on Delivery,Online Payment',
            'directDeliveryCharge' => 'required|numeric|min:0',
            'directPaidAmount' => 'nullable|numeric|min:0',
            'directPaidMethod' => 'nullable|string|max:50',
            'directPaymentReference' => 'nullable|string|max:100',
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

            $totalAmount = (float) $this->directTotal;
            $paidAmount = min($totalAmount, max(0, (float) $this->directPaidAmount));
            $dueAmount = max(0, $totalAmount - $paidAmount);

            if ($paidAmount >= $totalAmount) {
                $paymentStatus = 'paid';
                $paymentType = 'full';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
                $paymentType = 'partial';
            } else {
                $paymentStatus = 'pending';
                $paymentType = ($this->directPaymentMethod === 'Cash on Delivery') ? 'full' : 'credit';
            }

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
                'payment_type' => $paymentType,
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

            // Create Payment record if customer paid advance or full
            if ($paidAmount > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $customer->id,
                    'amount' => $paidAmount,
                    'amount_tendered' => $paidAmount,
                    'payment_method' => $this->directPaidMethod ?: ($this->directPaymentMethod === 'Cash on Delivery' ? 'Cash' : 'Bank Transfer'),
                    'payment_reference' => $this->directPaymentReference ?: 'Advance payment at order creation',
                    'is_completed' => true,
                    'payment_date' => now(),
                    'status' => 'paid',
                    'created_by' => Auth::id(),
                    'collected_by' => Auth::id(),
                    'collected_at' => now(),
                    'notes' => 'Advance / Initial payment collected at direct order creation time.',
                ]);
            }

            // Create Delivery Sale with Barcode
            $customerDetails = 'Customer: '.trim($this->directCustomerName)."\n"
                .'Phone: '.$cleanPhone."\n"
                .'Address: '.trim($this->directCustomerAddress);

            if ($paidAmount > 0) {
                $customerDetails .= "\nAdvance Paid: Rs. ".number_format($paidAmount, 2);
                $customerDetails .= "\nRemaining COD Due: Rs. ".number_format($dueAmount, 2);
            }

            $deliverySale = DeliverySale::create([
                'sale_id' => $sale->id,
                'delivery_method' => $this->directDeliveryMethod,
                'payment_method' => ($dueAmount > 0) ? 'Cash on Delivery' : 'Prepaid / Online',
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
        $sale = Sale::with(['customer', 'deliverySale', 'items.product.price', 'items.product.stock', 'payments'])->find($saleId);
        if (! $sale) {
            $this->dispatch('show-toast', type: 'error', message: 'Order not found');

            return;
        }

        $this->editSaleId = $sale->id;
        $this->editInvoiceNumber = $sale->invoice_number ?? $sale->sale_id;
        $this->editDeliveryStatus = $sale->delivery_status ?? 'pending';
        $this->editCustomerName = $this->getCustomerDisplayName($sale);
        $this->editCustomerPhone = $this->getCustomerPhone($sale) ?? '';
        $this->editCustomerAddress = $this->getCustomerAddress($sale);
        $this->editDeliveryMethod = $sale->deliverySale->delivery_method ?? 'Domex';
        $this->editTrackingNumber = $sale->deliverySale->tracking_number ?? '';
        $this->editPaymentMethod = $sale->deliverySale->payment_method ?? ($sale->due_amount > 0 ? 'Cash on Delivery' : 'Online Payment');
        $this->editDeliveryCharge = $sale->deliverySale->delivery_charge ?? 450;
        $this->editNotes = $sale->notes ?? '';

        // Load items from database
        $this->editSaleItems = [];
        $totalItemsDiscount = 0;
        foreach ($sale->items as $item) {
            $unitPrice = (float) $item->unit_price;
            $qty = (int) $item->quantity;
            $discount = (float) ($item->discount_per_unit ?: (($qty > 0 && $item->total_discount > 0) ? ($item->total_discount / $qty) : 0));
            $totalItemsDiscount += ($discount * $qty);
            $subtotal = (float) ($item->total ?: max(0, ($unitPrice - $discount)) * $qty);

            $this->editSaleItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product_name ?: ($item->product?->name ?? 'Custom Item'),
                'code' => $item->product_code ?: ($item->product?->code ?? 'N/A'),
                'quantity' => $qty,
                'original_quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'variant_id' => $item->variant_id,
                'variant_value' => $item->variant_value,
                'is_custom' => empty($item->product_id),
            ];
        }

        // Additional Order Discount
        $saleTotalDiscount = (float) ($sale->discount_amount ?? 0);
        $this->editOrderDiscount = max(0, $saleTotalDiscount - $totalItemsDiscount);

        // Advance / Paid Amount
        $totalPayments = (float) $sale->payments()->sum('amount');
        if ($totalPayments > 0) {
            $this->editPaidAmount = $totalPayments;
            $lastPayment = $sale->payments()->latest('id')->first();
            $this->editPaidMethod = $lastPayment?->payment_method ?? 'Cash';
            $this->editPaymentReference = $lastPayment?->payment_reference ?? '';
        } else {
            $calcPaid = max(0, (float) $sale->total_amount - (float) $sale->due_amount);
            $this->editPaidAmount = $calcPaid;
            $this->editPaidMethod = ($calcPaid > 0) ? 'Bank Transfer' : 'Cash';
            $this->editPaymentReference = '';
        }

        // Reset search & custom product inputs
        $this->editProductSearch = '';
        $this->editShowCustomProductForm = false;
        $this->editCustomItemName = '';
        $this->editCustomItemPrice = '';
        $this->editCustomItemCode = '';
        $this->editCustomItemQty = 1;
        $this->editCustomItemDiscount = 0;

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editSaleId = null;
        $this->editInvoiceNumber = '';
        $this->editDeliveryStatus = '';
        $this->editSaleItems = [];
        $this->editProductSearch = '';
        $this->editShowCustomProductForm = false;
        $this->editCustomItemName = '';
        $this->editCustomItemPrice = '';
        $this->editCustomItemCode = '';
        $this->editCustomItemQty = 1;
        $this->editCustomItemDiscount = 0;
        $this->editOrderDiscount = 0;
        $this->editPaidAmount = 0;
        $this->editPaidMethod = 'Cash';
        $this->editPaymentReference = '';
    }

    public function updatedEditPaymentMethod($value)
    {
        if ($value === 'Online Payment') {
            $this->editPaidAmount = (float) $this->editTotal;
            $this->editPaidMethod = 'Bank Transfer';
        } elseif ($value === 'Cash on Delivery') {
            if ((float) $this->editPaidAmount == (float) $this->editTotal) {
                $this->editPaidAmount = 0;
                $this->editPaidMethod = 'Cash';
            }
        }
    }

    public function setEditPaidZero()
    {
        $this->editPaidAmount = 0;
        $this->editPaymentMethod = 'Cash on Delivery';
        $this->editPaidMethod = 'Cash';
    }

    public function setEditPaidFull()
    {
        $this->editPaidAmount = (float) $this->editTotal;
        $this->editPaymentMethod = 'Online Payment';
        $this->editPaidMethod = 'Bank Transfer';
    }

    public function addProductToEditOrder($productId)
    {
        $product = ProductDetail::with(['price', 'stock'])->find($productId);
        if (! $product) {
            return;
        }

        foreach ($this->editSaleItems as $index => $item) {
            if ($item['product_id'] == $productId) {
                $this->editSaleItems[$index]['quantity'] += 1;
                $unitPrice = (float) $this->editSaleItems[$index]['unit_price'];
                $discount = (float) ($this->editSaleItems[$index]['discount'] ?? 0);
                $qty = (int) $this->editSaleItems[$index]['quantity'];
                $this->editSaleItems[$index]['subtotal'] = max(0, ($unitPrice - $discount)) * $qty;
                $this->editProductSearch = '';

                return;
            }
        }

        $unitPrice = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0);

        $this->editSaleItems[] = [
            'id' => null,
            'product_id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'quantity' => 1,
            'original_quantity' => 0,
            'unit_price' => $unitPrice,
            'discount' => 0,
            'subtotal' => $unitPrice,
            'variant_id' => null,
            'variant_value' => null,
            'is_custom' => false,
        ];

        $this->editProductSearch = '';
    }

    public function addCustomProductToEditOrder()
    {
        $this->validate([
            'editCustomItemName' => 'required|string|min:2|max:150',
            'editCustomItemPrice' => 'required|numeric|min:0',
            'editCustomItemQty' => 'required|integer|min:1',
            'editCustomItemDiscount' => 'nullable|numeric|min:0',
        ]);

        $name = trim($this->editCustomItemName);
        $code = trim($this->editCustomItemCode) ?: ('CUST-'.rand(100, 999));
        $price = (float) $this->editCustomItemPrice;
        $qty = (int) $this->editCustomItemQty;
        $discount = (float) ($this->editCustomItemDiscount ?: 0);
        $subtotal = max(0, ($price - $discount)) * $qty;

        $this->editSaleItems[] = [
            'id' => null,
            'product_id' => null,
            'name' => $name,
            'code' => $code,
            'quantity' => $qty,
            'original_quantity' => 0,
            'unit_price' => $price,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'variant_id' => null,
            'variant_value' => null,
            'is_custom' => true,
        ];

        $this->editCustomItemName = '';
        $this->editCustomItemPrice = '';
        $this->editCustomItemCode = '';
        $this->editCustomItemQty = 1;
        $this->editCustomItemDiscount = 0;
        $this->editShowCustomProductForm = false;

        $this->dispatch('show-toast', type: 'success', message: 'Custom item added to order!');
    }

    public function removeProductFromEditOrder($index)
    {
        if (isset($this->editSaleItems[$index])) {
            unset($this->editSaleItems[$index]);
            $this->editSaleItems = array_values($this->editSaleItems);
        }
    }

    public function updateEditItemQty($index, $qty)
    {
        $qty = max(1, (int) $qty);
        if (isset($this->editSaleItems[$index])) {
            $this->editSaleItems[$index]['quantity'] = $qty;
            $unitPrice = (float) $this->editSaleItems[$index]['unit_price'];
            $discount = (float) ($this->editSaleItems[$index]['discount'] ?? 0);
            $this->editSaleItems[$index]['subtotal'] = max(0, ($unitPrice - $discount)) * $qty;
        }
    }

    public function updateEditItemPrice($index, $price)
    {
        $price = max(0, (float) $price);
        if (isset($this->editSaleItems[$index])) {
            $this->editSaleItems[$index]['unit_price'] = $price;
            $qty = (int) $this->editSaleItems[$index]['quantity'];
            $discount = (float) ($this->editSaleItems[$index]['discount'] ?? 0);
            $this->editSaleItems[$index]['subtotal'] = max(0, ($price - $discount)) * $qty;
        }
    }

    public function updateEditItemDiscount($index, $discount)
    {
        $discount = max(0, (float) $discount);
        if (isset($this->editSaleItems[$index])) {
            $this->editSaleItems[$index]['discount'] = $discount;
            $unitPrice = (float) $this->editSaleItems[$index]['unit_price'];
            $qty = (int) $this->editSaleItems[$index]['quantity'];
            $this->editSaleItems[$index]['subtotal'] = max(0, ($unitPrice - $discount)) * $qty;
        }
    }

    public function getEditGrossSubtotalProperty()
    {
        $total = 0;
        foreach ($this->editSaleItems as $item) {
            $total += ((float) ($item['unit_price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }

        return $total;
    }

    public function getEditTotalItemDiscountProperty()
    {
        $total = 0;
        foreach ($this->editSaleItems as $item) {
            $total += ((float) ($item['discount'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }

        return $total;
    }

    public function getEditTotalDiscountProperty()
    {
        return $this->editTotalItemDiscount + (float) $this->editOrderDiscount;
    }

    public function getEditSubtotalProperty()
    {
        return array_sum(array_column($this->editSaleItems, 'subtotal'));
    }

    public function getEditTotalProperty()
    {
        $netAfterOrderDiscount = max(0, $this->editSubtotal - (float) $this->editOrderDiscount);

        return $netAfterOrderDiscount + (float) $this->editDeliveryCharge;
    }

    public function getEditDueAmountProperty()
    {
        $paid = max(0, (float) $this->editPaidAmount);

        return max(0, $this->editTotal - $paid);
    }

    public function saveEditOrder()
    {
        $this->validate([
            'editCustomerName' => 'required|string|min:2|max:255',
            'editCustomerPhone' => 'required|string|min:9|max:50',
            'editCustomerAddress' => 'required|string|min:5',
            'editDeliveryMethod' => 'required|string|in:Post,Domex,DHL',
            'editPaymentMethod' => 'required|string|in:Cash on Delivery,Online Payment',
            'editDeliveryCharge' => 'required|numeric|min:0',
            'editOrderDiscount' => 'nullable|numeric|min:0',
            'editPaidAmount' => 'nullable|numeric|min:0',
            'editPaidMethod' => 'nullable|string|max:50',
            'editPaymentReference' => 'nullable|string|max:100',
        ]);

        if (empty($this->editSaleItems)) {
            $this->dispatch('show-toast', type: 'error', message: 'An order must contain at least one item.');

            return;
        }

        try {
            DB::beginTransaction();

            $sale = Sale::with(['customer', 'deliverySale', 'items'])->findOrFail($this->editSaleId);

            $cleanPhone = preg_replace('/[^0-9+]/', '', $this->editCustomerPhone);

            // Determine if stock was previously deducted
            $shouldManageStock = ($sale->status === 'confirm' && ! in_array($sale->delivery_status, ['returned', 'cancelled']));

            $existingItems = $sale->items->keyBy('id');
            $keptItemIds = [];

            foreach ($this->editSaleItems as $item) {
                $itemId = $item['id'] ?? null;
                $newQty = max(1, (int) $item['quantity']);
                $productId = $item['product_id'] ?? null;
                $variantId = $item['variant_id'] ?? null;
                $variantValue = $item['variant_value'] ?? null;
                $unitPrice = max(0, (float) $item['unit_price']);
                $discount = max(0, (float) ($item['discount'] ?? 0));
                $totalDiscount = $discount * $newQty;
                $subtotal = max(0, ($unitPrice - $discount)) * $newQty;

                if ($itemId && isset($existingItems[$itemId])) {
                    $keptItemIds[] = $itemId;
                    $existing = $existingItems[$itemId];
                    $oldQty = (int) $existing->quantity;
                    $diff = $newQty - $oldQty;

                    if ($shouldManageStock && $productId && $diff != 0) {
                        if ($diff > 0) {
                            try {
                                FIFOStockService::deductStock($productId, $diff, $variantId, $variantValue);
                            } catch (\Throwable $e) {
                                $stock = ProductStock::where('product_id', $productId)->first();
                                if ($stock) {
                                    $stock->available_stock = max(0, $stock->available_stock - $diff);
                                    $stock->sold_count += $diff;
                                    $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                                    $stock->save();
                                }
                            }
                        } else {
                            $restoreQty = abs($diff);
                            try {
                                FIFOStockService::restoreStock($productId, $restoreQty, $variantId, $variantValue);
                            } catch (\Throwable $e) {
                                $stock = ProductStock::where('product_id', $productId)->first();
                                if ($stock) {
                                    $stock->available_stock += $restoreQty;
                                    if ($stock->sold_count >= $restoreQty) {
                                        $stock->sold_count -= $restoreQty;
                                    }
                                    $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                                    $stock->save();
                                }
                            }
                        }
                    }

                    $existing->update([
                        'quantity' => $newQty,
                        'unit_price' => $unitPrice,
                        'discount_per_unit' => $discount,
                        'total_discount' => $totalDiscount,
                        'total' => $subtotal,
                    ]);
                } else {
                    if ($shouldManageStock && $productId && $newQty > 0) {
                        try {
                            FIFOStockService::deductStock($productId, $newQty, $variantId, $variantValue);
                        } catch (\Throwable $e) {
                            $stock = ProductStock::where('product_id', $productId)->first();
                            if ($stock) {
                                $stock->available_stock = max(0, $stock->available_stock - $newQty);
                                $stock->sold_count += $newQty;
                                $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                                $stock->save();
                            }
                        }
                    }

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $productId,
                        'product_name' => $item['name'],
                        'product_code' => $item['code'],
                        'quantity' => $newQty,
                        'unit_price' => $unitPrice,
                        'discount_per_unit' => $discount,
                        'total_discount' => $totalDiscount,
                        'total' => $subtotal,
                        'variant_id' => $variantId,
                        'variant_value' => $variantValue,
                    ]);
                }
            }

            // Restore stock & delete removed items
            foreach ($existingItems as $oldItemId => $oldItem) {
                if (! in_array($oldItemId, $keptItemIds)) {
                    if ($shouldManageStock && $oldItem->product_id && $oldItem->quantity > 0) {
                        try {
                            FIFOStockService::restoreStock(
                                $oldItem->product_id,
                                $oldItem->quantity,
                                $oldItem->variant_id,
                                $oldItem->variant_value
                            );
                        } catch (\Throwable $e) {
                            $stock = ProductStock::where('product_id', $oldItem->product_id)->first();
                            if ($stock) {
                                $stock->available_stock += $oldItem->quantity;
                                if ($stock->sold_count >= $oldItem->quantity) {
                                    $stock->sold_count -= $oldItem->quantity;
                                }
                                $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                                $stock->save();
                            }
                        }
                    }
                    $oldItem->delete();
                }
            }

            // Clear cache
            try {
                \App\Http\Controllers\ProductApiController::clearCache();
            } catch (\Throwable $e) {
            }

            // Totals and Payments Calculation
            $totalAmount = (float) $this->editTotal;
            $paidAmount = min($totalAmount, max(0, (float) $this->editPaidAmount));
            $dueAmount = max(0, $totalAmount - $paidAmount);

            if ($paidAmount >= $totalAmount) {
                $paymentStatus = 'paid';
                $paymentType = 'full';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
                $paymentType = 'partial';
            } else {
                $paymentStatus = 'pending';
                $paymentType = ($this->editPaymentMethod === 'Cash on Delivery') ? 'full' : 'credit';
            }

            $sale->walking_customer_name = trim($this->editCustomerName);
            $sale->walking_customer_phone = $cleanPhone;
            $sale->subtotal = $this->editGrossSubtotal;
            $sale->discount_amount = $this->editTotalDiscount;
            $sale->total_amount = $totalAmount;
            $sale->due_amount = $dueAmount;
            $sale->payment_status = $paymentStatus;
            $sale->payment_type = $paymentType;
            $sale->notes = $this->editNotes;
            $sale->save();

            // Sync Payments
            $payments = Payment::where('sale_id', $sale->id)->get();
            if ($paidAmount <= 0) {
                Payment::where('sale_id', $sale->id)->delete();
            } elseif ($payments->isEmpty()) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'amount' => $paidAmount,
                    'amount_tendered' => $paidAmount,
                    'payment_method' => $this->editPaidMethod ?: 'Cash',
                    'payment_reference' => $this->editPaymentReference ?: 'Advance payment recorded during order edit',
                    'is_completed' => true,
                    'payment_date' => now(),
                    'status' => 'paid',
                    'created_by' => Auth::id(),
                    'collected_by' => Auth::id(),
                    'collected_at' => now(),
                    'notes' => 'Advance / Payment recorded during order edit.',
                ]);
            } else {
                $firstPayment = $payments->first();
                $firstPayment->amount = $paidAmount;
                $firstPayment->amount_tendered = $paidAmount;
                if (! empty($this->editPaidMethod)) {
                    $firstPayment->payment_method = $this->editPaidMethod;
                }
                if (! empty($this->editPaymentReference)) {
                    $firstPayment->payment_reference = $this->editPaymentReference;
                }
                $firstPayment->save();

                if ($payments->count() > 1) {
                    Payment::where('sale_id', $sale->id)->where('id', '!=', $firstPayment->id)->delete();
                }
            }

            // Sync DeliverySale
            $newDetails = 'Customer: '.trim($this->editCustomerName)."\nPhone: ".$cleanPhone."\nAddress: ".trim($this->editCustomerAddress);
            if ($paidAmount > 0) {
                $newDetails .= "\nAdvance Paid: Rs. ".number_format($paidAmount, 2);
                $newDetails .= "\nRemaining COD Due: Rs. ".number_format($dueAmount, 2);
            }

            if ($sale->deliverySale) {
                $sale->deliverySale->update([
                    'delivery_method' => $this->editDeliveryMethod,
                    'payment_method' => ($dueAmount > 0) ? 'Cash on Delivery' : 'Prepaid / Online',
                    'delivery_charge' => (float) $this->editDeliveryCharge,
                    'tracking_number' => $this->editTrackingNumber ?: null,
                    'customer_details' => $newDetails,
                ]);
            }

            if ($sale->customer && $sale->customer->name !== 'Walking Customer') {
                $sale->customer->update([
                    'name' => trim($this->editCustomerName),
                    'phone' => $cleanPhone,
                    'address' => trim($this->editCustomerAddress),
                ]);
            }

            DB::commit();

            $this->closeEditModal();
            $this->dispatch('show-toast', type: 'success', message: 'Order #'.$sale->invoice_number.' updated successfully! Products, advance, and stock synchronized.');
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
            } elseif ($sale->delivery_status === 'delivered' && $newDue <= 0) {
                $sale->delivery_status = 'completed';
                if ($sale->deliverySale) {
                    $sale->deliverySale->update(['status' => 'Completed']);
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

    // ─── Batch Delivered Orders Remittance Settlement ───────────────────────────

    public function openBatchPaymentModal(?string $courier = null)
    {
        $effectiveCourier = $courier;
        if (! $effectiveCourier && $this->courierFilter !== 'all') {
            $effectiveCourier = $this->courierFilter;
        }
        if (! $effectiveCourier || ! in_array($effectiveCourier, ['Post', 'Domex', 'DHL', 'all'])) {
            $effectiveCourier = 'Post';
        }

        $this->batchPaymentCourier = $effectiveCourier;
        $this->batchPaymentDate = now()->format('Y-m-d');
        $this->batchPaymentMethod = 'bank_transfer';
        $this->batchPaymentReference = '';
        $this->batchPaymentNotes = "{$effectiveCourier} delivered orders remittance collection";

        // Query all delivered orders with outstanding due for this courier
        $sales = $this->getDeliveredSettlementQuery($effectiveCourier)->get(['id', 'due_amount']);
        $this->batchSelectedSaleIds = $sales->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->batchSelectAll = true;
        $this->batchReceivedAmount = (float) $sales->sum('due_amount');
        $this->showBatchPaymentModal = true;
    }

    public function switchBatchCourier(string $courier)
    {
        if (in_array($courier, ['Post', 'Domex', 'DHL', 'all'])) {
            $this->batchPaymentCourier = $courier;
            $sales = $this->getDeliveredSettlementQuery($courier)->get(['id', 'due_amount']);
            $this->batchSelectedSaleIds = $sales->pluck('id')->map(fn ($id) => (string) $id)->toArray();
            $this->batchSelectAll = true;
            $this->batchReceivedAmount = (float) $sales->sum('due_amount');
            $this->batchPaymentNotes = "{$courier} delivered orders remittance collection";
        }
    }

    public function closeBatchPaymentModal()
    {
        $this->showBatchPaymentModal = false;
        $this->batchSelectedSaleIds = [];
        $this->batchReceivedAmount = 0;
        $this->batchPaymentReference = '';
        $this->batchPaymentNotes = '';
    }

    public function updatedBatchSelectAll($value)
    {
        if ($value) {
            $sales = $this->getDeliveredSettlementQuery($this->batchPaymentCourier)->pluck('id');
            $this->batchSelectedSaleIds = $sales->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->batchSelectedSaleIds = [];
        }
        $this->recalculateBatchReceivedAmount();
    }

    public function updatedBatchSelectedSaleIds()
    {
        $allCount = $this->getDeliveredSettlementQuery($this->batchPaymentCourier)->count();
        $this->batchSelectAll = (count($this->batchSelectedSaleIds) === $allCount) && ($allCount > 0);
        $this->recalculateBatchReceivedAmount();
    }

    public function setBatchReceivedFull()
    {
        $totalDue = $this->getDeliveredSettlementQuery($this->batchPaymentCourier)
            ->whereIn('id', $this->batchSelectedSaleIds)
            ->sum('due_amount');
        $this->batchReceivedAmount = (float) $totalDue;
    }

    public function setBatchReceivedZero()
    {
        $this->batchReceivedAmount = 0;
    }

    protected function recalculateBatchReceivedAmount()
    {
        $totalDue = (float) $this->getDeliveredSettlementQuery($this->batchPaymentCourier)
            ->whereIn('id', $this->batchSelectedSaleIds)
            ->sum('due_amount');
        if ((float) $this->batchReceivedAmount > $totalDue || (float) $this->batchReceivedAmount <= 0) {
            $this->batchReceivedAmount = $totalDue;
        }
    }

    public function getDeliveredSettlementQuery(string $courier)
    {
        $currentUser = Auth::user();
        $isDeliveryMan = $currentUser && ($currentUser->staff_type === 'delivery_man' || $currentUser->role === 'delivery_man');

        return Sale::query()
            ->where('delivery_status', 'delivered')
            ->where('due_amount', '>', 0)
            ->whereIn('order_source', ['whatsapp', 'ecommerce', 'delivery_man'])
            ->where('order_source', '!=', 'pos')
            ->when($courier !== 'all', function ($q) use ($courier) {
                $q->whereHas('deliverySale', fn ($dq) => $dq->where('delivery_method', $courier));
            })
            ->when($isDeliveryMan, function ($q) use ($currentUser) {
                $q->where(function ($sub) use ($currentUser) {
                    $sub->where('user_id', $currentUser->id)
                        ->orWhere('delivered_by', $currentUser->id)
                        ->orWhereNull('delivered_by')
                        ->orWhereIn('order_source', ['whatsapp', 'ecommerce']);
                });
            });
    }

    public function saveBatchPayment()
    {
        $this->validate([
            'batchPaymentDate' => 'required|date',
            'batchPaymentMethod' => 'required|string|in:cash,bank_transfer,cheque',
            'batchReceivedAmount' => 'required|numeric|min:0.01',
            'batchSelectedSaleIds' => 'required|array|min:1',
        ]);

        if (empty($this->batchSelectedSaleIds)) {
            $this->dispatch('show-toast', type: 'error', message: 'Please select at least one delivered order to settle.');

            return;
        }

        try {
            DB::beginTransaction();

            $sales = Sale::with(['deliverySale', 'customer'])
                ->whereIn('id', $this->batchSelectedSaleIds)
                ->where('due_amount', '>', 0)
                ->orderBy('created_at', 'asc') // Oldest delivered orders first (FIFO)
                ->get();

            if ($sales->isEmpty()) {
                $this->dispatch('show-toast', type: 'warning', message: 'No eligible delivered orders with outstanding due found.');
                DB::rollBack();

                return;
            }

            $remainingAmount = (float) $this->batchReceivedAmount;
            $totalAllocated = 0;
            $settledCount = 0;
            $fullyPaidCount = 0;

            foreach ($sales as $sale) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $due = (float) $sale->due_amount;
                $allocate = min($due, $remainingAmount);
                if ($allocate <= 0) {
                    continue;
                }

                Payment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'amount' => $allocate,
                    'payment_method' => $this->batchPaymentMethod,
                    'payment_reference' => $this->batchPaymentReference ?: "{$this->batchPaymentCourier} Remittance Batch",
                    'is_completed' => true,
                    'payment_date' => $this->batchPaymentDate,
                    'status' => 'paid',
                    'collected_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'collected_at' => now(),
                    'notes' => $this->batchPaymentNotes ?: "{$this->batchPaymentCourier} delivered order remittance settlement",
                ]);

                $newDue = max(0, $due - $allocate);
                $sale->due_amount = $newDue;
                if ($newDue <= 0) {
                    $sale->payment_status = 'paid';
                    $sale->delivery_status = 'completed'; // Fully paid and delivered -> Completed!
                    if ($sale->deliverySale) {
                        $sale->deliverySale->update(['status' => 'Completed']);
                    }
                    $fullyPaidCount++;
                } else {
                    $sale->payment_status = 'partial';
                }
                $sale->save();

                $remainingAmount -= $allocate;
                $totalAllocated += $allocate;
                $settledCount++;
            }

            DB::commit();

            $this->closeBatchPaymentModal();
            $this->dispatch('show-toast', type: 'success', message: 'Successfully received Rs. '.number_format($totalAllocated, 2)." across {$settledCount} orders ({$fullyPaidCount} fully completed). Total outstanding reduced!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error saving batch payment: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->dispatch('show-toast', type: 'error', message: 'Failed to record remittance: '.$e->getMessage());
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
        // Completely excludes shop staff POS counter walk-in sales.
        $baseQuery = Sale::query()
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

        // Filter by Courier Method at the base level so all KPI stats match the courier!
        if ($this->courierFilter && $this->courierFilter !== 'all') {
            $baseQuery->whereHas('deliverySale', function ($dq) {
                $dq->where('delivery_method', $this->courierFilter);
            });
        }

        // Active orders query (excluding completed)
        $activeBaseQuery = (clone $baseQuery)->where('delivery_status', '!=', 'completed');

        // 2. KPI Metrics (Courier-aware)
        $totalOrders = (clone $activeBaseQuery)->count();
        $totalOrdersAmount = (clone $activeBaseQuery)->sum('total_amount');
        $pendingPacking = (clone $activeBaseQuery)->where('delivery_status', 'pending')->count();
        $pendingPackingAmount = (clone $activeBaseQuery)->where('delivery_status', 'pending')->sum('total_amount');
        $packedCount = (clone $activeBaseQuery)->where('delivery_status', 'packed')->count();
        $packedAmount = (clone $activeBaseQuery)->where('delivery_status', 'packed')->sum('total_amount');
        $inTransitCount = (clone $activeBaseQuery)->where('delivery_status', 'in_transit')->count();
        $inTransitAmount = (clone $activeBaseQuery)->where('delivery_status', 'in_transit')->sum('total_amount');
        $inTransitCodValue = (clone $activeBaseQuery)->where('delivery_status', 'in_transit')->sum('due_amount');
        $deliveredCount = (clone $activeBaseQuery)->where('delivery_status', 'delivered')->count();
        $deliveredAmount = (clone $activeBaseQuery)->where('delivery_status', 'delivered')->sum('total_amount');
        $returnedCount = (clone $activeBaseQuery)->whereIn('delivery_status', ['returned', 'cancelled'])->count();
        $returnedAmount = (clone $activeBaseQuery)->whereIn('delivery_status', ['returned', 'cancelled'])->sum('total_amount');
        $todayOrders = (clone $activeBaseQuery)->whereDate('created_at', today())->count();

        // Completed stats
        $completedCount = (clone $baseQuery)->where('delivery_status', 'completed')->count();
        $completedAmount = (clone $baseQuery)->where('delivery_status', 'completed')->sum('total_amount');

        // Payment Orders stats (orders where due_amount > 0)
        $paymentDueCount = (clone $activeBaseQuery)->where('due_amount', '>', 0)->count();
        $paymentDueAmount = (clone $activeBaseQuery)->where('due_amount', '>', 0)->sum('due_amount');

        // Courier breakdowns (independent of current courier filter, for top pills/tabs)
        $courierBreakdownQuery = Sale::query()
            ->where('delivery_status', '!=', 'completed')
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
        $allActiveOrdersCount = (clone $courierBreakdownQuery)->count();
        $domexActiveCount = (clone $courierBreakdownQuery)->whereHas('deliverySale', fn ($q) => $q->where('delivery_method', 'Domex'))->count();
        $postActiveCount = (clone $courierBreakdownQuery)->whereHas('deliverySale', fn ($q) => $q->where('delivery_method', 'Post'))->count();
        $dhlActiveCount = (clone $courierBreakdownQuery)->whereHas('deliverySale', fn ($q) => $q->where('delivery_method', 'DHL'))->count();

        $whatsappOrdersCount = (clone $activeBaseQuery)->where('order_source', 'whatsapp')->count();
        $ecommerceOrdersCount = (clone $activeBaseQuery)->where('order_source', 'ecommerce')->count();
        $directDeliveryCount = (clone $activeBaseQuery)->where('order_source', 'delivery_man')->count();

        // COD collected metrics
        $todayCollectedCod = Payment::whereIn('sale_id', (clone $activeBaseQuery)->pluck('id'))
            ->whereDate('payment_date', today())
            ->sum('amount');

        $totalCollectedCod = Payment::whereIn('sale_id', (clone $activeBaseQuery)->pluck('id'))
            ->sum('amount');

        // 3. Main Query with Filters
        if ($this->statusFilter === 'completed') {
            $query = (clone $baseQuery)->where('delivery_status', 'completed');
        } elseif ($this->statusFilter === 'payment_due') {
            $query = (clone $activeBaseQuery)->where('due_amount', '>', 0);
        } elseif ($this->statusFilter === 'returned_cancelled') {
            $query = (clone $activeBaseQuery)->whereIn('delivery_status', ['returned', 'cancelled']);
        } elseif ($this->statusFilter && $this->statusFilter !== 'all') {
            $query = (clone $activeBaseQuery)->where('delivery_status', $this->statusFilter);
        } else {
            $query = clone $activeBaseQuery;
        }

        $query->with(['customer', 'deliverySale', 'domexOrderDetail', 'postOrderDetail', 'items.product', 'payments', 'user']);

        // Filter by Order Source
        if ($this->sourceFilter && $this->sourceFilter !== 'all') {
            $query->where('order_source', $this->sourceFilter);
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

        // Fetch products for Edit Order modal ONLY when user types in search box
        $editAvailableProducts = [];
        if ($this->showEditModal && ! empty(trim($this->editProductSearch))) {
            $pTerm = '%'.trim($this->editProductSearch).'%';
            $editAvailableProducts = ProductDetail::with(['price', 'stock'])
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

        // Delivered Orders with pending payment metrics (for the "Get Payment" action)
        $postDeliveredDueQuery = $this->getDeliveredSettlementQuery('Post');
        $postDeliveredDueCount = (clone $postDeliveredDueQuery)->count();
        $postDeliveredDueAmount = (float) (clone $postDeliveredDueQuery)->sum('due_amount');

        $activeCourierForSettlement = in_array($this->courierFilter, ['Post', 'Domex', 'DHL']) ? $this->courierFilter : 'Post';
        $courierDeliveredDueQuery = $this->getDeliveredSettlementQuery($activeCourierForSettlement);
        $courierDeliveredDueCount = (clone $courierDeliveredDueQuery)->count();
        $courierDeliveredDueAmount = (float) (clone $courierDeliveredDueQuery)->sum('due_amount');

        $batchDeliveredOrders = collect();
        if ($this->showBatchPaymentModal) {
            $batchDeliveredOrders = $this->getDeliveredSettlementQuery($this->batchPaymentCourier)
                ->with(['customer', 'deliverySale', 'items'])
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('livewire.delivery-man.delivery-man-dashboard', [
            'orders' => $orders,
            'availableProducts' => $availableProducts,
            'editAvailableProducts' => $editAvailableProducts,
            'allWhatsAppOrders' => $allWhatsAppOrders,
            'batchDeliveredOrders' => $batchDeliveredOrders,
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
                'completedCount' => $completedCount,
                'completedAmount' => $completedAmount,
                'paymentDueCount' => $paymentDueCount,
                'paymentDueAmount' => $paymentDueAmount,
                'allActiveOrdersCount' => $allActiveOrdersCount,
                'domexActiveCount' => $domexActiveCount,
                'postActiveCount' => $postActiveCount,
                'dhlActiveCount' => $dhlActiveCount,
                'postDeliveredDueCount' => $postDeliveredDueCount,
                'postDeliveredDueAmount' => $postDeliveredDueAmount,
                'courierDeliveredDueCount' => $courierDeliveredDueCount,
                'courierDeliveredDueAmount' => $courierDeliveredDueAmount,
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
            'showBatchPaymentModal' => $this->showBatchPaymentModal,
            'batchPaymentCourier' => $this->batchPaymentCourier,
            'batchPaymentDate' => $this->batchPaymentDate,
            'batchPaymentMethod' => $this->batchPaymentMethod,
            'batchPaymentReference' => $this->batchPaymentReference,
            'batchPaymentNotes' => $this->batchPaymentNotes,
            'batchReceivedAmount' => $this->batchReceivedAmount,
            'batchSelectedSaleIds' => $this->batchSelectedSaleIds,
            'batchSelectAll' => $this->batchSelectAll,
            'showDeleteModal' => $this->showDeleteModal,
            'showWhatsAppLinkModal' => $this->showWhatsAppLinkModal,
            'showWhatsAppManagerModal' => $this->showWhatsAppManagerModal,
            'showCreateSaleModal' => $this->showCreateSaleModal,
        ]);
    }
}
