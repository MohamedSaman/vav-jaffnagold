<div class="shop-page-shell">
    <div class="page-header page-header-hero">
        <h1>Shop the Collection</h1>
        <div class="breadcrumb-jg">
            <span>Curated pieces crafted with heritage and modern elegance</span>
        </div>
        <div style="margin-top:1.25rem;">
            <a href="{{ route('custom-products') }}" class="btn-jg btn-jg-light shop-customize-btn">Customize Your Own Product</a>
        </div>
    </div>

    <!-- ===== SHOP MARQUEE DISCOUNT TICKER ===== -->
    <div class="shop-marquee-strip">
        <div class="shop-marquee-track">
            @foreach ([false, true] as $isDuplicate)
                <div class="shop-marquee-segment"{{ $isDuplicate ? ' aria-hidden=true' : '' }}>
                    <span class="shop-marquee-item"><i class="bi bi-stars"></i> CELEBRATION OFFER: INSTANT 10% DISCOUNT AUTOMATICALLY CALCULATED ON ALL PIECES <i class="bi bi-diamond-fill"></i></span>
                    <span class="shop-marquee-item"><i class="bi bi-patch-check-fill"></i> GENUINE 22K HALLMARKED GOLD & 925 STERLING SILVER PURITY <i class="bi bi-diamond-fill"></i></span>
                    <span class="shop-marquee-item"><i class="bi bi-truck"></i> COMPLIMENTARY EXPRESS SHIPPING ON ALL ORDERS <i class="bi bi-diamond-fill"></i></span>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        /* ===== SHOP MARQUEE STYLES ===== */
        .shop-marquee-strip {
            background: linear-gradient(90deg, #1f1a16 0%, #3a2e24 50%, #1f1a16 100%);
            border-top: 1px solid rgba(204, 164, 59, 0.35);
            border-bottom: 1px solid rgba(204, 164, 59, 0.35);
            padding: 0.65rem 0;
            overflow: hidden;
            width: 100%;
        }

        .shop-marquee-track {
            display: flex;
            align-items: center;
            width: max-content;
            animation: shopMarqueeTicker 24s linear infinite;
        }

        .shop-marquee-segment {
            display: flex;
            align-items: center;
            flex-shrink: 0;
            gap: 2.2rem;
            padding-right: 2.2rem;
        }

        .shop-marquee-item {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #f3e0ad;
            white-space: nowrap;
        }

        .shop-marquee-item i {
            color: #d4af37;
            font-size: 0.7rem;
        }

        @keyframes shopMarqueeTicker {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        .shop-marquee-strip:hover .shop-marquee-track {
            animation-play-state: paused;
        }

        .shop-page-shell {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .shop-page-shell,
        .shop-page-shell * {
            box-sizing: border-box;
        }

        .shop-page-shell img {
            max-width: 100%;
        }

        .page-header-hero {
            position: relative;
            overflow: hidden;
        }

        .shop-customize-btn {
            background: var(--white);
            color: #000000;
            border: 2px solid rgba(49, 40, 33, 0.72);
            transition:
                background-color 0.3s ease-in-out,
                color 0.3s ease-in-out,
                border-color 0.3s ease-in-out;
        }

        .shop-customize-btn:hover,
        .shop-customize-btn:focus-visible {
            background: #000000;
            color: var(--white);
            border-color: #000000;
            outline: none;
        }

        .shop-customize-btn:focus-visible {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2), 0 0 0 5px rgba(0, 0, 0, 0.45);
        }

        .shop-product-summary {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.35rem;
            margin-bottom: 1.75rem;
            width: 100%;
            max-width: 100%;
        }

        .shop-product-summary .count {
            color: #d1960e;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.12em;
            line-height: 1.5;
            white-space: nowrap;
            flex: 0 0 auto;
        }

        .shop-filter-shell {
            position: relative;
            z-index: 4;
            flex: 0 0 auto;
        }

        .shop-filter-trigger {
            display: inline-flex;
            align-items: stretch;
            min-width: 236px;
            padding: 0;
            border: 1px solid #eadfcd;
            border-radius: 6px;
            background: #ffffff;
            box-shadow: 0 10px 24px rgba(73, 55, 38, 0.08);
            overflow: hidden;
            cursor: pointer;
            transition: box-shadow 0.22s ease, transform 0.22s ease;
        }

        .shop-filter-trigger:hover,
        .shop-filter-trigger:focus-visible {
            box-shadow: 0 14px 28px rgba(73, 55, 38, 0.12);
            transform: translateY(-1px);
            outline: none;
        }

        .shop-filter-trigger-icon {
            width: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #2c211a;
            color: #ffffff;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .shop-filter-trigger-label {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            padding: 0 1rem 0 1.05rem;
            color: #43362b;
            font-size: 0.98rem;
            font-weight: 500;
        }

        .shop-filter-trigger-label i {
            font-size: 1.45rem;
            color: #1f1712;
            transition: transform 0.22s ease;
        }

        .shop-filter-shell.is-open .shop-filter-trigger-label i {
            transform: rotate(180deg);
        }

        .shop-filter-overlay {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background:
                linear-gradient(180deg, rgba(39, 33, 27, 0.42) 0%, rgba(39, 33, 27, 0.62) 100%);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.22s ease, visibility 0s linear 0.22s;
            z-index: 1200;
        }

        .shop-filter-shell.is-open .shop-filter-overlay {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transition-delay: 0s;
        }

        .shop-filter-panel {
            position: absolute;
            top: 50%;
            left: 50%;
            width: min(560px, calc(100vw - 2rem));
            max-width: 100%;
            padding: 0.95rem;
            border: 1px solid #eadfcd;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 24px 54px rgba(44, 33, 26, 0.18);
            transform: translate(-50%, calc(-50% + 10px));
            transition: transform 0.22s ease;
        }

        .shop-filter-shell.is-open .shop-filter-panel {
            transform: translate(-50%, -50%);
        }

        .shop-filter-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.8rem;
        }

        .shop-filter-panel-title {
            color: #43362b;
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .shop-filter-close {
            width: 38px;
            height: 38px;
            border: 1px solid #eadfcd;
            border-radius: 999px;
            background: #ffffff;
            color: #43362b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .shop-filter-close:hover,
        .shop-filter-close:focus-visible {
            background: #f8f3eb;
            border-color: #d9cbb7;
            outline: none;
        }

        .shop-filter-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .shop-filter-field {
            display: flex;
            min-width: 0;
        }

        .shop-filter-field select,
        .shop-filter-field input {
            width: 100%;
            min-height: 42px;
            padding: 0.68rem 0.85rem;
            border: 1px solid #e7dccb;
            border-radius: 10px;
            background: #fffdfa;
            color: #2f251d;
            font-size: 0.88rem;
        }

        .shop-filter-field select {
            appearance: none;
            background-image:
                linear-gradient(45deg, transparent 50%, #756556 50%),
                linear-gradient(135deg, #756556 50%, transparent 50%);
            background-position:
                calc(100% - 20px) calc(50% - 3px),
                calc(100% - 14px) calc(50% - 3px);
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
            padding-right: 2.45rem;
        }

        .shop-filter-field input::placeholder {
            color: #9d9081;
        }

        .shop-filter-field select:focus-visible,
        .shop-filter-field input:focus-visible,
        .shop-filter-trigger:focus-visible,
        .shop-filter-action:focus-visible {
            outline: 2px solid rgba(184, 138, 43, 0.35);
            outline-offset: 2px;
        }

        .shop-filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 0.8rem;
            padding-top: 0.8rem;
            border-top: 1px solid #f1e7d9;
        }

        .shop-filter-action {
            min-height: 42px;
            padding: 0.72rem 1rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .shop-filter-action--ghost {
            border: 1px solid #dbcfbd;
            background: #ffffff;
            color: #5d4e42;
        }

        .shop-filter-action--ghost.is-disabled,
        .shop-filter-action--ghost:disabled {
            border-color: #e6dfd4;
            background: #ece7de;
            color: #a09586;
            cursor: not-allowed;
            pointer-events: none;
        }

        .shop-filter-action--ghost:hover {
            background: #f8f3eb;
        }

        .shop-filter-action--primary {
            border: 1px solid #64503c;
            background: #64503c;
            color: #ffffff;
        }

        .shop-filter-action--primary:hover {
            background: #544231;
            border-color: #544231;
        }

        .shop-product-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .shop-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2.5rem;
            flex-wrap: wrap;
        }

        .shop-pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            padding: 0.4rem 0.75rem;
            border: 1px solid #e0d5c4;
            border-radius: 8px;
            background: #ffffff;
            color: #43362b;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.22s ease;
            text-decoration: none;
        }

        .shop-pagination-btn:hover:not(.is-disabled):not(.is-active) {
            background: #f8f3eb;
            border-color: #d4af37;
            color: #2c211a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.15);
        }

        .shop-pagination-btn.is-active {
            background: linear-gradient(135deg, #2c211a 0%, #43362b 100%);
            color: #f3e0ad;
            border-color: #2c211a;
            cursor: default;
            box-shadow: 0 4px 14px rgba(44, 33, 26, 0.25);
        }

        .shop-pagination-btn.is-disabled {
            opacity: 0.35;
            cursor: not-allowed;
            pointer-events: none;
        }

        .shop-pagination-dots {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 42px;
            color: #9d9081;
            font-size: 1rem;
            letter-spacing: 0.15em;
        }

        .shop-pagination-info {
            text-align: center;
            margin-top: 0.85rem;
            color: #9d9081;
            font-size: 0.78rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 500;
        }

        @media (max-width: 1024px) {
            .shop-product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .shop-pagination {
                flex-direction: row;
                flex-wrap: nowrap;
                align-items: center;
                justify-content: flex-start;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                padding: 0.25rem 0;
                width: 100%;
                max-width: 100%;
            }

            .shop-pagination::-webkit-scrollbar {
                display: none;
            }

            .shop-pagination-links {
                justify-content: flex-start;
                flex-wrap: nowrap;
            }

            .shop-pagination-btn,
            .shop-pagination-dots {
                flex: 0 0 auto;
            }
        }

        @media (max-width: 768px) {
            .shop-product-summary {
                justify-content: center;
                gap: 0.85rem;
            }

            .shop-filter-shell {
                width: auto;
                display: block;
            }

            .shop-filter-trigger {
                width: auto;
            }

            .shop-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .shop-product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 480px) {
            .shop-product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.375rem;
            }

            .shop-product-summary {
                justify-content: center;
                align-items: center;
                flex-wrap: nowrap;
                margin-top: 1.1rem;
                margin-bottom: 1.45rem;
                gap: 0.55rem;
            }

            .shop-product-summary .count {
                font-size: 0.58rem;
                letter-spacing: 0.08em;
                text-align: left;
                white-space: nowrap;
                flex: 0 1 auto;
                min-width: 0;
            }

            .shop-filter-trigger {
                min-width: 0;
                width: auto;
            }

            .shop-filter-shell {
                flex: 0 0 auto;
            }

            .shop-pagination-links {
                gap: 0.3rem;
            }

            .shop-pagination {
                gap: 0.4rem;
                justify-content: center;
            }

            .shop-pagination-btn {
                width: 38px;
                height: 38px;
                min-width: 38px;
                padding: 0.25rem 0.5rem;
                flex: 0 0 auto;
            }

            .shop-pagination-dots {
                min-width: 24px;
                height: 38px;
                flex: 0 0 auto;
            }

            .shop-pagination-button {
                width: 38px;
                height: 38px;
            }

            .shop-filter-trigger-icon {
                width: 50px;
            }

            .shop-filter-trigger-label {
                padding: 0 0.75rem 0 0.8rem;
                gap: 0.55rem;
                font-size: 0.9rem;
            }

            .shop-filter-overlay {
                padding: 0.85rem;
            }

            .shop-filter-panel {
                width: min(94vw, 350px);
                padding: 0.85rem;
            }

            .shop-filter-panel-header {
                margin-bottom: 0.7rem;
            }

            .shop-filter-grid {
                gap: 0.65rem;
            }

            .shop-filter-field select,
            .shop-filter-field input {
                min-height: 40px;
                padding: 0.62rem 0.8rem;
                font-size: 0.84rem;
            }

            .shop-filter-actions {
                flex-direction: row;
                gap: 0.55rem;
            }

            .shop-filter-action {
                flex: 1 1 0;
                min-width: 0;
            }
        }
        .discount-badge-anim {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: linear-gradient(135deg, #d4af37 0%, #aa8214 100%);
            color: #ffffff;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            padding: 0.28rem 0.65rem;
            border-radius: 999px;
            box-shadow: 0 4px 14px rgba(170, 130, 20, 0.45);
            z-index: 3;
            animation: pulseMini 2s infinite ease-in-out;
            text-transform: uppercase;
        }

        @keyframes pulseMini {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
    </style>

    <section class="section-jg section-jg-cream" style="padding-top:2rem;">
        <div class="container-jg">
            <div class="shop-product-summary">
                <span class="count">Showing {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} of {{ $totalProducts }} products</span>
                <div class="shop-filter-shell" data-shop-filter-shell>
                    <button
                        type="button"
                        class="shop-filter-trigger"
                        data-shop-filter-trigger
                        aria-expanded="false"
                        aria-haspopup="true"
                        aria-controls="shop-filter-panel"
                    >
                        <span class="shop-filter-trigger-icon">
                            <i class="bi bi-sliders2"></i>
                        </span>
                        <span class="shop-filter-trigger-label">
                            <span>{{ $selectedCategory !== '' || $selectedBrand !== '' || $selectedAvailability !== '' || $minPrice !== '' || $maxPrice !== '' ? 'Filtered' : 'All Products' }}</span>
                            <i class="bi bi-chevron-down"></i>
                        </span>
                    </button>

                    @php
                        $hasActiveFilters = $selectedCategory !== ''
                            || $selectedBrand !== ''
                            || $selectedAvailability !== ''
                            || $minPrice !== ''
                            || $maxPrice !== ''
                            || $sortBy !== 'newest';
                    @endphp

                    <div class="shop-filter-overlay" data-shop-filter-overlay>
                        <div class="shop-filter-panel" id="shop-filter-panel" data-shop-filter-panel role="dialog" aria-modal="true" aria-label="Filter products">
                            <div class="shop-filter-panel-header">
                                <span class="shop-filter-panel-title">Filter Products</span>
                                <button type="button" class="shop-filter-close" data-shop-filter-close aria-label="Close filters">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <div class="shop-filter-grid">
                                <div class="shop-filter-field">
                                    <select id="shop-category-filter" wire:model="selectedCategory" aria-label="Category">
                                        <option value="">Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="shop-filter-field">
                                    <select id="shop-brand-filter" wire:model="selectedBrand" aria-label="Brand">
                                        <option value="">Brand</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="shop-filter-field">
                                    <select id="shop-availability-filter" wire:model="selectedAvailability" aria-label="Availability">
                                        <option value="">Availability</option>
                                        <option value="in_stock">In Stock</option>
                                        <option value="out_of_stock">Out of Stock</option>
                                    </select>
                                </div>

                                <div class="shop-filter-field">
                                    <select id="shop-sort-filter" wire:model="sortBy" aria-label="Sort by">
                                        <option value="newest">Sort By</option>
                                        <option value="newest">Newest</option>
                                        <option value="price_asc">Price Low - High</option>
                                        <option value="price_desc">Price High - Low</option>
                                        <option value="name_asc">Name A - Z</option>
                                        <option value="name_desc">Name Z - A</option>
                                    </select>
                                </div>

                                <div class="shop-filter-field">
                                    <input id="shop-min-price" type="number" min="0" step="0.01" placeholder="Min Price" wire:model="minPrice" aria-label="Minimum price">
                                </div>

                                <div class="shop-filter-field">
                                    <input id="shop-max-price" type="number" min="0" step="0.01" placeholder="Max Price" wire:model="maxPrice" aria-label="Maximum price">
                                </div>
                            </div>

                            <div class="shop-filter-actions">
                                <button
                                    type="button"
                                    class="shop-filter-action shop-filter-action--ghost{{ $hasActiveFilters ? '' : ' is-disabled' }}"
                                    @if($hasActiveFilters)
                                        wire:click="clearFilters"
                                        data-shop-filter-close
                                    @else
                                        disabled
                                    @endif
                                >
                                    Clear Filter
                                </button>
                                <button type="button" class="shop-filter-action shop-filter-action--primary" wire:click="applyFilters" data-shop-filter-close>Apply Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="product-grid shop-product-grid">
                @forelse ($products as $index => $product)
                    <div class="product-card reveal visible {{ $index > 0 ? 'reveal-delay-' . min($index % 6, 5) : '' }}" wire:key="shop-product-{{ $product['id'] }}" onclick="if (event.target.closest('button, a')) return; window.location='{{ route('product-detail', ['id' => $product['id']]) }}'">
                        <div class="img-wrap">
                            <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}">
                            <span class="discount-badge-anim">10% OFF</span>
                            @if ($product['tag'])
                                <span class="tag">{{ $product['tag'] }}</span>
                            @endif
                        </div>
                        <div class="info">
                            <h3>{{ $product['title'] }}</h3>
                            <div class="details">{{ $product['details'] ?? $product['subtitle'] }}</div>
                            <div class="price">
                                <span class="current">{{ $product['price'] }}</span>
                                @if (!empty($product['original_price']) && trim((string) $product['original_price']) !== trim((string) $product['price']))
                                    <span class="original">{{ $product['original_price'] }}</span>
                                @endif
                            </div>
                            <div class="actions">
                                <button class="card-tool-btn action-icon-btn wishlist-btn{{ $product['wishlist'] ? ' active' : '' }}" type="button" wire:click.prevent="toggleWishlist('{{ $product['id'] }}')">
                                    <i class="bi {{ $product['wishlist'] ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                </button>
                                <button class="card-tool-btn action-icon-btn cart-btn{{ $product['cart'] ? ' active' : '' }}" type="button" wire:click.prevent="toggleCart('{{ $product['id'] }}')" @if(!$product['in_stock']) disabled style="opacity:0.5; cursor:not-allowed;" @endif>
                                    <i class="bi {{ $product['cart'] ? 'bi-bag-fill' : 'bi-bag' }}"></i>
                                </button>
                                <button class="btn-jg btn-jg-primary btn-jg-sm" type="button" wire:click.prevent="buyNow('{{ $product['id'] }}')" @if(!$product['in_stock']) disabled style="opacity:0.5;" @endif>Buy Now</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="grid-column:1 / -1;">
                        <i class="bi bi-search"></i>
                        <h3>No products found</h3>
                        <p>New pieces will appear here as soon as they are available.</p>
                    </div>
                @endforelse
            </div>

            @if ($paginator->hasPages())
                <div class="shop-pagination">
                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <span class="shop-pagination-btn is-disabled"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <button type="button" class="shop-pagination-btn" wire:click="previousPage" wire:loading.attr="disabled"><i class="bi bi-chevron-left"></i></button>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $paginator->currentPage();
                        $lastPage = $paginator->lastPage();
                        $pages = [];

                        if ($lastPage <= 7) {
                            $pages = range(1, $lastPage);
                        } else {
                            $pages[] = 1;
                            if ($currentPage > 3) $pages[] = '...';
                            for ($i = max(2, $currentPage - 1); $i <= min($lastPage - 1, $currentPage + 1); $i++) {
                                $pages[] = $i;
                            }
                            if ($currentPage < $lastPage - 2) $pages[] = '...';
                            $pages[] = $lastPage;
                        }
                    @endphp

                    @foreach ($pages as $page)
                        @if ($page === '...')
                            <span class="shop-pagination-dots">&hellip;</span>
                        @elseif ($page == $currentPage)
                            <span class="shop-pagination-btn is-active">{{ $page }}</span>
                        @else
                            <button type="button" class="shop-pagination-btn" wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled">{{ $page }}</button>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <button type="button" class="shop-pagination-btn" wire:click="nextPage" wire:loading.attr="disabled"><i class="bi bi-chevron-right"></i></button>
                    @else
                        <span class="shop-pagination-btn is-disabled"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </div>

                <div class="shop-pagination-info">
                    Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }} &middot; {{ $totalProducts }} products
                </div>
            @endif
        </div>
    </section>

    <script>
        (() => {
            const initShopFilterDropdown = () => {
                const shell = document.querySelector('[data-shop-filter-shell]');
                if (!shell || shell.dataset.shopFilterBound === 'true') {
                    return;
                }

                const trigger = shell.querySelector('[data-shop-filter-trigger]');
                const overlay = shell.querySelector('[data-shop-filter-overlay]');
                const panel = shell.querySelector('[data-shop-filter-panel]');
                const closeButtons = shell.querySelectorAll('[data-shop-filter-close]');

                if (!trigger || !panel || !overlay) {
                    return;
                }

                shell.dataset.shopFilterBound = 'true';

                const setOpen = (open) => {
                    shell.classList.toggle('is-open', open);
                    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                };

                trigger.addEventListener('click', () => {
                    setOpen(!shell.classList.contains('is-open'));
                });

                closeButtons.forEach((button) => {
                    button.addEventListener('click', () => setOpen(false));
                });

                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) {
                        setOpen(false);
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        setOpen(false);
                    }
                });
            };

            document.addEventListener('livewire:initialized', initShopFilterDropdown);
            document.addEventListener('livewire:navigated', initShopFilterDropdown);
            window.addEventListener('load', initShopFilterDropdown);
        })();
    </script>
</div>
