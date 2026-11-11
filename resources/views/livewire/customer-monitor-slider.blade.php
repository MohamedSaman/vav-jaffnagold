<div class="monitor-light-page" id="monitorPage" wire:ignore.self>
    <!-- Light Luxury Ambient Glows -->
    <div class="light-ambient-glow glow-gold-top"></div>
    <div class="light-ambient-glow glow-gold-bottom"></div>
    <div class="light-grid-pattern"></div>

    <!-- Header Navigation Bar (Light Luxury Theme, Ignored by Livewire polling to preserve state) -->
    <header class="monitor-navbar" wire:ignore>
        <div class="brand-section">
            <img src="{{ asset('images/logo.webp') }}" alt="Jaffna Gold" class="brand-logo" onerror="this.style.display='none'">
            <div class="brand-info">
                <h1 class="brand-title font-cinzel">JAFFNA GOLD</h1>
                <span class="brand-tagline font-cinzel">HANDCRAFTED 22K GOLD & STERLING SILVER JEWELRY</span>
            </div>
        </div>

        <div class="header-center-pill d-none d-lg-flex">
            <span class="live-dot"></span>
            <span class="pill-text font-cinzel">SHOWROOM EXCLUSIVE OFFERS</span>
        </div>

        <div class="status-section">
            <!-- Smart TV Fullscreen Button with rock-solid inline handler -->
            <button type="button" class="tv-fullscreen-btn" id="btnFullscreen" onclick="window.toggleFullScreenMode(event)" title="Toggle Fullscreen for Smart TV (F / F11)">
                <i class="bi bi-arrows-fullscreen me-2" id="fullscreenIcon"></i>
                <span class="d-none d-sm-inline font-cinzel" id="fullscreenText">SMART TV FULLSCREEN</span>
            </button>

            <!-- Digital Clock -->
            <div class="light-clock" id="showroomClock">
                <i class="bi bi-clock-fill text-gold me-2"></i>
                <span class="clock-time font-monospace" id="clockTime">--:--:--</span>
                <span class="clock-date d-none d-md-inline" id="clockDate">--</span>
            </div>

            <!-- Play/Pause Control -->
            <button type="button" class="ctrl-circle-btn" id="btnPlayPause" onclick="window.togglePlayPause(event)" title="Play / Pause Auto-Slide">
                <i class="bi bi-pause-fill" id="playPauseIcon"></i>
            </button>
        </div>
    </header>

    <!-- Main Carousel Stage: wire:ignore strictly ensures Livewire never alters Swiper DOM elements -->
    <main class="carousel-stage" wire:ignore>
        @if ($totalSlides > 0)
            <!-- Swiper Carousel Container (3D Carousel Perspective matching reference) -->
            <div class="swiper showroom-swiper">
                <div class="swiper-wrapper">
                    @foreach ($sliderProducts as $index => $item)
                        <div class="swiper-slide product-slide-card" wire:key="slider-item-{{ $item['slide_key'] ?? $item['id'] }}">
                            <div class="card-inner">
                                <!-- Top of Image: Prominent Glowing Discount Badge -->
                                @if ($item['discount_percent'] > 0)
                                    <div class="top-discount-badge">
                                        <span class="badge-fire">🔥</span>
                                        <span class="badge-percent">{{ rtrim(rtrim(number_format($item['discount_percent'], 1), '0'), '.') }}% OFF</span>
                                        <span class="badge-sub">SPECIAL OFFER</span>
                                    </div>
                                @else
                                    <div class="top-featured-badge font-cinzel">
                                        <i class="bi bi-gem me-1"></i> FEATURED PIECE
                                    </div>
                                @endif

                                <!-- Product Image Container (Pure white porcelain background for seamless centering) -->
                                <div class="image-box">
                                    <img src="{{ $item['image'] }}" 
                                         alt="{{ $item['name'] }}" 
                                         class="product-img" 
                                         loading="lazy"
                                         onerror="this.onerror=null; this.src='{{ asset('images/product.jpg') }}';">
                                </div>

                                <!-- Card Body Information -->
                                <div class="card-body-content">
                                    <!-- Category & Code Tags -->
                                    <div class="product-tags-row">
                                        <span class="category-pill font-cinzel">{{ $item['category'] }}</span>
                                        @if (!empty($item['code']))
                                            <span class="code-pill">{{ $item['code'] }}</span>
                                        @endif
                                    </div>

                                    <!-- Product Name -->
                                    <h3 class="product-name font-playfair" title="{{ $item['name'] }}">
                                        {{ $item['name'] }}
                                    </h3>

                                    <!-- Pricing Box (Light Luxury) -->
                                    <div class="pricing-card">
                                        @if ($item['discount_percent'] > 0 && $item['savings'] > 0)
                                            <div class="original-price-row">
                                                <span class="strikethrough-price">Rs. {{ number_format($item['original_price'], 2) }}</span>
                                                <span class="savings-tag">Save Rs. {{ number_format($item['savings'], 0) }}</span>
                                            </div>
                                            <div class="special-price-row">
                                                <span class="currency-label">Rs.</span>
                                                <span class="special-price-num font-playfair">{{ number_format($item['final_price'], 2) }}</span>
                                            </div>
                                        @elseif ($item['final_price'] > 0)
                                            <div class="special-price-row single-price">
                                                <span class="currency-label">Rs.</span>
                                                <span class="special-price-num font-playfair">{{ number_format($item['final_price'], 2) }}</span>
                                            </div>
                                        @else
                                            <div class="special-price-row single-price">
                                                <span class="special-price-num font-playfair fs-5 text-gold">Showroom Exclusive</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Card Footer: Luxury Showroom Trust Badges (Replaces QR code) -->
                                    <div class="card-footer-luxury">
                                        <div class="luxury-badge-item">
                                            <i class="bi bi-patch-check-fill badge-gold-icon"></i>
                                            <div class="badge-text-col">
                                                <span class="luxury-badge-title font-cinzel">Certified Purity</span>
                                                <span class="luxury-badge-sub">100% 22K Gold & 925 Silver</span>
                                            </div>
                                        </div>
                                        <div class="luxury-badge-divider"></div>
                                        <div class="luxury-badge-item">
                                            <i class="bi bi-gem badge-gold-icon"></i>
                                            <div class="badge-text-col">
                                                <span class="luxury-badge-title font-cinzel">Showroom Available</span>
                                                <span class="luxury-badge-sub">Authenticity Guaranteed</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Swiper Navigation Arrows -->
                <div class="swiper-button-prev custom-nav-btn">
                    <i class="bi bi-chevron-left"></i>
                </div>
                <div class="swiper-button-next custom-nav-btn">
                    <i class="bi bi-chevron-right"></i>
                </div>

                <!-- Swiper Pagination Dots -->
                <div class="swiper-pagination custom-pagination"></div>
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-showcase-state">
                <div class="empty-showcase-card">
                    <div class="empty-icon-ring">
                        <i class="bi bi-tv text-gold"></i>
                    </div>
                    <h2 class="empty-heading font-cinzel">Smart TV Showroom Display</h2>
                    <p class="empty-subheading">
                        No products are currently marked for this monitor slider.
                    </p>
                    <div class="instruction-box">
                        <h6 class="text-gold fw-bold mb-2"><i class="bi bi-check-circle-fill me-1"></i> How to showcase products:</h6>
                        <ul class="text-start mb-0 ps-3">
                            <li>Visit the <strong>Product Inventory</strong> in Admin.</li>
                            <li>Switch the <strong>Slider</strong> toggle <span class="badge bg-warning text-dark">On</span> for any product.</li>
                            <li>Enter a <strong>Discount %</strong> (e.g. 10%, 15%, 20%).</li>
                            <li>The product will appear in this Smart TV display automatically!</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </main>
    <!-- Smart TV Fullscreen Hint Banner (Bottom Right, cleanly positioned without ticker) -->
    <div class="tv-fullscreen-hint" id="tvHint" onclick="window.toggleFullScreenMode(event)">
        <i class="bi bi-tv text-gold fs-5"></i>
        <span><strong>Smart TV Mode:</strong> Click or press <strong>F11</strong> for Fullscreen</span>
    </div>

    <!-- Light Theme & Ultra-Responsive Smart TV / Multi-Device Styles -->
    <style>
        .monitor-light-page {
            position: relative;
            height: 100vh;
            height: 100dvh;
            max-height: 100vh;
            max-height: 100dvh;
            width: 100vw;
            width: 100dvw;
            background: radial-gradient(ellipse at 50% 20%, #ffffff 0%, #faf8f2 50%, #f0e9dc 100%);
            color: #1f2937;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            user-select: none;
            box-sizing: border-box;
        }

        .text-gold {
            color: #b8972e !important;
        }

        /* Ambient Lighting */
        .light-ambient-glow {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(120px);
            z-index: 0;
            opacity: 0.35;
        }
        .glow-gold-top {
            width: clamp(380px, 50vw, 750px);
            height: clamp(250px, 35vh, 500px);
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            background: radial-gradient(circle, #fde68a 0%, rgba(253, 230, 138, 0) 70%);
        }
        .glow-gold-bottom {
            width: clamp(300px, 40vw, 600px);
            height: clamp(200px, 28vh, 400px);
            bottom: -80px;
            right: 10%;
            background: radial-gradient(circle, #fed7aa 0%, rgba(254, 215, 170, 0) 70%);
        }

        .light-grid-pattern {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(184, 151, 46, 0.08) 1.5px, transparent 1.5px);
            background-size: 36px 36px;
            pointer-events: none;
            z-index: 0;
        }

        /* Top Navbar */
        .monitor-navbar {
            position: relative;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: clamp(8px, 1.3vh, 16px) clamp(12px, 2.5vw, 40px);
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1.5px solid rgba(184, 151, 46, 0.25);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            flex-shrink: 0;
            gap: 12px;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: clamp(10px, 1.2vw, 16px);
            min-width: 0;
        }
        .brand-logo {
            height: clamp(34px, 4.2vh, 48px);
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(184, 151, 46, 0.25));
            flex-shrink: 0;
        }
        .brand-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .brand-title {
            font-size: clamp(1.15rem, 1.5vw, 1.65rem);
            font-weight: 800;
            letter-spacing: 0.14em;
            margin: 0;
            line-height: 1.15;
            background: linear-gradient(135deg, #1e1b18 0%, #8e6a10 70%, #b8972e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap;
        }
        .brand-tagline {
            font-size: clamp(0.52rem, 0.62vw, 0.72rem);
            letter-spacing: 0.16em;
            color: #8e6a10;
            font-weight: 700;
            display: block;
            white-space: nowrap;
        }

        .header-center-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: clamp(5px, 0.7vh, 8px) clamp(12px, 1.4vw, 20px);
            border-radius: 50px;
            background: #fffdf5;
            border: 1.5px solid rgba(184, 151, 46, 0.35);
            box-shadow: 0 2px 10px rgba(184, 151, 46, 0.12);
            white-space: nowrap;
            flex-shrink: 0;
        }
        .live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #b8972e;
            box-shadow: 0 0 8px #b8972e;
            animation: pulseDot 2s infinite;
        }
        @keyframes pulseDot {
            0% { transform: scale(0.9); opacity: 0.7; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.7; }
        }
        .pill-text {
            font-size: clamp(0.68rem, 0.78vw, 0.85rem);
            letter-spacing: 0.12em;
            font-weight: 800;
            color: #8e6a10;
        }

        .status-section {
            display: flex;
            align-items: center;
            gap: clamp(6px, 1vw, 14px);
            flex-shrink: 0;
        }

        /* Smart TV Fullscreen Button */
        .tv-fullscreen-btn {
            background: linear-gradient(135deg, #b8972e 0%, #997514 100%);
            color: #ffffff;
            border: none;
            padding: clamp(6px, 0.9vh, 9px) clamp(10px, 1.2vw, 18px);
            border-radius: 12px;
            font-size: clamp(0.74rem, 0.82vw, 0.88rem);
            font-weight: 700;
            letter-spacing: 0.08em;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(184, 151, 46, 0.35);
            transition: all 0.25s ease;
            white-space: nowrap;
        }
        .tv-fullscreen-btn:hover {
            transform: scale(1.04);
            box-shadow: 0 6px 20px rgba(184, 151, 46, 0.5);
        }

        .light-clock {
            font-size: clamp(0.78rem, 0.95vw, 1.05rem);
            font-weight: 700;
            color: #18181b;
            background: #f4efe4;
            padding: clamp(5px, 0.8vh, 8px) clamp(8px, 1vw, 15px);
            border-radius: 12px;
            border: 1px solid rgba(184, 151, 46, 0.2);
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }
        .clock-date {
            font-size: clamp(0.68rem, 0.8vw, 0.85rem);
            color: #64748b;
            font-weight: 500;
            margin-left: 6px;
            padding-left: 6px;
            border-left: 1px solid rgba(0, 0, 0, 0.15);
        }

        .ctrl-circle-btn {
            background: #ffffff;
            border: 1.5px solid rgba(184, 151, 46, 0.3);
            color: #8e6a10;
            width: clamp(34px, 4vh, 42px);
            height: clamp(34px, 4vh, 42px);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(1rem, 1.15vw, 1.25rem);
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }
        .ctrl-circle-btn:hover {
            background: #b8972e;
            color: #ffffff;
            transform: scale(1.06);
        }

        /* Carousel Stage - Expands to use full vertical space now ticker is removed */
        .carousel-stage {
            position: relative;
            z-index: 10;
            flex: 1;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            overflow: hidden;
            width: 100%;
        }

        .showroom-swiper {
            width: 100%;
            height: 100%;
            padding-top: clamp(18px, 3vh, 38px);
            padding-bottom: clamp(36px, 5.5vh, 60px);
            overflow: visible !important;
            display: flex;
            align-items: center;
            box-sizing: border-box;
        }

        .swiper-wrapper {
            align-items: center;
        }

        /* Product Slide Card */
        .product-slide-card {
            width: clamp(360px, 28vw, 460px) !important;
            max-width: 86vw;
            border-radius: clamp(20px, 2vw, 28px);
            cursor: pointer;
            box-sizing: border-box;
            transition: opacity 0.35s ease, transform 0.35s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Active center slide */
        .swiper-slide-active .card-inner,
        .product-slide-card.is-center-active .card-inner {
            border: 2.5px solid #c59b27 !important;
            box-shadow: 0 22px 65px rgba(184, 151, 46, 0.3), 0 8px 25px rgba(0, 0, 0, 0.08) !important;
            background: #ffffff !important;
            transform: translateY(-5px);
        }

        .swiper-slide:not(.swiper-slide-active) {
            opacity: 0.72;
            transition: opacity 0.4s ease;
        }
        .swiper-slide:not(.swiper-slide-active):hover {
            opacity: 0.95;
        }

        .card-inner {
            position: relative;
            background: #ffffff;
            border: 1.5px solid rgba(184, 151, 46, 0.25);
            border-radius: clamp(20px, 2vw, 28px);
            padding: clamp(18px, 2.4vh, 26px) clamp(14px, 1.8vw, 24px) clamp(14px, 1.8vh, 20px);
            display: flex;
            flex-direction: column;
            gap: clamp(8px, 1.2vh, 14px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.06);
            transition: all 0.4s ease;
            box-sizing: border-box;
            width: 100%;
        }

        /* Top Discount Badge: Positioned cleanly on top without overlapping image */
        .top-discount-badge {
            position: absolute;
            top: clamp(-13px, -1.6vh, -15px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            color: #ffffff;
            padding: clamp(5px, 0.8vh, 7px) clamp(14px, 1.5vw, 22px);
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-weight: 800;
            box-shadow: 0 6px 20px rgba(225, 29, 72, 0.45);
            border: 2.5px solid #ffffff;
            white-space: nowrap;
            animation: pulseBadge 2.4s infinite;
        }
        @keyframes pulseBadge {
            0% { transform: translateX(-50%) scale(1); }
            50% { transform: translateX(-50%) scale(1.05); }
            100% { transform: translateX(-50%) scale(1); }
        }

        .badge-fire {
            font-size: clamp(0.85rem, 1vw, 1.1rem);
        }
        .badge-percent {
            font-size: clamp(0.95rem, 1.15vw, 1.22rem);
            letter-spacing: 0.04em;
            font-weight: 900;
        }
        .badge-sub {
            font-size: clamp(0.55rem, 0.65vw, 0.68rem);
            letter-spacing: 0.12em;
            background: rgba(0, 0, 0, 0.25);
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
        }

        .top-featured-badge {
            position: absolute;
            top: clamp(-13px, -1.6vh, -15px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            background: #fffbeb;
            color: #997514;
            border: 1.5px solid rgba(197, 155, 39, 0.5);
            padding: clamp(5px, 0.7vh, 7px) clamp(12px, 1.4vw, 20px);
            border-radius: 50px;
            font-size: clamp(0.68rem, 0.76vw, 0.82rem);
            font-weight: 800;
            letter-spacing: 0.1em;
            white-space: nowrap;
            box-shadow: 0 4px 14px rgba(184, 151, 46, 0.18);
        }

        /* Image Box: Pure White porcelain background, mathematically centered */
        .image-box {
            position: relative;
            width: 100%;
            height: clamp(180px, 26vh, 280px);
            border-radius: clamp(14px, 1.4vw, 20px);
            overflow: hidden;
            background: #ffffff;
            border: 1px solid rgba(184, 151, 46, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(10px, 1.5vh, 16px);
            box-sizing: border-box;
            box-shadow: inset 0 1px 6px rgba(0, 0, 0, 0.02);
            margin-top: 4px;
        }

        .product-img {
            max-width: 90%;
            max-height: 90%;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            margin: auto;
            filter: drop-shadow(0 6px 16px rgba(0, 0, 0, 0.1));
            transition: transform 0.5s ease;
        }
        .product-slide-card.is-center-active .product-img,
        .swiper-slide-active .product-img {
            transform: scale(1.05);
        }

        /* Card Content */
        .card-body-content {
            display: flex;
            flex-direction: column;
            gap: clamp(7px, 1.1vh, 12px);
        }

        .product-tags-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .category-pill {
            background: #fef9c3;
            border: 1px solid rgba(184, 151, 46, 0.4);
            color: #8e6a10;
            padding: clamp(3px, 0.5vh, 5px) clamp(10px, 1vw, 14px);
            border-radius: 50px;
            font-size: clamp(0.68rem, 0.74vw, 0.78rem);
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .code-pill {
            font-size: clamp(0.65rem, 0.72vw, 0.75rem);
            font-family: monospace;
            color: #64748b;
            background: #f1f5f9;
            padding: 2px 7px;
            border-radius: 6px;
            font-weight: 600;
            white-space: nowrap;
        }

        .product-name {
            font-size: clamp(1.2rem, 1.45vw, 1.55rem);
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }

        /* Pricing Card (Light Luxury Ivory) */
        .pricing-card {
            background: linear-gradient(135deg, #fffdfa 0%, #faf5eb 100%);
            border: 1.5px solid rgba(184, 151, 46, 0.3);
            border-radius: clamp(12px, 1.2vw, 16px);
            padding: clamp(8px, 1.2vh, 13px) clamp(12px, 1.4vw, 18px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .original-price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .strikethrough-price {
            font-size: clamp(0.88rem, 1vw, 1.05rem);
            font-weight: 600;
            color: #94a3b8;
            text-decoration: line-through;
            text-decoration-color: #ef4444;
            text-decoration-thickness: 2px;
        }
        .savings-tag {
            font-size: clamp(0.66rem, 0.72vw, 0.76rem);
            font-weight: 800;
            color: #854d0e;
            background: #fef08a;
            padding: 2px 8px;
            border-radius: 6px;
            border: 1px dashed rgba(184, 151, 46, 0.5);
            white-space: nowrap;
        }

        .special-price-row {
            display: flex;
            align-items: baseline;
            gap: 5px;
        }
        .currency-label {
            font-size: clamp(1rem, 1.2vw, 1.25rem);
            font-weight: 800;
            color: #997514;
        }
        .special-price-num {
            font-size: clamp(1.7rem, 2.15vw, 2.25rem);
            font-weight: 900;
            color: #854d0e;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        /* Card Footer: Luxury Showroom Trust Badges */
        .card-footer-luxury {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: clamp(6px, 1vw, 12px);
            padding: clamp(6px, 0.9vh, 10px) clamp(10px, 1.2vw, 14px);
            background: linear-gradient(135deg, #fffefc 0%, #fbf7ee 100%);
            border: 1px solid rgba(184, 151, 46, 0.22);
            border-radius: clamp(10px, 1vw, 14px);
            box-shadow: 0 2px 8px rgba(184, 151, 46, 0.05);
            margin-top: 2px;
        }
        .luxury-badge-item {
            display: flex;
            align-items: center;
            gap: clamp(6px, 0.8vw, 9px);
            flex: 1;
            min-width: 0;
        }
        .badge-gold-icon {
            font-size: clamp(1.15rem, 1.3vw, 1.35rem);
            color: #b8972e;
            filter: drop-shadow(0 2px 4px rgba(184, 151, 46, 0.25));
            flex-shrink: 0;
        }
        .badge-text-col {
            display: flex;
            flex-direction: column;
            line-height: 1.25;
            min-width: 0;
        }
        .luxury-badge-title {
            font-size: clamp(0.68rem, 0.74vw, 0.78rem);
            font-weight: 800;
            color: #1e293b;
            letter-spacing: 0.04em;
            background: transparent !important;
            padding: 0 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .luxury-badge-sub {
            font-size: clamp(0.56rem, 0.63vw, 0.66rem);
            color: #64748b !important;
            font-weight: 600;
            background: transparent !important;
            padding: 0 !important;
            border-radius: 0 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .luxury-badge-divider {
            width: 1px;
            height: clamp(20px, 2.5vh, 26px);
            background: rgba(184, 151, 46, 0.22);
            flex-shrink: 0;
        }

        /* Swiper Navigation Buttons */
        .custom-nav-btn {
            width: clamp(44px, 4vw, 56px);
            height: clamp(44px, 4vw, 56px);
            border-radius: 50%;
            background: #ffffff;
            border: 1.5px solid rgba(184, 151, 46, 0.35);
            color: #8e6a10;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            top: 50%;
            transform: translateY(-50%);
            margin-top: 0;
            z-index: 25;
        }
        .custom-nav-btn:after {
            display: none;
        }
        .custom-nav-btn i {
            font-size: clamp(1.25rem, 1.4vw, 1.6rem);
        }
        .custom-nav-btn:hover {
            background: #b8972e;
            color: #ffffff;
            transform: translateY(-50%) scale(1.1);
        }
        .swiper-button-prev {
            left: clamp(12px, 2vw, 28px);
        }
        .swiper-button-next {
            right: clamp(12px, 2vw, 28px);
        }

        /* Pagination Dots - Positioned cleanly at the bottom without ticker collision */
        .custom-pagination {
            bottom: clamp(12px, 2vh, 22px) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(6px, 0.8vw, 10px);
            z-index: 20;
            pointer-events: auto;
        }
        .swiper-pagination-bullet {
            width: clamp(8px, 0.9vw, 12px);
            height: clamp(8px, 0.9vw, 12px);
            background: #cbd5e1;
            border-radius: 50%;
            opacity: 1;
            transition: all 0.35s ease;
        }
        .swiper-pagination-bullet-active {
            width: clamp(26px, 2.5vw, 38px);
            border-radius: 12px;
            background: #b8972e;
            box-shadow: 0 2px 10px rgba(184, 151, 46, 0.5);
        }

        /* Smart TV Fullscreen Hint Banner */
        .tv-fullscreen-hint {
            position: fixed;
            bottom: clamp(16px, 2.5vh, 26px);
            right: clamp(16px, 2.5vw, 28px);
            z-index: 100;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #1e1b18;
            border: 1.5px solid #b8972e;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 25px rgba(184, 151, 46, 0.25);
            cursor: pointer;
            transition: all 0.3s ease;
            animation: hintFadeOut 12s forwards;
        }
        .tv-fullscreen-hint:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(184, 151, 46, 0.4);
        }
        @keyframes hintFadeOut {
            0% { opacity: 0; transform: translateY(15px); }
            10% { opacity: 1; transform: translateY(0); }
            80% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; pointer-events: none; }
        }

        /* Empty State */
        .empty-showcase-state {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 55vh;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        .empty-showcase-card {
            background: #ffffff;
            border: 1.5px solid rgba(184, 151, 46, 0.3);
            border-radius: 28px;
            padding: clamp(24px, 4vw, 45px) clamp(18px, 3.5vw, 36px);
            max-width: min(580px, 92vw);
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
        }
        .empty-icon-ring {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            margin-bottom: 16px;
        }
        .empty-heading {
            font-size: clamp(1.4rem, 2vw, 1.85rem);
            font-weight: 800;
            color: #8e6a10;
            margin-bottom: 12px;
        }
        .empty-subheading {
            font-size: clamp(0.85rem, 1vw, 1rem);
            color: #64748b;
            margin-bottom: 24px;
        }
        .instruction-box {
            background: #fdfbf7;
            border: 1px solid rgba(184, 151, 46, 0.25);
            border-radius: 16px;
            padding: clamp(12px, 2vw, 18px) clamp(14px, 2.2vw, 22px);
        }
        .instruction-box ul li {
            margin-bottom: 6px;
            color: #334155;
            font-size: clamp(0.78rem, 0.85vw, 0.88rem);
        }

        /* ========================================================
           SCREEN RESPONSIVENESS (BIG SCREEN TV, 4K, LAPTOP, TABLET, MOBILE)
           ======================================================== */

        /* Ultra-Large Displays & 4K Smart TVs (>= 1921px width) */
        @media (min-width: 1921px) {
            .monitor-navbar {
                padding: 18px 48px;
            }
            .brand-logo {
                height: 56px;
            }
            .brand-title {
                font-size: 2.1rem;
            }
            .brand-tagline {
                font-size: 0.85rem;
            }
            .product-slide-card {
                width: clamp(520px, 25vw, 680px) !important;
            }
            .image-box {
                height: clamp(320px, 30vh, 440px);
            }
            .product-name {
                font-size: 2.1rem;
            }
            .special-price-num {
                font-size: 3.1rem;
            }
            .currency-label {
                font-size: 1.6rem;
            }
            .strikethrough-price {
                font-size: 1.35rem;
            }
            .savings-tag {
                font-size: 0.95rem;
                padding: 4px 12px;
            }
            .category-pill {
                font-size: 0.95rem;
                padding: 6px 18px;
            }
            .code-pill {
                font-size: 0.9rem;
                padding: 4px 10px;
            }
            .top-discount-badge {
                top: -18px;
                padding: 9px 26px;
            }
            .badge-percent {
                font-size: 1.45rem;
            }
            .badge-sub {
                font-size: 0.82rem;
            }
            .luxury-badge-title {
                font-size: 0.95rem;
            }
            .luxury-badge-sub {
                font-size: 0.82rem;
            }
            .custom-nav-btn {
                width: 68px;
                height: 68px;
            }
            .custom-nav-btn i {
                font-size: 2rem;
            }
        }

        /* Tablets & Smaller Desktops (769px to 1024px) */
        @media (max-width: 1024px) and (min-width: 769px) {
            .monitor-navbar {
                padding: 10px 22px;
            }
            .product-slide-card {
                width: clamp(340px, 46vw, 410px) !important;
            }
            .image-box {
                height: clamp(200px, 26vh, 250px);
            }
        }

        /* Tablets in Portrait (577px to 768px) */
        @media (max-width: 768px) {
            .monitor-navbar {
                padding: 8px 16px;
            }
            .brand-logo {
                height: 38px;
            }
            .brand-title {
                font-size: 1.25rem;
            }
            .brand-tagline {
                font-size: 0.56rem;
            }
            .product-slide-card {
                width: clamp(310px, 65vw, 360px) !important;
            }
            .image-box {
                height: clamp(180px, 24vh, 230px);
            }
            .custom-nav-btn {
                width: 42px;
                height: 42px;
            }
            .custom-nav-btn i {
                font-size: 1.25rem;
            }
            .tv-fullscreen-hint {
                display: none;
            }
        }

        /* Mobile Screens (<= 576px) */
        @media (max-width: 576px) {
            .monitor-navbar {
                padding: 8px 12px;
                gap: 8px;
            }
            .brand-section {
                gap: 8px;
            }
            .brand-logo {
                height: 32px;
            }
            .brand-title {
                font-size: 1.05rem;
                letter-spacing: 0.1em;
            }
            .brand-tagline {
                font-size: 0.48rem;
                letter-spacing: 0.1em;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .tv-fullscreen-btn {
                padding: 6px 10px;
                font-size: 0.78rem;
                border-radius: 9px;
            }
            .tv-fullscreen-btn i {
                margin-right: 0 !important;
            }
            .light-clock {
                font-size: 0.75rem;
                padding: 5px 8px;
                border-radius: 9px;
            }
            .ctrl-circle-btn {
                width: 32px;
                height: 32px;
                font-size: 0.95rem;
                border-radius: 9px;
            }
            .product-slide-card {
                width: clamp(270px, 86vw, 330px) !important;
            }
            .card-inner {
                padding: 16px 14px 12px;
                gap: 8px;
                border-radius: 20px;
            }
            .image-box {
                height: clamp(145px, 22vh, 190px);
                padding: 10px;
                border-radius: 14px;
            }
            .top-discount-badge {
                top: -11px;
                padding: 4px 12px;
                gap: 5px;
            }
            .badge-percent {
                font-size: 0.92rem;
            }
            .badge-sub {
                font-size: 0.55rem;
                padding: 1px 5px;
            }
            .category-pill {
                font-size: 0.65rem;
                padding: 2px 8px;
            }
            .code-pill {
                font-size: 0.62rem;
                padding: 1px 5px;
            }
            .product-name {
                font-size: 1.15rem;
            }
            .pricing-card {
                padding: 7px 12px;
                border-radius: 12px;
            }
            .strikethrough-price {
                font-size: 0.85rem;
            }
            .savings-tag {
                font-size: 0.64rem;
                padding: 1px 6px;
            }
            .currency-label {
                font-size: 0.95rem;
            }
            .special-price-num {
                font-size: 1.55rem;
            }
            .card-footer-luxury {
                padding: 6px 10px;
                gap: 6px;
                border-radius: 10px;
            }
            .badge-gold-icon {
                font-size: 1.05rem;
            }
            .luxury-badge-title {
                font-size: 0.64rem;
            }
            .luxury-badge-sub {
                font-size: 0.54rem;
            }
            .custom-nav-btn {
                display: none;
            }
            .custom-pagination {
                bottom: 10px !important;
            }
            .swiper-pagination-bullet {
                width: 8px;
                height: 8px;
            }
            .swiper-pagination-bullet-active {
                width: 22px;
            }
        }

        /* Narrow Phones (<= 380px) */
        @media (max-width: 380px) {
            .brand-tagline {
                display: none;
            }
            .product-slide-card {
                width: 88vw !important;
            }
            .card-inner {
                padding: 14px 10px 10px;
            }
        }

        /* Height-Constrained Screens (Laptops like 1366x768, 1280x800, or browser window with toolbars) */
        @media (max-height: 800px) {
            .monitor-navbar {
                padding: 7px 20px;
            }
            .showroom-swiper {
                padding-top: 14px;
                padding-bottom: 36px;
            }
            .image-box {
                height: clamp(150px, 24vh, 220px);
                margin-top: 2px;
            }
            .card-inner {
                padding: 14px 16px 12px;
                gap: 8px;
            }
            .card-body-content {
                gap: 6px;
            }
            .pricing-card {
                padding: 6px 12px;
            }
            .card-footer-luxury {
                padding: 5px 10px;
            }
            .custom-pagination {
                bottom: 8px !important;
            }
        }

        /* Extra Short Screens (Mobile in Landscape, <= 540px height) */
        @media (max-height: 540px) {
            .monitor-navbar {
                padding: 5px 14px;
            }
            .brand-logo {
                height: 26px;
            }
            .brand-title {
                font-size: 0.95rem;
            }
            .brand-tagline {
                display: none;
            }
            .header-center-pill {
                display: none !important;
            }
            .showroom-swiper {
                padding-top: 10px;
                padding-bottom: 26px;
            }
            .product-slide-card {
                width: 280px !important;
            }
            .image-box {
                height: 115px;
                padding: 6px;
            }
            .card-inner {
                padding: 10px 12px 8px;
                gap: 5px;
            }
            .product-name {
                font-size: 1rem;
            }
            .special-price-num {
                font-size: 1.35rem;
            }
            .card-footer-luxury {
                display: none;
            }
            .custom-pagination {
                bottom: 4px !important;
            }
        }
    </style>

    <!-- Scripts -->
    <script>
        (function() {
            // Silence Swiper loop warnings from browser console
            const _origWarn = console.warn;
            console.warn = function(...args) {
                if (args[0] && typeof args[0] === 'string' && args[0].indexOf('Swiper Loop Warning') !== -1) {
                    return;
                }
                _origWarn.apply(console, args);
            };

            // Live Clock
            function initClock() {
                const now = new Date();
                const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                const dateStr = now.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                
                const timeEl = document.getElementById('clockTime');
                const dateEl = document.getElementById('clockDate');
                if (timeEl) timeEl.textContent = timeStr;
                if (dateEl) dateEl.textContent = '• ' + dateStr;
            }
            setInterval(initClock, 1000);
            initClock();

            // Swiper Instance (Infinite Perspective 3D Carousel matching reference)
            let swiperInstance = null;
            let isAutoplayRunning = true;
            let lastActiveIndex = -1;
            let stuckCycles = 0;

            function initSwiper() {
                if (typeof Swiper === 'undefined') return;

                const swiperEl = document.querySelector('.showroom-swiper');
                if (!swiperEl) return;

                if (swiperInstance) {
                    try {
                        swiperInstance.destroy(true, true);
                    } catch(e) {}
                    swiperInstance = null;
                }

                swiperInstance = new Swiper('.showroom-swiper', {
                    effect: 'coverflow',
                    grabCursor: true,
                    centeredSlides: true,
                    slidesPerView: 'auto',
                    spaceBetween: 16,
                    breakpoints: {
                        576: {
                            spaceBetween: 24,
                        },
                        768: {
                            spaceBetween: 32,
                        },
                        1200: {
                            spaceBetween: 44,
                        },
                        1920: {
                            spaceBetween: 64,
                        },
                    },
                    loop: {{ $totalSlides > 2 ? 'true' : 'false' }},
                    loopAdditionalSlides: 4,
                    loopPreventsSliding: false,
                    speed: 650,
                    coverflowEffect: {
                        rotate: 0,
                        stretch: 0,
                        depth: 70,
                        modifier: 1,
                        scale: 0.85,
                        slideShadows: false,
                    },
                    autoplay: {
                        enabled: true,
                        delay: 4500,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: false,
                        waitForTransition: false,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    keyboard: {
                        enabled: true,
                    },
                    slideToClickedSlide: true,
                });

                window.showroomSwiper = swiperInstance;

                // Explicitly kick off autoplay immediately if user has not paused
                if (isAutoplayRunning && swiperInstance && swiperInstance.autoplay) {
                    try {
                        swiperInstance.autoplay.start();
                    } catch(e) {}
                }
            }

            // Play / Pause Toggle
            window.togglePlayPause = function(e) {
                if (e && e.stopPropagation) e.stopPropagation();
                if (!swiperInstance) return;

                const playIcon = document.getElementById('playPauseIcon');
                if (isAutoplayRunning) {
                    swiperInstance.autoplay.stop();
                    isAutoplayRunning = false;
                    if (playIcon) playIcon.className = 'bi bi-play-fill';
                } else {
                    swiperInstance.autoplay.start();
                    isAutoplayRunning = true;
                    if (playIcon) playIcon.className = 'bi bi-pause-fill';
                }
            };

            // Bulletproof Fullscreen Toggle (Cross-browser, Smart TVs, webOS, Tizen, Android TV, Mac, Windows)
            window.toggleFullScreenMode = function(e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                const doc = window.document;
                const docEl = doc.documentElement;

                const isFullscreen = !!(doc.fullscreenElement || doc.webkitFullscreenElement || doc.mozFullScreenElement || doc.msFullscreenElement);

                if (!isFullscreen) {
                    const enter = docEl.requestFullscreen || 
                                  docEl.webkitRequestFullscreen || 
                                  docEl.webkitRequestFullScreen || 
                                  docEl.mozRequestFullScreen || 
                                  docEl.msRequestFullscreen;

                    if (enter) {
                        try {
                            const res = enter.call(docEl);
                            if (res && res.catch) {
                                res.catch(err => console.warn('Fullscreen request:', err));
                            }
                        } catch (err) {
                            console.warn('Fullscreen invocation error:', err);
                        }
                    }
                } else {
                    const exit = doc.exitFullscreen || 
                                 doc.webkitExitFullscreen || 
                                 doc.webkitCancelFullScreen || 
                                 doc.mozCancelFullScreen || 
                                 doc.msExitFullscreen;

                    if (exit) {
                        try {
                            const res = exit.call(doc);
                            if (res && res.catch) {
                                res.catch(err => console.warn('Exit fullscreen:', err));
                            }
                        } catch (err) {
                            console.warn('Exit fullscreen error:', err);
                        }
                    }
                }
            };

            // Fullscreen UI State Sync
            function syncFullscreenState() {
                const isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
                const fsIcon = document.getElementById('fullscreenIcon');
                const fsText = document.getElementById('fullscreenText');
                const hint = document.getElementById('tvHint');

                if (isFullscreen) {
                    if (fsIcon) fsIcon.className = 'bi bi-fullscreen-exit me-2';
                    if (fsText) fsText.textContent = 'EXIT FULLSCREEN';
                    if (hint) hint.style.display = 'none';
                } else {
                    if (fsIcon) fsIcon.className = 'bi bi-arrows-fullscreen me-2';
                    if (fsText) fsText.textContent = 'SMART TV FULLSCREEN';
                }
            }

            ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange'].forEach(evt => {
                document.addEventListener(evt, syncFullscreenState);
            });

            // Double Click background to toggle fullscreen (Great for TV air remotes & touch displays)
            document.addEventListener('dblclick', function(e) {
                if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.card-inner')) return;
                window.toggleFullScreenMode(e);
            });

            // TV Remote & Keyboard Shortcuts
            window.addEventListener('keydown', function(e) {
                if (e.key === 'f' || e.key === 'F' || e.key === 'F11') {
                    e.preventDefault();
                    window.toggleFullScreenMode();
                } else if (e.key === 'ArrowRight' && swiperInstance) {
                    swiperInstance.slideNext();
                } else if (e.key === 'ArrowLeft' && swiperInstance) {
                    swiperInstance.slidePrev();
                } else if (e.key === ' ' || e.key === 'MediaPlayPause') {
                    e.preventDefault();
                    window.togglePlayPause();
                }
            });

            // Initialize
            function boot() {
                initSwiper();
                syncFullscreenState();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot);
            } else {
                setTimeout(boot, 60);
            }

            document.addEventListener('livewire:navigated', boot);

            // Rock-Solid Self-Healing Watchdog: Runs every 2 seconds
            // Guarantees auto-sliding never stops running, even if TV goes to sleep, tabs blur, or glitch occurs
            setInterval(function() {
                if (!isAutoplayRunning) return; // User deliberately paused

                if (!swiperInstance || swiperInstance.destroyed) {
                    console.warn('[Showroom Watchdog] Swiper instance uninitialized or destroyed. Reviving...');
                    initSwiper();
                    return;
                }

                // If autoplay is not running for any reason, start it immediately
                if (!swiperInstance.autoplay.running) {
                    try {
                        swiperInstance.autoplay.start();
                    } catch(e) {
                        initSwiper();
                    }
                }

                // If the slider has not moved for 16 seconds (8 cycles of 2s), nudge it forward
                if (swiperInstance.activeIndex === lastActiveIndex) {
                    stuckCycles++;
                    if (stuckCycles >= 8) {
                        stuckCycles = 0;
                        try {
                            swiperInstance.slideNext();
                        } catch(e) {
                            initSwiper();
                        }
                    }
                } else {
                    lastActiveIndex = swiperInstance.activeIndex;
                    stuckCycles = 0;
                }
            }, 2000);

            // Re-start autoplay when screen/tab regains focus or visibility
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden && swiperInstance && isAutoplayRunning) {
                    try { swiperInstance.autoplay.start(); } catch(e) {}
                }
            });
            window.addEventListener('focus', function() {
                if (swiperInstance && isAutoplayRunning) {
                    try { swiperInstance.autoplay.start(); } catch(e) {}
                }
            });

            // Live Catalog Auto-Sync: Polls lightweight, session-free endpoint every 30s
            // If admin updates products, discounts, or prices in back office, reload display smoothly
            let currentCatalogHash = '{{ $productsChecksum }}';
            async function checkCatalogUpdates() {
                try {
                    const res = await fetch('{{ route('customer.display-check') }}?_t=' + Date.now(), {
                        cache: 'no-store'
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data && data.hash && currentCatalogHash && data.hash !== currentCatalogHash) {
                        console.log('[Showroom Monitor] Catalog updated in Admin! Refreshing display...');
                        window.location.reload();
                    }
                } catch (e) {
                    // Ignore transient network hiccups
                }
            }
            setInterval(checkCatalogUpdates, 30000);

            // Smart TV Periodic Memory Hygiene: Reload every 6 hours to prevent browser memory leaks on TVs
            setTimeout(function() {
                window.location.reload();
            }, 6 * 60 * 60 * 1000);
        })();
    </script>
</div>
