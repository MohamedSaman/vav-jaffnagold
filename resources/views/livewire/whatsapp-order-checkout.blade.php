<div class="wa-checkout-page">
    <style>
        /* ==========================================================================
           JAFFNA GOLD - SIMPLE & ELEGANT WHATSAPP ORDER CHECKOUT
           ========================================================================== */
        .wa-checkout-page {
            background: #faf8f5;
            min-height: 90vh;
            padding: 2rem 1rem 4rem;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #27211b;
        }

        .wa-checkout-container {
            max-width: 980px;
            margin: 0 auto;
        }

        /* Top Header */
        .wa-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .wa-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(201, 168, 76, 0.12);
            border: 1px solid rgba(201, 168, 76, 0.35);
            color: #8c6819;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .wa-header-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.85rem;
            font-weight: 700;
            color: #27211b;
            margin: 0 0 0.4rem 0;
            line-height: 1.25;
        }

        .wa-header-desc {
            font-size: 0.92rem;
            color: #64748b;
            margin: 0;
        }

        /* 2-Column Layout */
        .wa-layout-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: start;
        }

        @media (min-width: 992px) {
            .wa-layout-grid {
                grid-template-columns: 1.35fr 1fr;
                gap: 1.75rem;
            }
        }

        /* Card Container */
        .wa-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e8e2d8;
            box-shadow: 0 6px 20px rgba(39, 33, 27, 0.04);
            padding: 1.5rem;
        }

        @media (min-width: 640px) {
            .wa-card {
                padding: 2rem;
            }
        }

        .wa-card-header-title {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 1.25rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 1.5px solid #f3eee7;
        }

        .wa-card-header-title i {
            color: #cca43b;
            font-size: 1.25rem;
        }

        /* Form Controls */
        .wa-form-group {
            margin-bottom: 1.1rem;
        }

        .wa-form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.85rem;
            margin-bottom: 1.1rem;
        }

        @media (min-width: 576px) {
            .wa-form-row-2 {
                grid-template-columns: 1fr 1fr;
            }
        }

        .wa-label {
            display: block;
            font-size: 0.84rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        .wa-label span.req {
            color: #e11d48;
            font-weight: 700;
        }

        .wa-input, .wa-textarea {
            width: 100%;
            padding: 0.75rem 0.95rem;
            font-size: 0.92rem;
            font-family: inherit;
            color: #1e293b;
            background: #ffffff;
            border: 1.5px solid #dcd3c5;
            border-radius: 10px;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .wa-input::placeholder, .wa-textarea::placeholder {
            color: #94a3b8;
        }

        .wa-input:focus, .wa-textarea:focus {
            border-color: #cca43b;
            box-shadow: 0 0 0 3px rgba(204, 164, 59, 0.15);
            background: #fffdfa;
        }

        .wa-input.is-error, .wa-textarea.is-error {
            border-color: #f43f5e;
            background: #fff5f5;
        }

        .wa-error-msg {
            color: #e11d48;
            font-size: 0.78rem;
            font-weight: 500;
            margin-top: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Payment Selection Cards */
        .wa-payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1.15rem;
        }

        .wa-payment-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1rem;
            background: #fdfcfb;
            border: 2px solid #e8e2d8;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }

        .wa-payment-card:hover {
            border-color: #cca43b;
            background: #fffdf9;
        }

        .wa-payment-card.active {
            border-color: #cca43b;
            background: #fffcf4;
            box-shadow: 0 2px 10px rgba(204, 164, 59, 0.12);
        }

        .wa-payment-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #f4eee2;
            color: #8c6819;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .wa-payment-card.active .wa-payment-icon {
            background: #cca43b;
            color: #ffffff;
        }

        .wa-payment-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .wa-payment-subtitle {
            font-size: 0.74rem;
            color: #64748b;
            margin: 0;
        }

        .wa-radio-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            flex-shrink: 0;
        }

        .wa-payment-card.active .wa-radio-dot {
            border-color: #cca43b;
        }

        .wa-payment-card.active .wa-radio-dot::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #cca43b;
        }

        /* Bank Details Box */
        .wa-bank-box {
            background: #fbfaf7;
            border: 1.5px dashed #d5be8a;
            border-radius: 12px;
            padding: 1rem 1.15rem;
            margin-bottom: 1.15rem;
        }

        .wa-bank-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.6rem;
            background: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #e7dcce;
            margin: 0.6rem 0;
            font-size: 0.82rem;
        }

        .wa-bank-item label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #8c6819;
            text-transform: uppercase;
        }

        .wa-bank-item value {
            display: block;
            font-weight: 700;
            color: #1e293b;
        }

        /* Submit Button */
        .wa-submit-btn {
            width: 100%;
            padding: 0.95rem 1.25rem;
            background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 1.05rem;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 6px 18px rgba(184, 134, 11, 0.25);
            transition: all 0.2s ease;
        }

        .wa-submit-btn:hover {
            background: linear-gradient(135deg, #dbb744 0%, #c49215 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(184, 134, 11, 0.35);
        }

        .wa-submit-btn:active {
            transform: translateY(0);
        }

        /* Order Summary Card */
        .wa-summary-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e8e2d8;
            box-shadow: 0 6px 20px rgba(39, 33, 27, 0.04);
            padding: 1.5rem;
            position: sticky;
            top: 1.5rem;
        }

        .wa-item-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            margin-bottom: 1.15rem;
            max-height: 320px;
            overflow-y: auto;
        }

        .wa-item-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.8rem;
            background: #fdfcfb;
            border: 1px solid #eee8df;
            border-radius: 10px;
        }

        .wa-item-thumb {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: #f6f2ea;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            border: 1px solid #e7dfd3;
        }

        .wa-item-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .wa-item-info {
            flex-grow: 1;
            min-width: 0;
        }

        .wa-item-name {
            font-size: 0.86rem;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0 0 0.15rem 0;
        }

        .wa-item-meta {
            font-size: 0.75rem;
            color: #64748b;
        }

        .wa-item-price {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
            text-align: right;
            flex-shrink: 0;
        }

        /* Price Breakdown Box */
        .wa-price-box {
            background: #fbfaf8;
            border: 1.5px solid #eee5d8;
            border-radius: 12px;
            padding: 1rem 1.15rem;
            margin-bottom: 1.15rem;
        }

        .wa-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.86rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .wa-price-row.total-row {
            margin-top: 0.65rem;
            padding-top: 0.65rem;
            border-top: 1.5px solid #e3d7c5;
            font-size: 1.05rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0;
        }

        .wa-price-row.total-row .total-amount {
            color: #b8860b;
            font-size: 1.45rem;
            font-weight: 800;
        }

        /* Trust Strip */
        .wa-trust-box {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 10px;
            padding: 0.65rem 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            color: #15803d;
            font-size: 0.78rem;
            font-weight: 600;
        }

        /* Status & Confirmation */
        .wa-status-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e3dcd1;
            box-shadow: 0 8px 30px rgba(39, 33, 27, 0.05);
            padding: 2.5rem 1.5rem;
            text-align: center;
            max-width: 620px;
            margin: 0 auto;
        }

        .wa-status-icon-circle {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .wa-status-icon-circle.success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
        .wa-status-icon-circle.warning { background: rgba(234, 179, 8, 0.12); color: #ca8a04; }
        .wa-status-icon-circle.danger  { background: rgba(239, 68, 68, 0.12); color: #dc2626; }

        .wa-receipt-box {
            background: #faf8f5;
            border: 1px solid #e7decf;
            border-radius: 12px;
            padding: 1.15rem 1.25rem;
            text-align: left;
            margin: 1.25rem 0 1.75rem;
        }

        .wa-receipt-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.45rem 0;
            border-bottom: 1px solid #ede4d6;
            font-size: 0.86rem;
        }

        .wa-receipt-line:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-top: 0.35rem;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .wa-btn-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
        }

        .wa-btn-whatsapp {
            background: #25D366;
            color: #ffffff;
            font-weight: 700;
            padding: 0.8rem 1.6rem;
            border-radius: 9999px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
        }

        .wa-btn-whatsapp:hover { background: #20bd5a; color: #ffffff; }

        .wa-btn-secondary {
            background: #ffffff;
            color: #334155;
            border: 1.5px solid #cbd5e1;
            font-weight: 600;
            padding: 0.8rem 1.6rem;
            border-radius: 9999px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .spinner-inline {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <div class="wa-checkout-container">

        {{-- 1. INVALID / NOT FOUND STATE --}}
        @if($isInvalid)
            <div class="wa-status-card">
                <div class="wa-status-icon-circle warning">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0 0 0.4rem 0;">
                    Order Link Not Found
                </h2>
                <p style="color: #64748b; font-size: 0.92rem; margin-bottom: 1.75rem;">
                    This checkout link is invalid or may have been removed. Please contact our support team.
                </p>
                <div class="wa-btn-group">
                    <a href="https://wa.me/94761919650" target="_blank" class="wa-btn-secondary">
                        <i class="bi bi-whatsapp"></i> Chat Support
                    </a>
                </div>
            </div>

        {{-- 2. EXPIRED STATE --}}
        @elseif($isExpired)
            <div class="wa-status-card">
                <div class="wa-status-icon-circle danger">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0 0 0.4rem 0;">
                    Order Link Expired
                </h2>
                <p style="color: #64748b; font-size: 0.92rem; margin-bottom: 1.75rem;">
                    This personalized order link has expired. Please send us a message on WhatsApp to get an updated order link.
                </p>
                <div class="wa-btn-group">
                    <a href="https://wa.me/94761919650" target="_blank" class="wa-btn-whatsapp">
                        <i class="bi bi-whatsapp"></i> Request New Link
                    </a>
                </div>
            </div>

        {{-- 3. ORDER CONFIRMED STATE --}}
        @elseif($orderConfirmed && $createdSale)
            <div class="wa-status-card">
                <div class="wa-status-icon-circle success">
                    <i class="bi bi-check-lg"></i>
                </div>
                <div class="wa-badge-pill">
                    <i class="bi bi-box-seam"></i> Parcel Confirmed
                </div>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 1.85rem; font-weight: 700; color: #1e293b; margin: 0 0 0.4rem 0;">
                    Delivery Order Placed!
                </h2>
                <p style="color: #64748b; font-size: 0.92rem; margin: 0;">
                    Thank you, <strong>{{ $createdSale->customer_display_name }}</strong>! Your parcel is being packed and prepared for delivery.
                </p>

                <div class="wa-receipt-box">
                    <div class="wa-receipt-line">
                        <span style="color: #64748b;">Order Invoice #</span>
                        <strong style="font-family: monospace; color: #1e293b;">{{ $createdSale->invoice_number }}</strong>
                    </div>
                    <div class="wa-receipt-line">
                        <span style="color: #64748b;">Delivery Parcel Barcode</span>
                        <strong style="font-family: monospace; color: #0369a1; background: #e0f2fe; padding: 2px 8px; border-radius: 4px;">
                            {{ $createdSale->deliverySale?->delivery_barcode ?? 'DLV-PENDING' }}
                        </strong>
                    </div>
                    <div class="wa-receipt-line">
                        <span style="color: #64748b;">Payment Method</span>
                        <strong style="color: {{ $createdSale->due_amount > 0 ? '#b8860b' : '#15803d' }};">
                            {{ $createdSale->due_amount > 0 ? 'Cash on Delivery (Pay to Courier)' : 'Paid' }}
                        </strong>
                    </div>
                    <div class="wa-receipt-line">
                        <span style="color: #1e293b;">Total Amount</span>
                        <span style="color: #b8860b; font-size: 1.3rem;">Rs. {{ number_format($createdSale->total_amount, 2) }}</span>
                    </div>
                </div>

                <div class="wa-btn-group">
                    <a href="https://wa.me/94761919650?text={{ urlencode('Hi Jaffna Gold, I confirmed my order ' . $createdSale->invoice_number . ' (Barcode: ' . ($createdSale->deliverySale?->delivery_barcode ?? '') . ')') }}" 
                       target="_blank" class="wa-btn-whatsapp">
                        <i class="bi bi-whatsapp"></i> Notify Us on WhatsApp
                    </a>
                </div>
            </div>

        {{-- 4. ACTIVE CHECKOUT FORM --}}
        @else
            <!-- Header Title -->
            <div class="wa-header">
                <div class="wa-badge-pill">
                    <i class="bi bi-shield-check"></i> Jaffna Gold Direct Order
                </div>
                <h1 class="wa-header-title">Enter Your Delivery Address</h1>
                <p class="wa-header-desc">Please provide your delivery details below to confirm your parcel dispatch.</p>
            </div>

            @if(session()->has('error'))
                <div style="background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 0.9rem 1.1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="wa-layout-grid">
                
                {{-- LEFT COLUMN: Clean Delivery Form --}}
                <div class="wa-card">
                    <form wire:submit.prevent="submitOrder">
                        
                        <div class="wa-card-header-title">
                            <i class="bi bi-geo-alt-fill"></i> Delivery Address & Contact
                        </div>

                        <!-- 1. Customer Full Name -->
                        <div class="wa-form-group">
                            <label class="wa-label">Your Full Name <span class="req">*</span></label>
                            <input type="text" 
                                   class="wa-input @error('customer_name') is-error @enderror" 
                                   wire:model="customer_name" 
                                   placeholder="e.g., S. Ramesh" 
                                   required>
                            @error('customer_name') 
                                <div class="wa-error-msg"><i class="bi bi-info-circle"></i> {{ $message }}</div> 
                            @enderror
                        </div>

                        <!-- 2. Phone Numbers -->
                        <div class="wa-form-row wa-form-row-2">
                            <div class="wa-form-group" style="margin-bottom: 0;">
                                <label class="wa-label">Phone Number <span class="req">*</span></label>
                                <input type="tel" 
                                       class="wa-input @error('customer_phone') is-error @enderror" 
                                       wire:model="customer_phone" 
                                       placeholder="e.g., 0771234567" 
                                       required>
                                @error('customer_phone') 
                                    <div class="wa-error-msg"><i class="bi bi-info-circle"></i> {{ $message }}</div> 
                                @enderror
                            </div>
                            <div class="wa-form-group" style="margin-bottom: 0;">
                                <label class="wa-label">WhatsApp Number <span style="font-weight: normal; color: #94a3b8;">(Optional)</span></label>
                                <input type="tel" 
                                       class="wa-input" 
                                       wire:model="customer_whatsapp" 
                                       placeholder="e.g., 0761234567">
                            </div>
                        </div>

                        <!-- 3. Street Address -->
                        <div class="wa-form-group">
                            <label class="wa-label">Delivery Street Address <span class="req">*</span></label>
                            <textarea class="wa-textarea @error('address_line1') is-error @enderror" 
                                      wire:model="address_line1" 
                                      rows="2" 
                                      placeholder="House / Shop No, Street Name, Landmark..." 
                                      required></textarea>
                            @error('address_line1') 
                                <div class="wa-error-msg"><i class="bi bi-info-circle"></i> {{ $message }}</div> 
                            @enderror
                        </div>

                        <!-- 4. City / Town & District -->
                        <div class="wa-form-row wa-form-row-2">
                            <div class="wa-form-group" style="margin-bottom: 0;">
                                <label class="wa-label">City / Town <span class="req">*</span></label>
                                <input type="text" 
                                       class="wa-input @error('city') is-error @enderror" 
                                       wire:model="city" 
                                       placeholder="e.g., Jaffna, Colombo, Kandy" 
                                       required>
                                @error('city') 
                                    <div class="wa-error-msg"><i class="bi bi-info-circle"></i> {{ $message }}</div> 
                                @enderror
                            </div>
                            <div class="wa-form-group" style="margin-bottom: 0;">
                                <label class="wa-label">District / Area <span style="font-weight: normal; color: #94a3b8;">(Optional)</span></label>
                                <input type="text" 
                                       class="wa-input" 
                                       wire:model="district" 
                                       placeholder="e.g., Jaffna">
                            </div>
                        </div>

                        <!-- 5. Payment Selection -->
                        <div class="wa-card-header-title" style="margin-top: 1.5rem;">
                            <i class="bi bi-wallet2"></i> Payment Method
                        </div>

                        <div class="wa-payment-grid">
                            <div class="wa-payment-card {{ $payment_method === 'Cash on Delivery' ? 'active' : '' }}" 
                                 wire:click="$set('payment_method', 'Cash on Delivery')">
                                <div class="wa-payment-icon">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <div>
                                    <div class="wa-payment-title">Cash on Delivery</div>
                                    <div class="wa-payment-subtitle">Pay cash to courier</div>
                                </div>
                                <div class="wa-radio-dot"></div>
                            </div>

                            <div class="wa-payment-card {{ $payment_method === 'Bank Transfer' ? 'active' : '' }}" 
                                 wire:click="$set('payment_method', 'Bank Transfer')">
                                <div class="wa-payment-icon">
                                    <i class="bi bi-bank"></i>
                                </div>
                                <div>
                                    <div class="wa-payment-title">Bank Transfer</div>
                                    <div class="wa-payment-subtitle">Direct Bank Deposit</div>
                                </div>
                                <div class="wa-radio-dot"></div>
                            </div>
                        </div>

                        {{-- Bank Details (Only when Bank Transfer is selected) --}}
                        @if($payment_method === 'Bank Transfer')
                            <div class="wa-bank-box">
                                <div style="display: flex; align-items: center; gap: 0.4rem; color: #8c6819; font-weight: 700; font-size: 0.88rem;">
                                    <i class="bi bi-info-circle-fill"></i> Jaffna Gold Bank Account:
                                </div>

                                <div class="wa-bank-info-grid">
                                    <div class="wa-bank-item">
                                        <label>Bank</label>
                                        <value>Commercial Bank</value>
                                    </div>
                                    <div class="wa-bank-item">
                                        <label>Account Name</label>
                                        <value>Jaffna Gold Covering</value>
                                    </div>
                                    <div class="wa-bank-item">
                                        <label>Account Number</label>
                                        <value style="letter-spacing: 0.04em;">8001 245 8990</value>
                                    </div>
                                    <div class="wa-bank-item">
                                        <label>Branch</label>
                                        <value>Jaffna Main Branch</value>
                                    </div>
                                </div>

                                <div class="wa-form-group" style="margin-bottom: 0;">
                                    <label class="wa-label">Upload Transfer Slip / Screenshot <span style="font-weight: normal; color: #94a3b8;">(Optional)</span></label>
                                    <input type="file" 
                                           class="wa-input" 
                                           style="padding: 0.5rem; background: #ffffff;" 
                                           wire:model="bank_slip" 
                                           accept="image/*">
                                    <div wire:loading wire:target="bank_slip" style="color: #b8860b; font-size: 0.78rem; margin-top: 0.25rem;">
                                        <span class="spinner-inline"></span> Uploading receipt...
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="wa-form-group" style="margin-bottom: 1.5rem;">
                            <label class="wa-label">Delivery Note <span style="font-weight: normal; color: #94a3b8;">(Optional)</span></label>
                            <input type="text" 
                                   class="wa-input" 
                                   wire:model="customer_notes" 
                                   placeholder="e.g., Call before delivery, Landmark...">
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="wa-submit-btn" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submitOrder" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="bi bi-shield-check"></i> Confirm Delivery Order
                            </span>
                            <span wire:loading wire:target="submitOrder" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                <span class="spinner-inline"></span> Placing Your Order...
                            </span>
                        </button>

                    </form>
                </div>

                {{-- RIGHT COLUMN: Sticky Order Summary & Total Price --}}
                <div>
                    <div class="wa-summary-card">
                        <div class="wa-card-header-title">
                            <i class="bi bi-bag-check-fill"></i> Order Summary
                        </div>

                        <div class="wa-item-list">
                            @if(is_array($whatsappOrder->items))
                                @foreach($whatsappOrder->items as $it)
                                    <div class="wa-item-card">
                                        <div class="wa-item-thumb">
                                            @if(!empty($it['image']))
                                                <img src="{{ asset($it['image']) }}" alt="{{ $it['name'] }}">
                                            @else
                                                <i class="bi bi-gem" style="color: #cca43b; font-size: 1.3rem;"></i>
                                            @endif
                                        </div>
                                        <div class="wa-item-info">
                                            <div class="wa-item-name" title="{{ $it['name'] }}">{{ $it['name'] }}</div>
                                            <div class="wa-item-meta">
                                                <span>Qty: <strong>{{ $it['quantity'] }}</strong></span>
                                                @if(!empty($it['code']))
                                                    &bull; <span>{{ $it['code'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="wa-item-price">
                                            Rs. {{ number_format($it['subtotal'] ?? ($it['unit_price'] * $it['quantity']), 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Clear Total Price Box -->
                        <div class="wa-price-box">
                            <div class="wa-price-row">
                                <span>Items Subtotal</span>
                                <span style="font-weight: 700; color: #1e293b;">Rs. {{ number_format($whatsappOrder->subtotal, 2) }}</span>
                            </div>
                            @if((float)$whatsappOrder->discount_amount > 0)
                                <div class="wa-price-row" style="color: #16a34a;">
                                    <span style="display: flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-tag-fill"></i> Discount Savings
                                    </span>
                                    <span style="font-weight: 700;">- Rs. {{ number_format($whatsappOrder->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="wa-price-row">
                                <span>Delivery Fee</span>
                                <span style="font-weight: 700; color: #1e293b;">
                                    @if((float)$whatsappOrder->delivery_charge > 0)
                                        Rs. {{ number_format($whatsappOrder->delivery_charge, 2) }}
                                    @else
                                        <span style="color: #16a34a; font-weight: 700;">FREE</span>
                                    @endif
                                </span>
                            </div>
                            <div class="wa-price-row total-row">
                                <span>Total Price</span>
                                <span class="total-amount">Rs. {{ number_format($whatsappOrder->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <div class="wa-trust-box" style="margin-bottom: 0.65rem;">
                            <i class="bi bi-patch-check-fill" style="font-size: 1.2rem;"></i>
                            <span>100% Guaranteed 22K Gold Covering.</span>
                        </div>

                        <div style="font-size: 0.74rem; color: #94a3b8; text-align: center;">
                            <i class="bi bi-shield-shaded"></i> Fast Dispatch from Jaffna, Sri Lanka
                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>
</div>
