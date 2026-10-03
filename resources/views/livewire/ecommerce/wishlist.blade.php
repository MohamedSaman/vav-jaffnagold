<div>
<div class="page-header">
    <h1>Your Wishlist</h1>
    <div class="breadcrumb-jg">
        <span>Curated pieces you have marked to keep close</span>
    </div>
</div>

<section class="section-jg section-jg-cream" style="padding-top:1rem;">
    <div class="container-jg">
        @if (count($wishlistItems) > 0)
            <div class="product-grid shop-product-grid">
                @foreach ($wishlistItems as $index => $item)
                    <div class="product-card reveal visible {{ $index > 0 ? 'reveal-delay-' . min($index % 6, 5) : '' }}" wire:key="wishlist-item-{{ $item['id'] }}" onclick="if (event.target.closest('button, a')) return; window.location='{{ route('product-detail', ['id' => $item['id']]) }}'">
                        <div class="img-wrap">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                            <span class="tag">{{ $item['in_stock'] ? 'In Stock' : 'Out of Stock' }}</span>
                        </div>
                        <div class="info">
                            <h3>{{ $item['name'] }}</h3>
                            <div class="details">{{ $item['options'] }}</div>
                            <div class="price">
                                <span class="current">{{ $item['price'] }}</span>
                            </div>
                            <div class="actions">
                                <button class="card-tool-btn action-icon-btn wishlist-btn active" type="button" wire:click.prevent="removeFromWishlist('{{ $item['id'] }}')" title="Remove from Wishlist">
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                                <button class="card-tool-btn action-icon-btn cart-btn" type="button" wire:click.prevent="moveToCart('{{ $item['id'] }}')" @if(!$item['in_stock']) disabled style="opacity:0.5; cursor:not-allowed;" title="Out of Stock" @else title="Move to Cart" @endif>
                                    <i class="bi bi-bag"></i>
                                </button>
                                <button class="btn-jg btn-jg-primary btn-jg-sm" type="button" onclick="window.location='{{ route('product-detail', ['id' => $item['id']]) }}'" @if(!$item['in_stock']) disabled style="opacity:0.5;" @endif>Buy Now</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-heart"></i>
                <h3>Your wishlist is empty</h3>
                <p>Start exploring our collections and save your favorite pieces.</p>
                <a href="{{ route('shop') }}" class="btn-jg btn-jg-primary">Browse Shop</a>
            </div>
        @endif
    </div>
</section>
</div>
