<div>
@php
    $contactDetails = [
        [
            'icon' => 'bi-geo-alt',
            'title' => 'Visit Us At',
            'lines' => [
                'Jaffna Gold Boutique',
                App\Models\WebsiteContent::getValue('address', '45 Hospital Street, Jaffna City, Sri Lanka'),
            ],
        ],
        [
            'icon' => 'bi-telephone',
            'title' => 'Call Us',
            'lines' => [
                App\Models\WebsiteContent::getValue('phone', '+94 76 191 9650'),
            ],
        ],
        [
            'icon' => 'bi-envelope',
            'title' => 'Email Us',
            'lines' => [
                App\Models\WebsiteContent::getValue('email', 'safrinsrilanaka@gmail.com'),
            ],
        ],
    ];

    $socialLinks = collect([
        ['label' => 'WhatsApp', 'icon' => 'bi-whatsapp', 'url' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', App\Models\WebsiteContent::getValue('phone', '94761919650')), 'class' => 'is-whatsapp'],
        ['label' => 'Facebook', 'icon' => 'bi-facebook', 'url' => 'https://web.facebook.com/jafnagold/?_rdc=1&_rdr#', 'class' => 'is-facebook'],
        ['label' => 'Instagram', 'icon' => 'bi-instagram', 'url' => 'https://www.instagram.com/jaffna.gold/?hl=en', 'class' => 'is-instagram'],
        ['label' => 'YouTube', 'icon' => 'bi-youtube', 'url' => 'https://www.youtube.com/@safrinsrilanka9993', 'class' => 'is-youtube'],
        ['label' => 'TikTok', 'icon' => 'bi-tiktok', 'url' => 'https://www.tiktok.com/@jaffna.gold.237', 'class' => 'is-tiktok'],
    ])->filter(fn ($item) => filled($item['url']))->values();
@endphp

<style>
    .contact-page-header {
        background: #302720;
        width: 100vw;
        max-width: none;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        padding: 3rem 1rem 2.6rem;
    }

    .contact-page-grid {
        display: grid;
        gap: 1.5rem;
    }

    .contact-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .contact-info-card,
    .contact-form-card,
    .contact-map-card,
    .contact-hours-card,
    .contact-social-card {
        background: var(--white);
        border: 1px solid rgba(201, 168, 76, 0.2);
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    }

    .contact-info-card {
        min-height: 168px;
        padding: 1.5rem 1.35rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .contact-info-icon {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(201, 168, 76, 0.15);
        color: var(--accent);
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    .contact-info-card h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.7rem;
        color: var(--navy);
    }

    .contact-info-card p {
        color: var(--body);
        font-size: 0.84rem;
        line-height: 1.5;
        margin: 0;
    }

    .contact-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
        gap: 1.5rem;
        margin-top: 1.5rem;
        align-items: start;
    }

    .contact-form-card {
        padding: 2.2rem clamp(1.5rem, 4vw, 2.5rem);
    }

    .contact-form-card h3 {
        font-size: 1.35rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: var(--navy);
    }

    .contact-map-card,
    .contact-hours-card {
        padding: 1.5rem clamp(1.2rem, 3.2vw, 1.8rem);
    }

    .contact-map-card h3,
    .contact-hours-card h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1.1rem;
        color: var(--navy);
    }

    .contact-map-surface {
        aspect-ratio: 16 / 9;
        background: var(--cream);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(201, 168, 76, 0.16);
    }

    .contact-map-frame {
        width: 100%;
        height: 100%;
        border: 0;
        display: block;
    }

    .contact-map-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.95rem;
        flex-wrap: wrap;
    }

    .contact-map-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--accent);
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .contact-map-link:hover,
    .contact-map-link:focus-visible {
        color: var(--accent-hover);
        text-decoration: underline;
    }

    .contact-hours-card p {
        color: var(--body);
        font-size: 0.88rem;
        line-height: 1.6;
        margin: 0;
    }

    .contact-social-row {
        display: flex;
        gap: 0.85rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }

    .contact-social-link {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(201, 168, 76, 0.12);
        color: var(--navy);
        font-size: 1.1rem;
        transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.25s ease, color 0.25s ease;
        text-decoration: none;
    }

    .contact-social-link:hover {
        transform: translateY(-3px);
    }

    .contact-social-link.is-whatsapp:hover { background: #25D366; color: var(--white); }
    .contact-social-link.is-facebook:hover { background: #1877F2; color: var(--white); }
    .contact-social-link.is-instagram:hover { background: #E1306C; color: var(--white); }
    .contact-social-link.is-youtube:hover { background: #FF0000; color: var(--white); }
    .contact-social-link.is-tiktok:hover { background: #000000; color: var(--white); }

    .contact-divider {
        margin: 2.2rem 0;
        border: 0;
        border-top: 1px solid rgba(201, 168, 76, 0.16);
    }

    @media (max-width: 1024px) {
        .contact-info-grid {
            grid-template-columns: 1fr;
            gap: 0.95rem;
        }

        .contact-main-grid {
            grid-template-columns: 1fr;
            gap: 1.8rem;
        }
    }

    @media (max-width: 768px) {
        .contact-page-header {
            padding: 2.4rem 1rem 2rem;
        }

        .contact-page-grid {
            gap: 1.25rem;
        }

        .contact-main-grid {
            gap: 1.25rem;
            margin-top: 1.25rem;
        }

        .contact-form-card,
        .contact-map-card,
        .contact-hours-card {
            padding: 1.25rem 1rem;
        }

        .contact-info-card {
            min-height: 0;
            padding: 1.15rem 1rem;
        }

        .contact-form-card .form-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .contact-form-card .form-jg {
            gap: 1rem;
        }

        .contact-form-card .form-jg input,
        .contact-form-card .form-jg textarea,
        .contact-form-card .btn-jg {
            width: 100%;
            min-width: 0;
        }

        .contact-form-card .btn-jg {
            min-height: 44px;
        }

        .contact-map-actions {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }

        .contact-map-link {
            min-height: 44px;
            justify-content: center;
            text-align: center;
        }

        .contact-social-row {
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .contact-page-header h1 {
            font-size: 1.9rem;
        }

        .contact-info-card p,
        .contact-hours-card p {
            overflow-wrap: anywhere;
        }

        .contact-map-surface {
            aspect-ratio: 4 / 3;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .contact-social-link {
            transition: none;
        }
    }
</style>

<div class="contact-page-header text-center">
    <h1 style="color: #f9f3ea;">Get in Touch</h1>
    <div class="breadcrumb-jg">
        <span style="color: rgba(249, 243, 234, 0.72);">Our experts are here to assist you with inquiries, custom orders, or boutique visits</span>
    </div>
</div>

<section class="section-jg section-jg-cream" style="padding-top:1.5rem;">
    <div class="container-jg">
        <div class="contact-page-grid">
            <!-- ===== TOP: INFO CARDS ===== -->
            <div class="contact-info-grid">
                @foreach ($contactDetails as $detail)
                    <article class="contact-info-card reveal visible">
                        <span class="contact-info-icon">
                            <i class="bi {{ $detail['icon'] }}" aria-hidden="true"></i>
                        </span>
                        <h3>{{ $detail['title'] }}</h3>
                        @foreach ($detail['lines'] as $line)
                            <p>{{ $line }}</p>
                        @endforeach
                    </article>
                @endforeach
            </div>

            <!-- ===== BOTTOM: FORM & MAP ===== -->
            <div class="contact-main-grid">
                <div class="contact-form-card reveal reveal-delay-1 visible">
                    <h3>Send an Inquiry</h3>

                    <form wire:submit.prevent="sendMessage">
                        <div class="form-jg" style="display: flex; flex-direction: column; gap: 0.85rem;">
                            <div class="form-row" style="display: flex; gap: 1rem;">
                                <div class="form-group" style="flex: 1;">
                                    <label>First Name <span class="text-danger">*</span></label>
                                    <input type="text" placeholder="John" wire:model="first_name" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                    @error('first_name') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group" style="flex: 1;">
                                    <label>Last Name <span class="text-danger">*</span></label>
                                    <input type="text" placeholder="Doe" wire:model="last_name" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                    @error('last_name') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email Address <span class="text-danger">*</span></label>
                                <input type="email" placeholder="your@email.com" wire:model="email" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                @error('email') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>Message / Details <span class="text-danger">*</span></label>
                                <textarea rows="4" placeholder="Tell us about your inquiry..." wire:model="message" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem; font-family: inherit;"></textarea>
                                @error('message') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" class="btn-jg btn-jg-primary" style="border: none; padding: 0.8rem 1.5rem; border-radius: 10px; font-weight: bold; cursor: pointer; align-self: flex-start;">
                                Send Message
                            </button>
                        </div>
                    </form>

                    <hr class="contact-divider">

                    <div class="contact-social-card" style="padding: 0; border: 0; box-shadow: none; background: transparent;">
                        @if ($socialLinks->isNotEmpty())
                            <div class="contact-social-row">
                                @foreach ($socialLinks as $social)
                                    <a
                                        href="{{ $social['url'] }}"
                                        class="contact-social-link {{ $social['class'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="{{ $social['label'] }}"
                                    >
                                        <i class="bi {{ $social['icon'] }}"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="contact-page-grid">
                    <div class="contact-map-card reveal reveal-delay-2 visible">
                        <h3>Find Our Boutique</h3>
                        <div class="contact-map-surface">
                            <iframe
                                class="contact-map-frame"
                                src="https://www.google.com/maps?q=9.6671306,80.0109034&z=17&output=embed"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                aria-label="Map showing Jaffna Gold location"
                            ></iframe>
                        </div>
                        <div class="contact-map-actions">
                            <a
                                href="https://maps.app.goo.gl/j12GHcTSrpdxhRsV9"
                                class="contact-map-link"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Open Jaffna Gold location in Google Maps"
                            >
                                <i class="bi bi-box-arrow-up-right"></i> Open Location
                            </a>
                        </div>
                    </div>

                    <div class="contact-hours-card reveal reveal-delay-3 visible">
                        <h3>Boutique Availability</h3>
                        <p>Monday - Saturday: 9:00 AM - 6:00 PM<br>Sunday: Closed</p>
                        <p style="margin-top: 0.5rem; font-size: 0.8rem;" class="text-muted">Feel free to walk in or call us in advance to schedule a personal customization consultation.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
