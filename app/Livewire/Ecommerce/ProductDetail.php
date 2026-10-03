<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use App\Models\ProductDetail as ProductDetailModel;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Product Detail')]
#[Layout('components.layouts.webapp')]
class ProductDetail extends Component
{
    use InteractsWithStorefront;

    #[Url]
    public $id;

    public $slug;

    public $product;

    public $quantity = 1;

    public $wishlisted = false;

    public $carted = false;

    public $selectedImageIndex = 0;

    public function selectImage($index)
    {
        $this->selectedImageIndex = (int) $index;
    }

    public function prevImage()
    {
        $urls = $this->product?->all_image_urls ?? [];
        $total = count($urls);
        if ($total <= 1) {
            return;
        }
        $this->selectedImageIndex = ($this->selectedImageIndex - 1 + $total) % $total;
    }

    public function nextImage()
    {
        $urls = $this->product?->all_image_urls ?? [];
        $total = count($urls);
        if ($total <= 1) {
            return;
        }
        $this->selectedImageIndex = ($this->selectedImageIndex + 1) % $total;
    }

    public function mount($slug = null)
    {
        $this->slug = $slug;
        if (! $this->id) {
            $this->id = request()->query('id');
        }

        $this->loadProduct();

        // 301 Redirect strategy: Redirect legacy or un-slugged URLs to canonical slug URL
        if ($this->product && (request()->routeIs('product-detail') || request()->is('product-detail'))) {
            return redirect()->to(route('product-detail.slug', ['slug' => $this->product->slug]), 301);
        }
    }

    public function loadProduct()
    {
        $query = ProductDetailModel::with(['category', 'brand', 'price', 'stock'])
            ->where('status', 'active')
            ->where('website_live', true);

        if ($this->slug) {
            // Extract numeric ID from slug if present at the end (e.g., "gold-ring-15")
            if (preg_match('/-(\d+)$/', $this->slug, $matches)) {
                $targetId = (int) $matches[1];
                $this->product = (clone $query)->find($targetId);
            }

            // Fallback lookup by product name slug if ID match wasn't found
            if (! $this->product) {
                $allProducts = (clone $query)->get();
                $this->product = $allProducts->first(function ($p) {
                    return $p->slug === $this->slug || Str::slug($p->name) === $this->slug;
                });
            }
        }

        if (! $this->product && $this->id) {
            $this->product = (clone $query)->find($this->id);
        }

        if (! $this->product) {
            // Fallback to the first active product if invalid ID/slug
            $this->product = (clone $query)->first();
        }

        if ($this->product) {
            $this->id = $this->product->id;
            $wishlist = session()->get('wishlist', []);
            $cart = session()->get('cart', []);
            $this->wishlisted = in_array($this->product->id, $wishlist);
            $this->carted = isset($cart[$this->product->id]);
        }
    }

    public function incrementQty()
    {
        $available = (int) ($this->product->stock?->available_stock ?? 0);
        if ($this->quantity < $available) {
            $this->quantity++;
        } else {
            $this->dispatch('showToast', message: 'Cannot exceed available stock.');
        }
    }

    public function decrementQty()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(): void
    {
        if (! $this->product) {
            return;
        }

        $available = (int) ($this->product->stock?->available_stock ?? 0);
        if ($available <= 0) {
            $this->dispatch('showToast', message: 'Product is currently out of stock.');

            return;
        }

        $cart = session()->get('cart', []);
        $added = ! isset($cart[$this->product->id]);

        if ($added) {
            $cart[$this->product->id] = $this->quantity;
            $msg = 'Added to cart';
        } else {
            unset($cart[$this->product->id]);
            $msg = 'Removed from cart';
        }

        session()->put('cart', $cart);
        $this->carted = $added;
        $this->dispatch('cartUpdated', count: count($cart));
        $this->dispatch('showToast', message: $msg);
    }

    public function toggleWishlist($productId = null): void
    {
        $productId = $productId ?? ($this->product?->id);
        if (! $productId) {
            return;
        }

        $wishlist = session()->get('wishlist', []);
        $added = ! in_array($productId, $wishlist);

        if ($added) {
            $wishlist[] = $productId;
            $msg = 'Added to wishlist';
        } else {
            $wishlist = array_diff($wishlist, [$productId]);
            $msg = 'Removed from wishlist';
        }

        session()->put('wishlist', $wishlist);

        if ($this->product && (string) $productId === (string) $this->product->id) {
            $this->wishlisted = $added;
        }

        $this->dispatch('wishlistUpdated', count: count($wishlist));
        $this->dispatch('showToast', message: $msg);
    }

    public function toggleCart($productId = null): void
    {
        $productId = $productId ?? ($this->product?->id);
        if (! $productId) {
            return;
        }

        $cart = session()->get('cart', []);
        $added = ! isset($cart[$productId]);

        if ($added) {
            $cart[$productId] = 1;
        } else {
            unset($cart[$productId]);
        }

        session()->put('cart', $cart);

        if ($this->product && (string) $productId === (string) $this->product->id) {
            $this->carted = $added;
        }

        $this->dispatch('cartUpdated', count: count($cart));
        $this->dispatch('showToast', message: $added ? 'Added to cart' : 'Removed from cart');
    }

    public function buyNow(): void
    {
        if (! $this->product) {
            return;
        }

        $available = (int) ($this->product->stock?->available_stock ?? 0);
        if ($available <= 0) {
            $this->dispatch('showToast', message: 'Product is currently out of stock.');

            return;
        }

        $cart = session()->get('cart', []);
        $cart[$this->product->id] = $this->quantity;
        session()->put('cart', $cart);

        $this->redirect(route('checkout'));
    }

    private function getRelatedProducts()
    {
        if (! $this->product) {
            return [];
        }

        return ProductDetailModel::with(['category', 'brand', 'price', 'stock'])
            ->where('status', 'active')
            ->where('website_live', true)
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->limit(3)
            ->get();
    }

    private function formatPrice($amount): string
    {
        return 'LKR '.number_format((float) $amount, 2);
    }

    private function productImageUrl(ProductDetailModel $product): string
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

            return url('/product-image-serve/'.$filename);
        }

        if (str_starts_with($image, 'images/') || str_starts_with($image, 'storage/')) {
            return asset($image);
        }

        if (str_contains($image, '/')) {
            return asset(ltrim($image, '/'));
        }

        return url('/product-image-serve/'.ltrim($image, '/'));
    }

    public function render()
    {
        $related = [];
        if ($this->product) {
            $related = $this->getRelatedProducts()->map(function ($p) {
                $originalPrice = (float) ($p->price?->retail_price ?? $p->price?->selling_price ?? 0);
                $price = $originalPrice * 0.90;
                $wishlist = session()->get('wishlist', []);
                $cart = session()->get('cart', []);

                return [
                    'id' => $p->id,
                    'slug' => $p->slug,
                    'title' => $p->name,
                    'image' => $this->productImageUrl($p),
                    'details' => str($p->description)->limit(92)->toString() ?: 'Generational craftsmanship from Jaffna Gold.',
                    'price' => $this->formatPrice($price),
                    'original_price' => $originalPrice > $price ? $this->formatPrice($originalPrice) : null,
                    'wishlist' => in_array($p->id, $wishlist),
                    'cart' => isset($cart[$p->id]),
                    'in_stock' => (int) ($p->stock?->available_stock ?? 0) > 0,
                ];
            })->all();
        }

        $productTitle = $this->product ? $this->product->name.' - 22K Gold & Sterling Silver' : 'Product Detail';
        $plainDesc = $this->product && $this->product->description
            ? strip_tags($this->product->description)
            : 'Crafted with signature Jaffna Gold finishing and timeless everyday elegance.';
        $productDesc = $this->product
            ? 'Buy '.$this->product->name.' online at Jaffna Gold. '.str($plainDesc)->limit(150)->toString()
            : 'View handcrafted jewelry product details at Jaffna Gold.';
        $productImage = $this->product ? $this->productImageUrl($this->product) : asset('images/jgg.png');
        $canonical = $this->product
            ? route('product-detail.slug', ['slug' => $this->product->slug])
            : url()->current();

        $schemas = [];
        if ($this->product) {
            $price = (float) ($this->product->price?->retail_price ?? $this->product->price?->selling_price ?? 0) * 0.90;
            $stock = (int) ($this->product->stock?->available_stock ?? 0);

            $productSchema = [
                '@context' => 'https://schema.org/',
                '@type' => 'Product',
                'name' => $this->product->name,
                'image' => [$productImage],
                'description' => str($plainDesc)->limit(155)->toString(),
                'sku' => $this->product->code ?? ('JG-'.$this->product->id),
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $this->product->brand?->brand_name ?? 'Jaffna Gold',
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $canonical,
                    'priceCurrency' => 'LKR',
                    'price' => number_format($price, 2, '.', ''),
                    'priceValidUntil' => now()->addYear()->format('Y-m-d'),
                    'itemCondition' => 'https://schema.org/NewCondition',
                    'availability' => $stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                ],
            ];

            $breadcrumbItems = [
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
            ];

            if ($this->product->category) {
                $breadcrumbItems[] = [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $this->product->category->category_name,
                    'item' => route('shop', ['category' => Str::slug($this->product->category->category_name)]),
                ];
                $breadcrumbItems[] = [
                    '@type' => 'ListItem',
                    'position' => 4,
                    'name' => $this->product->name,
                    'item' => $canonical,
                ];
            } else {
                $breadcrumbItems[] = [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $this->product->name,
                    'item' => $canonical,
                ];
            }

            $breadcrumbSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumbItems,
            ];

            $schemas = [$productSchema, $breadcrumbSchema];
        }

        $seoData = [
            'title' => $productTitle,
            'description' => $productDesc,
            'image' => $productImage,
            'canonical' => $canonical,
            'og_type' => 'product',
            'schemas' => $schemas,
        ];

        return view('livewire.ecommerce.product-detail', [
            'relatedProducts' => $related,
        ])->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
