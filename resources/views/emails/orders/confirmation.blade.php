<x-mail::message>
    # Order Confirmed - #{{ $order->id }}

    Hi {{ $userName }},

    Thanks for your order! Here's a summary:

{{--    <x-mail::table>--}}
        | Item        | Quantity | Price     |
        | :---------- | :------- | :-------- |
            | {{ $order->product->name }} | ${{ number_format($order->product->price, 2) }} |
        -------------------------------------
        | **Total** |          | **${{ number_format($order->total_amount, 2) }}** |
{{--    </x-mail::table>--}}

    You can view your order details here:

    <x-mail::button :url="$orderUrl">
        View Order
    </x-mail::button>

    We'll notify you again when your order ships (if applicable).

    Thanks,
    {{ config('app.name') }}
</x-mail::message>
