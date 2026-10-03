<?php

namespace App\Livewire\ShopStaff;

use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\GoldPlatingOrder;
use App\Models\GoldPlatingWorker;
use App\Models\HandmadeItem;
use App\Models\HandmadePurchase;
use App\Models\HandmadeSupplier;
use App\Models\HandmadeSupplierPayment;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Title('Local Handmade Purchasing & Workshop')]
#[Layout('components.layouts.shop-staff')]
class HandmadePurchasing extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Toolbar Filters
    public $search = '';
    public $typeFilter = 'all'; // all, raw_product, raw_with_stone, final_sale_plated
    public $statusFilter = 'all'; // all, raw_in_stock, stone_setting, plating, ready_for_stock, in_pos_stock
    public $supplierFilter = 'all';

    // Stock Added Success Print Modal
    public $showStockSuccessModal = false;
    public $stockSuccessData = null;

    // Purchase Intake Modal State (Multi-Item Batch)
    public $showPurchaseModal = false;
    public $purchase_supplier_id = null;
    public $purchase_date = null;
    public $purchase_items = []; // array of items with name, category, type, qty, weight, cost, selling_price, skip_stone
    public $paid_amount = 0;
    public $payment_method = 'cash';
    public $purchase_notes = '';

    // Supplier Management Modal State
    public $showSupplierModal = false;
    public $manageSupplierId = null;
    public $supplier_name = '';
    public $supplier_phone = '';
    public $supplier_address = '';
    public $supplier_opening_balance = 0;
    public $supplier_notes = '';

    // Supplier Payout Modal State
    public $showSupplierPaymentModal = false;
    public $payoutSupplierId = null;
    public $payoutSupplier = null;
    public $payoutAmount = null;
    public $payoutMethod = 'cash';
    public $payoutDate = null;
    public $payoutNotes = '';

    // Supplier Ledger Modal State
    public $showSupplierLedgerModal = false;
    public $ledgerSupplier = null;

    // Stage 1: Stone Setting Modal
    public $showStoneSettingModal = false;
    public $stoneItemId = null;
    public $stoneItem = null;
    public $stone_cost = null;
    public $stone_worker_notes = '';

    // Stage 2: Plating Workshop Modal
    public $showPlatingModal = false;
    public $platingItemId = null;
    public $platingItem = null;
    public $plating_worker_id = null;
    public $plating_cost = null;
    public $plating_notes = '';

    // Stage 3: Finish & Add to POS Stock Modal
    public $showAddToStockModal = false;
    public $stockItemId = null;
    public $stockItem = null;
    public $final_selling_price = null;

    // Delete Item State
    public $showDeleteModal = false;
    public $deletingItemId = null;
    public $deletingItemCode = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSupplierFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->resetPurchaseForm();
    }

    /* ----------------------------------------------------
       PURCHASE INTAKE MODAL (MULTI-ITEM BATCH)
    ---------------------------------------------------- */
    public function openPurchaseModal()
    {
        $this->resetPurchaseForm();
        $this->purchase_date = now()->format('Y-m-d');
        $this->showPurchaseModal = true;
    }

    public function closePurchaseModal()
    {
        $this->showPurchaseModal = false;
        $this->resetPurchaseForm();
    }

    public function resetPurchaseForm()
    {
        $this->purchase_supplier_id = null;
        $this->purchase_date = now()->format('Y-m-d');
        $this->purchase_items = [
            [
                'item_name' => '',
                'category_id' => '',
                'product_type' => 'raw_product',
                'skip_stone_setting' => false,
                'quantity' => 1,
                'weight' => '',
                'purchase_cost' => '',
                'selling_price' => '',
            ]
        ];
        $this->paid_amount = 0;
        $this->payment_method = 'cash';
        $this->purchase_notes = '';
        $this->resetValidation();
    }

    public function addPurchaseItem()
    {
        $this->purchase_items[] = [
            'item_name' => '',
            'category_id' => '',
            'product_type' => 'raw_product',
            'skip_stone_setting' => false,
            'quantity' => 1,
            'weight' => '',
            'purchase_cost' => '',
            'selling_price' => '',
        ];
    }

    public function removePurchaseItem($index)
    {
        if (count($this->purchase_items) > 1) {
            unset($this->purchase_items[$index]);
            $this->purchase_items = array_values($this->purchase_items);
        }
    }

    public function getTotalPurchaseAmountProperty()
    {
        $total = 0;
        foreach ($this->purchase_items as $it) {
            $qty = intval($it['quantity'] ?? 1);
            $cost = floatval($it['purchase_cost'] ?? 0);
            $total += ($qty * $cost);
        }
        return $total;
    }

    public function savePurchase()
    {
        $this->validate([
            'purchase_supplier_id' => 'required|exists:handmade_suppliers,id',
            'purchase_date' => 'required|date',
            'purchase_items' => 'required|array|min:1',
            'purchase_items.*.item_name' => 'required|string|max:255',
            'purchase_items.*.category_id' => 'nullable|exists:category_lists,id',
            'purchase_items.*.product_type' => 'required|in:raw_product,raw_with_stone,final_sale_plated',
            'purchase_items.*.quantity' => 'required|integer|min:1',
            'purchase_items.*.weight' => 'nullable|numeric|min:0',
            'purchase_items.*.purchase_cost' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,credit',
            'purchase_notes' => 'nullable|string|max:1000',
        ], [
            'purchase_items.*.item_name.required' => 'Item description is required for all items.',
            'purchase_items.*.purchase_cost.required' => 'Purchase cost is required for all items.',
            'purchase_items.*.quantity.required' => 'Quantity must be at least 1.',
        ]);

        // Validate POS Selling Price for final_sale_plated items
        foreach ($this->purchase_items as $index => $it) {
            if (($it['product_type'] ?? '') === 'final_sale_plated') {
                if (empty($it['selling_price']) || floatval($it['selling_price']) <= 0) {
                    $this->addError("purchase_items.{$index}.selling_price", "POS Selling Price is required for Final Plated Sale items.");
                    return;
                }
            }
        }

        try {
            DB::beginTransaction();

            $totalCost = $this->totalPurchaseAmount;
            $paid = (float) ($this->paid_amount ?? 0);
            $due = max(0, $totalCost - $paid);

            // 1. Create Purchase Invoice Batch
            $purchase = HandmadePurchase::create([
                'purchase_code' => HandmadePurchase::generatePurchaseCode(),
                'supplier_id' => $this->purchase_supplier_id,
                'user_id' => Auth::id(),
                'purchase_date' => $this->purchase_date,
                'total_amount' => $totalCost,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_method' => $this->payment_method,
                'notes' => $this->purchase_notes ? trim($this->purchase_notes) : null,
            ]);

            // 2. Record payment to supplier if paid > 0
            if ($paid > 0) {
                HandmadeSupplierPayment::create([
                    'supplier_id' => $this->purchase_supplier_id,
                    'purchase_id' => $purchase->id,
                    'user_id' => Auth::id(),
                    'amount' => $paid,
                    'payment_method' => $this->payment_method === 'credit' ? 'cash' : $this->payment_method,
                    'payment_date' => $this->purchase_date,
                    'notes' => 'Payment recorded during purchase batch ' . $purchase->purchase_code,
                ]);
            }

            $createdCount = 0;
            $finalSaleCount = 0;
            $generatedCodes = [];

            // 3. Create each handmade item
            foreach ($this->purchase_items as $it) {
                $itemType = $it['product_type'];
                $itemName = trim($it['item_name']);
                $qty = intval($it['quantity'] ?? 1);
                $unitCost = floatval($it['purchase_cost']);
                $itemTotalCost = $unitCost * $qty;
                $weight = !empty($it['weight']) ? floatval($it['weight']) : null;
                $categoryId = !empty($it['category_id']) ? $it['category_id'] : null;
                $skipStone = !empty($it['skip_stone_setting']);
                $sellingPrice = !empty($it['selling_price']) ? floatval($it['selling_price']) : null;

                $status = 'raw_in_stock';
                $productDetailId = null;
                $completedAt = null;
                $stoneNotes = null;

                // Generate item code in format SL-<PRODUCT_NAME>-001 (e.g. SL-KODI-001)
                $itemCode = HandmadeItem::generateItemCode($itemName, $generatedCodes);
                $generatedCodes[] = $itemCode;

                // If directly final plated sale product: create sellable POS product directly!
                if ($itemType === 'final_sale_plated') {
                    $status = 'in_pos_stock';
                    $completedAt = now();

                    // Create POS Product
                    $posProduct = $this->createPosProductRecord(
                        $itemName,
                        $categoryId,
                        $unitCost,
                        $sellingPrice,
                        $qty,
                        $itemCode
                    );
                    $productDetailId = $posProduct->id;
                    $finalSaleCount += $qty;
                } elseif ($itemType === 'raw_product' && $skipStone) {
                    // Plain raw jewelry - skipped stone works directly to plating stage
                    $status = 'stone_setting';
                    $stoneNotes = 'Plain piece - No stone works needed';
                } elseif ($itemType === 'raw_with_stone') {
                    // Has stones already, ready for plating
                    $status = 'stone_setting';
                }

                HandmadeItem::create([
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $this->purchase_supplier_id,
                    'category_id' => $categoryId,
                    'item_code' => $itemCode,
                    'item_name' => $itemName,
                    'product_type' => $itemType,
                    'status' => $status,
                    'quantity' => $qty,
                    'weight' => $weight,
                    'purchase_cost' => $unitCost,
                    'stone_cost' => 0.00,
                    'stone_worker_notes' => $stoneNotes,
                    'plating_cost' => 0.00,
                    'total_accumulated_cost' => $unitCost,
                    'selling_price' => $sellingPrice,
                    'product_detail_id' => $productDetailId,
                    'completed_at' => $completedAt,
                    'notes' => $this->purchase_notes ? trim($this->purchase_notes) : null,
                ]);
                $createdCount++;
            }

            DB::commit();

            $this->closePurchaseModal();

            $msg = "Purchase batch {$purchase->purchase_code} with {$createdCount} products (Total: Rs. " . number_format($totalCost, 2) . ") registered successfully!";
            if ($finalSaleCount > 0) {
                $msg .= " ({$finalSaleCount} items added directly to POS sellable stock).";
            }
            session()->flash('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Failed to record purchase batch: " . $e->getMessage());
        }
    }

    /* ----------------------------------------------------
       STAGE 1: STONE SETTING WORK
    ---------------------------------------------------- */
    public function openStoneSettingModal($itemId)
    {
        $this->stoneItem = HandmadeItem::findOrFail($itemId);
        $this->stoneItemId = $itemId;
        $this->stone_cost = $this->stoneItem->stone_cost > 0 ? $this->stoneItem->stone_cost : null;
        $this->stone_worker_notes = $this->stoneItem->stone_worker_notes ?: '';
        $this->showStoneSettingModal = true;
    }

    public function closeStoneSettingModal()
    {
        $this->showStoneSettingModal = false;
        $this->stoneItemId = null;
        $this->stoneItem = null;
        $this->resetValidation();
    }

    public function saveStoneSetting()
    {
        $this->validate([
            'stone_cost' => 'required|numeric|min:0',
            'stone_worker_notes' => 'nullable|string|max:500',
        ]);

        $item = HandmadeItem::findOrFail($this->stoneItemId);
        $item->stone_cost = (float) $this->stone_cost;
        $item->stone_worker_notes = $this->stone_worker_notes ? trim($this->stone_worker_notes) : null;
        $item->status = 'stone_setting';
        $item->recalculateTotalCost();

        $this->closeStoneSettingModal();
        session()->flash('success', "Stone setting cost of Rs. " . number_format($this->stone_cost, 2) . " updated for {$item->item_code}!");
    }

    public function skipStoneSetting($itemId = null)
    {
        $id = $itemId ?: $this->stoneItemId;
        if (!$id) return;

        $item = HandmadeItem::findOrFail($id);
        $item->stone_cost = 0.00;
        $item->stone_worker_notes = 'No stone works needed (plain piece)';
        $item->status = 'stone_setting'; // Moves to ready for plating
        $item->recalculateTotalCost();

        if ($this->showStoneSettingModal) {
            $this->closeStoneSettingModal();
        }

        session()->flash('info', "Stone work skipped for {$item->item_code} ({$item->item_name}). It is now ready to send to Plating!");
    }

    /* ----------------------------------------------------
       STAGE 2: PLATING WORKSHOP
    ---------------------------------------------------- */
    public function openPlatingModal($itemId)
    {
        $this->platingItem = HandmadeItem::findOrFail($itemId);
        $this->platingItemId = $itemId;
        $this->plating_worker_id = $this->platingItem->plating_worker_id;
        $this->plating_cost = $this->platingItem->plating_cost > 0 ? $this->platingItem->plating_cost : null;
        $this->plating_notes = '';
        $this->showPlatingModal = true;
    }

    public function closePlatingModal()
    {
        $this->showPlatingModal = false;
        $this->platingItemId = null;
        $this->platingItem = null;
        $this->resetValidation();
    }

    public function savePlating()
    {
        $this->validate([
            'plating_worker_id' => 'nullable|exists:gold_plating_workers,id',
            'plating_cost' => 'required|numeric|min:0',
            'plating_notes' => 'nullable|string|max:500',
        ]);

        $item = HandmadeItem::findOrFail($this->platingItemId);
        $item->plating_worker_id = $this->plating_worker_id ?: null;
        $item->plating_cost = (float) $this->plating_cost;
        $item->status = 'ready_for_stock';
        $item->recalculateTotalCost();

        // Create or update GoldPlatingOrder for workshop tracking
        $orderStatus = in_array($item->status, ['in_pos_stock', 'completed']) ? 'delivered' : 'ready';
        GoldPlatingOrder::updateOrCreate(
            ['handmade_item_id' => $item->id],
            [
                'order_code' => 'GP-' . $item->item_code,
                'user_id' => Auth::id(),
                'worker_id' => $this->plating_worker_id ?: null,
                'order_category' => 'handmade',
                'customer_name' => 'Shop Stock (Handmade)',
                'customer_phone' => $item->supplier ? $item->supplier->name : 'Handmade Inventory',
                'customer_address' => 'Shop Inventory',
                'service_type' => 'gold_plating',
                'item_description' => $item->item_name . ($item->weight ? " ({$item->weight}g)" : ''),
                'item_weight' => $item->weight,
                'is_charge_tbd' => false,
                'our_cost' => (float) $this->plating_cost,
                'customer_charge' => 0.00,
                'advance_amount' => 0.00,
                'balance_amount' => 0.00,
                'status' => $orderStatus,
                'received_date' => now()->toDateString(),
                'expected_delivery_date' => now()->toDateString(),
                'notes' => 'Handmade item workshop plating. ' . ($this->plating_notes ?: ''),
            ]
        );

        $this->closePlatingModal();
        session()->flash('success', "Plating cost of Rs. " . number_format($this->plating_cost, 2) . " saved and assigned to Gold Plating workshop!");
    }

    /* ----------------------------------------------------
       STAGE 3: FINISH & CONVERT TO POS SELLABLE STOCK
    ---------------------------------------------------- */
    public function openAddToStockModal($itemId)
    {
        $this->stockItem = HandmadeItem::with(['supplier', 'platingWorker'])->findOrFail($itemId);
        $this->stockItemId = $itemId;

        // Suggest selling price with default markup (e.g. 1.5x - 2x)
        $cost = (float) $this->stockItem->total_accumulated_cost;
        $this->final_selling_price = $this->stockItem->selling_price ?: round($cost * 1.5, 0);

        $this->showAddToStockModal = true;
    }

    public function closeAddToStockModal()
    {
        $this->showAddToStockModal = false;
        $this->stockItemId = null;
        $this->stockItem = null;
        $this->resetValidation();
    }

    public function confirmAddToStock()
    {
        $this->validate([
            'final_selling_price' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            $item = HandmadeItem::findOrFail($this->stockItemId);

            // Create POS Product
            $posProduct = $this->createPosProductRecord(
                $item->item_name,
                $item->category_id,
                (float) $item->total_accumulated_cost,
                (float) $this->final_selling_price,
                intval($item->quantity),
                $item->item_code
            );

            $item->update([
                'status' => 'in_pos_stock',
                'product_detail_id' => $posProduct->id,
                'selling_price' => (float) $this->final_selling_price,
                'completed_at' => now(),
            ]);

            // Update associated GoldPlatingOrder to delivered
            GoldPlatingOrder::where('handmade_item_id', $item->id)->update([
                'status' => 'delivered',
                'actual_delivery_date' => now(),
            ]);

            DB::commit();

            $this->closeAddToStockModal();
            $this->stockSuccessData = [
                'barcode' => $posProduct->barcode,
                'code' => $posProduct->code,
                'name' => $item->item_name,
                'price' => number_format((float) $this->final_selling_price, 2, '.', ''),
                'quantity' => intval($item->quantity),
            ];
            $this->showStockSuccessModal = true;
            session()->flash('success', "Handmade item {$item->item_code} ({$item->item_name}) is now live in POS Store Billing stock! Barcode: {$posProduct->barcode}");

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Failed to add item to POS stock: " . $e->getMessage());
        }
    }

    public function closeStockSuccessModal()
    {
        $this->showStockSuccessModal = false;
        $this->stockSuccessData = null;
    }

    /**
     * Helper to create ProductDetail, ProductPrice, ProductStock in POS system
     */
    private function createPosProductRecord($name, $categoryId, $cost, $sellingPrice, $qty, $itemCode = null): ProductDetail
    {
        if ($itemCode) {
            $code = $itemCode;
            if (ProductDetail::where('code', $code)->exists()) {
                $code = $itemCode . '-' . strtoupper(Str::random(3));
            }
            $barcode = $itemCode;
            if (ProductDetail::where('barcode', $barcode)->exists()) {
                $barcode = $itemCode . '-' . strtoupper(Str::random(3));
            }
        } else {
            $barcode = 'HM-' . strtoupper(Str::random(6));
            $code = 'PROD-' . strtoupper(Str::random(6));
        }

        $defaultBrandId = BrandList::first()->id ?? 1;
        $defaultCategoryId = CategoryList::first()->id ?? 1;

        // Create ProductDetail
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
            'origin' => 'local',
            'website_live' => false,
        ]);

        // Create ProductPrice
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

        // Create ProductStock
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
       DELETE ITEM
    ---------------------------------------------------- */
    public function confirmDeleteItem($id)
    {
        $item = HandmadeItem::findOrFail($id);
        $this->deletingItemId = $id;
        $this->deletingItemCode = $item->item_code;
        $this->showDeleteModal = true;
    }

    public function cancelDeleteItem()
    {
        $this->showDeleteModal = false;
        $this->deletingItemId = null;
        $this->deletingItemCode = '';
    }

    public function deleteItem()
    {
        if ($this->deletingItemId) {
            $item = HandmadeItem::findOrFail($this->deletingItemId);
            $code = $item->item_code;
            GoldPlatingOrder::where('handmade_item_id', $item->id)->delete();
            $item->delete();
            $this->cancelDeleteItem();
            session()->flash('success', "Handmade item {$code} deleted successfully.");
        }
    }

    /* ----------------------------------------------------
       LOCAL SUPPLIER MANAGEMENT
    ---------------------------------------------------- */
    public function openCreateSupplierModal()
    {
        $this->manageSupplierId = null;
        $this->supplier_name = '';
        $this->supplier_phone = '';
        $this->supplier_address = '';
        $this->supplier_opening_balance = 0;
        $this->supplier_notes = '';
        $this->showSupplierModal = true;
    }

    public function openEditSupplierModal($supplierId)
    {
        $supplier = HandmadeSupplier::findOrFail($supplierId);
        $this->manageSupplierId = $supplierId;
        $this->supplier_name = $supplier->name;
        $this->supplier_phone = $supplier->phone;
        $this->supplier_address = $supplier->address;
        $this->supplier_opening_balance = $supplier->opening_payable_balance;
        $this->supplier_notes = $supplier->notes;
        $this->showSupplierModal = true;
    }

    public function closeSupplierModal()
    {
        $this->showSupplierModal = false;
        $this->manageSupplierId = null;
        $this->resetValidation();
    }

    public function saveSupplier()
    {
        $this->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:50',
            'supplier_address' => 'nullable|string|max:255',
            'supplier_opening_balance' => 'required|numeric|min:0',
            'supplier_notes' => 'nullable|string|max:500',
        ]);

        if ($this->manageSupplierId) {
            $s = HandmadeSupplier::findOrFail($this->manageSupplierId);
            $s->update([
                'name' => trim($this->supplier_name),
                'phone' => $this->supplier_phone ? trim($this->supplier_phone) : null,
                'address' => $this->supplier_address ? trim($this->supplier_address) : null,
                'opening_payable_balance' => (float) $this->supplier_opening_balance,
                'notes' => $this->supplier_notes ? trim($this->supplier_notes) : null,
            ]);
            $msg = "Local supplier {$s->name} details updated successfully!";
        } else {
            $s = HandmadeSupplier::create([
                'name' => trim($this->supplier_name),
                'phone' => $this->supplier_phone ? trim($this->supplier_phone) : null,
                'address' => $this->supplier_address ? trim($this->supplier_address) : null,
                'opening_payable_balance' => (float) $this->supplier_opening_balance,
                'is_active' => true,
                'notes' => $this->supplier_notes ? trim($this->supplier_notes) : null,
            ]);
            $msg = "New local supplier {$s->name} registered successfully!";
        }

        $this->closeSupplierModal();
        session()->flash('success', $msg);
    }

    /* ----------------------------------------------------
       LOCAL SUPPLIER PAYOUT
    ---------------------------------------------------- */
    public function openSupplierPaymentModal($supplierId)
    {
        $this->payoutSupplier = HandmadeSupplier::findOrFail($supplierId);
        $this->payoutSupplierId = $supplierId;
        $this->payoutAmount = max(0, $this->payoutSupplier->outstanding_balance);
        $this->payoutMethod = 'cash';
        $this->payoutDate = now()->format('Y-m-d');
        $this->payoutNotes = '';
        $this->showSupplierPaymentModal = true;
    }

    public function closeSupplierPaymentModal()
    {
        $this->showSupplierPaymentModal = false;
        $this->payoutSupplierId = null;
        $this->payoutSupplier = null;
        $this->resetValidation();
    }

    public function saveSupplierPayment()
    {
        $this->validate([
            'payoutAmount' => 'required|numeric|min:1',
            'payoutMethod' => 'required|in:cash,bank_transfer,online',
            'payoutDate' => 'required|date',
            'payoutNotes' => 'nullable|string|max:500',
        ]);

        $supplier = HandmadeSupplier::findOrFail($this->payoutSupplierId);

        HandmadeSupplierPayment::create([
            'supplier_id' => $supplier->id,
            'user_id' => Auth::id(),
            'amount' => (float) $this->payoutAmount,
            'payment_method' => $this->payoutMethod,
            'payment_date' => $this->payoutDate,
            'notes' => $this->payoutNotes ? trim($this->payoutNotes) : "Payment given to local supplier {$supplier->name}",
        ]);

        $this->closeSupplierPaymentModal();
        session()->flash('success', "Payment of Rs. " . number_format($this->payoutAmount, 2) . " to {$supplier->name} recorded successfully!");
    }

    /* ----------------------------------------------------
       LOCAL SUPPLIER LEDGER
    ---------------------------------------------------- */
    public function openSupplierLedgerModal($supplierId)
    {
        $this->ledgerSupplier = HandmadeSupplier::with([
            'purchases' => function ($q) {
                $q->orderBy('purchase_date', 'desc')->orderBy('id', 'desc');
            },
            'payments' => function ($q) {
                $q->orderBy('payment_date', 'desc')->orderBy('id', 'desc');
            },
        ])->findOrFail($supplierId);

        $this->showSupplierLedgerModal = true;
    }

    public function closeSupplierLedgerModal()
    {
        $this->showSupplierLedgerModal = false;
        $this->ledgerSupplier = null;
    }

    /* ----------------------------------------------------
       RENDER
    ---------------------------------------------------- */
    public function render()
    {
        $query = HandmadeItem::with(['supplier', 'category', 'platingWorker', 'productDetail']);

        if (!empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($this->typeFilter !== 'all') {
            $query->where('product_type', $this->typeFilter);
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->supplierFilter !== 'all') {
            $query->where('supplier_id', $this->supplierFilter);
        }

        $suppliers = HandmadeSupplier::where('is_active', true)->with(['purchases', 'payments'])->get();
        $workers = GoldPlatingWorker::where('is_active', true)->get();
        $categories = CategoryList::orderBy('category_name')->get();

        // Overall stats
        $allItems = HandmadeItem::all();
        $stats = [
            'total_items' => $allItems->count(),
            'raw_in_stock' => $allItems->where('status', 'raw_in_stock')->count(),
            'stone_setting' => $allItems->where('status', 'stone_setting')->count(),
            'plating' => $allItems->where('status', 'plating')->count(),
            'ready_for_stock' => $allItems->where('status', 'ready_for_stock')->count(),
            'in_pos_stock' => $allItems->where('status', 'in_pos_stock')->count(),
            'total_spent' => $allItems->sum('total_accumulated_cost'),
            'total_supplier_payable' => $suppliers->sum(function ($s) {
                return $s->outstanding_balance;
            }),
        ];

        $items = $query->latest()->paginate(15);

        return view('livewire.shop-staff.handmade-purchasing', [
            'items' => $items,
            'suppliers' => $suppliers,
            'workers' => $workers,
            'categories' => $categories,
            'stats' => $stats,
            'labelSettings' => $this->getLabelSettings(),
        ]);
    }

    public function getLabelSettings(): array
    {
        $defaults = [
            'label_width'           => 48,
            'label_height'          => 12,
            'label_padding'         => 1,
            'label_text_width'      => 35,
            'label_tail_width'      => 22,
            'label_tail_height'     => 4,
            'label_font_family'     => 'Courier New',
            'label_font_shop'       => 6,
            'label_font_price'      => 8,
            'label_font_barcode'    => 5,
            'label_qr_size'         => 11,
            'label_barcode_type'    => 'qrcode_m',
            'label_show_shop'       => true,
            'label_show_barcode_text' => true,
            'label_show_qr'         => true,
        ];

        $boolKeys   = ['label_show_shop', 'label_show_barcode_text', 'label_show_qr'];
        $stringKeys = ['label_font_family', 'label_barcode_type'];
        $keys = array_keys($defaults);

        $rows = Setting::whereIn('key', $keys)->pluck('value', 'key');

        foreach ($defaults as $key => $def) {
            if ($rows->has($key)) {
                if (in_array($key, $boolKeys)) {
                    $defaults[$key] = (bool) $rows[$key];
                } elseif (in_array($key, $stringKeys)) {
                    $defaults[$key] = $rows[$key];
                } else {
                    $defaults[$key] = (float) $rows[$key];
                }
            }
        }

        return $defaults;
    }
}
