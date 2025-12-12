<p>Hello {{ $user->name }},</p>

<p>We noticed you left items in your cart. Here are the items:</p>

<ul>
@foreach($items as $item)
    <li>{{ $item->product->title }} — {{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</li>
@endforeach
</ul>

<p>Total: {{ number_format($totals['total'] ?? 0, 2) }}</p>

<p>Come back to complete your purchase.</p>
