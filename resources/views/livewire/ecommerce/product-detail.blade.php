<div>
@if ($product)
    @php
        $originalPrice = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0);
        $price = $originalPrice * 0.90;
        $availableStock = (int) ($product->stock?->available_stock ?? 0);
        $allImages = $product->all_image_urls;
        $currentImage = $allImages[$selectedImageIndex] ?? ($allImages[0] ?? asset('images/product.jpg'));
    @endphp

    <section class="section-jg section-jg-cream" style="padding-top:1rem;">
        <div class="container-jg">
            <div class="pd-layout">
                <!-- ===== IMAGES & GALLERY ===== -->
                <div class="pd-images reveal visible">
                    <div class="main-img" style="position: relative; overflow: hidden; border-radius: 12px; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.05); text-align: center; min-height: 380px; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ $currentImage }}" alt="{{ $product->name }}" id="mainProductImage" style="width: 100%; max-height: 480px; object-fit: contain; transition: all 0.25s ease;">
                        @if(count($allImages) > 1)
                            <button type="button" wire:click="prevImage" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.9); border: 1px solid #e2e8f0; color: #1e293b; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); cursor: pointer; transition: all 0.2s; z-index: 10;" title="Previous Image">
                                <i class="bi bi-chevron-left" style="font-size: 1.1rem; font-weight: bold;"></i>
                            </button>
                            <button type="button" wire:click="nextImage" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.9); border: 1px solid #e2e8f0; color: #1e293b; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); cursor: pointer; transition: all 0.2s; z-index: 10;" title="Next Image">
                                <i class="bi bi-chevron-right" style="font-size: 1.1rem; font-weight: bold;"></i>
                            </button>
                            <div style="position: absolute; bottom: 12px; right: 12px; background: rgba(0,0,0,0.65); color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">
                                {{ $selectedImageIndex + 1 }} / {{ count($allImages) }}
                            </div>
                        @endif
                    </div>

                    @if(count($allImages) > 1)
                        <div class="pd-thumbnails" style="display: flex; gap: 10px; margin-top: 14px; overflow-x: auto; padding-bottom: 6px; scrollbar-width: thin;">
                            @foreach($allImages as $idx => $thumbUrl)
                                <button type="button" 
                                    wire:click="selectImage({{ $idx }})" 
                                    style="flex: 0 0 76px; width: 76px; height: 76px; padding: 3px; border-radius: 10px; border: 2.5px solid {{ $selectedImageIndex === $idx ? 'var(--accent, #b88a2b)' : '#e5e7eb' }}; background: #fff; cursor: pointer; transition: all 0.2s; outline: none; box-shadow: {{ $selectedImageIndex === $idx ? '0 3px 10px rgba(184,138,43,0.35)' : 'none' }}; transform: {{ $selectedImageIndex === $idx ? 'scale(1.03)' : 'scale(1)' }};">
                                    <img src="{{ $thumbUrl }}" alt="{{ $product->name }} preview {{ $idx + 1 }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- ===== INFO ===== -->
                <div class="pd-info reveal reveal-delay-1 visible">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                        <span class="badge">{{ $product->category?->category_name }} · {{ $product->brand?->brand_name }}</span>
                        <span class="product-sku-badge" style="font-size: 0.72rem; font-weight: 600; color: #8c7662; letter-spacing: 0.05em; background: #f3ece1; padding: 0.2rem 0.55rem; border-radius: 4px; border: 1px solid #e5dacb; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <i class="bi bi-upc-scan" style="font-size: 0.75rem;"></i> SKU: {{ $product->code }}
                        </span>
                    </div>
                    <h1>{{ $product->name }}</h1>
                    <div class="subtitle">{{ $product->model ?? 'Genuine 22K Hallmarked Gold' }}</div>
                    <div class="price-block">
                        LKR {{ number_format($price, 2) }}
                        @if($originalPrice > $price)
                            <span class="original">LKR {{ number_format($originalPrice, 2) }}</span>
                        @endif
                    </div>

                    <div class="section">
                        <h4>Availability</h4>
                        <div style="font-size: 0.9rem; margin-top: 0.25rem;">
                            @if($availableStock > 0)
                                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> In Stock ({{ $availableStock }} available)</span>
                            @else
                                <span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> Out of Stock</span>
                            @endif
                        </div>
                    </div>

                    @if($availableStock > 0)
                        <div class="section">
                            <h4>Quantity</h4>
                            <div class="qty-stepper">
                                <button class="minus" wire:click="decrementQty">-</button>
                                <span>{{ $quantity }}</span>
                                <button class="plus" wire:click="incrementQty">+</button>
                            </div>
                        </div>
                    @endif

                    <div class="pd-action-group">
                        <div class="cta-row" style="margin-top: 1.5rem;">
                            <button class="btn-jg btn-jg-primary pd-add-cart-btn cart-btn{{ $carted ? ' active' : '' }}" wire:click="addToCart" @if($availableStock <= 0) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                                <i class="bi {{ $carted ? 'bi-bag-fill' : 'bi-bag' }}"></i>
                                <span>{{ $carted ? 'Remove from Cart' : 'Add to Cart' }}</span>
                            </button>
                            <button class="btn-jg btn-jg-outline wishlist-btn{{ $wishlisted ? ' active' : '' }} pd-wishlist-btn" wire:click="toggleWishlist">
                                <i class="bi {{ $wishlisted ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                <span>{{ $wishlisted ? 'Wishlisted' : 'Add to Wishlist' }}</span>
                            </button>
                        </div>
    
                        @if($availableStock > 0)
                            <div class="pd-buy-now-wrap" style="margin-top: 1rem;">
                                <button class="btn-jg btn-jg-outline pd-buy-now-btn" style="width: 100%; text-align: center; background: #000; color: #fff; border-color: #000;" wire:click="buyNow">
                                    Buy It Now
                                </button>
                            </div>
                        @else
                            <div class="pd-buy-now-wrap pd-mobile-buy-now-wrap" style="margin-top: 1rem;">
                                <button class="btn-jg btn-jg-outline pd-buy-now-btn" style="width: 100%; text-align: center; background: #000; color: #fff; border-color: #000; opacity: 0.5; cursor: not-allowed;" type="button" disabled>
                                    Buy It Now
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="trust-row">
                        <span><i class="bi bi-patch-check-fill"></i> Certified Purity</span>
                        <span><i class="bi bi-truck"></i> Free Shipping</span>
                        <span><i class="bi bi-arrow-return-left"></i> 30-Day Returns</span>
                    </div>

                    <div class="tab-container">
                        <div class="tabs-jg">
                            <button class="tab-jg active" data-tab="desc">Description</button>
                            <button class="tab-jg" data-tab="specs">Specifications</button>
                            <button class="tab-jg" data-tab="care">Care Instructions</button>
                        </div>

                        <div class="tab-panel active" id="desc">
                            <p style="font-size:0.9rem;line-height:1.7;">{{ $product->description ?: 'This premium piece is designed for everyday elegance with artisan finishing and signature Jaffna Gold detailing. Crafted by master artisans with generational experience, it represents the finest gold jewelry available.' }}</p>
                        </div>
                        <div class="tab-panel" id="specs">
                            <ul style="font-size:0.9rem;line-height:2;">
                                <li><strong>Code:</strong> {{ $product->code }}</li>
                                <li><strong>Model:</strong> {{ $product->model ?? '22K Gold' }}</li>
                                <li><strong>Category:</strong> {{ $product->category?->category_name }}</li>
                                <li><strong>Brand:</strong> {{ $product->brand?->brand_name }}</li>
                                <li><strong>Weight:</strong> {{ $product->description ? (preg_match('/weight:\s*([\d\.]+\s*g)/i', $product->description, $matches) ? $matches[1] : 'N/A') : 'N/A' }}</li>
                            </ul>
                        </div>
                        <div class="tab-panel" id="care">
                            <p style="font-size:0.9rem;line-height:1.7;">To maintain the brilliance of your Jaffna Gold jewelry, gently clean with a soft cloth after each wear. Avoid exposure to harsh chemicals, perfumes, and extreme temperatures. We recommend professional cleaning and inspection once a year to ensure the setting remains secure. Your piece includes our lifetime craftsmanship warranty.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== RELATED PIECES ===== -->
    @if(count($relatedProducts) > 0)
        <section class="section-jg section-jg-white pd-related-section">
            <div class="container-jg">
                <div class="text-center" style="margin-bottom:1rem;">
                    <span class="eyebrow">You May Also Like</span>
                    <h2 style="font-size:2rem;">Related Pieces</h2>
                </div>
                <div class="product-grid shop-product-grid">
                    @foreach ($relatedProducts as $index => $related)
                        <div class="product-card reveal visible {{ $index > 0 ? 'reveal-delay-' . min($index % 6, 5) : '' }}" wire:key="related-product-{{ $related['id'] }}" onclick="if (event.target.closest('button, a')) return; window.location='{{ route('product-detail', ['id' => $related['id']]) }}'">
                            <div class="img-wrap">
                                <img src="{{ $related['image'] }}" alt="{{ $related['title'] }}">
                            </div>
                            <div class="info">
                                <h3>{{ $related['title'] }}</h3>
                                <div class="details">{{ $related['details'] }}</div>
                                <div class="price">
                                    <span class="current">{{ $related['price'] }}</span>
                                    @if (!empty($related['original_price']) && trim((string) $related['original_price']) !== trim((string) $related['price']))
                                        <span class="original">{{ $related['original_price'] }}</span>
                                    @endif
                                </div>
                                <div class="actions">
                                    <button class="card-tool-btn action-icon-btn wishlist-btn{{ $related['wishlist'] ? ' active' : '' }}" type="button" wire:click.prevent="toggleWishlist('{{ $related['id'] }}')" aria-label="Toggle wishlist">
                                        <i class="bi {{ $related['wishlist'] ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                    </button>
                                    <button class="card-tool-btn action-icon-btn cart-btn{{ $related['cart'] ? ' active' : '' }}" type="button" wire:click.prevent="toggleCart('{{ $related['id'] }}')" @if(!$related['in_stock']) disabled style="opacity:0.5; cursor:not-allowed;" @endif aria-label="Toggle cart">
                                        <i class="bi {{ $related['cart'] ? 'bi-bag-fill' : 'bi-bag' }}"></i>
                                    </button>
                                    <button class="btn-jg btn-jg-primary btn-jg-sm" type="button" onclick="if (event.target.closest('button, a')) return; window.location='{{ route('product-detail', ['id' => $related['id']]) }}'">Buy Now</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@else
    <div class="container-jg text-center" style="padding: 4rem 1rem;">
        <h3>Product not found</h3>
        <p>Return to <a href="{{ route('shop') }}">Shop</a> to browse other collections.</p>
    </div>
@endif
</div>
