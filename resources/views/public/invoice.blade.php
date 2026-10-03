<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_number }} - Jaffna Gold</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        :root {
            --gold-primary: #D4AF37;
            --gold-light: #F9E79F;
            --gold-dark: #9A7B1C;
            --slate-900: #0F172A;
            --slate-800: #1E293B;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-100: #F1F5F9;
            --slate-50: #F8FAFC;
            --emerald-500: #10B981;
            --rose-500: #F43F5E;
            --amber-500: #F59E0B;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0B0F19 0%, #172033 100%);
            color: #1E293B;
            min-height: 100vh;
            padding: 24px 16px 80px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .heading-font {
            font-family: 'Outfit', sans-serif;
        }

        .container {
            max-width: 820px;
            margin: 0 auto;
        }

        /* Top Action Bar (floating on desktop) */
        .actions-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(212, 175, 55, 0.15);
            border: 1px solid rgba(212, 175, 55, 0.35);
            color: #F7DF84;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .btn-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #D4AF37 0%, #B8860B 100%);
            color: #0F172A;
            box-shadow: 0 4px 14px rgba(212, 175, 55, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.45);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .btn-whatsapp {
            background: #25D366;
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3);
        }

        .btn-whatsapp:hover {
            background: #20BA5A;
            transform: translateY(-1px);
        }

        /* Invoice Paper */
        .invoice-card {
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Invoice Header */
        .invoice-header {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: #FFFFFF;
            padding: 32px 36px;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid var(--gold-primary);
        }

        .invoice-header::after {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .brand-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 2px;
            color: #FFFFFF;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-title .gold-accent {
            color: var(--gold-primary);
        }

        .brand-subtitle {
            font-size: 12px;
            color: #94A3B8;
            font-weight: 500;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .brand-contacts {
            font-size: 11px;
            color: #CBD5E1;
            line-height: 1.6;
        }

        .brand-contacts span {
            display: inline-block;
            margin-right: 12px;
        }

        .invoice-status-block {
            text-align: right;
        }

        .invoice-number-tag {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #FFFFFF;
            margin-bottom: 6px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-paid {
            background: rgba(16, 185, 129, 0.15);
            color: #34D399;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }

        .status-partial {
            background: rgba(245, 158, 11, 0.15);
            color: #FBBF24;
            border: 1px solid rgba(245, 158, 11, 0.35);
        }

        .status-due {
            background: rgba(244, 63, 94, 0.15);
            color: #FB7185;
            border: 1px solid rgba(244, 63, 94, 0.35);
        }

        /* Invoice Meta Bar */
        .meta-strip {
            background: #F8FAFC;
            border-bottom: 1px solid #E2E8F0;
            padding: 18px 36px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }

        .meta-item-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            color: #64748B;
            margin-bottom: 3px;
        }

        .meta-item-value {
            font-size: 14px;
            font-weight: 700;
            color: #0F172A;
        }

        /* Invoice Body */
        .invoice-body {
            padding: 32px 36px;
        }

        /* Items Table */
        .items-table-wrapper {
            margin-bottom: 24px;
            overflow-x: auto;
        }

        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .items-table th {
            background: #F1F5F9;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 12px 14px;
            text-align: left;
            border-top: 1px solid #E2E8F0;
            border-bottom: 1px solid #E2E8F0;
        }

        .items-table th:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .items-table th:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .items-table td {
            padding: 14px;
            font-size: 13px;
            color: #1E293B;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .item-name {
            font-weight: 600;
            color: #0F172A;
            margin-bottom: 2px;
        }

        .item-meta {
            font-size: 11px;
            color: #64748B;
        }

        .warranty-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            background: #ECFDF5;
            color: #059669;
            border: 1px solid #A7F3D0;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            margin-top: 3px;
        }

        /* Financial Summary Card */
        .summary-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            padding-top: 16px;
            border-top: 1px solid #E2E8F0;
        }

        .notes-and-terms {
            font-size: 12px;
            color: #64748B;
            line-height: 1.6;
        }

        .terms-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-top: 8px;
        }

        .terms-title {
            font-weight: 700;
            color: #0F172A;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .totals-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 18px 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-bottom: 10px;
            color: #475569;
        }

        .total-row.discount-row {
            color: #059669;
            font-weight: 600;
        }

        .grand-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 2px dashed #CBD5E1;
        }

        .grand-total-label {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0F172A;
        }

        .grand-total-val {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #0F172A;
        }

        .paid-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #E2E8F0;
            color: #059669;
            font-weight: 600;
        }

        .due-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            margin-top: 8px;
            padding: 8px 12px;
            background: #FFF1F2;
            border: 1px solid #FECDD3;
            border-radius: 8px;
            color: #E11D48;
            font-weight: 700;
        }

        /* Payments History Strip */
        .payments-strip {
            margin-top: 20px;
            padding: 12px 16px;
            background: #F1F5F9;
            border-radius: 10px;
            font-size: 12px;
        }

        .payments-title {
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        /* Footer watermark */
        .invoice-footer {
            background: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            padding: 24px 36px;
            text-align: center;
            color: #64748B;
            font-size: 12px;
            line-height: 1.6;
        }

        .footer-gold-bar {
            width: 60px;
            height: 3px;
            background: var(--gold-primary);
            margin: 0 auto 12px auto;
            border-radius: 2px;
        }

        /* Mobile Adjustments */
        @media (max-width: 640px) {
            body {
                padding: 12px 8px 90px 8px;
            }

            .invoice-header {
                padding: 22px 18px;
            }

            .brand-row {
                flex-direction: column;
                gap: 14px;
            }

            .invoice-status-block {
                text-align: left;
            }

            .meta-strip {
                padding: 14px 18px;
                grid-template-columns: 1fr 1fr;
            }

            .invoice-body {
                padding: 20px 16px;
            }

            .summary-wrapper {
                grid-template-columns: 1fr;
            }

            .invoice-footer {
                padding: 18px 16px;
            }

            .actions-bar {
                justify-content: center;
            }
        }

        /* Print Stylesheet */
        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
                color: #000000 !important;
            }

            .actions-bar,
            .mobile-bottom-bar {
                display: none !important;
            }

            .invoice-card {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }

            .invoice-header {
                background: #0F172A !important;
                color: #FFFFFF !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .meta-strip,
            .totals-card,
            .terms-box,
            .items-table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Mobile Sticky Bottom Bar */
        .mobile-bottom-bar {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            padding: 12px 16px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.4);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 100;
        }

        @media (max-width: 640px) {
            .mobile-bottom-bar {
                display: flex;
                align-items: center;
                justify-content: space-around;
                gap: 8px;
            }

            .mobile-bottom-bar .btn {
                flex: 1;
                justify-content: center;
                padding: 10px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    {{-- Top Action Bar --}}
    <div class="actions-bar">
        <div class="badge-verified">
            <span class="material-symbols-outlined" style="font-size: 16px;">verified</span>
            Official Jaffna Gold Digital Invoice
        </div>
        <div class="btn-group">
            <button onclick="window.print()" class="btn btn-secondary">
                <span class="material-symbols-outlined" style="font-size: 16px;">print</span>
                Print / Save PDF
            </button>
            <a href="https://wa.me/94761919650?text={{ urlencode('Hello Jaffna Gold, I am checking my Invoice #' . $sale->invoice_number . ' for total Rs.' . number_format($sale->total_amount, 2)) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="btn btn-whatsapp">
                <span class="material-symbols-outlined" style="font-size: 16px;">chat</span>
                WhatsApp Us
            </a>
            <a href="tel:0761919650" class="btn btn-secondary">
                <span class="material-symbols-outlined" style="font-size: 16px;">call</span>
                Call Store
            </a>
        </div>
    </div>

    {{-- Main Invoice Card --}}
    <div class="invoice-card">
        {{-- Header --}}
        <div class="invoice-header">
            <div class="brand-row">
                <div>
                    <h1 class="brand-title">
                        <span>JAFFNA <span class="gold-accent">GOLD</span></span>
                        <span style="display:block; font-size: 1.15rem; color: #d4af37; letter-spacing: 2px; text-transform: uppercase; margin-top: 4px;">{{ config('shop.branch', 'VAVUNIYA BRANCH') }}</span>
                    </h1>
                    <p class="brand-subtitle">{{ config('shop.tagline', 'Handcrafted 22K Gold & 925 Sterling Silver Jewelry') }}</p>
                    <div class="brand-contacts">
                        <span><strong style="color:#FFFFFF;">Branch:</strong> {{ config('shop.branch', 'Vavuniya Branch') }}</span><br>
                        <span><strong style="color:#FFFFFF;">Address:</strong> {{ config('shop.address', '2nd Cross Street, Vavuniya') }}</span><br>
                        <span><strong style="color:#FFFFFF;">Hotline:</strong> {{ config('shop.phone', '076 191 9650') }} / {{ config('shop.hotline', '021 222 8589') }}</span>
                        <span><strong style="color:#FFFFFF;">Email:</strong> {{ config('shop.email', 'JaffnaGoldlanka@gmail.com') }}</span>
                    </div>
                </div>

                <div class="invoice-status-block">
                    <div class="invoice-number-tag">#{{ $sale->invoice_number }}</div>
                    @php
                        $paymentStatus = strtolower($sale->payment_status ?? 'paid');
                        $isFullyPaid = ($paymentStatus === 'paid') || ($sale->due_amount <= 0 && $sale->total_amount > 0);
                        $isPartial = ($paymentStatus === 'partial') || ($sale->due_amount > 0 && ($sale->total_amount - $sale->due_amount) > 0);
                    @endphp

                    @if($isFullyPaid)
                        <span class="status-pill status-paid">
                            <span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span>
                            Paid in Full
                        </span>
                    @elseif($isPartial)
                        <span class="status-pill status-partial">
                            <span class="material-symbols-outlined" style="font-size: 14px;">pending</span>
                            Partially Paid
                        </span>
                    @else
                        <span class="status-pill status-due">
                            <span class="material-symbols-outlined" style="font-size: 14px;">warning</span>
                            Payment Due
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta Strip --}}
        <div class="meta-strip">
            <div>
                <div class="meta-item-label">Branch</div>
                <div class="meta-item-value" style="color: #d4af37; font-weight: 700;">{{ config('shop.branch', 'Vavuniya Branch') }}</div>
            </div>

            <div>
                <div class="meta-item-label">Customer</div>
                <div class="meta-item-value">{{ $sale->customer_display_name ?: 'Valued Customer' }}</div>
                @if($sale->customer_display_phone && $sale->customer_display_phone !== 'xxxxx')
                    <div style="font-size: 12px; color: #64748B; margin-top: 2px;">{{ $sale->customer_display_phone }}</div>
                @endif
            </div>

            <div>
                <div class="meta-item-label">Invoice Date</div>
                <div class="meta-item-value">{{ $sale->created_at ? $sale->created_at->format('d M Y') : date('d M Y') }}</div>
                <div style="font-size: 12px; color: #64748B; margin-top: 2px;">{{ $sale->created_at ? $sale->created_at->format('h:i A') : '' }}</div>
            </div>

            <div>
                <div class="meta-item-label">Sale ID</div>
                <div class="meta-item-value" style="font-family: monospace;">{{ $sale->sale_id ?: 'INV-' . $sale->id }}</div>
            </div>

            <div>
                <div class="meta-item-label">Served By</div>
                <div class="meta-item-value">{{ $sale->user->name ?? 'Store Cashier' }}</div>
            </div>
        </div>

        {{-- Items List --}}
        <div class="invoice-body">
            <div class="items-table-wrapper">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 36px; text-align: center;">#</th>
                            <th>Description</th>
                            <th style="width: 60px; text-align: center;">Qty</th>
                            <th style="width: 110px; text-align: right;">Unit Price</th>
                            @if($sale->items->sum('total_discount') > 0)
                            <th style="width: 100px; text-align: right;">Discount</th>
                            @endif
                            <th style="width: 120px; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $index => $item)
                        <tr>
                            <td style="text-align: center; color: #94A3B8; font-weight: 600;">{{ $index + 1 }}</td>
                            <td>
                                <div class="item-name">{{ $item->product_name }}</div>
                                <div class="item-meta">
                                    @if($item->product_code)
                                        <span>Code: {{ $item->product_code }}</span>
                                    @endif
                                    @if($item->product_model && $item->product_model !== 'N/A')
                                        <span>&bull; Model: {{ $item->product_model }}</span>
                                    @endif
                                    @if($item->variant_value)
                                        <span>&bull; {{ $item->variant_value }}</span>
                                    @endif
                                </div>
                                @if(!empty($item->has_warranty))
                                    <div class="warranty-tag">
                                        <span class="material-symbols-outlined" style="font-size: 12px;">verified_user</span>
                                        Warranty: {{ $item->warranty_duration ?? 'Included' }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 700;">{{ $item->quantity }}</td>
                            <td style="text-align: right; font-weight: 600;">Rs. {{ number_format($item->unit_price, 2) }}</td>
                            @if($sale->items->sum('total_discount') > 0)
                            <td style="text-align: right; color: #059669; font-weight: 600;">
                                @if($item->discount_per_unit > 0)
                                    -Rs. {{ number_format($item->discount_per_unit, 2) }}
                                    @if($item->discount_percentage > 0)
                                        <div style="font-size: 10px;">({{ number_format($item->discount_percentage, 1) }}%)</div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            @endif
                            <td style="text-align: right; font-weight: 700; color: #0F172A;">
                                Rs. {{ number_format($item->total, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary & Notes --}}
            <div class="summary-wrapper">
                <div class="notes-and-terms">
                    @if($sale->notes)
                    <div style="margin-bottom: 14px; padding: 12px; background: #F8FAFC; border-left: 3px solid var(--gold-primary); border-radius: 4px;">
                        <strong style="color: #0F172A;">Notes:</strong> {{ $sale->notes }}
                    </div>
                    @endif

                    <div class="terms-box">
                        <div class="terms-title">
                            <span class="material-symbols-outlined" style="font-size: 15px; color: var(--gold-primary);">shield</span>
                            Store Policy &amp; Authenticity Guarantee
                        </div>
                        <ul style="padding-left: 18px; margin: 0; line-height: 1.7;">
                            <li>Original receipt required for any inquiry or exchange.</li>
                            <li>Returns / Exchanges accepted within 3 days in unworn, original state.</li>
                            <li>Certified authentic 22K Gold and 925 Sterling Silver craftsmanship.</li>
                        </ul>
                    </div>

                    @if($sale->payments && $sale->payments->count() > 0)
                    <div class="payments-strip">
                        <div class="payments-title">Payment Record</div>
                        @foreach($sale->payments as $payment)
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span>{{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}
                                @if($payment->payment_date) ({{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}) @endif
                            </span>
                            <strong style="color: #059669;">Rs. {{ number_format($payment->amount, 2) }}</strong>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="totals-card">
                    <div class="total-row">
                        <span>Items Subtotal</span>
                        <span style="font-weight: 600; color: #0F172A;">Rs. {{ number_format($sale->subtotal, 2) }}</span>
                    </div>

                    @if($sale->discount_amount > 0)
                    <div class="total-row discount-row">
                        <span>Discount Applied</span>
                        <span>-Rs. {{ number_format($sale->discount_amount, 2) }}</span>
                    </div>
                    @endif

                    <div class="grand-total-row">
                        <span class="grand-total-label">Grand Total</span>
                        <span class="grand-total-val">Rs. {{ number_format($sale->total_amount, 2) }}</span>
                    </div>

                    @php
                        $paidAmount = $sale->payments->sum('amount');
                        if ($paidAmount <= 0 && $sale->payment_status === 'paid') {
                            $paidAmount = $sale->total_amount;
                        }
                    @endphp

                    <div class="paid-row">
                        <span>Total Paid</span>
                        <span>Rs. {{ number_format($paidAmount, 2) }}</span>
                    </div>

                    @if($sale->due_amount > 0)
                    <div class="due-row">
                        <span>Balance Due</span>
                        <span>Rs. {{ number_format($sale->due_amount, 2) }}</span>
                    </div>
                    @else
                    <div style="text-align: right; font-size: 11px; color: #059669; font-weight: 700; margin-top: 8px;">
                        ✓ Account Balanced (No Due)
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="invoice-footer">
            <div class="footer-gold-bar"></div>
            <p><strong>Thank you for choosing Jaffna Gold!</strong></p>
            <p style="margin-top: 4px; font-size: 11px;">For custom craftsmanship, valuations, and service inquiries, please contact our team.</p>
        </div>
    </div>
</div>

{{-- Sticky Bottom Action Bar for Mobile Viewers --}}
<div class="mobile-bottom-bar">
    <button onclick="window.print()" class="btn btn-secondary">
        <span class="material-symbols-outlined" style="font-size: 16px;">print</span>
        Print
    </button>
    <a href="https://wa.me/94761919650?text={{ urlencode('Hello Jaffna Gold, I am checking my Invoice #' . $sale->invoice_number . ' for total Rs.' . number_format($sale->total_amount, 2)) }}"
       target="_blank"
       rel="noopener noreferrer"
       class="btn btn-whatsapp">
        <span class="material-symbols-outlined" style="font-size: 16px;">chat</span>
        WhatsApp
    </a>
    <a href="tel:0761919650" class="btn btn-secondary">
        <span class="material-symbols-outlined" style="font-size: 16px;">call</span>
        Call
    </a>
</div>

</body>
</html>
