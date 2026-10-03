<div class="custom-products-page">
    {{-- ===== HERO & HEADER ===== --}}
    <div class="custom-hero">
        <div class="custom-hero-overlay"></div>
        <div class="container-jg custom-hero-content">
            <div class="custom-hero-badge">
                <i class="bi bi-gem"></i> Premium Gold Covering Collection • தனிப்பயன் தங்கக் கவரிங் நகைகள்
            </div>
            <h1 class="custom-hero-title">
                Custom Masterpieces <span class="gold-gradient-text">In Gold Covering & Plating</span>
            </h1>
            <p class="custom-hero-subtitle">
                Handcrafted with authentic Jaffna goldsmith patterns using high-micron micro gold plating. Experience the exact luster, grandeur, and prestige of traditional 22K gold — from iconic <strong>Star Kodi & Veera Kodi</strong> to signature <strong>Lock SL Bangles</strong> and faceted <strong>Thakar Rings</strong> at affordable piece prices, completely free of gold gram rates or market surcharges.
            </p>

            {{-- Quality & Value Highlights Bar --}}
            <div class="rate-indicator-bar">
                <div class="rate-pill">
                    <span class="pulse-dot"></span>
                    <span class="rate-label">Finish:</span>
                    <strong class="rate-val">Premium Micro Gold Plated (உயர் ரக முலாம்)</strong>
                </div>
                <div class="rate-meta">
                    <span><i class="bi bi-shield-check text-gold"></i> Long-Lasting Color Guarantee</span>
                    <span class="divider-dot">•</span>
                    <span><i class="bi bi-tag-fill text-gold"></i> Fixed Piece Pricing (No Gram Rate)</span>
                    <span class="divider-dot">•</span>
                    <span><i class="bi bi-truck text-gold"></i> Island-wide Courier & Store Pickup</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== INTERACTIVE GOLD COVERING PRICE ESTIMATOR WIDGET ===== --}}
    <section class="calc-section">
        <div class="container-jg">
            <div class="calc-card">
                <div class="calc-header">
                    <div>
                        <span class="calc-tag"><i class="bi bi-sliders2"></i> Instant Design & Price Estimator</span>
                        <h2 class="calc-title">Custom Covering Estimator (தங்கக் கவரிங் கணிப்பான்)</h2>
                    </div>
                    <div class="calc-note">
                        <i class="bi bi-check2-circle text-gold"></i> Fixed Piece Pricing • No Gold Rate Multipliers
                    </div>
                </div>

                <div class="calc-body">
                    <div class="calc-controls">
                        {{-- Category Selector --}}
                        <div class="calc-group">
                            <label class="calc-label">1. Select Jewelry Category</label>
                            <div class="calc-cat-grid">
                                <button type="button" wire:click="$set('calcCategory', 'kodi')" class="calc-cat-btn {{ $calcCategory === 'kodi' ? 'active' : '' }}">
                                    <i class="bi bi-link-45deg"></i> Kodi / Chains
                                </button>
                                <button type="button" wire:click="$set('calcCategory', 'bangle')" class="calc-cat-btn {{ $calcCategory === 'bangle' ? 'active' : '' }}">
                                    <i class="bi bi-circle"></i> SL Bangles
                                </button>
                                <button type="button" wire:click="$set('calcCategory', 'ring')" class="calc-cat-btn {{ $calcCategory === 'ring' ? 'active' : '' }}">
                                    <i class="bi bi-gem"></i> Rings
                                </button>
                                <button type="button" wire:click="$set('calcCategory', 'bridal_thali')" class="calc-cat-btn {{ $calcCategory === 'bridal_thali' ? 'active' : '' }}">
                                    <i class="bi bi-heart"></i> Bridal Thali Sets
                                </button>
                            </div>
                        </div>

                        {{-- Size / Length Selection --}}
                        <div class="calc-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="calc-label mb-0">2. Select Preferred Size / Length</label>
                                <span class="calc-weight-badge">Selected: <strong>{{ $calcLengthSize }}</strong></span>
                            </div>

                            <div class="calc-pown-presets mt-2">
                                @if($calcCategory === 'kodi')
                                    @foreach(['20"', '22"', '24"', '26"', '28"', '30"'] as $preset)
                                        <button type="button" wire:click="$set('calcLengthSize', '{{ $preset }}')" class="pown-preset-btn {{ $calcLengthSize === $preset ? 'active' : '' }}">
                                            {{ $preset }} Length
                                        </button>
                                    @endforeach
                                @elseif($calcCategory === 'bangle')
                                    @foreach(['2.2', '2.4', '2.6', '2.8'] as $preset)
                                        <button type="button" wire:click="$set('calcLengthSize', '{{ $preset }}')" class="pown-preset-btn {{ $calcLengthSize === $preset ? 'active' : '' }}">
                                            Size {{ $preset }}
                                        </button>
                                    @endforeach
                                @elseif($calcCategory === 'ring')
                                    @foreach(['12', '14', '16', '18', '20', '22'] as $preset)
                                        <button type="button" wire:click="$set('calcLengthSize', '{{ $preset }}')" class="pown-preset-btn {{ $calcLengthSize === $preset ? 'active' : '' }}">
                                            Size {{ $preset }}
                                        </button>
                                    @endforeach
                                @else
                                    <button type="button" wire:click="$set('calcLengthSize', 'Bridal Set (26\")')" class="pown-preset-btn {{ $calcLengthSize === 'Bridal Set (26\")' ? 'active' : '' }}">
                                        Ceremonial Set (26")
                                    </button>
                                    <button type="button" wire:click="$set('calcLengthSize', 'Grand Bridal Set (28\")')" class="pown-preset-btn {{ $calcLengthSize === 'Grand Bridal Set (28\")' ? 'active' : '' }}">
                                        Grand Bridal Set (28")
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Plating / Coating Tier --}}
                        <div class="calc-group mt-3">
                            <label class="calc-label">3. Select Plating Finish & Durability</label>
                            <div class="plating-options-grid">
                                <button type="button" wire:click="$set('calcPlatingType', 'standard')" class="plating-option-btn {{ $calcPlatingType === 'standard' ? 'active' : '' }}">
                                    <div class="plating-btn-title">Standard Micro Covering</div>
                                    <div class="plating-btn-sub">22K gold color with daily protective seal</div>
                                </button>
                                <button type="button" wire:click="$set('calcPlatingType', 'double_coat')" class="plating-option-btn {{ $calcPlatingType === 'double_coat' ? 'active' : '' }}">
                                    <div class="plating-btn-title">Double-Coat Micro Plated <span class="badge-rec">Recommended</span></div>
                                    <div class="plating-btn-sub">1-Year color guarantee & anti-tarnish coat</div>
                                </button>
                                <button type="button" wire:click="$set('calcPlatingType', 'royal_finish')" class="plating-option-btn {{ $calcPlatingType === 'royal_finish' ? 'active' : '' }}">
                                    <div class="plating-btn-title">Royal Antique / High Luster</div>
                                    <div class="plating-btn-sub">Deeper traditional tone for bridal wear</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Estimator Result Box --}}
                    <div class="calc-result-box">
                        <div class="result-header">Estimated Covering Piece Price</div>
                        <div class="result-price">
                            LKR {{ number_format($calcPriceMin, 0) }} – {{ number_format($calcPriceMax, 0) }}
                        </div>
                        <div class="result-details">
                            <div class="detail-row">
                                <span>Category & Sizing:</span>
                                <strong>{{ ucfirst($calcCategory) }} ({{ $calcLengthSize }})</strong>
                            </div>
                            <div class="detail-row">
                                <span>Plating Quality:</span>
                                <strong>{{ $calcPlatingType === 'double_coat' ? 'Double-Coat (1-Yr Guarantee)' : ($calcPlatingType === 'royal_finish' ? 'Royal Antique Luster' : 'Standard Micro Covering') }}</strong>
                            </div>
                            <div class="detail-row">
                                <span>Design Molds:</span>
                                <strong>Authentic Jaffna Heritage Dies</strong>
                            </div>
                            <div class="detail-row">
                                <span>Price Policy:</span>
                                <strong>Fixed Piece Price (No Gram Rate)</strong>
                            </div>
                        </div>
                        <div class="result-action">
                            <a href="#customCatalog" class="btn-calc-scroll">
                                Browse Available Covering Designs <i class="bi bi-arrow-down-short"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== MAIN CATALOG & FILTER SECTION ===== --}}
    <section class="catalog-section" id="customCatalog">
        <div class="container-jg">
            {{-- Filter Bar --}}
            <div class="catalog-toolbar">
                <div class="category-tabs">
                    <button type="button" wire:click="setCategory('all')" class="cat-tab {{ $selectedCategory === 'all' ? 'active' : '' }}">
                        All Custom Pieces ({{ \App\Models\CustomProduct::active()->count() }})
                    </button>
                    <button type="button" wire:click="setCategory('kodi')" class="cat-tab {{ $selectedCategory === 'kodi' ? 'active' : '' }}">
                        <i class="bi bi-link-45deg"></i> Kodi (Chains)
                    </button>
                    <button type="button" wire:click="setCategory('bangle')" class="cat-tab {{ $selectedCategory === 'bangle' ? 'active' : '' }}">
                        <i class="bi bi-circle"></i> SL Bangles
                    </button>
                    <button type="button" wire:click="setCategory('ring')" class="cat-tab {{ $selectedCategory === 'ring' ? 'active' : '' }}">
                        <i class="bi bi-gem"></i> Rings
                    </button>
                    <button type="button" wire:click="setCategory('bridal_thali')" class="cat-tab {{ $selectedCategory === 'bridal_thali' ? 'active' : '' }}">
                        <i class="bi bi-heart"></i> Bridal & Thali Sets
                    </button>
                </div>

                <div class="toolbar-controls">
                    {{-- Price Range Filter --}}
                    <div class="control-select-wrap">
                        <label class="control-label">Price Filter:</label>
                        <select wire:model.live="selectedPriceRange" class="filter-select">
                            <option value="all">All Covering Prices</option>
                            <option value="under_3000">Under LKR 3,000</option>
                            <option value="3000_to_6000">LKR 3,000 – 6,000</option>
                            <option value="6000_to_10000">LKR 6,000 – 10,000</option>
                            <option value="10000_plus">LKR 10,000+</option>
                        </select>
                    </div>

                    {{-- Search Input --}}
                    <div class="search-wrap">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search covering item (e.g. Star kodi, lock bangle, takar ring)..." class="filter-search-input">
                    </div>
                </div>
            </div>

            {{-- Products Grid --}}
            @if($products->isEmpty())
                <div class="empty-state-card">
                    <i class="bi bi-gem empty-icon"></i>
                    <h3>No custom designs match your filter</h3>
                    <p>Try resetting filters or contact our Jaffna workshop directly for bespoke covering commissions.</p>
                    <button type="button" wire:click="resetFilters" class="btn-reset-filters">Reset All Filters</button>
                </div>
            @else
                <div class="custom-products-grid">
                    @foreach($products as $product)
                        @php
                            $cardImages = $product->all_image_urls;
                        @endphp
                        <div class="custom-card" wire:key="custom-prod-{{ $product->id }}"
                             x-data="{
                                 images: {{ json_encode($cardImages) }},
                                 activeIdx: 0,
                                 next() { this.activeIdx = (this.activeIdx + 1) % this.images.length },
                                 prev() { this.activeIdx = (this.activeIdx - 1 + this.images.length) % this.images.length },
                                 select(idx) { this.activeIdx = idx }
                             }">
                            {{-- Image Container --}}
                            <div class="card-img-container">
                                <template x-for="(img, idx) in images" :key="'card-img-'+idx">
                                    <img x-show="activeIdx === idx"
                                         :src="img"
                                         alt="{{ $product->name }}"
                                         class="card-img"
                                         loading="lazy">
                                </template>

                                <div class="card-purity-badge">
                                    <i class="bi bi-patch-check-fill"></i> Gold Covering Plated
                                </div>
                                <div class="card-category-badge">
                                    {{ $product->category_label }}
                                </div>

                                @if(count($cardImages) > 1)
                                    <!-- Prev / Next Navigation Arrows -->
                                    <button type="button" @click.stop="prev()" class="card-nav-arrow card-nav-arrow-left" aria-label="Previous photo">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <button type="button" @click.stop="next()" class="card-nav-arrow card-nav-arrow-right" aria-label="Next photo">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>

                                    <!-- Dots Navigation -->
                                    <div class="card-nav-dots" @click.stop>
                                        <template x-for="(img, idx) in images" :key="'dot-'+idx">
                                            <span class="card-dot" :class="{ 'active': activeIdx === idx }" @click.stop="select(idx)"></span>
                                        </template>
                                    </div>

                                    <!-- Photo Count Badge -->
                                    <div class="card-photo-count-pill" @click.stop>
                                        <i class="bi bi-images"></i> <span x-text="(activeIdx + 1) + '/' + images.length"></span>
                                    </div>
                                @endif
                            </div>

                            {{-- Preview All Images Option Thumbnails --}}
                            @if(count($cardImages) > 1)
                                <div class="card-thumbs-strip" @click.stop>
                                    <span class="card-thumbs-strip-label"><i class="bi bi-camera"></i> Photos:</span>
                                    <div class="card-thumbs-list">
                                        <template x-for="(img, idx) in images" :key="'thumb-'+idx">
                                            <button type="button"
                                                    class="card-thumb-btn"
                                                    :class="{ 'active': activeIdx === idx }"
                                                    @click.stop="select(idx)"
                                                    :title="'View Photo ' + (idx + 1)">
                                                <img :src="img" :alt="'Photo ' + (idx + 1)">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            @endif

                            {{-- Card Body --}}
                            <div class="card-body">
                                <div class="card-header-row">
                                    <div>
                                        <h3 class="card-title">{{ $product->name }}</h3>
                                        @if($product->tamil_name)
                                            <span class="card-tamil-title">{{ $product->tamil_name }}</span>
                                        @endif
                                    </div>
                                    <span class="card-pown-span">
                                        @if($product->category === 'kodi')
                                            Lengths: 20" – 28"
                                        @elseif($product->category === 'bangle')
                                            Sizes: 2.2 – 2.8
                                        @elseif($product->category === 'ring')
                                            Sizes: 12 – 22
                                        @else
                                            Ceremonial Set
                                        @endif
                                    </span>
                                </div>

                                <p class="card-desc">
                                    {{ Str::limit($product->description, 135) }}
                                </p>

                                {{-- Available Sizes / Options Pills --}}
                                <div class="pown-options-group">
                                    <span class="pown-label">Available Sizes / Lengths:</span>
                                    <div class="pown-chips">
                                        @foreach($product->pown_options ?? [] as $opt)
                                            @php
                                                $optLabel = $product->category === 'kodi' ? ($opt . '" Length') : ($product->category === 'bangle' ? 'Size ' . $opt : ($product->category === 'ring' ? 'Size ' . $opt : $opt . '" Set'));
                                            @endphp
                                            <span class="pown-chip" wire:click="openInquiryModal({{ $product->id }}, '{{ $opt }}')" title="Click to customize {{ $optLabel }}">
                                                <strong>{{ $opt }}</strong>{{ $product->category === 'kodi' ? '"' : '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Price Block (Fixed Covering Piece Price) --}}
                                <div class="card-price-block">
                                    <div class="price-range-label">Piece Price Range (Gold Covering)</div>
                                    <div class="price-range-val">
                                        LKR {{ number_format($product->price_range_min, 0) }} – {{ number_format($product->price_range_max, 0) }}
                                    </div>
                                    <div class="price-per-pown">
                                        Fixed piece price • No gold gram rate
                                    </div>
                                </div>

                                {{-- Feature Specs --}}
                                @if(!empty($product->specifications))
                                    <ul class="card-spec-list">
                                        @foreach(array_slice($product->specifications, 0, 2) as $spec)
                                            <li><i class="bi bi-check2-circle"></i> {{ $spec }}</li>
                                        @endforeach
                                    </ul>
                                @endif

                                {{-- Actions --}}
                                <div class="card-actions">
                                    <button type="button" wire:click="openInquiryModal({{ $product->id }})" class="btn-customize-inquire">
                                        <i class="bi bi-sliders"></i> Customize & Inquire
                                    </button>
                                    @php
                                        $waQuickMsg = urlencode("Vanakkam Jaffna Gold! I am interested in custom gold covering jewelry: {$product->name} ({$product->tamil_name}). Price Range: LKR " . number_format($product->price_range_min, 0) . " - " . number_format($product->price_range_max, 0) . ". Please provide custom details.");
                                    @endphp
                                    <a href="https://wa.me/94770000000?text={{ $waQuickMsg }}" target="_blank" rel="noopener" class="btn-card-wa" title="Order via WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ===== WHY CHOOSE JAFFNA GOLD COVERING CRAFTSMANSHIP ===== --}}
    <section class="heritage-section">
        <div class="container-jg">
            <div class="heritage-grid">
                <div class="heritage-box">
                    <div class="heritage-icon"><i class="bi bi-stars"></i></div>
                    <h3>Multi-Layer Micro Gold Plated</h3>
                    <p>High-micron electroplating delivers the identical 22K yellow luster, color depth, and radiant shine of pure gold without peeling or fading.</p>
                </div>
                <div class="heritage-box">
                    <div class="heritage-icon"><i class="bi bi-hammer"></i></div>
                    <h3>Authentic Jaffna Goldsmith Dies</h3>
                    <p>Crafted using authentic traditional dies and molds: Star relief links, twisted Veera ropes, traditional Kaappu locks, and diamond-cut Thakar rings.</p>
                </div>
                <div class="heritage-box">
                    <div class="heritage-icon"><i class="bi bi-shield-check"></i></div>
                    <h3>Anti-Tarnish Protective Shield</h3>
                    <p>Protected by a microscopic hypoallergenic lacquer coat to resist perspiration, moisture, and daily humidity for long-lasting color preservation.</p>
                </div>
                <div class="heritage-box">
                    <div class="heritage-icon"><i class="bi bi-wallet2"></i></div>
                    <h3>Affordable Fixed Piece Prices</h3>
                    <p>Enjoy royal elegance for weddings, temple festivals, and festive celebrations at accessible piece prices with zero gold gram rate fluctuations.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== CUSTOMIZER & INQUIRY MODAL ===== --}}
    @if($showInquiryModal && $activeProduct)
        <div class="modal-backdrop-jg" wire:click.self="closeInquiryModal">
            <div class="modal-card-jg">
                <div class="modal-header-jg">
                    <div class="modal-title-wrap">
                        <span class="modal-eyebrow">Custom Gold Covering Order Request</span>
                        <h2 class="modal-title">{{ $activeProduct->name }}</h2>
                        @if($activeProduct->tamil_name)
                            <span class="modal-tamil">{{ $activeProduct->tamil_name }}</span>
                        @endif
                    </div>
                    <button type="button" class="modal-close-btn" wire:click="closeInquiryModal" aria-label="Close modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="modal-body-jg">
                    @if($inquirySubmitted)
                        {{-- Success Submission View --}}
                        <div class="submission-success">
                            <div class="success-icon-wrap">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <h3>Custom Order Inquiry Received!</h3>
                            <p class="success-inquiry-num">Inquiry Ref: <strong>{{ $submittedInquiryNumber }}</strong></p>
                            <div class="success-alert">
                                <i class="bi bi-phone-vibrate"></i>
                                An SMS confirmation has been dispatched to <strong>{{ $inquiryCustomerPhone }}</strong>. Our team in Jaffna will contact you shortly to finalize your custom size, plating finish, and dispatch date.
                            </div>

                            <div class="success-summary-box">
                                <div class="summary-item">
                                    <span>Product:</span> <strong>{{ $activeProduct->name }}</strong>
                                </div>
                                <div class="summary-item">
                                    <span>Selected Size / Length:</span> <strong>{{ $inquirySize }}</strong>
                                </div>
                                <div class="summary-item">
                                    <span>Plating Finish:</span> 
                                    <strong>{{ $inquiryPlating === 'double_coat' ? 'Double-Coat (1-Yr Guarantee)' : ($inquiryPlating === 'royal_finish' ? 'Royal Antique Luster' : 'Standard Micro Covering') }}</strong>
                                </div>
                                <div class="summary-item">
                                    <span>Estimated Piece Price:</span> <strong>LKR {{ number_format($this->calculateEstimatedPrice(), 0) }}</strong>
                                </div>
                            </div>

                            <div class="success-actions">
                                @if($whatsAppUrl)
                                    <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener" class="btn-success-wa">
                                        <i class="bi bi-whatsapp"></i> Continue Discussion on WhatsApp
                                    </a>
                                @endif
                                <button type="button" wire:click="closeInquiryModal" class="btn-success-close">Done</button>
                            </div>
                        </div>
                    @else
                        {{-- Customization Form --}}
                        <div class="modal-form-grid">
                            {{-- Left Column: Product & Sizing Specs --}}
                            <div class="modal-spec-col">
                                @php
                                    $modalImages = $activeProduct->all_image_urls;
                                @endphp
                                <div class="modal-gallery-container" x-data="{
                                    images: {{ json_encode($modalImages) }},
                                    activeIdx: 0,
                                    next() { this.activeIdx = (this.activeIdx + 1) % this.images.length },
                                    prev() { this.activeIdx = (this.activeIdx - 1 + this.images.length) % this.images.length },
                                    select(idx) { this.activeIdx = idx }
                                }">
                                    <div class="modal-preview-img">
                                        <template x-for="(img, idx) in images" :key="'modal-img-'+idx">
                                            <img x-show="activeIdx === idx"
                                                 :src="img"
                                                 alt="{{ $activeProduct->name }}"
                                                 loading="lazy">
                                        </template>
                                        <span class="preview-badge">Gold Covering Plated</span>

                                        @if(count($modalImages) > 1)
                                            <button type="button" @click.stop="prev()" class="modal-img-arrow modal-img-arrow-left" aria-label="Previous">
                                                <i class="bi bi-chevron-left"></i>
                                            </button>
                                            <button type="button" @click.stop="next()" class="modal-img-arrow modal-img-arrow-right" aria-label="Next">
                                                <i class="bi bi-chevron-right"></i>
                                            </button>
                                            <div class="modal-img-counter">
                                                <i class="bi bi-camera"></i> <span x-text="(activeIdx + 1) + ' / ' + images.length"></span>
                                            </div>
                                        @endif
                                    </div>

                                    @if(count($modalImages) > 1)
                                        <div class="modal-thumbs-options">
                                            <span class="modal-thumbs-title"><i class="bi bi-images"></i> Available Image Angles / Options:</span>
                                            <div class="modal-thumbs-row">
                                                <template x-for="(img, idx) in images" :key="'modal-thumb-'+idx">
                                                    <button type="button"
                                                            class="modal-thumb-item"
                                                            :class="{ 'active': activeIdx === idx }"
                                                            @click="select(idx)"
                                                            :title="'View image ' + (idx + 1)">
                                                        <img :src="img" :alt="'Angle ' + (idx + 1)">
                                                        <span class="modal-thumb-num" x-text="idx + 1"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Size / Length Selection --}}
                                <div class="spec-section">
                                    <label class="spec-label">1. Choose Size or Length:</label>
                                    <div class="modal-pown-chips">
                                        @foreach($activeProduct->pown_options ?? [20, 22, 24, 26, 28] as $opt)
                                            @php
                                                $chipText = $activeProduct->category === 'kodi' ? ($opt . '" Length') : ($activeProduct->category === 'bangle' ? 'Size ' . $opt : ($activeProduct->category === 'ring' ? 'Size ' . $opt : $opt . '" Set'));
                                            @endphp
                                            <button type="button" wire:click="selectSizeOption('{{ $chipText }}')" class="modal-pown-btn {{ str_contains($inquirySize, (string)$opt) ? 'active' : '' }}">
                                                {{ $chipText }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="custom-pown-input-wrap mt-2">
                                        <span class="custom-pown-text">Or custom specification:</span>
                                        <input type="text" wire:model.live="inquirySize" placeholder="e.g. 25 inches, Size 2.5" class="form-input custom-pown-input-wide">
                                    </div>
                                </div>

                                {{-- Plating Selection --}}
                                <div class="spec-section mt-3">
                                    <label class="spec-label">2. Choose Plating Quality:</label>
                                    <div class="d-flex flex-column gap-2">
                                        <label class="plating-radio-pill {{ $inquiryPlating === 'standard' ? 'active' : '' }}">
                                            <input type="radio" wire:model.live="inquiryPlating" value="standard">
                                            <span>Standard Micro Gold Covering</span>
                                        </label>
                                        <label class="plating-radio-pill {{ $inquiryPlating === 'double_coat' ? 'active' : '' }}">
                                            <input type="radio" wire:model.live="inquiryPlating" value="double_coat">
                                            <span>Double-Coat (1-Year Color Guarantee)</span>
                                        </label>
                                        <label class="plating-radio-pill {{ $inquiryPlating === 'royal_finish' ? 'active' : '' }}">
                                            <input type="radio" wire:model.live="inquiryPlating" value="royal_finish">
                                            <span>Royal High-Luster Antique Plating</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="spec-price-summary">
                                    <div class="spec-price-label">Estimated Piece Price:</div>
                                    <div class="spec-price-val">LKR {{ number_format($this->calculateEstimatedPrice(), 0) }}</div>
                                    <div class="spec-price-sub">Fixed piece price • No gold gram rate</div>
                                </div>
                            </div>

                            {{-- Right Column: Customer Details Form --}}
                            <div class="modal-inputs-col">
                                <form wire:submit.prevent="submitInquiry">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="inquiryCustomerName" placeholder="e.g. Sivanathan Ravindran" class="form-input @error('inquiryCustomerName') is-invalid @enderror">
                                        @error('inquiryCustomerName') <span class="error-msg">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label">Phone Number (Sri Lanka) <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="inquiryCustomerPhone" placeholder="0771234567 or +94771234567" class="form-input @error('inquiryCustomerPhone') is-invalid @enderror">
                                        <span class="input-hint">You will receive an instant SMS confirmation.</span>
                                        @error('inquiryCustomerPhone') <span class="error-msg">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group mb-3 position-relative">
                                        <label class="form-label">City / Delivery Location <span class="text-danger">*</span></label>
                                        <input type="text" wire:model.live.debounce.150ms="inquiryCity" placeholder="Type city (e.g. Jaffna, Nallur, Colombo)..." class="form-input @error('inquiryCity') is-invalid @enderror" autocomplete="off">
                                        @error('inquiryCity') <span class="error-msg">{{ $message }}</span> @enderror

                                        @if($showCitySuggestions)
                                            <div class="city-suggestions-dropdown">
                                                @foreach($citySuggestions as $suggestion)
                                                    <div class="city-suggestion-item" wire:click="selectCity('{{ $suggestion }}')">
                                                        <i class="bi bi-geo-alt-fill text-gold"></i> {{ $suggestion }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label">Reference Design / Custom Photo (Optional)</label>
                                        <div class="custom-file-upload-wrap">
                                            <input type="file" wire:model="reference_image" accept="image/*" class="form-input custom-file-input">
                                            <span class="input-hint">Attach a photo or sketch of your desired jewelry design (Max 10MB).</span>
                                        </div>

                                        <div wire:loading wire:target="reference_image" class="text-gold small mt-1">
                                            <i class="bi bi-hourglass-split"></i> Uploading reference photo...
                                        </div>

                                        @if($reference_image)
                                            @php
                                                try {
                                                    $refPreview = $reference_image->temporaryUrl();
                                                } catch (\Throwable $e) {
                                                    $refPreview = null;
                                                }
                                            @endphp
                                            @if($refPreview)
                                                <div class="mt-2 d-flex align-items-center gap-2">
                                                    <img src="{{ $refPreview }}" class="rounded border" style="width: 54px; height: 54px; object-fit: cover; border-color: #d4af37 !important;" alt="Reference Photo">
                                                    <span class="badge" style="background-color: rgba(212,175,55,0.2); color: #d4af37; border: 1px solid #d4af37;">Photo Attached</span>
                                                </div>
                                            @endif
                                        @endif
                                        @error('reference_image') <span class="error-msg text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="form-label">Special Crafting Notes / Requests</label>
                                        <textarea wire:model="inquiryNotes" rows="3" placeholder="e.g. Please include traditional S-hook clasp, extra safety loop, matching earrings, or custom lock preference..." class="form-textarea"></textarea>
                                    </div>

                                    <div class="modal-submit-row">
                                        <button type="submit" class="btn-modal-submit" wire:loading.attr="disabled" wire:target="submitInquiry,reference_image">
                                            <span wire:loading.remove wire:target="submitInquiry,reference_image"><i class="bi bi-send-check"></i> Submit Custom Covering Request</span>
                                            <span wire:loading wire:target="submitInquiry"><i class="bi bi-hourglass-split"></i> Submitting Request...</span>
                                            <span wire:loading wire:target="reference_image"><i class="bi bi-cloud-arrow-up"></i> Uploading Image...</span>
                                        </button>
                                        <button type="button" wire:click="closeInquiryModal" class="btn-modal-cancel">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ===== INLINE SCOPED STYLES ===== --}}
    <style>
        .custom-products-page {
            background-color: #0d0c0b;
            color: #f5f0eb;
            min-height: 100vh;
        }

        .gold-gradient-text {
            background: linear-gradient(135deg, #fce09b 0%, #d4af37 50%, #aa841f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .text-gold {
            color: #d4af37 !important;
        }

        /* ─── Hero ────────────────────────────────────────── */
        .custom-hero {
            position: relative;
            background: radial-gradient(circle at 50% 20%, #2a221a 0%, #14110f 70%, #0d0c0b 100%);
            padding: 4.5rem 1rem 3.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            overflow: hidden;
        }

        .custom-hero-overlay {
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23d4af37' fill-opacity='0.03' fill-rule='evenodd'%3E%3Cpath d='M30 30L15 0H0v15l30 30 30-30V0H45L30 30z'/%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .custom-hero-content {
            position: relative;
            z-index: 2;
            max-width: 52rem;
            margin: 0 auto;
        }

        .custom-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(212, 175, 55, 0.12);
            border: 1px solid rgba(212, 175, 55, 0.35);
            color: #f7e2a9;
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
        }

        .custom-hero-title {
            font-size: 2.85rem;
            font-weight: 800;
            line-height: 1.15;
            color: #ffffff;
            margin-bottom: 1.25rem;
            font-family: inherit;
        }

        @media (max-width: 768px) {
            .custom-hero-title {
                font-size: 2rem;
            }
        }

        .custom-hero-subtitle {
            font-size: 1.05rem;
            line-height: 1.7;
            color: #d1c7bc;
            margin-bottom: 2rem;
        }

        .rate-indicator-bar {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            background: rgba(26, 22, 19, 0.9);
            border: 1px solid rgba(212, 175, 55, 0.4);
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .rate-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: pulseGreen 1.8s infinite;
        }

        @keyframes pulseGreen {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        .rate-label {
            font-size: 0.85rem;
            color: #a89c8f;
        }

        .rate-val {
            color: #fce09b;
            font-size: 0.95rem;
        }

        .rate-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
            color: #e5ded5;
            flex-wrap: wrap;
            justify-content: center;
        }

        .divider-dot {
            color: rgba(212, 175, 55, 0.5);
        }

        /* ─── Calculator Section ───────────────────────────── */
        .calc-section {
            padding: 2.5rem 1rem;
            position: relative;
            z-index: 5;
            margin-top: -1.5rem;
        }

        .calc-card {
            background: linear-gradient(145deg, #1f1a16 0%, #151210 100%);
            border: 1px solid rgba(212, 175, 55, 0.35);
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .calc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .calc-tag {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #d4af37;
            font-weight: 700;
        }

        .calc-title {
            font-size: 1.45rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0.25rem 0 0;
        }

        .calc-note {
            background: rgba(212, 175, 55, 0.1);
            color: #f7e2a9;
            border: 1px solid rgba(212, 175, 55, 0.25);
            padding: 0.4rem 0.85rem;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .calc-body {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 900px) {
            .calc-body {
                grid-template-columns: 1fr;
            }
        }

        .calc-label {
            font-size: 0.88rem;
            font-weight: 600;
            color: #e2d9cd;
            display: block;
            margin-bottom: 0.6rem;
        }

        .calc-cat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 600px) {
            .calc-cat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .calc-cat-btn {
            background: #25201b;
            border: 1px solid #3d352e;
            color: #cfc4b6;
            padding: 0.65rem 0.5rem;
            border-radius: 8px;
            font-size: 0.84rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .calc-cat-btn:hover {
            border-color: #d4af37;
            color: #fce09b;
        }

        .calc-cat-btn.active {
            background: linear-gradient(135deg, #d4af37 0%, #9a761c 100%);
            border-color: #fce09b;
            color: #110e0c;
            font-weight: 700;
        }

        .calc-weight-badge {
            font-size: 0.85rem;
            color: #fce09b;
            background: #2b241d;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        .calc-pown-presets {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .pown-preset-btn {
            background: #25201b;
            border: 1px solid #3d352e;
            color: #a89c8f;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.78rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .pown-preset-btn:hover {
            color: #ffffff;
            border-color: #d4af37;
        }

        .pown-preset-btn.active {
            background: #d4af37;
            color: #0d0c0b;
            font-weight: 700;
            border-color: #fce09b;
        }

        .plating-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.6rem;
        }

        .plating-option-btn {
            background: #201a15;
            border: 1px solid #382e25;
            padding: 0.65rem 0.75rem;
            border-radius: 8px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .plating-option-btn:hover {
            border-color: #d4af37;
        }

        .plating-option-btn.active {
            border-color: #d4af37;
            background: rgba(212, 175, 55, 0.12);
        }

        .plating-btn-title {
            font-size: 0.84rem;
            font-weight: 700;
            color: #fce09b;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .badge-rec {
            font-size: 0.65rem;
            background: #22c55e;
            color: #0d0c0b;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .plating-btn-sub {
            font-size: 0.74rem;
            color: #9c9083;
            margin-top: 0.2rem;
        }

        .calc-result-box {
            background: #181412;
            border: 1px solid rgba(212, 175, 55, 0.35);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .result-header {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #a89c8f;
            margin-bottom: 0.4rem;
        }

        .result-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fce09b;
            line-height: 1.2;
            margin-bottom: 1.25rem;
        }

        .result-details {
            border-top: 1px solid #2b2520;
            padding-top: 0.85rem;
            margin-bottom: 1.25rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.84rem;
            margin-bottom: 0.45rem;
            color: #b8aba0;
        }

        .detail-row strong {
            color: #ffffff;
        }

        .btn-calc-scroll {
            display: block;
            text-align: center;
            background: rgba(212, 175, 55, 0.15);
            border: 1px solid #d4af37;
            color: #fce09b;
            padding: 0.65rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-calc-scroll:hover {
            background: #d4af37;
            color: #0d0c0b;
        }

        /* ─── Catalog & Toolbars ──────────────────────────── */
        .catalog-section {
            padding: 2.5rem 1rem 4rem;
        }

        .catalog-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1.25rem;
        }

        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .cat-tab {
            background: #1f1a16;
            border: 1px solid #3a322a;
            color: #c4b8aa;
            padding: 0.55rem 1.1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .cat-tab:hover {
            border-color: #d4af37;
            color: #ffffff;
        }

        .cat-tab.active {
            background: linear-gradient(135deg, #d4af37 0%, #aa841f 100%);
            border-color: #fce09b;
            color: #0d0c0b;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .toolbar-controls {
            display: flex;
            gap: 0.85rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .control-select-wrap {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .control-label {
            font-size: 0.82rem;
            color: #9c9083;
        }

        .filter-select {
            background: #1f1a16;
            border: 1px solid #3a322a;
            color: #fce09b;
            padding: 0.5rem 0.85rem;
            border-radius: 8px;
            font-size: 0.85rem;
            outline: none;
        }

        .filter-select:focus {
            border-color: #d4af37;
        }

        .search-wrap {
            position: relative;
            min-width: 260px;
        }

        .search-icon {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #8a7e72;
            font-size: 0.85rem;
        }

        .filter-search-input {
            width: 100%;
            background: #1f1a16;
            border: 1px solid #3a322a;
            color: #ffffff;
            padding: 0.5rem 0.85rem 0.5rem 2.2rem;
            border-radius: 8px;
            font-size: 0.85rem;
            outline: none;
        }

        .filter-search-input:focus {
            border-color: #d4af37;
        }

        /* ─── Product Card Grid ───────────────────────────── */
        .custom-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 1.75rem;
        }

        .custom-card {
            background: #191512;
            border: 1px solid #302821;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .custom-card:hover {
            transform: translateY(-4px);
            border-color: rgba(212, 175, 55, 0.6);
            box-shadow: 0 16px 35px rgba(0, 0, 0, 0.5);
        }

        .card-img-container {
            position: relative;
            height: 220px;
            background: #110f0d;
            overflow: hidden;
        }

        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .custom-card:hover .card-img {
            transform: scale(1.05);
        }

        .card-purity-badge {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            background: rgba(18, 14, 12, 0.88);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(212, 175, 55, 0.5);
            color: #fce09b;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .card-category-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: rgba(212, 175, 55, 0.9);
            color: #110e0c;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .card-nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            background: rgba(18, 14, 12, 0.78);
            border: 1px solid rgba(212, 175, 55, 0.55);
            color: #fce09b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.85rem;
            z-index: 5;
            opacity: 0.85;
            transition: all 0.2s ease;
            backdrop-filter: blur(4px);
        }

        .card-nav-arrow:hover {
            background: #d4af37;
            color: #120e0c;
            border-color: #d4af37;
            transform: translateY(-50%) scale(1.1);
        }

        .card-nav-arrow-left {
            left: 0.6rem;
        }

        .card-nav-arrow-right {
            right: 0.6rem;
        }

        .card-nav-dots {
            position: absolute;
            bottom: 0.55rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 0.35rem;
            z-index: 4;
            background: rgba(18, 14, 12, 0.7);
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(212, 175, 55, 0.35);
        }

        .card-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.45);
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .card-dot.active {
            background: #d4af37;
            width: 14px;
            border-radius: 9999px;
        }

        .card-photo-count-pill {
            position: absolute;
            bottom: 0.55rem;
            right: 0.6rem;
            background: rgba(18, 14, 12, 0.82);
            border: 1px solid rgba(212, 175, 55, 0.45);
            color: #fce09b;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.15rem 0.45rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            z-index: 4;
            backdrop-filter: blur(4px);
        }

        .card-thumbs-strip {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.85rem;
            background: #171310;
            border-bottom: 1px solid rgba(212, 175, 55, 0.15);
            overflow-x: auto;
        }

        .card-thumbs-strip-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #d4af37;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .card-thumbs-list {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .card-thumb-btn {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            border: 1.5px solid rgba(212, 175, 55, 0.25);
            background: #110e0c;
            padding: 0;
            overflow: hidden;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .card-thumb-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .card-thumb-btn:hover {
            border-color: rgba(212, 175, 55, 0.75);
            transform: scale(1.08);
        }

        .card-thumb-btn.active {
            border-color: #d4af37;
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.6);
            transform: scale(1.08);
        }

        .card-body {
            padding: 1.35rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.6rem;
            gap: 0.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            line-height: 1.2;
        }

        .card-tamil-title {
            display: block;
            font-size: 0.82rem;
            color: #d4af37;
            font-weight: 600;
            margin-top: 0.15rem;
        }

        .card-pown-span {
            font-size: 0.78rem;
            font-weight: 700;
            color: #fce09b;
            background: #29221b;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            white-space: nowrap;
        }

        .card-desc {
            font-size: 0.84rem;
            line-height: 1.55;
            color: #aba094;
            margin-bottom: 1rem;
        }

        .pown-options-group {
            margin-bottom: 1rem;
        }

        .pown-label {
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #8c8073;
            display: block;
            margin-bottom: 0.4rem;
        }

        .pown-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .pown-chip {
            background: #241d18;
            border: 1px solid #42382f;
            color: #ded4c8;
            font-size: 0.74rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .pown-chip:hover {
            border-color: #d4af37;
            color: #fce09b;
            background: #2f251e;
        }

        .pown-chip strong {
            color: #fce09b;
        }

        .card-price-block {
            background: #14110f;
            border: 1px solid rgba(212, 175, 55, 0.25);
            border-radius: 8px;
            padding: 0.85rem;
            margin-bottom: 1rem;
        }

        .price-range-label {
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9e9184;
            margin-bottom: 0.2rem;
        }

        .price-range-val {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fce09b;
            line-height: 1.2;
        }

        .price-per-pown {
            font-size: 0.76rem;
            color: #8c8073;
            margin-top: 0.25rem;
        }

        .card-spec-list {
            list-style: none;
            padding: 0;
            margin: 0 0 1.25rem;
            font-size: 0.78rem;
            color: #a89d91;
        }

        .card-spec-list li {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.25rem;
        }

        .card-spec-list li i {
            color: #d4af37;
            font-size: 0.82rem;
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: auto;
        }

        .btn-customize-inquire {
            flex-grow: 1;
            background: linear-gradient(135deg, #d4af37 0%, #a67c1c 100%);
            color: #110e0c;
            border: none;
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .btn-customize-inquire:hover {
            background: linear-gradient(135deg, #fce09b 0%, #d4af37 100%);
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
        }

        .btn-card-wa {
            background: #25d366;
            color: #ffffff;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            text-decoration: none;
            transition: background 0.2s ease;
            flex-shrink: 0;
        }

        .btn-card-wa:hover {
            background: #20ba5a;
            color: #ffffff;
        }

        /* ─── Heritage Section ────────────────────────────── */
        .heritage-section {
            background: #14110f;
            border-top: 1px solid rgba(212, 175, 55, 0.15);
            border-bottom: 1px solid rgba(212, 175, 55, 0.15);
            padding: 3.5rem 1rem;
        }

        .heritage-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 2rem;
        }

        .heritage-box {
            text-align: center;
            padding: 1rem;
        }

        .heritage-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(212, 175, 55, 0.12);
            border: 1px solid rgba(212, 175, 55, 0.35);
            color: #fce09b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin: 0 auto 1rem;
        }

        .heritage-box h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .heritage-box p {
            font-size: 0.85rem;
            line-height: 1.6;
            color: #9c9083;
            margin: 0;
        }

        /* ─── Modal Styles ────────────────────────────────── */
        .modal-backdrop-jg {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow-y: auto;
        }

        .modal-card-jg {
            background: #1c1814;
            border: 1px solid rgba(212, 175, 55, 0.4);
            border-radius: 18px;
            width: 100%;
            max-width: 820px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.8);
            overflow: hidden;
            animation: modalFadeIn 0.25s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header-jg {
            background: #25201b;
            border-bottom: 1px solid rgba(212, 175, 55, 0.25);
            padding: 1.25rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-eyebrow {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #d4af37;
            font-weight: 700;
            display: block;
        }

        .modal-title {
            font-size: 1.45rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
        }

        .modal-tamil {
            font-size: 0.85rem;
            color: #fce09b;
        }

        .modal-close-btn {
            background: none;
            border: none;
            color: #8c8073;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .modal-close-btn:hover {
            color: #ffffff;
        }

        .modal-body-jg {
            padding: 1.75rem;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-form-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 1.75rem;
        }

        @media (max-width: 768px) {
            .modal-form-grid {
                grid-template-columns: 1fr;
            }
        }

        .modal-gallery-container {
            margin-bottom: 1.25rem;
        }

        .modal-preview-img {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            height: 220px;
            background: #110e0c;
            border: 1px solid #3d342c;
        }

        .modal-preview-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .modal-img-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            background: rgba(18, 14, 12, 0.78);
            border: 1px solid rgba(212, 175, 55, 0.5);
            color: #fce09b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.95rem;
            z-index: 5;
            transition: all 0.2s ease;
            backdrop-filter: blur(4px);
        }

        .modal-img-arrow:hover {
            background: #d4af37;
            color: #120e0c;
            transform: translateY(-50%) scale(1.1);
        }

        .modal-img-arrow-left {
            left: 0.75rem;
        }

        .modal-img-arrow-right {
            right: 0.75rem;
        }

        .modal-img-counter {
            position: absolute;
            bottom: 0.6rem;
            right: 0.6rem;
            background: rgba(18, 14, 12, 0.85);
            border: 1px solid rgba(212, 175, 55, 0.5);
            color: #fce09b;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            z-index: 4;
            backdrop-filter: blur(4px);
        }

        .modal-thumbs-options {
            margin-top: 0.75rem;
            padding: 0.6rem 0.85rem;
            background: #191410;
            border: 1px solid rgba(212, 175, 55, 0.25);
            border-radius: 10px;
        }

        .modal-thumbs-title {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #d4af37;
            margin-bottom: 0.45rem;
        }

        .modal-thumbs-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .modal-thumb-item {
            position: relative;
            width: 52px;
            height: 52px;
            border-radius: 8px;
            border: 2px solid rgba(212, 175, 55, 0.3);
            background: #110e0c;
            padding: 0;
            overflow: hidden;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .modal-thumb-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .modal-thumb-item:hover {
            border-color: rgba(212, 175, 55, 0.8);
            transform: translateY(-2px);
        }

        .modal-thumb-item.active {
            border-color: #d4af37;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.7);
            transform: translateY(-2px);
        }

        .modal-thumb-num {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: rgba(18, 14, 12, 0.85);
            color: #fce09b;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0.05rem 0.25rem;
            border-radius: 4px;
            line-height: 1;
        }

        .preview-badge {
            position: absolute;
            bottom: 0.6rem;
            left: 0.6rem;
            background: rgba(17, 14, 12, 0.88);
            border: 1px solid #d4af37;
            color: #fce09b;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            z-index: 4;
        }

        .spec-label {
            font-size: 0.84rem;
            font-weight: 700;
            color: #e5ded5;
            display: block;
            margin-bottom: 0.5rem;
        }

        .modal-pown-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-bottom: 0.5rem;
        }

        .modal-pown-btn {
            background: #251f1a;
            border: 1px solid #42372d;
            color: #cfc4b6;
            font-size: 0.78rem;
            padding: 0.4rem 0.65rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .modal-pown-btn:hover {
            border-color: #d4af37;
            color: #ffffff;
        }

        .modal-pown-btn.active {
            background: #d4af37;
            border-color: #fce09b;
            color: #0d0c0b;
            font-weight: 700;
        }

        .custom-pown-input-wrap {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            font-size: 0.8rem;
            color: #9e9184;
        }

        .custom-pown-input-wide {
            width: 100%;
            padding: 0.45rem 0.65rem;
            font-size: 0.85rem;
        }

        .plating-radio-pill {
            background: #231c17;
            border: 1px solid #3d3228;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #d6ccbe;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .plating-radio-pill.active {
            border-color: #d4af37;
            background: rgba(212, 175, 55, 0.12);
            color: #fce09b;
            font-weight: 600;
        }

        .plating-radio-pill input {
            accent-color: #d4af37;
        }

        .spec-price-summary {
            background: #14110e;
            border: 1px solid rgba(212, 175, 55, 0.35);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1.25rem;
            text-align: center;
        }

        .spec-price-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9e9184;
            margin-bottom: 0.2rem;
        }

        .spec-price-val {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fce09b;
        }

        .spec-price-sub {
            font-size: 0.76rem;
            color: #8a7e72;
            margin-top: 0.2rem;
        }

        /* Form Inputs */
        .form-label {
            font-size: 0.84rem;
            font-weight: 600;
            color: #ded5c8;
            margin-bottom: 0.35rem;
            display: block;
        }

        .form-input, .form-textarea {
            width: 100%;
            background: #25201b;
            border: 1px solid #42382f;
            color: #ffffff;
            padding: 0.6rem 0.85rem;
            border-radius: 8px;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .form-input:focus, .form-textarea:focus {
            border-color: #d4af37;
        }

        .form-input.is-invalid {
            border-color: #ef4444;
        }

        .error-msg {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: block;
        }

        .input-hint {
            font-size: 0.75rem;
            color: #8c8073;
            margin-top: 0.25rem;
            display: block;
        }

        .city-suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #2b241d;
            border: 1px solid #d4af37;
            border-radius: 8px;
            max-height: 160px;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7);
        }

        .city-suggestion-item {
            padding: 0.5rem 0.85rem;
            font-size: 0.85rem;
            color: #e5ded5;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px solid #383027;
        }

        .city-suggestion-item:hover {
            background: #3d3328;
            color: #fce09b;
        }

        .modal-submit-row {
            display: flex;
            gap: 0.75rem;
        }

        .btn-modal-submit {
            flex-grow: 1;
            background: linear-gradient(135deg, #d4af37 0%, #aa841f 100%);
            color: #0d0c0b;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-modal-submit:hover:not(:disabled) {
            background: linear-gradient(135deg, #fce09b 0%, #d4af37 100%);
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
        }

        .btn-modal-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-modal-cancel {
            background: #2b241d;
            border: 1px solid #4a3e33;
            color: #cfc4b6;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .btn-modal-cancel:hover {
            background: #383027;
            color: #ffffff;
        }

        /* Submission Success */
        .submission-success {
            text-align: center;
            padding: 1.5rem 1rem;
        }

        .success-icon-wrap {
            font-size: 3.5rem;
            color: #22c55e;
            margin-bottom: 1rem;
        }

        .submission-success h3 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }

        .success-inquiry-num {
            font-size: 1rem;
            color: #d4af37;
            margin-bottom: 1.25rem;
        }

        .success-alert {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            padding: 0.85rem 1.25rem;
            border-radius: 10px;
            font-size: 0.88rem;
            max-width: 520px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-align: left;
        }

        .success-summary-box {
            background: #14110e;
            border: 1px solid #332a22;
            border-radius: 10px;
            padding: 1rem;
            max-width: 450px;
            margin: 0 auto 1.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.88rem;
            padding: 0.35rem 0;
            color: #a89d91;
            border-bottom: 1px solid #241d17;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-item strong {
            color: #ffffff;
        }

        .success-actions {
            display: flex;
            justify-content: center;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .btn-success-wa {
            background: #25d366;
            color: #ffffff;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.92rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }

        .btn-success-wa:hover {
            background: #20ba5a;
            color: #ffffff;
        }

        .btn-success-close {
            background: #2d2620;
            border: 1px solid #4a3e33;
            color: #ffffff;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.92rem;
            cursor: pointer;
        }

        .empty-state-card {
            text-align: center;
            padding: 4rem 1rem;
            background: #191512;
            border: 1px solid #332a22;
            border-radius: 16px;
        }

        .empty-icon {
            font-size: 3rem;
            color: #d4af37;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .btn-reset-filters {
            background: #d4af37;
            color: #0d0c0b;
            border: none;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 1rem;
        }
    </style>
</div>
