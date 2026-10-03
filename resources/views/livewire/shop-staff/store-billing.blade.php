<div class="pos-billing-terminal">
    <!-- Load TailWind & Premium Fonts -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <style type="text/tailwindcss">
        @layer base {
            :root {
                /* Accent palette - tweak these for different color themes */
                --accent-50: #f0f2ff;
                --accent-100: #e0e4ff;
                --accent-300: #4361ee;
                --accent-500: #161b97; /* logo blue accent */
                --accent-700: #12167d;
                --bg-pos: #fcfcfc;
                --muted: #64748b;
            }

            html, body { background: var(--bg-pos); font-family: 'Inter', sans-serif; color: #0f172a; }

            /* Scrollbar */
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 12px; }

            /* Material icons tuning */
            .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24; }

            /* Header */
            .pos-billing-terminal header { background: linear-gradient(90deg, var(--accent-50), #ffffff); border-bottom: 1px solid rgba(0,0,0,0.04); }
            .pos-billing-terminal header .bg-blue-gradient { background: linear-gradient(135deg,var(--accent-500),var(--accent-700)); box-shadow: 0 6px 18px rgba(22,27,151,0.12); }

            /* Accent buttons */
            .btn-accent { background: linear-gradient(90deg,var(--accent-500),var(--accent-700)); color: white; }
            .btn-accent:hover { filter: brightness(.95); }

            /* Product cards */
            .product-card { @apply bg-white rounded-xl border border-slate-100 overflow-hidden shadow-md; }
            .product-card .card-body { @apply p-4; }
            /* Ensure grid product images fill the display area uniformly */
            .product-card img, .product-grid-img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
            /* Cart thumbnails: fixed square size */
            .cart-thumb { width: 32px; height: 32px; object-fit: cover; object-position: center; border-radius: 6px; }
            .product-card .price { color: var(--accent-700); font-weight: 800; }

            /* 'In stock' badge */
            .badge-instock { background: linear-gradient(90deg,#10b981,#06b6d4); color: white; font-weight: 700; padding: .25rem .5rem; border-radius: .375rem; font-size: .625rem; }

            /* Search input */
            .search-input { @apply w-full rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm; }

            /* Cart area */
            .cart-empty { color: var(--muted); }
            .cart-row { @apply transition-shadow; }
            .cart-row:hover { box-shadow: 0 6px 18px rgba(15,23,42,0.03); }

            /* Make the small remove button always visible (overrides hover-hidden) */
            .group\/thumb > button { opacity: 1 !important; transform: translate(0,0) !important; }

            /* Make small control buttons rounder and clearer */
            .qty-btn { @apply w-6 h-6 rounded-full text-[10px] font-bold bg-slate-100 border border-slate-200 flex items-center justify-center; }

            /* Product grid plus button */
            .add-btn { @apply w-9 h-9 rounded-lg bg-white border border-slate-200 shadow-sm flex items-center justify-center; }
            .add-btn:hover { transform: translateY(-3px); transition: transform .15s ease; }

            /* Receipt print styling tweak for in-browser modal */
            .receipt-container { border-radius: 12px; border: 1px solid rgba(15,23,42,0.04); }

            /* Footer action area */
            .pos-footer .btn { @apply rounded-lg px-6 py-3 font-black; }
            .pos-footer .btn-primary { background: linear-gradient(90deg,var(--accent-500),var(--accent-700)); color: white; }
        }
    </style>

    <div class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden text-sm">

        <!-- Header Section -->
        <header
            class="bg-white border-b border-slate-200 px-3 py-0 flex items-center justify-between shadow-sm shrink-0">
            <div class="flex items-center gap-2">
                <div class="flex items-center">
                    jaffna gold pos system
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 border border-amber-600 transition-all text-xs font-black shadow-sm tracking-tight"
                    wire:click="openPriceHelperModal" title="Quick Price Range Reference">
                    <span class="material-symbols-outlined text-base">price_check</span>
                    PRICE HELPER
                </button>
                <button
                    class="flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 transition-colors text-xs font-bold text-slate-600 border border-slate-200"
                    wire:click="goToDashboard">
                    <span class="material-symbols-outlined text-base">dashboard</span>
                    DASHBOARD
                </button>
                <div class="bg-slate-100 px-3 py-1 rounded border border-slate-200">
                    <span class="font-mono text-base font-bold text-[#161b97] tracking-widest"
                        id="posClock">00:00:00</span>
                </div>
                <button
                    class="flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 transition-colors text-xs font-bold text-slate-600 border border-slate-200"
                    wire:click="viewCloseRegisterReport">
                    <span class="material-symbols-outlined text-base">analytics</span>
                    POS REPORT
                </button>
            </div>
        </header>
        <!-- Main Content Area -->
        <main class="flex flex-1 overflow-hidden p-3 gap-3">

            <!-- LEFT SECTION: Search & Cart (50%) -->
            <aside class="w-1/2 flex flex-col bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                {{-- Search Bar with Alpine Keyboard Navigation --}}
                <div class="p-3 border-b border-slate-100 bg-slate-50/50" x-data="{ highlightIndex: -1 }"
                    x-on:product-added-to-cart.window="
                         highlightIndex = -1;
                         $nextTick(() => {
                             if ($refs.searchInput) { $refs.searchInput.focus(); $refs.searchInput.select(); }
                         })
                     " x-init="$nextTick(() => { if ($refs.searchInput) $refs.searchInput.focus(); })">
                    <div class="relative">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                        <input
                            class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-md focus:ring-2 focus:ring-[#161b97]/20 focus:border-[#161b97] outline-none text-sm transition-all"
                            x-ref="searchInput" wire:model="search"
                            wire:input.debounce.300ms="handleBarcodeOrSearch($event.target.value)"
                            placeholder="Scan barcode or type product name / code..." type="text"
                            x-on:keydown.arrow-down.prevent="
                                let items = document.querySelectorAll('[data-search-result]');
                                if (items.length > 0) {
                                    highlightIndex = (highlightIndex + 1) % items.length;
                                    items[highlightIndex]?.scrollIntoView({ block: 'nearest' });
                                }
                            " x-on:keydown.arrow-up.prevent="
                                let items = document.querySelectorAll('[data-search-result]');
                                if (items.length > 0) {
                                    highlightIndex = highlightIndex <= 0 ? items.length - 1 : highlightIndex - 1;
                                    items[highlightIndex]?.scrollIntoView({ block: 'nearest' });
                                }
                            " x-on:keydown.enter.prevent="
                                if (highlightIndex >= 0) {
                                    let items = document.querySelectorAll('[data-search-result]');
                                    if (items[highlightIndex]) items[highlightIndex].click();
                                    highlightIndex = -1;
                                } else {
                                    let val = $el.value;
                                    $el.value = '';
                                    $wire.handleBarcodeOrSearch(val);
                                }
                            " x-on:keydown.escape.prevent="
                                highlightIndex = -1;
                                $wire.set('search', '');
                            " x-on:input="highlightIndex = -1">

                        <!-- Search Dropdown -->
                        @if($search && count($searchResults) > 0)
                        <div
                            class="absolute w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-2xl z-50 max-h-96 overflow-y-auto custom-scrollbar">
                            @foreach($searchResults as $sIndex => $res)
                            <div class="flex items-center gap-3 p-3 cursor-pointer border-b border-slate-50 last:border-0 transition-colors"
                                data-search-result data-search-index="{{ $sIndex }}"
                                :class="highlightIndex === {{ $sIndex }} ? 'bg-blue-50 border-l-2 !border-l-[#161b97]' : 'hover:bg-slate-50'"
                                wire:click="addToCart({{ json_encode($res) }})"
                                x-on:mouseenter="highlightIndex = {{ $sIndex }}">
                                <img src="{{ $this->getImageUrl($res['image']) }}"
                                    onerror="this.onerror=null;this.src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSrn_80I-lMAa0pVBNmFmQ7VI6l4rr74JW-eQ&s';"
                                    class="w-10 h-10 rounded object-cover border border-slate-100">
                                <div class="flex-1">
                                    <h5 class="text-xs font-bold text-slate-800">{{ $res['name'] }}</h5>
                                    <p class="text-[10px] text-slate-500 font-mono">
                                        <span class="font-bold text-slate-700">{{ $res['code'] }}</span>
                                        @if(!empty($res['barcode']))
                                            | <span class="text-slate-500">Barcode: <span class="text-slate-800 font-bold">{{ $res['barcode'] }}</span></span>
                                        @endif
                                        | <span class="font-bold {{ $res['stock'] <= 5 ? 'text-amber-500' : 'text-green-600' }}">Stock: {{ $res['stock'] }}</span>
                                        @if(($res['pending'] ?? 0) > 0)
                                        | <span class="font-bold text-orange-500">Pending: {{ $res['pending'] }}</span>
                                        @endif
                                    </p>
                                </div>
                                <span class="text-xs font-black text-[#161b97]">Rs.
                                    {{ number_format($res['price'], 2) }}</span>
                            </div>
                            @endforeach
                        </div>
                        @elseif($search && strlen(trim($search)) >= 2 && count($searchResults) === 0)
                        <div class="absolute w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-xl z-50 p-4 text-center">
                            <span class="material-symbols-outlined text-3xl text-slate-300 mx-auto block mb-1">search_off</span>
                            <p class="text-xs font-bold text-slate-600">No products found matching "{{ $search }}"</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Check the barcode or product code and try again</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Cart Header with Custom Product Button --}}
                <div class="flex items-center justify-between px-3 py-1.5 border-b border-slate-200 bg-white">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#161b97] text-base">shopping_cart</span>
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Cart Items</span>
                        @if(count($cart) > 0)
                        <span class="bg-[#161b97] text-white text-[8px] font-black px-2 py-0.5 rounded-full">{{ count($cart) }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" class="flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-900 border border-amber-600 rounded-lg transition-all text-[9px] font-black uppercase tracking-tight shadow-sm"
                            wire:click="openServiceModal" title="Customer Gold Plating, Cleaning & Services">
                            <span class="material-symbols-outlined text-sm">auto_fix_high</span>
                            Services / Plating
                        </button>
                        <button class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 hover:border-amber-300 transition-all text-[9px] font-black text-amber-700 uppercase tracking-tight shadow-sm"
                            wire:click="addCustomProduct">
                            <span class="material-symbols-outlined text-sm">add_circle</span>
                            Custom Product
                        </button>
                    </div>
                </div>

                {{-- Cart Table --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-white z-10 border-b border-slate-200">
                            <tr class="text-[9px] uppercase font-black text-slate-400 tracking-wider bg-slate-50">
                                <th class="px-3 py-2 text-left">Item Details</th>
                                <th class="px-2 py-2 text-center">Qty</th>
                                <th class="px-2 py-2 text-right">Price</th>
                                <th class="px-2 py-2 text-center">Discount</th>
                                <th class="px-3 py-2 text-right">Subtotal</th>
                                <th class="px-1 py-2 w-8">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($cart as $index => $item)
                            @php $cartKey = $item['key'] ?? $index; @endphp
                            <tr class="group hover:bg-amber-50/30 transition-colors border-b border-slate-100" wire:key="cart-{{ $cartKey }}" data-cart-key="{{ $cartKey }}" data-price="{{ $item['price'] }}" data-qty="{{ $item['quantity'] }}">
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        @if(!empty($item['is_custom']))
                                        {{-- Custom Product: Editable Name & Code --}}
                                        <div class="w-8 h-8 rounded-md border border-amber-300 bg-amber-50 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-amber-500 text-sm">edit_note</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <input type="text"
                                                class="w-full text-xs font-bold text-slate-700 bg-amber-50 border border-amber-200 rounded px-2 py-1 mb-0.5 focus:outline-none focus:border-[#161b97] transition-colors"
                                                value="{{ $item['name'] }}"
                                                wire:change="updateCustomName('{{ $cartKey }}', $event.target.value)"
                                                placeholder="Enter product name...">
                                            <input type="text"
                                                class="w-full text-[9px] text-slate-400 font-mono bg-slate-50 border border-slate-200 rounded px-2 py-0.5 focus:outline-none focus:border-[#161b97] transition-colors"
                                                value="{{ $item['code'] ?? '' }}"
                                                wire:change="updateCustomCode('{{ $cartKey }}', $event.target.value)"
                                                placeholder="Product code...">
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <span class="inline-flex items-center gap-0.5 bg-amber-100 text-amber-700 text-[7px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider">
                                                    <span class="material-symbols-outlined text-[9px]">edit_square</span>
                                                    CUSTOM
                                                </span>
                                                <label class="inline-flex items-center gap-1 cursor-pointer select-none px-1.5 py-0.5 rounded border transition-all text-[9px] font-bold {{ !empty($item['has_warranty']) ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}" title="Toggle 6-Month Warranty">
                                                    <input type="checkbox"
                                                        wire:change="updateWarranty('{{ $cartKey }}', $event.target.checked)"
                                                        @checked(!empty($item['has_warranty']))
                                                        class="rounded border-slate-300 text-emerald-600 focus:ring-0 focus:ring-offset-0 w-3.5 h-3.5 cursor-pointer accent-emerald-600">
                                                    <span class="flex items-center gap-0.5">
                                                        <span class="material-symbols-outlined text-[11px] leading-none">{{ !empty($item['has_warranty']) ? 'verified' : 'shield' }}</span>
                                                        {{ !empty($item['has_warranty']) ? '6M Warranty' : 'Warranty' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        @else
                                        {{-- Regular Product --}}
                                        <img src="{{ $item['image_url'] ?? $this->getImageUrl($item['image']) }}"
                                            onerror="this.onerror=null;this.src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSrn_80I-lMAa0pVBNmFmQ7VI6l4rr74JW-eQ&s';"
                                            class="w-8 h-8 rounded-md border border-slate-200 object-cover shrink-0">
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-xs font-bold text-slate-700 truncate max-w-[130px]" title="{{ $item['name'] }}">{{ $item['name'] }}</h4>
                                            <p class="text-[9px] text-slate-400 font-mono">{{ $item['code'] ?? '' }}</p>
                                            <div class="mt-1">
                                                <label class="inline-flex items-center gap-1 cursor-pointer select-none px-1.5 py-0.5 rounded border transition-all text-[9px] font-bold {{ !empty($item['has_warranty']) ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}" title="Toggle 6-Month Warranty">
                                                    <input type="checkbox"
                                                        wire:change="updateWarranty('{{ $cartKey }}', $event.target.checked)"
                                                        @checked(!empty($item['has_warranty']))
                                                        class="rounded border-slate-300 text-emerald-600 focus:ring-0 focus:ring-offset-0 w-3.5 h-3.5 cursor-pointer accent-emerald-600">
                                                    <span class="flex items-center gap-0.5">
                                                        <span class="material-symbols-outlined text-[11px] leading-none">{{ !empty($item['has_warranty']) ? 'verified' : 'shield' }}</span>
                                                        {{ !empty($item['has_warranty']) ? '6M Warranty' : 'Warranty' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 py-2">
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="flex items-center">
                                            <button class="w-7 h-7 flex items-center justify-center rounded-l border border-r-0 border-slate-200 bg-slate-100 hover:bg-slate-200 text-sm font-bold text-slate-600 transition-all" wire:click="decrementQuantity('{{ $cartKey }}')">−</button>
                                            <input type="number" min="1" step="1" max="{{ $item['stock'] ?? 0 }}" value="{{ $item['quantity'] }}"
                                                id="cart-qty-{{ $index }}"
                                                wire:change="updateQuantity('{{ $cartKey }}', $event.target.value)"
                                                wire:key="qty-{{ $cartKey }}"
                                                @keydown.enter.prevent="
                                                        $wire.updateQuantity('{{ $cartKey }}', $event.target.value);
                                                        $nextTick(() => {
                                                             const searchInput = document.querySelector('[x-ref=searchInput]');
                                                             if (searchInput) { 
                                                                 searchInput.focus(); 
                                                                 searchInput.select();
                                                             }
                                                         });
                                                     "
                                                class="w-16 h-7 text-center text-xs font-black bg-white border-y border-slate-200 focus:outline-none focus:border-[#161b97] transition-colors [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                                            <button class="w-7 h-7 flex items-center justify-center rounded-r border border-l-0 border-slate-200 bg-slate-100 hover:bg-slate-200 text-sm font-bold text-slate-600 transition-all" wire:click="incrementQuantity('{{ $cartKey }}')">+</button>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-2">
                                    <div class="flex items-center justify-end gap-1">
                                        <span class="text-[9px] font-bold text-slate-400">Rs.</span>
                                        <input type="number" step="0.01" min="0" value="{{ $item['price'] }}"
                                            id="cart-price-{{ $index }}"
                                            wire:change="updatePrice('{{ $cartKey }}', $event.target.value)"
                                            wire:key="price-{{ $cartKey }}"
                                            x-on:keydown.enter.prevent="$wire.updatePrice('{{ $cartKey }}', $event.target.value)"
                                            class="w-24 h-7 text-right text-xs font-bold bg-slate-50 border border-slate-200 rounded px-2 focus:outline-none focus:border-[#161b97] transition-colors" />
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @php
                                    $discountPercent = $item['discount_percentage'] ?? 0;
                                    $discountNumericValue = $discountPercent > 0 ? rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.') : '';
                                    @endphp
                                    <div class="flex items-center justify-center" wire:key="disc-wrap-{{ $cartKey }}">
                                        <div class="flex items-center h-7 rounded overflow-hidden border {{ $discountNumericValue > 0 ? 'border-emerald-300' : 'border-slate-200' }}">
                                            <input type="number"
                                                step="any"
                                                min="0"
                                                max="100"
                                                id="cart-disc-{{ $cartKey }}"
                                                wire:key="disc-input-{{ $cartKey }}"
                                                value="{{ $discountNumericValue }}"
                                                oninput="recalcInstantDiscount(this, '{{ $cartKey }}')"
                                                wire:input.debounce.250ms="updateDiscount('{{ $cartKey }}', $event.target.value)"
                                                wire:change="updateDiscount('{{ $cartKey }}', $event.target.value)"
                                                x-on:keydown.enter.prevent="$wire.updateDiscount('{{ $cartKey }}', $event.target.value)"
                                                placeholder="0"
                                                class="cart-discount-input w-14 h-full px-2 text-[10px] font-bold text-center focus:outline-none transition-all border-0 {{ $discountNumericValue > 0 ? 'text-emerald-600 bg-emerald-50' : 'text-slate-500 bg-slate-50' }}" />
                                            <div class="h-full px-1.5 text-[8px] font-black border-l whitespace-nowrap bg-emerald-500 text-white border-emerald-400 flex items-center">
                                                %
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <p class="text-sm font-black text-slate-800 row-total-{{ $cartKey }}">Rs. {{ number_format($item['total'], 0) }}</p>
                                </td>
                                <td class="px-1 py-2 w-8 text-center">
                                    <button class="w-6 h-6 flex items-center justify-center bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors mx-auto" wire:click="removeFromCart('{{ $cartKey }}')" title="Remove">
                                        <span class="material-symbols-outlined text-[11px] font-black leading-none">close</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-24 text-center">
                                    <div class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                        <span class="material-symbols-outlined text-4xl text-slate-200">shopping_cart_off</span>
                                    </div>
                                    <p class="text-slate-400 font-black uppercase tracking-widest text-[10px]">Your cart is empty</p>
                                    <p class="text-slate-300 text-[9px] mt-1">Scan or search products to begin</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Left Footer: Totals --}}
                <div class="p-4 bg-slate-50 border-t border-slate-200 bg-gradient-to-b from-slate-50/50 to-white">
                    <div class="grid grid-cols-2 gap-4 items-end mb-4">
                        <div class="space-y-1.5">
                            @php
                            $originalSubtotal = collect($cart)->sum(function ($item) {
                            return ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                            });
                            $unitDiscountRs = collect($cart)->sum(function ($item) {
                            return ($item['discount'] ?? 0) * ($item['quantity'] ?? 0);
                            });
                            $globalDiscountAmount = $this->additionalDiscountAmount ?? 0;
                            $totalDiscountRs = max(0, $unitDiscountRs + $globalDiscountAmount);
                            $totalDiscountPercent = $originalSubtotal > 0 ? (($totalDiscountRs / $originalSubtotal) * 100) : 0;
                            @endphp
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-semibold">Subtotal (Before Discount)</span>
                                <span class="font-bold text-slate-700">Rs. {{ number_format($originalSubtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-semibold">Unit Discount</span>
                                <span class="font-bold text-red-500 pos-unit-discount">- Rs. {{ number_format($unitDiscountRs, 2) }}</span>
                            </div>

                            @if($globalDiscountAmount > 0)
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-semibold">Global Discount</span>
                                <span class="font-bold text-amber-600">
                                    - Rs. {{ number_format($globalDiscountAmount, 2) }}
                                    @if(($additionalDiscountType ?? 'fixed') === 'percentage' && ($additionalDiscount ?? 0) > 0)
                                    <span class="text-slate-400 font-semibold">({{ number_format($additionalDiscount, 2) }}%)</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-semibold">Total Discount</span>
                                <span class="font-bold text-red-500">- Rs. {{ number_format($totalDiscountRs, 2) }} <span class="text-slate-400 font-semibold">({{ number_format($totalDiscountPercent, 2) }}%)</span></span>
                            </div>
                            @endif
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-semibold">Tax (0%)</span>
                                <span class="font-bold text-slate-700">Rs. 0.00</span>
                            </div>
                        </div>
                        {{-- Direct Global Discount Card (Replaces Modal Button - Same as Quotation Billing) --}}
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm text-[#161b97]">sell</span>
                                    Global Discount
                                </span>
                                <div class="inline-flex p-0.5 bg-white border border-slate-200 rounded-md text-[10px] font-black">
                                    <button type="button"
                                        wire:click="setDiscountType('fixed')"
                                        class="px-2 py-0.5 rounded transition-all {{ $additionalDiscountType === 'fixed' ? 'bg-[#161b97] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                        Rs.
                                    </button>
                                    <button type="button"
                                        wire:click="setDiscountType('percentage')"
                                        class="px-2 py-0.5 rounded transition-all {{ $additionalDiscountType === 'percentage' ? 'bg-[#161b97] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                        %
                                    </button>
                                </div>
                            </div>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-xs font-bold text-slate-400 pointer-events-none">
                                    {{ $additionalDiscountType === 'percentage' ? '%' : 'Rs.' }}
                                </span>
                                <input type="number"
                                    step="any"
                                    min="0"
                                    {{ $additionalDiscountType === 'percentage' ? 'max=100' : '' }}
                                    wire:model.live.debounce.250ms="additionalDiscount"
                                    placeholder="0"
                                    class="w-full pl-9 pr-7 py-2 bg-white border border-slate-200 rounded-lg text-sm font-black text-slate-800 text-right focus:border-[#161b97] outline-none transition-all" />
                                @if((float) ($additionalDiscount ?: 0) > 0)
                                <button type="button"
                                    wire:click="clearAdditionalDiscount"
                                    class="absolute right-2 text-slate-300 hover:text-red-500 transition-colors p-0.5"
                                    title="Clear discount">
                                    <span class="material-symbols-outlined text-xs">close</span>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 items-center border-t border-slate-200 pt-4">
                        <div class="flex justify-between items-baseline">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total</span>
                            <span class="text-3xl font-black text-[#161b97] tracking-tighter pos-grand-total">Rs. {{ number_format($grandTotal, 2) }}</span>
                        </div>
                        <button class="w-full bg-[#161b97] hover:bg-blue-800 text-white font-black py-3 rounded-lg flex items-center justify-center gap-2 shadow-xl shadow-blue-500/20 transition-all text-xs uppercase tracking-widest disabled:opacity-40 disabled:grayscale disabled:cursor-not-allowed group"
                            wire:click="validateAndCreateSale" {{ count($cart) == 0 ? 'disabled' : '' }}>
                            <span class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform">payments</span>
                            Complete Sale
                        </button>
                    </div>
                </div>
            </aside>

            <!-- RIGHT SECTION: Selections & Product Grid (50%) -->
            <section class="w-1/2 flex flex-col gap-3 overflow-hidden">
                {{-- Selection Box (Customer/Price) --}}
                <div class="bg-white p-2 rounded-lg shadow-sm border border-slate-200 space-y-1.5">
                    <div class="flex gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase block tracking-widest">Customer Details</label>
                                <button type="button" class="text-[8px] font-black uppercase text-[#161b97] hover:underline" wire:click="openCustomerModal">New Customer</button>
                            </div>
                            <div class="relative grid grid-cols-2 gap-1.5">
                                <div class="relative group">
                                    <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-base group-focus-within:text-[#161b97] transition-colors">person</span>
                                    <input type="text" wire:model.live.debounce.250ms="customerSearchName" placeholder="Customer name" autocomplete="off"
                                        class="w-full pl-8 pr-2 py-2 bg-slate-50 border border-slate-200 rounded-md outline-none text-xs font-bold focus:ring-2 focus:ring-[#161b97]/10 focus:border-[#161b97] transition-all">
                                </div>
                                <div class="relative group">
                                    <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-base group-focus-within:text-[#161b97] transition-colors">phone</span>
                                    <input type="text" wire:model.live.debounce.250ms="customerSearchPhone" placeholder="Phone number" autocomplete="off"
                                        class="w-full pl-8 pr-2 py-2 bg-slate-50 border border-slate-200 rounded-md outline-none text-xs font-bold focus:ring-2 focus:ring-[#161b97]/10 focus:border-[#161b97] transition-all">
                                </div>
                                @if(count($customerSuggestions) > 0)
                                <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl z-[80] overflow-hidden">
                                    @foreach($customerSuggestions as $suggestion)
                                    <button type="button" class="w-full flex items-center justify-between gap-2 px-3 py-2 text-left hover:bg-blue-50 border-b border-slate-100 last:border-0" wire:click="selectCustomerSuggestion({{ $suggestion->id }})">
                                        <span class="text-xs font-bold text-slate-700 truncate">{{ $suggestion->business_name ?: $suggestion->name }}</span>
                                        <span class="text-[10px] text-slate-500 shrink-0">{{ $suggestion->phone }}</span>
                                    </button>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="w-1/3">
                            <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block tracking-widest">Price Type</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">sell</span>
                                <select class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-md outline-none text-xs font-bold appearance-none focus:border-[#161b97] transition-all" wire:model.live="priceType">
                                    <option value="retail">Retail Price</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="distribute">Distribute Price</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-300 text-lg pointer-events-none">expand_more</span>
                            </div>
                        </div>
                    </div>

                    {{-- Customer Balance Information (Conditional) --}}
                    @if($customerId && $customerId != '' && $selectedCustomer && $selectedCustomer->type != 'Walking Customer')
                    <div class="flex items-center justify-between px-2 py-1 bg-slate-50 border border-slate-100 rounded">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider">Opening Balance</span>
                        <span class="text-xs font-black text-slate-800">{{ number_format($customerOpeningBalanceDisplay, 2) }}</span>
                    </div>
                    @endif

                    {{-- Filter Buttons --}}
                    <div class="grid grid-cols-2 gap-2">
                        <button class="flex items-center justify-center gap-1.5 py-1.5 bg-slate-100 border border-slate-200 rounded-md hover:bg-slate-200 hover:border-slate-300 transition-all font-black text-[9px] text-slate-600 uppercase tracking-tighter shadow-sm"
                            wire:click="toggleCategoryPanel">
                            <span class="material-symbols-outlined text-sm text-[#161b97]">category</span>
                            FILTER BY CATEGORY
                        </button>
                        <button class="flex items-center justify-center gap-1.5 py-1.5 bg-slate-100 border border-slate-200 rounded-md hover:bg-slate-200 hover:border-slate-300 transition-all font-black text-[9px] text-slate-600 uppercase tracking-tighter shadow-sm"
                            wire:click="toggleBrandPanel">
                            <span class="material-symbols-outlined text-sm text-[#161b97]">branding_watermark</span>
                            FILTER BY BRAND
                        </button>
                    </div>
                </div>

                {{-- Product Grid Area: Clean Scanner Status (Matches Quotation Billing) --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar pr-1 flex items-center justify-center bg-white rounded-lg border border-slate-200 shadow-sm">
                    <div class="py-24 text-center px-6">
                        <div class="bg-slate-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 shadow-inner">
                            <span class="material-symbols-outlined text-5xl text-slate-300">barcode_scanner</span>
                        </div>
                        <h4 class="text-slate-600 font-black uppercase tracking-wider text-xs">Ready for Barcode or Product Code</h4>
                        <p class="text-slate-400 text-xs mt-1 max-w-sm mx-auto">
                            Scan a product barcode or enter a product code in the search bar on the left to add items directly to this sale.
                        </p>
                        <div class="mt-4 flex items-center justify-center gap-2">
                            <span class="inline-flex items-center gap-1 text-[10px] text-slate-500 bg-slate-50 px-2.5 py-1 rounded border border-slate-200 font-mono font-bold">
                                <span class="material-symbols-outlined text-xs text-green-600 font-black">check_circle</span> Barcode scanner ready
                            </span>
                            <span class="inline-flex items-center gap-1 text-[10px] text-slate-500 bg-slate-50 px-2.5 py-1 rounded border border-slate-200 font-mono font-bold">
                                <span class="material-symbols-outlined text-xs text-blue-600 font-black">check_circle</span> Product code ready
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>


    {{-- Sliding Category Sidebar (Right to Left, 50% width) --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[2000] transition-opacity duration-300 {{ $showCategoryPanel ? 'opacity-100' : 'opacity-0 pointer-events-none' }}" wire:click.self="$set('showCategoryPanel', false)"></div>
    <aside class="fixed right-0 top-0 bottom-0 w-1/2 bg-white z-[2001] shadow-2xl transition-transform duration-300 transform {{ $showCategoryPanel ? 'translate-x-0' : 'translate-x-full' }} flex flex-col">
        <div class="p-4 flex justify-between items-center border-b border-slate-100 bg-slate-50">
            <h6 class="mb-0 font-black text-xs text-slate-800 tracking-widest"><i class="material-symbols-outlined align-middle mr-2 text-[#161b97]">grid_view</i>ALL CATEGORIES</h6>
            <button class="text-slate-400 hover:text-slate-600 transition-colors" wire:click="$set('showCategoryPanel', false)">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-2 overflow-y-auto flex-1 custom-scrollbar">
            <button class=" mb-1 text-center  p-3 rounded-lg transition-all border border-slate-100 {{ !$selectedCategory ? 'bg-[#161b97] text-white shadow-lg shadow-blue-500/30' : 'hover:bg-slate-100 text-slate-600' }}"
                wire:click="showAllProducts">
                <span class="font-black text-xs tracking-tight">Show All Items</span>
                <span class="text-[10px] font-bold opacity-70">{{ count($products) }}</span>
            </button>
            @foreach($categories as $category)
            <button class=" mb-1 text-center p-3 rounded-lg transition-all border border-slate-100 {{ $selectedCategory == $category->id ? 'bg-[#161b97] text-white shadow-lg shadow-blue-500/30' : 'hover:bg-slate-100 text-slate-600' }}"
                wire:click="selectCategory({{ $category->id }})">
                <span class="font-black text-xs tracking-tight">{{ $category->category_name }}</span>
                <span class="text-[10px] font-bold opacity-70">{{ $category->products_count }}</span>
            </button>
            @endforeach
        </div>
    </aside>

    {{-- Sliding Brand Sidebar (Right to Left, 50% width) --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[2000] transition-opacity duration-300 {{ $showBrandPanel ? 'opacity-100' : 'opacity-0 pointer-events-none' }}" wire:click.self="$set('showBrandPanel', false)"></div>
    <aside class="fixed right-0 top-0 bottom-0 w-1/2 bg-white z-[2001] shadow-2xl transition-transform duration-300 transform {{ $showBrandPanel ? 'translate-x-0' : 'translate-x-full' }} flex flex-col">
        <div class="p-4 flex justify-between items-center border-b border-slate-100 bg-slate-50">
            <h6 class="mb-0 font-black text-xs text-slate-800 tracking-widest"><i class="material-symbols-outlined align-middle mr-2 text-[#161b97]">local_offer</i>ALL BRANDS</h6>
            <button class="text-slate-400 hover:text-slate-600 transition-colors" wire:click="$set('showBrandPanel', false)">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-2 overflow-y-auto flex-1 custom-scrollbar">
            <button class=" mb-1 text-center  p-3 rounded-lg transition-all border border-slate-100 {{ !$selectedBrand ? 'bg-[#161b97] text-white shadow-lg shadow-blue-500/30' : 'hover:bg-slate-100 text-slate-600' }}"
                wire:click="showAllBrands">
                <span class="font-black text-xs tracking-tight">Show All Brands</span>
                <span class="text-[10px] font-bold opacity-70">{{ count($products) }}</span>
            </button>
            @foreach($brands as $brand)
            <button class=" mb-1 text-center p-3 rounded-lg transition-all border border-slate-100 {{ $selectedBrand == $brand['id'] ? 'bg-[#161b97] text-white shadow-lg shadow-blue-500/30' : 'hover:bg-slate-100 text-slate-600' }}"
                wire:click="selectBrand({{ $brand['id'] }})">
                <span class="font-black text-xs tracking-tight">{{ $brand['brand_name'] }}</span>
                <span class="text-[10px] font-bold opacity-70">{{ $brand['products_count'] }}</span>
            </button>
            @endforeach
        </div>
    </aside>

    {{-- ================= OPENING CASH IN HAND MODAL ================= --}}
    @if($showOpeningCashModal)
    <div class="fixed inset-0 z-[5000] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-md"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative transform transition-all z-10">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-[#161b97] to-[#12167d] px-6 py-5 text-center">
                <div class="w-16 h-16 rounded-full bg-white/15 border-2 border-white/30 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-white text-3xl">account_balance_wallet</span>
                </div>
                <h3 class="font-black text-lg text-white uppercase tracking-widest">Cash in Hand</h3>
                <p class="text-[10px] text-blue-200 font-bold uppercase tracking-wider mt-1">Enter opening cash to start your POS session</p>
            </div>
            {{-- Body --}}
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2">Opening Cash Amount</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-sm font-black text-slate-400 pointer-events-none">Rs.</span>
                        <input type="number"
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-2xl font-black text-slate-800 outline-none focus:border-[#161b97] transition-colors text-center"
                            wire:model="openingCashAmount"
                            min="0" step="0.01"
                            placeholder="0.00"
                            x-init="$nextTick(() => { $el.focus(); $el.select(); })"
                            @keydown.enter.prevent="$wire.submitOpeningCash()">
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-blue-500 text-base mt-0.5">info</span>
                        <p class="text-[10px] text-blue-700 font-bold leading-relaxed">
                            Enter the total cash available in your register before starting sales.
                            You can enter <strong>0</strong> if starting with no cash.
                        </p>
                    </div>
                </div>
            </div>
            {{-- Footer --}}
            <div class="px-6 pb-6">
                <button class="w-full bg-[#161b97] hover:bg-blue-800 text-white font-black py-4 rounded-xl flex items-center justify-center gap-2 shadow-xl shadow-blue-500/20 transition-all text-sm uppercase tracking-widest"
                    wire:click="submitOpeningCash"
                    wire:loading.attr="disabled"
                    wire:target="submitOpeningCash">
                    <span wire:loading wire:target="submitOpeningCash">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="submitOpeningCash">
                        <span class="material-symbols-outlined text-xl">play_arrow</span>
                    </span>
                    <span wire:loading.remove wire:target="submitOpeningCash">Start POS Session</span>
                    <span wire:loading wire:target="submitOpeningCash">Starting...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODALS WRAPPER --}}
    @if($showPaymentModal || $showCustomerModal || $showSaleModal || $showCloseRegisterModal || $showServiceModal || $showPriceHelperModal)
    <div class="fixed inset-0 z-[3000] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        {{-- CUSTOMER MODAL --}}
        @if($showCustomerModal)
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden relative transform transition-all">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-black text-xs uppercase tracking-widest text-[#161b97]"><i class="material-symbols-outlined align-middle mr-2">person_add</i>ADD NEW CUSTOMER</h3>
                <button class="text-slate-400 hover:text-slate-600" wire:click="closeCustomerModal"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4">
                <div class="col-span-2 md:col-span-1">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Full Name *</label>
                    <input type="text" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold" wire:model="customerName" placeholder="Enter name...">
                    @error('customerName') <span class="text-red-500 text-[9px] font-bold mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2 md:col-span-1">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Phone Number * </label>
                    <input type="text" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold" wire:model="customerPhone" placeholder="07xxxxxxxx or 07xxxx, 07yyyy / 09zzzz">
                    @error('customerPhone') <span class="text-red-500 text-[9px] font-bold mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2 md:col-span-1">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Customer Type *</label>
                    <select class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold" wire:model="customerType">
                        <option value="">-- Select Type --</option>
                        <option value="retail">Retail</option>
                        <option value="wholesale">Wholesale</option>
                        <option value="distributor">Distributor</option>
                    </select>
                    @error('customerType') <span class="text-red-500 text-[9px] font-bold mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Email Address</label>
                    <input type="email" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold" wire:model="customerEmail" placeholder="email@example.com">
                    @error('customerEmail') <span class="text-red-500 text-[9px] font-bold mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Billing Address</label>
                    <textarea class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold" wire:model="customerAddress" rows="2" placeholder="Address..."></textarea>
                </div>
                <div class="col-span-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Business Name</label>
                    <input type="text" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold" wire:model="businessName" placeholder="Business name...">
                </div>

                {{-- More Information Toggle Button --}}
                <div class="col-span-2">
                    <button type="button" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 transition-colors text-xs font-bold text-slate-600"
                        wire:click="$toggle('showCustomerMoreInfo')">
                        <span class="material-symbols-outlined text-base transition-transform" style="transform: rotateZ({{ $showCustomerMoreInfo ? '180' : '0' }})deg)">
                            expand_more
                        </span>
                        More Information
                    </button>
                </div>

                {{-- More Information Section (Conditional) --}}
                @if($showCustomerMoreInfo)
                <div class="col-span-2 space-y-3 pt-2 border-t border-slate-200">
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Opening Balance</label>
                        <input type="number" step="0.01" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold"
                            wire:model="customerOpeningBalance" placeholder="0.00">
                        @error('customerOpeningBalance') <span class="text-red-500 text-[9px] font-bold mt-1">{{ $message }}</span> @enderror
                        <small class="text-slate-500 text-[8px] mt-1">Amount customer owes at the start</small>
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Overpaid Amount</label>
                        <input type="number" step="0.01" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold"
                            wire:model="customerOverpaidAmount" placeholder="0.00">
                        @error('customerOverpaidAmount') <span class="text-red-500 text-[9px] font-bold mt-1">{{ $message }}</span> @enderror
                        <small class="text-slate-500 text-[8px] mt-1">Advance payment from customer</small>
                    </div>
                </div>
                @endif
            </div>
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                <button class="px-6 py-2.5 text-[10px] font-black uppercase text-slate-400" wire:click="closeCustomerModal">Discard</button>
                <button class="px-8 py-2.5 bg-[#161b97] text-white rounded-lg text-[10px] font-black uppercase shadow-lg shadow-blue-500/20" wire:click="createCustomer">Save Customer</button>
            </div>
        </div>
        @endif

        {{-- PAYMENT MODAL --}}
        @if($showPaymentModal)
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden relative transform transition-all flex flex-col">

            {{-- Header --}}
            <div class="shrink-0 bg-gradient-to-r from-slate-900 to-slate-800 px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#161b97]/20 border border-[#161b97]/30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[#161b97] text-base">payments</span>
                    </div>
                    <div>
                        <h3 class="font-black text-sm text-white uppercase tracking-widest leading-none">Secure Transaction</h3>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Select payment method to complete sale</p>
                    </div>
                </div>
                <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-slate-400 hover:text-white"
                    wire:click="closePaymentModal">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="flex min-h-0">

                {{-- LEFT: Method + Form --}}
                <div class="w-[45%] border-r border-slate-100 p-5 flex flex-col gap-4">

                    {{-- Mode Toggle: Single vs Multi --}}
                    <div>
                        <div class="flex p-1 bg-slate-100 rounded-xl border border-slate-200 mb-4">
                            <button type="button" wire:click="$set('paymentMode', 'single')" class="flex-1 py-2 text-[10px] sm:text-xs font-black uppercase rounded-lg transition-all {{ $paymentMode === 'single' ? 'bg-white shadow text-[#161b97]' : 'text-slate-500 hover:bg-slate-200' }}">
                                Single Payment
                            </button>
                            <button type="button" wire:click="$set('paymentMode', 'multiple')" class="flex-1 py-2 text-[10px] sm:text-xs font-black uppercase rounded-lg transition-all {{ $paymentMode === 'multiple' ? 'bg-white shadow text-[#161b97]' : 'text-slate-500 hover:bg-slate-200' }}">
                                Multi Payment
                            </button>
                        </div>

                        {{-- Payment Method Selection --}}
                        @if($paymentMode === 'single')
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Payment Method</p>
                        <div class="flex rounded-xl overflow-hidden border border-slate-200 bg-slate-50">
                            <button type="button"
                                wire:click="$set('paymentMethod','cash')"
                                class="flex-1 flex items-center justify-center gap-1 px-1 py-2.5 text-[10px] sm:text-xs font-black uppercase transition-all {{ $paymentMethod === 'cash' ? 'bg-[#161b97] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' }}">
                                <span class="material-symbols-outlined text-sm sm:text-base">payments</span>
                                Cash
                            </button>
                            <button type="button"
                                wire:click="$set('paymentMethod','bank_transfer')"
                                class="flex-1 flex items-center justify-center gap-1 px-1 py-2.5 text-[10px] sm:text-xs font-black uppercase transition-all border-l border-slate-200 {{ $paymentMethod === 'bank_transfer' ? 'bg-[#161b97] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' }}">
                                <span class="material-symbols-outlined text-sm sm:text-base">account_balance</span>
                                Bank 
                            </button>
                            <button type="button"
                                wire:click="$set('paymentMethod','online')"
                                class="flex-1 min-w-0 flex items-center justify-center gap-1 px-1 py-2.5 text-[10px] sm:text-xs font-black uppercase whitespace-nowrap transition-all border-l border-slate-200 {{ $paymentMethod === 'online' ? 'bg-[#161b97] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' }}">
                                <span class="inline-flex shrink-0 items-center" aria-label="Visa and Mastercard accepted">
                                    <svg class="w-9 h-5 sm:w-10 sm:h-5" viewBox="0 0 40 20" role="img" aria-hidden="true">
                                        <text x="0" y="13" font-size="8" font-weight="900" font-style="italic" fill="{{ $paymentMethod === 'online' ? '#ffffff' : '#1a1f71' }}">VISA</text>
                                        <circle cx="28" cy="10" r="7" fill="#eb001b"/>
                                        <circle cx="34" cy="10" r="7" fill="#f79e1b" fill-opacity=".95"/>
                                    </svg>
                                </span>
                                <span class="leading-[1.05] text-[9px] sm:text-[10px]">Card<br>Pay</span>
                            </button>
                        </div>
                        @else
                        {{-- Multi Payment Methods --}}
                        <div class="flex gap-3 mb-2">
                            <div class="flex-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">Primary Method</label>
                                <select disabled class="w-full px-3 py-2 text-xs font-bold bg-slate-100 border border-slate-200 rounded-xl outline-none text-slate-500">
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">Secondary Method</label>
                                <select wire:model.live="splitSecondaryMethod" class="w-full px-3 py-2 text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-[#161b97] transition-colors">
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="online">Card</option>
                                </select>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Cash Form --}}
                    @if($paymentMethod === 'cash' || $paymentMethod === 'multiple')
                    <div class="space-y-3" x-data="{ localAmount: '{{ $amountReceived ?? 0 }}', grandTotal: '{{ $grandTotal ?? 0 }}', bankAmount: '{{ $bankTransferAmount ?? 0 }}' }">
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">Amount Received</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-[10px] font-black text-slate-400 pointer-events-none">Rs.</span>
                                <input type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xl font-black text-slate-800 outline-none focus:border-[#161b97] transition-colors"
                                    oninput="this.value = this.value.replace(/[^0-9\.]/g, '')"
                                    wire:model.debounce.300ms="amountReceived"
                                    @input="localAmount = $event.target.value"
                                    x-init="$nextTick(() => $el.focus())">
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-emerald-600 text-base">currency_exchange</span>
                                <span class="text-[9px] font-black text-emerald-700 uppercase tracking-widest">Balance to Return</span>
                            </div>
                            <span class="text-lg font-black text-emerald-700">Rs. <span x-text="(() => { let a=parseFloat(localAmount||0); let b=parseFloat(bankAmount||0); let g=parseFloat(grandTotal||0); if(isNaN(a)) a=0; if(isNaN(b)) b=0; if(isNaN(g)) g=0; let t = a; if('{{ $paymentMethod }}' === 'multiple') t += b; return (Math.max(0,t-g)).toFixed(2); })()">{{ number_format(max(0, (float) ($amountReceived ?: 0) + ($paymentMethod === 'multiple' ? (float) ($bankTransferAmount ?: 0) : 0) - (float) ($grandTotal ?? 0)), 2) }}</span></span>
                        </div>
                    </div>
                    @endif

                    {{-- Bank / Online Form --}}
                    @if($paymentMethod === 'bank_transfer' || $paymentMethod === 'online' || $paymentMethod === 'multiple')
                    <div class="space-y-2.5" x-data="{ localBankAmount: '{{ $bankTransferAmount ?? 0 }}', grandTotal: '{{ $grandTotal ?? 0 }}' }">
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">Amount</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-[10px] font-black text-slate-400 pointer-events-none">Rs.</span>
                                <input type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-xl font-black text-slate-800 outline-none focus:border-[#161b97] transition-colors"
                                    oninput="this.value = this.value.replace(/[^0-9\.]/g, '')"
                                    wire:model.debounce.300ms="bankTransferAmount"
                                    @input="localBankAmount = $event.target.value">
                            </div>
                        </div>
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">
                                {{ ($paymentMethod === 'online' || ($paymentMethod === 'multiple' && $splitSecondaryMethod === 'online')) ? 'Platform (e.g. PayHere, FriMi)' : 'Bank Name' }}
                            </label>
                            <input type="text"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-[#161b97] transition-colors"
                                wire:model="bankTransferBankName" 
                                placeholder="{{ ($paymentMethod === 'online' || ($paymentMethod === 'multiple' && $splitSecondaryMethod === 'online')) ? 'Platform name' : 'e.g. Commercial Bank' }}">
                        </div>
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">Reference / Slip No.</label>
                            <input type="text"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-[#161b97] transition-colors"
                                wire:model="bankTransferReferenceNumber" placeholder="Transaction reference">
                        </div>
                    </div>
                    @endif

                    {{-- Notes --}}
                    <div class="mt-auto">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">Notes</label>
                        <textarea class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-[#161b97] transition-colors resize-none"
                            wire:model="paymentNotes" placeholder="Optional transaction notes..." rows="2"></textarea>
                    </div>
                </div>

                {{-- RIGHT: Order Summary --}}
                <div class="w-[55%] bg-slate-50/60 p-5 flex flex-col gap-4">

                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Order Breakdown</p>

                    {{-- Summary rows --}}
                    <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100 overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Customer</span>
                            <span class="text-xs font-black text-slate-800">
                                @php
                                    $paymentCustName = ($selectedCustomer && $selectedCustomer->name !== 'Walking Customer')
                                        ? ($selectedCustomer->business_name ?: $selectedCustomer->name)
                                        : ($walkingCustomerName ?: ($customerSearchName ?: 'Walking Customer'));
                                    $paymentCustPhone = ($selectedCustomer && $selectedCustomer->name !== 'Walking Customer')
                                        ? $selectedCustomer->phone
                                        : ($walkingCustomerPhone ?: ($customerSearchPhone ?: null));
                                @endphp
                                {{ $paymentCustName }}
                                @if($paymentCustPhone && $paymentCustPhone !== 'xxxxx')
                                    <span class="text-[10px] text-slate-500 font-normal">({{ $paymentCustPhone }})</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Items</span>
                            <span class="text-xs font-bold text-slate-700">{{ count($cart) }} item{{ count($cart) != 1 ? 's' : '' }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Subtotal</span>
                            <span class="text-xs font-bold text-slate-700">Rs. {{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if(($additionalDiscountAmount + $totalDiscount) > 0)
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Discounts</span>
                            <span class="text-xs font-bold text-emerald-600">-Rs. {{ number_format($additionalDiscountAmount + $totalDiscount, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Grand Total --}}
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl px-5 py-4 flex items-center justify-between shadow-inner">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Grand Total</p>
                            <p class="text-3xl font-black text-white tracking-tight mt-0.5">Rs. {{ number_format($grandTotal, 2) }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-xl">paid</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-[#161b97] text-base">
                            {{ $paymentMethod === 'cash' ? 'payments' : ($paymentMethod === 'online' ? 'phone_iphone' : ($paymentMethod === 'multiple' ? 'call_split' : 'account_balance')) }}
                        </span>
                        <div class="flex-1">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Paying via</p>
                            <p class="text-xs font-black text-slate-700">
                                {{ $paymentMethod === 'cash' ? 'Cash Payment' : ($paymentMethod === 'online' ? 'Online Transfer' : ($paymentMethod === 'multiple' ? 'Split Payment' : 'Bank Transfer')) }}
                            </p>
                        </div>
                        @if($paymentMethod === 'cash' && (float) ($amountReceived ?: 0) >= (float) ($grandTotal ?? 0))
                        <span class="text-[9px] font-black text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Ready</span>
                        @elseif($paymentMethod === 'cash' && (float) ($amountReceived ?: 0) > 0)
                          <span class="text-[9px] font-black text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">Partial</span>
                        @elseif(($paymentMethod === 'bank_transfer' || $paymentMethod === 'online') && (float) ($bankTransferAmount ?: 0) >= (float) ($grandTotal ?? 0))
                        <span class="text-[9px] font-black text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Ready</span>
                        @elseif($paymentMethod === 'multiple' && ((float) ($amountReceived ?: 0) + (float) ($bankTransferAmount ?: 0)) >= (float) ($grandTotal ?? 0))
                        <span class="text-[9px] font-black text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Ready</span>
                        @elseif($paymentMethod === 'multiple' && ((float) ($amountReceived ?: 0) + (float) ($bankTransferAmount ?: 0)) > 0)
                        <span class="text-[9px] font-black text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">Partial</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="mt-auto flex gap-3">
                        <button class="flex-1 py-3 bg-white border-2 border-slate-200 text-slate-500 font-black rounded-xl uppercase tracking-wider hover:bg-slate-50 transition-all text-xs"
                            wire:click="closePaymentModal">Cancel</button>
                        <button class="flex-[2] py-3 bg-[#161b97] hover:bg-yellow-600 text-white font-black rounded-xl uppercase tracking-wider shadow-lg shadow-yellow-500/20 text-xs flex items-center justify-center gap-2 transition-all"
                            wire:click="completeSaleWithPaymentAndPrint">
                            <span class="material-symbols-outlined text-base">print_connect</span>
                            Process & Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- SALE PREVIEW MODAL (Invoice) --}}
        @if($showSaleModal && $createdSale)
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden relative transform transition-all flex flex-col max-h-[90vh]">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-5 py-3.5 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#161b97]/20 border border-[#161b97]/30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[#161b97] text-base">receipt_long</span>
                    </div>
                    <div>
                        <h3 class="font-black text-sm text-white uppercase tracking-widest leading-none">Transaction Finalized</h3>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Invoice #{{ $createdSale->invoice_number }} &bull; {{ $createdSale->created_at->format('d/m/Y h:i A') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-emerald-500/20 border border-emerald-500/30 rounded-full text-[9px] font-black text-emerald-400 uppercase tracking-widest">{{ ucfirst($createdSale->payment_status ?? 'paid') }}</span>
                    <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-slate-400 hover:text-white" wire:click="closeModal">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            </div>

            {{-- Invoice Content --}}
            <div class="flex-1 overflow-y-auto p-2 bg-white flex justify-center" id="printableInvoice">
                <div class="receipt-container" style="width: 76mm; padding: 0 3mm; background: white; color: #000; line-height: 1.2; font-size: 12px; box-sizing: border-box;">
                    <!-- Thermal Receipt Header -->
                    <div style="text-align:center; font-weight:bold; padding-top: 3mm;">
                        <div style="border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 5px 0; margin-bottom: 3px; font-size: 16px; text-transform: uppercase;">
                            {{ config('shop.name', 'JAFFNA GOLD COVERING') }}
                        </div>
                        <div style="font-size: 12px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 3px;">
                            {{ config('shop.branch', 'VAVUNIYA BRANCH') }}
                        </div>
                        <div style="font-size: 11px;">{{ config('shop.address', '2nd Cross Street, Vavuniya') }}</div>
                        <div style="font-size: 11px;">Tel: {{ config('shop.phone', '0761919650') }}</div>
                        <div style="font-size: 11px;">Hotline: {{ config('shop.hotline', '021 222 85 89') }}</div>
                    </div>

                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

                    <!-- Receipt Meta -->
                    <div style="font-size: 11px; font-weight: bold;">
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 90px;">Branch</span>
                            <span style="margin-right: 5px;">:</span>
                            <span>{{ config('shop.branch', 'Vavuniya Branch') }}</span>
                        </div>
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 90px;">Receipt No</span>
                            <span style="margin-right: 5px;">:</span>
                            <span>{{ $createdSale->invoice_number }}</span>
                        </div>
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 90px;">Date</span>
                            <span style="margin-right: 5px;">:</span>
                            <span>{{ $createdSale->created_at->format('d-m-Y') }}</span>
                        </div>
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 90px;">Time</span>
                            <span style="margin-right: 5px;">:</span>
                            <span>{{ $createdSale->created_at->format('h:i A') }}</span>
                        </div>
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 90px;">Cashier</span>
                            <span style="margin-right: 5px;">:</span>
                            <span>{{ $createdSale->user->name ?? 'Admin' }}</span>
                        </div>
                        @php
                            $receiptCustName = ($createdSale->customer && $createdSale->customer->name !== 'Walking Customer')
                                ? ($createdSale->customer->business_name ?: $createdSale->customer->name)
                                : ($createdSale->walking_customer_name ?: null);
                            $receiptCustPhone = ($createdSale->customer && $createdSale->customer->name !== 'Walking Customer')
                                ? $createdSale->customer->phone
                                : ($createdSale->walking_customer_phone ?: null);
                        @endphp
                        @if($receiptCustName)
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 90px;">Customer</span>
                            <span style="margin-right: 5px;">:</span>
                            <span>{{ $receiptCustName }}</span>
                        </div>
                        @endif
                        @if($receiptCustPhone && $receiptCustPhone !== 'xxxxx')
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 90px;">Phone</span>
                            <span style="margin-right: 5px;">:</span>
                            <span>{{ $receiptCustPhone }}</span>
                        </div>
                        @endif
                    </div>

                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

                    <!-- Boxed Table Structure -->
                    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000; margin-top: 5px;">
                        <thead>
                            <tr style="border-bottom: 1.5px solid #000;">
                                <th style="border-right: 1.5px solid #000; padding: 4px; text-align: left; font-size: 11px; font-weight: bold;">Item</th>
                                <th style="border-right: 1.5px solid #000; padding: 4px; text-align: center; font-size: 11px; font-weight: bold; width: 45px;">Qty</th>
                                <th style="padding: 4px; text-align: right; font-size: 11px; font-weight: bold; width: 85px;">Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($createdSale->items as $item)
                            <tr style="border-bottom: 1.5px solid #000;">
                                <td style="border-right: 1.5px solid #000; padding: 4px; font-size: 11px; font-weight: bold; vertical-align: top; line-height: 1.3;">
                                    {{ $loop->iteration }}. {{ $item->product_name }}
                                    <div style="font-size: 9px; font-weight: normal; margin-top: 2px;">Price: {{ number_format($item->unit_price, 0) }}</div>
                                    @if($item->discount_per_unit > 0)
                                    <div style="font-size: 9px; font-weight: bold; margin-top: 1px;">
                                        Disc: {{ number_format($item->discount_per_unit, 0) }}
                                        @if($item->discount_percentage > 0) ({{ number_format($item->discount_percentage, 0) }}%) @endif
                                    </div>
                                    @endif
                                    @if($item->has_warranty)
                                    <div style="font-size: 9px; font-weight: bold; font-style: italic; color: #000000ff; margin-top: 1px;">({{ $item->warranty_duration }} Warranty)</div>
                                    @endif
                                </td>
                                <td style="border-right: 1.5px solid #000; padding: 4px; text-align: center; font-size: 11px; font-weight: bold; vertical-align: top;">
                                    {{ $item->quantity }}
                                </td>
                                <td style="padding: 4px; text-align: right; font-size: 11px; font-weight: bold; vertical-align: top;">
                                    {{ number_format($item->total, 0) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

                    <!-- Totals Block -->
                    <div style="font-size: 11px; font-weight: bold; line-height: 1.4;">
                        @php
                        $originalSubtotal = $createdSale->items->sum(fn($i) => $i->unit_price * $i->quantity);
                        $totalDiscountRs = $createdSale->discount_amount;
                        $totalDiscPercentage = $originalSubtotal > 0 ? ($totalDiscountRs / $originalSubtotal * 100) : 0;
                        @endphp
                        <div style="display: flex; justify-content: space-between;">
                            <span>Total Items</span>
                            <span>{{ $createdSale->items->count() }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Sub Total</span>
                            <span>{{ number_format($originalSubtotal, 0) }}</span>
                        </div>
                        @if($totalDiscountRs > 0)
                        <div style="display: flex; justify-content: space-between;">
                            <span>Discount @if($totalDiscPercentage > 0)({{ number_format($totalDiscPercentage, 1) }}%)@endif</span>
                            <span>-{{ number_format($totalDiscountRs, 0) }}</span>
                        </div>
                        @endif

                        <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 800; margin: 2px 0;">
                            <span>TOTAL</span>
                            <span>{{ number_format($createdSale->total_amount, 0) }}</span>
                        </div>

                        @php
                        $totalReceived = $createdSale->payments->sum(function($p) {
                            return $p->amount_tendered ?? $p->amount;
                        });
                        $balanceAmount = max(0, $totalReceived - $createdSale->total_amount);
                        @endphp
                        <div style="display: flex; justify-content: space-between;">
                            <span>RECEIVED</span>
                            <span>{{ number_format($totalReceived, 0) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>BALANCE</span>
                            <span>{{ number_format($balanceAmount, 0) }}</span>
                        </div>
                        @if($createdSale->due_amount > 0)
                        <div style="display: flex; justify-content: space-between;">
                            <span>DUE AMOUNT</span>
                            <span>{{ number_format($createdSale->due_amount, 0) }}</span>
                        </div>
                        @endif
                    </div>

                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

                    <!-- Payment Method -->
                    <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">
                        @if($createdSale->payments && $createdSale->payments->count() > 0)
                        @foreach($createdSale->payments as $payment)
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 110px;">Payment Method</span>
                            <span>: {{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}</span>
                        </div>
                        @endforeach
                        @else
                        @if($createdSale->payment_type == 'full' && ($createdSale->payments->count() == 0 || $createdSale->payments->first()->payment_method == 'cash'))
                        <div style="display: flex;">
                            <span style="width: 110px;">Payment Method</span>
                            <span>: CASH</span>
                        </div>
                        @else
                        <div style="display: flex;">
                            <span style="width: 110px;">Payment Method</span>
                            <span>: ONLINE TRANSFER</span>
                        </div>
                        @endif
                        @endif
                        <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
                    </div>

                    {{-- Receipt Barcode (Vector SVG Code 128) --}}
                    <div style="display: flex; justify-content: center; margin: 8px 0;" wire:ignore
                        x-data="{ 
                                saleId: '{{ $createdSale->invoice_number ?? $createdSale->sale_id }}',
                                init() {
                                    this.renderBarcode();
                                },
                                renderBarcode() {
                                    let attempts = 0;
                                    const tryRender = () => {
                                        if (typeof JsBarcode === 'function' && this.$refs.barcode) {
                                            JsBarcode(this.$refs.barcode, this.saleId, {
                                                format: 'CODE128',
                                                width: 2,
                                                height: 55,
                                                displayValue: true,
                                                fontSize: 13,
                                                textMargin: 3,
                                                font: 'Arial',
                                                fontOptions: 'bold',
                                                background: '#ffffff',
                                                lineColor: '#000000',
                                                margin: 4
                                            });
                                        } else if (attempts < 30) {
                                            attempts++;
                                            setTimeout(tryRender, 200);
                                        }
                                    };
                                    tryRender();
                                }
                             }">
                        <svg x-ref="barcode" class="barcode-element" style="max-width: 100%; image-rendering: pixelated; image-rendering: -webkit-optimize-contrast; shape-rendering: crispEdges;"></svg>
                    </div>

                    <!-- Footer -->
                    <div style="text-align: center; font-size: 11px; margin-top: 10px;">
                        <div style="font-weight: bold;">Thank You for Your Visit!</div>
                        <div style="font-weight: bold;">Return will be accepted within 3 days</div>
                        <div style="font-weight: bold;">Visit Us Again</div>
                        <div style="margin: 12px auto 0 auto; width: 80%; border-top: 1px solid #000; border-bottom: 1px solid #000; height: 3px;"></div>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="px-5 pb-5 pt-3 bg-slate-50 border-t border-slate-100 flex flex-wrap justify-center gap-3 shrink-0">
                <button class="px-6 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-colors"
                    wire:click="createNewSale">Close &amp; New</button>
                @if($createdSale && $createdSale->customer_display_phone && $createdSale->customer_display_phone !== 'xxxxx')
                <button class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-600/20 flex items-center gap-1.5 transition-colors"
                    wire:click="sendSaleSms({{ $createdSale->id }})" wire:loading.attr="disabled">
                    <span class="material-symbols-outlined text-base">sms</span> Send SMS
                </button>
                @endif
                <button class="px-8 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-slate-300 flex items-center gap-2 transition-colors"
                    onclick="printInvoice()">
                    <span class="material-symbols-outlined text-base">print</span> Print Invoice
                </button>
                <button class="px-8 py-2.5 bg-[#161b97] hover:bg-yellow-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-yellow-500/20 transition-colors"
                    wire:click="downloadInvoice">Download PDF</button>
            </div>
        </div>
        @endif

        {{-- POS REPORT MODAL (Close Register) --}}
        @if($showCloseRegisterModal)
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden relative transform transition-all border border-slate-100">
            <div class="bg-slate-900 p-8 text-center relative overflow-hidden">
                <div class="absolute inset-0 opacity-10 bg-[url('https://JaffnaGold.lk/logo.png')] bg-center bg-no-repeat bg-contain scale-150"></div>
                <h3 class="text-xl font-black text-white uppercase tracking-[0.2em] relative z-10">Terminal Summary</h3>
                <p class="text-slate-400 text-[10px] font-bold mt-2 relative z-10">{{ date('d M Y | H:i') }}</p>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <label class="text-[8px] font-black text-slate-300 uppercase tracking-widest block mb-1">Session Inflow</label>
                        <span class="text-lg font-black text-slate-800">Rs. {{ number_format($sessionSummary['opening_cash'] ?? 0, 2) }}</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                        <label class="text-[8px] font-black text-slate-300 uppercase tracking-widest block mb-1">Total Turnover</label>
                        <span class="text-lg font-black text-[#e67e22]">Rs. {{ number_format($sessionSummary['total_pos_sales'] ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="space-y-3 pt-4 border-t border-slate-50">
                    <div class="flex justify-between items-center text-xs font-bold text-slate-500 py-1 transition-all hover:bg-slate-50 px-2 rounded">
                        <span>Terminal Cash Offset:</span>
                        <span class="text-slate-800">Rs. {{ number_format($sessionSummary['pos_cash_sales'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-bold text-slate-500 py-1 transition-all hover:bg-slate-50 px-2 rounded">
                        <span>Internal Expenses:</span>
                        <span class="text-red-500">Rs. {{ number_format($sessionSummary['expenses'] ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="p-6 bg-[#e67e22] rounded-2xl flex items-center justify-between text-white shadow-xl shadow-orange-500/20">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em]">Liquid Cash in Hand</span>
                    <span class="text-2xl font-black tracking-tighter">Rs. {{ number_format($sessionSummary['expected_cash'] ?? 0, 2) }}</span>
                </div>
                <div class="pt-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Managerial Notes</label>
                    <textarea class="w-full p-3 bg-slate-50 border-2 border-slate-100 rounded-xl text-xs font-bold outline-none italic" rows="2" wire:model="closeRegisterNotes" placeholder="Log terminal anomalies..."></textarea>
                </div>
            </div>
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button class="flex-1 py-4 bg-white border border-slate-200 text-slate-400 font-black rounded-xl uppercase tracking-widest text-[10px] shadow-sm" wire:click="$set('showCloseRegisterModal', false)">Lock Review</button>
                <button class="flex-1 py-4 bg-slate-800 text-white font-black rounded-xl uppercase tracking-widest text-[10px] shadow-xl shadow-slate-200" wire:click="closeRegisterAndRedirect">Finalize Close</button>
            </div>
        </div>
        @endif

        {{-- SERVICE / GOLD PLATING MODAL --}}
        @if($showServiceModal)
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden relative transform transition-all">
            <div class="p-4 border-b border-amber-200 flex justify-between items-center bg-amber-500 text-slate-950">
                <h3 class="font-black text-xs uppercase tracking-widest flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">auto_fix_high</span>
                    NEW SERVICE / GOLD PLATING ORDER
                </h3>
                <button class="text-slate-800 hover:text-black transition-colors" wire:click="closeServiceModal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 max-h-[75vh] overflow-y-auto space-y-4">
                {{-- Customer Details --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 md:col-span-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Customer Name *</label>
                        <input type="text" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 outline-none focus:border-amber-500" wire:model="serviceCustomerName" placeholder="Enter customer name...">
                        @error('serviceCustomerName') <span class="text-red-500 text-[9px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Phone Number *</label>
                        <input type="text" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 outline-none focus:border-amber-500" wire:model="serviceCustomerPhone" placeholder="07xxxxxxxx">
                        @error('serviceCustomerPhone') <span class="text-red-500 text-[9px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Customer Address (Optional)</label>
                        <input type="text" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-800 outline-none focus:border-amber-500" wire:model="serviceCustomerAddress" placeholder="Address / Town">
                    </div>
                </div>

                {{-- Service & Item & Worker --}}
                <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Service Type *</label>
                        <select class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 outline-none focus:border-amber-500" wire:model="serviceType">
                            <option value="gold_plating">Gold Plating (Covering)</option>
                            <option value="cleaning">Jewelry Cleaning / Washing</option>
                            <option value="polishing">Polishing / Buffing</option>
                            <option value="repair">Soldering / Repairing</option>
                            <option value="other">Other Service</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Assign Workshop Worker</label>
                        <select class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 outline-none focus:border-amber-500" wire:model="serviceWorkerId">
                            <option value="">-- Worker (Ramesh / Sanker) --</option>
                            @foreach($platingWorkers ?? [] as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} (Payable: Rs. {{ number_format($w->outstanding_balance, 0) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-[10px] font-black text-slate-700 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-amber-600">format_list_bulleted</span>
                                Service Products & Fees (ITEMS & FEE) *
                            </label>
                            <button type="button" wire:click="addServiceItem" class="px-2.5 py-1 bg-amber-100 hover:bg-amber-200 text-amber-900 text-[10px] font-black rounded-lg transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">add</span> + Add Item
                            </button>
                        </div>

                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead class="bg-slate-100/80 border-b border-slate-200 text-[9px] uppercase font-black text-slate-600 tracking-wider">
                                    <tr>
                                        <th class="p-2 pl-3 w-8">#</th>
                                        <th class="p-2">ITEMS (Product Description) *</th>
                                        <th class="p-2 w-36 text-right">FEE (Rs.)</th>
                                        <th class="p-2 w-12 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($serviceItems as $idx => $item)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="p-2 pl-3 text-slate-400 font-bold text-[10px] align-middle">{{ $idx + 1 }}</td>
                                        <td class="p-2 align-middle">
                                            <input type="text" 
                                                class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-900 outline-none focus:border-amber-500 focus:bg-white"
                                                wire:model="serviceItems.{{ $idx }}.item_name"
                                                placeholder="e.g. kodi chain, ring, necklace...">
                                        </td>
                                        <td class="p-2 align-middle">
                                            <input type="number" step="0.01"
                                                class="w-full p-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-black text-amber-900 outline-none focus:border-amber-500 focus:bg-white text-right"
                                                wire:model.live="serviceItems.{{ $idx }}.fee"
                                                placeholder="0.00">
                                        </td>
                                        <td class="p-2 text-center align-middle">
                                            @if(count($serviceItems) > 1)
                                            <button type="button" wire:click="removeServiceItem({{ $idx }})" class="p-1 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Remove item">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                            @else
                                            <span class="text-slate-300 material-symbols-outlined text-sm">lock</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @error('serviceItems') <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Charges, Costs & Advance --}}
                <div class="p-4 bg-amber-50/60 border border-amber-200 rounded-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-900 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">payments</span> Pricing & Advance
                        </span>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="serviceIsChargeTbd" class="w-4 h-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-[10px] font-black uppercase tracking-tight text-amber-800">Charge T.B.D. (To Be Decided)</span>
                        </label>
                    </div>

                    @if($serviceIsChargeTbd)
                    <div class="p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-[10px] text-blue-800 font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">info</span>
                        <span>Total charge is marked as <strong>T.B.D.</strong> Waiting for workshop inspection. Receipt will print with T.B.D.</span>
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1 block">Customer Charge (Rs.)</label>
                            <input type="number" step="0.01" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-900 outline-none {{ $serviceIsChargeTbd ? 'opacity-50 cursor-not-allowed bg-slate-100' : 'focus:border-amber-500' }}" wire:model.live="serviceCustomerCharge" placeholder="{{ $serviceIsChargeTbd ? 'T.B.D.' : '0.00' }}" {{ $serviceIsChargeTbd ? 'disabled' : '' }}>
                            @error('serviceCustomerCharge') <span class="text-red-500 text-[9px] font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1 block">Workshop Cost (Rs.) <span class="text-slate-400 font-normal">(Internal)</span></label>
                            <input type="number" step="0.01" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-900 outline-none focus:border-amber-500" wire:model.live="serviceOurCost" placeholder="0.00">
                        </div>

                        <div>
                            <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1 block">Advance Amount (Rs.)</label>
                            <input type="number" step="0.01" class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-emerald-700 outline-none focus:border-emerald-500" wire:model.live="serviceAdvanceAmount" placeholder="0.00">
                            <span class="text-[8px] text-slate-500 mt-0.5 block">Recorded in Today's Cash Summary.</span>
                        </div>
                        <div>
                            <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1 block">Advance Payment Method</label>
                            <select class="w-full p-2.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-800 outline-none focus:border-amber-500" wire:model="servicePaymentMethod">
                                <option value="cash">Cash</option>
                                <option value="online">Online Transfer</option>
                                <option value="card">Card Payment</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Date & Notes --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Expected Delivery Date</label>
                        <input type="date" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 outline-none focus:border-amber-500" wire:model="serviceExpectedDeliveryDate">
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Notes / Instructions</label>
                        <input type="text" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-800 outline-none focus:border-amber-500" wire:model="serviceNotes" placeholder="Special instructions...">
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button type="button" class="flex-1 py-3 bg-white border border-slate-200 text-slate-500 font-black rounded-xl uppercase tracking-widest text-[10px] shadow-sm hover:bg-slate-100 transition-colors" wire:click="closeServiceModal">
                    Cancel
                </button>
                <button type="button" class="flex-1 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black rounded-xl uppercase tracking-widest text-[10px] shadow-lg shadow-amber-200 flex items-center justify-center gap-1.5 transition-all" wire:click="saveServiceOrder">
                    <span class="material-symbols-outlined text-sm">print</span>
                    Save & Print Receipt
                </button>
            </div>
        </div>
        @endif

        {{-- PRICE HELPER / QUICK PRICE GUIDE MODAL --}}
        @if($showPriceHelperModal)
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden relative transform transition-all border border-slate-100 animate-in fade-in zoom-in-95 duration-150">
            {{-- Header --}}
            <div class="p-4 border-b border-amber-200 flex justify-between items-center bg-amber-500 text-slate-950">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-slate-950/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">price_check</span>
                    </div>
                    <div>
                        <h3 class="font-black text-xs uppercase tracking-widest leading-none">PRICE HELPER GUIDE</h3>
                        <p class="text-[10px] text-slate-900/80 font-semibold mt-0.5">Quick reference price ranges for billing</p>
                    </div>
                </div>
                <button class="w-7 h-7 rounded-full bg-slate-950/10 hover:bg-slate-950/20 flex items-center justify-center text-slate-950 transition-colors" wire:click="closePriceHelperModal">
                    <span class="material-symbols-outlined text-base font-bold">close</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-5 max-h-[75vh] overflow-y-auto space-y-4 bg-slate-50/50">
                {{-- Search & Category Filter Bar --}}
                <div class="flex flex-col sm:flex-row items-center gap-2.5">
                    <div class="relative flex-1 w-full">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
                        <input type="text" wire:model.live.debounce.150ms="priceHelperSearch"
                            placeholder="Search price guide e.g. 5kan kodi..."
                            class="w-full pl-9 pr-8 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none text-xs font-semibold transition-all">
                        @if($priceHelperSearch)
                        <button type="button" wire:click="$set('priceHelperSearch', '')"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                        @endif
                    </div>

                    {{-- Category Filter Pills --}}
                    @if(count($this->priceHelperCategories) > 0)
                    <div class="flex items-center gap-1 overflow-x-auto max-w-full pb-1 sm:pb-0 shrink-0">
                        <button type="button"
                            wire:click="setPriceHelperCategory('all')"
                            class="px-2.5 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all {{ $priceHelperCategoryFilter === 'all' ? 'bg-[#161b97] text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}">
                            All
                        </button>
                        @foreach($this->priceHelperCategories as $cat)
                        <button type="button"
                            wire:click="setPriceHelperCategory('{{ $cat }}')"
                            class="px-2.5 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all {{ $priceHelperCategoryFilter === $cat ? 'bg-[#161b97] text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}">
                            {{ $cat }}
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Price Guides List --}}
                <div class="space-y-2">
                    @forelse($this->priceHelpersList as $phItem)
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 hover:border-amber-400 hover:shadow-sm transition-all flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-0.5">
                                <h4 class="text-xs font-black text-slate-800 capitalize truncate">{{ $phItem->item_name }}</h4>
                                @if($phItem->category)
                                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 font-black text-[8px] uppercase tracking-wider">
                                    {{ $phItem->category }}
                                </span>
                                @endif
                            </div>
                            @if($phItem->notes)
                            <p class="text-[10px] text-slate-500 line-clamp-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs text-slate-400">info</span>
                                {{ $phItem->notes }}
                            </p>
                            @endif
                        </div>

                        {{-- Price Range Box --}}
                        <div class="text-right shrink-0 bg-amber-50 border border-amber-200 rounded-xl px-3 py-1.5">
                            <span class="text-[8px] uppercase font-black tracking-widest text-amber-800 block">Price Range</span>
                            <span class="text-xs font-black text-slate-900 font-mono">
                                Rs. {{ number_format($phItem->min_price, 0) }} - {{ number_format($phItem->max_price, 0) }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center bg-white rounded-xl border border-dashed border-slate-200">
                        <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-2">
                            <span class="material-symbols-outlined text-xl">search_off</span>
                        </div>
                        <p class="text-xs font-bold text-slate-600">No price guide entries found</p>
                        @if($priceHelperSearch)
                        <p class="text-[10px] text-slate-400 mt-0.5">Try searching with a different term or clear the filter</p>
                        @endif
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-3 bg-white border-t border-slate-100 flex justify-between items-center text-[10px] text-slate-400 px-5">
                <span>Manage entries in <strong>Admin Settings &gt; Billing Price Guide</strong></span>
                <button class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black rounded-lg uppercase tracking-wider text-[10px] transition-colors"
                    wire:click="closePriceHelperModal">
                    Close
                </button>
            </div>
        </div>
        @endif
    </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('POS System Loaded');

            // Real-time Clock Implementation
            function updateClock() {
                const el = document.getElementById('posClock');
                if (!el) return;
                const now = new Date();
                el.innerText = now.getHours().toString().padStart(2, '0') + ':' +
                    now.getMinutes().toString().padStart(2, '0') + ':' +
                    now.getSeconds().toString().padStart(2, '0');
            }
            setInterval(updateClock, 1000);
            updateClock();

            // Native Post-load adjustments
            const initLayout = () => {
                const grid = document.getElementById('productGridContainer');
                if (grid) {
                    grid.style.scrollBehavior = 'smooth';
                }
            };
            initLayout();

            // Keyboard Logic
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    if (@json(count($cart)) > 0) {
                        @this.validateAndCreateSale();
                    }
                }
                if (e.key === 'F10') {
                    e.preventDefault();
                    @this.validateAndCreateSale();
                }
            });
        });

        // Print Invoice Function - Optimized for 80mm Thermal Printer
        function printInvoice() {
            console.log('=== Print Invoice Function Called ===');

            const printEl = document.getElementById('printableInvoice');
            if (!printEl) {
                console.error('ERROR: Printable invoice element not found');
                setTimeout(function() {
                    const retryEl = document.getElementById('printableInvoice');
                    if (retryEl) {
                        printInvoice();
                    } else {
                        alert('Invoice not ready for printing.');
                    }
                }, 1000);
                return;
            }

            // Get the receipt container
            const receiptContainer = printEl.querySelector('.receipt-container');
            if (!receiptContainer) {
                console.error('ERROR: Receipt container not found');
                alert('Invoice content not ready. Please try again.');
                return;
            }

            // Clone the content
            let content = receiptContainer.cloneNode(true);
            let htmlContent = content.outerHTML;

            // Open a new window with minimal size for thermal receipt
            const printWindow = window.open('', '_blank', 'width=350,height=600');

            if (!printWindow) {
                alert('Popup blocked. Please allow pop-ups for this site.');
                return;
            }

            // Complete HTML document with styles optimized for 80mm thermal printer
            const fullHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Receipt - Jaffna Gold</title>
                <style>
                    @page { 
                        size: 80mm auto; 
                        margin: 0mm; 
                    }
                    * {
                        box-sizing: border-box;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    html {
                        margin: 0;
                        padding: 0;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        width: 80mm;
                        background: #fff;
                        font-family: Arial, sans-serif; 
                        font-size: 11px;
                        color: #000;
                        font-weight: bold;
                    }
                    .receipt-container {
                        width: 80mm;
                        margin: 0;
                        padding: 3mm ;
                        box-sizing: border-box;
                    }
                    @media print {
                        html, body { margin: 0; padding: 0; width: 80mm; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                ${htmlContent}
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            window.onafterprint = function() { window.close(); };
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `;

            printWindow.document.open();
            printWindow.document.write(fullHtml);
            printWindow.document.close();
            printWindow.focus();
        }

        // Real-time instant discount calculation for POS cart
        function recalcInstantDiscount(input, cartKey) {
            if (!input) return;
            const raw = input.value.trim();
            const pct = Math.max(0, Math.min(100, parseFloat(raw) || 0));

            // 1. Instant styling update on input container
            const wrap = input.parentElement;
            if (wrap) {
                if (pct > 0) {
                    wrap.classList.remove('border-slate-200');
                    wrap.classList.add('border-emerald-300');
                    input.classList.remove('text-slate-500', 'bg-slate-50');
                    input.classList.add('text-emerald-600', 'bg-emerald-50');
                } else {
                    wrap.classList.remove('border-emerald-300');
                    wrap.classList.add('border-slate-200');
                    input.classList.remove('text-emerald-600', 'bg-emerald-50');
                    input.classList.add('text-slate-500', 'bg-slate-50');
                }
            }

            // 2. Instant row total recalculation
            const row = input.closest('tr');
            if (row) {
                const price = parseFloat(row.getAttribute('data-price')) || 0;
                const qty = parseFloat(row.getAttribute('data-qty')) || 0;
                const unitDisc = (price * pct) / 100;
                const rowTotal = Math.max(0, (price - unitDisc) * qty);
                const rowTotalEl = row.querySelector('.row-total-' + cartKey);
                if (rowTotalEl) {
                    rowTotalEl.textContent = 'Rs. ' + Math.round(rowTotal).toLocaleString('en-US');
                }
            }

            // 3. Instant footer unit discount & grand total recalculation
            let totalUnitDiscount = 0;
            let totalOriginalSubtotal = 0;
            document.querySelectorAll('tr[data-price]').forEach(function(r) {
                const p = parseFloat(r.getAttribute('data-price')) || 0;
                const q = parseFloat(r.getAttribute('data-qty')) || 0;
                const discInput = r.querySelector('.cart-discount-input');
                const dVal = discInput ? discInput.value.trim() : '0';
                const dPct = Math.max(0, Math.min(100, parseFloat(dVal) || 0));
                totalOriginalSubtotal += p * q;
                totalUnitDiscount += ((p * dPct) / 100) * q;
            });

            const subtotalAfterItemDiscounts = Math.max(0, totalOriginalSubtotal - totalUnitDiscount);

            // Check global discount if any
            let globalAmount = 0;
            const globalInput = document.querySelector('input[wire\\:model\\.live\\.debounce\\.250ms="additionalDiscount"]');
            const gVal = parseFloat(globalInput ? globalInput.value : 0) || 0;
            if (gVal > 0) {
                const isPct = globalInput.closest('.space-y-2')?.querySelector('button.bg-\\[\\#161b97\\]')?.textContent.trim() === '%';
                if (isPct) {
                    globalAmount = (subtotalAfterItemDiscounts * Math.min(100, gVal)) / 100;
                } else {
                    globalAmount = Math.min(gVal, subtotalAfterItemDiscounts);
                }
            }

            const grandTotal = Math.max(0, subtotalAfterItemDiscounts - globalAmount);

            const unitDiscEl = document.querySelector('.pos-unit-discount');
            if (unitDiscEl) {
                unitDiscEl.textContent = '- Rs. ' + totalUnitDiscount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            const grandTotalEl = document.querySelector('.pos-grand-total');
            if (grandTotalEl) {
                grandTotalEl.textContent = 'Rs. ' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
        window.recalcInstantDiscount = recalcInstantDiscount;

        // Make printInvoice available globally
        window.printInvoice = printInvoice;
        console.log('printInvoice function registered globally');
    </script>
</div>
<script>
    // Enforce uniform product and cart image sizes after DOM loads
    document.addEventListener('DOMContentLoaded', function() {
        // Product grid images inside the aspect container: fill and crop
        document.querySelectorAll('.aspect-[4/3] img').forEach(function(img) {
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.objectPosition = 'center';
        });

        // Cart thumbnails: fixed square size
        document.querySelectorAll('table tbody img').forEach(function(img) {
            img.style.width = '32px';
            img.style.height = '32px';
            img.style.objectFit = 'cover';
            img.style.objectPosition = 'center';
        });
    });
</script>
