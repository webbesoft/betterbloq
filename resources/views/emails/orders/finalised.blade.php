<x-mail::message>
# Your Order #{{ $order->id }} is Finalized!

Hi {{ $user->name }},

Great news! The purchase pool for **{{ $product->name }}** has been finalized.

Your order details:
- **Initial Amount:** ${{ number_format($initialAmount, 2) }}
@if($appliedTier)
- **Discount Tier Applied:** {{ $appliedTier->name }} ({{ $appliedTier->discount_percentage }}%)
- **Discount Value:** ${{ number_format($discountApplied, 2) }}
@endif
- **Final Amount Charged:** ${{ number_format($finalAmount, 2) }}
- **Quantity:** {{ $order->quantity }}
- **Status:** {{ ucfirst($order->status) }}

@if($order->status === 'completed')
Your payment has been successfully processed for the final amount.
@elseif($order->status === 'capture_failed')
There was an issue processing the final payment for your order. Please contact support.
@endif

You can view your order details here:
<x-mail::button :url="route('orders.show', $order)">
View Order
</x-mail::button>

Thanks for participating,
<br>
{{ config('app.name') }}
</x-mail::message>