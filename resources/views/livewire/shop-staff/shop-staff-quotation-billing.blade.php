<div class="pos-billing-terminal" wire:poll.10s>
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
                --accent-50: #f0f2ff;
                --accent-100: #e0e4ff;
                --accent-300: #4361ee;
                --accent-500: #161b97;
                --accent-700: #12167d;
                --bg-pos: #fcfcfc;
                --muted: #64748b;
            }

            html, body { background: var(--bg-pos); font-family: 'Inter', sans-serif; color: #0f172a; }

            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 12px; }

            .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24; }

            .pos-billing-terminal header { background: linear-gradient(90deg, var(--accent-50), #ffffff); border-bottom: 1px solid rgba(0,0,0,0.04); }
            .pos-billing-terminal header .bg-blue-gradient { background: linear-gradient(135deg,var(--accent-500),var(--accent-700)); box-shadow: 0 6px 18px rgba(22,27,151,0.12); }

            .btn-accent { background: linear-gradient(90deg,var(--accent-500),var(--accent-700)); color: white; }
            .btn-accent:hover { filter: brightness(.95); }

            .product-card { @apply bg-white rounded-xl border border-slate-100 overflow-hidden shadow-md; }
            .product-card .card-body { @apply p-4; }
            .product-card img, .product-grid-img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
            .cart-thumb { width: 32px; height: 32px; object-fit: cover; object-position: center; border-radius: 6px; }
            .product-card .price { color: var(--accent-700); font-weight: 800; }

            .badge-instock { background: linear-gradient(90deg,#10b981,#06b6d4); color: white; font-weight: 700; padding: .25rem .5rem; border-radius: .375rem; font-size: .625rem; }

            .search-input { @apply w-full rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm; }

            .cart-empty { color: var(--muted); }
            .cart-row { @apply transition-shadow; }
            .cart-row:hover { box-shadow: 0 6px 18px rgba(15,23,42,0.03); }

            .group\/thumb > button { opacity: 1 !important; transform: translate(0,0) !important; }

            .qty-btn { @apply w-6 h-6 rounded-full text-[10px] font-bold bg-slate-100 border border-slate-200 flex items-center justify-center; }

            .add-btn { @apply w-9 h-9 rounded-lg bg-white border border-slate-200 shadow-sm flex items-center justify-center; }
            .add-btn:hover { transform: translateY(-3px); transition: transform .15s ease; }

            .receipt-container { border-radius: 12px; border: 1px solid rgba(15,23,42,0.04); }

            .pos-footer .btn { @apply rounded-lg px-6 py-3 font-black; }
            .pos-footer .btn-primary { background: linear-gradient(90deg,var(--accent-500),var(--accent-700)); color: white; }
        }
    </style>

    <div class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden text-sm">

        <!-- Header Section -->
        <header class="bg-white border-b border-slate-200 px-3 py-0 flex items-center justify-between shadow-sm shrink-0">
            <div class="flex items-center gap-2">
                <div class="flex items-center">
                    <img src="{{ asset('images/jg.png') }}" class="h-20 w-auto" alt="Logo">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a class="flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 transition-colors text-xs font-bold text-slate-600 border border-slate-200"
                    href="{{ route('shop-staff.quotation-list') }}">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    QUOTATIONS LIST
                </a>
                <div class="bg-slate-100 px-3 py-1 rounded border border-slate-200">
                    <span class="font-mono text-base font-bold text-[#161b97] tracking-widest" id="posClock">00:00:00</span>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex flex-1 overflow-hidden p-3 gap-3">

            <!-- LEFT SECTION: Search & Cart (50%) -->
            <aside class="w-1/2 flex flex-col bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                {{-- Search Bar --}}
                <div class="p-3 border-b border-slate-100 bg-slate-50/50" x-data="{ highlightIndex: -1 }"
                    x-on:product-added-to-cart.window="
                         highlightIndex = -1;
                         $nextTick(() => {
                             if ($refs.searchInput) { $refs.searchInput.focus(); $refs.searchInput.select(); }
                         })
                     " x-init="$nextTick(() => { if ($refs.searchInput) $refs.searchInput.focus(); })">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                        <input class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-md focus:ring-2 focus:ring-[#161b97]/20 focus:border-[#161b97] outline-none text-sm transition-all"
                            x-ref="searchInput" wire:model.live.debounce.300ms="search"
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
                                    $wire.handleBarcodeOrSearch($el.value);
                                }
                            " x-on:keydown.escape.prevent="
                                highlightIndex = -1;
                                $wire.set('search', '');
                            " x-on:input="highlightIndex = -1">

                        <!-- Search Dropdown -->
                        @if($search && count($searchResults) > 0)
                        <div class="absolute w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-2xl z-50 max-h-96 overflow-y-auto custom-scrollbar">
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
                                    </p>
                                </div>
                                <span class="text-xs font-black text-[#161b97]">
                                    Rs. {{ number_format(match ($priceType) {
                                        'wholesale' => $res['wholesale_price'] ?? $res['price'] ?? 0,
                                        'distribute' => $res['distribute_price'] ?? $res['price'] ?? 0,
                                        default => $res['retail_price'] ?? $res['price'] ?? 0
                                    }, 2) }}
                                </span>
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

                {{-- Cart Header --}}
                <div class="flex items-center justify-between px-3 py-1.5 border-b border-slate-200 bg-white">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#161b97] text-base">description</span>
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Quotation Items</span>
                        @if(count($cart) > 0)
                        <span class="bg-[#161b97] text-white text-[8px] font-black px-2 py-0.5 rounded-full">{{ count($cart) }}</span>
                        @endif
                    </div>
                    <button class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 hover:border-amber-300 transition-all text-[9px] font-black text-amber-700 uppercase tracking-tight shadow-sm"
                        wire:click="addCustomProduct">
                        <span class="material-symbols-outlined text-sm">add_circle</span>
                        Custom Product
                    </button>
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
                            <tr class="group hover:bg-amber-50/30 transition-colors border-b border-slate-100" wire:key="cart-{{ $cartKey }}">
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        @if(!empty($item['is_custom']))
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
                                                value="{{ $item['code'] }}"
                                                wire:change="updateCustomCode('{{ $cartKey }}', $event.target.value)"
                                                placeholder="Product code...">
                                            <span class="inline-flex items-center gap-0.5 mt-0.5 bg-amber-100 text-amber-700 text-[7px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider">
                                                <span class="material-symbols-outlined text-[9px]">edit_square</span>
                                                CUSTOM
                                            </span>
                                        </div>
                                        @else
                                        <img src="{{ $this->getImageUrl($item['image']) }}"
                                            onerror="this.onerror=null;this.src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSrn_80I-lMAa0pVBNmFmQ7VI6l4rr74JW-eQ&s';"
                                            class="w-8 h-8 rounded-md border border-slate-200 object-cover shrink-0">
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-bold text-slate-700 truncate max-w-[120px]" title="{{ $item['name'] }}">{{ $item['name'] }}</h4>
                                            @if(($item['total'] / $item['quantity']) >= $warrantyThreshold)
                                            <p class="text-[9px] text-emerald-600 font-bold italic">6-Month Warranty</p>
                                            @endif
                                            <p class="text-[9px] text-slate-400 font-mono">{{ $item['code'] }}@if(!empty($item['barcode'])) | <span class="text-slate-500 font-semibold">BC: {{ $item['barcode'] }}</span>@endif</p>
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
                                            class="w-24 h-7 text-right text-xs font-bold bg-slate-50 border border-slate-200 rounded px-2 focus:outline-none focus:border-[#161b97] transition-colors" />
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @php
                                    $discountPercent = $item['discount_percentage'] ?? 0;
                                    $discountNumericValue = $discountPercent > 0 ? rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.') : '';
                                    @endphp
                                    <div class="flex flex-col items-center gap-0.5"
                                        x-data="{
                                                val: '{{ $discountNumericValue }}',
                                                apply() {
                                                    const raw = this.val === '' ? '0' : this.val;
                                                    $wire.updateDiscount('{{ $cartKey }}', raw + '%');
                                                }
                                            }"
                                        x-init="val = '{{ $discountNumericValue }}'"
                                        wire:key="disc-wrap-{{ $cartKey }}">
                                        <div class="flex items-center h-7 rounded overflow-hidden border"
                                            :class="val > 0 ? 'border-emerald-300' : 'border-slate-200'">
                                            <input type="number"
                                                step="any"
                                                min="0"
                                                max="100"
                                                x-model="val"
                                                @input.debounce.250ms="apply()"
                                                @change="apply()"
                                                @keydown.enter.prevent="apply()"
                                                placeholder="0"
                                                class="w-14 h-full px-2 text-[10px] font-bold text-center focus:outline-none transition-all border-0 bg-transparent"
                                                :class="val > 0 ? 'text-emerald-600 bg-emerald-50' : 'text-slate-500 bg-slate-50'" />
                                            <div class="h-full px-1.5 text-[8px] font-black border-l transition-colors whitespace-nowrap bg-emerald-500 text-white border-emerald-400 flex items-center">
                                                %
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <p class="text-sm font-black text-slate-800">Rs. {{ number_format($item['total'], 0) }}</p>
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
                                    <p class="text-slate-400 font-black uppercase tracking-widest text-[10px]">Quotation cart is empty</p>
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
                            $totalDiscountRs = max(0, $unitDiscount + $additionalDiscountAmount);
                            $totalDiscountPercent = $subtotal > 0 ? (($totalDiscountRs / $subtotal) * 100) : 0;
                            @endphp
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-semibold">Subtotal</span>
                                <span class="font-bold text-slate-700">Rs. {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-semibold">Unit Discount</span>
                                <span class="font-bold text-red-500">- Rs. {{ number_format($unitDiscount, 2) }}</span>
                            </div>

                            @if($additionalDiscountAmount > 0)
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-semibold">Global Discount</span>
                                <span class="font-bold text-amber-600">
                                    - Rs. {{ number_format($additionalDiscountAmount, 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-semibold">Total Discount</span>
                                <span class="font-bold text-red-500">- Rs. {{ number_format($totalDiscountRs, 2) }} <span class="text-slate-400 font-semibold">({{ number_format($totalDiscountPercent, 2) }}%)</span></span>
                            </div>
                            @endif
                        </div>
                        <div>
                            <div class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs text-[#161b97]">sell</span>
                                        Global Discount
                                    </span>
                                    <div class="inline-flex p-0.5 bg-slate-100 rounded-md border border-slate-200 text-[9px] font-black">
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
                                        class="w-full pl-9 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-black text-slate-800 text-right focus:bg-white focus:border-[#161b97] outline-none transition-all" />
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
                    </div>
                    <div class="grid grid-cols-2 gap-4 items-center border-t border-slate-200 pt-4">
                        <div class="flex justify-between items-baseline">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Grand Total</span>
                            <span class="text-3xl font-black text-[#161b97] tracking-tighter">Rs. {{ number_format($grandTotal, 2) }}</span>
                        </div>
                        <button class="w-full bg-[#161b97] hover:bg-blue-800 text-white font-black py-3 rounded-lg flex items-center justify-center gap-2 shadow-xl shadow-blue-500/20 transition-all text-xs uppercase tracking-widest disabled:opacity-40 disabled:grayscale disabled:cursor-not-allowed group"
                            wire:click="createQuotation" {{ count($cart) == 0 ? 'disabled' : '' }}>
                            <span class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform">check_circle</span>
                            Create Quotation
                        </button>
                    </div>
                </div>
            </aside>

            <!-- RIGHT SECTION: Selections & Product Grid (50%) -->
            <section class="w-1/2 flex flex-col gap-3 overflow-hidden">
                {{-- Selection Box (Customer/Price) --}}
                <div class="bg-white p-2 rounded-lg shadow-sm border border-slate-200 space-y-1.5">
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block tracking-widest">Customer Selection</label>
                            <div class="relative group">
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg group-focus-within:text-[#161b97] transition-colors">person</span>
                                <select class="w-full pl-9 pr-10 py-2 bg-slate-50 border border-slate-200 rounded-md outline-none text-xs font-bold appearance-none focus:ring-2 focus:ring-[#161b97]/10 focus:border-[#161b97] transition-all" wire:model.live="customerId">
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->business_name ?? $customer->name }} ({{ $customer->phone }})</option>
                                    @endforeach
                                </select>
                                <button class="absolute right-8 top-1/2 -translate-y-1/2 text-[#161b97] p-1.5 hover:bg-blue-50 rounded-full transition-all" wire:click="openCustomerModal" title="Add Customer">
                                    <span class="material-symbols-outlined text-lg">person_add</span>
                                </button>
                                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-300 text-lg pointer-events-none">expand_more</span>
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

                    <div class="flex gap-2">
                        <div class="flex-1">
                            <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block tracking-widest">Valid Until Date</label>
                            <div class="relative">
                                <input type="date" class="w-full py-1.5 bg-slate-50 border border-slate-200 rounded px-2.5 text-xs font-bold focus:border-[#161b97] outline-none" wire:model="validUntil" />
                            </div>
                        </div>
                    </div>

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

                    @if($selectedCategory || $selectedBrand)
                    <div class="flex items-center gap-2 pt-1 border-t border-slate-100">
                        <span class="text-[9px] text-slate-400 font-bold">Active Filter:</span>
                        @if($selectedCategory)
                            @php $catObj = $categories->firstWhere('id', $selectedCategory); @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-50 text-[#161b97] border border-blue-200">
                                {{ $catObj->category_name ?? 'Category' }}
                                <button type="button" wire:click="showAllProducts" class="hover:text-red-500 font-black">×</button>
                            </span>
                        @endif
                        @if($selectedBrand)
                            @php $brandObj = $brands->firstWhere('id', $selectedBrand); @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-50 text-[#161b97] border border-blue-200">
                                {{ $brandObj->brand_name ?? 'Brand' }}
                                <button type="button" wire:click="showAllBrands" class="hover:text-red-500 font-black">×</button>
                            </span>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Product Grid Area: Clean Scanner Status (Matches POS Store Billing) --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar pr-1 flex items-center justify-center bg-white rounded-lg border border-slate-200 shadow-sm">
                    <div class="py-24 text-center px-6">
                        <div class="bg-slate-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 shadow-inner">
                            <span class="material-symbols-outlined text-5xl text-slate-300">barcode_scanner</span>
                        </div>
                        <h4 class="text-slate-600 font-black uppercase tracking-wider text-xs">Ready for Barcode or Product Code</h4>
                        <p class="text-slate-400 text-xs mt-1 max-w-sm mx-auto">
                            Scan a product barcode or enter a product code in the search bar on the left to add items directly to this quotation.
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

    {{-- Sliding Category Sidebar --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[2000] transition-opacity duration-300 {{ $showCategoryPanel ? 'opacity-100' : 'opacity-0 pointer-events-none' }}" wire:click.self="$set('showCategoryPanel', false)"></div>
    <aside class="fixed right-0 top-0 bottom-0 w-1/2 bg-white z-[2001] shadow-2xl transition-transform duration-300 transform {{ $showCategoryPanel ? 'translate-x-0' : 'translate-x-full' }} flex flex-col">
        <div class="p-4 flex justify-between items-center border-b border-slate-100 bg-slate-50">
            <h6 class="mb-0 font-black text-xs text-slate-800 tracking-widest"><i class="material-symbols-outlined align-middle mr-2 text-[#161b97]">grid_view</i>ALL CATEGORIES</h6>
            <button class="text-slate-400 hover:text-slate-600 transition-colors" wire:click="$set('showCategoryPanel', false)">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-2 overflow-y-auto flex-1 custom-scrollbar">
            <button class="mb-1 w-full text-left p-3 rounded-lg transition-all border border-slate-100 {{ !$selectedCategory ? 'bg-[#161b97] text-white shadow-lg shadow-blue-500/30' : 'hover:bg-slate-100 text-slate-600' }}"
                wire:click="showAllProducts">
                <span class="font-black text-xs tracking-tight">Show All Items</span>
            </button>
            @foreach($categories as $category)
            <button class="mb-1 w-full text-left p-3 rounded-lg transition-all border border-slate-100 {{ $selectedCategory == $category->id ? 'bg-[#161b97] text-white shadow-lg shadow-blue-500/30' : 'hover:bg-slate-100 text-slate-600' }}"
                wire:click="selectCategory({{ $category->id }})">
                <span class="font-black text-xs tracking-tight">{{ $category->category_name }}</span>
                <span class="text-[10px] font-bold opacity-70">({{ $category->products_count }})</span>
            </button>
            @endforeach
        </div>
    </aside>

    {{-- Sliding Brand Sidebar --}}
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[2000] transition-opacity duration-300 {{ $showBrandPanel ? 'opacity-100' : 'opacity-0 pointer-events-none' }}" wire:click.self="$set('showBrandPanel', false)"></div>
    <aside class="fixed right-0 top-0 bottom-0 w-1/2 bg-white z-[2001] shadow-2xl transition-transform duration-300 transform {{ $showBrandPanel ? 'translate-x-0' : 'translate-x-full' }} flex flex-col">
        <div class="p-4 flex justify-between items-center border-b border-slate-100 bg-slate-50">
            <h6 class="mb-0 font-black text-xs text-slate-800 tracking-widest"><i class="material-symbols-outlined align-middle mr-2 text-[#161b97]">local_offer</i>ALL BRANDS</h6>
            <button class="text-slate-400 hover:text-slate-600 transition-colors" wire:click="$set('showBrandPanel', false)">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-2 overflow-y-auto flex-1 custom-scrollbar">
            <button class="mb-1 w-full text-left p-3 rounded-lg transition-all border border-slate-100 {{ !$selectedBrand ? 'bg-[#161b97] text-white shadow-lg shadow-blue-500/30' : 'hover:bg-slate-100 text-slate-600' }}"
                wire:click="showAllBrands">
                <span class="font-black text-xs tracking-tight">Show All Brands</span>
            </button>
            @foreach($brands as $brand)
            <button class="mb-1 w-full text-left p-3 rounded-lg transition-all border border-slate-100 {{ $selectedBrand == $brand->id ? 'bg-[#161b97] text-white shadow-lg shadow-blue-500/30' : 'hover:bg-slate-100 text-slate-600' }}"
                wire:click="selectBrand({{ $brand->id }})">
                <span class="font-black text-xs tracking-tight">{{ $brand->brand_name }}</span>
                <span class="text-[10px] font-bold opacity-70">({{ $brand->products_count }})</span>
            </button>
            @endforeach
        </div>
    </aside>

    {{-- Customer Modal --}}
    @if($showCustomerModal)
    <div class="fixed inset-0 z-[5000] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative z-10">
            <div class="bg-[#161b97] px-6 py-4 flex justify-between items-center text-white">
                <h3 class="font-black text-xs uppercase tracking-widest">Add New Customer</h3>
                <button class="text-white/60 hover:text-white" wire:click="closeCustomerModal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 space-y-3">
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Customer Name *</label>
                    <input type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:border-[#161b97] outline-none"
                        wire:model="customerName" placeholder="Enter name">
                    @error('customerName') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Phone</label>
                        <input type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:border-[#161b97] outline-none"
                            wire:model="customerPhone" placeholder="Phone">
                        @error('customerPhone') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Email</label>
                        <input type="email" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:border-[#161b97] outline-none"
                            wire:model="customerEmail" placeholder="Email">
                    </div>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Address *</label>
                    <input type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:border-[#161b97] outline-none"
                        wire:model="customerAddress" placeholder="Address">
                    @error('customerAddress') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Type *</label>
                        <select class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:border-[#161b97] outline-none"
                            wire:model="customerType">
                            <option value="retail">Retail</option>
                            <option value="wholesale">Wholesale</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Business Name</label>
                        <input type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:border-[#161b97] outline-none"
                            wire:model="businessName" placeholder="Business Name">
                    </div>
                </div>
            </div>
            <div class="px-6 pb-6 flex gap-3">
                <button class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black rounded-xl text-xs uppercase tracking-widest"
                    wire:click="closeCustomerModal">Cancel</button>
                <button class="flex-1 py-3 bg-[#161b97] hover:bg-blue-800 text-white font-black rounded-xl text-xs uppercase tracking-widest"
                    wire:click="createCustomer">Save Customer</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Quotation Print Modal --}}
    @if($showQuotationModal && $createdQuotation)
    <div class="fixed inset-0 z-[4000] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden relative z-10 flex flex-col max-h-[90vh]">
            <div class="bg-gradient-to-r from-[#161b97] to-[#12167d] px-5 py-4 text-white flex justify-between items-center shrink-0">
                <div>
                    <h3 class="font-black text-xs uppercase tracking-widest">Quotation Created</h3>
                    <p class="text-[9px] text-blue-200 font-bold uppercase tracking-wider mt-0.5">{{ $createdQuotation->quotation_number }}</p>
                </div>
                <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-slate-400 hover:text-white"
                    wire:click="createNewQuotation">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>

            <!-- Quotation Receipt Render (Thermal Print style) -->
            <div class="flex-1 overflow-y-auto p-4 bg-slate-100 flex justify-center" id="printableQuotation">
                <div class="receipt-container" style="width: 76mm; padding: 4mm; background: white; color: #000; line-height: 1.2; font-size: 12px; box-sizing: border-box; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <div style="text-align:center; font-weight:bold;">
                        <div style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 5px 0; margin-bottom: 3px; font-size: 16px; text-transform: uppercase;">
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

                    <div style="font-size: 11px; font-weight: bold;">
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 85px;">Branch</span>
                            <span>: {{ config('shop.branch', 'Vavuniya Branch') }}</span>
                        </div>
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 85px;">Quotation No</span>
                            <span>: {{ $createdQuotation->quotation_number }}</span>
                        </div>
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 85px;">Date</span>
                            <span>: {{ $createdQuotation->created_at->format('d-m-Y') }}</span>
                        </div>
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 85px;">Cashier</span>
                            <span>: {{ Auth::user()->name }}</span>
                        </div>
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="width: 85px;">Valid Until</span>
                            <span>: {{ \Carbon\Carbon::parse($createdQuotation->valid_until)->format('d-m-Y') }}</span>
                        </div>
                    </div>

                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

                    <div style="display: flex; font-weight: bold; font-size: 12px; margin-bottom: 2px;">
                        <span style="flex: 2;">Item</span>
                        <span style="flex: 0.5; text-align: center;">Qty</span>
                        <span style="flex: 1; text-align: right;">Price</span>
                    </div>
                    <div style="border-bottom: 1px dotted #000; margin-bottom: 5px;"></div>

                    <div style="font-size: 11px;">
                        @foreach($createdQuotation->items as $item)
                        <div style="display: flex; margin-bottom: 2px;">
                            <span style="flex: 2;font-weight: bold">{{ $item['product_name'] }}</span>
                            <span style="flex: 0.5; text-align: center;">{{ $item['quantity'] }}</span>
                            <span style="flex: 1; text-align: right;">{{ number_format($item['unit_price'], 0) }}</span>
                        </div>
                        @if(($item['discount_per_unit'] ?? 0) > 0)
                        <div style="display: flex; font-size: 11px; color: #000; font-weight: bold; margin-top: 1px; margin-bottom: 3px;">
                            <span style="flex: 2; padding-left: 5px;">
                                Disc: {{ number_format($item['discount_per_unit'], 0) }}
                            </span>
                        </div>
                        @endif
                        @endforeach
                    </div>

                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

                    <div style="font-size: 11px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-weight: bold;">
                            <span>Sub Total</span>
                            <span>{{ number_format($createdQuotation->subtotal, 0) }}</span>
                        </div>
                        @if($createdQuotation->discount_amount > 0)
                        <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-weight: bold;">
                            <span>Discount</span>
                            <span>-{{ number_format($createdQuotation->discount_amount, 0) }}</span>
                        </div>
                        @endif

                        <div style="border-bottom: 1px dashed #000; margin: 5px 0;"></div>

                        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px;">
                            <span>TOTAL</span>
                            <span>{{ number_format($createdQuotation->total_amount, 0) }}</span>
                        </div>
                    </div>

                    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

                    <div style="text-align: center; font-size: 12px; font-weight: bold; border: 1px solid #000; padding: 4px; margin: 5px 0;">
                        THIS IS A QUOTATION ONLY
                    </div>

                    <div style="text-align: center; font-size: 11px; margin-top: 10px;">
                        <div style="font-weight: bold;">Thank You for Requesting a Quote!</div>
                        <div style="font-weight: bold;">Visit Us Again</div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex justify-center gap-3 shrink-0">
                <button class="px-6 py-2.5 bg-white border-2 border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-colors"
                    wire:click="createNewQuotation">Close &amp; New</button>
                <button class="px-8 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-slate-300 flex items-center gap-2 transition-colors"
                    onclick="window.open('/shop-staff/print/quotation/{{ $createdQuotation->id }}', '_blank', 'width=350,height=600')">
                    <span class="material-symbols-outlined text-base">print</span> Print Quotation
                </button>
                <button class="px-8 py-2.5 bg-[#161b97] hover:bg-yellow-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-yellow-500/20 transition-colors"
                    wire:click="downloadQuotation">Download PDF</button>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Uniform sizes inside grid
        document.querySelectorAll('.aspect-\\[4\\/3\\] img').forEach(function(img) {
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.objectPosition = 'center';
        });
    });
</script>
