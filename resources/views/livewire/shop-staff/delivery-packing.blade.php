<div wire:keydown.escape.window="closeModal">
    <!-- Load TailWind & Premium Fonts -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <style>
        :root {
            --accent-500: #161b97;
            --accent-700: #12167d;
        }

        html,
        body {
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 12px;
        }

        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-card.active {
            transform: translateY(-2px);
        }

        .order-card {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        .pulse-dot {
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.4s ease-out both;
        }

        .stagger-1 {
            animation-delay: 0.05s;
        }

        .stagger-2 {
            animation-delay: 0.1s;
        }

        .stagger-3 {
            animation-delay: 0.15s;
        }

        .stagger-4 {
            animation-delay: 0.2s;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-scale-in {
            animation: scaleIn 0.25s ease-out both;
        }
    </style>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50/30">

        {{-- ═══════════════ HEADER ═══════════════ --}}
        <div class="bg-white/80 backdrop-blur-xl border-b border-slate-200/60 sticky top-0 z-40">
            <div class="max-w-8xl mx-auto px-6 sm:px-8 lg:px-12 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#161b97] to-[#4361ee] flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <span class="material-symbols-outlined text-white text-xl">local_shipping</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-black text-slate-800 tracking-tight">Delivery Packing</h1>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Manage &
                                pack delivery orders</p>
                        </div>
                    </div>
                    <div
                        class="bg-slate-100/80 border border-slate-200/60 px-4 py-2 rounded-xl flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-400 text-base">calendar_today</span>
                        <span class="text-xs font-bold text-slate-600">{{ now()->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-8xl mx-auto px-6 sm:px-8 lg:px-12 py-6">

            {{-- ═══════════════ SUCCESS ALERT ═══════════════ --}}
            @if (session()->has('success'))
                <div
                    class="mb-6 flex items-center gap-3 px-5 py-4 bg-emerald-50 border border-emerald-200/60 rounded-2xl shadow-sm animate-fade-in">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-white text-lg">check_circle</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- ═══════════════ STATS OVERVIEW (5 Tabs) ═══════════════ --}}
            <div class="grid grid-cols-5 gap-3 mb-6">
                {{-- All --}}
                <button wire:click="setFilter('all')"
                    class="stat-card group rounded-2xl border p-3 sm:p-4 text-left {{ $filterStatus === 'all' ? 'bg-gradient-to-br from-[#161b97] to-[#4361ee] border-transparent shadow-xl shadow-blue-500/20 active' : 'bg-white border-slate-200/60 hover:border-blue-200 hover:shadow-lg' }}">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center {{ $filterStatus === 'all' ? 'bg-white/15' : 'bg-blue-50' }} transition-colors shrink-0">
                            <span
                                class="material-symbols-outlined {{ $filterStatus === 'all' ? 'text-white' : 'text-[#161b97]' }} text-base sm:text-lg">inventory_2</span>
                        </div>
                        <div>
                            <p
                                class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest {{ $filterStatus === 'all' ? 'text-blue-200' : 'text-slate-400' }}">
                                All</p>
                            <p
                                class="text-xl sm:text-2xl font-black {{ $filterStatus === 'all' ? 'text-white' : 'text-slate-800' }}">
                                {{ $totalCount }}</p>
                        </div>
                    </div>
                </button>

                {{-- Pending --}}
                <button wire:click="setFilter('pending')"
                    class="stat-card group rounded-2xl border p-3 sm:p-4 text-left {{ $filterStatus === 'pending' ? 'bg-gradient-to-br from-amber-500 to-orange-500 border-transparent shadow-xl shadow-amber-500/20 active' : 'bg-white border-slate-200/60 hover:border-amber-200 hover:shadow-lg' }}">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center {{ $filterStatus === 'pending' ? 'bg-white/15' : 'bg-amber-50' }} transition-colors shrink-0 relative">
                            <span
                                class="material-symbols-outlined {{ $filterStatus === 'pending' ? 'text-white' : 'text-amber-500' }} text-base sm:text-lg">pending_actions</span>
                            @if($pendingCount > 0 && $filterStatus !== 'pending')
                                <span
                                    class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-amber-500 pulse-dot"></span>
                            @endif
                        </div>
                        <div>
                            <p
                                class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest {{ $filterStatus === 'pending' ? 'text-amber-100' : 'text-slate-400' }}">
                                Pending</p>
                            <p
                                class="text-xl sm:text-2xl font-black {{ $filterStatus === 'pending' ? 'text-white' : 'text-slate-800' }}">
                                {{ $pendingCount }}</p>
                        </div>
                    </div>
                </button>

                {{-- Packed --}}
                <button wire:click="setFilter('packed')"
                    class="stat-card group rounded-2xl border p-3 sm:p-4 text-left {{ $filterStatus === 'packed' ? 'bg-gradient-to-br from-violet-500 to-purple-600 border-transparent shadow-xl shadow-violet-500/20 active' : 'bg-white border-slate-200/60 hover:border-violet-200 hover:shadow-lg' }}">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center {{ $filterStatus === 'packed' ? 'bg-white/15' : 'bg-violet-50' }} transition-colors shrink-0">
                            <span
                                class="material-symbols-outlined {{ $filterStatus === 'packed' ? 'text-white' : 'text-violet-500' }} text-base sm:text-lg">package_2</span>
                        </div>
                        <div>
                            <p
                                class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest {{ $filterStatus === 'packed' ? 'text-violet-100' : 'text-slate-400' }}">
                                Packed</p>
                            <p
                                class="text-xl sm:text-2xl font-black {{ $filterStatus === 'packed' ? 'text-white' : 'text-slate-800' }}">
                                {{ $packedCount }}</p>
                        </div>
                    </div>
                </button>

                {{-- Delivered --}}
                <button wire:click="setFilter('delivered')"
                    class="stat-card group rounded-2xl border p-3 sm:p-4 text-left {{ $filterStatus === 'delivered' ? 'bg-gradient-to-br from-sky-500 to-blue-600 border-transparent shadow-xl shadow-sky-500/20 active' : 'bg-white border-slate-200/60 hover:border-sky-200 hover:shadow-lg' }}">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center {{ $filterStatus === 'delivered' ? 'bg-white/15' : 'bg-sky-50' }} transition-colors shrink-0">
                            <span
                                class="material-symbols-outlined {{ $filterStatus === 'delivered' ? 'text-white' : 'text-sky-500' }} text-base sm:text-lg">local_shipping</span>
                        </div>
                        <div>
                            <p
                                class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest {{ $filterStatus === 'delivered' ? 'text-sky-100' : 'text-slate-400' }}">
                                Delivered</p>
                            <p
                                class="text-xl sm:text-2xl font-black {{ $filterStatus === 'delivered' ? 'text-white' : 'text-slate-800' }}">
                                {{ $deliveredCount }}</p>
                        </div>
                    </div>
                </button>

                {{-- Completed --}}
                <button wire:click="setFilter('completed')"
                    class="stat-card group rounded-2xl border p-3 sm:p-4 text-left {{ $filterStatus === 'completed' ? 'bg-gradient-to-br from-emerald-500 to-teal-500 border-transparent shadow-xl shadow-emerald-500/20 active' : 'bg-white border-slate-200/60 hover:border-emerald-200 hover:shadow-lg' }}">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center {{ $filterStatus === 'completed' ? 'bg-white/15' : 'bg-emerald-50' }} transition-colors shrink-0">
                            <span
                                class="material-symbols-outlined {{ $filterStatus === 'completed' ? 'text-white' : 'text-emerald-500' }} text-base sm:text-lg">task_alt</span>
                        </div>
                        <div>
                            <p
                                class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest {{ $filterStatus === 'completed' ? 'text-emerald-100' : 'text-slate-400' }}">
                                Completed</p>
                            <p
                                class="text-xl sm:text-2xl font-black {{ $filterStatus === 'completed' ? 'text-white' : 'text-slate-800' }}">
                                {{ $completedCount }}</p>
                        </div>
                    </div>
                </button>
            </div>

            {{-- ═══════════════ SEARCH & FILTERS ═══════════════ --}}
            <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-4 items-center">
                    <div class="relative flex-1 w-full">
                        <span
                            class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                        <input type="text" wire:model.live.debounce.500ms="search"
                            placeholder="Search by barcode, order number or customer..."
                            class="w-full pl-12 pr-4 py-3 bg-slate-50/80 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 outline-none focus:ring-2 focus:ring-[#161b97]/15 focus:border-[#161b97] transition-all placeholder:text-slate-400" />
                    </div>
                    <label
                        class="flex items-center gap-3 px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors shrink-0">
                        <div class="relative">
                            <input type="checkbox" wire:model.live="todayOnly" class="sr-only peer" />
                            <div class="w-10 h-5 bg-slate-300 rounded-full peer-checked:bg-[#161b97] transition-colors">
                            </div>
                            <div
                                class="absolute left-0.5 top-0.5 bg-white w-4 h-4 rounded-full shadow-sm transition-transform peer-checked:translate-x-5">
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-600 whitespace-nowrap">Today Only</span>
                    </label>
                </div>
            </div>

            {{-- ═══════════════ CURRENT FILTER ═══════════════ --}}
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-black text-slate-700 uppercase tracking-wider">
                        @if($filterStatus === 'all') All Orders
                        @elseif($filterStatus === 'pending') Pending Orders
                        @elseif($filterStatus === 'packed') Packed Orders
                        @elseif($filterStatus === 'delivered') Delivered Orders
                        @elseif($filterStatus === 'completed') Completed Orders
                        @else Orders
                        @endif
                    </h2>
                    <span
                        class="bg-slate-100 text-slate-500 text-[10px] font-black px-2.5 py-1 rounded-full">{{ count($sales) }}
                        results</span>
                </div>
            </div>

            {{-- ═══════════════ ORDERS LIST ═══════════════ --}}
            <div class="space-y-4">
                @forelse($sales as $index => $sale)
                    @php
                        $status = $sale->delivery_status ?? 'N/A';
                        $barcode = $sale->deliverySale->delivery_barcode ?? $sale->sale_id ?? 'N/A';
                        $statusConfig = match ($status) {
                            'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
                            'packed' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'border' => 'border-violet-200', 'dot' => 'bg-violet-500'],
                            'delivered' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-200', 'dot' => 'bg-sky-500'],
                            'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                            default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'dot' => 'bg-slate-400'],
                        };
                    @endphp

                    <div
                        class="order-card bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden animate-fade-in stagger-{{ min($index + 1, 4) }}">
                        {{-- Order Header --}}
                        <div
                            class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-slate-500 text-lg">qr_code</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-sm font-black text-slate-800 font-mono tracking-wide">{{ $barcode }}
                                        </h3>
                                        <div
                                            class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $statusConfig['bg'] }} {{ $statusConfig['border'] }} border">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }} {{ $status === 'pending' ? 'pulse-dot' : '' }}"></span>
                                            <span
                                                class="text-[10px] font-black uppercase tracking-wider {{ $statusConfig['text'] }}">{{ ucfirst($status) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="material-symbols-outlined text-slate-300 text-xs">schedule</span>
                                        <span
                                            class="text-[11px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y, h:i A') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Action buttons based on status --}}
                            <div class="flex items-center gap-2 shrink-0">
                                @if($status === 'pending')
                                    {{-- Pending → View Details to Pack --}}
                                    <button
                                        class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#161b97] to-[#4361ee] text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-blue-500/15 hover:shadow-xl transition-all group"
                                        wire:click="showPackingModal({{ $sale->id }})">
                                        <span
                                            class="material-symbols-outlined text-base group-hover:scale-110 transition-transform">visibility</span>
                                        View & Pack
                                    </button>
                                @elseif($status === 'packed')
                                    {{-- Packed → Direct Deliver button --}}
                                    <button
                                        class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-sky-500/15 hover:shadow-xl transition-all group"
                                        wire:click="confirmStatusChange('delivered', {{ $sale->id }})">
                                        <span
                                            class="material-symbols-outlined text-base group-hover:scale-110 transition-transform">local_shipping</span>
                                        Mark Delivered
                                    </button>
                                @elseif($status === 'delivered')
                                    {{-- Delivered → Direct Complete button --}}
                                    <button
                                        class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-emerald-500/15 hover:shadow-xl transition-all group"
                                        wire:click="confirmStatusChange('completed', {{ $sale->id }})">
                                        <span
                                            class="material-symbols-outlined text-base group-hover:scale-110 transition-transform">task_alt</span>
                                        Complete
                                    </button>
                                @elseif($status === 'completed')
                                    {{-- Completed badge --}}
                                    <div
                                        class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-xl">
                                        <span class="material-symbols-outlined text-emerald-500 text-base">verified</span>
                                        <span
                                            class="text-[10px] font-black text-emerald-700 uppercase tracking-wider">Done</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Order Body --}}
                        <div class="px-5 py-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-blue-500 text-sm">person</span>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Customer
                                        </p>
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $this->getCustomerDisplayName($sale) }}</p>
                                        @php $phone = $this->getCustomerPhone($sale); @endphp
                                        @if($phone)
                                            <p class="text-[10px] text-slate-400 font-medium">{{ $phone }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-emerald-500 text-sm">payments</span>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total</p>
                                        <p class="text-sm font-black text-slate-800">Rs.
                                            {{ number_format($sale->total_amount, 2) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-7 h-7 rounded-lg {{ ($sale->due_amount ?? 0) > 0 ? 'bg-red-50' : 'bg-slate-50' }} flex items-center justify-center shrink-0">
                                        <span
                                            class="material-symbols-outlined {{ ($sale->due_amount ?? 0) > 0 ? 'text-red-500' : 'text-slate-400' }} text-sm">account_balance_wallet</span>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Due</p>
                                        <p
                                            class="text-sm font-bold {{ ($sale->due_amount ?? 0) > 0 ? 'text-red-600' : 'text-slate-500' }}">
                                            Rs. {{ number_format($sale->due_amount ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Items Preview --}}
                            <div class="mt-3 pt-3 border-t border-slate-100">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($sale->items->take(4) as $item)
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-50 border border-slate-200/80 rounded-lg text-[11px] font-medium text-slate-600">
                                            {{ $item->product_name ?? ($item->product->name ?? 'Product') }}
                                            <span
                                                class="bg-slate-200 text-slate-600 text-[9px] font-black px-1 py-0.5 rounded">×{{ $item->quantity }}</span>
                                        </span>
                                    @endforeach
                                    @if(count($sale->items) > 4)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 bg-blue-50 border border-blue-200/60 rounded-lg text-[10px] font-black text-[#161b97]">+{{ count($sale->items) - 4 }}
                                            more</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Delivery Info --}}
                            @if($sale->deliverySale)
                                <div class="mt-3 pt-3 border-t border-slate-100">
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-violet-50 border border-violet-200/60 rounded-lg text-[10px] font-bold text-violet-700">
                                            <span class="material-symbols-outlined text-xs">local_shipping</span>
                                            {{ $sale->deliverySale->delivery_method ?? 'N/A' }}
                                        </span>
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 border border-blue-200/60 rounded-lg text-[10px] font-bold text-blue-700">
                                            <span class="material-symbols-outlined text-xs">credit_card</span>
                                            {{ $sale->deliverySale->payment_method ?? 'N/A' }}
                                        </span>
                                    </div>
                                    @if($sale->deliverySale->customer_details)
                                        <div class="mt-2 px-3 py-2 bg-amber-50 border border-amber-200/60 rounded-lg">
                                            <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-0.5">Address
                                            </p>
                                            <p class="text-xs text-amber-800 font-medium whitespace-pre-line">
                                                {{ $sale->deliverySale->customer_details }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div
                        class="bg-white rounded-2xl border border-slate-200/60 shadow-sm py-16 text-center animate-fade-in">
                        <div
                            class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-3xl text-slate-200">inbox</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-500 uppercase tracking-widest mb-2">No Orders Found</h3>
                        <p class="text-xs text-slate-400 font-medium max-w-sm mx-auto">No orders matching your current
                            filter.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══════════════ PACKING MODAL (Only for Pending Orders) ═══════════════ --}}
    @if($showModal && $modalSale)
        <div class="fixed inset-0 z-[5000] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl relative z-10 transform transition-all animate-fade-in flex flex-col"
                style="max-height: 85vh;">
                {{-- Modal Header (fixed) --}}
                <div
                    class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4 flex items-center justify-between rounded-t-2xl shrink-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-xl">package_2</span>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-white uppercase tracking-wider">Packing Details</h2>
                            <p class="text-xs text-white/80 font-bold font-mono mt-0.5">
                                {{ $modalSale->deliverySale->delivery_barcode ?? $modalSale->sale_id }}</p>
                        </div>
                    </div>
                    <button
                        class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors"
                        wire:click="closeModal">
                        <span class="material-symbols-outlined text-white text-base">close</span>
                    </button>
                </div>

                {{-- Modal Body (scrollable) --}}
                <div class="overflow-y-auto custom-scrollbar flex-1 p-5">

                    {{-- Order Info --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Customer</p>
                            <p class="text-sm font-bold text-slate-700">{{ $this->getCustomerDisplayName($modalSale) }}</p>
                            @php $modalPhone = $this->getCustomerPhone($modalSale); @endphp
                            @if($modalPhone)
                                <p class="text-[10px] text-slate-400 font-medium mt-0.5">{{ $modalPhone }}</p>
                            @endif
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Date</p>
                            <p class="text-sm font-bold text-slate-700">
                                {{ \Carbon\Carbon::parse($modalSale->created_at)->format('d M Y') }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Total</p>
                            <p class="text-sm font-black text-slate-800">Rs.
                                {{ number_format($modalSale->total_amount, 2) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Payment</p>
                            <p class="text-sm font-bold text-slate-700">{{ ucfirst($modalSale->payment_status ?? 'N/A') }}
                            </p>
                        </div>
                    </div>

                    {{-- Delivery Details --}}
                    @if($modalSale->deliverySale)
                        <div class="mb-5 bg-violet-50 rounded-xl border border-violet-100 p-4">
                            <p class="text-[9px] font-black text-violet-500 uppercase tracking-widest mb-2">Delivery Info</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <p class="text-[8px] font-black text-violet-400 uppercase">Method</p>
                                    <p class="text-sm font-bold text-violet-800">
                                        {{ $modalSale->deliverySale->delivery_method ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black text-violet-400 uppercase">Payment</p>
                                    <p class="text-sm font-bold text-violet-800">
                                        {{ $modalSale->deliverySale->payment_method ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black text-violet-400 uppercase">Barcode</p>
                                    <p class="text-sm font-bold text-violet-800 font-mono">
                                        {{ $modalSale->deliverySale->delivery_barcode ?? 'N/A' }}</p>
                                </div>
                            </div>
                            @if($modalSale->deliverySale->customer_details)
                                <div class="mt-3 pt-3 border-t border-violet-200/60">
                                    <p class="text-[8px] font-black text-violet-400 uppercase mb-1">Customer / Address</p>
                                    <p class="text-sm text-violet-800 font-medium whitespace-pre-line">
                                        {{ $modalSale->deliverySale->customer_details }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Products Table --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="material-symbols-outlined text-[#161b97] text-base">checklist</span>
                            <h3 class="text-[10px] font-black text-slate-700 uppercase tracking-widest">Products to Pack
                            </h3>
                            <span
                                class="bg-[#161b97] text-white text-[9px] font-black px-2 py-0.5 rounded-full">{{ count($modalSale->items) }}</span>
                        </div>

                        <div class="bg-slate-50 rounded-xl border border-slate-100 overflow-hidden">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-slate-100/80">
                                        <th class="px-4 py-2.5 text-left text-[9px] font-black text-slate-500 uppercase tracking-widest">#</th>
                                        <th class="px-4 py-2.5 text-left text-[9px] font-black text-slate-500 uppercase tracking-widest">Product</th>
                                        <th class="px-4 py-2.5 text-center text-[9px] font-black text-slate-500 uppercase tracking-widest">Qty</th>
                                        <th class="px-4 py-2.5 text-right text-[9px] font-black text-slate-500 uppercase tracking-widest">Price</th>
                                        <th class="px-4 py-2.5 text-right text-[9px] font-black text-slate-500 uppercase tracking-widest">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @php $itemsTotal = 0; @endphp
                                    @foreach($modalSale->items as $idx => $item)
                                        @php
                                            $lineTotal = $item->total ?? ($item->unit_price * $item->quantity);
                                            $itemsTotal += $lineTotal;
                                        @endphp
                                        <tr class="hover:bg-white transition-colors">
                                            <td class="px-4 py-3">
                                                <span class="w-6 h-6 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-500">{{ $idx + 1 }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <p class="text-sm font-bold text-slate-700">{{ $item->product->name ?? $item->product_name ?? 'Product' }}</p>
                                                @if($item->variant_value)
                                                    <p class="text-[10px] text-slate-400 font-medium">{{ $item->variant_value }}</p>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center justify-center w-9 h-9 bg-white border-2 border-[#161b97]/15 rounded-xl text-base font-black text-[#161b97]">{{ $item->quantity }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <p class="text-sm font-medium text-slate-600">Rs. {{ number_format($item->unit_price, 2) }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <p class="text-sm font-bold text-slate-800">Rs. {{ number_format($lineTotal, 2) }}</p>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-100/50 border-t-2 border-slate-200">
                                        <td colspan="4" class="px-4 py-2.5 text-right text-[10px] font-black text-slate-500 uppercase tracking-widest">Subtotal</td>
                                        <td class="px-4 py-2.5 text-right text-sm font-bold text-slate-700">Rs. {{ number_format($itemsTotal, 2) }}</td>
                                    </tr>
                                    @if($modalSale->discount_amount && $modalSale->discount_amount > 0)
                                        <tr class="bg-slate-100/30">
                                            <td colspan="4" class="px-4 py-2 text-right text-[10px] font-black text-red-500 uppercase tracking-widest">Discount</td>
                                            <td class="px-4 py-2 text-right text-sm font-bold text-red-500">- Rs. {{ number_format($modalSale->discount_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($modalSale->deliverySale && $modalSale->deliverySale->delivery_charge > 0)
                                        <tr class="bg-slate-100/30">
                                            <td colspan="4" class="px-4 py-2 text-right text-[10px] font-black text-slate-500 uppercase tracking-widest">Delivery Charge</td>
                                            <td class="px-4 py-2 text-right text-sm font-bold text-slate-700">Rs. {{ number_format($modalSale->deliverySale->delivery_charge, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-[#161b97]/5 border-t-2 border-[#161b97]/20">
                                        <td colspan="4" class="px-4 py-3 text-right text-xs font-black text-[#161b97] uppercase tracking-widest">Grand Total</td>
                                        <td class="px-4 py-3 text-right text-base font-black text-[#161b97]">Rs. {{ number_format($modalSale->total_amount, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer (fixed at bottom) --}}
                <div
                    class="px-5 py-3.5 bg-white border-t border-slate-200 flex items-center justify-between gap-3 rounded-b-2xl shrink-0">
                    <button
                        class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-500 uppercase tracking-wider hover:bg-slate-50 transition-all flex items-center gap-2"
                        wire:click="closeModal">
                        <span class="material-symbols-outlined text-sm">close</span>
                        Close
                    </button>
                    <button
                        class="px-6 py-2.5 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-violet-500/20 hover:shadow-xl transition-all flex items-center gap-2 group"
                        wire:click="confirmStatusChange('packed', {{ $modalSale->id }})" wire:loading.attr="disabled">
                        <span
                            class="material-symbols-outlined text-base group-hover:scale-110 transition-transform">package_2</span>
                        Mark as Packed
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════ CONFIRMATION MODAL ═══════════════ --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-[6000] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm"></div>

            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden relative z-10 animate-scale-in">
                <div class="p-6 text-center">
                    @php
                        $cIcon = match ($confirmAction) {
                            'packed' => ['icon' => 'package_2', 'bg' => 'bg-violet-100', 'text' => 'text-violet-600', 'grad' => 'from-violet-500 to-purple-600', 'shadow' => 'shadow-violet-500/20'],
                            'delivered' => ['icon' => 'local_shipping', 'bg' => 'bg-sky-100', 'text' => 'text-sky-600', 'grad' => 'from-sky-500 to-blue-600', 'shadow' => 'shadow-sky-500/20'],
                            'completed' => ['icon' => 'task_alt', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'grad' => 'from-emerald-500 to-teal-500', 'shadow' => 'shadow-emerald-500/20'],
                            default => ['icon' => 'help', 'bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'grad' => 'from-slate-500 to-slate-600', 'shadow' => 'shadow-slate-500/20'],
                        };
                    @endphp

                    <div class="w-14 h-14 rounded-2xl {{ $cIcon['bg'] }} flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined {{ $cIcon['text'] }} text-2xl">{{ $cIcon['icon'] }}</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 mb-1.5">{{ $confirmTitle }}</h3>
                    <p class="text-sm text-slate-500 font-medium mb-6">{{ $confirmMessage }}</p>

                    <div class="flex items-center justify-center gap-3">
                        <button
                            class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-500 uppercase tracking-wider hover:bg-slate-50 transition-all"
                            wire:click="cancelConfirm">
                            Cancel
                        </button>
                        <button
                            class="px-6 py-2.5 bg-gradient-to-r {{ $cIcon['grad'] }} text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg {{ $cIcon['shadow'] }} hover:shadow-xl transition-all flex items-center gap-2"
                            wire:click="executeStatusChange" wire:loading.attr="disabled" wire:target="executeStatusChange">
                            <span wire:loading.remove wire:target="executeStatusChange"
                                class="material-symbols-outlined text-sm">{{ $cIcon['icon'] }}</span>
                            <span wire:loading wire:target="executeStatusChange">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="executeStatusChange">Yes, Confirm</span>
                            <span wire:loading wire:target="executeStatusChange">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>