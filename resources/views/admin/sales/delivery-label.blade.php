<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Label - {{ $sale->deliverySale->delivery_barcode ?? 'N/A' }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @page {
            size: 6in 4in;
            margin: 0;
        }

        html, body {
            width: 6in;
            height: 4in;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 700;
            color: #000000;
            background: #ffffff;
            overflow: hidden;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .label-wrapper {
            width: 6in;
            height: 4in;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 700;
            background: #ffffff;
            overflow: hidden;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        .label-border {
            border: 2px solid #000000;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 10px;
            border-bottom: 2px solid #000000;
            position: relative;
        }

        .company-name {
            flex: 1;
            font-size: 26px;
            font-weight: 800;
            line-height: 1.1;
            text-align: center;
            text-transform: uppercase;
        }

        .company-name span {
            font-size: 13px;
            font-weight: 800;
            display: block;
            text-align: center;
            letter-spacing: 3px;
        }

        .meta {
            text-align: right;
            font-weight: 800;
            position: absolute;
            right: 10px;
            top: 6px;
        }

        .meta .method {
            font-size: 18px;
            font-weight: 800;
        }

        .meta .date {
            font-size: 14px;
            font-weight: 800;
            margin-top: 2px;
        }

        /* CUSTOMER + BARCODE ROW */
        .row {
            display: flex;
            width: 100%;
        }

        .row-grow {
            flex: 1;
        }

        .customer-section {
            flex: 2;
            padding: 8px 10px;
            border-right: 2px solid #000000;
            border-bottom: 2px solid #000000;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .to-label {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .customer-line {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.35;
            display: block;
            margin-left: 18px;
            color: #000000;
        }

        .customer-name-line {
            font-size: 20px;
            font-weight: 800;
            line-height: 1.3;
            display: block;
            margin-left: 18px;
            color: #000000;
        }

        .customer-phone-line {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.35;
            display: block;
            margin-left: 18px;
            color: #000000;
        }

        .barcode-section {
            flex: 1;
            padding: 6px 8px;
            text-align: center;
            border-bottom: 2px solid #000000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #ffffff;
        }

        .barcode-element,
        .barcode-section svg {
            width: 100%;
            max-width: 100%;
            height: 65px;
            image-rendering: pixelated;
            image-rendering: -webkit-optimize-contrast;
            shape-rendering: crispEdges;
            display: block;
            margin: 0 auto;
        }

        .barcode-text {
            font-size: 13px;
            font-weight: 800;
            margin-top: 3px;
            letter-spacing: 1px;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
        }

        /* PRICE + ICONS ROW */
        .price-row,
        .icons-row {
            flex: 1;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            padding: 6px;
            border-bottom: 2px solid #000000;
        }

        .price-row {
            background: #ffffff;
            border-right: 2px solid #000000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            line-height: 1.15;
            padding: 4px;
        }

        .price-title {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #000000;
        }

        .price-amount {
            font-size: 34px;
            font-weight: 800;
            color: #000000;
        }

        .icons-row {
            justify-content: space-evenly;
            padding: 6px;
            background: #ffffff;
        }

        .icon-item {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            border: 2px solid #000000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* COD black label style */
        .cod-box {
            width: 70px;
            height: 44px;
            background: #000000;
            color: #ffffff;
            font-weight: 800;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* FOOTER */
        .footer {
            padding: 4px 10px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.35;
            color: #000000;
        }

        @media print {
            @page {
                size: 6in 4in;
                margin: 0;
            }
            html, body {
                width: 6in;
                height: 4in;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                overflow: hidden;
            }
            .label-wrapper {
                width: 6in;
                height: 4in;
                margin: 0;
                padding: 0;
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            .barcode-element,
            .barcode-section svg {
                image-rendering: pixelated;
                image-rendering: -webkit-optimize-contrast;
                shape-rendering: crispEdges;
            }
        }
    </style>
</head>

<body>
    <div class="label-wrapper">
        <div class="label-border">

            <!-- HEADER -->
            <div class="header">
                <div class="company-name">
                    {{ config('shop.name', 'JAFFNA GOLD COVERING') }} <br><span style="letter-spacing: 2px;">{{ strtoupper(config('shop.branch', 'VAVUNIYA BRANCH')) }}</span>
                </div>
                <div class="meta">
                    <div class="method">
                        {{ strtoupper($sale->deliverySale->delivery_method ?? 'POST') }}
                        @if(($sale->deliverySale->payment_method ?? '') === 'Cash on Delivery' || $sale->payment_type === 'cod')
                            <span style="display:block; font-size:13px; color:#000000; font-weight:800;">[ COD ]</span>
                        @else
                            <span style="display:block; font-size:13px; color:#000000; font-weight:800;">[ PREPAID ]</span>
                        @endif
                    </div>
                    <div class="date">
                        {{ $sale->created_at->format('d.m.Y') }}
                    </div>
                </div>
            </div>

            <!-- CUSTOMER + BARCODE -->
            <div class="row row-grow">
                <div class="customer-section">
                    <div class="to-label">TO :</div>
                    @php
                        $custName = ($sale->customer && $sale->customer->name !== 'Walking Customer') 
                            ? $sale->customer->name 
                            : ($sale->walking_customer_name ?: null);
                        $custPhone = ($sale->customer && $sale->customer->phone && $sale->customer->phone !== 'xxxxx')
                            ? $sale->customer->phone
                            : ($sale->walking_customer_phone ?: null);
                        $custAddress = ($sale->customer && $sale->customer->address && $sale->customer->address !== 'xxxxx')
                            ? $sale->customer->address
                            : null;

                        $rawDetails = $sale->deliverySale->customer_details ?? '';
                        if (!$custName && preg_match('/Customer:\s*([^|\n]+)/i', $rawDetails, $m)) {
                            $custName = trim($m[1]);
                        }
                        if (!$custPhone && preg_match('/Phone:\s*([^|\n]+)/i', $rawDetails, $m)) {
                            $custPhone = trim($m[1]);
                        }
                        if (!$custAddress && preg_match('/Address:\s*(.+)/is', $rawDetails, $m)) {
                            $custAddress = trim($m[1]);
                        }
                    @endphp

                    @if($custName)
                        <span class="customer-name-line">{{ $custName }}</span>
                    @endif

                    @if($custPhone)
                        <span class="customer-phone-line">Tel: {{ $custPhone }}</span>
                    @endif

                    @if($custAddress)
                        @foreach(preg_split('/\r\n|\r|\n|,/', $custAddress) as $addrLine)
                            @if(trim($addrLine))
                                <span class="customer-line">{{ trim($addrLine) }}</span>
                            @endif
                        @endforeach
                    @elseif(!$custName && !$custPhone)
                        @php
                            $parts = preg_split('/\s*[\|]\s*|\n|\r\n/', $rawDetails);
                        @endphp
                        @foreach($parts as $part)
                            @if(trim($part))
                                <span class="customer-line">{{ trim($part) }}</span>
                            @endif
                        @endforeach
                    @endif
                </div>

                <div class="barcode-section">
                    <svg id="barcode" class="barcode-element"></svg>
                    <div class="barcode-text">
                        {{ $sale->deliverySale?->delivery_barcode ?? $sale->invoice_number }}
                    </div>
                </div>
            </div>

            <!-- PRICE + ICONS -->
            <div class="row">
                <div class="price-row">
                    @if(($sale->deliverySale->payment_method ?? '') === 'Cash on Delivery' || $sale->payment_type === 'cod')
                        <span class="price-title">COLLECT COD:</span>
                        <span class="price-amount">Rs.{{ number_format($sale->due_amount > 0 ? $sale->due_amount : $sale->total_amount, 2) }}</span>
                    @else
                        <span class="price-title">PREPAID / PAID:</span>
                        <span class="price-amount">Rs.{{ number_format($sale->total_amount, 2) }}</span>
                    @endif
                </div>

                <div class="icons-row">

                    <!-- Fragile -->
                    <div class="icon-item">
                        <div class="icon-box">
                            <svg viewBox="0 0 100 100" width="36" height="36">
                                <rect x="5" y="5" width="90" height="90"
                                    fill="none" stroke="black" stroke-width="6" />

                                <!-- Glass -->
                                <path d="M30 25 H70 C70 50 55 60 50 60 C45 60 30 50 30 25 Z" fill="black" />

                                <line x1="50" y1="60" x2="50" y2="80"
                                    stroke="black" stroke-width="6" />
                                <line x1="40" y1="80" x2="60" y2="80"
                                    stroke="black" stroke-width="6" />
                            </svg>
                        </div>
                    </div>

                    <!-- Recycle -->
                    <div class="icon-item">
                        <div class="icon-box">
                            <svg viewBox="0 0 100 100" width="36" height="36">
                                <rect x="5" y="5" width="90" height="90"
                                    fill="none" stroke="black" stroke-width="6" />

                                <polygon points="50,20 60,40 40,40" fill="black" />
                                <polygon points="20,60 40,55 35,75" fill="black" />
                                <polygon points="80,60 65,75 60,55" fill="black" />
                            </svg>
                        </div>
                    </div>

                    <!-- This Side Up -->
                    <div class="icon-item">
                        <div class="icon-box">
                            <svg viewBox="0 0 100 100" width="36" height="36">
                                <!-- Outer Border -->
                                <rect x="5" y="5" width="90" height="90"
                                    fill="none" stroke="black" stroke-width="6" />

                                <!-- Left Arrow -->
                                <line x1="35" y1="75" x2="35" y2="35"
                                    stroke="black" stroke-width="8" />
                                <polygon points="35,20 20,40 50,40" fill="black" />

                                <!-- Right Arrow -->
                                <line x1="65" y1="75" x2="65" y2="35"
                                    stroke="black" stroke-width="8" />
                                <polygon points="65,20 50,40 80,40" fill="black" />
                            </svg>
                        </div>
                    </div>

                    <!-- COD / PAID Box -->
                    @if($sale->deliverySale && $sale->deliverySale->payment_method === 'Cash on Delivery')
                    <div class="icon-item">
                        <div class="cod-box">
                            COD
                        </div>
                    </div>
                    @else
                    <div class="icon-item">
                        <div class="cod-box">
                            PAID
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                <div>BRANCH: {{ strtoupper(config('shop.branch', 'VAVUNIYA BRANCH')) }} | {{ config('shop.address', '2ND CROSS STREET, VAVUNIYA') }}</div>
                <div>TEL: {{ config('shop.phone', '076 1919 650') }} | HOTLINE: {{ config('shop.hotline', '021 222 85 89') }}</div>
                <div>Return will be accepted within 3 days</div>
                <div>Thank you for your purchase!</div>
            </div>

        </div>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $sale->deliverySale->delivery_barcode ?? $sale->invoice_number ?? 'N/A' }}", {
            format: "CODE128",
            width: 2,
            height: 65,
            displayValue: false,
            margin: 0,
            lineColor: "#000000",
            background: "#ffffff"
        });

        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>