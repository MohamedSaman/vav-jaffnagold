<?php

namespace App\Livewire\ShopStaff;

use App\Models\ProductDetail;
use App\Models\CategoryList;
use App\Models\SaleItem;
use App\Models\HandmadeItem;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Product List')]
#[Layout('components.layouts.shop-staff')]
class ShopStaffProductList extends Component
{
    use WithPagination;

    public $activeTab = 'all'; // 'all' or 'handmade'
    public $search = '';
    public $categoryFilter = '';
    public $stockFilter = 'all'; // all, in_stock, low_stock, out_of_stock
    public $handmadeStageFilter = 'all'; // all, in_pos_stock, ready_for_stock, plating, stone_setting, raw_in_stock
    public $categories = [];

    public $selectedHandmadeItemId = null;
    public $showHandmadeDetailModal = false;

    public function mount()
    {
        $this->categories = CategoryList::orderBy('category_name')->get();

        // Optional tab query parameter support
        if (request()->query('tab') === 'handmade') {
            $this->activeTab = 'handmade';
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatedStockFilter()
    {
        $this->resetPage();
    }

    public function updatedHandmadeStageFilter()
    {
        $this->resetPage();
    }

    public function viewHandmadeDetail($id)
    {
        $this->selectedHandmadeItemId = $id;
        $this->showHandmadeDetailModal = true;
    }

    public function closeHandmadeDetailModal()
    {
        $this->showHandmadeDetailModal = false;
        $this->selectedHandmadeItemId = null;
    }

    public function loadProducts()
    {
        $query = ProductDetail::with(['stock', 'price', 'category', 'variant', 'stocks', 'prices', 'handmadeItem']);

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        $items = [];
        $products = $query->get();

        foreach ($products as $product) {
            // If product has variant stocks, expand each variant
            if (($product->variant_id ?? null) !== null && $product->stocks && $product->stocks->isNotEmpty()) {
                $orderedValues = [];
                if ($product->variant && is_array($product->variant->variant_values) && count($product->variant->variant_values) > 0) {
                    $orderedValues = $product->variant->variant_values;
                }

                $stocksByValue = [];
                foreach ($product->stocks as $stock) {
                    $stocksByValue[$stock->variant_value] = $stock;
                }

                if (!empty($orderedValues)) {
                    foreach ($orderedValues as $val) {
                        if (!isset($stocksByValue[$val])) continue;
                        $stock = $stocksByValue[$val];
                        $priceRecord = $product->prices->firstWhere('variant_value', $stock->variant_value) ?? $product->price;

                        $pendingQty = SaleItem::whereHas('sale', function ($q) {
                            $q->where('status', 'pending');
                        })
                            ->where('product_id', $product->id)
                            ->where('variant_value', $stock->variant_value)
                            ->sum('quantity');

                        $availableStock = max(0, ($stock->available_stock ?? 0) - $pendingQty);

                        // Apply stock filter
                        if ($this->stockFilter === 'in_stock' && $availableStock <= 10) continue;
                        if ($this->stockFilter === 'low_stock' && ($availableStock <= 0 || $availableStock > 10)) continue;
                        if ($this->stockFilter === 'out_of_stock' && $availableStock > 0) continue;

                        $items[] = [
                            'id' => $product->id . '::' . $stock->variant_value,
                            'product_id' => $product->id,
                            'variant_value' => $stock->variant_value,
                            'name' => $product->name . ' (' . $stock->variant_value . ')',
                            'code' => $product->code,
                            'barcode' => $product->barcode ?: $product->code,
                            'image' => $product->image ?? '',
                            'category' => $product->category->category_name ?? 'N/A',
                            'retail_price' => $priceRecord->retail_price ?? 0,
                            'wholesale_price' => $priceRecord->wholesale_price ?? 0,
                            'stock' => $availableStock,
                            'pending' => $pendingQty,
                            'is_handmade' => ($product->model === 'Handmade Jewelry' || $product->handmadeItem !== null),
                        ];
                    }

                    foreach ($stocksByValue as $v => $stock) {
                        if (in_array($v, $orderedValues)) continue;
                        $priceRecord = $product->prices->firstWhere('variant_value', $stock->variant_value) ?? $product->price;

                        $pendingQty = SaleItem::whereHas('sale', function ($q) {
                            $q->where('status', 'pending');
                        })
                            ->where('product_id', $product->id)
                            ->where('variant_value', $stock->variant_value)
                            ->sum('quantity');

                        $availableStock = max(0, ($stock->available_stock ?? 0) - $pendingQty);

                        // Apply stock filter
                        if ($this->stockFilter === 'in_stock' && $availableStock <= 10) continue;
                        if ($this->stockFilter === 'low_stock' && ($availableStock <= 0 || $availableStock > 10)) continue;
                        if ($this->stockFilter === 'out_of_stock' && $availableStock > 0) continue;

                        $items[] = [
                            'id' => $product->id . '::' . $stock->variant_value,
                            'product_id' => $product->id,
                            'variant_value' => $stock->variant_value,
                            'name' => $product->name . ' (' . $stock->variant_value . ')',
                            'code' => $product->code,
                            'barcode' => $product->barcode ?: $product->code,
                            'image' => $product->image ?? '',
                            'category' => $product->category->category_name ?? 'N/A',
                            'retail_price' => $priceRecord->retail_price ?? 0,
                            'wholesale_price' => $priceRecord->wholesale_price ?? 0,
                            'stock' => $availableStock,
                            'pending' => $pendingQty,
                            'is_handmade' => ($product->model === 'Handmade Jewelry' || $product->handmadeItem !== null),
                        ];
                    }
                } else {
                    foreach ($product->stocks as $stock) {
                        if (($stock->available_stock ?? 0) <= 0) continue;
                        $priceRecord = $product->prices->firstWhere('variant_value', $stock->variant_value) ?? $product->price;

                        $pendingQty = SaleItem::whereHas('sale', function ($q) {
                            $q->where('status', 'pending');
                        })
                            ->where('product_id', $product->id)
                            ->where('variant_value', $stock->variant_value)
                            ->sum('quantity');

                        $availableStock = max(0, ($stock->available_stock ?? 0) - $pendingQty);

                        // Apply stock filter
                        if ($this->stockFilter === 'in_stock' && $availableStock <= 10) continue;
                        if ($this->stockFilter === 'low_stock' && ($availableStock <= 0 || $availableStock > 10)) continue;
                        if ($this->stockFilter === 'out_of_stock' && $availableStock > 0) continue;

                        $items[] = [
                            'id' => $product->id . '::' . $stock->variant_value,
                            'product_id' => $product->id,
                            'variant_value' => $stock->variant_value,
                            'name' => $product->name . ' (' . $stock->variant_value . ')',
                            'code' => $product->code,
                            'barcode' => $product->barcode ?: $product->code,
                            'image' => $product->image ?? '',
                            'category' => $product->category->category_name ?? 'N/A',
                            'retail_price' => $priceRecord->retail_price ?? 0,
                            'wholesale_price' => $priceRecord->wholesale_price ?? 0,
                            'stock' => $availableStock,
                            'pending' => $pendingQty,
                            'is_handmade' => ($product->model === 'Handmade Jewelry' || $product->handmadeItem !== null),
                        ];
                    }
                }
            } else {
                // Single product without variants
                $priceRecord = $product->price;
                $stockQty = $product->stock->available_stock ?? 0;

                $pendingQty = SaleItem::whereHas('sale', function ($q) {
                    $q->where('status', 'pending');
                })
                    ->where('product_id', $product->id)
                    ->sum('quantity');

                $availableStock = max(0, $stockQty - $pendingQty);

                // Apply stock filter
                if ($this->stockFilter === 'in_stock' && $availableStock <= 10) continue;
                if ($this->stockFilter === 'low_stock' && ($availableStock <= 0 || $availableStock > 10)) continue;
                if ($this->stockFilter === 'out_of_stock' && $availableStock > 0) continue;

                $items[] = [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'variant_value' => null,
                    'name' => $product->name,
                    'code' => $product->code,
                    'barcode' => $product->barcode ?: $product->code,
                    'image' => $product->image ?? '',
                    'category' => $product->category->category_name ?? 'N/A',
                    'retail_price' => $priceRecord->retail_price ?? 0,
                    'wholesale_price' => $priceRecord->wholesale_price ?? 0,
                    'stock' => $availableStock,
                    'pending' => $pendingQty,
                    'is_handmade' => ($product->model === 'Handmade Jewelry' || $product->handmadeItem !== null),
                ];
            }
        }

        // Filter items by search term (including variant values)
        if ($this->search) {
            $searchTerm = strtolower(trim($this->search));
            $items = array_filter($items, function ($item) use ($searchTerm) {
                return str_contains(strtolower($item['name']), $searchTerm)
                    || str_contains(strtolower($item['code']), $searchTerm)
                    || str_contains(strtolower($item['barcode']), $searchTerm)
                    || (isset($item['variant_value']) && str_contains(strtolower($item['variant_value']), $searchTerm));
            });
        }

        return $items;
    }

    public function loadHandmadeProducts()
    {
        $query = HandmadeItem::with([
            'supplier',
            'category',
            'platingWorker',
            'purchase',
            'productDetail.stock',
            'productDetail.price'
        ]);

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->handmadeStageFilter && $this->handmadeStageFilter !== 'all') {
            $query->where('status', $this->handmadeStageFilter);
        }

        if ($this->stockFilter && $this->stockFilter !== 'all') {
            if ($this->stockFilter === 'in_stock') {
                $query->where('status', 'in_pos_stock')
                      ->whereHas('productDetail.stock', function ($sq) {
                          $sq->where('available_stock', '>', 0);
                      });
            } elseif ($this->stockFilter === 'low_stock') {
                $query->where('status', 'in_pos_stock')
                      ->whereHas('productDetail.stock', function ($sq) {
                          $sq->where('available_stock', '>', 0)
                             ->where('available_stock', '<=', 10);
                      });
            } elseif ($this->stockFilter === 'out_of_stock') {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'in_pos_stock')
                      ->orWhereHas('productDetail.stock', function ($sq) {
                          $sq->where('available_stock', '<=', 0);
                      });
                });
            }
        }

        if ($this->search) {
            $searchTerm = trim($this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('item_code', 'like', "%{$searchTerm}%")
                  ->orWhere('item_name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('supplier', function ($sq) use ($searchTerm) {
                      $sq->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
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
            'label_barcode_type'    => 'qrcode_m', // 'datamatrix', 'qrcode_l', 'qrcode_m', 'code128'
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

    public function render()
    {
        $products = $this->activeTab === 'all' ? $this->loadProducts() : [];
        $handmadeItems = $this->activeTab === 'handmade' ? $this->loadHandmadeProducts() : [];
        $selectedHandmadeItem = $this->selectedHandmadeItemId 
            ? HandmadeItem::with(['supplier', 'category', 'platingWorker', 'purchase', 'productDetail.stock', 'productDetail.price'])->find($this->selectedHandmadeItemId) 
            : null;

        $totalCatalogCount = ProductDetail::count();
        $totalHandmadeCount = HandmadeItem::count();
        $handmadeInStockCount = HandmadeItem::where('status', 'in_pos_stock')->count();
        $handmadeInProgressCount = HandmadeItem::where('status', '!=', 'in_pos_stock')->count();
        $handmadeTotalCostValue = HandmadeItem::sum('total_accumulated_cost');

        return view('livewire.shop-staff.shop-staff-product-list', [
            'products' => $products,
            'handmadeItems' => $handmadeItems,
            'selectedHandmadeItem' => $selectedHandmadeItem,
            'categories' => $this->categories,
            'totalCatalogCount' => $totalCatalogCount,
            'totalHandmadeCount' => $totalHandmadeCount,
            'handmadeInStockCount' => $handmadeInStockCount,
            'handmadeInProgressCount' => $handmadeInProgressCount,
            'handmadeTotalCostValue' => $handmadeTotalCostValue,
            'labelSettings' => $this->getLabelSettings(),
        ]);
    }
}
