@extends('layouts.admin')

@section('page_title', 'Order ' . $order->number)

@section('actions')
    <span class="text-sm text-gray-600">Payment: </span>
    <x-badge
        color="{{ $order->payment_status === 'paid' ? 'green' : ($order->payment_status === 'failed' ? 'red' : 'gray') }}">{{ $order->payment_status }}</x-badge>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-section title="Order info" class="lg:col-span-2">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Number:</span> <span class="font-medium">{{ $order->number }}</span></div>
                <div><span class="text-gray-500">Status:</span> <x-badge color="indigo">{{ $order->status }}</x-badge></div>
                <div><span class="text-gray-500">Payment method:</span> <span
                        class="font-medium">{{ strtoupper($order->payment_method) }}</span></div>
                <div><span class="text-gray-500">Transaction:</span> <span
                        class="font-medium">{{ $order->transaction_id ?? '—' }}</span></div>
            </div>

            <h3 class="mt-6 mb-2 font-medium">Items</h3>
            <x-table>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Product</th>
                        <th class="px-4 py-2 text-left">SKU</th>
                        <th class="px-4 py-2 text-right">Qty</th>
                        <th class="px-4 py-2 text-right">Unit</th>
                        <th class="px-4 py-2 text-right">Total</th>
                    </tr>
                </thead>
                @foreach ($order->items as $i)
                    <tr>
                        <td class="px-4 py-2">{{ $i->title }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $i->sku ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">{{ $i->quantity }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($i->unit_price, 2) }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($i->total, 2) }}</td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                <div class="text-right text-gray-600">Subtotal</div>
                <div class="text-right">${{ number_format($order->subtotal, 2) }}</div>
                <div class="text-right text-gray-600">Discount</div>
                <div class="text-right">- ${{ number_format($order->discount, 2) }}</div>
                <div class="text-right text-gray-600">Tax</div>
                <div class="text-right">${{ number_format($order->tax, 2) }}</div>
                <div class="text-right text-gray-600">Shipping</div>
                <div class="text-right">${{ number_format($order->shipping, 2) }}</div>
                <div class="col-span-2 border-t pt-2"></div>
                <div class="text-right font-semibold">Total</div>
                <div class="text-right font-semibold">${{ number_format($order->total, 2) }}</div>
            </div>
        </x-section>

        <div class="space-y-6">
            <x-section title="Addresses">
                <div class="text-sm">
                    <div class="font-medium mb-1">Shipping</div>
                    <div class="text-gray-700">
                        @php $sa = $order->shipping_address ?? []; @endphp
                        {{ $sa['name'] ?? '' }}<br>
                        {{ $sa['line1'] ?? '' }}<br>
                        {{ $sa['city'] ?? '' }}, {{ $sa['zip'] ?? '' }} {{ $sa['country'] ?? '' }}
                    </div>
                    <div class="font-medium mt-3 mb-1">Billing</div>
                    <div class="text-gray-700">
                        @php $ba = $order->billing_address ?? []; @endphp
                        {{ $ba['name'] ?? '' }}<br>
                        {{ $ba['line1'] ?? '' }}<br>
                        {{ $ba['city'] ?? '' }}, {{ $ba['zip'] ?? '' }} {{ $ba['country'] ?? '' }}
                    </div>
                </div>
            </x-section>

            @include('admin.orders._status-form', ['order' => $order])
        </div>
    </div>
@endsection
