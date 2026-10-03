<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation Receipt - Jaffna Gold</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @page { 
            size: 80mm auto; 
            margin: 0; 
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
            background: #ffffff;
            font-family: Arial, Helvetica, sans-serif; 
            font-size: 11px;
            color: #000000;
            font-weight: 700;
        }
        .receipt-container {
            width: 80mm;
            margin: 0;
            padding: 3mm;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 700;
        }
        .barcode-element {
            image-rendering: pixelated;
            image-rendering: -webkit-optimize-contrast;
            shape-rendering: crispEdges;
            display: block;
            margin: 0 auto;
        }
        @media print {
            @page { 
                size: 80mm auto; 
                margin: 0; 
            }
            html, body {
                margin: 0; 
                padding: 0; 
                width: 80mm;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .barcode-element {
                image-rendering: pixelated;
                image-rendering: -webkit-optimize-contrast;
                shape-rendering: crispEdges;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-container" style="width: 76mm; padding: 0 3mm; background: #ffffff; color: #000000; line-height: 1.25; font-size: 12px; box-sizing: border-box;">
        <!-- Thermal Receipt Header -->
        <div style="text-align:center; font-weight:800; padding-top: 3mm;">
            <div style="border-top: 2px solid #000000; border-bottom: 2px solid #000000; padding: 5px 0; margin-bottom: 3px; font-size: 16px; text-transform: uppercase;">
                {{ config('shop.name', 'JAFFNA GOLD COVERING') }}
            </div>
            <div style="font-size: 12px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 3px;">
                {{ config('shop.branch', 'VAVUNIYA BRANCH') }}
            </div>
            <div style="font-size: 11px; font-weight: 700;">{{ config('shop.address', '2nd Cross Street, Vavuniya') }}</div>
            <div style="font-size: 11px; font-weight: 700;">Tel: {{ config('shop.phone', '0761919650') }}</div>
            <div style="font-size: 11px; font-weight: 700;">Hotline: {{ config('shop.hotline', '021 222 85 89') }}</div>
        </div>

        <div style="border-bottom: 1px dashed #000000; margin: 8px 0;"></div>

        <!-- Receipt Meta -->
        <div style="font-size: 11px; font-weight: 700;">
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Branch</span>
                <span>: {{ config('shop.branch', 'Vavuniya Branch') }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Quotation No</span>
                <span>: {{ $quotation->quotation_number }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Date</span>
                <span>: {{ $quotation->created_at->format('d-m-Y') }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Time</span>
                <span>: {{ $quotation->created_at->format('h:i A') }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Cashier</span>
                <span>: {{ $quotation->user->name ?? 'Admin' }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 85px;">Valid Until</span>
                <span>: {{ \Carbon\Carbon::parse($quotation->valid_until)->format('d-m-Y') }}</span>
            </div>
        </div>

        <div style="border-bottom: 1px dashed #000000; margin: 8px 0;"></div>

        <!-- Table Header -->
        <div style="display: flex; font-weight: 800; font-size: 12px; margin-bottom: 2px;">
            <span style="flex: 2;">Item</span>
            <span style="flex: 0.5; text-align: center;">Qty</span>
            <span style="flex: 1; text-align: right;">Price</span>
        </div>
        <div style="border-bottom: 1px solid #000000; margin-bottom: 5px;"></div>

        <!-- Items -->
        <div style="font-size: 11px;">
            @foreach($quotation->items as $item)
            <div style="display: flex; margin-bottom: 2px;">
                <span style="flex: 2; font-weight: 700;">
                    {{ $item['product_name'] ?? $item['name'] ?? 'N/A' }}
                    @if(!empty($item['has_warranty']))
                    <div style="font-size: 10px; font-weight: 800; font-style: italic; color: #000000; margin-top: 1px;">({{ $item['warranty_duration'] }} Warranty)</div>
                    @endif
                </span>
                <span style="flex: 0.5; text-align: center; font-weight: 700;">{{ $item['quantity'] }}</span>
                <span style="flex: 1; text-align: right; font-weight: 700;">{{ number_format($item['unit_price'], 0) }}</span>
            </div>
            @if(($item['discount_per_unit'] ?? $item['discount'] ?? 0) > 0)
            <div style="display: flex; font-size: 11px; color: #000000; font-weight: 800; margin-top: 1px; margin-bottom: 3px;">
                <span style="flex: 2; padding-left: 5px;">
                    Disc: {{ number_format($item['discount_per_unit'] ?? $item['discount'], 0) }}
                    @if(($item['discount_percentage'] ?? 0) > 0) ({{ number_format($item['discount_percentage'], 0) }}%) @endif
                </span>
            </div>
            @endif
            @endforeach
        </div>

        <div style="border-bottom: 1px dashed #000000; margin: 8px 0;"></div>

        <!-- Totals -->
        <div style="font-size: 11px;">
            @php
            $originalSubtotal = $quotation->subtotal;
            $totalDiscountRs = $quotation->discount_amount;
            $totalDiscPercentage = $originalSubtotal > 0 ? ($totalDiscountRs / $originalSubtotal * 100) : 0;
            @endphp
            <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-weight: 700;">
                <span>Sub Total</span>
                <span>{{ number_format($originalSubtotal, 0) }}</span>
            </div>
            @if($totalDiscountRs > 0)
            <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-weight: 700;">
                <span>Discount @if($totalDiscPercentage > 0)({{ number_format($totalDiscPercentage, 1) }}%)@endif</span>
                <span>-{{ number_format($totalDiscountRs, 0) }}</span>
            </div>
            @endif

            <div style="border-bottom: 1px dashed #000000; margin: 5px 0;"></div>

            <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 14px;">
                <span>TOTAL</span>
                <span>{{ number_format($quotation->total_amount, 0) }}</span>
            </div>

            <div style="border-bottom: 1px dashed #000000; margin: 5px 0;"></div>
        </div>

        <!-- Document Type Indicator -->
        <div style="text-align: center; font-size: 13px; font-weight: 800; margin: 5px 0; border: 2px solid #000000; padding: 4px;">
            THIS IS A QUOTATION ONLY
        </div>

        <div style="border-bottom: 1px dashed #000000; margin: 8px 0;"></div>

        {{-- Receipt Barcode (Vector SVG Code 128) --}}
        <div style="display: flex; justify-content: center; margin: 8px 0;">
            <svg id="barcode" class="barcode-element" style="max-width: 100%;"></svg>
        </div>

        <!-- Footer -->
        <div style="text-align: center; font-size: 11px; margin-top: 10px;">
            <div style="font-weight: 800;">Thank You for Requesting a Quote!</div>
            <div style="font-weight: 700;">Valid for 30 Days</div>
            <div style="font-weight: 700;">Jaffna Gold Covering</div>
            <div style="border-top: 1px solid #000000; margin-top: 8px; padding-top: 2px;"></div>
            <div style="border-top: 1px solid #000000; margin-top: 2px;"></div>
        </div>
    </div>

    <script>
        window.onload = function() {
            // Render vector Code 128 barcode
            const saleId = '{{ $quotation->quotation_number }}';
            if (typeof JsBarcode === 'function') {
                JsBarcode("#barcode", saleId, {
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
            }
            
            // Print and close
            setTimeout(function() {
                window.print();
                window.onafterprint = function() { window.close(); };
            }, 500);
        };
    </script>
</body>
</html>
