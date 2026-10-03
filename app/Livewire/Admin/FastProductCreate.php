<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductSupplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductBatch;
use App\Models\Setting;
use App\Http\Controllers\ProductApiController;
use App\Livewire\Concerns\WithDynamicLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

#[Title("Fast Product Entry & QR")]
class FastProductCreate extends Component
{
    use WithDynamicLayout;
    use WithFileUploads;

    // Header & Setup fields
    public $purchasingNumber = '';
    public $isEditingOrder = false;
    public $editingOrderId = null;
    public $editingOrderStatus = '';
    public $deletedItemIds = [];
    public $successMessage = '';

    public $selectedCategoryId = null;
    public $selectedItemName = '';
    public $selectedSupplierId = null;
    public $purchaseDate;
    public $defaultMarginPercent = 1000.0;
    public $costMultiplier = 1;
    public $sellingMultiplier = 0;

    // Fast Keyboard Input Row fields
    public $quickCost = '';
    public $quickQuantity = 1;
    public $quickSize = '';
    public $showSizeField = false;
    public $quickSystemCostPreview = 0;
    public $quickSellingPricePreview = 0;

    // Batch items list
    public $items = [];

    // Excel import file and supplier selection
    public $excelFile;
    public $importSupplierId = null;

    // Status & message tracking
    public $isSaved = false;
    public $savedCount = 0;
    public $errorMessage = '';
    public $filterStatus = 'all'; // 'all', 'with_qr', 'no_qr', 'printed', 'not_printed'

    // Search & Edit state
    public $searchQuery = '';
    public $editingIndex = null;
    public $editName = '';
    public $editSize = '';
    public $editEnteredCost = '';
    public $editCostPrice = '';
    public $editSellingPrice = '';
    public $editQuantity = 1;
    public $editBarcode = '';

    // Default reference IDs
    public $defaultBrandId;
    public $defaultCategoryId;
    public $defaultSupplierId;

    public function mount()
    {
        $this->purchaseDate = date('Y-m-d');
        $this->initializeDefaults();

        // Check if an existing purchase order was requested for editing
        $orderId = request()->query('order_id') ?: request()->query('edit_order');
        if ($orderId) {
            $this->loadOrderForEditing($orderId);
            return;
        }

        // Generate default purchasing number for new batch
        $this->purchasingNumber = $this->generateUniqueOrderCode();

        // Restore active staging session if exists (power cut / reload protection)
        $serverDraft = session('fast_product_stage_draft');
        if (!empty($serverDraft) && !empty($serverDraft['items'])) {
            $this->items = $serverDraft['items'];
            if (isset($serverDraft['selectedCategoryId'])) $this->selectedCategoryId = $serverDraft['selectedCategoryId'];
            if (isset($serverDraft['selectedItemName'])) $this->selectedItemName = $serverDraft['selectedItemName'];
            if (isset($serverDraft['selectedSupplierId'])) $this->selectedSupplierId = $serverDraft['selectedSupplierId'];
            if (isset($serverDraft['importSupplierId'])) $this->importSupplierId = $serverDraft['importSupplierId'];
            if (isset($serverDraft['costMultiplier'])) $this->costMultiplier = $serverDraft['costMultiplier'];
            if (isset($serverDraft['sellingMultiplier'])) $this->sellingMultiplier = $serverDraft['sellingMultiplier'];
            if (isset($serverDraft['purchaseDate'])) $this->purchaseDate = $serverDraft['purchaseDate'];
            if (isset($serverDraft['showSizeField'])) $this->showSizeField = (bool) $serverDraft['showSizeField'];
            if (isset($serverDraft['quickSize'])) $this->quickSize = $serverDraft['quickSize'];
            if (!empty($serverDraft['purchasingNumber'])) $this->purchasingNumber = $serverDraft['purchasingNumber'];
            $this->isSaved = false;
        }

        $categoryQuery = request()->query('category_id');
        $itemQuery = request()->query('item');

        if ($categoryQuery) {
            $cat = CategoryList::find($categoryQuery);
            if ($cat) {
                $this->selectedCategoryId = $cat->id;
                $this->selectedItemName = $cat->category_name;
            }
        } elseif ($itemQuery) {
            $cat = CategoryList::where('category_name', 'like', '%' . $itemQuery . '%')->first();
            if ($cat) {
                $this->selectedCategoryId = $cat->id;
                $this->selectedItemName = $cat->category_name;
            } else {
                $this->selectedItemName = $itemQuery;
            }
        } else {
            // Pick first category from DB
            $firstCat = CategoryList::where('category_name', '!=', 'Default Category')->first() ?? CategoryList::first();
            if ($firstCat) {
                $this->selectedCategoryId = $firstCat->id;
                $this->selectedItemName = $firstCat->category_name;
            }
        }

        if ($this->isBangleCategory($this->selectedItemName)) {
            $this->showSizeField = true;
        }

        $this->updateSellingPricePreview();
    }

    public function generateUniqueOrderCode(): string
    {
        $datePart = date('Ymd', strtotime($this->purchaseDate ?: date('Y-m-d')));
        $code = 'PO-' . $datePart . '-' . strtoupper(Str::random(5));
        while (PurchaseOrder::where('order_code', $code)->exists()) {
            $code = 'PO-' . $datePart . '-' . strtoupper(Str::random(5));
        }
        return $code;
    }

    public function generateNewPurchasingNumber()
    {
        if (!$this->isEditingOrder) {
            $this->purchasingNumber = $this->generateUniqueOrderCode();
            $this->saveDraftState();
        }
    }

    public function loadOrderForEditing($orderId)
    {
        $order = PurchaseOrder::with([
            'supplier',
            'items' => function($q) {
                $q->with(['product.price', 'product.stock', 'product.category', 'variant']);
            }
        ])->find($orderId);

        if (!$order) {
            $this->errorMessage = "Purchase Order #{$orderId} not found!";
            $this->purchasingNumber = $this->generateUniqueOrderCode();
            return;
        }

        $this->isEditingOrder = true;
        $this->editingOrderId = $order->id;
        $this->purchasingNumber = $order->order_code;
        $this->editingOrderStatus = $order->status;
        $this->selectedSupplierId = $order->supplier_id;
        $this->importSupplierId = $order->supplier_id;
        $this->purchaseDate = $order->order_date ? date('Y-m-d', strtotime($order->order_date)) : date('Y-m-d');
        $this->costMultiplier = floatval($order->cost_multiplier ?: ($order->supplier->cost_multiplier ?? 1));
        $this->sellingMultiplier = floatval($order->sell_multiplier ?: ($order->supplier->sell_multiplier ?? 0));
        $this->deletedItemIds = [];

        $this->items = [];
        $hasAnySize = false;

        foreach ($order->items as $item) {
            $product = $item->product;
            $qty = floatval($item->quantity);
            $recQty = floatval($item->received_quantity);
            $receiveStatus = $item->status ?? ($recQty > 0 || $order->status === 'complete' ? 'received' : 'notreceived');

            $costMult = max(1, floatval($this->costMultiplier ?: 1));
            $enteredCost = floatval($item->supplier_cost ?: ($product && $product->price ? $product->price->supplier_price : ($item->unit_price / $costMult)));
            $systemCost = floatval($item->system_cost ?: ($item->unit_price ?: ($product && $product->price ? $product->price->system_cost : 0)));
            $sellingPrice = floatval($item->selling_price ?: ($product && $product->price ? $product->price->selling_price : 0));

            $sizeVal = $product ? ($product->size ?? null) : null;
            if (!empty($sizeVal)) {
                $hasAnySize = true;
            }

            $this->items[] = [
                'temp_id' => Str::uuid()->toString(),
                'po_item_id' => $item->id,
                'saved_product_id' => $item->product_id,
                'name' => $product->name ?? 'Jewelry Item',
                'code' => $product->code ?? '',
                'size' => $sizeVal,
                'entered_cost' => $enteredCost,
                'cost_price' => $systemCost,
                'margin_percent' => 1000.0,
                'selling_price' => $sellingPrice,
                'quantity' => intval($qty),
                'barcode' => $product->barcode ?? null,
                'is_printed' => ($product && $product->barcode_printed === 'Yes'),
                'is_saved' => true,
                'receive_status' => $receiveStatus,
            ];
        }

        // Set default category from first item's product if available
        $firstItemProduct = $order->items->first()?->product;
        if ($firstItemProduct && $firstItemProduct->category_id) {
            $this->selectedCategoryId = $firstItemProduct->category_id;
            $this->selectedItemName = $firstItemProduct->category->category_name ?? $firstItemProduct->name;
        }

        if ($hasAnySize || $this->isBangleCategory($this->selectedItemName)) {
            $this->showSizeField = true;
        }

        $this->isSaved = true;
        $this->errorMessage = '';
        $this->updateSellingPricePreview();
        $this->js("setTimeout(renderQRCodes, 200);");
    }

    public function resetToNewOrder()
    {
        $this->items = [];
        $this->isEditingOrder = false;
        $this->editingOrderId = null;
        $this->editingOrderStatus = '';
        $this->deletedItemIds = [];
        $this->isSaved = false;
        $this->savedCount = 0;
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->purchasingNumber = $this->generateUniqueOrderCode();
        session()->forget('fast_product_stage_draft');
        $this->dispatch('draft-cleared');
        return redirect()->route('admin.fast-product-create');
    }

    private function isBangleCategory(?string $name): bool
    {
        if (empty($name)) return false;
        return (bool) preg_match('/bangle/i', $name);
    }

    private function initializeDefaults()
    {
        // Default brand
        $brand = BrandList::firstOrCreate(
            ['brand_name' => 'Default Brand'],
            ['status' => 'active']
        );
        $this->defaultBrandId = $brand->id;

        // Default category
        $category = CategoryList::firstOrCreate(
            ['category_name' => 'Default Category'],
            ['status' => 'active']
        );
        $this->defaultCategoryId = $category->id;
        $this->selectedCategoryId = $category->id;

        // Default supplier
        $supplier = ProductSupplier::firstOrCreate(
            ['name' => 'Default Supplier'],
            [
                'phone' => '0000000000',
                'email' => 'default@supplier.com',
                'address' => 'Default Address',
                'status' => 'active'
            ]
        );
        $this->defaultSupplierId = $supplier->id;
        $this->selectedSupplierId = $supplier->id;
        $this->importSupplierId = $supplier->id;
    }

    public function updatedSelectedSupplierId($value)
    {
        if ($value) {
            $this->importSupplierId = $value;
            $supplier = ProductSupplier::find($value);
            if ($supplier) {
                if (floatval($supplier->cost_multiplier) > 0) {
                    $this->costMultiplier = floatval($supplier->cost_multiplier);
                }
                if (floatval($supplier->sell_multiplier) > 0) {
                    $this->sellingMultiplier = floatval($supplier->sell_multiplier);
                }
                $this->recalculateUnsavedItems();
            }
        }
        $this->saveDraftState();
    }

    public function updatedPurchasingNumber($value)
    {
        $this->purchasingNumber = trim((string)$value);
        $this->saveDraftState();
    }

    public function updatedExcelFile()
    {
        if ($this->excelFile) {
            try {
                $originalName = $this->excelFile->getClientOriginalName();
                $fileBaseName = trim(pathinfo($originalName, PATHINFO_FILENAME));
                if (!empty($fileBaseName)) {
                    $this->purchasingNumber = $fileBaseName;
                    $this->saveDraftState();
                }
            } catch (\Exception $e) {
                Log::warning('Could not extract file name in updatedExcelFile: ' . $e->getMessage());
            }
        }
    }

    public function updatedSelectedCategoryId($value)
    {
        if ($value) {
            $cat = CategoryList::find($value);
            if ($cat) {
                $this->selectedItemName = $cat->category_name;
                if ($this->isBangleCategory($cat->category_name)) {
                    $this->showSizeField = true;
                } else {
                    $hasBatchSizes = collect($this->items)->contains(fn($i) => !empty($i['size']));
                    if (!$hasBatchSizes) {
                        $this->showSizeField = false;
                    }
                }
            }
        }
    }

    public function updatedSelectedItemName($value)
    {
        if ($this->isBangleCategory($value)) {
            $this->showSizeField = true;
        }
    }

    public function updatedQuickCost()
    {
        $this->updateSellingPricePreview();
    }

    public function updatedCostMultiplier()
    {
        if ($this->costMultiplier === '' || $this->costMultiplier === null) {
            $this->updateSellingPricePreview();
            return;
        }

        $costMult = max(0.01, floatval($this->costMultiplier));
        if ($this->selectedSupplierId) {
            ProductSupplier::where('id', $this->selectedSupplierId)->update(['cost_multiplier' => $costMult]);
        }

        $this->recalculateUnsavedItems();
        $this->saveDraftState();
    }

    public function updatedSellingMultiplier()
    {
        if ($this->sellingMultiplier === '' || $this->sellingMultiplier === null) {
            $this->updateSellingPricePreview();
            return;
        }

        $sellMult = max(0, floatval($this->sellingMultiplier));
        if ($this->selectedSupplierId) {
            ProductSupplier::where('id', $this->selectedSupplierId)->update(['sell_multiplier' => $sellMult]);
        }

        $this->recalculateUnsavedItems();
        $this->saveDraftState();
    }

    public function recalculateUnsavedItems()
    {
        $costMult = ($this->costMultiplier !== '' && $this->costMultiplier !== null) ? floatval($this->costMultiplier) : 1.0;
        if ($costMult <= 0) $costMult = 1.0;
        $sellMult = ($this->sellingMultiplier !== '' && $this->sellingMultiplier !== null) ? floatval($this->sellingMultiplier) : 0.0;

        foreach ($this->items as $idx => $item) {
            if (!$item['is_saved']) {
                $enteredCost = isset($item['entered_cost']) && $item['entered_cost'] !== '' && floatval($item['entered_cost']) > 0
                    ? max(0, floatval($item['entered_cost']))
                    : max(0, floatval($item['cost_price']) / $costMult);
                $this->items[$idx]['entered_cost'] = $enteredCost;
                $this->items[$idx]['cost_price'] = round($enteredCost * $costMult, 2);
                if ($sellMult > 0) {
                    $this->items[$idx]['selling_price'] = round($enteredCost * $sellMult, 2);
                }
            }
        }
        $this->updateSellingPricePreview();
    }

    public function updatedDefaultMarginPercent()
    {
        $this->recalculateUnsavedItems();
        $this->saveDraftState();
    }

    public function setMarginPercent($percent)
    {
        $this->defaultMarginPercent = max(0, floatval($percent));
        $this->updatedDefaultMarginPercent();
    }

    private function updateSellingPricePreview()
    {
        $cost = max(0, floatval($this->quickCost));
        $costMult = ($this->costMultiplier !== '' && $this->costMultiplier !== null) ? floatval($this->costMultiplier) : 1;
        $sellMult = ($this->sellingMultiplier !== '' && $this->sellingMultiplier !== null) ? floatval($this->sellingMultiplier) : 0;

        if ($cost > 0) {
            $this->quickSystemCostPreview = round($cost * $costMult, 2);
            $this->quickSellingPricePreview = round($cost * $sellMult, 2);
        } else {
            $this->quickSystemCostPreview = 0;
            $this->quickSellingPricePreview = 0;
        }
    }

    /**
     * Clean numeric string from currency symbols (Rs., LKR, $, commas, spaces, units)
     */
    public function cleanNumeric($value): float
    {
        if (is_numeric($value)) {
            return floatval($value);
        }
        $str = (string)$value;
        if (trim($str) === '') {
            return 0.0;
        }

        // Remove words and trailing abbreviation dots like 'Rs.', 'LKR.', 'USD', 'pcs', etc.
        $str = preg_replace('/[a-zA-Z]+\.?/', '', $str);
        $str = str_replace(',', '', $str);
        $str = preg_replace('/[^0-9.]/', '', $str);

        // If multiple dots remain, keep only the last one as decimal
        $parts = explode('.', $str);
        if (count($parts) > 2) {
            $decimals = array_pop($parts);
            $integers = implode('', $parts);
            $str = $integers . '.' . $decimals;
        }

        $cleaned = trim($str);
        return is_numeric($cleaned) ? floatval($cleaned) : 0.0;
    }

    /**
     * Add single item to batch from rapid keyboard inputs
     */
    public function addItemToBatch()
    {
        $cost = max(0, floatval($this->quickCost));
        $qty = max(1, intval($this->quickQuantity));

        if ($cost <= 0) {
            $this->js("setTimeout(() => { const el = document.getElementById('quick-cost-input'); if(el){ el.focus(); el.select(); } }, 50);");
            return;
        }

        $itemName = !empty(trim($this->selectedItemName)) ? trim($this->selectedItemName) : 'Jewelry Item';
        
        $costMult = ($this->costMultiplier !== '' && $this->costMultiplier !== null) ? floatval($this->costMultiplier) : 1;
        $sellMult = ($this->sellingMultiplier !== '' && $this->sellingMultiplier !== null) ? floatval($this->sellingMultiplier) : 0;

        $systemCost = round($cost * $costMult, 2);
        $sellingPrice = round($cost * $sellMult, 2);
        $uniqueCode = 'PROD-' . strtoupper(Str::random(6));

        $size = trim((string)$this->quickSize);

        array_unshift($this->items, [
            'temp_id' => Str::uuid()->toString(),
            'name' => $itemName,
            'code' => $uniqueCode,
            'size' => !empty($size) ? $size : null,
            'entered_cost' => $cost,
            'cost_price' => $systemCost,
            'margin_percent' => 1000.0,
            'selling_price' => $sellingPrice,
            'quantity' => $qty,
            'barcode' => null, // Empty QR initially
            'is_printed' => false,
            'is_saved' => false,
            'saved_product_id' => null,
        ]);

        // Reset inputs and refocus cost input (preserve quickSize for sticky behavior!)
        $this->quickCost = '';
        $this->quickQuantity = 1;
        $this->quickSystemCostPreview = 0;
        $this->quickSellingPricePreview = 0;
        $this->isSaved = false;
        $this->errorMessage = '';

        $this->js("setTimeout(() => { const el = document.getElementById('quick-cost-input'); if(el){ el.focus(); el.select(); } }, 50);");
    }

    /**
     * Inline row updates
     */
    public function updateRowCost($index, $value)
    {
        if (isset($this->items[$index]) && (!$this->items[$index]['is_saved'] || $this->isEditingOrder)) {
            $enteredCost = max(0, $this->cleanNumeric($value));
            $this->items[$index]['entered_cost'] = $enteredCost;
            $this->items[$index]['cost_price'] = round($enteredCost * $this->costMultiplier, 2);
            $this->items[$index]['selling_price'] = round($enteredCost * $this->sellingMultiplier, 2);
            $this->isSaved = false;
            $this->saveDraftState();
        }
    }

    public function updateRowSize($index, $value)
    {
        if (isset($this->items[$index]) && (!$this->items[$index]['is_saved'] || $this->isEditingOrder)) {
            $size = trim((string)$value);
            $this->items[$index]['size'] = !empty($size) ? $size : null;
            $this->isSaved = false;
            $this->saveDraftState();
        }
    }

    public function updateRowSystemCost($index, $value)
    {
        if (isset($this->items[$index]) && (!$this->items[$index]['is_saved'] || $this->isEditingOrder)) {
            $systemCost = max(0, $this->cleanNumeric($value));
            $this->items[$index]['cost_price'] = $systemCost;
            $this->isSaved = false;
            $this->saveDraftState();
        }
    }

    public function updateRowMargin($index, $value)
    {
        if (isset($this->items[$index]) && (!$this->items[$index]['is_saved'] || $this->isEditingOrder)) {
            $cost = max(0, floatval($this->items[$index]['cost_price']));
            $margin = max(0, $this->cleanNumeric($value));
            $this->items[$index]['margin_percent'] = $margin;
            $this->items[$index]['selling_price'] = round($cost + ($cost * $margin / 100), 2);
            $this->isSaved = false;
            $this->saveDraftState();
        }
    }

    public function updateRowSellingPrice($index, $value)
    {
        if (isset($this->items[$index]) && (!$this->items[$index]['is_saved'] || $this->isEditingOrder)) {
            $sellingPrice = max(0, $this->cleanNumeric($value));
            $this->items[$index]['selling_price'] = $sellingPrice;
            $this->isSaved = false;
            $this->saveDraftState();
        }
    }

    public function updateRowQuantity($index, $value)
    {
        if (isset($this->items[$index]) && (!$this->items[$index]['is_saved'] || $this->isEditingOrder)) {
            $this->items[$index]['quantity'] = max(1, intval($this->cleanNumeric($value)));
            $this->isSaved = false;
            $this->saveDraftState();
        }
    }

    public function updateRowName($index, $value)
    {
        if (isset($this->items[$index]) && (!$this->items[$index]['is_saved'] || $this->isEditingOrder)) {
            $this->items[$index]['name'] = trim($value) ?: ($this->selectedItemName ?: 'Jewelry Item');
            $this->isSaved = false;
            $this->saveDraftState();
        }
    }

    public function updateRowBarcode($index, $value)
    {
        if (isset($this->items[$index]) && (!$this->items[$index]['is_saved'] || $this->isEditingOrder)) {
            $barcode = trim($value);
            $this->items[$index]['barcode'] = !empty($barcode) ? $barcode : null;
            $this->isSaved = false;
            $this->saveDraftState();
            $this->js("setTimeout(renderQRCodes, 100);");
        }
    }

    /**
     * Generate unique EAN-13 barcode code with extra exclusion support
     */
    public function generateUniqueBarcode(array $extraExcluded = []): string
    {
        $existingInBatch = array_filter(array_column($this->items, 'barcode'));
        $allExcluded = array_merge($existingInBatch, $extraExcluded);

        do {
            $code = '200' . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int) $code[$i] * ($i % 2 === 0 ? 1 : 3);
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            $barcode = $code . $checkDigit;
        } while (ProductDetail::where('barcode', $barcode)->exists() || in_array($barcode, $allExcluded));

        return $barcode;
    }

    /**
     * Generate QR/barcode for all items that don't have one
     */
    public function generateAllQRCodes()
    {
        if (empty($this->items)) {
            return;
        }

        $generated = [];
        foreach ($this->items as $idx => $item) {
            $isDuplicate = false;
            if (!empty($item['barcode'])) {
                $q = ProductDetail::where('barcode', $item['barcode']);
                if (!empty($item['saved_product_id'])) {
                    $q->where('id', '!=', $item['saved_product_id']);
                }
                $isDuplicate = $q->exists() || in_array($item['barcode'], $generated);
            }

            if (empty($item['barcode']) || $isDuplicate) {
                $newBarcode = $this->generateUniqueBarcode($generated);
                $this->items[$idx]['barcode'] = $newBarcode;
                $generated[] = $newBarcode;

                // If product already saved in DB, update barcode in DB too
                if (!empty($item['saved_product_id'])) {
                    ProductDetail::where('id', $item['saved_product_id'])->update(['barcode' => $newBarcode]);
                }
            } else {
                $generated[] = $item['barcode'];
            }
        }

        $this->errorMessage = '';
        $this->js("setTimeout(renderQRCodes, 100);");
    }

    /**
     * Generate QR code for a single row and automatically save the product to database
     */
    public function generateSingleQR($index)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['barcode'] = $this->generateUniqueBarcode();

            // Auto-save this product to database immediately with its generated QR
            if (empty($this->items[$index]['is_saved'])) {
                $this->saveSingleProduct($index, false);
            }

            $this->js("setTimeout(renderQRCodes, 100);");
        }
    }

    /**
     * Save an individual product from the batch list directly to the database
     */
    public function saveSingleProduct($index, $autoClear = false)
    {
        if (!isset($this->items[$index])) {
            return false;
        }

        $item = $this->items[$index];
        if (!empty($item['is_saved'])) {
            if ($autoClear) {
                array_splice($this->items, $index, 1);
                $this->items = array_values($this->items);
                $this->js("setTimeout(renderQRCodes, 100);");
            }
            return true;
        }

        $this->errorMessage = '';

        try {
            DB::beginTransaction();

            $orderDate = $this->purchaseDate ?: date('Y-m-d');
            $supplierId = $this->selectedSupplierId ?: $this->defaultSupplierId;

            // Ensure unique SKU/Code
            $productCode = !empty($item['code']) ? trim($item['code']) : ('PROD-' . strtoupper(Str::random(8)));
            while (ProductDetail::where('code', $productCode)->exists()) {
                $productCode = 'PROD-' . strtoupper(Str::random(8));
            }

            // Ensure unique Barcode
            $barcode = !empty($item['barcode']) ? trim($item['barcode']) : null;
            if (empty($barcode) || ProductDetail::where('barcode', $barcode)->exists()) {
                $barcode = $this->generateUniqueBarcode();
            }

            $receiveStatus = $item['receive_status'] ?? 'received';
            $isReceived = ($receiveStatus === 'received');

            $product = ProductDetail::create([
                'code' => $productCode,
                'name' => $item['name'] ?: ($this->selectedItemName ?: 'Jewelry Item'),
                'size' => !empty($item['size']) ? trim($item['size']) : null,
                'model' => null,
                'image' => null,
                'description' => $isReceived ? 'Fast entry' : 'Not received / missed from supplier batch',
                'barcode' => $barcode,
                'barcode_printed' => ($isReceived && !empty($item['is_printed'])) ? 'Yes' : 'No',
                'status' => $isReceived ? 'active' : 'inactive',
                'brand_id' => $this->defaultBrandId,
                'category_id' => $this->selectedCategoryId ?: $this->defaultCategoryId,
                'supplier_id' => $supplierId,
                'variant_id' => null,
                'website_live' => false,
            ]);

            $costMult = ($this->costMultiplier !== '' && $this->costMultiplier !== null) ? floatval($this->costMultiplier) : 1.0;
            if ($costMult <= 0) $costMult = 1.0;
            $enteredCost = isset($item['entered_cost']) && $item['entered_cost'] !== '' ? max(0, floatval($item['entered_cost'])) : max(0, floatval($item['cost_price']) / $costMult);
            $systemCost = max(0, floatval($item['cost_price']));
            $sellingPrice = max(0, floatval($item['selling_price']));

            $sellMult = ($this->sellingMultiplier !== '' && $this->sellingMultiplier !== null) ? floatval($this->sellingMultiplier) : 0.0;

            ProductPrice::create([
                'product_id' => $product->id,
                'variant_id' => null,
                'pricing_mode' => 'single',
                'supplier_price' => $enteredCost,
                'system_cost' => $systemCost,
                'cost_multiplier' => $costMult,
                'sell_multiplier' => $sellMult,
                'selling_price' => $sellingPrice,
                'retail_price' => $sellingPrice,
                'wholesale_price' => $sellingPrice,
                'distributor_price' => null,
                'discount_price' => 0,
            ]);

            $qty = max(1, intval($item['quantity']));
            $stockQty = $isReceived ? $qty : 0;
            ProductStock::create([
                'product_id' => $product->id,
                'variant_id' => null,
                'available_stock' => $stockQty,
                'damage_stock' => 0,
                'total_stock' => $stockQty,
                'sold_count' => 0,
                'restocked_quantity' => 0,
            ]);

            $itemSupplierTotal = ($qty * $enteredCost);
            $itemSystemTotal = ($qty * $systemCost);
            $itemSellingTotal = ($qty * $sellingPrice);

            // Attach product to current purchasing number / active order
            $orderCode = trim((string)$this->purchasingNumber) ?: $this->generateUniqueOrderCode();
            $this->purchasingNumber = $orderCode;

            if ($this->isEditingOrder && $this->editingOrderId) {
                $purchaseOrder = PurchaseOrder::find($this->editingOrderId);
                if (!$purchaseOrder) {
                    $purchaseOrder = PurchaseOrder::where('order_code', $orderCode)->first();
                }
            } else {
                $purchaseOrder = PurchaseOrder::where('order_code', $orderCode)->first();
            }

            if (!$purchaseOrder) {
                $poStatus = $isReceived ? 'complete' : 'pending';
                $purchaseOrder = PurchaseOrder::create([
                    'order_code' => $orderCode,
                    'supplier_id' => $supplierId,
                    'order_date' => $orderDate,
                    'received_date' => $orderDate,
                    'status' => $poStatus,
                    'cost_multiplier' => $costMult,
                    'sell_multiplier' => $sellMult,
                    'total_amount' => $isReceived ? $itemSystemTotal : 0,
                    'total_supplier_amount' => $isReceived ? $itemSupplierTotal : 0,
                    'total_system_amount' => $isReceived ? $itemSystemTotal : 0,
                    'total_selling_amount' => $isReceived ? $itemSellingTotal : 0,
                    'due_amount' => 0,
                ]);
            }

            $this->editingOrderId = $purchaseOrder->id;
            $this->isEditingOrder = true;
            $this->purchasingNumber = $purchaseOrder->order_code;

            $poItem = PurchaseOrderItem::create([
                'order_id' => $purchaseOrder->id,
                'product_id' => $product->id,
                'variant_id' => null,
                'variant_value' => null,
                'quantity' => $qty,
                'received_quantity' => $isReceived ? $qty : 0,
                'unit_price' => $systemCost,
                'supplier_cost' => $enteredCost,
                'system_cost' => $systemCost,
                'cost_multiplier' => $costMult,
                'total_supplier_cost' => $itemSupplierTotal,
                'total_system_cost' => $itemSystemTotal,
                'selling_price' => $sellingPrice,
                'sell_multiplier' => $sellMult,
                'total_selling_price' => $itemSellingTotal,
                'discount' => 0,
                'discount_type' => 'fixed',
                'status' => $isReceived ? 'received' : 'notreceived',
            ]);

            // Consolidate order totals
            $allOrderItems = PurchaseOrderItem::where('order_id', $purchaseOrder->id)->get();
            $totSupplier = $allOrderItems->sum('total_supplier_cost');
            $totSystem = $allOrderItems->sum('total_system_cost');
            $totSelling = $allOrderItems->sum('total_selling_price');
            $hasNotRecv = $allOrderItems->contains(fn($i) => ($i->status === 'notreceived' || $i->received_quantity < $i->quantity));
            $hasRecv = $allOrderItems->contains(fn($i) => ($i->status === 'received' || $i->received_quantity > 0));
            $poStatus = ($hasRecv && !$hasNotRecv) ? 'complete' : ($hasRecv ? 'received' : 'pending');

            $purchaseOrder->update([
                'status' => $poStatus,
                'total_amount' => $totSystem,
                'total_supplier_amount' => $totSupplier,
                'total_system_amount' => $totSystem,
                'total_selling_amount' => $totSelling,
            ]);
            $this->editingOrderStatus = $poStatus;

            // Sync ProductBatch record for FIFO inventory
            if ($isReceived && $stockQty > 0) {
                $existingBatch = ProductBatch::where('product_id', $product->id)
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->first();

                if ($existingBatch) {
                    $existingBatch->update([
                        'supplier_price' => $enteredCost,
                        'selling_price' => $sellingPrice,
                        'retail_price' => $sellingPrice,
                        'wholesale_price' => $sellingPrice,
                        'quantity' => $qty,
                        'remaining_quantity' => $stockQty,
                        'received_date' => $orderDate,
                        'status' => 'active',
                    ]);
                } else {
                    $batchNum = $orderCode . '-P' . $product->id;
                    if (ProductBatch::where('batch_number', $batchNum)->exists()) {
                        $batchNum = $batchNum . '-' . strtoupper(Str::random(4));
                    }
                    ProductBatch::create([
                        'product_id' => $product->id,
                        'purchase_order_id' => $purchaseOrder->id,
                        'batch_number' => $batchNum,
                        'supplier_price' => $enteredCost,
                        'selling_price' => $sellingPrice,
                        'retail_price' => $sellingPrice,
                        'wholesale_price' => $sellingPrice,
                        'quantity' => $qty,
                        'remaining_quantity' => $stockQty,
                        'received_date' => $orderDate,
                        'status' => 'active',
                    ]);
                }
            }

            DB::commit();

            ProductApiController::clearCache();

            if ($autoClear) {
                array_splice($this->items, $index, 1);
                $this->items = array_values($this->items);
            } else {
                $this->items[$index]['is_saved'] = true;
                $this->items[$index]['saved_product_id'] = $product->id;
                $this->items[$index]['code'] = $product->code;
                $this->items[$index]['barcode'] = $barcode;
                $this->items[$index]['po_item_id'] = $poItem->id;
            }

            $this->isSaved = (count(array_filter($this->items, fn($i) => empty($i['is_saved']))) === 0);

            $this->js("setTimeout(renderQRCodes, 100);");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveSingleProduct error: ' . $e->getMessage());
            $this->errorMessage = 'Failed to save product: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Clear all already-saved products from the batch list
     */
    public function clearSavedItems()
    {
        $this->items = array_values(array_filter($this->items, fn($i) => empty($i['is_saved'])));
        $this->js("setTimeout(renderQRCodes, 100);");
    }

    /**
     * Get count of saved items in the batch
     */
    public function getSavedItemsCountProperty(): int
    {
        return count(array_filter($this->items, fn($i) => !empty($i['is_saved'])));
    }

    /**
     * Remove item from batch
     */
    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            $item = $this->items[$index];
            if (!empty($item['po_item_id'])) {
                $this->deletedItemIds[] = $item['po_item_id'];
            }
            array_splice($this->items, $index, 1);
            $this->items = array_values($this->items);
            $this->js("setTimeout(renderQRCodes, 100);");
        }
    }

    /**
     * Clear all unsaved batch items or reset batch
     */
    public function clearBatch()
    {
        $this->items = [];
        $this->isSaved = false;
        $this->savedCount = 0;
        $this->isEditingOrder = false;
        $this->editingOrderId = null;
        $this->editingOrderStatus = '';
        $this->deletedItemIds = [];
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->purchasingNumber = $this->generateUniqueOrderCode();
        session()->forget('fast_product_stage_draft');
        $this->dispatch('draft-cleared');
        $this->js("setTimeout(() => { document.getElementById('quick-cost-input')?.focus(); }, 100)");
    }

    /**
     * Filter batch products list view
     */
    public function setFilter(string $status)
    {
        $this->filterStatus = $status;
        $this->js("setTimeout(renderQRCodes, 100);");
    }

    public function matchesFilter(array $item): bool
    {
        $hasQr = !empty($item['barcode']);
        $isPrinted = !empty($item['is_printed']) && $item['is_printed'] === true;
        $receiveStatus = $item['receive_status'] ?? 'received';

        if ($this->filterStatus === 'with_qr') {
            return $hasQr;
        } elseif ($this->filterStatus === 'no_qr') {
            return !$hasQr;
        } elseif ($this->filterStatus === 'printed') {
            return $isPrinted;
        } elseif ($this->filterStatus === 'not_printed') {
            return !$isPrinted;
        } elseif ($this->filterStatus === 'received') {
            return $receiveStatus === 'received';
        } elseif ($this->filterStatus === 'notreceived') {
            return $receiveStatus === 'notreceived';
        }

        return true;
    }

    public function getFilterCounts(): array
    {
        $all = count($this->items);
        $withQr = 0;
        $noQr = 0;
        $printed = 0;
        $notPrinted = 0;
        $received = 0;
        $notreceived = 0;

        foreach ($this->items as $item) {
            if (!empty($item['barcode'])) {
                $withQr++;
            } else {
                $noQr++;
            }

            if (!empty($item['is_printed']) && $item['is_printed'] === true) {
                $printed++;
            } else {
                $notPrinted++;
            }

            $rec = $item['receive_status'] ?? 'received';
            if ($rec === 'notreceived') {
                $notreceived++;
            } else {
                $received++;
            }
        }

        return [
            'all' => $all,
            'with_qr' => $withQr,
            'no_qr' => $noQr,
            'printed' => $printed,
            'not_printed' => $notPrinted,
            'received' => $received,
            'notreceived' => $notreceived,
        ];
    }

    public function getFilterCountsProperty(): array
    {
        return $this->getFilterCounts();
    }

    public function getMissedTotalCostProperty(): float
    {
        $missed = array_filter($this->items, fn($i) => ($i['receive_status'] ?? 'received') === 'notreceived');
        return floatval(array_reduce($missed, fn($c, $i) => $c + (floatval($i['cost_price'] ?? 0) * intval($i['quantity'] ?? 1)), 0));
    }

    public function getUnsavedReceivedCountProperty(): int
    {
        return count(array_filter($this->items, fn($i) => empty($i['is_saved']) && ($i['receive_status'] ?? 'received') === 'received'));
    }

    public function getUnsavedNotReceivedCountProperty(): int
    {
        return count(array_filter($this->items, fn($i) => empty($i['is_saved']) && ($i['receive_status'] ?? 'received') === 'notreceived'));
    }

    public function toggleReceiveStatus(int $index)
    {
        if (!isset($this->items[$index])) {
            return;
        }

        $current = $this->items[$index]['receive_status'] ?? 'received';
        $newStatus = ($current === 'received') ? 'notreceived' : 'received';
        $this->items[$index]['receive_status'] = $newStatus;

        if (!empty($this->items[$index]['saved_product_id'])) {
            $productId = $this->items[$index]['saved_product_id'];
            $qty = intval($this->items[$index]['quantity'] ?? 1);

            if ($newStatus === 'notreceived') {
                ProductStock::where('product_id', $productId)->update([
                    'available_stock' => 0,
                    'total_stock' => 0,
                ]);
                ProductDetail::where('id', $productId)->update([
                    'status' => 'inactive',
                    'description' => 'Not received / missed from supplier batch',
                ]);
                PurchaseOrderItem::where('product_id', $productId)->update([
                    'status' => 'notreceived',
                    'received_quantity' => 0,
                ]);
            } else {
                ProductStock::where('product_id', $productId)->update([
                    'available_stock' => $qty,
                    'total_stock' => $qty,
                ]);
                ProductDetail::where('id', $productId)->update([
                    'status' => 'active',
                    'description' => 'Fast entry',
                ]);
                PurchaseOrderItem::where('product_id', $productId)->update([
                    'status' => 'received',
                    'received_quantity' => $qty,
                ]);
            }
        }

        $this->js("setTimeout(renderQRCodes, 100);");
    }

    public function markAllAsReceived()
    {
        foreach ($this->items as $idx => $item) {
            $this->items[$idx]['receive_status'] = 'received';
            if (!empty($item['saved_product_id'])) {
                $qty = intval($item['quantity'] ?? 1);
                ProductStock::where('product_id', $item['saved_product_id'])->update([
                    'available_stock' => $qty,
                    'total_stock' => $qty,
                ]);
                ProductDetail::where('id', $item['saved_product_id'])->update([
                    'status' => 'active',
                    'description' => 'Fast entry',
                ]);
                PurchaseOrderItem::where('product_id', $item['saved_product_id'])->update([
                    'status' => 'received',
                    'received_quantity' => $qty,
                ]);
            }
        }
        $this->js("setTimeout(renderQRCodes, 100);");
    }

    public function markAllAsNotReceived()
    {
        foreach ($this->items as $idx => $item) {
            $this->items[$idx]['receive_status'] = 'notreceived';
            if (!empty($item['saved_product_id'])) {
                ProductStock::where('product_id', $item['saved_product_id'])->update([
                    'available_stock' => 0,
                    'total_stock' => 0,
                ]);
                ProductDetail::where('id', $item['saved_product_id'])->update([
                    'status' => 'inactive',
                    'description' => 'Not received / missed from supplier batch',
                ]);
                PurchaseOrderItem::where('product_id', $item['saved_product_id'])->update([
                    'status' => 'notreceived',
                    'received_quantity' => 0,
                ]);
            }
        }
        $this->js("setTimeout(renderQRCodes, 100);");
    }

    public function downloadMissedProductsExcel()
    {
        $missedItems = array_values(array_filter($this->items, fn($i) => ($i['receive_status'] ?? 'received') === 'notreceived'));
        if (empty($missedItems)) {
            $this->errorMessage = 'No missed / unreceived products in the current batch.';
            return;
        }

        $supplier = $this->selectedSupplierId ? ProductSupplier::find($this->selectedSupplierId) : null;
        $supplierName = $supplier ? Str::slug($supplier->name) : 'supplier';
        $fileName = 'missed_products_' . $supplierName . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($missedItems) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['#', 'Product Name', 'Code / SKU', 'Size', 'Expected Qty', 'Supplier Cost', 'Total Cost', 'Status'];
            $sheet->fromArray([$headers], null, 'A1');

            $rows = [];
            foreach ($missedItems as $idx => $item) {
                $qty = intval($item['quantity'] ?? 1);
                $cost = floatval($item['entered_cost'] ?? ($item['cost_price'] ?? 0));
                $rows[] = [
                    $idx + 1,
                    $item['name'] ?? 'Item',
                    $item['code'] ?? '',
                    $item['size'] ?? '',
                    $qty,
                    $cost,
                    $cost * $qty,
                    'NOT RECEIVED / MISSED',
                ];
            }

            $sheet->fromArray($rows, null, 'A2');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function saveReceivedOnly()
    {
        $unsavedReceived = array_filter($this->items, fn($i) => empty($i['is_saved']) && ($i['receive_status'] ?? 'received') === 'received');

        if (empty($unsavedReceived)) {
            $this->errorMessage = 'No unsaved received items found to save.';
            return;
        }

        $this->saveBatch(true);
    }

    public function matchesSearch(array $item, string $search): bool
    {
        if (empty($search)) {
            return true;
        }

        $name = strtolower($item['name'] ?? '');
        $code = strtolower($item['code'] ?? '');
        $barcode = strtolower($item['barcode'] ?? '');

        return str_contains($name, $search) || str_contains($code, $search) || str_contains($barcode, $search);
    }

    public function updatedSearchQuery()
    {
        unset($this->filteredItems);
        unset($this->inventorySearchResults);
    }

    public function getFilteredItems(): array
    {
        $result = [];
        $search = trim(strtolower($this->searchQuery));

        foreach ($this->items as $index => $item) {
            if ($this->matchesFilter($item) && $this->matchesSearch($item, $search)) {
                $item['_original_index'] = $index;
                $result[] = $item;
            }
        }
        return $result;
    }

    public function getInventorySearchResultsProperty(): array
    {
        $search = trim($this->searchQuery);
        if (strlen($search) < 2) {
            return [];
        }

        $existingSavedIds = array_filter(array_column($this->items, 'saved_product_id'));

        return ProductDetail::with(['price', 'stock'])
            ->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->whereNotIn('id', $existingSavedIds)
            ->take(5)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'barcode' => $p->barcode,
                    'cost_price' => floatval($p->price->supplier_price ?? 0),
                    'selling_price' => floatval($p->price->selling_price ?? 0),
                    'quantity' => intval($p->stock->available_stock ?? 0),
                ];
            })
            ->toArray();
    }

    /**
     * Load an existing product from inventory into batch for immediate editing
     */
    public function loadProductIntoBatch($productId)
    {
        $product = ProductDetail::with(['price', 'stock'])->find($productId);
        if (!$product) {
            return;
        }

        $cost = floatval($product->price->supplier_price ?? 0);
        $sell = floatval($product->price->selling_price ?? 0);
        $qty = intval($product->stock->available_stock ?? 0);
        $costMult = ($this->costMultiplier !== '' && $this->costMultiplier !== null) ? floatval($this->costMultiplier) : 1;

        array_unshift($this->items, [
            'temp_id' => Str::uuid()->toString(),
            'name' => $product->name,
            'code' => $product->code,
            'size' => $product->size ?? null,
            'entered_cost' => $costMult > 0 ? round($cost / $costMult, 2) : $cost,
            'cost_price' => $cost,
            'margin_percent' => 1000.0,
            'selling_price' => $sell,
            'quantity' => $qty,
            'barcode' => $product->barcode,
            'is_printed' => ($product->barcode_printed === 'Yes'),
            'is_saved' => true,
            'saved_product_id' => $product->id,
        ]);

        if (!empty($product->size)) {
            $this->showSizeField = true;
        }

        $this->searchQuery = '';
        $this->startEditingSaved(0);
        $this->js("setTimeout(renderQRCodes, 100);");
    }

    /**
     * Start editing a saved product row
     */
    public function startEditingSaved($index)
    {
        if (!isset($this->items[$index])) {
            return;
        }

        $this->editingIndex = $index;
        $this->editName = $this->items[$index]['name'];
        $this->editSize = $this->items[$index]['size'] ?? '';
        $this->editEnteredCost = $this->items[$index]['entered_cost'] ?? 0;
        $this->editCostPrice = $this->items[$index]['cost_price'];
        $this->editSellingPrice = $this->items[$index]['selling_price'];
        $this->editQuantity = $this->items[$index]['quantity'];
        $this->editBarcode = $this->items[$index]['barcode'] ?? '';
    }

    /**
     * Live recalculation when editing supplier cost
     */
    public function updatedEditEnteredCost($value)
    {
        $costMult = ($this->costMultiplier !== '' && $this->costMultiplier !== null) ? floatval($this->costMultiplier) : 1;
        $sellMult = ($this->sellingMultiplier !== '' && $this->sellingMultiplier !== null) ? floatval($this->sellingMultiplier) : 0;
        $entered = max(0, $this->cleanNumeric($value));
        $this->editCostPrice = round($entered * $costMult, 2);
        if ($sellMult > 0) {
            $this->editSellingPrice = round($entered * $sellMult, 2);
        }
    }

    /**
     * Cancel editing a saved product
     */
    public function cancelEditing()
    {
        $this->editingIndex = null;
        $this->errorMessage = '';
    }

    /**
     * Generate a new unique barcode for the item currently being edited
     */
    public function generateEditBarcode()
    {
        $this->editBarcode = $this->generateUniqueBarcode();
        $this->errorMessage = '';
        $this->js("setTimeout(renderQRCodes, 100);");
    }

    /**
     * Save changes made to a saved product back to database
     */
    public function saveEditedProduct()
    {
        if ($this->editingIndex === null || !isset($this->items[$this->editingIndex])) {
            return;
        }

        $idx = $this->editingIndex;
        $item = $this->items[$idx];

        $name = trim($this->editName) ?: $item['name'];
        $size = trim($this->editSize) ?: null;
        $costPrice = max(0, floatval($this->editCostPrice));
        $sellingPrice = max(0, floatval($this->editSellingPrice));
        $qty = max(0, intval($this->editQuantity));
        $barcode = trim($this->editBarcode) ?: null;

        $costMult = ($this->costMultiplier !== '' && $this->costMultiplier !== null) ? floatval($this->costMultiplier) : 1.0;
        if ($costMult <= 0) $costMult = 1.0;
        $enteredCost = isset($this->editEnteredCost) && $this->editEnteredCost !== '' ? max(0, floatval($this->editEnteredCost)) : round($costPrice / $costMult, 2);

        // Barcode uniqueness validation before querying DB update
        if (!empty($barcode)) {
            $conflictQuery = ProductDetail::where('barcode', $barcode);
            if (!empty($item['saved_product_id'])) {
                $conflictQuery->where('id', '!=', $item['saved_product_id']);
            }
            $conflict = $conflictQuery->first();
            if ($conflict) {
                $this->errorMessage = "Duplicate Barcode: '{$barcode}' is already assigned to '{$conflict->name}' (Code: {$conflict->code}). Please choose a unique barcode or click 'Gen' to create a new one.";
                return;
            }

            // Check if barcode conflicts with another row in this batch
            foreach ($this->items as $bIdx => $bItem) {
                if ($bIdx !== $idx && !empty($bItem['barcode']) && $bItem['barcode'] === $barcode) {
                    $this->errorMessage = "Duplicate Barcode: '{$barcode}' is already used by row #" . ($bIdx + 1) . " in this batch.";
                    return;
                }
            }
        }

        if (!empty($item['saved_product_id'])) {
            try {
                DB::beginTransaction();

                $product = ProductDetail::find($item['saved_product_id']);
                if ($product) {
                    $product->update([
                        'name' => $name,
                        'size' => $size,
                        'barcode' => $barcode,
                    ]);

                    ProductPrice::updateOrCreate(
                        ['product_id' => $product->id],
                        [
                            'supplier_price' => $enteredCost > 0 ? $enteredCost : $costPrice,
                            'system_cost' => $costPrice,
                            'cost_multiplier' => $costMult,
                            'selling_price' => $sellingPrice,
                            'retail_price' => $sellingPrice,
                            'wholesale_price' => $sellingPrice,
                        ]
                    );

                    ProductStock::updateOrCreate(
                        ['product_id' => $product->id],
                        [
                            'available_stock' => $qty,
                            'total_stock' => $qty,
                        ]
                    );

                    $rawSuppCost = $enteredCost > 0 ? $enteredCost : $costPrice;
                    PurchaseOrderItem::where('product_id', $product->id)
                        ->update([
                            'quantity' => $qty,
                            'received_quantity' => $qty,
                            'unit_price' => $costPrice,
                            'supplier_cost' => $rawSuppCost,
                            'system_cost' => $costPrice,
                            'cost_multiplier' => $costMult,
                            'total_supplier_cost' => $qty * $rawSuppCost,
                            'total_system_cost' => $qty * $costPrice,
                            'selling_price' => $sellingPrice,
                            'total_selling_price' => $qty * $sellingPrice,
                        ]);

                    $orderIds = PurchaseOrderItem::where('product_id', $product->id)->pluck('order_id')->unique();
                    foreach ($orderIds as $oId) {
                        $orderSupp = PurchaseOrderItem::where('order_id', $oId)->sum('total_supplier_cost');
                        $orderSys = PurchaseOrderItem::where('order_id', $oId)->sum('total_system_cost');
                        $orderSell = PurchaseOrderItem::where('order_id', $oId)->sum('total_selling_price');
                        PurchaseOrder::where('id', $oId)->update([
                            'total_amount' => $orderSys ?: 0,
                            'total_supplier_amount' => $orderSupp ?: 0,
                            'total_system_amount' => $orderSys ?: 0,
                            'total_selling_amount' => $orderSell ?: 0,
                        ]);
                    }
                }

                DB::commit();
                ProductApiController::clearCache();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error updating saved product in FastProductCreate: ' . $e->getMessage());
                $this->errorMessage = 'Failed to update product: ' . $e->getMessage();
                return;
            }
        }

        // Update item in batch
        $this->items[$idx]['name'] = $name;
        $this->items[$idx]['size'] = $size;
        $this->items[$idx]['cost_price'] = $costPrice;
        $this->items[$idx]['selling_price'] = $sellingPrice;
        $this->items[$idx]['quantity'] = $qty;
        $this->items[$idx]['barcode'] = $barcode;
        $this->items[$idx]['entered_cost'] = $enteredCost;

        $this->editingIndex = null;
        $this->errorMessage = '';
        $this->js("setTimeout(renderQRCodes, 100);");
    }

    /**
     * Submit search when user hits Enter (auto-loads or auto-edits matching barcode/code)
     */
    public function searchSubmit()
    {
        $search = trim($this->searchQuery);
        if (empty($search)) return;

        // Check if barcode or code in current batch
        foreach ($this->items as $idx => $item) {
            if ((!empty($item['barcode']) && strcasecmp($item['barcode'], $search) === 0)
                || (!empty($item['code']) && strcasecmp($item['code'], $search) === 0)) {
                $this->startEditingSaved($idx);
                return;
            }
        }

        // Check database
        $product = ProductDetail::where('barcode', $search)->orWhere('code', $search)->first();
        if ($product) {
            $this->loadProductIntoBatch($product->id);
        }
    }

    public function getFilteredItemsProperty(): array
    {
        return $this->getFilteredItems();
    }

    public function getFilteredCount(): int
    {
        return count($this->getFilteredItems());
    }

    public function getFilteredCountProperty(): int
    {
        return $this->getFilteredCount();
    }

    /**
     * Mark a row as printed
     */
    public function markRowPrinted($index)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['is_printed'] = true;
            if (!empty($this->items[$index]['saved_product_id'])) {
                ProductDetail::where('id', $this->items[$index]['saved_product_id'])->update(['barcode_printed' => 'Yes']);
            }
        }
    }

    /**
     * Toggle printed status for a row
     */
    public function togglePrintStatus($index)
    {
        if (isset($this->items[$index])) {
            $newStatus = empty($this->items[$index]['is_printed']);
            $this->items[$index]['is_printed'] = $newStatus;
            if (!empty($this->items[$index]['saved_product_id'])) {
                ProductDetail::where('id', $this->items[$index]['saved_product_id'])
                    ->update(['barcode_printed' => $newStatus ? 'Yes' : 'No']);
            }
        }
    }

    /**
     * Mark all batch items as printed
     */
    public function markAllBatchAsPrinted()
    {
        foreach ($this->items as $idx => $item) {
            if (!empty($item['barcode'])) {
                $this->items[$idx]['is_printed'] = true;
                if (!empty($item['saved_product_id'])) {
                    ProductDetail::where('id', $item['saved_product_id'])->update(['barcode_printed' => 'Yes']);
                }
            }
        }
    }

    /**
     * Get preset configuration for the selected supplier
     */
    public function getSupplierPreset($supplierId = null): array
    {
        $id = $supplierId ?: $this->importSupplierId ?: $this->selectedSupplierId;
        $supplier = $id ? ProductSupplier::find($id) : null;
        $name = $supplier ? strtoupper(trim($supplier->name)) : '';

        if (str_contains($name, 'SELVA')) {
            return [
                'type' => 'selva',
                'name' => $supplier ? $supplier->name : 'SELVA',
                'badge_class' => 'bg-primary text-white',
                'badge_text' => 'SELVA Sheet Format',
                'description' => 'SELVA Supplier Sheet (Description -> Product Name, Price -> Supplier Cost, Quantity -> Stock)',
                'template_file' => 'fast_product_template_selva.xlsx',
                'columns' => ['Description (Product Name)', 'Quantity (Stock)', 'Price (Supplier Cost)'],
            ];
        } elseif (str_contains($name, 'THARA')) {
            return [
                'type' => 'thara_gold',
                'name' => $supplier ? $supplier->name : 'Thara Gold',
                'badge_class' => 'bg-warning text-dark',
                'badge_text' => 'Thara Gold Invoice Format',
                'description' => 'Tax Invoice format (Particulars, Rate, Qty, HSN Code)',
                'template_file' => 'fast_product_template_thara_gold.xlsx',
                'columns' => ['Particulars (Product Name)', 'Rate (Raw Cost)', 'Qty (Stock)'],
            ];
        } elseif (str_contains($name, 'MR GOLD') || preg_match('/\bMR\b/i', $name)) {
            return [
                'type' => 'mr',
                'name' => $supplier ? $supplier->name : 'MR GOLD',
                'badge_class' => 'bg-dark text-white',
                'badge_text' => 'MR GOLD Sheet Format',
                'description' => 'MR GOLD Supplier Sheet (Product Name, Supplier Cost, Quantity, SKU)',
                'template_file' => 'fast_product_template_mr.xlsx',
                'columns' => ['Product Name', 'Supplier Cost', 'Quantity', 'SKU'],
            ];
        } elseif (str_contains($name, 'RAMESH')) {
            return [
                'type' => 'ramesh',
                'name' => $supplier ? $supplier->name : 'RAMESH',
                'badge_class' => 'bg-info text-dark',
                'badge_text' => 'RAMESH Sheet Format',
                'description' => 'RAMESH Product Sheet (Item, Unit Price, Qty, Amount)',
                'template_file' => 'fast_product_template_ramesh.xlsx',
                'columns' => ['Item (Product Name)', 'Unit Price (Raw Cost)', 'Qty (Stock)', 'Amount (Total)'],
            ];
        } elseif (str_contains($name, 'POOVATHINGAL')) {
            return [
                'type' => 'poovathingal',
                'name' => $supplier ? $supplier->name : 'POOVATHINGAL',
                'badge_class' => 'bg-success text-white',
                'badge_text' => 'POOVATHINGAL Sheet Format',
                'description' => 'Poovathingal Product Sheet (Item Description, Cost Price, Quantity, Code)',
                'template_file' => 'fast_product_template_poovathingal.xlsx',
                'columns' => ['Item Description (Product Name)', 'Cost Price (Raw Cost)', 'Quantity (Stock)', 'Code (SKU)'],
            ];
        } elseif (str_contains($name, 'ALFA')) {
            return [
                'type' => 'alfa_gold',
                'name' => $supplier ? $supplier->name : 'ALFA GOLD',
                'badge_class' => 'bg-danger text-white',
                'badge_text' => 'ALFA GOLD Sheet Format',
                'description' => 'Alfa Gold Product Sheet (Description, Rate, Quantity, Item Code)',
                'template_file' => 'fast_product_template_alfa_gold.xlsx',
                'columns' => ['Description (Product Name)', 'Rate (Raw Cost)', 'Quantity (Stock)', 'Item Code (SKU)'],
            ];
        }

        return [
            'type' => 'standard',
            'name' => $supplier ? $supplier->name : 'Standard Supplier',
            'badge_class' => 'bg-secondary text-white',
            'badge_text' => 'Standard Format',
            'description' => 'Standard Product Import (Product Name, SKU Code, Cost Price, Quantity, Barcode)',
            'template_file' => 'fast_product_import_template.xlsx',
            'columns' => ['Product Name', 'Cost Price', 'Quantity', 'SKU Code', 'Selling Price', 'Barcode'],
        ];
    }

    /**
     * Download supplier-specific Excel template
     */
    public function downloadSupplierTemplate($supplierId = null)
    {
        $preset = $this->getSupplierPreset($supplierId);
        $filename = $preset['template_file'];
        $path = public_path('templates/' . $filename);

        if (file_exists($path)) {
            return response()->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $headers = [];
        $sampleRows = [];

        switch ($preset['type']) {
            case 'selva':
                $headers = ['S.No', 'Description', 'Quantity', 'Price'];
                $sampleRows = [
                    [1, 'ARM8314275-AWH---17B NECKLACE', 13, 375.00],
                    [2, 'ARM8314317-ARUW---17B NECKLACE', 12, 385.00],
                    [3, 'ARM8765660-AMX---17H MUGAPPU', 12, 145.00],
                ];
                break;

            case 'thara_gold':
                $headers = ['Particulars', 'Rate', 'Qty', 'HSN Code'];
                $sampleRows = [
                    ['18" THARA ROPE CHAIN', 420.00, 10, '71171990'],
                    ['22" THARA FANCY CHAIN', 550.00, 5, '71171990'],
                    ['THARA MUGAPPU BRACELET', 320.00, 12, '71171990'],
                ];
                break;

            case 'mr':
                $headers = ['Product Name', 'Supplier Cost', 'Quantity', 'SKU'];
                $sampleRows = [
                    ['MR GOLD RING 24K', 450.00, 8, 'MR-R-01'],
                    ['MR GOLD BANGLE', 600.00, 4, 'MR-B-02'],
                    ['MR GOLD STUD EARRING', 220.00, 15, 'MR-E-03'],
                ];
                break;

            case 'ramesh':
                $headers = ['Item', 'Unit Price', 'Qty', 'Amount'];
                $sampleRows = [
                    ['RAMESH JUMKHA', 280.00, 15, 4200.00],
                    ['RAMESH STUD', 150.00, 20, 3000.00],
                    ['RAMESH STONE BRACELET', 340.00, 8, 2720.00],
                ];
                break;

            case 'poovathingal':
                $headers = ['Item Description', 'Cost Price', 'Quantity', 'Code'];
                $sampleRows = [
                    ['POOVATHINGAL CHAIN 22K', 490.00, 10, 'PV-CH-01'],
                    ['POOVATHINGAL BRACELET', 380.00, 6, 'PV-BR-02'],
                ];
                break;

            case 'alfa_gold':
                $headers = ['Description', 'Rate', 'Quantity', 'Item Code'];
                $sampleRows = [
                    ['ALFA GOLD NECKLACE', 520.00, 12, 'ALF-NK-01'],
                    ['ALFA GOLD PENDANT', 210.00, 25, 'ALF-PD-05'],
                ];
                break;

            default:
                return $this->downloadExcelTemplate();
        }

        return response()->streamDownload(function () use ($headers, $sampleRows) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->fromArray($sampleRows, null, 'A2');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import Excel File (Supplier List / Invoices) with or without barcodes
     */
    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'importSupplierId' => 'nullable|exists:product_suppliers,id',
        ]);

        if ($this->importSupplierId) {
            $this->selectedSupplierId = $this->importSupplierId;
        }

        $this->errorMessage = '';

        try {
            $data = Excel::toArray([], $this->excelFile)[0] ?? [];

            if (empty($data) || count($data) < 2) {
                $this->errorMessage = 'The uploaded Excel/CSV file is empty or missing data rows.';
                return;
            }

            // Find header row index by inspecting first 10 rows
            $headerRowIdx = -1;
            $nameIdx = -1;
            $sizeIdx = -1;
            $codeIdx = -1;
            $costIdx = -1;
            $priceIdx = -1;
            $qtyIdx = -1;
            $barcodeIdx = -1;

            for ($rowIdx = 0; $rowIdx < min(10, count($data)); $rowIdx++) {
                $rowCells = array_map(fn($h) => strtolower(trim(str_replace(["\r", "\n"], ' ', (string)$h))), $data[$rowIdx]);

                $tempNameIdx = -1;
                $tempSizeIdx = -1;
                $tempCostIdx = -1;
                $tempQtyIdx = -1;
                $tempCodeIdx = -1;
                $tempPriceIdx = -1;
                $tempBarcodeIdx = -1;

                foreach ($rowCells as $i => $col) {
                    if (in_array($col, ['description', 'particulars', 'particular', 'item name', 'product name', 'product_name', 'name', 'item', 'item_name'])) {
                        $tempNameIdx = $i;
                    } elseif (in_array($col, ['size', 'bangle size', 'sz', 'item size'])) {
                        $tempSizeIdx = $i;
                    } elseif (in_array($col, ['cost', 'cost_price', 'cost price', 'supplier_price', 'supplier price', 'buy_price', 'purchase_price', 'unit rate', 'raw cost', 'cost rate', 'unit price', 'unit_price', 'supplier cost', 'rate', 'unit cost', 'rate / price'])) {
                        $tempCostIdx = $i;
                    } elseif (in_array($col, ['quantity', 'qty', 'stock', 'available_stock', 'available stock', 'pieces', 'nos', 'count', 'stock quantity'])) {
                        $tempQtyIdx = $i;
                    } elseif (in_array($col, ['selling_price', 'selling price', 'retail_price', 'retail price', 'mrp', 'sell price', 'selling_rate'])) {
                        $tempPriceIdx = $i;
                    } elseif ($col === 'price') {
                        // If specific cost column not already assigned, 'price' in supplier invoices is supplier cost
                        if ($tempCostIdx === -1) {
                            $tempCostIdx = $i;
                        } else {
                            $tempPriceIdx = $i;
                        }
                    } elseif (in_array($col, ['barcode', 'qr', 'qr_code', 'qrcode', 'upc', 'code128', 'ean', 'qr / barcode'])) {
                        $tempBarcodeIdx = $i;
                    } elseif (in_array($col, ['code', 'product_code', 'product code', 'sku', 'item code'])) {
                        $tempCodeIdx = $i;
                    }
                    // HSN Code is deliberately not mapped to barcode (HSN is Indian GST tax code, not a barcode)
                }

                // If this row has at least name and (cost or qty), or both cost and qty, consider it the header row!
                if (($tempNameIdx !== -1 && ($tempCostIdx !== -1 || $tempQtyIdx !== -1)) || ($tempCostIdx !== -1 && $tempQtyIdx !== -1)) {
                    $headerRowIdx = $rowIdx;
                    $nameIdx = $tempNameIdx;
                    $sizeIdx = $tempSizeIdx;
                    $costIdx = $tempCostIdx;
                    $qtyIdx = $tempQtyIdx;
                    $codeIdx = $tempCodeIdx;
                    $priceIdx = $tempPriceIdx;
                    $barcodeIdx = $tempBarcodeIdx;
                    break;
                }
            }

            // Fallback: If no header row detected, assume row 0
            if ($headerRowIdx === -1) {
                $headerRowIdx = 0;
                $header = array_map(fn($h) => strtolower(trim((string)$h)), $data[0]);
                foreach ($header as $i => $col) {
                    if (in_array($col, ['description', 'particulars', 'name', 'product_name', 'product name', 'item', 'item_name'])) $nameIdx = $i;
                    elseif (in_array($col, ['size', 'bangle size', 'sz', 'item size'])) $sizeIdx = $i;
                    elseif (in_array($col, ['code', 'product_code', 'product code', 'sku', 'item code'])) $codeIdx = $i;
                    elseif (in_array($col, ['rate', 'cost', 'cost_price', 'cost price', 'supplier_price', 'supplier price', 'buy_price', 'purchase_price', 'unit price', 'unit_price', 'unit rate', 'supplier cost', 'price'])) $costIdx = $i;
                    elseif (in_array($col, ['selling_price', 'selling price', 'retail_price', 'retail price', 'mrp'])) $priceIdx = $i;
                    elseif (in_array($col, ['qty', 'quantity', 'stock', 'available_stock', 'available stock', 'stock quantity'])) $qtyIdx = $i;
                    elseif (in_array($col, ['barcode', 'qr', 'qr_code', 'qrcode', 'upc', 'code128', 'ean'])) $barcodeIdx = $i;
                }
            }

            $importedRows = 0;
            $ignoredPhrases = ['total', 'gross amount', 'cgst amount', 'sgst amount', 'igst amount', 'net amount', 'ack dt', 'ack no', 'rupees', 'amount', 'carry'];

            for ($r = $headerRowIdx + 1; $r < count($data); $r++) {
                $row = $data[$r];
                if (empty(array_filter($row, fn($v) => !is_null($v) && trim((string)$v) !== ''))) continue;

                $rawName = $nameIdx !== -1 && isset($row[$nameIdx]) ? trim((string)$row[$nameIdx]) : '';
                $rawLowerName = strtolower($rawName);

                // Skip invoice footer/summary rows
                $isFooter = false;
                foreach ($ignoredPhrases as $phrase) {
                    if ($rawLowerName === $phrase || str_starts_with($rawLowerName, $phrase) || str_contains($rawLowerName, $phrase)) {
                        $isFooter = true;
                        break;
                    }
                }
                if ($isFooter) continue;

                $rowCombined = strtolower(implode(' ', array_filter(array_map('strval', $row))));
                if (str_contains($rowCombined, 'gross amount') || str_contains($rowCombined, 'net amount') || str_contains($rowCombined, 'cgst amount') || str_contains($rowCombined, 'sgst amount') || str_contains($rowCombined, 'grand total') || str_starts_with(trim($rowCombined), 'total')) {
                    continue;
                }

                $cost = $costIdx !== -1 && isset($row[$costIdx]) ? $this->cleanNumeric($row[$costIdx]) : 0;
                $qty = $qtyIdx !== -1 && isset($row[$qtyIdx]) ? max(1, intval($this->cleanNumeric($row[$qtyIdx]))) : 1;

                // Skip if name is empty and cost is 0
                if (empty($rawName) && $cost <= 0) {
                    continue;
                }

                $name = !empty($rawName) ? $rawName : ($this->selectedItemName ?: 'Jewelry Item');
                $code = $codeIdx !== -1 && !empty($row[$codeIdx]) ? trim((string)$row[$codeIdx]) : ('PROD-' . strtoupper(Str::random(6)));

                $barcode = null;
                if ($barcodeIdx !== -1 && !empty($row[$barcodeIdx])) {
                    $barcode = trim((string)$row[$barcodeIdx]);
                }

                $systemCost = $cost;
                $sellingPrice = 0;
                $sellMult = ($this->sellingMultiplier !== '' && $this->sellingMultiplier !== null) ? floatval($this->sellingMultiplier) : 0;
                $costMult = ($this->costMultiplier !== '' && $this->costMultiplier !== null) ? floatval($this->costMultiplier) : 1;

                if ($priceIdx !== -1 && !empty($row[$priceIdx])) {
                    $sellingPrice = $this->cleanNumeric($row[$priceIdx]);
                    if ($cost > 0) {
                        $systemCost = round($cost * $costMult, 2);
                    }
                } elseif ($cost > 0) {
                    $systemCost = round($cost * $costMult, 2);
                    $sellingPrice = round($cost * $sellMult, 2);
                }

                $size = ($sizeIdx !== -1 && isset($row[$sizeIdx]) && trim((string)$row[$sizeIdx]) !== '') ? trim((string)$row[$sizeIdx]) : null;

                $this->items[] = [
                    'temp_id' => Str::uuid()->toString(),
                    'name' => $name,
                    'code' => $code,
                    'size' => $size,
                    'entered_cost' => $cost,
                    'cost_price' => $systemCost,
                    'margin_percent' => 1000.0,
                    'selling_price' => $sellingPrice,
                    'quantity' => $qty,
                    'barcode' => $barcode,
                    'is_printed' => false,
                    'is_saved' => false,
                    'saved_product_id' => null,
                    'receive_status' => 'received',
                ];

                if (!empty($size)) {
                    $this->showSizeField = true;
                }

                $importedRows++;
            }

            // Automatically set purchasing number / batch name from Excel file name
            if ($this->excelFile) {
                try {
                    $originalName = $this->excelFile->getClientOriginalName();
                    $fileBaseName = trim(pathinfo($originalName, PATHINFO_FILENAME));
                    if (!empty($fileBaseName)) {
                        $this->purchasingNumber = $fileBaseName;
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not extract file name in importExcel: ' . $e->getMessage());
                }
            }

            $this->reset('excelFile');
            $this->isSaved = false;
            $this->successMessage = "Successfully imported {$importedRows} products! Purchasing / Batch Code set to '{$this->purchasingNumber}'. Click 'Save All Products' to save into inventory.";
            $this->saveDraftState();

            $this->js("
                const modalEl = document.getElementById('excelImportModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.hide();
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('padding-right');
                }
            ");
            $this->js("setTimeout(renderQRCodes, 150);");

        } catch (\Exception $e) {
            Log::error('FastProductCreate excel import error: ' . $e->getMessage());
            $this->errorMessage = 'Excel import error: ' . $e->getMessage();
        }
    }

    /**
     * Download styled Excel (.xlsx) template
     */
    public function downloadExcelTemplate()
    {
        $path = public_path('templates/fast_product_import_template.xlsx');
        if (file_exists($path)) {
            return response()->download($path, 'fast_product_import_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->streamDownload(function () {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['Product Name', 'SKU Code', 'Cost Price', 'Selling Price', 'Quantity', 'Barcode'];
            $sheet->fromArray([$headers], null, 'A1');
            $sampleRows = [
                ['Fm Neck Z310', 'FM-Z310', 370.00, 450.00, 11, '71171990'],
                ['24 Cz Mu 591', '24CZ-591', 440.00, 550.00, 5, '71171990'],
            ];
            $sheet->fromArray($sampleRows, null, 'A2');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'fast_product_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Download sample Excel / CSV template
     */
    public function downloadSampleTemplate()
    {
        $path = public_path('templates/fast_product_import_template.csv');
        if (file_exists($path)) {
            return response()->download($path, 'fast_product_import_template.csv', [
                'Content-Type' => 'text/csv',
            ]);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fast_product_import_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Product Name', 'SKU Code', 'Cost Price', 'Selling Price', 'Quantity', 'Barcode']);
            fputcsv($handle, ['Fm Neck Z310', 'FM-Z310', '370.00', '450.00', '11', '71171990']);
            fputcsv($handle, ['24 Cz Mu 591', '24CZ-591', '440.00', '550.00', '5', '71171990']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Save all batch products to database in a single transaction
     */
    public function saveBatch($receivedOnly = false)
    {
        if ($this->isEditingOrder) {
            if (empty($this->items)) {
                $this->errorMessage = 'No items in the order to save.';
                return;
            }
        } else {
            $unsavedItems = array_filter($this->items, function($i) use ($receivedOnly) {
                if (!empty($i['is_saved'])) return false;
                if ($receivedOnly && ($i['receive_status'] ?? 'received') === 'notreceived') return false;
                return true;
            });

            if (empty($unsavedItems)) {
                $this->errorMessage = $receivedOnly ? 'No unsaved received items found to save.' : 'No unsaved items in batch.';
                return;
            }
        }

        $this->errorMessage = '';
        $this->successMessage = '';

        // Validate and preserve purchasing number
        $orderCode = trim((string)$this->purchasingNumber);
        if (empty($orderCode)) {
            $orderCode = $this->generateUniqueOrderCode();
            $this->purchasingNumber = $orderCode;
        }

        // Validate uniqueness against other purchase orders
        $collisionQuery = PurchaseOrder::where('order_code', $orderCode);
        if ($this->isEditingOrder && $this->editingOrderId) {
            $collisionQuery->where('id', '!=', $this->editingOrderId);
        }
        if ($collisionQuery->exists()) {
            $this->errorMessage = "Purchasing Number '{$orderCode}' is already used by another purchase order. Please enter a unique order number.";
            return;
        }

        try {
            DB::beginTransaction();

            $orderDate = $this->purchaseDate ?: date('Y-m-d');
            $supplierId = $this->selectedSupplierId ?: $this->defaultSupplierId;
            $costMult = ($this->costMultiplier !== '' && $this->costMultiplier !== null) ? floatval($this->costMultiplier) : 1.0;
            if ($costMult <= 0) $costMult = 1.0;
            $sellMult = ($this->sellingMultiplier !== '' && $this->sellingMultiplier !== null) ? floatval($this->sellingMultiplier) : 0.0;

            // Handle deleted items tracked during this edit session
            if (!empty($this->deletedItemIds)) {
                PurchaseOrderItem::whereIn('id', $this->deletedItemIds)->delete();
                $this->deletedItemIds = [];
            }

            // Get or create PurchaseOrder
            if ($this->isEditingOrder && $this->editingOrderId) {
                $purchaseOrder = PurchaseOrder::find($this->editingOrderId);
                if (!$purchaseOrder) {
                    $purchaseOrder = PurchaseOrder::where('order_code', $orderCode)->first();
                }
            } else {
                $purchaseOrder = null;
            }

            if (!$purchaseOrder) {
                $purchaseOrder = PurchaseOrder::create([
                    'order_code' => $orderCode,
                    'supplier_id' => $supplierId,
                    'order_date' => $orderDate,
                    'received_date' => $orderDate,
                    'status' => 'pending',
                    'cost_multiplier' => $costMult,
                    'sell_multiplier' => $sellMult,
                    'total_amount' => 0,
                    'total_supplier_amount' => 0,
                    'total_system_amount' => 0,
                    'total_selling_amount' => 0,
                    'due_amount' => 0,
                ]);
            } else {
                $purchaseOrder->update([
                    'order_code' => $orderCode,
                    'supplier_id' => $supplierId,
                    'order_date' => $orderDate,
                    'received_date' => $orderDate,
                    'cost_multiplier' => $costMult,
                    'sell_multiplier' => $sellMult,
                ]);
            }

            $this->editingOrderId = $purchaseOrder->id;
            $this->isEditingOrder = true;
            $this->purchasingNumber = $purchaseOrder->order_code;

            $usedCodesInBatch = [];
            $usedBarcodesInBatch = [];

            // Process each item in batch
            foreach ($this->items as $idx => $item) {
                if ($receivedOnly && ($item['receive_status'] ?? 'received') === 'notreceived' && empty($item['is_saved'])) {
                    continue;
                }

                $receiveStatus = $item['receive_status'] ?? 'received';
                $isReceived = ($receiveStatus === 'received');
                $qty = max(1, intval($item['quantity']));
                $stockQty = $isReceived ? $qty : 0;

                $enteredCost = isset($item['entered_cost']) && $item['entered_cost'] !== ''
                    ? max(0, floatval($item['entered_cost']))
                    : max(0, floatval($item['cost_price']) / $costMult);
                $systemCost = max(0, floatval($item['cost_price']));
                $sellingPrice = max(0, floatval($item['selling_price']));

                $itemSupplierTotal = ($qty * $enteredCost);
                $itemSystemTotal = ($qty * $systemCost);
                $itemSellingTotal = ($qty * $sellingPrice);

                if (!empty($item['saved_product_id'])) {
                    // Update existing product
                    $product = ProductDetail::find($item['saved_product_id']);
                    if ($product) {
                        $barcode = !empty($item['barcode']) ? trim($item['barcode']) : $product->barcode;
                        $product->update([
                            'name' => $item['name'] ?: ($this->selectedItemName ?: 'Jewelry Item'),
                            'size' => !empty($item['size']) ? trim($item['size']) : null,
                            'barcode' => $barcode,
                            'barcode_printed' => ($isReceived && !empty($item['is_printed'])) ? 'Yes' : 'No',
                            'status' => $isReceived ? 'active' : 'inactive',
                            'supplier_id' => $supplierId,
                            'category_id' => $this->selectedCategoryId ?: $this->defaultCategoryId,
                            'description' => $isReceived ? 'Fast entry' : 'Not received / missed from supplier batch',
                        ]);

                        ProductPrice::updateOrCreate(
                            ['product_id' => $product->id],
                            [
                                'pricing_mode' => 'single',
                                'supplier_price' => $enteredCost,
                                'system_cost' => $systemCost,
                                'cost_multiplier' => $costMult,
                                'sell_multiplier' => $sellMult,
                                'selling_price' => $sellingPrice,
                                'retail_price' => $sellingPrice,
                                'wholesale_price' => $sellingPrice,
                                'discount_price' => 0,
                            ]
                        );

                        ProductStock::updateOrCreate(
                            ['product_id' => $product->id],
                            [
                                'available_stock' => $stockQty,
                                'total_stock' => $stockQty,
                            ]
                        );

                        $poItem = null;
                        if (!empty($item['po_item_id'])) {
                            $poItem = PurchaseOrderItem::find($item['po_item_id']);
                        }
                        if (!$poItem) {
                            $poItem = PurchaseOrderItem::where('order_id', $purchaseOrder->id)
                                ->where('product_id', $product->id)
                                ->first();
                        }

                        if ($poItem) {
                            $poItem->update([
                                'order_id' => $purchaseOrder->id,
                                'quantity' => $qty,
                                'received_quantity' => $isReceived ? $qty : 0,
                                'unit_price' => $systemCost,
                                'supplier_cost' => $enteredCost,
                                'system_cost' => $systemCost,
                                'cost_multiplier' => $costMult,
                                'total_supplier_cost' => $itemSupplierTotal,
                                'total_system_cost' => $itemSystemTotal,
                                'selling_price' => $sellingPrice,
                                'sell_multiplier' => $sellMult,
                                'total_selling_price' => $itemSellingTotal,
                                'status' => $receiveStatus,
                            ]);
                        } else {
                            $poItem = PurchaseOrderItem::create([
                                'order_id' => $purchaseOrder->id,
                                'product_id' => $product->id,
                                'variant_id' => null,
                                'variant_value' => null,
                                'quantity' => $qty,
                                'received_quantity' => $isReceived ? $qty : 0,
                                'unit_price' => $systemCost,
                                'supplier_cost' => $enteredCost,
                                'system_cost' => $systemCost,
                                'cost_multiplier' => $costMult,
                                'total_supplier_cost' => $itemSupplierTotal,
                                'total_system_cost' => $itemSystemTotal,
                                'selling_price' => $sellingPrice,
                                'sell_multiplier' => $sellMult,
                                'total_selling_price' => $itemSellingTotal,
                                'discount' => 0,
                                'discount_type' => 'fixed',
                                'status' => $receiveStatus,
                            ]);
                        }
                        $this->items[$idx]['po_item_id'] = $poItem->id;

                        if ($isReceived && $stockQty > 0) {
                            $existingBatch = ProductBatch::where('product_id', $product->id)
                                ->where('purchase_order_id', $purchaseOrder->id)
                                ->first();

                            if ($existingBatch) {
                                $existingBatch->update([
                                    'supplier_price' => $enteredCost,
                                    'selling_price' => $sellingPrice,
                                    'retail_price' => $sellingPrice,
                                    'wholesale_price' => $sellingPrice,
                                    'quantity' => $qty,
                                    'remaining_quantity' => $stockQty,
                                    'received_date' => $orderDate,
                                    'status' => 'active',
                                ]);
                            } else {
                                $batchNum = $orderCode . '-P' . $product->id;
                                if (ProductBatch::where('batch_number', $batchNum)->exists()) {
                                    $batchNum = $batchNum . '-' . strtoupper(Str::random(4));
                                }
                                ProductBatch::create([
                                    'product_id' => $product->id,
                                    'purchase_order_id' => $purchaseOrder->id,
                                    'batch_number' => $batchNum,
                                    'supplier_price' => $enteredCost,
                                    'selling_price' => $sellingPrice,
                                    'retail_price' => $sellingPrice,
                                    'wholesale_price' => $sellingPrice,
                                    'quantity' => $qty,
                                    'remaining_quantity' => $stockQty,
                                    'received_date' => $orderDate,
                                    'status' => 'active',
                                ]);
                            }
                        }
                    }
                } else {
                    // Create new product
                    $productCode = !empty($item['code']) ? trim($item['code']) : ('PROD-' . strtoupper(Str::random(8)));
                    while (in_array($productCode, $usedCodesInBatch) || ProductDetail::where('code', $productCode)->exists()) {
                        $productCode = 'PROD-' . strtoupper(Str::random(8));
                    }
                    $usedCodesInBatch[] = $productCode;

                    $barcode = !empty($item['barcode']) ? trim($item['barcode']) : null;
                    if (empty($barcode) || in_array($barcode, $usedBarcodesInBatch) || ProductDetail::where('barcode', $barcode)->exists()) {
                        $barcode = $this->generateUniqueBarcode($usedBarcodesInBatch);
                    }
                    $usedBarcodesInBatch[] = $barcode;
                    $this->items[$idx]['barcode'] = $barcode;

                    $product = ProductDetail::create([
                        'code' => $productCode,
                        'name' => $item['name'] ?: ($this->selectedItemName ?: 'Jewelry Item'),
                        'size' => !empty($item['size']) ? trim($item['size']) : null,
                        'model' => null,
                        'image' => null,
                        'description' => $isReceived ? 'Fast entry' : 'Not received / missed from supplier batch',
                        'barcode' => $barcode,
                        'barcode_printed' => ($isReceived && !empty($item['is_printed'])) ? 'Yes' : 'No',
                        'status' => $isReceived ? 'active' : 'inactive',
                        'brand_id' => $this->defaultBrandId,
                        'category_id' => $this->selectedCategoryId ?: $this->defaultCategoryId,
                        'supplier_id' => $supplierId,
                        'variant_id' => null,
                        'website_live' => false,
                    ]);

                    ProductPrice::create([
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'pricing_mode' => 'single',
                        'supplier_price' => $enteredCost,
                        'system_cost' => $systemCost,
                        'cost_multiplier' => $costMult,
                        'sell_multiplier' => $sellMult,
                        'selling_price' => $sellingPrice,
                        'retail_price' => $sellingPrice,
                        'wholesale_price' => $sellingPrice,
                        'distributor_price' => null,
                        'discount_price' => 0,
                    ]);

                    ProductStock::create([
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'available_stock' => $stockQty,
                        'damage_stock' => 0,
                        'total_stock' => $stockQty,
                        'sold_count' => 0,
                        'restocked_quantity' => 0,
                    ]);

                    $poItem = PurchaseOrderItem::create([
                        'order_id' => $purchaseOrder->id,
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'variant_value' => null,
                        'quantity' => $qty,
                        'received_quantity' => $isReceived ? $qty : 0,
                        'unit_price' => $systemCost,
                        'supplier_cost' => $enteredCost,
                        'system_cost' => $systemCost,
                        'cost_multiplier' => $costMult,
                        'total_supplier_cost' => $itemSupplierTotal,
                        'total_system_cost' => $itemSystemTotal,
                        'selling_price' => $sellingPrice,
                        'sell_multiplier' => $sellMult,
                        'total_selling_price' => $itemSellingTotal,
                        'discount' => 0,
                        'discount_type' => 'fixed',
                        'status' => $receiveStatus,
                    ]);

                    $this->items[$idx]['is_saved'] = true;
                    $this->items[$idx]['saved_product_id'] = $product->id;
                    $this->items[$idx]['code'] = $product->code;
                    $this->items[$idx]['po_item_id'] = $poItem->id;

                    if ($isReceived && $stockQty > 0) {
                        $existingBatch = ProductBatch::where('product_id', $product->id)
                            ->where('purchase_order_id', $purchaseOrder->id)
                            ->first();

                        if ($existingBatch) {
                            $existingBatch->update([
                                'supplier_price' => $enteredCost,
                                'selling_price' => $sellingPrice,
                                'retail_price' => $sellingPrice,
                                'wholesale_price' => $sellingPrice,
                                'quantity' => $qty,
                                'remaining_quantity' => $stockQty,
                                'received_date' => $orderDate,
                                'status' => 'active',
                            ]);
                        } else {
                            $batchNum = $orderCode . '-P' . $product->id;
                            if (ProductBatch::where('batch_number', $batchNum)->exists()) {
                                $batchNum = $batchNum . '-' . strtoupper(Str::random(4));
                            }
                            ProductBatch::create([
                                'product_id' => $product->id,
                                'purchase_order_id' => $purchaseOrder->id,
                                'batch_number' => $batchNum,
                                'supplier_price' => $enteredCost,
                                'selling_price' => $sellingPrice,
                                'retail_price' => $sellingPrice,
                                'wholesale_price' => $sellingPrice,
                                'quantity' => $qty,
                                'remaining_quantity' => $stockQty,
                                'received_date' => $orderDate,
                                'status' => 'active',
                            ]);
                        }
                    }
                }
            }

            // Recalculate totals of PurchaseOrder
            $orderItems = PurchaseOrderItem::where('order_id', $purchaseOrder->id)->get();
            $totalBatchCost = $orderItems->sum('total_system_cost');
            $totalBatchSupplierCost = $orderItems->sum('total_supplier_cost');
            $totalBatchSellingAmount = $orderItems->sum('total_selling_price');

            $hasNotReceived = $orderItems->contains(fn($p) => ($p->status === 'notreceived' || $p->received_quantity < $p->quantity));
            $hasReceived = $orderItems->contains(fn($p) => ($p->status === 'received' || $p->received_quantity > 0));
            $poStatus = ($hasReceived && !$hasNotReceived) ? 'complete' : ($hasReceived ? 'received' : 'pending');

            $purchaseOrder->update([
                'order_code' => $orderCode,
                'status' => $poStatus,
                'total_amount' => $totalBatchCost,
                'total_supplier_amount' => $totalBatchSupplierCost,
                'total_system_amount' => $totalBatchCost,
                'total_selling_amount' => $totalBatchSellingAmount,
            ]);

            // Ensure all batches linked to this purchase order have batch numbers formatted with order code
            $linkedBatches = ProductBatch::where('purchase_order_id', $purchaseOrder->id)->get();
            foreach ($linkedBatches as $b) {
                $targetBatchNum = $orderCode . '-P' . $b->product_id;
                if ($b->batch_number !== $targetBatchNum) {
                    if (!ProductBatch::where('batch_number', $targetBatchNum)->where('id', '!=', $b->id)->exists()) {
                        $b->update(['batch_number' => $targetBatchNum]);
                    }
                }
            }

            $this->editingOrderStatus = $poStatus;

            DB::commit();

            $this->isSaved = true;
            $this->savedCount = count($this->items);
            $this->successMessage = "Purchasing Order '{$orderCode}' saved successfully with {$this->savedCount} items!";

            session()->forget('fast_product_stage_draft');
            $this->dispatch('draft-cleared');
            ProductApiController::clearCache();

            $this->js("setTimeout(renderQRCodes, 150);");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FastProductCreate saveBatch error: ' . $e->getMessage());
            $this->errorMessage = 'Failed to save products: ' . $e->getMessage();
        }
    }

    /**
     * Get count of unsaved items currently in the batch
     */
    public function getUnsavedItemsCount(): int
    {
        return count(array_filter($this->items, fn($i) => empty($i['is_saved'])));
    }

    public function getUnsavedItemsCountProperty(): int
    {
        return $this->getUnsavedItemsCount();
    }

    /**
     * Save all batch items and redirect to a target URL (used by navigation guard)
     */
    public function saveBatchAndRedirect(string $redirectUrl = '')
    {
        // If quickCost has an unadded amount, add it to batch first
        $quickCost = max(0, floatval($this->quickCost));
        if ($quickCost > 0) {
            $this->addItemToBatch();
        }

        $unsavedItems = array_filter($this->items, fn($i) => empty($i['is_saved']));
        if (!empty($unsavedItems)) {
            $this->saveBatch();
        }

        if (empty($this->errorMessage) && ($this->isSaved || empty($this->items))) {
            if (!empty($redirectUrl)) {
                return redirect($redirectUrl);
            }
            return true;
        }

        return false;
    }

    /**
     * Get label settings from database
     */
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
            'label_barcode_type'    => 'datamatrix',
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

    public function getHasSizeColumnProperty(): bool
    {
        if ($this->showSizeField) {
            return true;
        }
        foreach ($this->items as $item) {
            if (!empty($item['size'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get payload for local staging auto-save (power cut / connection loss protection)
     */
    public function getDraftPayload(): array
    {
        $unsavedItems = array_values(array_filter($this->items, fn($i) => empty($i['is_saved'])));
        $supplier = $this->selectedSupplierId ? ProductSupplier::find($this->selectedSupplierId) : null;
        $category = $this->selectedCategoryId ? CategoryList::find($this->selectedCategoryId) : null;

        $totalCost = array_reduce($unsavedItems, fn($c, $i) => $c + (floatval($i['cost_price'] ?? 0) * intval($i['quantity'] ?? 1)), 0);

        return [
            'timestamp' => now()->toIso8601String(),
            'formatted_time' => now()->format('d M Y, h:i A'),
            'total_items' => count($unsavedItems),
            'total_cost' => $totalCost,
            'supplier_name' => $supplier ? $supplier->name : 'Default Supplier',
            'category_name' => $category ? $category->category_name : 'Default Category',
            'items' => $unsavedItems,
            'selectedCategoryId' => $this->selectedCategoryId,
            'selectedItemName' => $this->selectedItemName,
            'selectedSupplierId' => $this->selectedSupplierId,
            'importSupplierId' => $this->importSupplierId,
            'costMultiplier' => $this->costMultiplier,
            'sellingMultiplier' => $this->sellingMultiplier,
            'purchaseDate' => $this->purchaseDate,
            'showSizeField' => $this->showSizeField,
            'quickSize' => $this->quickSize,
            'purchasingNumber' => $this->purchasingNumber,
        ];
    }

    /**
     * Resume unsaved batch from local storage stage
     */
    public function resumeDraft(array $draft)
    {
        if (empty($draft['items'])) {
            return;
        }

        $this->items = $draft['items'];
        if (isset($draft['selectedCategoryId'])) $this->selectedCategoryId = $draft['selectedCategoryId'];
        if (isset($draft['selectedItemName'])) $this->selectedItemName = $draft['selectedItemName'];
        if (isset($draft['selectedSupplierId'])) $this->selectedSupplierId = $draft['selectedSupplierId'];
        if (isset($draft['importSupplierId'])) $this->importSupplierId = $draft['importSupplierId'];
        if (isset($draft['costMultiplier'])) $this->costMultiplier = $draft['costMultiplier'];
        if (isset($draft['sellingMultiplier'])) $this->sellingMultiplier = $draft['sellingMultiplier'];
        if (isset($draft['purchaseDate'])) $this->purchaseDate = $draft['purchaseDate'];
        if (isset($draft['showSizeField'])) $this->showSizeField = (bool) $draft['showSizeField'];
        if (isset($draft['quickSize'])) $this->quickSize = $draft['quickSize'];
        if (!empty($draft['purchasingNumber'])) $this->purchasingNumber = $draft['purchasingNumber'];

        $this->isSaved = false;
        $this->updateSellingPricePreview();

        $this->dispatch('draft-resumed', count($this->items));
        $this->js("setTimeout(renderQRCodes, 150);");
    }

    /**
     * Discard draft
     */
    public function discardDraft()
    {
        $this->clearBatch();
        $this->dispatch('draft-cleared');
    }

    /**
     * Save current uncommitted draft state to session and sync frontend
     */
    public function saveDraftState()
    {
        $unsavedCount = $this->getUnsavedItemsCount();
        if ($unsavedCount > 0 && !$this->isSaved) {
            $payload = $this->getDraftPayload();
            session(['fast_product_stage_draft' => $payload]);
            $this->dispatch('draft-updated', $payload);
        }
    }

    /**
     * Livewire rendered lifecycle hook - automatically keeps frontend stage and session in sync
     */
    public function rendered()
    {
        $this->saveDraftState();
    }

    public function render()
    {
        $categories = CategoryList::orderBy('category_name')->get();
        $suppliers = ProductSupplier::where('status', 'active')->orderBy('name')->get();

        $totalItems = count($this->items);
        $totalQty = array_sum(array_column($this->items, 'quantity'));
        $totalCost = array_reduce($this->items, fn($c, $i) => $c + (floatval($i['cost_price'] ?? 0) * intval($i['quantity'] ?? 0)), 0);
        $totalRetail = array_reduce($this->items, fn($c, $i) => $c + (floatval($i['selling_price'] ?? 0) * intval($i['quantity'] ?? 0)), 0);

        $receivedItems = array_filter($this->items, fn($i) => ($i['receive_status'] ?? 'received') === 'received');
        $missedItems = array_filter($this->items, fn($i) => ($i['receive_status'] ?? 'received') === 'notreceived');

        $receivedItemsCount = count($receivedItems);
        $receivedQty = array_sum(array_column($receivedItems, 'quantity'));
        $receivedCost = array_reduce($receivedItems, fn($c, $i) => $c + (floatval($i['cost_price'] ?? 0) * intval($i['quantity'] ?? 0)), 0);

        $missedItemsCount = count($missedItems);
        $missedQty = array_sum(array_column($missedItems, 'quantity'));
        $missedCost = array_reduce($missedItems, fn($c, $i) => $c + (floatval($i['cost_price'] ?? 0) * intval($i['quantity'] ?? 0)), 0);

        return view('livewire.admin.fast-product-create', [
            'categories' => $categories,
            'suppliers' => $suppliers,
            'labelSettings' => $this->getLabelSettings(),
            'totalItems' => $totalItems,
            'totalQty' => $totalQty,
            'totalCost' => $totalCost,
            'totalRetail' => $totalRetail,
            'receivedItemsCount' => $receivedItemsCount,
            'receivedQty' => $receivedQty,
            'receivedCost' => $receivedCost,
            'missedItemsCount' => $missedItemsCount,
            'missedQty' => $missedQty,
            'missedCost' => $missedCost,
            'unsavedCount' => $this->getUnsavedItemsCount(),
            'hasSizeColumn' => $this->hasSizeColumn,
        ])->layout($this->layout);
    }
}
