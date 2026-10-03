{{-- components/receipt.blade.php --}}
<div class="receipt-container text-center">
    <h3 style="margin: 0; font-weight: bold; text-transform: uppercase;">{{ config('shop.name', 'JAFFNA GOLD') }}</h3>
    <div style="font-size: 13px; font-weight: bold; color: #b45309; text-transform: uppercase;">{{ config('shop.branch', 'Vavuniya Branch') }}</div>
    <div style="font-size: 11px; color: #555;">{{ config('shop.address', '2nd Cross Street, Vavuniya') }}</div>
    <div style="font-size: 11px; color: #555;">Tel: {{ config('shop.phone', '0761919650') }} | Hotline: {{ config('shop.hotline', '021 222 85 89') }}</div>
    <hr style="margin: 8px 0;">
    <div style="text-align: left;">
        <h4>Invoice #{{ $receipt->invoice_number }}</h4>
        <p><strong>Branch:</strong> {{ config('shop.branch', 'Vavuniya Branch') }}</p>
        <p><strong>Date:</strong> {{ $receipt->created_at->format('d-m-Y H:i') }}</p>
        <p><strong>Customer:</strong> {{ $receipt->customer->name ?? 'Walk-in Customer' }}</p>
    </div>
    <hr>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₹{{ number_format($item->unit_price,2) }}</td>
                <td>
                    @if($item->discount_per_unit > 0)
                        - ₹{{ number_format($item->discount_per_unit,2) }}
                    @else
                        - ₹0.00
                    @endif
                </td>
                <td>₹{{ number_format(($item->unit_price - $item->discount_per_unit) * $item->quantity,2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <p><strong>Subtotal:</strong> ₹{{ number_format($receipt->subtotal,2) }}</p>
    <p><strong>Discount:</strong> ₹{{ number_format($receipt->discount_amount,2) }}</p>
    <p><strong>Grand Total:</strong> ₹{{ number_format($receipt->total_amount,2) }}</p>
    <p><strong>Payment Status:</strong> {{ ucfirst($receipt->payment_status) }}</p>
</div>