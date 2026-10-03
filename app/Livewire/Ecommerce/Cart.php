<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use App\Models\ProductDetail;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Cart')]
#[Layout('components.layouts.webapp')]
class Cart extends Component
{
    use InteractsWithStorefront;
    public $cartItems = [];
    public $subtotal = 0;
    public $shipping = 0; // Free shipping
    public $total = 0;

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        $cart = session()->get('cart', []);
        $productIds = array_keys($cart);

        if (empty($productIds)) {
            $this->cartItems = [];
            $this->subtotal = 0;
            $this->total = 0;
            return;
        }

        $products = ProductDetail::with(['price', 'stock'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $this->cartItems = [];
        $this->subtotal = 0;

        foreach ($cart as $id => $qty) {
            if (isset($products[$id])) {
                $p = $products[$id];
                $originalPrice = (float) ($p->price?->retail_price ?? $p->price?->selling_price ?? 0);
                $price = $originalPrice * 0.90;
                $available = (int) ($p->stock?->available_stock ?? 0);
                
                // Adjust quantity if exceeds available stock
                if ($qty > $available) {
                    $qty = $available;
                    $cart[$id] = $qty;
                    session()->put('cart', $cart);
                }

                if ($qty <= 0) {
                    unset($cart[$id]);
                    session()->put('cart', $cart);
                    continue;
                }

                $lineTotal = $price * $qty;
                $this->subtotal += $lineTotal;

                $this->cartItems[] = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'image' => $this->productImageUrl($p),
                    'options' => collect([$p->category?->category_name, $p->brand?->brand_name, $p->model])->filter()->implode(' · '),
                    'price' => $price,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                    'max_quantity' => $available,
                ];
            } else {
                // Product no longer exists or is inactive, remove from session cart
                unset($cart[$id]);
                session()->put('cart', $cart);
            }
        }

        $this->total = $this->subtotal + $this->shipping;
    }

    public function incrementQty($productId)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            $product = ProductDetail::with('stock')->find($productId);
            $available = (int) ($product?->stock?->available_stock ?? 0);

            if ($cart[$productId] < $available) {
                $cart[$productId]++;
                session()->put('cart', $cart);
                $this->loadCart();
                $this->dispatch('cartUpdated', count: count($cart));
            } else {
                $this->dispatch('showToast', message: 'Cannot exceed available stock.');
            }
        }
    }

    public function decrementQty($productId)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            if ($cart[$productId] > 1) {
                $cart[$productId]--;
                session()->put('cart', $cart);
                $this->loadCart();
                $this->dispatch('cartUpdated', count: count($cart));
            } else {
                $this->removeItem($productId);
            }
        }
    }

    public function removeItem($productId)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            $this->loadCart();
            $this->dispatch('cartUpdated', count: count($cart));
            $this->dispatch('showToast', message: 'Item removed from cart');
        }
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
            'title' => 'Shopping Cart | Jaffna Gold',
            'robots' => 'noindex, nofollow',
            'canonical' => route('cart'),
        ];

        return view('livewire.ecommerce.cart')
            ->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
