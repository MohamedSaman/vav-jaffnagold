<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Service Receipt - {{ $order->order_code }}</title>
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
    <div class="receipt-container" style="width: 76mm; padding: 0 3mm; background: white; color: #000; line-height: 1.25; font-size: 11px; box-sizing: border-box;">
        <!-- Header -->
        <div style="text-align:center; font-weight:bold; padding-top: 2mm;">
            <div style="border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 4px 0; margin-bottom: 3px; font-size: 15px; text-transform: uppercase;">
                {{ config('shop.name', 'JAFFNA GOLD COVERING') }}
            </div>
            <div style="font-size: 11px; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 3px;">
                {{ config('shop.branch', 'VAVUNIYA BRANCH') }}
            </div>
            <div style="font-size: 10px; font-weight: normal;">{{ config('shop.address', '2nd Cross Street, Vavuniya') }}</div>
            <div style="font-size: 10px; font-weight: normal;">Tel: {{ config('shop.phone', '0761919650') }} | Hotline: {{ config('shop.hotline', '021 222 85 89') }}</div>
            <div style="margin-top: 5px; font-size: 12px; background: #000; color: #fff; padding: 2px 0; text-transform: uppercase;">
                @if($order->order_category === 'handmade')
                    WORKSHOP PLATING JOB SLIP
                @elseif($order->order_category === 'shop')
                    OUR SHOP WORK / PLATING JOB SLIP
                @else
                    SERVICE / PLATING RECEIPT
                @endif
            </div>
        </div>

        <div style="border-bottom: 1px dashed #000; margin: 6px 0;"></div>

        <!-- Meta Info -->
        <div style="font-size: 11px; font-weight: bold;">
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Branch</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ config('shop.branch', 'Vavuniya Branch') }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Order No</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $order->order_code }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Received Date</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $order->received_date ? $order->received_date->format('d-m-Y') : $order->created_at->format('d-m-Y') }}</span>
            </div>
            @if($order->expected_delivery_date)
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Expected Date</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $order->expected_delivery_date->format('d-m-Y') }}</span>
            </div>
            @endif
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Staff</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $order->user->name ?? 'Shop Staff' }}</span>
            </div>
            @if($order->worker)
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Craftsman</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $order->worker->name }}</span>
            </div>
            @endif
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Status</span>
                <span style="margin-right: 5px;">:</span>
                <span style="text-transform: uppercase;">{{ str_replace('_', ' ', $order->status) }}</span>
            </div>
        </div>

        <div style="border-bottom: 1px dashed #000; margin: 6px 0;"></div>

        <!-- Customer Info -->
        <div style="font-size: 11px; font-weight: bold; margin-bottom: 4px;">
            <div style="font-size: 10px; color: #555; text-transform: uppercase; margin-bottom: 2px;">
                @if($order->order_category === 'handmade')
                    Source / Inventory Details:
                @elseif($order->order_category === 'shop')
                    Internal Shop Details:
                @else
                    Customer Details:
                @endif
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">
                    @if($order->order_category === 'handmade')
                        Inventory
                    @elseif($order->order_category === 'shop')
                        Origin
                    @else
                        Name
                    @endif
                </span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $order->customer_name }}</span>
            </div>
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">
                    @if($order->order_category === 'handmade')
                        Supplier/Batch
                    @elseif($order->order_category === 'shop')
                        Reference
                    @else
                        Phone
                    @endif
                </span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $order->customer_phone }}</span>
            </div>
            @if($order->customer_address)
            <div style="display: flex; margin-bottom: 2px;">
                <span style="width: 90px;">Address</span>
                <span style="margin-right: 5px;">:</span>
                <span>{{ $order->customer_address }}</span>
            </div>
            @endif
        </div>

        <div style="border-bottom: 1px dashed #000; margin: 6px 0;"></div>

        <!-- Service & Item Details Table -->
        <div style="font-size: 10px; color: #555; text-transform: uppercase; margin-bottom: 3px;">
            Service: <span style="color:#000; font-weight:bold;">{{ str_replace('_', ' ', $order->service_type) }}</span>
        </div>
        @if($order->items && $order->items->count() > 0)
        <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000; margin-top: 3px;">
            <thead>
                <tr style="border-bottom: 1.5px solid #000; background: #eee; font-size: 10px;">
                    <th style="padding: 4px 5px; text-align: left; text-transform: uppercase;">ITEMS</th>
                    <th style="padding: 4px 5px; text-align: right; text-transform: uppercase; width: 80px;">{{ $order->order_category === 'shop' ? 'COST (RS.)' : 'FEE (RS.)' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr style="border-bottom: 1px dashed #bbb; font-size: 11px;">
                    <td style="padding: 4px 5px; text-align: left; font-weight: bold;">
                        @if($item->isReceived())
                            <span style="font-size: 10px; font-weight: 900;">[✓]</span>
                        @else
                            <span style="font-size: 10px; color: #666;">[ ]</span>
                        @endif
                        {{ $item->item_name }}
                    </td>
                    <td style="padding: 4px 5px; text-align: right; font-weight: bold;">
                        @if($order->order_category === 'shop')
                            {{ $item->cost > 0 ? number_format($item->cost, 0) : '-' }}
                        @elseif($order->is_charge_tbd && $item->fee == 0)
                            <span style="font-size: 9px;">T.B.D.</span>
                        @else
                            {{ number_format($item->fee, 0) }}
                        @endif
                    </td>
                </tr>
                @endforeach
                @if($order->notes)
                <tr style="border-top: 1px solid #000;">
                    <td colspan="2" style="padding: 3px 5px; font-size: 9px; font-weight: normal;">
                        <strong>Notes:</strong> {{ $order->notes }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
        @else
        <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000; margin-top: 3px;">
            <tbody>
                <tr style="border-bottom: 1px solid #000;">
                    <td style="padding: 4px; font-weight: bold; width: 85px; border-right: 1px solid #000; background: #f5f5f5;">Service</td>
                    <td style="padding: 4px; font-weight: bold; text-transform: uppercase;">{{ str_replace('_', ' ', $order->service_type) }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #000;">
                    <td style="padding: 4px; font-weight: bold; border-right: 1px solid #000; background: #f5f5f5;">Item</td>
                    <td style="padding: 4px; font-weight: bold;">{{ $order->item_description }}</td>
                </tr>
                @if($order->notes)
                <tr>
                    <td style="padding: 4px; font-weight: bold; border-right: 1px solid #000; background: #f5f5f5;">Notes</td>
                    <td style="padding: 4px; font-size: 10px;">{{ $order->notes }}</td>
                </tr>
                @endif
            </tbody>
        </table>
        @endif

        <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

        <!-- Payment & Charges Breakdown -->
        @if($order->order_category === 'handmade' || $order->order_category === 'shop')
        <table style="width: 100%; border-collapse: collapse; font-size: 11px; font-weight: bold;">
            <tr>
                <td style="padding: 3px 0; text-align: left;">Workshop Charge / Cost:</td>
                <td style="padding: 3px 0; text-align: right; font-size: 13px;">Rs. {{ number_format($order->our_cost, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 2px 0; text-align: left; font-size: 10px; color: #555;">Assigned Craftsman:</td>
                <td style="padding: 2px 0; text-align: right; font-size: 11px;">{{ $order->worker->name ?? 'Unassigned' }}</td>
            </tr>
            <tr style="border-top: 1.5px solid #000;">
                <td style="padding: 4px 0; text-align: left; font-size: 11px; font-weight: 900;">Order Type:</td>
                <td style="padding: 4px 0; text-align: right; font-size: 11px; font-weight: 900;">
                    {{ $order->order_category === 'handmade' ? 'Shop Handmade Stock' : 'Our Shop Work (Internal)' }}
                </td>
            </tr>
        </table>
        @else
        <table style="width: 100%; border-collapse: collapse; font-size: 11px; font-weight: bold;">
            <tr>
                <td style="padding: 3px 0; text-align: left;">Total Service Charge:</td>
                <td style="padding: 3px 0; text-align: right; font-size: 12px;">
                    @if($order->is_charge_tbd || is_null($order->customer_charge))
                        <span style="font-weight: 900; background: #eee; padding: 1px 4px; border: 1px solid #000;">T.B.D.</span>
                    @else
                        Rs. {{ number_format($order->customer_charge, 2) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 3px 0; text-align: left;">Advance Paid:</td>
                <td style="padding: 3px 0; text-align: right; color: #000;">
                    Rs. {{ number_format($order->advance_amount, 2) }}
                </td>
            </tr>
            @if($order->advance_payment_method)
            <tr>
                <td style="padding: 2px 0; text-align: left; font-size: 10px; font-weight: normal; color: #555;">Payment Mode:</td>
                <td style="padding: 2px 0; text-align: right; font-size: 10px; text-transform: uppercase;">
                    {{ str_replace('_', ' ', $order->advance_payment_method) }}
                </td>
            </tr>
            @endif

            @php
                $totalPaid = $order->payments->sum('amount');
                $remainingBalance = ($order->is_charge_tbd || is_null($order->customer_charge)) ? null : max(0, $order->customer_charge - $totalPaid);
            @endphp

            @if($order->payments->count() > 1)
            <tr>
                <td style="padding: 3px 0; text-align: left;">Total Collected:</td>
                <td style="padding: 3px 0; text-align: right;">Rs. {{ number_format($totalPaid, 2) }}</td>
            </tr>
            @endif

            <tr style="border-top: 1.5px solid #000;">
                <td style="padding: 4px 0; text-align: left; font-size: 12px; font-weight: 900;">Balance Due:</td>
                <td style="padding: 4px 0; text-align: right; font-size: 13px; font-weight: 900;">
                    @if($order->is_charge_tbd || is_null($order->customer_charge))
                        <span style="background: #eee; padding: 1px 4px; border: 1px solid #000;">T.B.D.</span>
                    @else
                        Rs. {{ number_format($remainingBalance ?? $order->balance_amount, 2) }}
                    @endif
                </td>
            </tr>
        </table>
        @endif

        <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

        <!-- Barcode (Vector SVG Code 128) -->
        <div style="display: flex; justify-content: center; margin: 6px 0;">
            <svg id="barcode" class="barcode-element" style="max-width: 100%;"></svg>
        </div>

        <!-- Footer -->
        <div style="text-align: center; font-size: 10px; margin-top: 8px;">
            @if($order->order_category === 'shop')
                <div style="font-weight: 800; text-transform: uppercase;">* Internal Shop Plating Job Slip *</div>
                <div style="font-size: 9px; font-weight: 700; margin-top: 2px;">Return plated items directly to shop inventory</div>
            @else
                <div style="font-weight: 800; text-transform: uppercase;">* Please bring this receipt for collection *</div>
                <div style="font-size: 9px; font-weight: 700; margin-top: 2px;">Items not claimed within 30 days are not our responsibility</div>
            @endif
            <div style="font-weight: 800; margin-top: 3px;">Thank You for Choosing Jaffna Gold!</div>
            <div style="margin: 8px auto 0 auto; width: 80%; border-top: 1px solid #000000; border-bottom: 1px solid #000000; height: 3px;"></div>
        </div>
    </div>

    <script>
        window.onload = function() {
            const orderCode = '{{ $order->order_code }}';
            if (typeof JsBarcode === 'function') {
                JsBarcode("#barcode", orderCode, {
                    format: 'CODE128',
                    width: 2,
                    height: 50,
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
            
            setTimeout(function() {
                window.print();
                window.onafterprint = function() { window.close(); };
            }, 500);
        };
    </script>
</body>
</html>
