<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; font-size: 14px; }
        .container { max-width: 800px; margin: auto; padding: 20px; }
        .header, .footer { text-align: center; margin-bottom: 20px; }
        .details, .items, .totals { margin-bottom: 30px; }
        .details table, .items table, .totals table { width: 100%; border-collapse: collapse; }
        .details th, .details td, .items th, .items td, .totals th, .totals td { padding: 8px; text-align: left; }
        .items th, .items td { border-bottom: 1px solid #ddd; }
        .items th { background-color: #f2f2f2; }
        .totals { float: right; width: 40%; }
        .totals td:first-child { font-weight: bold; }
        .text-right { text-align: right; }
        .company-details { margin-bottom: 20px; }
        .customer-details { margin-bottom: 20px; }
        h1, h2 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice</h1>
            <p>Betterbloq</p>
            <p>Your Company Address</p>
            <p>Your Company Contact</p>
        </div>

        <div class="details">
            <table>
                <tr>
                    <td>
                        <div class="customer-details">
                            <h2>Bill To:</h2>
                            {{-- Use user name for now until we add details to email --}}
                            <p>{{ $order->user->name }}</p>
                            @if($order->address)
                                <p>
                                    {{ $order->address->line1 ?? '' }}<br>
                                    {{ $order->address->line2 ?? '' }}<br>
                                    {{ $order->address->city ?? '' }}, {{ $order->address->state ?? '' }} {{ $order->address->postal_code ?? '' }}<br>
                                    {{ $order->address->country ?? '' }}
                                </p>
                            @endif
                            <p>Phone: {{ $order->phone ?? 'N/A' }}</p>
                        </div>
                    </td>
                    <td class="text-right">
                        <h2>Invoice #{{ $order->id }}</h2>
                        <p>Order Date: {{ $order->created_at->format('Y-m-d') }}</p>
                        <p>Status: {{ ucfirst($order->status) }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="items">
            <h2>Order Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- You'll need order items data. Assuming $order->orderItems relationship exists --}}
                    {{-- Or if it's just one product per order as implied by `data.product` --}}
                    @if($order->product)
                        <tr>
                            <td>{{ $order->product->name }}</td>
                            <td>{{ $order->quantity }}</td>
                            {{-- You NEED the price paid AT THE TIME OF ORDER. Store this on the order or order_item.
                                 Don't rely on the current product price. Let's assume 'price_per_unit' exists on the order --}}
                            <td>${{ number_format($order->price_per_unit ?? 0, 2) }}</td>
                            <td>${{ number_format(($order->price_per_unit ?? 0) * $order->quantity, 2) }}</td>
                        </tr>
                    @else
                        {{-- Loop through $order->orderItems if multiple items per order --}}
                        {{-- @foreach($order->orderItems as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->price_per_unit, 2) }}</td>
                            <td>${{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach --}}
                        <tr><td colspan="4">Item details not available.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="totals">
            <table>
                {{-- Assuming total_amount is stored on the order --}}
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">${{ number_format($order->total_amount ?? 0, 2) }}</td> {{-- Adjust based on how you calculate/store totals --}}
                </tr>
                <tr>
                    <td>Tax (if applicable):</td>
                    <td class="text-right">$0.00</td> {{-- Add tax logic if needed --}}
                </tr>
                <tr>
                    <td><strong>Grand Total:</strong></td>
                    <td class="text-right"><strong>${{ number_format($order->total_amount ?? 0, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>