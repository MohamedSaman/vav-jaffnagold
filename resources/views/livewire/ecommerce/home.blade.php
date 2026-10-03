<div>
    @php
        $heroImages = collect(glob(public_path('images/website images/hero/*.{jpg,jpeg,png,webp,avif,JPG,JPEG,PNG,WEBP,AVIF}'), GLOB_BRACE) ?: [])
            ->sort()
            ->values();
        $featureItems = [
            ['icon' => 'bi-gem', 'title' => 'Sterling Silver Purity', 'text' => 'Every piece crafted from 925 sterling silver, ethically sourced and hallmarked for authenticity.'],
            ['icon' => 'bi-hammer', 'title' => 'Handcrafted Excellence', 'text' => 'Each design is meticulously handcrafted by master artisans with generations of experience.'],
            ['icon' => 'bi-globe2', 'title' => 'Free Worldwide Shipping', 'text' => 'Complimentary express shipping on all orders, with secure packaging and full insurance.'],
            ['icon' => 'bi-arrow-return-left', 'title' => '30-Day Returns', 'text' => 'Love it or return it within 30 days for a full refund - no questions asked.'],
            ['icon' => 'bi-award', 'title' => 'Certified Gemstones', 'text' => 'All diamonds and gemstones are independently certified for quality and origin.'],
            ['icon' => 'bi-shield-check', 'title' => 'Lifetime Warranty', 'text' => 'Every purchase includes our lifetime craftsmanship warranty for your peace of mind.'],
        ];
        
        $heroHeadline = App\Models\WebsiteContent::getValue('hero_headline', 'Heritage & Modern Elegance');
        $heroSubheadline = App\Models\WebsiteContent::getValue('hero_subheadline', 'Curated pieces crafted with generational artistry and modern perfection.');
        $aboutIntro = App\Models\WebsiteContent::getValue('about_intro', 'For over twelve years, Jaffna Gold has been synonymous with exceptional craftsmanship. Our journey began in the ancient city of Jaffna, where master goldsmiths passed down their art through three generations. Today, we blend traditional techniques with contemporary design to create pieces that transcend trends.');
    @endphp

    <style>
        .home-sweep-btn {
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        .home-sweep-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform 0.35s ease;
            z-index: -1;
        }

        .home-sweep-btn.btn-jg-outline::before {
            background: var(--navy);
        }

        .home-sweep-btn:hover::before,
        .home-sweep-btn:focus-visible::before {
            transform: scaleX(1);
        }

        .home-sweep-btn.btn-jg-outline:hover,
        .home-sweep-btn.btn-jg-outline:focus-visible {
            background: transparent;
            color: var(--white);
        }

        .hero-watermark {
            position: absolute;
            left: 3rem;
            top: 50%;
            width: min(55vw, 550px);
            transform: translateY(-50%);
            opacity: 0.15;
            z-index: 1;
            pointer-events: none;
            filter: brightness(1.6) saturate(0);
        }

        .press-strip {
            overflow: hidden;
        }

        .press-strip .inner {
            display: flex;
            width: max-content;
        }

        .press-strip-track {
            display: flex;
            align-items: center;
            width: max-content;
            animation: pressTicker 24s linear infinite;
        }

        .press-strip-segment {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .press-strip-item {
            display: inline-flex;
            align-items: center;
            gap: 1.5rem;
            padding-inline: 0.9rem;
            font-size: 0.82rem;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: inherit;
            text-decoration: none;
            position: relative;
            transition: color 0.3s ease;
            white-space: nowrap;
        }

        .press-strip-item i {
            font-size: 0.78rem;
            color: var(--accent);
            transition: color 0.3s ease;
        }

        @keyframes pressTicker {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        .press-strip:hover .press-strip-track,
        .press-strip:focus-within .press-strip-track {
            animation-play-state: paused;
        }

        .press-strip-item__label {
            position: relative;
            display: inline-flex;
            color: #4f766e;
            opacity: 0.7;
            transition: color 0.3s ease, opacity 0.3s ease, text-shadow 0.5s ease;
        }

        .press-strip-item__label::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -0.28rem;
            width: 100%;
            height: 1px;
            background: currentColor;
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform 0.5s ease, opacity 0.5s ease;
            opacity: 0.85;
        }

        .press-strip-item:hover .press-strip-item__label,
        .press-strip-item:focus-visible .press-strip-item__label {
            opacity: 1;
            color: #63a49d;
        }

        /* ===== WHY JAFFNA GOLD CAROUSEL ===== */
        .why-carousel-section {
            position: relative;
            overflow: hidden;
            padding: 5rem 1rem;
            color: var(--white);
        }

        .why-carousel-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 1;
            filter: brightness(0.35); /* Darken bg image */
        }

        .why-carousel-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(39, 33, 27, 0.65), rgba(39, 33, 27, 0.9));
            z-index: 2;
        }

        .why-carousel-shell {
            position: relative;
            z-index: 3;
        }

        .why-carousel-shell .eyebrow {
            color: var(--accent);
            font-size: 2rem;
            font-weight: 500;
        }

        .why-carousel-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        .why-carousel-viewport {
            flex: 1;
            overflow: hidden;
            border-radius: 16px;
        }

        .why-carousel-track {
            display: flex;
            transition: transform 0.85s cubic-bezier(0.25, 1, 0.5, 1);
            width: 100%;
        }

        .why-carousel-slide {
            flex: 0 0 100%;
            width: 100%;
            padding: 0.5rem;
            box-sizing: border-box;
        }

        .why-carousel-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 2.8rem 2.2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .why-carousel-card:hover {
            transform: translateY(-2px);
            border-color: rgba(201, 168, 76, 0.4);
        }

        .why-carousel-card i {
            font-size: 2.5rem;
            color: var(--accent);
            display: inline-block;
            margin-bottom: 1.2rem;
        }

        .why-carousel-card h4 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 500;
            color: #ffffff !important;
            margin-bottom: 0.8rem;
            letter-spacing: 0.02em;
        }

        .why-carousel-card p {
            font-size: 0.92rem;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.82);
            max-width: 580px;
            margin: 0 auto;
        }

        .why-carousel-nav {
            display: flex;
            align-items: center;
            flex: 0 0 auto;
        }

        .why-carousel-btn {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 1px solid rgba(201, 168, 76, 0.45);
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .why-carousel-btn:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: #ffffff;
            transform: scale(1.05);
        }

        .why-carousel-btn i {
            font-size: 1.1rem;
        }

        @media (max-width: 1024px) {
            .home-product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .home-explore-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .hero-watermark {
                left: 1rem;
                width: 180px;
                opacity: 0.12;
            }

            .hero-frame {
                overflow: hidden;
            }

            .hero-jg .content {
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
                gap: 1rem;
                padding: 4.25rem 1rem 3.5rem;
                min-height: 600px;
            }

            .hero-jg .content h1,
            .hero-jg .content p {
                max-width: 100%;
            }

            .hero-jg .content h1 {
                margin-bottom: 0;
            }

            .hero-jg .content p {
                margin-bottom: 0;
                line-height: 1.65;
            }

            .hero-jg .content .ctas {
                flex-direction: column;
                flex-wrap: nowrap;
                align-items: stretch;
                gap: 0.85rem;
                width: 100%;
            }

            .hero-jg .content .ctas .btn-jg {
                width: 100%;
                justify-content: center;
                min-height: 48px;
                padding: 0.95rem 1.25rem;
                font-size: 0.74rem;
            }

            .why-carousel-section {
                padding-block: 3rem;
            }

            .why-carousel-wrap {
                display: block;
                max-width: 100%;
            }

            .why-carousel-slide {
                min-height: auto;
                padding: 0.5rem 0;
            }

            .home-explore-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .two-col {
                gap: 1.5rem;
            }

            .two-col .image-block,
            .two-col .text-block {
                min-width: 0;
            }

            .two-col .image-block {
                order: 1;
            }

            .two-col .image-block video,
            .two-col .image-block img {
                width: 100%;
                height: auto;
                display: block;
            }

            .two-col .text-block {
                order: 2;
            }

            .stats-row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
                align-items: stretch;
                justify-content: stretch;
            }

            .stats-row > div {
                min-width: 0;
            }

            .stat-item,
            .stats-row > div:last-child {
                display: flex;
                flex-direction: column;
                justify-content: center;
                min-height: 96px;
                padding: 0.85rem 0.75rem;
                border-radius: 12px;
                background: rgba(201, 168, 76, 0.08);
            }

            .stats-row > div:last-child .btn-jg {
                width: 100%;
                min-height: 44px;
            }

            .why-carousel-viewport {
                margin: 0.85rem 0;
            }

            .why-carousel-nav {
                justify-content: center;
                gap: 0.75rem;
            }

            .why-carousel-btn {
                width: 44px;
                height: 44px;
            }

            .why-carousel-card {
                padding: 1.5rem 1rem;
            }

            .why-carousel-card h4 {
                font-size: 1.2rem;
            }

            .why-carousel-card p {
                font-size: 0.86rem;
            }

            .product-card .info h3,
            .product-card .info .details,
            .product-card .info .price {
                overflow-wrap: anywhere;
            }

            .testimonial-card {
                padding: 1.35rem 1rem;
            }
        }

        @media (max-width: 640px) {
            .home-product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .home-explore-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .why-carousel-wrap {
                display: block;
            }

            .why-carousel-viewport {
                margin: 0.85rem 0;
            }

            .why-carousel-nav {
                display: flex;
                justify-content: center;
                gap: 0.7rem;
            }
        }

        @media (max-width: 480px) {
            .hero-jg .content {
                padding: 2rem 1rem 8rem;
                min-height: 560px;
                gap: 0.85rem;
            }

            .hero-jg .content h1 {
                font-size: 2.35rem;
                max-width: 9ch;
            }

            .hero-jg .content p {
                font-size: 0.92rem;
            }

            .hero-jg .content .ctas .btn-jg {
                font-size: 0.7rem;
                padding-inline: 1rem;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .home-product-grid,
            .home-explore-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.375rem;
            }

            .why-carousel-card {
                padding: 1.35rem 0.9rem;
            }

            .why-carousel-card i {
                font-size: 2rem;
            }

            .testimonial-card .author {
                align-items: flex-start;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .press-strip-track {
                animation: none;
                transform: none;
            }
        }

        /* ===== 10% DISCOUNT ANIMATED STYLES ===== */
        .hero-discount-badge-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 1.1rem;
        }

        .hero-discount-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.55rem 1.4rem;
            background: linear-gradient(135deg, rgba(39, 33, 27, 0.92) 0%, rgba(75, 59, 41, 0.96) 50%, rgba(39, 33, 27, 0.92) 100%);
            border: 1.5px solid #cca43b;
            border-radius: 999px;
            box-shadow: 0 0 22px rgba(204, 164, 59, 0.45);
            animation: floatBadge 3.5s ease-in-out infinite, pulseBadgeGlow 2.5s ease-in-out infinite;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(8px);
        }

        .hero-discount-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45), transparent);
            transform: skewX(-25deg);
            animation: goldShimmer 2.8s infinite;
        }

        .hero-discount-badge .discount-percent {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 800;
            color: #ffd700;
            text-shadow: 0 0 12px rgba(255, 215, 0, 0.7);
            letter-spacing: 0.04em;
            line-height: 1;
        }

        .hero-discount-badge .discount-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #f3e0ad;
            display: flex;
            align-items: center;
            gap: 0.4rem;
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

        .discount-banner-section {
            background: linear-gradient(135deg, #1f1a16 0%, #312821 50%, #1f1a16 100%);
            padding: 2.8rem 1rem;
            border-top: 1px solid rgba(204, 164, 59, 0.3);
            border-bottom: 1px solid rgba(204, 164, 59, 0.3);
            position: relative;
            overflow: hidden;
        }

        .discount-banner-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 50%, rgba(204, 164, 59, 0.15), transparent 70%);
            pointer-events: none;
        }

        .discount-banner-container {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .discount-banner-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .discount-badge-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4af37, #997510);
            border: 3px solid #ffe699;
            box-shadow: 0 0 25px rgba(212, 175, 55, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            flex-shrink: 0;
            animation: pulseMini 2.5s infinite ease-in-out;
        }

        .discount-badge-circle .circle-num {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .discount-badge-circle .circle-text {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.12em;
        }

        .discount-banner-text {
            flex: 1;
            min-width: 260px;
        }

        .discount-banner-text h3 {
            color: #f9f3ea;
            font-size: 1.75rem;
            margin-bottom: 0.4rem;
        }

        .discount-banner-text p {
            color: rgba(249, 243, 234, 0.82);
            font-size: 0.92rem;
            line-height: 1.5;
            margin: 0;
        }

        .discount-banner-btn {
            background: #cca43b;
            color: #1f1a16;
            border: 1px solid #cca43b;
            font-weight: 700;
            padding: 0.9rem 1.6rem;
            transition: all 0.3s ease;
        }

        .discount-banner-btn:hover {
            background: #e6b83b;
            box-shadow: 0 0 20px rgba(230, 184, 59, 0.5);
            transform: translateY(-2px);
        }

        @keyframes floatBadge {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-7px); }
        }

        @keyframes pulseBadgeGlow {
            0%, 100% {
                box-shadow: 0 0 15px rgba(204, 164, 59, 0.35), inset 0 0 10px rgba(204, 164, 59, 0.2);
                border-color: rgba(204, 164, 59, 0.7);
            }
            50% {
                box-shadow: 0 0 30px rgba(255, 215, 0, 0.8), inset 0 0 20px rgba(255, 215, 0, 0.45);
                border-color: rgba(255, 215, 0, 1);
            }
        }

        @keyframes goldShimmer {
            0% { left: -100%; }
            30%, 100% { left: 200%; }
        }

        @keyframes pulseMini {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
    </style>

    <!-- ===== HERO ===== -->
    <section class="hero-jg" id="hero">
        <div class="hero-frame">
            <div class="hero-media">
                @forelse ($heroImages as $index => $heroImage)
                    <img
                        src="{{ asset('images/website images/hero/' . basename($heroImage)) }}"
                        alt="Jaffna Gold hero showcase image {{ $index + 1 }}"
                        class="hero-slide {{ $index === 0 ? 'is-active' : '' }}"
                    >
                @empty
                    <img
                        src="{{ asset('images/website images/hero/hero_8.png') }}"
                        alt="Jaffna Gold handcrafted jewelry displayed on textured fabric"
                        class="hero-slide is-active"
                    >
                @endforelse
            </div>
            <div class="overlay"></div>
            <img
                src="{{ asset('images/website images/logohero.png') }}"
                alt=""
                class="hero-watermark"
                aria-hidden="true"
            >
            <div class="content">
                <div class="hero-discount-badge-wrap">
                    <div class="hero-discount-badge">
                        <span class="discount-percent">10% OFF</span>
                        <span class="discount-label"><i class="bi bi-stars"></i> SPECIAL INSTANT DISCOUNT <i class="bi bi-stars"></i></span>
                    </div>
                </div>

                <span class="eyebrow">Jaffna Gold</span>
                <h1>
                    <span>{{ $heroHeadline }}</span>
                </h1>
                <p>{{ $heroSubheadline }}</p>
                <div class="ctas">
                    <a href="{{ route('shop') }}" class="btn-jg btn-jg-primary">Shop Collection</a>
                    <a href="{{ route('about') }}" class="btn-jg btn-jg-light">Our Story</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== PRESS STRIP ===== -->
    <section class="press-strip">
        <div class="container-jg">
            <div class="inner">
                <div class="press-strip-track">
                    @foreach ([false, true] as $isDuplicate)
                        <div class="press-strip-segment"{{ $isDuplicate ? ' aria-hidden=true' : '' }}>
                            @foreach ($stripItems as $stripItem)
                                <a href="{{ $stripItem['href'] }}" class="press-strip-item">
                                    <span class="press-strip-item__label">{{ $stripItem['label'] }}</span>
                                    <i class="bi bi-diamond-fill" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ===== ABOUT PREVIEW / CRAFT STORY ===== -->
    <section class="section-jg section-jg-white">
        <div class="container-jg">
            <div class="two-col">
                <div class="image-block reveal">
                    <video autoplay muted loop playsinline preload="auto" class="w-full h-full object-cover">
                        <source src="{{ asset('images/website images/vid/vid1.mp4') }}" type="video/mp4">
                    </video>
                </div>
                <div class="text-block reveal reveal-delay-1">
                    <span class="eyebrow">Our Craft Story</span>
                    <h2>3 Generations of Goldsmithing</h2>
                    <p>{{ $aboutIntro }}</p>
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="number">500+</div>
                            <div class="label">Designs</div>
                        </div>
                        <div class="stat-item">
                            <div class="number">12</div>
                            <div class="label">Years</div>
                        </div>
                        <div class="stat-item">
                            <div class="number">50K+</div>
                            <div class="label">Customers</div>
                        </div>
                        <div>
                            <a href="{{ route('about') }}" class="btn-jg btn-jg-outline home-sweep-btn" style="text-align: center;">Meet Our Artisans</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== 10% DISCOUNT PROMO BANNER ===== -->
    <section class="discount-banner-section">
        <div class="discount-banner-container">
            <div class="discount-banner-content">
                <div class="discount-badge-circle">
                    <span class="circle-num">10%</span>
                    <span class="circle-text">OFF</span>
                </div>
                <div class="discount-banner-text">
                    <h3>Storewide 10% Instant Discount</h3>
                    <p>Celebration offer automatically calculated on retail prices across our entire handcrafted jewelry collection.</p>
                </div>
                <a href="{{ route('shop') }}" class="btn-jg discount-banner-btn">Shop Discounted Collection <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- ===== New Arrivals Products ===== -->
    <section class="section-jg section-jg-white">
        <div class="container-jg">
            <div class="text-center" style="margin-bottom:1rem;">
                <h2 class="eyebrow" style="font-size:2rem;">New Arrivals</h2>
            </div>
            <div class="product-grid home-product-grid">
                @foreach ($newArrivalProducts as $index => $product)
                    <div class="product-card reveal {{ $index > 0 ? 'reveal-delay-' . min($index, 5) : '' }}" wire:key="new-arrival-{{ $product['id'] }}" onclick="if (event.target.closest('button, a')) return; window.location='{{ route('product-detail', ['id' => $product['id']]) }}'">
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
                                <button class="card-tool-btn action-icon-btn cart-btn{{ $product['cart'] ? ' active' : '' }}" type="button" wire:click.prevent="toggleCart('{{ $product['id'] }}')" @if(!$product['in_stock']) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                                    <i class="bi {{ $product['cart'] ? 'bi-bag-fill' : 'bi-bag' }}"></i>
                                </button>
                                <button class="btn-jg btn-jg-primary btn-jg-sm" type="button" wire:click.prevent="buyNow('{{ $product['id'] }}')" @if(!$product['in_stock']) disabled style="opacity: 0.5;" @endif>Buy Now</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center" style="margin-top:2.5rem;">
                <a href="{{ route('shop') }}" class="btn-jg btn-jg-outline home-sweep-btn">View All New Arrivals</a>
            </div>
        </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <section class="section-jg section-jg-cream why-carousel-section">
        <div class="why-carousel-bg" style="background-image:url('{{ asset('images/website images/img/img_1.jpeg') }}')"></div>
        <div class="why-carousel-overlay"></div>
        <div class="container-jg why-carousel-shell">
            <div class="text-center" style="margin-bottom:2rem;">
                <span class="eyebrow">Why Jaffna Gold</span>
            </div>
            <div class="why-carousel-wrap">
                <div class="why-carousel-nav">
                    <button class="why-carousel-btn" type="button" data-why-carousel-prev aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </div>
                <div class="why-carousel-viewport">
                    <div class="why-carousel-track" data-why-carousel-track>
                        @foreach (array_merge($featureItems, $featureItems) as $feature)
                            <div class="why-carousel-slide">
                                <div class="why-carousel-card">
                                    <i class="bi {{ $feature['icon'] }}"></i>
                                    <h4>{{ $feature['title'] }}</h4>
                                    <p>{{ $feature['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="why-carousel-nav">
                    <button class="why-carousel-btn" type="button" data-why-carousel-next aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FEATURED PRODUCTS ===== -->
    <section class="section-jg section-jg-white">
        <div class="container-jg">
            <div class="text-center" style="margin-bottom:1rem;">
                <h2 class="eyebrow" style="font-size:2rem;">Featured Products</h2>
            </div>
            <div class="product-grid home-product-grid">
                @foreach ($featuredProducts as $index => $product)
                    <div class="product-card reveal {{ $index > 0 ? 'reveal-delay-' . min($index, 5) : '' }}" wire:key="featured-{{ $product['id'] }}" onclick="if (event.target.closest('button, a')) return; window.location='{{ route('product-detail', ['id' => $product['id']]) }}'">
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
                                <button class="card-tool-btn action-icon-btn cart-btn{{ $product['cart'] ? ' active' : '' }}" type="button" wire:click.prevent="toggleCart('{{ $product['id'] }}')" @if(!$product['in_stock']) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                                    <i class="bi {{ $product['cart'] ? 'bi-bag-fill' : 'bi-bag' }}"></i>
                                </button>
                                <button class="btn-jg btn-jg-primary btn-jg-sm" type="button" wire:click.prevent="buyNow('{{ $product['id'] }}')" @if(!$product['in_stock']) disabled style="opacity: 0.5;" @endif>Buy Now</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center" style="margin-top:2.5rem;">
                <a href="{{ route('shop') }}" class="btn-jg btn-jg-outline home-sweep-btn">View All Featured Products</a>
            </div>
        </div>
    </section>

    <!-- ===== EXPLORE MORE DESIGNS ===== -->
    <section class="section-jg section-jg-white" style="padding-top:0;">
        <div class="container-jg">
            <div class="text-center" style="margin-bottom:1rem;">
                <h2 class="eyebrow" style="font-size:2rem;">Explore More Designs</h2>
            </div>
            <div class="product-grid home-explore-grid">
                @foreach ($scrollProducts as $product)
                    <div class="product-card reveal visible" wire:key="scroll-product-{{ $product['id'] }}" onclick="if (event.target.closest('button, a')) return; window.location='{{ route('product-detail', ['id' => $product['id']]) }}'">
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
                                <button class="card-tool-btn action-icon-btn cart-btn{{ $product['cart'] ? ' active' : '' }}" type="button" wire:click.prevent="toggleCart('{{ $product['id'] }}')" @if(!$product['in_stock']) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                                    <i class="bi {{ $product['cart'] ? 'bi-bag-fill' : 'bi-bag' }}"></i>
                                </button>
                                <button class="btn-jg btn-jg-primary btn-jg-sm" type="button" wire:click.prevent="buyNow('{{ $product['id'] }}')" @if(!$product['in_stock']) disabled style="opacity: 0.5;" @endif>Buy Now</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center" style="margin-top:2.5rem;">
                <a href="{{ route('shop') }}" class="btn-jg btn-jg-outline home-sweep-btn">View More</a>
            </div>
        </div>
    </section>

    <!-- ===== TESTIMONIALS ===== -->
    <section class="section-jg section-jg-cream">
        <div class="container-jg">
            <div class="text-center" style="margin-bottom:1rem;">
                <span class="eyebrow">Reviews</span>
                <h2 style="font-size:2rem;">Loved by Our Customers</h2>
            </div>
            <div class="testimonial-grid">
                <div class="testimonial-card reveal">
                    <p class="quote">"The craftsmanship is absolutely stunning. I've never owned a piece of jewelry that feels so special. The diamond ring exceeded every expectation."</p>
                    <div class="author">
                        <div class="avatar">S</div>
                        <div class="author-info">
                            <div class="name">Sarah M.</div>
                            <div class="product">Classic Diamond Ring</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal reveal-delay-1">
                    <p class="quote">"I ordered the pearl earrings for my wedding day and they were perfect. The quality is remarkable and the packaging made them feel even more precious."</p>
                    <div class="author">
                        <div class="avatar">E</div>
                        <div class="author-info">
                            <div class="name">Emily R.</div>
                            <div class="product">Pearl Drop Earrings</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal reveal-delay-2">
                    <p class="quote">"A family heirloom in the making. The gold necklace is even more beautiful in person. You can feel the love and care in every detail."</p>
                    <div class="author">
                        <div class="avatar">A</div>
                        <div class="author-info">
                            <div class="name">Amara K.</div>
                            <div class="product">Gold Chain Necklace</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FINAL CTA BANNER ===== -->
    <section class="cta-banner">
        <div class="container-jg">
            <h2>Design Your Dream Piece</h2>
            <p>Book a free consultation with our master artisans and bring your vision to life.</p>
            <a href="{{ route('contact') }}" class="btn-jg btn-jg-primary">Book Now</a>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const track = document.querySelector('[data-why-carousel-track]');
            const prevButton = document.querySelector('[data-why-carousel-prev]');
            const nextButton = document.querySelector('[data-why-carousel-next]');

            if (!track || !prevButton || !nextButton) {
                return;
            }

            const uniqueSlides = track.children.length / 2;
            let currentIndex = 0;
            let autoplayId = null;

            const updateTrack = (useTransition = true) => {
                track.style.transition = useTransition ? 'transform 0.85s ease' : 'none';
                track.style.transform = `translateX(-${currentIndex * 100}%)`;
            };

            const restartAutoplay = () => {
                window.clearInterval(autoplayId);
                autoplayId = window.setInterval(() => {
                    currentIndex += 1;
                    updateTrack(true);
                }, 3000);
            };

            nextButton.addEventListener('click', () => {
                currentIndex += 1;
                updateTrack(true);
                restartAutoplay();
            });

            prevButton.addEventListener('click', () => {
                if (currentIndex === 0) {
                    currentIndex = uniqueSlides;
                    updateTrack(false);
                }

                window.requestAnimationFrame(() => {
                    currentIndex -= 1;
                    updateTrack(true);
                });
                restartAutoplay();
            });

            track.addEventListener('transitionend', () => {
                if (currentIndex >= uniqueSlides) {
                    currentIndex = 0;
                    updateTrack(false);
                }
            });

            updateTrack(false);
            restartAutoplay();
        });
    </script>
</div>
