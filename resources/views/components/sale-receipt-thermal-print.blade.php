<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - Jaffna Gold</title>
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
                <span style="width: 90px;">Branch</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ config('shop.branch', 'Vavuniya Branch') }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Receipt No</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $sale->invoice_number }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Date</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $sale->created_at->format('d-m-Y') }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Time</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $sale->created_at->format('h:i A') }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Cashier</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $sale->user->name ?? 'Admin' }}</span>
            </div>
            @php
                $thermalCustName = ($sale->customer && $sale->customer->name !== 'Walking Customer')
                    ? ($sale->customer->business_name ?: $sale->customer->name)
                    : ($sale->walking_customer_name ?: null);
                $thermalCustPhone = ($sale->customer && $sale->customer->name !== 'Walking Customer')
                    ? $sale->customer->phone
                    : ($sale->walking_customer_phone ?: null);
            @endphp
            @if($thermalCustName)
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Customer</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $thermalCustName }}</span>
            </div>
            @endif
            @if($thermalCustPhone && $thermalCustPhone !== 'xxxxx')
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Phone</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $thermalCustPhone }}</span>
            </div>
            @endif
        </div>

        <div style="border-bottom: 1px dashed #000000; margin: 8px 0;"></div>

        <!-- Boxed Table Structure -->
        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000; margin-top: 5px;">
            <thead>
                <tr style="border-bottom: 2px solid #000000;">
                    <th style="border-right: 2px solid #000000; padding: 4px; text-align: left; font-size: 11px; font-weight: 800;">Item</th>
                    <th style="border-right: 2px solid #000000; padding: 4px; text-align: center; font-size: 11px; font-weight: 800; width: 45px;">Qty</th>
                    <th style="padding: 4px; text-align: right; font-size: 11px; font-weight: 800; width: 85px;">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr style="border-bottom: 1px solid #000000;">
                    <td style="border-right: 2px solid #000000; padding: 4px; font-size: 11px; font-weight: 700; vertical-align: top; line-height: 1.3;">
                        {{ $loop->iteration }}. {{ $item->product_name }}
                        <div style="font-size: 10px; font-weight: 700; margin-top: 2px;">Price: {{ number_format($item->unit_price, 0) }}</div>
                        @if($item->discount_per_unit > 0)
                        <div style="font-size: 10px; font-weight: 800; margin-top: 1px;">
                            Disc: {{ number_format($item->discount_per_unit, 0) }}
                            @if($item->discount_percentage > 0) ({{ number_format($item->discount_percentage, 0) }}%) @endif
                        </div>
                        @endif
                        @if($item->has_warranty)
                        <div style="font-size: 10px; font-weight: 800; font-style: italic; color: #000000; margin-top: 1px;">({{ $item->warranty_duration }} Warranty)</div>
                        @endif
                    </td>
                    <td style="border-right: 2px solid #000000; padding: 4px; text-align: center; font-size: 11px; font-weight: 700; vertical-align: top;">
                        {{ $item->quantity }}
                    </td>
                    <td style="padding: 4px; text-align: right; font-size: 11px; font-weight: 700; vertical-align: top;">
                        {{ number_format($item->total, 0) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="border-bottom: 1px dashed #000000; margin: 8px 0;"></div>

        <!-- Totals Block -->
        <div style="font-size: 11px; font-weight: 700; line-height: 1.4;">
            @php
            $originalSubtotal = $sale->items->sum(fn($i) => $i->unit_price * $i->quantity);
            $totalDiscountRs = $sale->discount_amount;
            $totalDiscPercentage = $originalSubtotal > 0 ? ($totalDiscountRs / $originalSubtotal * 100) : 0;
            @endphp
            <div style="display: flex; justify-content: space-between;">
                <span>Total Items</span>
                <span>{{ $sale->items->count() }}</span>
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
                <span>{{ number_format($sale->total_amount, 0) }}</span>
            </div>

            @php
            $totalReceived = $sale->payments->sum(function($p) {
                return $p->amount_tendered ?? $p->amount;
            });
            $balanceAmount = max(0, $totalReceived - $sale->total_amount);
            @endphp
            <div style="display: flex; justify-content: space-between;">
                <span>RECEIVED</span>
                <span>{{ number_format($totalReceived, 0) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>BALANCE</span>
                <span>{{ number_format($balanceAmount, 0) }}</span>
            </div>
            @if($sale->due_amount > 0)
            <div style="display: flex; justify-content: space-between;">
                <span>DUE AMOUNT</span>
                <span>{{ number_format($sale->due_amount, 0) }}</span>
            </div>
            @endif
        </div>

        <div style="border-bottom: 1px dashed #000000; margin: 8px 0;"></div>

        <!-- Payment Method -->
        <div style="font-size: 11px; font-weight: 700; margin-bottom: 5px;">
            @if($sale->payments && $sale->payments->count() > 0)
            @foreach($sale->payments as $payment)
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 110px;">Payment Method</span>
                <span>: {{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            @endforeach
            @else
            @if($sale->payment_type == 'full' && ($sale->payments->count() == 0 || $sale->payments->first()->payment_method == 'cash'))
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
            <div style="border-bottom: 1px dashed #000000; margin: 8px 0;"></div>
        </div>

        {{-- Receipt Barcode (Vector SVG Code 128) --}}
        <div style="display: flex; justify-content: center; margin: 8px 0;">
            <svg id="barcode" class="barcode-element" style="max-width: 100%;"></svg>
        </div>

        <!-- Footer -->
        <div style="text-align: center; font-size: 11px; margin-top: 10px;">
            <div style="font-weight: 800;">Thank You for Your Visit!</div>
            <div style="font-weight: 700;">Return will be accepted within 3 days</div>
            <div style="font-weight: 700;">Visit Us Again</div>
            <div style="margin: 12px auto 0 auto; width: 80%; border-top: 1px solid #000000; border-bottom: 1px solid #000000; height: 3px;"></div>
        </div>
    </div>

    <script>
        window.onload = function() {
            // Render vector Code 128 barcode
            const saleId = '{{ $sale->invoice_number ?? $sale->sale_id }}';
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
