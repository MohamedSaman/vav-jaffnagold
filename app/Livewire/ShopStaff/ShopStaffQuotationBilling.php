<?php

namespace App\Livewire\ShopStaff;

use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\Quotation;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Create Quotation')]
#[Layout('components.layouts.shop-staff')]
class ShopStaffQuotationBilling extends Component
{
    use WithFileUploads;

    // Basic Properties
    public $search = '';

    public $searchResults = [];

    public $relatedProducts = [];

    public $customerId = '';

    // Categories and Products for Grid
    public $categories = [];

    public $selectedCategory = null;

    public $brands = [];

    public $selectedBrand = null;

    public $products = [];

    // UI: sliding panels (opened via filter buttons)
    public $showCategoryPanel = false;

    public $showBrandPanel = false;

    // Cart Items
    public $cart = [];

    // Customer Properties
    public $customers = [];

    public $selectedCustomer = null;

    // Customer Form (for new customer - only used in modal)
    public $customerName = '';

    public $customerPhone = '';

    public $customerEmail = '';

    public $customerAddress = '';

    public $customerType = 'retail';

    public $businessName = '';

    public $customerOpeningBalance = 0;

    // Customer Balance Properties (for selected customer display)
    public $customerOpeningBalanceDisplay = 0;

    // Quotation Properties
    public $notes = '';

    public $validUntil;

    public $termsConditions = "1. This quotation is valid for 30 days.\n2. Prices are subject to change.";

    // Discount Properties
    public $additionalDiscount = '';

    public $additionalDiscountType = 'fixed'; // 'fixed' or 'percentage'

    // Price Type Selection
    public $priceType = 'retail'; // 'retail', 'wholesale', or 'distribute'

    // Modals
    public $showQuotationModal = false;

    public $showCustomerModal = false;

    public $lastQuotationId = null;

    public $createdQuotation = null;

    public $warrantyThreshold = 1000;

    public function mount()
    {
        $this->validUntil = now()->addDays(30)->format('Y-m-d');

        // Load default lists
        $this->categories = CategoryList::withCount('products')->orderBy('category_name')->get();
        $this->brands = BrandList::withCount('products')->orderBy('brand_name')->get();
        $this->loadCustomers();
        $this->loadProducts();
        $this->setDefaultCustomer();

        // Get settings
        $threshold = Setting::where('key', 'warranty_threshold')->first();
        if ($threshold) {
            $this->warrantyThreshold = floatval($threshold->value);
        }
    }

    public function loadCustomers()
    {
        $this->customers = Customer::orderBy('name')->get();
    }

    public function setDefaultCustomer()
    {
        $walkingCustomer = Customer::where('name', 'Walking Customer')->first();
        if ($walkingCustomer) {
            $this->customerId = $walkingCustomer->id;
            $this->selectedCustomer = $walkingCustomer;
            $this->customerOpeningBalanceDisplay = $walkingCustomer->opening_balance ?? 0;
        }
    }

    public function updatedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                $this->selectedCustomer = $customer;
                $this->customerOpeningBalanceDisplay = $customer->opening_balance ?? 0;
            }
        } else {
            $this->selectedCustomer = null;
            $this->customerOpeningBalanceDisplay = 0;
        }
    }

    public function loadProducts()
    {
        $this->products = [];
    }

    /**
     * Search product by exact barcode or product code.
     */
    public function findProductByBarcodeOrCode(string $term): ?array
    {
        $cleanTerm = trim(str_replace("\xc2\xa0", ' ', $term));
        if ($cleanTerm === '') {
            return null;
        }

        $product = ProductDetail::where(function ($q) use ($cleanTerm) {
            $q->where('barcode', $cleanTerm)
                ->orWhere('code', $cleanTerm)
                ->orWhereRaw('TRIM(barcode) = ?', [$cleanTerm])
                ->orWhereRaw('TRIM(code) = ?', [$cleanTerm]);
        })
            ->where(function ($q) {
                $q->whereHas('stock', function ($sq) {
                    $sq->where('available_stock', '>', 0);
                })->orWhereHas('stocks', function ($sq) {
                    $sq->where('available_stock', '>', 0);
                });
            })
            ->with(['stock', 'price', 'category', 'stocks', 'prices', 'variant'])
            ->first();

        if (! $product) {
            return null;
        }

        return $this->formatProductForCart($product);
    }

    /**
     * Format a product model into cart/search array structure.
     */
    public function formatProductForCart(ProductDetail $product, ?string $preferredVariantValue = null): array
    {
        $isVariant = (($product->variant_id ?? null) !== null && $product->stocks && $product->stocks->isNotEmpty());

        if ($isVariant) {
            $stock = null;
            if ($preferredVariantValue) {
                $stock = $product->stocks->firstWhere('variant_value', $preferredVariantValue);
            }
            if (! $stock) {
                $stock = $product->stocks->first(fn ($s) => ($s->available_stock ?? 0) > 0) ?? $product->stocks->first();
            }

            $vValue = $stock ? $stock->variant_value : null;

            $pPrice = $product->price->selling_price ?? $product->price->retail_price ?? 0;
            $pWPrice = $product->price->wholesale_price ?? 0;
            $pDPrice = $product->price->distributor_price ?? $product->price->distribute_price ?? 0;
            $pDiscPrice = $product->price->discount_price ?? 0;

            if ($vValue && $product->prices && $product->prices->isNotEmpty()) {
                $specificPrice = $product->prices->firstWhere('variant_value', $vValue);
                if ($specificPrice) {
                    $pPrice = $specificPrice->selling_price ?? $specificPrice->retail_price ?? $pPrice;
                    $pWPrice = $specificPrice->wholesale_price ?? $pWPrice;
                    $pDPrice = $specificPrice->distributor_price ?? $specificPrice->distribute_price ?? $pDPrice;
                    $pDiscPrice = $specificPrice->discount_price ?? $pDiscPrice;
                }
            }

            return [
                'id' => $product->id,
                'name' => $product->name.($vValue ? ' ('.$vValue.')' : ''),
                'code' => $product->code,
                'barcode' => $product->barcode,
                'model' => $product->model,
                'image' => $product->image,
                'retail_price' => (float) $pPrice,
                'wholesale_price' => (float) $pWPrice,
                'distribute_price' => (float) $pDPrice,
                'discount_price' => (float) $pDiscPrice,
                'stock' => $stock ? ($stock->available_stock ?? 0) : ($product->stock->available_stock ?? 0),
                'is_variant' => true,
                'variant_id' => $product->variant_id,
                'variant_value' => $vValue,
                'category_id' => $product->category_id,
                'brand_id' => $product->brand_id,
                'has_warranty' => $product->has_warranty ?? 0,
                'warranty_duration' => $product->warranty_duration ?? null,
            ];
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'barcode' => $product->barcode,
            'model' => $product->model,
            'image' => $product->image,
            'retail_price' => (float) ($product->price->selling_price ?? $product->price->retail_price ?? 0),
            'wholesale_price' => (float) ($product->price->wholesale_price ?? 0),
            'distribute_price' => (float) ($product->price->distributor_price ?? $product->price->distribute_price ?? 0),
            'discount_price' => (float) ($product->price->discount_price ?? 0),
            'stock' => $product->stock->available_stock ?? 0,
            'is_variant' => false,
            'variant_id' => null,
            'variant_value' => null,
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'has_warranty' => $product->has_warranty ?? 0,
            'warranty_duration' => $product->warranty_duration ?? null,
        ];
    }

    /**
     * Handle instant barcode scan or Enter key press on search.
     */
    public function handleBarcodeOrSearch(?string $term = null)
    {
        $rawTerm = $term ?? $this->search;
        $cleanTerm = trim(str_replace("\xc2\xa0", ' ', $rawTerm));

        if (empty($cleanTerm)) {
            return;
        }

        // 1. Exact match on barcode or product code -> auto add to cart
        $exact = $this->findProductByBarcodeOrCode($cleanTerm);
        if ($exact) {
            $this->addToCart($exact);
            $this->showToast('success', "Added: {$exact['name']}");

            return;
        }

        // 2. Otherwise run search to populate search results
        $this->search = $cleanTerm;
        $this->updatedSearch();

        // 3. If single result found, auto add
        if (count($this->searchResults) === 1) {
            $single = $this->searchResults[0];
            $this->addToCart($single);
            $this->showToast('success', "Added: {$single['name']}");
        } elseif (empty($this->searchResults)) {
            $this->showToast('error', "No product found matching '{$cleanTerm}'");
        }
    }

    public function updatedSearch()
    {
        $searchTerm = trim(str_replace("\xc2\xa0", ' ', $this->search));

        if (strlen($searchTerm) < 2) {
            $this->searchResults = [];

            return;
        }

        // Exact barcode/code auto-add
        $exact = $this->findProductByBarcodeOrCode($searchTerm);
        if ($exact) {
            $this->addToCart($exact);
            $this->showToast('success', "Added: {$exact['name']}");

            return;
        }

        $searchWords = array_filter(explode(' ', $searchTerm));

        $matches = ProductDetail::where(function ($query) use ($searchTerm, $searchWords) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('code', 'like', "%{$searchTerm}%")
                    ->orWhere('barcode', 'like', "%{$searchTerm}%")
                    ->orWhere('model', 'like', "%{$searchTerm}%");
            })
                ->orWhereHas('stocks', function ($q) use ($searchTerm) {
                    $q->where('variant_value', 'like', "%{$searchTerm}%")
                        ->where('available_stock', '>', 0);
                })
                ->when(count($searchWords) > 1, function ($q) use ($searchWords) {
                    $q->orWhere(function ($subQuery) use ($searchWords) {
                        foreach ($searchWords as $index => $word) {
                            if ($index === 0) {
                                $subQuery->where('name', 'like', "%{$word}%");
                            } else {
                                $subQuery->whereHas('stocks', function ($stockQuery) use ($word) {
                                    $stockQuery->where('variant_value', 'like', "%{$word}%")
                                        ->where('available_stock', '>', 0);
                                });
                            }
                        }
                    });
                });
        })
            ->when($this->selectedCategory, function ($query) {
                return $query->where('category_id', $this->selectedCategory);
            })
            ->when($this->selectedBrand, function ($query) {
                return $query->where('brand_id', $this->selectedBrand);
            })
            ->where(function ($q) {
                $q->whereHas('stock', function ($sq) {
                    $sq->where('available_stock', '>', 0);
                })->orWhereHas('stocks', function ($sq) {
                    $sq->where('available_stock', '>', 0);
                });
            })
            ->with(['stock', 'price', 'category', 'stocks', 'prices', 'variant'])
            ->limit(20)
            ->get();

        $results = [];
        foreach ($matches as $product) {
            if (($product->variant_id ?? null) !== null && $product->stocks && $product->stocks->isNotEmpty()) {
                foreach ($product->stocks as $vStock) {
                    if (($vStock->available_stock ?? 0) <= 0) {
                        continue;
                    }

                    $vValue = $vStock->variant_value;
                    $pPrice = $product->price->selling_price ?? $product->price->retail_price ?? 0;
                    $pWPrice = $product->price->wholesale_price ?? 0;
                    $pDPrice = $product->price->distributor_price ?? $product->price->distribute_price ?? 0;
                    $pDiscPrice = $product->price->discount_price ?? 0;

                    if ($product->prices && $product->prices->isNotEmpty()) {
                        $specificPrice = $product->prices->firstWhere('variant_value', $vValue);
                        if ($specificPrice) {
                            $pPrice = $specificPrice->selling_price ?? $specificPrice->retail_price ?? $pPrice;
                            $pWPrice = $specificPrice->wholesale_price ?? $pWPrice;
                            $pDPrice = $specificPrice->distributor_price ?? $specificPrice->distribute_price ?? $pDPrice;
                            $pDiscPrice = $specificPrice->discount_price ?? $pDiscPrice;
                        }
                    }

                    $results[] = [
                        'id' => $product->id,
                        'name' => $product->name.' ('.$vValue.')',
                        'code' => $product->code,
                        'barcode' => $product->barcode,
                        'model' => $product->model,
                        'image' => $product->image,
                        'retail_price' => (float) $pPrice,
                        'wholesale_price' => (float) $pWPrice,
                        'distribute_price' => (float) $pDPrice,
                        'discount_price' => (float) $pDiscPrice,
                        'stock' => $vStock->available_stock ?? 0,
                        'is_variant' => true,
                        'variant_id' => $product->variant_id,
                        'variant_value' => $vValue,
                        'category_id' => $product->category_id,
                        'brand_id' => $product->brand_id,
                        'has_warranty' => $product->has_warranty ?? 0,
                        'warranty_duration' => $product->warranty_duration ?? null,
                    ];
                }
            } else {
                $results[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'barcode' => $product->barcode,
                    'model' => $product->model,
                    'image' => $product->image,
                    'retail_price' => (float) ($product->price->selling_price ?? $product->price->retail_price ?? 0),
                    'wholesale_price' => (float) ($product->price->wholesale_price ?? 0),
                    'distribute_price' => (float) ($product->price->distributor_price ?? $product->price->distribute_price ?? 0),
                    'discount_price' => (float) ($product->price->discount_price ?? 0),
                    'stock' => $product->stock->available_stock ?? 0,
                    'is_variant' => false,
                    'variant_id' => null,
                    'variant_value' => null,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'has_warranty' => $product->has_warranty ?? 0,
                    'warranty_duration' => $product->warranty_duration ?? null,
                ];
            }
        }

        $this->searchResults = $results;
    }

    public function updatedPriceType($value)
    {
        foreach ($this->cart as $index => $item) {
            if (! empty($item['is_custom'])) {
                continue;
            }

            $productRecord = ProductDetail::with(['price', 'prices'])->find($item['id']);
            if (! $productRecord) {
                continue;
            }

            $priceRecord = $productRecord->price;
            if (! empty($item['variant_value']) && $productRecord->prices && $productRecord->prices->isNotEmpty()) {
                $priceRecord = $productRecord->prices->firstWhere('variant_value', $item['variant_value']) ?? $productRecord->price;
            }

            $newPrice = match ($value) {
                'wholesale' => $priceRecord->wholesale_price ?? 0,
                'distribute' => $priceRecord->distributor_price ?? $priceRecord->distribute_price ?? 0,
                default => $priceRecord->selling_price ?? $priceRecord->retail_price ?? 0,
            };

            $this->cart[$index]['price'] = (float) $newPrice;
            $this->cart[$index]['total'] = ($newPrice - ($this->cart[$index]['discount'] ?? 0)) * $this->cart[$index]['quantity'];
        }

        if (! empty($this->search)) {
            $this->updatedSearch();
        }
    }

    public function selectCategory($catId)
    {
        $this->selectedCategory = $catId;
        $this->showCategoryPanel = false;
        if (! empty($this->search)) {
            $this->updatedSearch();
        }
    }

    public function showAllProducts()
    {
        $this->selectedCategory = null;
        $this->showCategoryPanel = false;
        if (! empty($this->search)) {
            $this->updatedSearch();
        }
    }

    public function selectBrand($brandId)
    {
        $this->selectedBrand = $brandId;
        $this->showBrandPanel = false;
        if (! empty($this->search)) {
            $this->updatedSearch();
        }
    }

    public function showAllBrands()
    {
        $this->selectedBrand = null;
        $this->showBrandPanel = false;
        if (! empty($this->search)) {
            $this->updatedSearch();
        }
    }

    public function toggleCategoryPanel()
    {
        $this->showCategoryPanel = ! $this->showCategoryPanel;
        $this->showBrandPanel = false;
    }

    public function toggleBrandPanel()
    {
        $this->showBrandPanel = ! $this->showBrandPanel;
        $this->showCategoryPanel = false;
    }

    public function getImageUrl($imagePath)
    {
        if (! $imagePath) {
            return 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSrn_80I-lMAa0pVBNmFmQ7VI6l4rr74JW-eQ&s';
        }

        return asset('storage/'.$imagePath);
    }

    // Cart Actions
    public function addToCart($product)
    {
        if (is_numeric($product)) {
            $found = collect($this->products)->firstWhere('id', (int) $product);
            if (! $found) {
                $detail = ProductDetail::with(['stock', 'price'])->find($product);
                if ($detail) {
                    $found = [
                        'id' => $detail->id,
                        'name' => $detail->name,
                        'code' => $detail->code,
                        'model' => $detail->model,
                        'barcode' => $detail->barcode,
                        'image' => $detail->image,
                        'retail_price' => $detail->price?->selling_price ?? 0,
                        'wholesale_price' => $detail->price?->wholesale_price ?? 0,
                        'distribute_price' => $detail->price?->distributor_price ?? $detail->price?->distribute_price ?? 0,
                        'discount_price' => 0,
                        'stock' => $detail->stock?->available_stock ?? 0,
                        'is_variant' => false,
                        'variant_id' => null,
                        'variant_value' => null,
                        'has_warranty' => 0,
                        'warranty_duration' => null,
                    ];
                }
            }
            $product = $found;
        }

        if (! $product || ! isset($product['id'])) {
            return;
        }

        $cartKey = $product['id'].'-'.($product['variant_value'] ?? 'none');
        $existing = collect($this->cart)->firstWhere('key', $cartKey);

        if ($existing) {
            $this->incrementQuantity($cartKey);
        } else {
            $price = match ($this->priceType) {
                'wholesale' => $product['wholesale_price'] ?? $product['price'] ?? 0,
                'distribute' => $product['distribute_price'] ?? $product['price'] ?? 0,
                default => $product['retail_price'] ?? $product['price'] ?? 0,
            };

            $discountVal = $product['discount_price'] ?? 0;
            $discountPercentage = 0;
            if ($price > 0 && $discountVal > 0) {
                $discountPercentage = ($discountVal / $price) * 100;
            }

            $this->cart[] = [
                'key' => $cartKey,
                'id' => $product['id'],
                'name' => $product['name'],
                'code' => $product['code'],
                'barcode' => $product['barcode'] ?? '',
                'model' => $product['model'] ?? '',
                'image' => $product['image'] ?? '',
                'price' => (float) $price,
                'quantity' => 1,
                'discount' => (float) $discountVal,
                'discount_percentage' => (float) $discountPercentage,
                'total' => (float) ($price - $discountVal),
                'stock' => (int) ($product['stock'] ?? 0),
                'is_variant' => ! empty($product['is_variant']),
                'variant_id' => $product['variant_id'] ?? null,
                'variant_value' => $product['variant_value'] ?? null,
                'has_warranty' => $product['has_warranty'] ?? 0,
                'warranty_duration' => $product['warranty_duration'] ?? null,
            ];
        }

        $this->search = '';
        $this->searchResults = [];
        $this->dispatch('product-added-to-cart');
    }

    public function addCustomProduct()
    {
        $customId = 'custom-'.uniqid();
        $this->cart[] = [
            'key' => $customId,
            'id' => null,
            'name' => 'Custom Product '.(count($this->cart) + 1),
            'code' => 'CUSTOM-'.now()->format('His'),
            'model' => 'Custom',
            'image' => '',
            'price' => 0,
            'quantity' => 1,
            'discount' => 0,
            'discount_percentage' => 0,
            'total' => 0,
            'stock' => 9999,
            'is_variant' => false,
            'variant_id' => null,
            'variant_value' => null,
            'is_custom' => true,
            'has_warranty' => 0,
            'warranty_duration' => null,
        ];
    }

    public function updateCustomName($key, $name)
    {
        $this->cart = collect($this->cart)->map(function ($item) use ($key, $name) {
            if ($item['key'] === $key) {
                $item['name'] = $name;
            }

            return $item;
        })->toArray();
    }

    public function updateCustomCode($key, $code)
    {
        $this->cart = collect($this->cart)->map(function ($item) use ($key, $code) {
            if ($item['key'] === $key) {
                $item['code'] = $code;
            }

            return $item;
        })->toArray();
    }

    public function incrementQuantity($key)
    {
        $this->cart = collect($this->cart)->map(function ($item) use ($key) {
            if ($item['key'] === $key) {
                if ($item['quantity'] < $item['stock']) {
                    $item['quantity'] += 1;
                    $item['total'] = ($item['price'] - $item['discount']) * $item['quantity'];
                } else {
                    $this->showToast('warning', 'Maximum stock reached.');
                }
            }

            return $item;
        })->toArray();
    }

    public function decrementQuantity($key)
    {
        $this->cart = collect($this->cart)->map(function ($item) use ($key) {
            if ($item['key'] === $key) {
                if ($item['quantity'] > 1) {
                    $item['quantity'] -= 1;
                    $item['total'] = ($item['price'] - $item['discount']) * $item['quantity'];
                }
            }

            return $item;
        })->toArray();
    }

    public function updateQuantity($key, $qty)
    {
        $qty = intval($qty);
        if ($qty < 1) {
            $qty = 1;
        }

        $this->cart = collect($this->cart)->map(function ($item) use ($key, $qty) {
            if ($item['key'] === $key) {
                if ($qty <= $item['stock']) {
                    $item['quantity'] = $qty;
                } else {
                    $item['quantity'] = $item['stock'];
                    $this->showToast('warning', 'Quantity capped at maximum stock ('.$item['stock'].').');
                }
                $item['total'] = ($item['price'] - $item['discount']) * $item['quantity'];
            }

            return $item;
        })->toArray();
    }

    public function updatePrice($key, $price)
    {
        $price = floatval($price);
        if ($price < 0) {
            $price = 0;
        }

        $this->cart = collect($this->cart)->map(function ($item) use ($key, $price) {
            if ($item['key'] === $key) {
                $item['price'] = $price;
                $item['total'] = ($price - $item['discount']) * $item['quantity'];
            }

            return $item;
        })->toArray();
    }

    public function updateDiscount($key, $discountExpr)
    {
        $this->cart = collect($this->cart)->map(function ($item) use ($key, $discountExpr) {
            if ($item['key'] === $key) {
                $price = $item['price'];
                $discountVal = 0;

                if (str_ends_with($discountExpr, '%')) {
                    $pct = floatval(rtrim($discountExpr, '%'));
                    $pct = max(0, min(100, $pct));
                    $discountVal = ($price * $pct) / 100;
                    $item['discount_percentage'] = $pct;
                } else {
                    $discountVal = floatval($discountExpr);
                    $discountVal = max(0, min($price, $discountVal));
                    $item['discount_percentage'] = $price > 0 ? ($discountVal / $price) * 100 : 0;
                }

                $item['discount'] = $discountVal;
                $item['total'] = ($price - $discountVal) * $item['quantity'];
            }

            return $item;
        })->toArray();
    }

    public function removeFromCart($key)
    {
        $this->cart = collect($this->cart)->filter(function ($item) use ($key) {
            return $item['key'] !== $key;
        })->values()->toArray();
    }

    // Totals Calculations
    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    public function getUnitDiscountProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return $item['discount'] * $item['quantity'];
        });
    }

    public function getSubtotalAfterUnitDiscountProperty()
    {
        return $this->subtotal - $this->unitDiscount;
    }

    public function updatedAdditionalDiscount($value)
    {
        if ($value === '' || $value === null) {
            $this->additionalDiscount = '';

            return;
        }

        $num = floatval($value);
        if ($num < 0) {
            $this->additionalDiscount = 0;

            return;
        }

        if ($this->additionalDiscountType === 'percentage' && $num > 100) {
            $this->additionalDiscount = 100;

            return;
        }
    }

    public function setDiscountType($type)
    {
        $this->additionalDiscountType = in_array($type, ['fixed', 'percentage']) ? $type : 'fixed';
        if ($this->additionalDiscountType === 'percentage' && floatval($this->additionalDiscount) > 100) {
            $this->additionalDiscount = 100;
        }
    }

    public function clearAdditionalDiscount()
    {
        $this->additionalDiscount = '';
    }

    public function getAdditionalDiscountAmountProperty()
    {
        $val = floatval($this->additionalDiscount ?: 0);
        if ($val <= 0) {
            return 0;
        }

        if ($this->additionalDiscountType === 'percentage') {
            $capped = min(100, max(0, $val));

            return ($this->subtotalAfterUnitDiscount * $capped) / 100;
        }

        return min($val, $this->subtotalAfterUnitDiscount);
    }

    public function getGrandTotalProperty()
    {
        return max(0, $this->subtotalAfterUnitDiscount - $this->additionalDiscountAmount);
    }

    // Discount Modals (Kept for backwards compatibility)
    public $showSaleDiscountModal = false;

    public $tempDiscountType = 'fixed';

    public $tempDiscountValue = '';

    public function openSaleDiscountModal()
    {
        $this->tempDiscountType = $this->additionalDiscountType;
        $this->tempDiscountValue = $this->additionalDiscount;
        $this->showSaleDiscountModal = true;
    }

    public function closeSaleDiscountModal()
    {
        $this->showSaleDiscountModal = false;
    }

    public function applySaleDiscount()
    {
        $this->additionalDiscountType = $this->tempDiscountType;
        $this->additionalDiscount = floatval($this->tempDiscountValue) ?: '';
        $this->showSaleDiscountModal = false;
        $this->showToast('success', 'Global discount applied successfully.');
    }

    public function removeSaleDiscount()
    {
        $this->additionalDiscount = '';
        $this->showSaleDiscountModal = false;
        $this->showToast('success', 'Global discount removed.');
    }

    // Customer Modal
    public function openCustomerModal()
    {
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->customerAddress = '';
        $this->customerType = 'retail';
        $this->businessName = '';
        $this->showCustomerModal = true;
    }

    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
    }

    public function createCustomer()
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'nullable|string|max:20',
            'customerEmail' => 'nullable|email|max:255',
            'customerAddress' => 'nullable|string|max:500',
            'customerType' => 'required|in:retail,wholesale,distributor',
            'businessName' => 'nullable|string|max:255',
        ]);

        try {
            $customer = Customer::create([
                'name' => $this->customerName,
                'phone' => $this->customerPhone ?: null,
                'email' => $this->customerEmail ?: null,
                'address' => $this->customerAddress ?: null,
                'type' => $this->customerType,
                'business_name' => $this->businessName ?: null,
                'user_id' => Auth::id(),
            ]);

            $this->loadCustomers();
            $this->customerId = $customer->id;
            $this->selectedCustomer = $customer;
            $this->customerOpeningBalanceDisplay = $customer->opening_balance ?? 0;
            $this->closeCustomerModal();
            $this->showToast('success', 'Customer "'.$customer->name.'" created successfully!');
        } catch (\Exception $e) {
            $this->showToast('error', 'Failed to create customer: '.$e->getMessage());
        }
    }

    // Create Quotation
    public function createQuotation()
    {
        if (empty($this->cart)) {
            $this->showToast('error', 'Please add at least one product to the quotation.');

            return;
        }

        if (! $this->selectedCustomer && ! $this->customerId) {
            $this->setDefaultCustomer();
        }

        try {
            DB::beginTransaction();

            $customer = $this->selectedCustomer ?? Customer::find($this->customerId);

            if (! $customer) {
                $this->showToast('error', 'Customer not found.');

                return;
            }

            // Map cart items
            $items = collect($this->cart)->map(function ($item, $index) {
                return [
                    'id' => $index + 1,
                    'product_id' => $item['id'] ?? null,
                    'product_code' => $item['code'] ?? '',
                    'product_name' => $item['name'] ?? '',
                    'product_model' => $item['model'] ?? '',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'discount_per_unit' => $item['discount'] ?? 0,
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                    'total_discount' => ($item['discount'] ?? 0) * $item['quantity'],
                    'total' => $item['total'],
                    'is_custom' => ! empty($item['is_custom']),
                    'is_variant' => ! empty($item['is_variant']),
                    'variant_id' => $item['variant_id'] ?? null,
                    'variant_value' => $item['variant_value'] ?? null,
                    'has_warranty' => $item['has_warranty'] ?? 0,
                    'warranty_duration' => $item['warranty_duration'] ?? null,
                ];
            })->toArray();

            // Total discount = item unit discounts + global discount
            $totalDiscount = $this->unitDiscount + $this->additionalDiscountAmount;

            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateQuotationNumber(),
                'customer_id' => $customer->id,
                'customer_type' => $customer->type === 'wholesale' || $customer->type === 'distributor' ? 'wholesale' : 'retail',
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'customer_address' => $customer->address,
                'quotation_date' => now(),
                'valid_until' => $this->validUntil,
                'subtotal' => $this->subtotal,
                'discount_amount' => $totalDiscount,
                'additional_discount' => $this->additionalDiscountAmount,
                'additional_discount_type' => $this->additionalDiscountType,
                'additional_discount_value' => floatval($this->additionalDiscount ?: 0),
                'tax_amount' => 0,
                'shipping_charges' => 0,
                'total_amount' => $this->grandTotal,
                'items' => $items,
                'terms_conditions' => $this->termsConditions,
                'notes' => $this->notes,
                'status' => 'draft',
                'created_by' => Auth::id(),
                'user_id' => Auth::id(),
            ]);

            DB::commit();

            $this->createdQuotation = $quotation;
            $this->lastQuotationId = $quotation->id;

            // Reset the cart and related properties
            $this->cart = [];
            $this->notes = '';
            $this->additionalDiscount = '';
            $this->additionalDiscountType = 'fixed';
            $this->validUntil = now()->addDays(30)->format('Y-m-d');
            $this->setDefaultCustomer();

            $this->showQuotationModal = true;
            $this->showToast('success', 'Quotation created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quotation creation failed', ['error' => $e->getMessage()]);
            $this->showToast('error', 'Failed to create quotation: '.$e->getMessage());
        }
    }

    public function downloadQuotation()
    {
        if (! $this->createdQuotation) {
            $this->showToast('error', 'No quotation found to download.');

            return;
        }

        try {
            $quotation = Quotation::find($this->createdQuotation->id);
            if (! $quotation) {
                $this->showToast('error', 'Quotation not found.');

                return;
            }

            if (is_string($quotation->items)) {
                $quotation->items = json_decode($quotation->items, true);
            }

            $pdf = PDF::loadView('admin.quotations.print', compact('quotation'));
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('dpi', 150);
            $pdf->setOption('defaultFont', 'sans-serif');

            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                'quotation-'.$quotation->quotation_number.'.pdf'
            );
        } catch (\Exception $e) {
            $this->showToast('error', 'Failed to generate PDF: '.$e->getMessage());
        }
    }

    public function createNewQuotation()
    {
        $this->showQuotationModal = false;
        $this->createdQuotation = null;
        $this->lastQuotationId = null;
        $this->cart = [];
        $this->notes = '';
        $this->additionalDiscount = '';
        $this->additionalDiscountType = 'fixed';
        $this->validUntil = now()->addDays(30)->format('Y-m-d');
        $this->setDefaultCustomer();
        $this->loadProducts();
    }

    public function showToast($type, $message)
    {
        $bgColors = [
            'success' => '#10b981',
            'error' => '#ef4444',
            'warning' => '#f59e0b',
            'info' => '#3b82f6',
        ];

        $icons = [
            'success' => '✓',
            'error' => '✕',
            'warning' => '⚠',
            'info' => 'ℹ',
        ];

        $bg = $bgColors[$type] ?? $bgColors['info'];
        $icon = $icons[$type] ?? $icons['info'];
        $escapedMessage = addslashes($message);

        $this->js("
            const toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;top:20px;right:20px;background:{$bg};color:white;padding:16px 24px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;font-size:14px;font-weight:600;display:flex;align-items:center;gap:12px;animation:slideIn 0.3s ease;min-width:300px;max-width:500px;';
            toast.innerHTML = '<span style=\"font-size:20px;font-weight:bold;\">{$icon}</span><span>{$escapedMessage}</span>';
            document.body.appendChild(toast);
            const style = document.createElement('style');
            style.textContent = '@keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(400px); opacity: 0; } }';
            document.head.appendChild(style);
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        ");
    }

    public function render()
    {
        return view('livewire.shop-staff.shop-staff-quotation-billing', [
            'subtotal' => $this->subtotal,
            'grandTotal' => $this->grandTotal,
            'unitDiscount' => $this->unitDiscount,
            'additionalDiscountAmount' => $this->additionalDiscountAmount,
        ]);
    }
}
