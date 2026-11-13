<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $order->order_code }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 10mm 12mm 10mm 12mm;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
        }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-info { background-color: #e0f2fe; color: #075985; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }

        .info-cards {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .info-cards td {
            padding: 8px 12px;
            vertical-align: top;
            border-right: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .info-cards td:last-child {
            border-right: none;
        }
        .info-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #334155;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 4px 6px;
            font-size: 8.5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .text-info { color: #0284c7; }
        .text-success { color: #16a34a; }
        .code-font {
            font-family: monospace;
            font-size: 8.5px;
            font-weight: bold;
            color: #0f172a;
        }

        .totals-row td {
            background-color: #e2e8f0 !important;
            font-weight: bold;
            border-top: 2px solid #475569;
            border-bottom: 2px solid #475569;
            font-size: 9px;
        }

        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
        }
        .summary-box td {
            padding: 8px 12px;
            text-align: center;
            border-right: 1px solid #cbd5e1;
        }
        .summary-box td:last-child {
            border-right: none;
        }
        .summary-box .sum-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #475569;
            font-weight: bold;
            display: block;
        }
        .summary-box .sum-val {
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
            display: block;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <h1 class="company-title">JAFFNA GOLD POS</h1>
                <div class="doc-title">
                    Purchase Order Details: <span style="color: #b45309;">{{ $order->order_code }}</span>
                </div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 2px;">
                    Detailed breakdown of products, costs, prices, and totals
                </div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div style="margin-bottom: 4px;">
                    <span class="badge {{ $order->status === 'complete' ? 'badge-success' : ($order->status === 'received' ? 'badge-info' : 'badge-warning') }}">
                        Status: {{ strtoupper($order->status ?? 'PENDING') }}
                    </span>
                </div>
                <div style="font-size: 8.5px; color: #475569;">
                    <strong>Generated:</strong> {{ date('Y-m-d H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="info-cards">
        <tr>
            <td style="width: 25%;">
                <span class="info-label">Supplier</span>
                <span class="info-value">{{ $order->supplier->name ?? 'N/A' }}</span>
                @if(!empty($order->supplier?->phone))
                    <div style="font-size: 8px; color: #64748b;">Phone: {{ $order->supplier->phone }}</div>
                @endif
                @if(!empty($order->supplier?->businessname))
                    <div style="font-size: 8px; color: #64748b;">{{ $order->supplier->businessname }}</div>
                @endif
            </td>
            <td style="width: 25%;">
                <span class="info-label">Order Dates</span>
                <div><strong>Ordered:</strong> {{ $order->order_date ? date('M d, Y', strtotime($order->order_date)) : '-' }}</div>
                <div><strong>Received:</strong> {{ $order->received_date ? date('M d, Y', strtotime($order->received_date)) : 'Not Received Yet' }}</div>
            </td>
            <td style="width: 25%;">
                <span class="info-label">Status & Rates</span>
                <div>Cost Multiplier: <strong>{{ $costMultiplier }}x</strong></div>
                @if($sellMultiplier > 0)
                    <div>Sell Multiplier: <strong>{{ $sellMultiplier }}x</strong></div>
                @endif
            </td>
            <td style="width: 25%;">
                <span class="info-label">Summary</span>
                <div class="info-value">{{ count($items) }} items / {{ $totalOrderQty }} ordered</div>
                @if($totalReceivedQty > 0)
                    <div style="color: #166534; font-weight: bold; font-size: 8.5px;">({{ $totalReceivedQty }} received)</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 24px;">#</th>
                <th style="width: 85px;">Code</th>
                <th>Product</th>
                <th class="text-center" style="width: 55px;">Status</th>
                <th class="text-center" style="width: 55px;">Order Qty</th>
                <th class="text-center" style="width: 55px;">Rec Qty</th>
                <th class="text-right" style="width: 70px;">Supplier Cost</th>
                <th class="text-right" style="width: 70px;">System Cost</th>
                <th class="text-right" style="width: 70px;">Selling Price</th>
                <th class="text-right" style="width: 75px;">Total Supplier</th>
                <th class="text-right" style="width: 75px;">Total System</th>
                <th class="text-right" style="width: 75px;">Total Selling</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td class="text-center text-muted">{{ $item['index'] }}</td>
                <td class="code-font">{{ $item['code'] ?: 'N/A' }}</td>
                <td>
                    <div class="font-bold">{{ $item['product'] }}</div>
                    @if(!empty($item['size']))
                        <div class="text-muted" style="font-size: 7.5px;">Size: {{ $item['size'] }}</div>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge {{ $item['status'] === 'Received' ? 'badge-success' : ($item['status'] === 'Pending' ? 'badge-warning' : 'badge-info') }}" style="font-size: 7.5px; padding: 1px 4px;">
                        {{ $item['status'] }}
                    </span>
                </td>
                <td class="text-center font-bold">{{ $item['order_qty'] }}</td>
                <td class="text-center font-bold" style="color: {{ $item['received_qty'] > 0 ? '#166534' : '#64748b' }};">
                    {{ $item['received_qty'] > 0 ? $item['received_qty'] : '-' }}
                </td>
                <td class="text-right text-info">Rs. {{ number_format($item['supplier_cost'], 2) }}</td>
                <td class="text-right font-bold">
                    Rs. {{ number_format($item['system_cost'], 2) }}
                    @if($item['cost_multiplier'] > 1)
                        <span class="text-muted" style="font-size: 7px;">({{ $item['cost_multiplier'] }}x)</span>
                    @endif
                </td>
                <td class="text-right text-success font-bold">Rs. {{ number_format($item['selling_price'], 2) }}</td>
                <td class="text-right text-info font-bold">Rs. {{ number_format($item['total_supplier'], 2) }}</td>
                <td class="text-right font-bold">Rs. {{ number_format($item['total_system'], 2) }}</td>
                <td class="text-right text-success font-bold">Rs. {{ number_format($item['total_selling'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="4" class="text-center">GRAND TOTALS</td>
                <td class="text-center">{{ number_format($totalOrderQty, 2) }}</td>
                <td class="text-center">{{ number_format($totalReceivedQty, 2) }}</td>
                <td colspan="3" class="text-center text-muted">-</td>
                <td class="text-right text-info">Rs. {{ number_format($totalSupplierSum, 2) }}</td>
                <td class="text-right font-bold">Rs. {{ number_format($totalSystemSum, 2) }}</td>
                <td class="text-right text-success">Rs. {{ number_format($totalSellingSum, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="summary-box">
        <tr>
            <td>
                <span class="sum-label">Total Supplier Cost</span>
                <span class="sum-val" style="color: #0284c7;">Rs. {{ number_format($totalSupplierSum, 2) }}</span>
            </td>
            <td>
                <span class="sum-label">Total System Cost</span>
                <span class="sum-val" style="color: #0f172a;">Rs. {{ number_format($totalSystemSum, 2) }}</span>
            </td>
            <td>
                <span class="sum-label">Expected Selling Value</span>
                <span class="sum-val" style="color: #16a34a;">Rs. {{ number_format($totalSellingSum, 2) }}</span>
            </td>
            <td>
                <span class="sum-label">Est. Profit</span>
                <span class="sum-val" style="color: #16a34a;">+ Rs. {{ number_format($estProfit, 2) }}</span>
            </td>
        </tr>
    </table>
</body>
</html>
