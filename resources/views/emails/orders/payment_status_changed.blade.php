<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Payment Status Changed</title>
</head>

<body>
    <p>Hello {{ $user?->name ?? 'Customer' }},</p>
    <p>The payment status for your order {{ $order->number }} has changed.</p>

    <p><strong>Old status:</strong> {{ $oldStatus }}</p>
    <p><strong>New status:</strong> {{ $newStatus }}</p>

    @if ($newStatus === 'paid')
        <p>Your payment was received successfully. Thank you!</p>
    @elseif($newStatus === 'refunded')
        <p>Your payment has been refunded.</p>
    @elseif($newStatus === 'failed')
        <p>Your payment attempt failed. You can try again from your account or contact support.</p>
    @endif

    <h3>Order Summary</h3>
    <ul>
        @foreach ($order->items as $item)
            <li>{{ $item->title }} (x{{ $item->quantity }}) - {{ number_format((float) $item->unit_price, 2) }}</li>
        @endforeach
    </ul>

    <p><strong>Total:</strong> {{ number_format((float) $order->total, 2) }}</p>
</body>

</html>
