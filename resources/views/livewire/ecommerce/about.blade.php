<div>
@php
    $buildYoutubeEmbed = static function (string $videoId, bool $muted = true, bool $autoplay = false): string {
        $params = [
            'enablejsapi' => 1,
            'playsinline' => 1,
            'rel' => 0,
            'controls' => 1,
            'modestbranding' => 1,
            'playlist' => $videoId,
            'loop' => 1,
        ];

        if ($muted) {
            $params['mute'] = 1;
        }

        if ($autoplay) {
            $params['autoplay'] = 1;
        }

        return 'https://www.youtube.com/embed/' . $videoId . '?' . http_build_query($params);
    };

    $legacyStats = [
        ['value' => '1988', 'label' => 'Workshop Founded'],
        ['value' => '3', 'label' => 'Generations of Goldsmiths'],
        ['value' => '25+', 'label' => 'Master Artisans'],
        ['value' => '10k+', 'label' => 'Pieces Handcrafted'],
    ];
@endphp

<style>
    .about-hero {
        position: relative;
        min-height: clamp(380px, 50vh, 560px);
        width: 100vw;
        max-width: none;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        overflow: hidden;
        background: #1f1a16;
        isolation: isolate;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .about-hero-media,
    .about-boutique-media {
        position: absolute;
        inset: 0;
    }

    .about-hero-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }

    .about-hero-overlay,
    .about-boutique-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(31, 26, 22, 0.5) 0%, rgba(31, 26, 22, 0.32) 48%, rgba(31, 26, 22, 0.72) 100%);
    }

    .about-hero-content,
    .about-boutique-content {
        position: relative;
        z-index: 1;
        text-align: center;
        color: var(--white);
        max-width: 46rem;
        padding: 2rem 1.5rem;
    }

    .about-hero-content .eyebrow,
    .about-boutique-content .eyebrow {
        color: #f3e0ad;
        letter-spacing: 0.15em;
        font-size: 0.7rem;
        font-weight: 500;
    }

    .about-hero-content h1,
    .about-boutique-content h2 {
        color: #f9f3ea;
        margin: 1rem 0 1.1rem;
    }

    .about-hero-content h1 {
        font-size: clamp(2.2rem, 4.8vw, 3.5rem);
        line-height: 1.05;
        font-weight: 600;
    }

    .about-hero-content p,
    .about-boutique-content p {
        color: rgba(249, 243, 234, 0.88);
        font-size: 0.95rem;
        line-height: 1.7;
        max-width: 34rem;
        margin: 0 auto;
    }

    .about-section-intro {
        text-align: center;
        max-width: 40rem;
        margin: 0 auto 2rem;
    }

    .about-section-intro h2 {
        font-size: clamp(1.95rem, 3.2vw, 2.8rem);
        margin-bottom: 0.9rem;
    }

    .about-section-intro p,
    .about-copy-card p,
    .about-process-intro p,
    .about-process-info p {
        font-size: 0.9rem;
        line-height: 1.7;
        color: var(--body);
    }

    .about-copy-card h2,
    .about-process-intro h2,
    .about-boutique-content h2 {
        font-size: clamp(1.9rem, 3.1vw, 2.7rem);
        font-weight: 600;
        color: var(--navy);
    }

    .about-copy-card h2,
    .about-process-intro h2 {
        margin-bottom: 0.95rem;
    }

    .about-process-info h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--navy);
    }

    .about-split-section {
        position: relative;
    }

    .about-split-section::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 75%;
        width: 22px;
        transform: translateX(-50%);
        background:
            linear-gradient(90deg, transparent 0 32%, rgba(201, 168, 76, 0.94) 32% 46%, transparent 46% 54%, rgba(201, 168, 76, 0.94) 54% 68%, transparent 68% 100%);
        opacity: 0.92;
        pointer-events: none;
    }

    .about-split-grid,
    .about-artisan-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 2.4rem;
        align-items: center;
        position: relative;
        z-index: 1;
    }

    .about-split-grid {
        align-items: stretch;
    }

    .about-artisan-grid {
        grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
    }

    .about-copy-card {
        background: var(--white);
        border: 1px solid rgba(201, 168, 76, 0.22);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .about-copy-card p + p {
        margin-top: 1rem;
    }

    .about-media-frame {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 4 / 3;
        background: var(--cream);
        border: 1px solid rgba(201, 168, 76, 0.2);
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
    }

    .about-split-grid .about-media-frame {
        aspect-ratio: auto;
        height: 100%;
        min-height: 100%;
    }

    .about-media-frame iframe,
    .about-process-media iframe,
    .about-boutique-media iframe,
    .about-media-frame video,
    .about-process-media video,
    .about-boutique-media video {
        width: 100%;
        height: 100%;
        display: block;
        border: 0;
    }

    .about-media-label {
        position: absolute;
        top: 1rem;
        left: 1rem;
        z-index: 2;
        background: rgba(31, 26, 22, 0.78);
        color: #f3e0ad;
        font-size: 0.62rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        padding: 0.38rem 0.82rem;
        border-radius: 999px;
    }

    /* --- New: legacy stats strip --- */
    .about-legacy {
        position: relative;
        width: 100vw;
        max-width: none;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        background: #1f1a16;
        padding: clamp(2.4rem, 5vw, 3.6rem) 1.5rem;
    }

    .about-legacy-inner {
        max-width: 72rem;
        margin: 0 auto;
        text-align: center;
    }

    .about-legacy-inner .eyebrow {
        color: #f3e0ad;
        letter-spacing: 0.28em;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .about-legacy-grid {
        margin-top: 1.8rem;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.5rem;
    }

    .about-legacy-stat {
        border-left: 1px solid rgba(243, 224, 173, 0.22);
        padding: 0.2rem 0.6rem;
    }

    .about-legacy-grid .about-legacy-stat:first-child {
        border-left: none;
    }

    .about-legacy-stat strong {
        display: block;
        font-size: clamp(1.9rem, 3.6vw, 2.8rem);
        color: #f9f3ea;
        line-height: 1.1;
        margin-bottom: 0.35rem;
    }

    .about-legacy-stat span {
        display: block;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        color: rgba(249, 243, 234, 0.72);
    }

    .about-process-intro {
        text-align: center;
        max-width: 42rem;
        margin: 0 auto 2.2rem;
    }

    .about-process-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.5rem;
    }

    .about-process-card {
        background: var(--white);
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(201, 168, 76, 0.22);
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .about-process-card:hover {
        transform: translateY(-6px) scale(1.015);
        box-shadow: 0 14px 30px rgba(49, 40, 33, 0.12);
    }

    .about-process-media {
        aspect-ratio: 4 / 5;
        overflow: hidden;
        background: var(--cream);
    }

    .about-process-info {
        padding: 1.2rem;
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
    }

    .about-process-info h4 {
        margin-bottom: 0.55rem;
    }

    .about-process-info p {
        color: var(--body);
    }

    .about-boutique {
        position: relative;
        min-height: clamp(380px, 50vh, 560px);
        width: 100vw;
        max-width: none;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        overflow: hidden;
        background: #1f1a16;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    .about-boutique-content h2 {
        color: #f9f3ea;
    }

    @media (max-width: 1024px) {
        .about-split-section::before {
            display: none;
        }

        .about-split-grid,
        .about-artisan-grid {
            grid-template-columns: 1fr;
        }

        .about-process-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .about-legacy-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 1.8rem;
        }

        .about-legacy-grid .about-legacy-stat:nth-child(3) {
            border-left: none;
        }
    }

    @media (max-width: 768px) {
        .about-hero {
            min-height: 56vh;
        }

        .about-artisan-grid {
            gap: 1.25rem;
        }

        .about-media-frame,
        .about-copy-card {
            min-width: 0;
        }

        .about-media-frame {
            order: 1;
        }

        .about-media-frame iframe {
            width: 100%;
            height: auto;
            aspect-ratio: 4 / 3;
        }

        .about-copy-card {
            order: 2;
        }

        .about-copy-card {
            padding: 1.5rem;
        }

        .about-legacy-grid {
            grid-template-columns: 1fr;
        }

        .about-legacy-stat {
            border-left: none;
            border-top: 1px solid rgba(243, 224, 173, 0.22);
            padding-top: 1rem;
        }

        .about-legacy-grid .about-legacy-stat:first-child {
            border-top: none;
            padding-top: 0;
        }
    }

    @media (max-width: 640px) {
        .about-process-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .about-copy-card {
            padding: 1.15rem 1rem;
        }

        .about-process-info {
            padding: 1rem 0.95rem 1.1rem;
        }

        .about-process-info p,
        .about-copy-card p {
            overflow-wrap: anywhere;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .about-process-card,
        .reveal {
            transition: none !important;
        }
    }
</style>

<section class="section-jg section-jg-white about-split-section">
    <div class="container-jg">
        <div class="about-artisan-grid">
            <div class="about-media-frame reveal">
                <span class="about-media-label">Our Vision</span>
                <iframe
                    src="{{ $buildYoutubeEmbed('qzEhIzwKATg', false) }}"
                    data-base-src="{{ $buildYoutubeEmbed('qzEhIzwKATg', false) }}"
                    data-autoplay-src="{{ $buildYoutubeEmbed('qzEhIzwKATg', false, true) }}"
                    title="22 March 2025"
                    loading="lazy"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen>
                </iframe>
            </div>

            <div class="about-copy-card reveal reveal-delay-1">
                <span class="eyebrow">OUR ARTISANS</span>
                <h2>Master Craftsmen Behind Every Piece</h2>
                <p>{{ App\Models\WebsiteContent::getValue('about_intro', 'Our team of skilled artisans brings decades of combined experience in traditional jewelry making. From goldsmiths and stone setters to polishers and gem specialists, every person contributes knowledge, precision, and pride to the final piece.') }}</p>
                <p>{{ App\Models\WebsiteContent::getValue('about_details', 'We value fair working conditions, responsible craftsmanship, and the preservation of skills passed down through families and generations.') }}</p>
            </div>
        </div>
    </div>
</section>

<section class="about-legacy">
    <div class="about-legacy-inner reveal">
        <span class="eyebrow">OUR LEGACY</span>
        <div class="about-legacy-grid">
            @foreach ($legacyStats as $stat)
                <div class="about-legacy-stat">
                    <strong>{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-jg section-jg-cream">
    <div class="container-jg">
        <div class="about-process-intro reveal">
            <span class="eyebrow">THE CRAFT</span>
            <h2>The Making of a Jaffna Gold Piece</h2>
            <p>From the first sketch to the final polish, every stage is guided by precision, patience, and skilled hands.</p>
        </div>

        <div class="about-process-grid">
            <article class="about-process-card reveal">
                <div class="about-process-media">
                    <iframe
                        src="{{ $buildYoutubeEmbed('pum2irlsLd8') }}"
                        data-base-src="{{ $buildYoutubeEmbed('pum2irlsLd8') }}"
                        data-autoplay-src="{{ $buildYoutubeEmbed('pum2irlsLd8', true, true) }}"
                        title="31 January 2024"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="about-process-info">
                    <h4>Designing the Piece</h4>
                    <p>Every creation begins with an idea, carefully shaped through sketches, measurements, and design planning.</p>
                </div>
            </article>

            <article class="about-process-card reveal reveal-delay-1">
                <div class="about-process-media">
                    <iframe
                        src="{{ $buildYoutubeEmbed('WMLapVZPx6c') }}"
                        data-base-src="{{ $buildYoutubeEmbed('WMLapVZPx6c') }}"
                        data-autoplay-src="{{ $buildYoutubeEmbed('WMLapVZPx6c', true, true) }}"
                        title="more design available"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="about-process-info">
                    <h4>Shaping the Gold</h4>
                    <p>Skilled hands form and refine the metal using techniques built through years of experience.</p>
                </div>
            </article>

            <article class="about-process-card reveal reveal-delay-2">
                <div class="about-process-media">
                    <iframe
                        src="{{ $buildYoutubeEmbed('PkzKtrtkWwg') }}"
                        data-base-src="{{ $buildYoutubeEmbed('PkzKtrtkWwg') }}"
                        data-autoplay-src="{{ $buildYoutubeEmbed('PkzKtrtkWwg', true, true) }}"
                        title="New designs available jaffnagold covering Jaffna #jewellery #gold #jewelry #necklace #chain"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="about-process-info">
                    <h4>Detailing and Stone Setting</h4>
                    <p>Each detail is carefully completed, from intricate surface work to precise gemstone placement.</p>
                </div>
            </article>

            <article class="about-process-card reveal reveal-delay-3">
                <div class="about-process-media">
                    <iframe
                        src="{{ $buildYoutubeEmbed('-00IiIfE5Ls') }}"
                        data-base-src="{{ $buildYoutubeEmbed('-00IiIfE5Ls') }}"
                        data-autoplay-src="{{ $buildYoutubeEmbed('-00IiIfE5Ls', true, true) }}"
                        title="29 January 2026"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="about-process-info">
                    <h4>Finishing and Inspection</h4>
                    <p>The piece is polished, inspected, and prepared before it becomes part of the Jaffna Gold collection.</p>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="about-boutique">
    <div class="about-boutique-media">
        <video autoplay muted loop playsinline preload="metadata" aria-label="Decorative boutique experience video">
            <source src="{{ asset('images/website images/vid/hero_video.mp4') }}" type="video/mp4">
        </video>
        <div class="about-boutique-overlay"></div>
    </div>
    <div class="about-boutique-content reveal">
        <span class="eyebrow">VISIT JAFFNA GOLD</span>
        <h2>Inside Our Boutique</h2>
        <p>Step inside Jaffna Gold and explore our collections in a setting designed for personal service, thoughtful guidance, and memorable jewelry experiences.</p>
        <a href="{{ route('contact') }}" class="btn-jg btn-jg-light">Plan Your Visit</a>
    </div>
</section>

<script>
    (() => {
        const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const setIframePlayback = (iframe, shouldAutoplay) => {
            if (!iframe) {
                return;
            }

            const nextSrc = shouldAutoplay ? iframe.dataset.autoplaySrc : iframe.dataset.baseSrc;

            if (nextSrc && iframe.src !== nextSrc) {
                iframe.src = nextSrc;
            }
        };

        const artisanObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                const iframe = entry.target.querySelector('iframe[data-base-src]');

                if (!iframe) {
                    return;
                }

                if (entry.isIntersecting && entry.intersectionRatio >= 0.45 && !prefersReducedMotion) {
                    setIframePlayback(iframe, true);
                } else {
                    setIframePlayback(iframe, false);
                }
            });
        }, {
            threshold: [0, 0.45, 0.75],
        });

        document.querySelectorAll('.about-media-frame').forEach((frame) => {
            const iframe = frame.querySelector('iframe[data-base-src]');

            if (!iframe) {
                return;
            }

            artisanObserver.observe(frame);
        });

        document.querySelectorAll('.about-process-card').forEach((card) => {
            const iframe = card.querySelector('.about-process-media iframe[data-base-src]');

            if (!iframe) {
                return;
            }

            const startPlayback = () => {
                if (!prefersReducedMotion) {
                    setIframePlayback(iframe, true);
                }
            };

            const stopPlayback = () => {
                setIframePlayback(iframe, false);
            };

            card.addEventListener('mouseenter', startPlayback);
            card.addEventListener('mouseleave', stopPlayback);
            card.addEventListener('focusin', startPlayback);
            card.addEventListener('focusout', stopPlayback);
            card.addEventListener('touchstart', startPlayback, { passive: true });
        });
    })();
</script>
</div>
