<div>
<div class="page-header">
    <h1>Your Cart</h1>
    <div class="breadcrumb-jg">
        <span>Review your curated items before checkout</span>
    </div>
</div>

<section class="section-jg section-jg-cream" style="padding-top:1rem;">
    <div class="container-jg">
        @if (count($cartItems) > 0)
            <div class="cart-layout">
                <!-- ===== CART ITEMS ===== -->
                <div class="reveal visible">
                    @foreach ($cartItems as $item)
                        <div class="cart-item-row" wire:key="cart-item-{{ $item['id'] }}">
                            <div class="thumb">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                            </div>
                            <div class="details">
                                <h4>{{ $item['name'] }}</h4>
                                <div class="options">{{ $item['options'] }}</div>
                                <div class="unit-price">LKR {{ number_format($item['price'], 2) }} each</div>
                            </div>
                            <div class="qty-stepper">
                                <button class="minus" wire:click="decrementQty('{{ $item['id'] }}')">−</button>
                                <span>{{ $item['quantity'] }}</span>
                                <button class="plus" wire:click="incrementQty('{{ $item['id'] }}')">+</button>
                            </div>
                            <div class="right">
                                <div class="line-total">LKR {{ number_format($item['line_total'], 2) }}</div>
                                <button class="remove-btn" wire:click="removeItem('{{ $item['id'] }}')">Remove</button>
                            </div>
                        </div>
                    @endforeach

                    <div class="cart-continue-row" style="margin-top:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;">
                        <a href="{{ route('shop') }}" class="btn-jg btn-jg-outline">Continue Shopping</a>
                    </div>
                </div>

                <!-- ===== ORDER SUMMARY ===== -->
                <div class="order-summary reveal reveal-delay-1 visible">
                    <h3>Order Summary</h3>
                    <div class="row">
                        <span>Subtotal</span>
                        <span>LKR {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="row">
                        <span>Shipping</span>
                        <span style="color:var(--accent);">Free</span>
                    </div>
                    <div class="row total">
                        <span>Total</span>
                        <span>LKR {{ number_format($total, 2) }}</span>
                    </div>
                    <a href="{{ route('checkout') }}" class="btn-jg btn-jg-primary" style="text-align: center;">Proceed to Checkout</a>
                </div>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-bag-x"></i>
                <h3>Your cart is empty</h3>
                <p>Add some beautiful, handcrafted pieces to your collection.</p>
                <a href="{{ route('shop') }}" class="btn-jg btn-jg-primary">Start Shopping</a>
            </div>
        @endif
    </div>
</section>
</div>
