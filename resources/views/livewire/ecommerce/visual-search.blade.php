{{--
    Visual search page.

    Reuses the storefront's .product-card / .product-grid classes from the webapp
    layout, so results are visually identical to shop listings. Only the
    upload panel and the similarity badge are new.
--}}
<div class="vs-page-shell">
    <div class="page-header page-header-hero">
        <h1>Search by Photo</h1>
        <div class="breadcrumb-jg">
            <span>Saw a design you love on Facebook, Instagram, WhatsApp or TikTok? Upload the photo and we will find it.</span>
        </div>
    </div>

    <section class="section-jg">
        <div class="container-jg">

            {{-- ===== UPLOAD PANEL ===== --}}
            <div class="vs-upload-card" x-data="visualSearchUpload()">
                <div class="vs-upload-grid">
                    <div
                        class="vs-dropzone"
                        :class="{ 'is-dragging': dragging }"
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave.prevent="dragging = false"
                        x-on:drop.prevent="handleDrop($event)"
                        x-on:click="$refs.fileInput.click()"
                        role="button"
                        tabindex="0"
                        x-on:keydown.enter="$refs.fileInput.click()"
                        aria-label="Choose or drop a photo to search with"
                    >
                        {{-- wire:model triggers updatedPhoto(), which runs the search immediately. --}}
                        <input
                            type="file"
                            x-ref="fileInput"
                            wire:model="photo"
                            accept="image/jpeg,image/png,image/webp,image/gif,image/bmp"
                            class="vs-file-input"
                            aria-hidden="true"
                            tabindex="-1"
                        >

                        <div class="vs-dropzone-inner" wire:loading.remove wire:target="photo">
                            @if ($photo && ! $errors->has('photo'))
                                <img src="{{ $photo->temporaryUrl() }}" alt="Your uploaded photo" class="vs-preview-img">
                                <span class="vs-dropzone-hint">Tap to choose a different photo</span>
                            @else
                                <i class="bi bi-camera vs-dropzone-icon"></i>
                                <strong class="vs-dropzone-title">Drop a photo here, or tap to browse</strong>
                                <span class="vs-dropzone-hint">
                                    JPG, PNG or WEBP &middot; up to {{ round(((int) config('image_search.upload.max_kilobytes', 5120)) / 1024, 1) }} MB
                                </span>
                            @endif
                        </div>

                        {{-- Upload progress: uploads are the slow part on mobile data. --}}
                        <div class="vs-dropzone-inner vs-uploading" wire:loading.flex wire:target="photo">
                            <div class="vs-spinner" aria-hidden="true"></div>
                            <strong class="vs-dropzone-title">Uploading your photo...</strong>
                            <div class="vs-progress" x-show="progress > 0">
                                <div class="vs-progress-bar" :style="`width: ${progress}%`"></div>
                            </div>
                        </div>
                    </div>

                    <div class="vs-upload-side">
                        <h2 class="vs-side-title">How it works</h2>
                        <ol class="vs-steps">
                            <li><span>1</span> Upload any jewelry photo &mdash; a screenshot works fine.</li>
                            <li><span>2</span> Our AI reads the design, not the file name.</li>
                            <li><span>3</span> We show you the closest matching pieces in stock.</li>
                        </ol>

                        <div class="vs-field">
                            <label for="vs-category" class="vs-label">Narrow by category (optional)</label>
                            <select id="vs-category" wire:model.live="categoryId" class="vs-select">
                                <option value="">All categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($hasSearched || $photo)
                            <button type="button" class="vs-reset-btn" wire:click="clearSearch">
                                <i class="bi bi-arrow-counterclockwise"></i> Start over
                            </button>
                        @endif
                    </div>
                </div>

                @error('photo')
                    <div class="vs-alert vs-alert-error" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                @if ($errorMessage)
                    <div class="vs-alert vs-alert-error" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ $errorMessage }}</span>
                    </div>
                @endif
            </div>

            {{-- ===== SEARCHING STATE ===== --}}
            <div class="vs-searching" wire:loading.flex wire:target="photo, search, categoryId">
                <div class="vs-spinner vs-spinner-lg" aria-hidden="true"></div>
                <p>Matching your photo against our collection...</p>
            </div>

            {{-- ===== RESULTS ===== --}}
            <div wire:loading.remove wire:target="photo, search, categoryId">
                @if ($hasSearched && ! $errorMessage)
                    <div class="vs-results-head">
                        <div>
                            <h2 class="vs-results-title">
                                {{ count($results) > 0 ? 'Closest matches' : 'No close matches yet' }}
                            </h2>
                            <p class="vs-results-meta">
                                @if (count($results) > 0)
                                    Showing {{ count($results) }} {{ Str::plural('piece', count($results)) }}, most similar first.
                                @else
                                    We could not find a piece close enough to that photo.
                                @endif

                                @if ($detectedType)
                                    <span class="vs-chip">
                                        <i class="bi bi-magic"></i>
                                        Detected: {{ Str::headline(str_replace('_', ' ', $detectedType)) }}
                                        @if ($detectedConfidence)
                                            ({{ round($detectedConfidence * 100) }}% sure)
                                        @endif
                                    </span>
                                @endif
                            </p>
                        </div>

                        @if ($tookMs !== null)
                            <span class="vs-timing" title="Time spent inside the AI search service">
                                <i class="bi bi-lightning-charge-fill"></i> {{ number_format($tookMs, 0) }} ms
                            </span>
                        @endif
                    </div>

                    @if (count($results) > 0)
                        <div class="product-grid shop-product-grid">
                            @foreach ($results as $index => $product)
                                <div
                                    class="product-card reveal visible"
                                    wire:key="vs-product-{{ $product['id'] }}"
                                    onclick="if (event.target.closest('button, a')) return; window.location='{{ route('product-detail', ['id' => $product['id']]) }}'"
                                >
                                    <div class="img-wrap">
                                        <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" loading="lazy">

                                        {{-- The match strength is the whole reason a customer came here. --}}
                                        <span class="vs-match-badge" title="Visual similarity to your photo">
                                            {{ $product['similarity'] }}% match
                                        </span>

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
                                            <button
                                                class="card-tool-btn action-icon-btn wishlist-btn{{ $product['wishlist'] ? ' active' : '' }}"
                                                type="button"
                                                wire:click.prevent="toggleWishlist('{{ $product['id'] }}')"
                                                aria-label="Toggle wishlist"
                                            >
                                                <i class="bi {{ $product['wishlist'] ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                            </button>
                                            <button
                                                class="card-tool-btn action-icon-btn cart-btn{{ $product['cart'] ? ' active' : '' }}"
                                                type="button"
                                                wire:click.prevent="toggleCart('{{ $product['id'] }}')"
                                                @if(!$product['in_stock']) disabled style="opacity:0.5; cursor:not-allowed;" @endif
                                                aria-label="Add to cart"
                                            >
                                                <i class="bi {{ $product['cart'] ? 'bi-bag-fill' : 'bi-bag' }}"></i>
                                            </button>
                                            <button
                                                class="btn-jg btn-jg-primary btn-jg-sm"
                                                type="button"
                                                wire:click.prevent="buyNow('{{ $product['id'] }}')"
                                                @if(!$product['in_stock']) disabled style="opacity:0.5;" @endif
                                            >Buy Now</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <p class="vs-footnote">
                            Not quite what you had in mind? Try a photo where the piece fills more of the frame,
                            or <a href="{{ route('shop') }}">browse the full collection</a>.
                        </p>
                    @else
                        <div class="empty-state vs-empty">
                            <i class="bi bi-image"></i>
                            <h3>Nothing close enough</h3>
                            <p>
                                Try a clearer photo where the jewelry fills most of the frame, or
                                <a href="{{ route('shop') }}">browse the full collection</a>.
                                Our team can also craft a custom piece from your photo &mdash;
                                <a href="{{ route('contact') }}">get in touch</a>.
                            </p>
                        </div>
                    @endif
                @elseif (! $hasSearched)
                    <div class="vs-intro-note">
                        <i class="bi bi-shield-check"></i>
                        <span>Your photo is used only to run this search. We do not add it to your account or share it.</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <style>
        /* ===== VISUAL SEARCH ===== */
        .vs-page-shell { padding-bottom: 3rem; }

        .vs-upload-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 1.75rem;
            margin-bottom: 2.25rem;
            box-shadow: 0 12px 40px rgba(39, 33, 27, 0.06);
        }

        .vs-upload-grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 2rem;
            align-items: stretch;
        }

        .vs-dropzone {
            position: relative;
            border: 2px dashed var(--border);
            border-radius: 4px;
            background: var(--cream-soft);
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color 0.25s ease, background 0.25s ease;
            padding: 1.5rem;
            overflow: hidden;
        }

        .vs-dropzone:hover,
        .vs-dropzone:focus-visible,
        .vs-dropzone.is-dragging {
            border-color: var(--accent);
            background: #fffdf7;
            outline: none;
        }

        /* Visually hidden but still reachable: the dropzone proxies clicks to it. */
        .vs-file-input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .vs-dropzone-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0.6rem;
            width: 100%;
        }

        .vs-uploading { display: none; }

        .vs-dropzone-icon {
            font-size: 2.6rem;
            color: var(--accent);
        }

        .vs-dropzone-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.15rem;
            color: var(--navy);
            font-weight: 600;
        }

        .vs-dropzone-hint {
            font-size: 0.82rem;
            color: var(--body);
            letter-spacing: 0.04em;
        }

        .vs-preview-img {
            max-width: 100%;
            max-height: 230px;
            object-fit: contain;
            border-radius: 3px;
            border: 1px solid var(--border);
            background: var(--white);
        }

        .vs-progress {
            width: 70%;
            height: 4px;
            background: var(--border);
            border-radius: 999px;
            overflow: hidden;
        }

        .vs-progress-bar {
            height: 100%;
            background: var(--accent);
            transition: width 0.2s ease;
        }

        .vs-upload-side {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        .vs-side-title {
            font-size: 1.05rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .vs-steps {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
        }

        .vs-steps li {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            font-size: 0.9rem;
            color: var(--body);
            line-height: 1.5;
        }

        .vs-steps li span {
            flex: 0 0 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--accent);
            color: var(--white);
            font-size: 0.74rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.1rem;
        }

        .vs-field { margin-top: auto; }

        .vs-label {
            display: block;
            font-size: 0.72rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--navy);
            font-weight: 700;
            margin-bottom: 0.4rem;
        }

        .vs-select {
            width: 100%;
            padding: 0.7rem 0.85rem;
            border: 1px solid var(--border);
            border-radius: 3px;
            background: var(--white);
            color: var(--navy);
            font-size: 0.9rem;
        }

        .vs-select:focus {
            outline: none;
            border-color: var(--accent);
        }

        .vs-reset-btn {
            align-self: flex-start;
            background: none;
            border: 1px solid var(--border);
            border-radius: 3px;
            padding: 0.55rem 1rem;
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--body);
            cursor: pointer;
            transition: border-color 0.2s ease, color 0.2s ease;
        }

        .vs-reset-btn:hover {
            border-color: var(--accent);
            color: var(--navy);
        }

        .vs-alert {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-top: 1.25rem;
            padding: 0.8rem 1rem;
            border-radius: 3px;
            font-size: 0.88rem;
        }

        .vs-alert-error {
            background: #fdf2f2;
            border: 1px solid #f0c9c9;
            color: #9b2c2c;
        }

        .vs-searching {
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 3rem 0;
            color: var(--body);
        }

        .vs-spinner {
            width: 26px;
            height: 26px;
            border: 3px solid var(--border);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: vsSpin 0.8s linear infinite;
        }

        .vs-spinner-lg { width: 40px; height: 40px; border-width: 4px; }

        @keyframes vsSpin { to { transform: rotate(360deg); } }

        .vs-results-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .vs-results-title { font-size: 1.5rem; }

        .vs-results-meta {
            margin-top: 0.4rem;
            font-size: 0.88rem;
            color: var(--body);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .vs-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #fdf8ea;
            border: 1px solid rgba(204, 164, 59, 0.45);
            color: #8a6d1c;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .vs-timing {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.76rem;
            color: var(--tan);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .vs-match-badge {
            position: absolute;
            top: 0.65rem;
            left: 0.65rem;
            z-index: 3;
            background: rgba(39, 33, 27, 0.88);
            color: #f3e0ad;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            padding: 0.28rem 0.55rem;
            border-radius: 999px;
            border: 1px solid rgba(204, 164, 59, 0.5);
        }

        .vs-footnote,
        .vs-intro-note {
            margin-top: 2rem;
            font-size: 0.88rem;
            color: var(--body);
            text-align: center;
        }

        .vs-intro-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1.25rem;
            background: var(--cream-soft);
            border: 1px solid var(--border);
            border-radius: 3px;
        }

        .vs-footnote a,
        .vs-empty a { color: var(--accent-hover); text-decoration: underline; }

        .vs-empty { padding: 3rem 1.5rem; }

        @media (max-width: 860px) {
            .vs-upload-grid { grid-template-columns: 1fr; gap: 1.5rem; }
            .vs-upload-card { padding: 1.15rem; }
            .vs-dropzone { min-height: 240px; }
            .vs-results-head { align-items: flex-start; }
        }
    </style>

    <script>
        function visualSearchUpload() {
            return {
                dragging: false,
                progress: 0,

                init() {
                    // Livewire emits real upload progress; surfacing it matters most
                    // on mobile data, where a 5 MB photo is not instant.
                    this.$el.addEventListener('livewire-upload-start', () => { this.progress = 1; });
                    this.$el.addEventListener('livewire-upload-progress', (event) => {
                        this.progress = event.detail.progress;
                    });
                    this.$el.addEventListener('livewire-upload-finish', () => { this.progress = 0; });
                    this.$el.addEventListener('livewire-upload-error', () => { this.progress = 0; });
                },

                handleDrop(event) {
                    this.dragging = false;

                    const files = event.dataTransfer?.files;
                    if (!files || files.length === 0) {
                        return;
                    }

                    // Hand the dropped file to the same input wire:model watches,
                    // so the drop path and the browse path behave identically.
                    const input = this.$refs.fileInput;
                    const transfer = new DataTransfer();
                    transfer.items.add(files[0]);
                    input.files = transfer.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                },
            };
        }
    </script>
</div>
