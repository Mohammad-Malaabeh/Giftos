<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Order Placed</title>
</head>

<body>
    <p>Hello {{ $user?->name ?? 'Customer' }},</p>
    <p>Thank you for your order {{ $order->number }}.</p>

    <p><strong>Total:</strong> {{ number_format((float) $order->total, 2) }}</p>
    <p><strong>Status:</strong> {{ $order->status }} | <strong>Payment:</strong> {{ $order->payment_status }}</p>

    <h3>Items</h3>
    <ul>
        @foreach ($order->items as $item)
            <li>
                {{ $item->title }} (x{{ $item->quantity }}) - {{ number_format((float) $item->unit_price, 2) }}
            </li>
        @endforeach
    </ul>

    <p>We’ll keep you updated on the progress of your order.</p>
</body>

</html>
