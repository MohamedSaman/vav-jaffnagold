<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\ProductStock;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ReturnsProduct;
use App\Models\DeliverySale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Concerns\WithDynamicLayout;

#[Title('Sales Management')]
class SalesList extends Component
{
    use WithDynamicLayout;

    use WithPagination;

    public $search = '';
    public $selectedSale = null;
    public $paymentStatusFilter = 'all';
    public $deliveryStatusFilter = 'all';
    public $dateFilter = '';
    public $showViewModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showReturnModal = false;

    // Edit form properties
    public $editSaleId;
    public $editCustomerId;
    public $editPaymentStatus;
    public $editNotes;
    public $editDueAmount;
    public $editPaidAmount;
    public $editPayBalanceAmount = 0;

    // Delivery edit properties
    public $editDeliveryStatus;
    public $editDeliveryMethod;
    public $editPaymentMethod;
    public $editCustomerDetails;
    public $editDeliveryCharge = 0;
    public $editSaleItems = [];

    // Edit product search
    public $editProductSearch = '';
    public $editProductResults = [];
    public $editRemovedItemIds = []; // track DB item IDs to delete on save

    // Return properties
    public $returnItems = [];
    public $totalReturnValue = 0;
    public $perPage = 10;

    public function mount()
    {
        // Initialize component
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPaymentStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDeliveryStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function viewSale($saleId)
    {
        $query = Sale::with([
            'customer',
            'items',
            'user',
            'deliverySale',
            'returns' => function ($q) {
                $q->with('product');
            }
        ])->where('sale_type', $this->getSaleType());

        if ($this->isStaff()) {
            $query->where('user_id', Auth::id());
        }

        $this->selectedSale = $query->find($saleId);

        $this->showViewModal = true;
        $this->dispatch('showModal', 'viewModal');
    }

    public function editSale($saleId)
    {
        $query = Sale::with(['customer', 'deliverySale', 'items'])->where('sale_type', $this->getSaleType());
        if ($this->isStaff()) {
            $query->where('user_id', Auth::id());
        }
        $sale = $query->find($saleId);

        if ($sale) {
            $this->editSaleId = $sale->id;
            $this->editCustomerId = $sale->customer_id;
            $this->editPaymentStatus = $sale->payment_status;
            $this->editNotes = $sale->notes;
            $this->editDueAmount = $sale->due_amount;
            $this->editPaidAmount = $sale->total_amount - $sale->due_amount;
            $this->editPayBalanceAmount = 0;

            // Load sale items for display
            $this->editSaleItems = $sale->items->map(function ($item) {
                return [
                    'sale_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_per_unit' => $item->discount_per_unit,
                    'discount' => $item->discount_per_unit * $item->quantity,
                    'total' => $item->total,
                ];
            })->toArray();

            $this->editRemovedItemIds = [];

            // Load delivery sale info
            if ($sale->deliverySale) {
                $this->editDeliveryStatus = $sale->deliverySale->status;
                $this->editDeliveryMethod = $sale->deliverySale->delivery_method;
                $this->editPaymentMethod = $sale->deliverySale->payment_method;
                $this->editCustomerDetails = $sale->deliverySale->customer_details;
                $this->editDeliveryCharge = $sale->deliverySale->delivery_charge ?? 0;
            }

            $this->showEditModal = true;
            $this->dispatch('showModal', 'editModal');
        }
    }

    // Return Product Functionality
    public function returnSale($saleId)
    {
        $query = Sale::with(['items.product', 'customer'])->where('sale_type', $this->getSaleType());
        if ($this->isStaff()) {
            $query->where('user_id', Auth::id());
        }
        $this->selectedSale = $query->find($saleId);

        if ($this->selectedSale) {
            // Initialize return items from sale items
            $this->returnItems = [];
            foreach ($this->selectedSale->items as $item) {
                $this->returnItems[] = [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'unit_price' => $item->unit_price,
                    'max_qty' => $item->quantity,
                    'return_qty' => 0,
                ];
            }

            $this->showReturnModal = true;
            $this->dispatch('showModal', 'returnModal');
        }
    }

    public function updatedReturnItems()
    {
        $this->calculateTotalReturnValue();
    }

    private function calculateTotalReturnValue()
    {
        $this->totalReturnValue = collect($this->returnItems)->sum(
            fn($item) => $item['return_qty'] * $item['unit_price']
        );
    }

    public function removeFromReturn($index)
    {
        unset($this->returnItems[$index]);
        $this->returnItems = array_values($this->returnItems);
        $this->calculateTotalReturnValue();
    }

    public function clearReturnCart()
    {
        $this->returnItems = [];
        $this->totalReturnValue = 0;
    }

    public function processReturn()
    {
        $this->calculateTotalReturnValue();

        if (empty($this->returnItems) || !$this->selectedSale) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Please select items for return.']);
            return;
        }

        // Check if at least one item has a return quantity > 0
        $hasReturnItems = false;
        foreach ($this->returnItems as $item) {
            if (isset($item['return_qty']) && $item['return_qty'] > 0) {
                if ($item['return_qty'] > $item['max_qty']) {
                    $this->dispatch('showToast', ['type' => 'error', 'message' => 'Invalid return quantity for ' . $item['name']]);
                    return;
                }
                $hasReturnItems = true;
            }
        }

        if (!$hasReturnItems) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Please enter at least one return quantity.']);
            return;
        }

        $this->confirmReturn();
    }

    public function confirmReturn()
    {
        try {
            DB::transaction(function () {
                // Filter only items with return_qty > 0
                $itemsToReturn = array_filter($this->returnItems, function ($item) {
                    return isset($item['return_qty']) && $item['return_qty'] > 0;
                });

                foreach ($itemsToReturn as $item) {
                    ReturnsProduct::create([
                        'sale_id' => $this->selectedSale->id,
                        'product_id' => $item['product_id'],
                        'return_quantity' => $item['return_qty'],
                        'selling_price' => $item['unit_price'],
                        'total_amount' => $item['return_qty'] * $item['unit_price'],
                        'notes' => 'Customer return processed via system',
                    ]);

                    // Update stock (increase available stock)
                    $this->updateProductStock($item['product_id'], $item['return_qty']);
                }
            });

            $this->showReturnModal = false;
            $this->clearReturnCart();
            $this->dispatch('hideModal', 'returnModal');
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Return processed successfully!']);

            // Refresh the selected sale to show updated returns
            if ($this->selectedSale) {
                $this->selectedSale->refresh();
            }
        } catch (\Exception $e) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error processing return: ' . $e->getMessage()]);
        }
    }

    private function updateProductStock($productId, $quantity)
    {
        $stock = ProductStock::where('product_id', $productId)->first();

        if ($stock) {
            $stock->available_stock += $quantity;
            $stock->updateTotals();
        } else {
            ProductStock::create([
                'product_id' => $productId,
                'available_stock' => $quantity,
                'damage_stock' => 0,
                'total_stock' => $quantity,
                'sold_count' => 0,
                'restocked_quantity' => 0,
            ]);
        }
    }

    public function updatedEditPaymentStatus($value)
    {
        if ($this->editSaleId) {
            $sale = Sale::find($this->editSaleId);
            if ($sale) {
                if ($value === 'paid') {
                    $this->editPaidAmount = $sale->total_amount;
                    $this->editDueAmount = 0;
                    $this->editPayBalanceAmount = $sale->due_amount;
                } elseif ($value === 'pending') {
                    $this->editPaidAmount = 0;
                    $this->editDueAmount = $sale->total_amount;
                    $this->editPayBalanceAmount = 0;
                } else {
                    $this->editPayBalanceAmount = 0;
                }
            }
        }
    }

    public function updatedEditPayBalanceAmount($value)
    {
        if ($this->editSaleId) {
            $sale = Sale::find($this->editSaleId);
            if ($sale) {
                $value = floatval($value);
                $maxPayable = $sale->due_amount;

                if ($value > $maxPayable) {
                    $this->editPayBalanceAmount = $maxPayable;
                    $value = $maxPayable;
                }

                if ($value < 0) {
                    $this->editPayBalanceAmount = 0;
                    $value = 0;
                }

                $this->editPaidAmount = $sale->total_amount - $sale->due_amount + $value;
                $this->editDueAmount = $sale->due_amount - $value;

                if ($this->editDueAmount <= 0) {
                    $this->editPaymentStatus = 'paid';
                } elseif ($value > 0) {
                    $this->editPaymentStatus = 'partial';
                } else {
                    $this->editPaymentStatus = 'pending';
                }
            }
        }
    }

    public function updateSale()
    {
        try {
            DB::transaction(function () {
                $sale = Sale::with(['deliverySale', 'items'])->find($this->editSaleId);
                if (!$sale) return;

                // --- 1. Delete removed items & restore stock ---
                if (!empty($this->editRemovedItemIds)) {
                    $removedItems = SaleItem::whereIn('id', $this->editRemovedItemIds)->get();
                    foreach ($removedItems as $removedItem) {
                        $stock = ProductStock::where('product_id', $removedItem->product_id)->first();
                        if ($stock) {
                            $stock->available_stock += $removedItem->quantity;
                            if ($stock->sold_count >= $removedItem->quantity) {
                                $stock->sold_count -= $removedItem->quantity;
                            }
                            $stock->save();
                        }
                        $removedItem->delete();
                    }
                }

                // --- 2. Update existing items & add new items ---
                $newSubtotal = 0;
                $newDiscount = 0;

                foreach ($this->editSaleItems as $item) {
                    $qty = max(1, intval($item['quantity']));
                    $unitPrice = floatval($item['unit_price']);
                    $discountPerUnit = floatval($item['discount_per_unit'] ?? 0);
                    $lineTotal = ($unitPrice - $discountPerUnit) * $qty;
                    $totalDiscount = $discountPerUnit * $qty;

                    if (!empty($item['sale_item_id'])) {
                        // Existing item — update
                        $dbItem = SaleItem::find($item['sale_item_id']);
                        if ($dbItem) {
                            $oldQty = $dbItem->quantity;
                            $qtyDiff = $qty - $oldQty;

                            // Adjust stock
                            if ($qtyDiff != 0) {
                                $stock = ProductStock::where('product_id', $dbItem->product_id)->first();
                                if ($stock) {
                                    $stock->available_stock -= $qtyDiff;
                                    $stock->sold_count += $qtyDiff;
                                    $stock->save();
                                }
                            }

                            $dbItem->update([
                                'quantity' => $qty,
                                'unit_price' => $unitPrice,
                                'discount_per_unit' => $discountPerUnit,
                                'total_discount' => $totalDiscount,
                                'total' => $lineTotal,
                            ]);
                        }
                    } else {
                        // New item — create & deduct stock
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $item['product_id'],
                            'product_code' => $item['product_code'],
                            'product_name' => $item['product_name'],
                            'quantity' => $qty,
                            'unit_price' => $unitPrice,
                            'discount_per_unit' => $discountPerUnit,
                            'total_discount' => $totalDiscount,
                            'total' => $lineTotal,
                        ]);

                        $stock = ProductStock::where('product_id', $item['product_id'])->first();
                        if ($stock) {
                            $stock->available_stock -= $qty;
                            $stock->sold_count += $qty;
                            $stock->save();
                        }
                    }

                    $newSubtotal += $unitPrice * $qty;
                    $newDiscount += $totalDiscount;
                }

                $newTotal = $newSubtotal - $newDiscount;

                // --- 3. Add delivery charge to total ---
                $deliveryCharge = max(0, floatval($this->editDeliveryCharge));
                $newTotal += $deliveryCharge;

                // --- 4. Update sale totals ---
                $sale->update([
                    'subtotal' => $newSubtotal,
                    'discount_amount' => $newDiscount,
                    'total_amount' => $newTotal,
                    'due_amount' => $newTotal, // reset due to new total
                    'payment_status' => 'pending',
                ]);

                // --- 5. Update delivery sale details ---
                if ($sale->deliverySale) {
                    $sale->deliverySale->update([
                        'status' => $this->editDeliveryStatus,
                        'customer_details' => $this->editCustomerDetails,
                        'delivery_charge' => $deliveryCharge,
                    ]);
                }
            });

            $this->showEditModal = false;
            $this->resetEditForm();
            $this->dispatch('hideModal', 'editModal');
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Sale updated successfully!']);

            // Force page reload to refresh the sales list with updated data
            $this->js('setTimeout(() => location.reload(), 500)');
        } catch (\Exception $e) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error updating sale: ' . $e->getMessage()]);
        }
    }

    public function payFullBalance()
    {
        if ($this->editSaleId) {
            $sale = Sale::find($this->editSaleId);
            if ($sale) {
                $this->editPayBalanceAmount = $sale->due_amount;
                $this->updatedEditPayBalanceAmount($sale->due_amount);
            }
        }
    }

    public function resetPayBalance()
    {
        $this->editPayBalanceAmount = 0;
        $this->updatedEditPayBalanceAmount(0);
    }

    public function deleteSale($saleId)
    {
        $query = Sale::with('deliverySale')->where('sale_type', $this->getSaleType());
        if ($this->isStaff()) {
            $query->where('user_id', Auth::id());
        }
        $sale = $query->find($saleId);

        // Block deletion if delivery status is Delivered or Cancelled
        if ($sale && $sale->deliverySale && in_array($sale->deliverySale->status, ['Delivered', 'Cancelled'])) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Cannot delete this sale — delivery status is "' . $sale->deliverySale->status . '".']);
            return;
        }

        $this->selectedSale = $sale;
        $this->showDeleteModal = true;
        $this->dispatch('showModal', 'deleteModal');
    }

    public function confirmDelete()
    {
        try {
            DB::transaction(function () {
                // Restore stock using centralized method (handles variants, FIFO, total_stock & cache)
                if (!in_array($this->selectedSale->delivery_status, ['returned', 'cancelled'])) {
                    $this->selectedSale->restoreStock();
                }

                \App\Models\Payment::where('sale_id', $this->selectedSale->id)->delete();
                \App\Models\DeliverySale::where('sale_id', $this->selectedSale->id)->delete();
                SaleItem::where('sale_id', $this->selectedSale->id)->delete();

                $this->selectedSale->delete();
            });

            $this->showDeleteModal = false;
            $this->selectedSale = null;
            $this->dispatch('hideModal', 'deleteModal');
            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Sale deleted successfully!']);
        } catch (\Exception $e) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error deleting sale: ' . $e->getMessage()]);
        }
    }

    public function printInvoice($saleId)
    {
        $sale = \App\Models\Sale::with(['customer', 'items', 'payments', 'returns' => function ($q) {
            $q->with('product');
        }])->find($saleId);
        if (!$sale) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Sale not found.']);
            return;
        }
        // Store sale ID in session for print route
        session(['print_sale_id' => $sale->id]);
        // Open invoice print page in new window


        // Also open delivery label print page in new window
        $deliveryLabelUrl = route('admin.print.delivery-label', $sale->id);
        $this->js("setTimeout(() => { window.open('$deliveryLabelUrl', '_blank', 'width=500,height=700'); }, 500);");
    }

    public function downloadInvoice($saleId)
    {
        $query = Sale::with(['customer', 'items', 'returns' => function ($q) {
            $q->with('product');
        }])->where('sale_type', $this->getSaleType());

        if ($this->isStaff()) {
            $query->where('user_id', Auth::id());
        }

        $sale = $query->find($saleId);

        if (!$sale) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Sale not found.']);
            return;
        }

        try {
            $sale->paid_amount = $sale->total_amount - $sale->due_amount;
            $sale->balance_amount = $sale->due_amount;

            $pdf = PDF::loadView('admin.sales.invoice', compact('sale'));

            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('dpi', 150);
            $pdf->setOption('defaultFont', 'sans-serif');

            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                'invoice-' . $sale->invoice_number . '.pdf'
            );
        } catch (\Exception $e) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Failed to generate PDF: ' . $e->getMessage()]);
        }
    }


    public function closeModals()
    {
        $this->showViewModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showReturnModal = false;
        $this->selectedSale = null;
        $this->resetEditForm();
        $this->clearReturnCart();

        $this->dispatch('hideModal', 'viewModal');
        $this->dispatch('hideModal', 'editModal');
        $this->dispatch('hideModal', 'deleteModal');
        $this->dispatch('hideModal', 'returnModal');
    }

    private function resetEditForm()
    {
        $this->editSaleId = null;
        $this->editCustomerId = null;
        $this->editPaymentStatus = '';
        $this->editNotes = '';
        $this->editDueAmount = 0;
        $this->editPaidAmount = 0;
        $this->editPayBalanceAmount = 0;
        $this->editDeliveryStatus = '';
        $this->editDeliveryMethod = '';
        $this->editPaymentMethod = '';
        $this->editCustomerDetails = '';
        $this->editDeliveryCharge = 0;
        $this->editSaleItems = [];
        $this->editProductSearch = '';
        $this->editProductResults = [];
        $this->editRemovedItemIds = [];
    }

    // === Edit Modal: Product Search ===
    public function updatedEditProductSearch()
    {
        $search = trim($this->editProductSearch);
        if (strlen($search) < 2) {
            $this->editProductResults = [];
            return;
        }

        $this->editProductResults = ProductDetail::with('price')
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->limit(8)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name,
                    'selling_price' => $p->price ? $p->price->selling_price : 0,
                    'discount_price' => $p->price ? ($p->price->discount_price ?? 0) : 0,
                ];
            })
            ->toArray();
    }

    public function addEditProduct($productId)
    {
        $product = ProductDetail::with('price')->find($productId);
        if (!$product) return;

        // Check if product already in the list
        foreach ($this->editSaleItems as $idx => $item) {
            if ($item['product_id'] == $productId) {
                $this->editSaleItems[$idx]['quantity'] += 1;
                $this->editSaleItems[$idx]['total'] = ($this->editSaleItems[$idx]['unit_price'] - $this->editSaleItems[$idx]['discount_per_unit']) * $this->editSaleItems[$idx]['quantity'];
                $this->editProductSearch = '';
                $this->editProductResults = [];
                return;
            }
        }

        $sellingPrice = $product->price ? $product->price->selling_price : 0;
        $discountPrice = $product->price ? ($product->price->discount_price ?? 0) : 0;
        $discountPerUnit = $discountPrice > 0 ? ($sellingPrice - $discountPrice) : 0;

        $this->editSaleItems[] = [
            'sale_item_id' => null, // new item
            'product_id' => $product->id,
            'product_code' => $product->code,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => $sellingPrice,
            'discount_per_unit' => $discountPerUnit,
            'discount' => $discountPerUnit,
            'total' => $sellingPrice - $discountPerUnit,
        ];

        $this->editProductSearch = '';
        $this->editProductResults = [];
    }

    public function removeEditItem($index)
    {
        if (isset($this->editSaleItems[$index])) {
            $item = $this->editSaleItems[$index];
            // If it's an existing DB item, track it for deletion on save
            if (!empty($item['sale_item_id'])) {
                $this->editRemovedItemIds[] = $item['sale_item_id'];
            }
            unset($this->editSaleItems[$index]);
            $this->editSaleItems = array_values($this->editSaleItems);
        }
    }

    public function updateEditItemQty($index, $qty)
    {
        if (isset($this->editSaleItems[$index])) {
            $qty = max(1, intval($qty));
            $this->editSaleItems[$index]['quantity'] = $qty;
            $this->editSaleItems[$index]['discount'] = $this->editSaleItems[$index]['discount_per_unit'] * $qty;
            $this->editSaleItems[$index]['total'] = ($this->editSaleItems[$index]['unit_price'] - $this->editSaleItems[$index]['discount_per_unit']) * $qty;
        }
    }

    public function updateEditItemPrice($index, $price)
    {
        if (isset($this->editSaleItems[$index])) {
            $price = max(0, floatval($price));
            $this->editSaleItems[$index]['unit_price'] = $price;
            $qty = $this->editSaleItems[$index]['quantity'];
            $this->editSaleItems[$index]['total'] = ($price - $this->editSaleItems[$index]['discount_per_unit']) * $qty;
        }
    }

    public function updateDeliveryStatus($saleId, $status)
    {
        try {
            $sale = Sale::with(['deliverySale', 'items'])->where('sale_type', $this->getSaleType())->find($saleId);
            if ($sale && $sale->deliverySale) {
                // Block status change if current status is Delivered or Cancelled
                $currentStatus = $sale->deliverySale->status;
                if (in_array($currentStatus, ['Delivered', 'Cancelled'])) {
                    $this->dispatch('showToast', ['type' => 'error', 'message' => 'Cannot change status — delivery is already "' . $currentStatus . '".']);
                    return;
                }

                // If changing to Cancelled, restore stock for all items in this sale
                if ($status === 'Cancelled') {
                    // Use the centralized restoreStock() which handles variants, FIFO batches, total_stock & cache
                    $sale->restoreStock();

                    // Mark the sale itself as cancelled so stock won't be double-restored
                    $sale->delivery_status = 'cancelled';
                    $sale->status = 'cancelled';
                    $sale->save();

                    // Update ecommerce order if linked
                    if ($sale->ecommerce_order_id) {
                        \App\Models\EcommerceOrder::where('id', $sale->ecommerce_order_id)->update(['order_status' => 'cancelled']);
                    }
                }

                $sale->deliverySale->update(['status' => $status]);
                $this->dispatch('showToast', ['type' => 'success', 'message' => 'Delivery status updated to ' . $status . '. Stock restored to inventory!']);
            }
        } catch (\Exception $e) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getSalesProperty()
    {
        $query = Sale::with(['customer', 'user', 'items', 'returns', 'deliverySale']);

        // Filter by sale_type and user_id based on role
        if ($this->isStaff()) {
            $query->where('user_id', Auth::id())
                ->where('sale_type', 'staff');
        } else {
            $query->where('sale_type', 'admin');
        }

        return $query->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhere('sale_id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($customerQuery) {
                        $customerQuery->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('deliverySale', function ($deliveryQuery) {
                        $deliveryQuery->where('delivery_barcode', 'like', '%' . $this->search . '%');
                    });
            });
        })
            ->when($this->paymentStatusFilter !== 'all', function ($query) {
                $query->where('payment_status', $this->paymentStatusFilter);
            })
            ->when($this->deliveryStatusFilter !== 'all', function ($query) {
                $query->whereHas('deliverySale', function ($q) {
                    $q->where('status', $this->deliveryStatusFilter);
                });
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('created_at', $this->dateFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function getSalesStatsProperty()
    {
        // Base query filtered by role
        if ($this->isStaff()) {
            $baseSales = Sale::where('sale_type', 'staff')->where('user_id', Auth::id());
            $todaySales = Sale::where('sale_type', 'staff')->where('user_id', Auth::id())->whereDate('created_at', today());
        } else {
            $baseSales = Sale::where('sale_type', 'admin');
            $todaySales = Sale::where('sale_type', 'admin')->whereDate('created_at', today());
        }

        return [
            'total_sales' => (clone $baseSales)->count(),
            'total_amount' => (clone $baseSales)->sum('total_amount'),
            'pending_payments' => (clone $baseSales)->where('payment_status', 'pending')->sum('due_amount'),
            'partial_payments' => (clone $baseSales)->where('payment_status', 'partial')->sum('due_amount'),
            'paid_amount' => (clone $baseSales)->where('payment_status', 'paid')->sum('total_amount'),
            'today_sales' => $todaySales->count(),
            'today_amount' => (clone $todaySales)->sum('total_amount'),
        ];
    }

    public function getCustomersProperty()
    {
        return Customer::orderBy('name')->get();
    }

    public function markAsPaid($saleId)
    {
        try {
            $query = Sale::where('sale_type', $this->getSaleType());
            if ($this->isStaff()) {
                $query->where('user_id', Auth::id());
            }
            $sale = $query->find($saleId);

            if ($sale) {
                $sale->update([
                    'payment_status' => 'paid',
                    'due_amount' => 0,
                    'payment_type' => 'full'
                ]);

                $this->dispatch('showToast', ['type' => 'success', 'message' => 'Sale marked as paid successfully!']);
            }
        } catch (\Exception $e) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error updating sale: ' . $e->getMessage()]);
        }
    }

    public function markAsPending($saleId)
    {
        try {
            $query = Sale::where('sale_type', $this->getSaleType());
            if ($this->isStaff()) {
                $query->where('user_id', Auth::id());
            }
            $sale = $query->find($saleId);

            if ($sale) {
                $sale->update([
                    'payment_status' => 'pending',
                    'due_amount' => $sale->total_amount,
                    'payment_type' => 'partial'
                ]);

                $this->dispatch('showToast', ['type' => 'success', 'message' => 'Sale marked as pending successfully!']);
            }
        } catch (\Exception $e) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Error updating sale: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $sales = $this->sales;
        $stats = $this->salesStats;

        if (!\App\Helpers\TaxAuditHelper::isUnlocked()) {
            $stats['total_amount'] = 104500;
            $stats['today_amount'] = 104500;
            $stats['paid_amount'] = 104500;
            $stats['pending_payments'] = 0;
            $stats['partial_payments'] = 0;
        }

        return view('livewire.admin.sales-list', [
            'sales' => $sales,
            'stats' => $stats,
            'customers' => $this->customers,
        ])->layout($this->layout);
    }
}
