<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductDetail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Shop')]
#[Layout('components.layouts.webapp')]
class Shop extends Component
{
    use WithPagination;
    use InteractsWithStorefront;

    #[Url(as: 'category')]
    public string $selectedCategory = '';

    #[Url(as: 'brand')]
    public string $selectedBrand = '';

    #[Url(as: 'availability')]
    public string $selectedAvailability = '';

    #[Url(as: 'min_price')]
    public string $minPrice = '';

    #[Url(as: 'max_price')]
    public string $maxPrice = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'newest';

    #[Url(as: 'search')]
    public string $search = '';

    protected $perPage = 24;

    public function clearFilters(): void
    {
        $this->reset([
            'selectedCategory',
            'selectedBrand',
            'selectedAvailability',
            'minPrice',
            'maxPrice',
            'search',
        ]);

        $this->sortBy = 'newest';
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory(): void { $this->resetPage(); }
    public function updatedSelectedBrand(): void { $this->resetPage(); }
    public function updatedSelectedAvailability(): void { $this->resetPage(); }
    public function updatedMinPrice(): void { $this->resetPage(); }
    public function updatedMaxPrice(): void { $this->resetPage(); }
    public function updatedSortBy(): void { $this->resetPage(); }
    public function updatedSearch(): void { $this->resetPage(); }

    private function shopProductsQuery()
    {
        $query = ProductDetail::query()
            ->with([
                'category:id,category_name',
                'brand:id,brand_name',
                'price:product_id,retail_price,wholesale_price,selling_price,discount_price',
                'stock:product_id,available_stock',
            ])
            ->where('status', 'active')
            ->where('website_live', true);

        // Search filter
        if ($this->search !== '') {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Category filter
        if ($this->selectedCategory !== '') {
            if (is_numeric($this->selectedCategory)) {
                $query->where('category_id', $this->selectedCategory);
            } else {
                $query->whereHas('category', fn ($q) => $q->where('category_name', 'like', '%' . str_replace('-', ' ', $this->selectedCategory) . '%'));
            }
        }

        // Brand filter
        if ($this->selectedBrand !== '') {
            if (is_numeric($this->selectedBrand)) {
                $query->where('brand_id', $this->selectedBrand);
            } else {
                $query->whereHas('brand', fn ($q) => $q->where('brand_name', 'like', '%' . str_replace('-', ' ', $this->selectedBrand) . '%'));
            }
        }

        // Availability filter
        if ($this->selectedAvailability !== '') {
            $query->whereHas('stock', function ($q) {
                if ($this->selectedAvailability === 'in_stock') {
                    $q->where('available_stock', '>', 0);
                } else {
                    $q->where('available_stock', '<=', 0);
                }
            });
        }

        // Price range filters (apply on joined price table)
        if ($this->minPrice !== '' && is_numeric($this->minPrice)) {
            $query->whereHas('price', function ($q) {
                $q->whereRaw('(COALESCE(retail_price, selling_price, 0) * 0.90) >= ?', [(float) $this->minPrice]);
            });
        }

        if ($this->maxPrice !== '' && is_numeric($this->maxPrice)) {
            $query->whereHas('price', function ($q) {
                $q->whereRaw('(COALESCE(retail_price, selling_price, 0) * 0.90) <= ?', [(float) $this->maxPrice]);
            });
        }

        // Sorting
        match ($this->sortBy) {
            'price_asc' => $query->orderBy(
                \App\Models\ProductPrice::selectRaw('COALESCE(retail_price, selling_price, 0)')
                    ->whereColumn('product_prices.product_id', 'product_details.id')
                    ->limit(1),
                'asc'
            ),
            'price_desc' => $query->orderBy(
                \App\Models\ProductPrice::selectRaw('COALESCE(retail_price, selling_price, 0)')
                    ->whereColumn('product_prices.product_id', 'product_details.id')
                    ->limit(1),
                'desc'
            ),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest('product_details.created_at'),
        };

        return $query;
    }

    private function categoryOptions()
    {
        return CategoryList::query()
            ->whereHas('products', fn ($query) => $query->where('status', 'active')->where('website_live', true))
            ->orderBy('category_name')
            ->get(['id', 'category_name']);
    }

    private function brandOptions()
    {
        return BrandList::query()
            ->whereHas('products', fn ($query) => $query->where('status', 'active')->where('website_live', true))
            ->orderBy('brand_name')
            ->get(['id', 'brand_name']);
    }

    private function productPriceValue(ProductDetail $product): float
    {
        $originalPrice = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0);
        return $originalPrice * 0.90;
    }

    private function formatCurrency(float $amount): string
    {
        return 'LKR ' . number_format($amount, 2);
    }

    private function productImageUrl(ProductDetail $product): string
    {
        $image = trim((string) ($product->image ?? ''));

        if ($image === '' || $image === 'images/product.jpg') {
            return asset('images/product.jpg');
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        if (str_starts_with($image, 'storage/images/')) {
            $filename = substr($image, strlen('storage/images/'));
            return url('/product-image-serve/' . $filename);
        }

        if (str_starts_with($image, 'images/') || str_starts_with($image, 'storage/')) {
            return asset($image);
        }

        if (str_contains($image, '/')) {
            return asset(ltrim($image, '/'));
        }

        return url('/product-image-serve/' . ltrim($image, '/'));
    }

    private function mapProductCard(ProductDetail $product): array
    {
        $description = trim((string) $product->description);
        $originalPrice = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0);
        $priceValue = $originalPrice * 0.90;
        $availableStock = (int) ($product->stock?->available_stock ?? 0);
        $wishlist = session()->get('wishlist', []);
        $cart = session()->get('cart', []);

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'image' => $this->productImageUrl($product),
            'title' => $product->name,
            'subtitle' => collect([
                $product->category?->category_name,
                $product->brand?->brand_name,
            ])->filter()->implode(' - '),
            'details' => $description !== ''
                ? str($description)->limit(92)->toString()
                : 'Crafted with signature Jaffna Gold finishing and timeless everyday elegance.',
            'price' => $this->formatCurrency($priceValue),
            'original_price' => $originalPrice > $priceValue ? $this->formatCurrency($originalPrice) : null,
            'tag' => $availableStock > 0 ? 'In Stock' : 'Out of Stock',
            'cart' => isset($cart[$product->id]),
            'wishlist' => in_array($product->id, $wishlist),
            'category_id' => (string) ($product->category?->id ?? ''),
            'category_slug' => Str::slug((string) ($product->category?->category_name ?? '')),
            'brand_id' => (string) ($product->brand?->id ?? ''),
            'brand_slug' => Str::slug((string) ($product->brand?->brand_name ?? '')),
            'availability' => $availableStock > 0 ? 'in_stock' : 'out_of_stock',
            'price_value' => $priceValue,
            'sort_timestamp' => $product->created_at?->timestamp ?? 0,
            'in_stock' => $availableStock > 0,
        ];
    }

    private function paginatedProducts()
    {
        return $this->shopProductsQuery()->paginate($this->perPage);
    }

    public function addToCart($productId, $quantity = 1): void
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }
        session()->put('cart', $cart);
        $this->dispatch('cartUpdated', count: count($cart));
        $this->dispatch('showToast', message: 'Added to cart');
    }

    public function toggleCart($productId): void
    {
        $cart = session()->get('cart', []);
        $added = ! isset($cart[$productId]);

        if ($added) {
            $cart[$productId] = 1;
        } else {
            unset($cart[$productId]);
        }

        session()->put('cart', $cart);
        $this->dispatch('cartUpdated', count: count($cart));
        $this->dispatch('showToast', message: $added ? 'Added to cart' : 'Removed from cart');
    }

    public function toggleWishlist($productId): void
    {
        $wishlist = session()->get('wishlist', []);
        if (in_array($productId, $wishlist)) {
            $wishlist = array_diff($wishlist, [$productId]);
            $msg = 'Removed from wishlist';
        } else {
            $wishlist[] = $productId;
            $msg = 'Added to wishlist';
        }
        session()->put('wishlist', $wishlist);
        $this->dispatch('wishlistUpdated', count: count($wishlist));
        $this->dispatch('showToast', message: $msg);
    }

    public function buyNow($productId): void
    {
        $cart = session()->get('cart', []);
        if (!isset($cart[$productId])) {
            $cart[$productId] = 1;
            session()->put('cart', $cart);
        }
        $this->redirect(route('checkout'));
    }

    public function render()
    {
        $paginator = $this->paginatedProducts();
        $products = $paginator->getCollection()->map(fn (ProductDetail $product) => $this->mapProductCard($product))->values()->all();
        $totalProducts = $paginator->total();

        $title = 'Shop Luxury Jewelry Collection';
        $description = 'Explore Jaffna Gold\'s collection of 22K gold rings, sterling silver necklaces, earrings, and handcrafted luxury jewelry in Sri Lanka.';

        if ($this->selectedCategory !== '') {
            $catModel = is_numeric($this->selectedCategory)
                ? CategoryList::find($this->selectedCategory)
                : CategoryList::all()->first(fn ($c) => Str::slug($c->category_name) === $this->selectedCategory);
            $catName = $catModel?->category_name ?? ucfirst($this->selectedCategory);
            $title = $catName . ' | Buy Online at Jaffna Gold';
            $description = 'Browse handcrafted ' . $catName . ' at Jaffna Gold. Premium 22K gold and 925 sterling silver jewelry with islandwide delivery in Sri Lanka.';
        } elseif ($this->selectedBrand !== '') {
            $brandModel = is_numeric($this->selectedBrand)
                ? BrandList::find($this->selectedBrand)
                : BrandList::all()->first(fn ($b) => Str::slug($b->brand_name) === $this->selectedBrand);
            $brandName = $brandModel?->brand_name ?? ucfirst($this->selectedBrand);
            $title = $brandName . ' Jewelry Collection | Jaffna Gold';
            $description = 'Explore ' . $brandName . ' luxury handcrafted jewelry at Jaffna Gold.';
        }

        $robots = 'index, follow';
        if ($this->search !== '' || request()->has('search')) {
            $robots = 'noindex, follow';
            $searchTerm = $this->search ?: request()->query('search');
            $title = 'Search Results for "' . e($searchTerm) . '" | Jaffna Gold';
        }

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Shop',
                    'item' => route('shop'),
                ],
            ],
        ];

        $itemListElements = [];
        foreach ($products as $index => $prod) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => route('product-detail.slug', ['slug' => $prod['slug']]),
                'name' => $prod['title'],
            ];
        }

        $itemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $itemListElements,
        ];

        $seoData = [
            'title' => $title,
            'description' => $description,
            'keywords' => 'gold shop Sri Lanka, buy gold rings online, sterling silver jewelry, Jaffna Gold collection',
            'robots' => $robots,
            'canonical' => route('shop'),
            'schemas' => [$breadcrumbSchema, $itemListSchema],
        ];

        return view('livewire.ecommerce.shop', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'paginator' => $paginator,
            'categories' => $this->categoryOptions(),
            'brands' => $this->brandOptions(),
        ])->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
