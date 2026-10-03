<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use App\Models\CategoryList;
use App\Models\ProductDetail;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Home')]
#[Layout('components.layouts.webapp')]
class Home extends Component
{
    use InteractsWithStorefront;

    private const HOMEPAGE_PRODUCT_LIMIT = 8;

    private function homeProductsQuery()
    {
        return ProductDetail::query()
            ->with(['category:id,category_name', 'brand:id,brand_name', 'price:product_id,retail_price,wholesale_price,selling_price,discount_price', 'stock:product_id,available_stock'])
            ->where('status', 'active')
            ->where('website_live', true)
            ->latest('product_details.created_at');
    }

    private function formatProductPrice(ProductDetail $product): string
    {
        $originalPrice = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0);
        $price = $originalPrice * 0.90;

        return 'LKR ' . number_format($price, 2);
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
        $details = $description !== ''
            ? str($description)->limit(92)->toString()
            : 'Crafted with signature Jaffna Gold finishing and timeless everyday elegance.';

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
            'details' => $details,
            'price' => $this->formatProductPrice($product),
            'original_price' => $originalPrice > $priceValue ? 'LKR ' . number_format($originalPrice, 2) : null,
            'tag' => $availableStock > 0 ? 'In Stock' : 'Out of Stock',
            'cart' => isset($cart[$product->id]),
            'wishlist' => in_array($product->id, $wishlist),
            'in_stock' => $availableStock > 0,
        ];
    }


    private function buildStripItems(): array
    {
        $categoryIds = CategoryList::query()
            ->pluck('id', 'category_name')
            ->all();

        $categoryMap = [
            'Gold Rings' => $categoryIds['Gold Rings'] ?? null,
            "Necklaces" => $categoryIds['Necklaces'] ?? null,
            'Earrings' => $categoryIds['Earrings'] ?? null,
            'Watches' => $categoryIds['Watches'] ?? null,
            'Bracelets' => $categoryIds['Bracelets'] ?? null,
        ];

        return collect($categoryMap)
            ->map(fn ($categoryId, $label) => [
                'label' => $label,
                'href' => $categoryId ? route('shop', ['category' => $categoryId]) : route('shop'),
                'category_id' => $categoryId,
            ])
            ->values()
            ->all();
    }

    public function addToCart($productId, $quantity = 1): void
    {
        if (str_starts_with($productId, 'dummy-')) {
            $this->dispatch('showToast', message: 'Dummy products cannot be ordered.');
            return;
        }

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
        if (str_starts_with($productId, 'dummy-')) {
            $this->dispatch('showToast', message: 'Dummy products cannot be ordered.');
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
        $this->dispatch('cartUpdated', count: count($cart));
        $this->dispatch('showToast', message: $added ? 'Added to cart' : 'Removed from cart');
    }

    public function toggleWishlist($productId): void
    {
        if (str_starts_with($productId, 'dummy-')) {
            $this->dispatch('showToast', message: 'Dummy products cannot be added to wishlist.');
            return;
        }

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
        if (str_starts_with($productId, 'dummy-')) {
            $this->dispatch('showToast', message: 'Dummy products cannot be ordered.');
            return;
        }

        $cart = session()->get('cart', []);
        if (!isset($cart[$productId])) {
            $cart[$productId] = 1;
            session()->put('cart', $cart);
        }
        $this->redirect(route('checkout'));
    }

    public function render()
    {
        $dbProducts = $this->homeProductsQuery()->get();

        $scrollProducts = $dbProducts
            ->take(self::HOMEPAGE_PRODUCT_LIMIT)
            ->map(fn (ProductDetail $product) => $this->mapProductCard($product))
            ->all();

        // New Arrivals: Latest 4 products
        $newArrivalProducts = $dbProducts
            ->take(4)
            ->map(fn (ProductDetail $product) => $this->mapProductCard($product))
            ->all();

        // Featured Products: Next 4 products
        $featuredProducts = $dbProducts
            ->skip(4)
            ->take(4)
            ->map(fn (ProductDetail $product) => $this->mapProductCard($product))
            ->all();

        $seoData = [
            'title' => 'Jaffna Gold | Handcrafted 22K & 925 Sterling Silver Jewelry',
            'description' => 'Discover Jaffna Gold - Premium handcrafted 22K gold and 925 sterling silver jewelry in Sri Lanka. Explore rings, necklaces, earrings, bracelets, and custom designs.',
            'keywords' => 'Jaffna Gold, gold jewelry Sri Lanka, 22K gold, 925 sterling silver, handcrafted jewelry, gold rings, gold necklaces, Jaffna goldsmiths',
            'canonical' => route('home'),
        ];

        return view('livewire.ecommerce.home', [
            'scrollProducts' => $scrollProducts,
            'newArrivalProducts' => $newArrivalProducts,
            'featuredProducts' => $featuredProducts,
            'stripItems' => $this->buildStripItems(),
        ])->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
