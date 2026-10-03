<?php

namespace App\Livewire\Ecommerce\Concerns;

use App\Models\ProductDetail;
use Illuminate\Support\Str;

/**
 * Turns a ProductDetail into the array shape the storefront product-card markup
 * expects.
 *
 * The logic mirrors {@see \App\Livewire\Ecommerce\Shop} so visual search results
 * look and behave exactly like shop listings — same 10% display discount, same
 * image URL resolution, same stock badges.
 */
trait PresentsProductCards
{
    /**
     * Resolve a stored image path to a browser-usable URL.
     *
     * Product images live in one of several places depending on which upload path
     * created them, so every layout the app has ever written is handled here.
     */
    protected function productImageUrl(ProductDetail $product): string
    {
        $image = trim((string) ($product->image ?? ''));

        if ($image === '' || $image === 'images/product.jpg') {
            return asset('images/product.jpg');
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        // Served through a controller rather than the storage symlink, which is
        // not guaranteed to exist on shared hosting.
        if (str_starts_with($image, 'storage/images/')) {
            return url('/product-image-serve/' . substr($image, strlen('storage/images/')));
        }

        if (str_starts_with($image, 'images/') || str_starts_with($image, 'storage/')) {
            return asset($image);
        }

        if (str_contains($image, '/')) {
            return asset(ltrim($image, '/'));
        }

        return url('/product-image-serve/' . ltrim($image, '/'));
    }

    protected function formatCurrency(float $amount): string
    {
        return 'LKR ' . number_format($amount, 2);
    }

    /**
     * @return array<string,mixed>
     */
    protected function mapProductCard(ProductDetail $product): array
    {
        $description = trim((string) $product->description);
        $originalPrice = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0);

        // The storefront advertises a flat 10% off the retail price.
        $priceValue = $originalPrice * 0.90;

        $availableStock = (int) ($product->stock?->available_stock ?? 0);
        $wishlist = session()->get('wishlist', []);
        $cart = session()->get('cart', []);

        return [
            'id' => $product->id,
            'image' => $this->productImageUrl($product),
            'title' => $product->name,
            'subtitle' => collect([
                $product->category?->category_name,
                $product->brand?->brand_name,
            ])->filter()->implode(' - '),
            'details' => $description !== ''
                ? Str::limit($description, 92)
                : 'Crafted with signature Jaffna Gold finishing and timeless everyday elegance.',
            'price' => $this->formatCurrency($priceValue),
            'original_price' => $originalPrice > $priceValue ? $this->formatCurrency($originalPrice) : null,
            'tag' => $availableStock > 0 ? 'In Stock' : 'Out of Stock',
            'cart' => isset($cart[$product->id]),
            'wishlist' => in_array($product->id, $wishlist),
            'category_id' => (string) ($product->category?->id ?? ''),
            'category_name' => $product->category?->category_name,
            'price_value' => $priceValue,
            'in_stock' => $availableStock > 0,
        ];
    }

    // ------------------------------------------------------------ cart actions

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
            $message = 'Removed from wishlist';
        } else {
            $wishlist[] = $productId;
            $message = 'Added to wishlist';
        }

        session()->put('wishlist', $wishlist);
        $this->dispatch('wishlistUpdated', count: count($wishlist));
        $this->dispatch('showToast', message: $message);
    }

    public function buyNow($productId): void
    {
        $cart = session()->get('cart', []);

        if (! isset($cart[$productId])) {
            $cart[$productId] = 1;
            session()->put('cart', $cart);
        }

        $this->redirect(route('checkout'));
    }
}
