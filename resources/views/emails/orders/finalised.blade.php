<x-mail::message>
# Your Order #{{ $order->id }} is Finalized

Hi {{ $user->name }},

Your purchase pool order has been finalized.

@foreach($products as $line)
- {{ $line->product?->name ?? $line->description }} × {{ $line->quantity }}: ${{ number_format($line->final_line_price ?? $line->total_price, 2) }}
@endforeach

- **Initial payment:** ${{ number_format($initialAmount, 2) }}
- **Discount refunded:** ${{ number_format($discountApplied, 2) }}
- **Final amount:** ${{ number_format($finalAmount, 2) }}

Any discount refund has been issued to your original payment method.

<x-mail::button :url="route('orders.show', $order)">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
