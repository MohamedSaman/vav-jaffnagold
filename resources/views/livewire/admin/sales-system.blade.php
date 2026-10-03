<div class="container-fluid py-3" x-data="salesPage()" x-init="init()">

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 premium-alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 premium-alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Main Billing Section --}}
    <div class="premium-card">
        <div class="premium-card-header bg-black py-2">
            <div class="d-flex align-items-center justify-content-between px-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon">
                        <i class="bi bi-gem"></i>
                    </div>
                    <h6 class="mb-0 fw-bold " style="letter-spacing: 0.05em; text-transform: uppercase;color:white;">
                        Billing System</h6>
                </div>
                <div class="d-block d-md-flex align-items-center gap-3">
                    <span class="badge border border-gold px-3 py-2 rounded-pill">
                        <i class="bi bi-calendar3 me-1"></i> {{ date('D, M d Y') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="premium-card-body p-3">
            {{-- Top Section: Customer & Search --}}
            <div class="row g-3 mb-3">
                {{-- Customer Details (Left) --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="input-header">
                            <i class="bi bi-person-lines-fill me-2 text-gold small"></i>
                            <span class="fw-bold small">Customer & Delivery Details</span>
                            <span class="text-danger small">*</span>
                        </div>
                        @if($customerName || $customerPhone || $customerAddress || $selectedCustomer)
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none fw-semibold"
                                wire:click="clearSelectedCustomer" style="font-size: 0.75rem;">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </button>
                        @endif
                    </div>

                    <div class="customer-form-wrapper p-2 bg-light rounded-3 border">
                        <div class="row g-2 mb-2">
                            {{-- Customer Name --}}
                            <div class="col-6 position-relative">
                                <label class="form-label mb-1 text-muted fw-semibold" style="font-size: 0.72rem;">
                                    <i class="bi bi-person me-1 text-gold"></i>Customer Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm premium-input"
                                    wire:model.live.debounce.300ms="customerName"
                                    placeholder="Enter customer name..." autocomplete="off" required>
                                @error('customerName')
                                    <span class="text-danger small d-block" style="font-size: 0.68rem;">{{ $message }}</span>
                                @enderror

                                {{-- Customer suggestions dropdown --}}
                                @if(!empty($customerSuggestions))
                                    <div class="customer-suggestions-dropdown shadow-lg rounded-2 border position-absolute bg-white w-100"
                                        style="z-index: 1050; top: 100%; left: 0; max-height: 200px; overflow-y: auto;">
                                        @foreach($customerSuggestions as $sug)
                                            <div class="px-2 py-1 border-bottom customer-sug-item cursor-pointer"
                                                wire:key="sug-{{ $sug['id'] }}"
                                                wire:click="selectExistingCustomerById({{ $sug['id'] }})"
                                                style="cursor: pointer; font-size: 0.75rem;">
                                                <div class="fw-bold text-dark">{{ $sug['name'] }}</div>
                                                <div class="text-muted d-flex justify-content-between" style="font-size: 0.7rem;">
                                                    <span><i class="bi bi-telephone me-1"></i>{{ $sug['phone'] ?? 'No phone' }}</span>
                                                    <span>{{ Str::limit($sug['address'] ?? '', 20) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Customer Phone --}}
                            <div class="col-6">
                                <label class="form-label mb-1 text-muted fw-semibold" style="font-size: 0.72rem;">
                                    <i class="bi bi-telephone me-1 text-gold"></i>Phone / Mobile <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm premium-input"
                                    wire:model.live.debounce.400ms="customerPhone"
                                    placeholder="e.g. 0771234567" required>
                                @error('customerPhone')
                                    <span class="text-danger small d-block" style="font-size: 0.68rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Delivery Address --}}
                        <div class="mb-0">
                            <label class="form-label mb-1 text-muted fw-semibold" style="font-size: 0.72rem;">
                                <i class="bi bi-geo-alt me-1 text-gold"></i>Delivery Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control premium-textarea py-1 px-2"
                                wire:model="customerAddress" rows="2"
                                placeholder="House / Shop No, Street, Town, District..." required style="font-size: 0.8rem !important;"></textarea>
                            @error('customerAddress')
                                <span class="text-danger small d-block" style="font-size: 0.68rem;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Product Search (Right) --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-end mb-1">
                        <div class="input-header">
                            <i class="bi bi-search me-2 text-gold small"></i>
                            <span class="fw-bold small">Product Search / Scan Barcode</span>
                        </div>
                        <div class="price-type-selection">
                            <select class="form-select form-select-sm border-gold fw-bold text-dark"
                                wire:model.live="priceType"
                                style="font-size: 0.75rem; width: 150px; background-color: #f8f9fa;">
                                <option value="retail">Retail (10%)</option>
                                <option value="wholesale">Wholesale (25%)</option>
                            </select>
                        </div>
                    </div>
                    <div class="search-input-wrapper">

                        <input type="text" class="form-control premium-search-input" wire:model.live="search"
                            id="productSearchInput" placeholder="Scan barcode or type product name..."
                            autocomplete="off">
                        @if($search)
                            <button class="search-clear-btn" wire:click="$set('search', '')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        @endif

                        {{-- Search Results Dropdown --}}
                        @if($search && count($searchResults) > 0)
                            <div class="search-results-dropdown">
                                @foreach($searchResults as $product)
                                    <div class="search-result-item" wire:key="product-{{ $product['id'] }}"
                                        wire:click="addToCart({{ json_encode($product) }})"
                                        style="{{ $product['stock'] <= 0 ? 'opacity: 0.5; pointer-events: none;' : '' }}">
                                        <div class="product-info">
                                            <div class="product-name">{{ $product['name'] }}</div>
                                            <div class="product-meta">
                                                <span class="meta-tag"><i
                                                        class="bi bi-upc me-1"></i>{{ $product['code'] }}</span>
                                                <span class="meta-tag"><i
                                                        class="bi bi-box me-1"></i>{{ $product['model'] }}</span>
                                            </div>
                                        </div>
                                        <div class="product-price-stock text-end">
                                            <div class="product-price">Rs.{{ number_format($product['price'], 2) }}</div>
                                            <div class="product-stock {{ $product['stock'] <= 0 ? 'out-of-stock' : '' }}">
                                                <i class="bi bi-boxes me-1"></i>Stock: {{ $product['stock'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($search && count($searchResults) === 0)
                            <div class="search-results-dropdown">
                                <div class="no-results">
                                    <i class="bi bi-search display-6 d-block mb-2 text-muted"></i>
                                    <span>No products found for "<strong>{{ $search }}</strong>"</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Middle Section: Cart (Full Width) --}}
            <div class="row g-0 mb-3">
                <div class="col-12">
                    <div class="cart-section-wrapper border rounded-3 overflow-hidden">
                        <div class="bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                            <div class="fw-bold d-flex align-items-center" style="font-size: 0.9rem;">
                                <i class="bi bi-cart3 me-2 text-gold"></i>
                                <span>Cart Items</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button
                                    class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 d-flex align-items-center gap-1"
                                    wire:click="addCustomProduct" style="font-size: 0.75rem; font-weight: 600;">
                                    <i class="bi bi-plus-circle"></i> Custom Product
                                </button>
                                @if(count($cart) > 0)
                                    <span class="badge bg-black text-gold rounded-pill px-3 py-1">{{ count($cart) }}
                                        Items</span>
                                @endif
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table premium-table mb-0">
                                <thead>
                                    <tr>
                                        <th width="40" class="text-center">#</th>
                                        <th>PRODUCT DETAILS</th>
                                        <th width="160" class="text-center">UNIT PRICE</th>
                                        <th width="140" class="text-center">QUANTITY</th>
                                        <th width="160" class="text-center">DISCOUNT</th>
                                        <th width="140" class="text-end pe-4">ITEM TOTAL</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cart as $index => $item)
                                        <tr wire:key="{{ $item['key'] ?? 'cart_' . $index }}" class="cart-item-row">
                                            <td class="text-center"><span
                                                    class="row-num text-gold fw-bold">{{ $index + 1 }}</span></td>
                                            <td>
                                                <div class="cart-product-info">
                                                    @if(!empty($item['is_custom']))
                                                        <input type="text"
                                                            class="form-control form-control-sm fw-bold border-warning mb-1"
                                                            style="font-size: 0.85rem; background: #fffbeb;"
                                                            value="{{ $item['name'] }}"
                                                            wire:change="updateCustomName({{ $index }}, $event.target.value)"
                                                            placeholder="Enter product name...">
                                                        <input type="text"
                                                            class="form-control form-control-sm text-muted border-0 bg-light"
                                                            style="font-size: 0.7rem;" value="{{ $item['code'] }}"
                                                            wire:change="updateCustomCode({{ $index }}, $event.target.value)"
                                                            placeholder="Product code...">
                                                        <span class="badge bg-warning text-dark mt-1"
                                                            style="font-size: 0.6rem;"><i
                                                                class="bi bi-pencil-square me-1"></i>CUSTOM</span>
                                                    @else
                                                        <strong class="d-block text-dark h6 mb-0">{{ $item['name'] }}</strong>
                                                        <small class="text-muted fw-bold"
                                                            style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ $item['code'] }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm rounded-3 overflow-hidden border">
                                                    <span class="input-group-text bg-white border-0 text-muted"
                                                        style="font-size: 0.8rem;">Rs</span>
                                                    <input type="number"
                                                        class="form-control border-0 ps-0 fw-bold text-dark text-end pe-3"
                                                        style="color: #c5a02c !important;"
                                                        wire:change="updatePrice({{ $index }}, $event.target.value)"
                                                        value="{{ $item['price'] }}" min="0" step="0.01">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div
                                                    class="qty-stepper d-inline-flex align-items-center bg-light rounded-3 p-1">
                                                    <button
                                                        class="btn btn-link text-dark p-0 px-2 text-decoration-none fw-bold"
                                                        wire:click="decrementQuantity({{ $index }})">-</button>
                                                    <span class="px-3 fw-bold"
                                                        style="min-width: 40px;">{{ $item['quantity'] }}</span>
                                                    <button
                                                        class="btn btn-link text-dark p-0 px-2 text-decoration-none fw-bold"
                                                        wire:click="incrementQuantity({{ $index }})">+</button>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm rounded-3 overflow-hidden border">
                                                    <input type="text"
                                                        class="form-control border-0 ps-2 fw-bold text-danger text-end pe-2"
                                                        wire:change="updateDiscount({{ $index }}, $event.target.value)"
                                                        value="{{ ($item['discount_percentage'] ?? 0) > 0 ? ($item['discount_percentage'] . '%') : $item['discount'] }}"
                                                        style="font-size: 0.8rem;">
                                                </div>
                                                @if(($item['discount_percentage'] ?? 0) > 0)
                                                    <div class="text-center" style="margin-top: -3px;"><small class="text-muted"
                                                            style="font-size: 0.65rem;">(Rs.{{ number_format($item['discount'], 2) }})</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-dark fs-6">
                                                Rs.{{ number_format($item['total'], 2) }}
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm text-danger border-0 p-2 hover-bg-danger-soft"
                                                    wire:click="removeFromCart({{ $index }})">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-white p-3 border-top">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                {{-- Left: Discount + Delivery Charge --}}
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    {{-- Additional Discount --}}
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="fw-bold text-dark text-uppercase mb-0"
                                            style="font-size: 0.7rem; letter-spacing: 0.5px;">Discount:</label>
                                        <div
                                            class="special-discount-input d-flex align-items-center bg-white border rounded-pill p-1 pe-3">
                                            <input type="number"
                                                class="form-control form-control-sm border-0 bg-transparent text-center fw-bold"
                                                style="width: 80px; box-shadow: none;"
                                                wire:model.live="additionalDiscount" min="0"
                                                step="{{ $additionalDiscountType === 'percentage' ? '1' : '0.01' }}"
                                                @if($additionalDiscountType === 'percentage') max="100" @endif>
                                            <button type="button"
                                                class="btn rounded-pill px-2 py-0 ms-2 fw-bold border-0"
                                                style="height: 24px; font-size: 0.65rem; min-width: 32px;
                                            {{ $additionalDiscountType === 'percentage' ? 'background: var(--primary-blue); color: #fff;' : 'background: #000; color: #fff;' }}"
                                                wire:click="toggleDiscountType">
                                                {{ $additionalDiscountType === 'percentage' ? '%' : 'Rs' }}
                                            </button>
                                            <button type="button" class="btn p-0 ms-2 border-0 text-muted"
                                                wire:click="toggleDiscountType" title="Switch between Rs and %">
                                                <i class="bi bi-arrow-repeat" style="font-size: 1rem;"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Delivery Charge --}}
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="fw-bold text-dark text-uppercase mb-0"
                                            style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                            <i class="bi bi-truck me-1 text-gold"></i>Delivery:
                                        </label>
                                        <div class="delivery-charge-input">
                                            <span class="currency-label">Rs</span>
                                            <input type="number" class="delivery-charge-field"
                                                wire:model.live="deliveryCharge" min="0" step="1" placeholder="450">
                                        </div>
                                    </div>
                                </div>

                                {{-- Right: Subtotal --}}
                                <div class="text-end">
                                    <div>
                                        <span class="fw-bold text-dark fs-5">Subtotal:</span>
                                        <span class="fw-black fs-5 ms-1 text-muted">Rs.</span>
                                        <span class="fw-black fs-5">{{ number_format($subtotal, 2) }}</span>
                                    </div>
                                    @if($additionalDiscountAmount > 0)
                                        <div class="text-danger fw-bold" style="font-size: 0.85rem;">
                                            Discount: -Rs.{{ number_format($additionalDiscountAmount, 2) }}
                                            @if($additionalDiscountType === 'percentage')
                                                <span class="text-muted">({{ $additionalDiscount }}%)</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if($deliveryCharge > 0)
                                        <div class="text-success fw-bold" style="font-size: 0.85rem;">
                                            Delivery: +Rs.{{ number_format($deliveryCharge, 2) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(count($cart) > 0)
                        <div class="mt-2 text-end">
                            <button class="btn btn-sm text-danger p-0 border-0" wire:click="clearCart"
                                style="font-size: 0.7rem;">
                                <i class="bi bi-trash me-1"></i>Clear Cart
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Bottom Section: Delivery, Payment & Create Sale --}}
            <div class="row g-3 pt-3 border-top">
                <div class="col-md-9">
                    <div class="row g-3">
                        {{-- Delivery Method --}}
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-muted text-uppercase mb-2" style="font-size: 0.7rem;">
                                <i class="bi bi-truck me-2 text-gold"></i>Delivery Method
                            </label>
                            <div class="selection-bar">
                                <div class="selection-item {{ $deliveryMethod === 'Post' ? 'active' : '' }}"
                                    wire:click="$set('deliveryMethod', 'Post')">
                                    <i class="bi bi-mailbox2"></i>
                                    <span>Post Delivery</span>
                                </div>
                                <div class="selection-item {{ $deliveryMethod === 'Domex' ? 'active' : '' }}"
                                    wire:click="$set('deliveryMethod', 'Domex')">
                                    <i class="bi bi-house-heart"></i>
                                    <span>Domex</span>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div class="col-md-7">
                            <label class="form-label fw-bold text-muted text-uppercase mb-2" style="font-size: 0.7rem;">
                                <i class="bi bi-credit-card me-2 text-gold"></i>Payment Method
                            </label>
                            <div class="selection-bar">
                                <div class="selection-item {{ $paymentMethod === 'Cash on Delivery' ? 'active' : '' }}"
                                    wire:click="$set('paymentMethod', 'Cash on Delivery')">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Cash on Delivery</span>
                                </div>
                                <div class="selection-item {{ $paymentMethod === 'Online Payment' ? 'active' : '' }}"
                                    wire:click="$set('paymentMethod', 'Online Payment')">
                                    <i class="bi bi-phone"></i>
                                    <span>Online Payment</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Grand Total & Create Button --}}
                <div class="col-md-3">
                    <div
                        class="bg-black p-4 rounded-4 h-100 d-flex flex-column justify-content-center border border-gold border-opacity-25 shadow-lg">
                        <div class="text-center mb-3">
                            @if($deliveryCharge > 0)
                                <div class="d-flex justify-content-between px-2 mb-1" style="font-size: 0.65rem;">
                                    <span class="text-white opacity-80 fw-bold">Delivery</span>
                                    <span
                                        class="text-white opacity-90 fw-bold">+Rs.{{ number_format($deliveryCharge, 0) }}</span>
                                </div>
                            @endif
                            <div class=" opacity-50 fw-bold mb-1"
                                style="font-size: 0.7rem; letter-spacing: 2px;color:white;">TOTAL PAYABLE</div>
                            <div class="h3 mb-0" style="letter-spacing: -1px;color:white;">
                                Rs.{{ number_format($grandTotal, 2) }}</div>
                        </div>
                        <button
                            class="btn btn-gold-premium w-100 py-2 rounded-3 text-white d-flex align-items-center justify-content-center gap-2"
                            style="font-size: 0.85rem;" wire:click="createSale" {{ count($cart) == 0 ? 'disabled' : '' }}>
                            <i class="bi bi-lock-fill"></i>
                            <span>CREATE SALE</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sale Preview Modal --}}
    @if($showSaleModal && $createdSale)
        <div class="modal fade show d-block" tabindex="-1"
            style="background-color: rgba(0,0,0,0.7); backdrop-filter: blur(5px);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 15px;">
                    <div class="modal-body p-0">
                        {{-- Header Section --}}
                        <div class="p-1 bg-white border-bottom">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <img src="{{ asset('images/jg.png') }}" alt="Logo" class="img-fluid"
                                        style="max-height: 70px;">
                                </div>
                                <div class="col-6 text-center">
                                    <h2 class="mb-0 fw-black text-dark"
                                        style="font-size: 1.8rem; letter-spacing: 1px; line-height: 1.2;">JAFFNA GOLD
                                        COVERING</h2>
                                </div>
                                <div class="col-3 text-end">
                                    <div class="badge bg-gold text-white p-2 px-3 rounded-pill fw-bold"
                                        style="font-size: 0.7rem;">SALE CONFIRMED</div>
                                </div>
                            </div>
                        </div>

                        {{-- Info Section --}}
                        <div class="p-2 bg-light-gold border-bottom">
                            <div class="row g-4">
                                <div class="col-6">
                                    <div class="text-muted small text-uppercase fw-bold mb-2"
                                        style="font-size: 0.65rem; letter-spacing: 1px;">Customer Information</div>
                                    <div class="p-3 bg-white rounded-3 border">
                                        @php
                                            $cName = ($createdSale->customer && $createdSale->customer->name !== 'Walking Customer')
                                                ? $createdSale->customer->name
                                                : ($createdSale->walking_customer_name ?: 'Walking Customer');
                                            $cPhone = ($createdSale->customer && $createdSale->customer->phone && $createdSale->customer->phone !== 'xxxxx')
                                                ? $createdSale->customer->phone
                                                : ($createdSale->walking_customer_phone ?: null);
                                            $cAddress = ($createdSale->customer && $createdSale->customer->address && $createdSale->customer->address !== 'xxxxx')
                                                ? $createdSale->customer->address
                                                : null;
                                        @endphp
                                        <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">
                                            <i class="bi bi-person-fill me-1 text-gold"></i>{{ $cName }}
                                        </div>
                                        @if($cPhone)
                                            <div class="text-dark small mb-1 fw-semibold">
                                                <i class="bi bi-telephone-fill me-1 text-gold"></i>{{ $cPhone }}
                                            </div>
                                        @endif
                                        @if($cAddress)
                                            <div class="text-muted small mb-2 text-break" style="font-size: 0.78rem;">
                                                <i class="bi bi-geo-alt-fill me-1 text-gold"></i>{{ $cAddress }}
                                            </div>
                                        @elseif($createdSale->deliverySale && $createdSale->deliverySale->customer_details)
                                            <div class="text-muted small mb-2 text-break" style="font-size: 0.78rem; white-space: pre-wrap;">
                                                {{ $createdSale->deliverySale->customer_details }}
                                            </div>
                                        @endif
                                        <div class="d-flex gap-2 mt-2">
                                            <span class="badge bg-black text-gold rounded-pill px-2 py-1"
                                                style="font-size: 0.6rem;">{{ $createdSale->deliverySale->delivery_method ?? 'Post' }}</span>
                                            <span class="badge border border-gold text-gold rounded-pill px-2 py-1"
                                                style="font-size: 0.6rem;">{{ $createdSale->deliverySale->payment_method ?? 'Cash on Delivery' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small text-uppercase fw-bold mb-2"
                                        style="font-size: 0.65rem; letter-spacing: 1px;">Invoice Details</div>
                                    <div class="p-3 bg-white rounded-3 border">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted small">Invoice No:</span>
                                            <span class="fw-bold text-dark small">#{{ $createdSale->invoice_number }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Total Discount:</span>
                                            <span class="text-danger fw-bold">-
                                                Rs.{{ number_format($this->totalDiscount + ($additionalDiscountAmount ?? 0), 2) }}</span>
                                        </div>
                                        @php
                                            $totalOriginalPrice = collect($cart)->sum(function ($item) {
                                                return $item['price'] * $item['quantity']; });
                                            $totalDisc = $this->totalDiscount + ($additionalDiscountAmount ?? 0);
                                            $totalDiscPercent = $totalOriginalPrice > 0 ? ($totalDisc / $totalOriginalPrice * 100) : 0;
                                        @endphp
                                        @if($totalDiscPercent > 0)
                                            <div class="text-end mb-2" style="margin-top: -10px;">
                                                <small class="badge bg-danger-soft text-danger fw-black"
                                                    style="font-size: 0.65rem;">Total Discount Percentage:
                                                    {{ number_format($totalDiscPercent, 2) }}%</small>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted small">Date:</span>
                                            <span
                                                class="fw-bold text-dark small">{{ $createdSale->created_at->format('M d, Y | h:i A') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted small">Status:</span>
                                            <span class="text-success fw-bold small"><i
                                                    class="bi bi-patch-check-fill me-1"></i>{{ $createdSale->status }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Items Table --}}
                        <div class="p-4 bg-white">
                            <div class="table-responsive rounded-3 border overflow-hidden">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr style="font-size: 0.7rem;" class="text-uppercase text-muted fw-bold">
                                            <th class="ps-3">Product Name</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end pe-3">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($createdSale->items as $item)
                                            <tr style="font-size: 0.85rem;">
                                                <td class="ps-3 fw-bold">{{ $item->product_name }}</td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-end pe-3 fw-bold">Rs.{{ number_format($item->total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light-gold">
                                        <tr style="font-size: 0.8rem;">
                                            <td colspan="2" class="text-end text-muted">Subtotal</td>
                                            <td class="text-end pe-3 fw-bold">
                                                Rs.{{ number_format($createdSale->subtotal, 2) }}</td>
                                        </tr>
                                        @if($createdSale->discount_amount > 0)
                                            <tr style="font-size: 0.8rem;" class="text-danger">
                                                <td colspan="2" class="text-end fw-bold">Discount</td>
                                                <td class="text-end pe-3 fw-bold">
                                                    -Rs.{{ number_format($createdSale->discount_amount, 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr class="fw-black h4 bg-black text-gold">
                                            <td colspan="2" class="text-end border-0 ps-3 py-3">GRAND TOTAL</td>
                                            <td class="text-end pe-3 border-0 py-3">
                                                Rs.{{ number_format($createdSale->total_amount, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 p-3 px-4 gap-2 flex-wrap">
                        <button type="button"
                            class="btn btn-outline-gold rounded-pill px-4 py-2 border-2 d-flex align-items-center gap-2"
                            onclick="openDeliveryPrintWindow({{ $createdSale->id }})" style="font-size: 0.8rem;">
                            <i class="bi bi-printer-fill"></i> PRINT LABEL
                        </button>
                        <button type="button"
                            class="btn btn-outline-dark rounded-pill px-3 py-2 border-2 d-flex align-items-center gap-2"
                            onclick="openPrintWindow({{ $createdSale->id }})" style="font-size: 0.8rem;">
                            <i class="bi bi-receipt"></i> PRINT RECEIPT
                        </button>
                        <button type="button"
                            class="btn btn-gold-premium rounded-pill px-4 py-2 fw-black d-flex align-items-center gap-2"
                            wire:click="createNewSale" style="font-size: 0.8rem;">
                            <i class="bi bi-plus-circle-fill"></i> NEW SALE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
    <style>
        :root {
            --primary-blue: #161b97;
            --deep-blue: #12167d;
            --light-blue: #f0f2ff;
            --jg-red: #f30b1f;
            --dark-bg: #000000;
        }

        .text-gold {
            color: var(--primary-blue) !important;
        }

        .bg-gold {
            background-color: var(--primary-blue) !important;
        }

        .bg-light-gold {
            background-color: var(--light-blue) !important;
        }

        .border-gold {
            border-color: var(--primary-blue) !important;
        }

        .premium-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid rgba(22, 27, 151, 0.1);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .premium-textarea {
            border: 2px solid #eee;
            border-radius: 8px;
            padding: 10px !important;
            font-size: 0.85rem !important;
            transition: all 0.3s;
            background: #fbfbfb;
        }

        .premium-textarea:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(22, 27, 151, 0.1);
            background: #fff;
        }

        .premium-input {
            border: 2px solid #eee;
            border-radius: 8px;
            transition: all 0.3s;
            background: #fbfbfb;
            font-size: 0.82rem !important;
        }

        .premium-input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(22, 27, 151, 0.1);
            background: #fff;
        }

        .customer-sug-item:hover {
            background-color: var(--light-blue) !important;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-blue);
            font-size: 1.1rem;
            z-index: 10;
        }

        .premium-search-input {
            padding: 12px 45px 12px 55px !important;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 0.95rem !important;
            transition: all 0.3s;
            background: #fbfbfb;
        }

        .premium-search-input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(22, 27, 151, 0.1);
            background: #fff;
        }

        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            margin-top: 5px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #eee;
        }

        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f5f5f5;
            font-size: 0.85rem;
        }

        .search-result-item:hover {
            background: var(--light-gold);
        }

        .qty-controls {
            display: flex;
            align-items: center;
            background: #f0f0f0;
            border-radius: 8px;
            padding: 2px;
            width: fit-content;
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            border: none;
            background: none;
            font-weight: bold;
            color: #555;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-input {
            width: 35px;
            text-align: center;
            border: none;
            background: none;
            font-weight: 700;
            font-size: 0.8rem;
        }

        /* Selection Bar Style */
        .selection-bar {
            display: flex;
            background: #f4f4f4;
            padding: 5px;
            border-radius: 12px;
            gap: 5px;
            border: 1px solid #e0e0e0;
        }

        .selection-item {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.8rem;
            color: #777;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
        }

        .selection-item i {
            font-size: 1.1rem;
        }

        .selection-item:hover:not(.active) {
            background: #ececec;
            color: #444;
        }

        .selection-item.active {
            background: var(--primary-blue);
            color: #fff;
            box-shadow: 0 4px 12px rgba(22, 27, 151, 0.2);
            transform: translateY(-1px);
        }

        /* Delivery Charge */
        .delivery-charge-bar {
            background: #f8f8f8;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 8px 15px;
        }

        .delivery-charge-input {
            display: flex;
            align-items: center;
            background: #fff;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: border-color 0.3s;
        }

        .delivery-charge-input:focus-within {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(22, 27, 151, 0.1);
        }

        .delivery-charge-input .currency-label {
            padding: 5px 10px;
            font-weight: 800;
            font-size: 0.75rem;
            color: #999;
            background: #f4f4f4;
            border-right: 1px solid #eee;
        }

        .delivery-charge-field {
            border: none;
            outline: none;
            width: 90px;
            padding: 5px 10px;
            font-weight: 800;
            font-size: 0.85rem;
            text-align: right;
            color: #222;
            background: transparent;
        }

        .delivery-charge-field::-webkit-inner-spin-button,
        .delivery-charge-field::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .btn-gold-premium {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--deep-blue) 100%);
            color: #fff;
            font-weight: 800;
            border: none;
            letter-spacing: 0.1em;
            transition: all 0.3s;
        }

        .btn-gold-premium:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(22, 27, 151, 0.4);
        }

        .btn-outline-gold {
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
            background: transparent;
            font-weight: 700;
        }

        .btn-outline-gold:hover {
            background: var(--primary-blue);
            color: #fff;
        }

        .btn-black {
            background: #000;
            color: #fff;
        }

        .btn-black:hover {
            background: #222;
            color: var(--primary-blue);
        }

        .modal-content {
            border-radius: 20px;
            border: 2px solid var(--primary-blue);
        }

        .premium-alert {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        /* Table Styles */
        .premium-table thead th {
            background: #fbfbfb;
            color: #888;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 10px 15px;
            border-bottom: 2px solid #eee;
        }

        .cart-item-row td {
            padding: 8px 15px !important;
            vertical-align: middle;
            font-size: 0.85rem;
        }

        .row-num {
            color: var(--primary-blue);
            font-weight: 800;
            font-size: 0.8rem;
        }

        .header-icon {
            width: 32px;
            height: 32px;
            background: var(--primary-blue);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 1rem;
        }

        .qty-stepper {
            border: 1px solid #e0e0e0;
        }

        .qty-stepper button:hover {
            background: #e9e9e9;
        }

        .fw-black {
            font-weight: 900 !important;
        }

        .fs-10 {
            font-size: 10px !important;
        }

        .hover-bg-danger-soft:hover {
            background-color: #fff5f5;
            border-radius: 8px;
        }

        .special-discount-input {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .delivery-radio-card .radio-content {
            height: 100px;
            justify-content: center;
        }

        .search-clear-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #ccc;
        }

        .table-responsive {
            min-height: 100px !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .premium-search-input {
                padding: 15px 40px 15px 45px !important;
            }

            .search-icon {
                left: 15px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function salesPage() {
            return {
                init() {
                    Livewire.on('focus-search', () => {
                        this.$nextTick(() => {
                            const searchInput = document.getElementById('productSearchInput');
                            if (searchInput) searchInput.focus();
                        });
                    });

                    this.$nextTick(() => {
                        const searchInput = document.getElementById('productSearchInput');
                        if (searchInput) searchInput.focus();
                    });
                }
            };
        }

        function getPrintPrefix() {
            if (window.location.pathname.startsWith('/delivery')) return '/delivery';
            if (window.location.pathname.startsWith('/staff')) return '/staff';
            return '/admin';
        }

        function openPrintWindow(saleId) {
            const printUrl = getPrintPrefix() + '/print/sale/' + saleId;
            window.open(printUrl, '_blank', 'width=800,height=600');
        }

        function openDeliveryPrintWindow(saleId) {
            const printUrl = getPrintPrefix() + '/print/delivery-label/' + saleId;
            window.open(printUrl, '_blank', 'width=500,height=700');
        }

        document.addEventListener('keydown', function (e) {
            if (e.target.id === 'productSearchInput' && e.key === 'Enter') {
                e.preventDefault();
            }
        });

        window.addEventListener('refreshPage', () => {
            window.location.reload();
        });
    </script>
@endpush