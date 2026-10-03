<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\ProductDetail;
use App\Models\ProductStock;
use App\Models\ProductPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.admin')]
#[Title('Product Transfer')]
class ProductTransfer extends Component
{
    use WithPagination;

    // ─── Tab Management ─────────────────────────────
    public $activeTab = 'create'; // 'create' | 'history' | 'shops'

    // ─── Destination Shop ───────────────────────────
    public $shops = [];
    public $selectedShopId = '';
    public $newShopName = '';
    public $newShopCode = '';
    public $newShopPhone = '';
    public $newShopAddress = '';
    public $newShopDbDatabase = '';
    public $newShopNotes = '';
    public $showAddShopModal = false;

    // ─── Transfer Cart ──────────────────────────────
    public $cart = [];
    public $transferDate;
    public $transferNotes = '';

    // ─── Product Search ─────────────────────────────
    public $productSearch = '';
    public $searchResults = [];

    // ─── History ────────────────────────────────────
    public $historySearch = '';
    public $viewTransferId = null;
    public $viewTransferItems = [];
    public $showViewModal = false;

    // ─── Stats ──────────────────────────────────────
    public $totalTransfers = 0;
    public $totalItemsTransferred = 0;
    public $totalShops = 0;

    public function mount()
    {
        $this->transferDate = now()->format('Y-m-d');
        $this->loadShops();
        $this->loadStats();
    }

    public function loadShops()
    {
        if (\Schema::hasTable('destination_shops')) {
            $this->shops = DB::table('destination_shops')->where('is_active', true)->get()->toArray();
        } else {
            $this->shops = [];
        }
        $this->totalShops = count($this->shops);
    }

    public function loadStats()
    {
        if (\Schema::hasTable('product_transfers')) {
            $this->totalTransfers = DB::table('product_transfers')->count();
            $this->totalItemsTransferred = DB::table('product_transfers')->sum('total_quantity');
        }
    }

    // ─── Product Search ─────────────────────────────
    public function updatedProductSearch()
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = ProductDetail::where(function ($q) {
            $q->where('name', 'like', '%' . $this->productSearch . '%')
              ->orWhere('code', 'like', '%' . $this->productSearch . '%')
              ->orWhere('barcode', 'like', '%' . $this->productSearch . '%');
        })
        ->with(['stock', 'price'])
        ->limit(10)
        ->get()
        ->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code ?? '',
                'barcode' => $p->barcode ?? '',
                'available_stock' => $p->stock->available_stock ?? 0,
                'cost_price' => $p->price->supplier_price ?? 0,
                'selling_price' => $p->price->selling_price ?? 0,
                'image' => $p->image ?? null,
            ];
        })
        ->toArray();
    }

    public function addToCart($productId)
    {
        // Check if already in cart
        foreach ($this->cart as $key => $item) {
            if ($item['product_id'] == $productId) {
                $this->dispatch('show-toast', type: 'info', message: 'Product already in transfer cart.');
                return;
            }
        }

        $product = ProductDetail::with(['stock', 'price'])->find($productId);
        if (!$product) return;

        $availableStock = $product->stock->available_stock ?? 0;
        if ($availableStock <= 0) {
            $this->dispatch('show-toast', type: 'error', message: 'No stock available for this product.');
            return;
        }

        $this->cart[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'code' => $product->code ?? '',
            'barcode' => $product->barcode ?? '',
            'available_stock' => $availableStock,
            'quantity' => 1,
            'cost_price' => $product->price->supplier_price ?? 0,
            'selling_price' => $product->price->selling_price ?? 0,
        ];

        $this->productSearch = '';
        $this->searchResults = [];
        $this->dispatch('show-toast', type: 'success', message: $product->name . ' added to transfer cart.');
    }

    public function updateQuantity($index, $qty)
    {
        if (!isset($this->cart[$index])) return;

        $qty = max(1, intval($qty));
        $maxStock = $this->cart[$index]['available_stock'];

        if ($qty > $maxStock) {
            $qty = $maxStock;
            $this->dispatch('show-toast', type: 'warning', message: 'Cannot exceed available stock (' . $maxStock . ')');
        }

        $this->cart[$index]['quantity'] = $qty;
    }

    public function incrementQty($index)
    {
        if (!isset($this->cart[$index])) return;
        $this->updateQuantity($index, $this->cart[$index]['quantity'] + 1);
    }

    public function decrementQty($index)
    {
        if (!isset($this->cart[$index])) return;
        $this->updateQuantity($index, $this->cart[$index]['quantity'] - 1);
    }

    public function removeFromCart($index)
    {
        if (isset($this->cart[$index])) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        }
    }

    public function clearCart()
    {
        $this->cart = [];
    }

    // ─── Computed Totals ────────────────────────────
    public function getCartTotalQuantityProperty()
    {
        return array_sum(array_column($this->cart, 'quantity'));
    }

    public function getCartTotalCostProperty()
    {
        return array_sum(array_map(fn($i) => $i['quantity'] * $i['cost_price'], $this->cart));
    }

    public function getCartTotalValueProperty()
    {
        return array_sum(array_map(fn($i) => $i['quantity'] * $i['selling_price'], $this->cart));
    }

    // ─── Execute Transfer ───────────────────────────
    public function executeTransfer()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-toast', type: 'error', message: 'Transfer cart is empty.');
            return;
        }

        if (empty($this->selectedShopId)) {
            $this->dispatch('show-toast', type: 'error', message: 'Please select a destination shop.');
            return;
        }

        if (!\Schema::hasTable('product_transfers') || !\Schema::hasTable('product_transfer_items') || !\Schema::hasTable('destination_shops')) {
            $this->dispatch('show-toast', type: 'error', message: 'Product Transfer tables not found. Please run migrations first.');
            return;
        }

        try {
            DB::beginTransaction();

            $shop = DB::table('destination_shops')->find($this->selectedShopId);
            if (!$shop) {
                $this->dispatch('show-toast', type: 'error', message: 'Destination shop not found.');
                return;
            }

            // Generate transfer number
            $todayCount = DB::table('product_transfers')
                ->whereDate('transfer_date', now()->toDateString())
                ->count();
            $transferNumber = 'TRF-' . now()->format('Ymd') . '-' . str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);

            $totalQty = $this->cartTotalQuantity;
            $totalCost = $this->cartTotalCost;
            $totalValue = $this->cartTotalValue;

            // Create transfer record
            $transferId = DB::table('product_transfers')->insertGetId([
                'transfer_number' => $transferNumber,
                'source_shop' => 'Jaffna Gold Main Shop',
                'destination_shop_id' => $shop->id,
                'destination_shop_name' => $shop->name,
                'user_id' => Auth::id(),
                'transfer_date' => $this->transferDate,
                'total_items' => count($this->cart),
                'total_quantity' => $totalQty,
                'total_cost' => $totalCost,
                'total_value' => $totalValue,
                'status' => 'completed',
                'is_synced' => false,
                'sync_log' => null,
                'notes' => $this->transferNotes ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $isSynced = false;
            $syncLog = [];

            foreach ($this->cart as $item) {
                // 1. Insert transfer item
                DB::table('product_transfer_items')->insert([
                    'product_transfer_id' => $transferId,
                    'product_id' => $item['product_id'],
                    'product_code' => $item['code'],
                    'product_name' => $item['name'],
                    'barcode' => $item['barcode'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['cost_price'],
                    'unit_price' => $item['selling_price'],
                    'subtotal' => $item['quantity'] * $item['selling_price'],
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Deduct from source stock
                $stock = ProductStock::where('product_id', $item['product_id'])->first();
                if ($stock) {
                    $stock->available_stock = max(0, $stock->available_stock - $item['quantity']);
                    $stock->total_stock = $stock->available_stock + ($stock->damage_stock ?? 0);
                    $stock->save();
                }

                // 3. Sync to target database if configured
                if (!empty($shop->db_database)) {
                    try {
                        $targetDb = $shop->db_database;

                        // Check if product exists in target DB by code
                        $targetProduct = DB::connection('mysql')
                            ->table($targetDb . '.product_details')
                            ->where('code', $item['code'])
                            ->first();

                        if ($targetProduct) {
                            // Increment stock in target DB
                            DB::connection('mysql')
                                ->table($targetDb . '.product_stocks')
                                ->where('product_id', $targetProduct->id)
                                ->increment('available_stock', $item['quantity']);

                            DB::connection('mysql')
                                ->table($targetDb . '.product_stocks')
                                ->where('product_id', $targetProduct->id)
                                ->increment('total_stock', $item['quantity']);

                            $syncLog[] = "{$item['name']} (existing) +{$item['quantity']} stock";
                        } else {
                            // Get full product from source
                            $sourceProduct = ProductDetail::find($item['product_id']);
                            $sourcePrice = ProductPrice::where('product_id', $item['product_id'])->first();

                            if ($sourceProduct) {
                                // Ensure category exists in target
                                if ($sourceProduct->category_id) {
                                    $sourceCategory = DB::table('category_lists')->find($sourceProduct->category_id);
                                    if ($sourceCategory) {
                                        $targetCategory = DB::connection('mysql')
                                            ->table($targetDb . '.category_lists')
                                            ->where('category_name', $sourceCategory->category_name)
                                            ->first();
                                        if (!$targetCategory) {
                                            DB::connection('mysql')
                                                ->table($targetDb . '.category_lists')
                                                ->insert([
                                                    'category_name' => $sourceCategory->category_name,
                                                    'created_at' => now(),
                                                    'updated_at' => now(),
                                                ]);
                                        }
                                    }
                                }

                                // Ensure brand exists in target
                                if ($sourceProduct->brand_id) {
                                    $sourceBrand = DB::table('brand_lists')->find($sourceProduct->brand_id);
                                    if ($sourceBrand) {
                                        $targetBrand = DB::connection('mysql')
                                            ->table($targetDb . '.brand_lists')
                                            ->where('brand_name', $sourceBrand->brand_name)
                                            ->first();
                                        if (!$targetBrand) {
                                            DB::connection('mysql')
                                                ->table($targetDb . '.brand_lists')
                                                ->insert([
                                                    'brand_name' => $sourceBrand->brand_name,
                                                    'created_at' => now(),
                                                    'updated_at' => now(),
                                                ]);
                                        }
                                    }
                                }

                                // Insert product into target DB
                                $newProductId = DB::connection('mysql')
                                    ->table($targetDb . '.product_details')
                                    ->insertGetId([
                                        'name' => $sourceProduct->name,
                                        'code' => $sourceProduct->code,
                                        'barcode' => $sourceProduct->barcode,
                                        'category_id' => $sourceProduct->category_id,
                                        'brand_id' => $sourceProduct->brand_id,
                                        'description' => $sourceProduct->description,
                                        'image' => $sourceProduct->getRawOriginal('image'),
                                        'status' => 'active',
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);

                                // Insert price
                                if ($sourcePrice) {
                                    DB::connection('mysql')
                                        ->table($targetDb . '.product_prices')
                                        ->insert([
                                            'product_id' => $newProductId,
                                            'supplier_price' => $sourcePrice->supplier_price,
                                            'selling_price' => $sourcePrice->selling_price,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                }

                                // Insert stock
                                DB::connection('mysql')
                                    ->table($targetDb . '.product_stocks')
                                    ->insert([
                                        'product_id' => $newProductId,
                                        'available_stock' => $item['quantity'],
                                        'total_stock' => $item['quantity'],
                                        'sold_count' => 0,
                                        'damage_stock' => 0,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);

                                $syncLog[] = "{$item['name']} (NEW in target) +{$item['quantity']} stock";
                            }
                        }

                        $isSynced = true;
                    } catch (\Throwable $e) {
                        $syncLog[] = "SYNC ERROR for {$item['name']}: " . $e->getMessage();
                        Log::error('Product Transfer DB Sync Error: ' . $e->getMessage());
                    }
                }
            }

            // Update sync status
            DB::table('product_transfers')->where('id', $transferId)->update([
                'is_synced' => $isSynced,
                'sync_log' => !empty($syncLog) ? json_encode($syncLog) : null,
            ]);

            // Clear API product cache
            try {
                \App\Http\Controllers\ProductApiController::clearCache();
            } catch (\Throwable $e) {}

            DB::commit();

            // Reset form
            $this->cart = [];
            $this->transferNotes = '';
            $this->productSearch = '';
            $this->searchResults = [];
            $this->loadStats();

            $syncMsg = $isSynced ? ' Stock synced to ' . $shop->name . ' database!' : '';
            $this->dispatch('show-toast', type: 'success', message: "Transfer {$transferNumber} completed! {$totalQty} items transferred.{$syncMsg}");
            $this->js("Swal.fire({icon:'success', title:'Transfer Complete!', html:'<strong>{$transferNumber}</strong><br>{$totalQty} items transferred to <strong>{$shop->name}</strong>{$syncMsg}', confirmButtonText:'OK'})");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product Transfer Error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Transfer failed: ' . $e->getMessage());
        }
    }

    // ─── Add New Shop ───────────────────────────────
    public function openAddShopModal()
    {
        $this->showAddShopModal = true;
    }

    public function closeAddShopModal()
    {
        $this->showAddShopModal = false;
        $this->reset(['newShopName', 'newShopCode', 'newShopPhone', 'newShopAddress', 'newShopDbDatabase', 'newShopNotes']);
    }

    public function saveNewShop()
    {
        if (empty($this->newShopName)) {
            $this->dispatch('show-toast', type: 'error', message: 'Shop name is required.');
            return;
        }

        if (!\Schema::hasTable('destination_shops')) {
            $this->dispatch('show-toast', type: 'error', message: 'Destination shops table not found. Please run migrations first.');
            return;
        }

        DB::table('destination_shops')->insert([
            'name' => $this->newShopName,
            'code' => $this->newShopCode ?: strtoupper(substr($this->newShopName, 0, 3)),
            'phone' => $this->newShopPhone ?: null,
            'address' => $this->newShopAddress ?: null,
            'db_database' => $this->newShopDbDatabase ?: null,
            'is_active' => true,
            'notes' => $this->newShopNotes ?: null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->closeAddShopModal();
        $this->loadShops();
        $this->dispatch('show-toast', type: 'success', message: 'Destination shop added!');
    }

    public function toggleShopActive($shopId)
    {
        if (!\Schema::hasTable('destination_shops')) return;

        $shop = DB::table('destination_shops')->find($shopId);
        if ($shop) {
            DB::table('destination_shops')->where('id', $shopId)->update([
                'is_active' => !$shop->is_active,
                'updated_at' => now(),
            ]);
            $this->loadShops();
        }
    }

    // ─── View Transfer Detail ───────────────────────
    public function viewTransfer($transferId)
    {
        if (!\Schema::hasTable('product_transfers')) return;

        $this->viewTransferId = $transferId;
        $this->viewTransferItems = DB::table('product_transfer_items')
            ->where('product_transfer_id', $transferId)
            ->get()
            ->toArray();
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewTransferId = null;
        $this->viewTransferItems = [];
    }

    // ─── Cancel Transfer (Restore Stock) ────────────
    public function cancelTransfer($transferId)
    {
        if (!\Schema::hasTable('product_transfers')) return;

        try {
            DB::beginTransaction();

            $transfer = DB::table('product_transfers')->find($transferId);
            if (!$transfer || $transfer->status === 'cancelled') {
                $this->dispatch('show-toast', type: 'error', message: 'Transfer not found or already cancelled.');
                return;
            }

            $items = DB::table('product_transfer_items')
                ->where('product_transfer_id', $transferId)
                ->get();

            foreach ($items as $item) {
                // Restore source stock
                $stock = ProductStock::where('product_id', $item->product_id)->first();
                if ($stock) {
                    $stock->available_stock += $item->quantity;
                    $stock->total_stock = $stock->available_stock + ($stock->damage_stock ?? 0);
                    $stock->save();
                }

                // Revert target DB stock if was synced
                if ($transfer->is_synced) {
                    $shop = DB::table('destination_shops')->find($transfer->destination_shop_id);
                    if ($shop && !empty($shop->db_database)) {
                        try {
                            $targetProduct = DB::connection('mysql')
                                ->table($shop->db_database . '.product_details')
                                ->where('code', $item->product_code)
                                ->first();

                            if ($targetProduct) {
                                DB::connection('mysql')
                                    ->table($shop->db_database . '.product_stocks')
                                    ->where('product_id', $targetProduct->id)
                                    ->decrement('available_stock', $item->quantity);

                                DB::connection('mysql')
                                    ->table($shop->db_database . '.product_stocks')
                                    ->where('product_id', $targetProduct->id)
                                    ->decrement('total_stock', $item->quantity);
                            }
                        } catch (\Throwable $e) {
                            Log::error('Revert sync error: ' . $e->getMessage());
                        }
                    }
                }
            }

            DB::table('product_transfers')->where('id', $transferId)->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);

            // Clear cache
            try { \App\Http\Controllers\ProductApiController::clearCache(); } catch (\Throwable $e) {}

            DB::commit();
            $this->loadStats();
            $this->dispatch('show-toast', type: 'success', message: 'Transfer cancelled. Stock restored to source.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Cancel failed: ' . $e->getMessage());
        }
    }

    // ─── History Query ──────────────────────────────
    public function getTransfersProperty()
    {
        if (!\Schema::hasTable('product_transfers')) {
            return collect([]);
        }

        $query = DB::table('product_transfers')
            ->orderBy('created_at', 'desc');

        if ($this->historySearch) {
            $query->where(function ($q) {
                $q->where('transfer_number', 'like', '%' . $this->historySearch . '%')
                  ->orWhere('destination_shop_name', 'like', '%' . $this->historySearch . '%');
            });
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.product-transfer', [
            'transfers' => $this->transfers,
        ]);
    }
}
