<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use App\Models\ProductDetail;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Wishlist')]
#[Layout('components.layouts.webapp')]
class Wishlist extends Component
{
    use InteractsWithStorefront;

    public $wishlistItems = [];

    public function mount()
    {
        $this->loadWishlist();
    }

    public function loadWishlist()
    {
        $wishlist = session()->get('wishlist', []);

        if (empty($wishlist)) {
            $this->wishlistItems = [];
            return;
        }

        $products = ProductDetail::with(['price', 'stock'])
            ->whereIn('id', $wishlist)
            ->where('status', 'active')
            ->where('website_live', true)
            ->get();

        $this->wishlistItems = [];

        foreach ($products as $p) {
            $originalPrice = (float) ($p->price?->retail_price ?? $p->price?->selling_price ?? 0);
            $price = $originalPrice * 0.90;
            $available = (int) ($p->stock?->available_stock ?? 0);

            $this->wishlistItems[] = [
                'id' => $p->id,
                'slug' => $p->slug,
                'name' => $p->name,
                'image' => $this->productImageUrl($p),
                'options' => collect([$p->category?->category_name, $p->brand?->brand_name, $p->model])->filter()->implode(' · '),
                'price' => 'LKR ' . number_format($price, 2),
                'in_stock' => $available > 0,
            ];
        }
    }

    public function removeFromWishlist($productId)
    {
        $wishlist = session()->get('wishlist', []);
        $wishlist = array_diff($wishlist, [$productId]);
        session()->put('wishlist', $wishlist);
        $this->loadWishlist();
        $this->dispatch('wishlistUpdated', count: count($wishlist));
        $this->dispatch('showToast', message: 'Removed from wishlist');
    }

    public function moveToCart($productId)
    {
        $wishlist = session()->get('wishlist', []);
        $wishlist = array_diff($wishlist, [$productId]);
        session()->put('wishlist', $wishlist);

        $cart = session()->get('cart', []);
        $cart[$productId] = 1;
        session()->put('cart', $cart);

        $this->loadWishlist();
        $this->dispatch('wishlistUpdated', count: count($wishlist));
        $this->dispatch('cartUpdated', count: count($cart));
        $this->dispatch('showToast', message: 'Moved item to cart');
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

    public function render()
    {
        $seoData = [
            'title' => 'My Wishlist | Jaffna Gold',
            'robots' => 'noindex, nofollow',
            'canonical' => route('wishlist'),
        ];

        return view('livewire.ecommerce.wishlist')
            ->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
