<?php

namespace App\Livewire\Salesman;

use App\Models\ProductDetail;
use App\Models\CategoryList;
use App\Models\SaleItem;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Product List')]
#[Layout('components.layouts.salesman')]
class SalesmanProductList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $categories = [];

    // Product detail modal
    public $selectedProduct = null;
    public $showProductModal = false;

    public function mount()
    {
        $this->categories = CategoryList::orderBy('category_name')->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function viewProduct($productId)
    {
        $this->selectedProduct = ProductDetail::with(['stock', 'price', 'category', 'variant', 'brand'])
            ->find($productId);
        $this->showProductModal = true;
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->selectedProduct = null;
    }

    public function loadProducts()
    {
        $query = ProductDetail::with(['stock', 'price', 'category', 'variant', 'stocks', 'prices'])
            ->where('status', 'active');

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('code', 'like', $searchTerm)
                  ->orWhereHas('variant', function ($vq) use ($searchTerm) {
                      $vq->where('variant_values', 'like', $searchTerm);
                  });
            });
        }

        $items = [];
        $products = $query->get();

        // Fetch all pending quantities in a single query (Fixes N+1 issue)
        $pendingSales = SaleItem::select('product_id', 'variant_value', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('sale', function ($q) {
                $q->where('status', 'pending');
            })
            ->groupBy('product_id', 'variant_value')
            ->get()
            ->keyBy(function ($item) {
                return $item->product_id . '_' . ($item->variant_value ?: 'null');
            });

        foreach ($products as $product) {
            // If product has variant stocks, expand each variant
            if (($product->variant_id ?? null) !== null && $product->stocks && $product->stocks->isNotEmpty()) {
                $orderedValues = [];
                if ($product->variant && is_array($product->variant->variant_values)) {
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

                        $key = $product->id . '_' . ($stock->variant_value ?: 'null');
                        $pendingQty = $pendingSales->has($key) ? $pendingSales[$key]->total_qty : 0;

                        $availableStock = max(0, ($stock->available_stock ?? 0) - $pendingQty);

                        $items[] = [
                            'id' => $product->id . '::' . $stock->variant_value,
                            'product_id' => $product->id,
                            'variant_value' => $stock->variant_value,
                            'name' => $product->name . ' (' . $stock->variant_value . ')',
                            'code' => $product->code,
                            'image' => $product->image ?? '',
                            'category' => $product->category->name ?? 'N/A',
                            'distributor_price' => $priceRecord->distributor_price ?? $priceRecord->wholesale_price ?? 0,
                            'stock' => $availableStock,
                            'pending' => $pendingQty,
                        ];
                    }

                    foreach ($stocksByValue as $v => $stock) {
                        if (in_array($v, $orderedValues)) continue;
                        $priceRecord = $product->prices->firstWhere('variant_value', $stock->variant_value) ?? $product->price;

                        $key = $product->id . '_' . ($stock->variant_value ?: 'null');
                        $pendingQty = $pendingSales->has($key) ? $pendingSales[$key]->total_qty : 0;

                        $availableStock = max(0, ($stock->available_stock ?? 0) - $pendingQty);

                        $items[] = [
                            'id' => $product->id . '::' . $stock->variant_value,
                            'product_id' => $product->id,
                            'variant_value' => $stock->variant_value,
                            'name' => $product->name . ' (' . $stock->variant_value . ')',
                            'code' => $product->code,
                            'image' => $product->image ?? '',
                            'category' => $product->category->name ?? 'N/A',
                            'distributor_price' => $priceRecord->distributor_price ?? $priceRecord->wholesale_price ?? 0,
                            'stock' => $availableStock,
                            'pending' => $pendingQty,
                        ];
                    }
                } else {
                    foreach ($product->stocks as $stock) {
                        $priceRecord = $product->prices->firstWhere('variant_value', $stock->variant_value) ?? $product->price;

                        $key = $product->id . '_' . ($stock->variant_value ?: 'null');
                        $pendingQty = $pendingSales->has($key) ? $pendingSales[$key]->total_qty : 0;

                        $availableStock = max(0, ($stock->available_stock ?? 0) - $pendingQty);

                        $items[] = [
                            'id' => $product->id . '::' . $stock->variant_value,
                            'product_id' => $product->id,
                            'variant_value' => $stock->variant_value,
                            'name' => $product->name . ' (' . $stock->variant_value . ')',
                            'code' => $product->code,
                            'image' => $product->image ?? '',
                            'category' => $product->category->name ?? 'N/A',
                            'distributor_price' => $priceRecord->distributor_price ?? $priceRecord->wholesale_price ?? 0,
                            'stock' => $availableStock,
                            'pending' => $pendingQty,
                        ];
                    }
                }
            } else {
                // Single product without variants
                $priceRecord = $product->price;
                $stockQty = $product->stock->available_stock ?? 0;

                $key = $product->id . '_null';
                // Some pending sales might not have variant_value explicitly set to null but rather empty string, so we sum all for the product if it doesn't have variants
                // However, the key lookup is simplest:
                $pendingQty = $pendingSales->has($key) ? $pendingSales[$key]->total_qty : 0;
                // Add fallback for empty string key
                if ($pendingQty == 0) {
                    $keyEmpty = $product->id . '_';
                    $pendingQty = $pendingSales->has($keyEmpty) ? $pendingSales[$keyEmpty]->total_qty : 0;
                }

                $availableStock = max(0, $stockQty - $pendingQty);

                $items[] = [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'variant_value' => null,
                    'name' => $product->name,
                    'code' => $product->code,
                    'image' => $product->image ?? '',
                    'category' => $product->category->name ?? 'N/A',
                    'distributor_price' => $priceRecord->distributor_price ?? $priceRecord->wholesale_price ?? 0,
                    'stock' => $availableStock,
                    'pending' => $pendingQty,
                ];
            }
        }

        return $items;
    }

    public function render()
    {
        $products = $this->loadProducts();

        return view('livewire.salesman.salesman-product-list', [
            'products' => $products,
            'categories' => $this->categories,
        ]);
    }
}
