@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
    <div class="max-w-5xl mx-auto py-10 space-y-8">

        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-semibold">Order #{{ $order->id }}</h2>
                <p class="text-gray-500 text-sm">{{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div>
                @if ($order->status === 'pending')
                    <span class="px-2 py-1 text-sm rounded bg-yellow-100 text-yellow-800">Pending</span>
                @elseif($order->status === 'completed')
                    <span class="px-2 py-1 text-sm rounded bg-green-100 text-green-800">Completed</span>
                @elseif($order->status === 'paid')
                    <span class="px-2 py-1 text-sm rounded bg-blue-100 text-blue-800">Paid</span>
                @elseif($order->status === 'canceled')
                    <span class="px-2 py-1 text-sm rounded bg-red-100 text-red-800">Canceled</span>
                @else
                    <span class="px-2 py-1 text-sm rounded bg-gray-100 text-gray-800">{{ ucfirst($order->status) }}</span>
                @endif
            </div>
        </div>

        {{-- Shipping & Billing --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border rounded-lg shadow-sm p-5 bg-white">
                <h3 class="font-semibold text-gray-800 mb-2">Shipping Address</h3>
                <hr class="mb-3">
                @if ($order->shipping_name || $order->shipping_line1)
                    <p class="text-gray-700">{{ $order->shipping_name }}</p>
                    <p class="text-gray-700">
                        {{ $order->shipping_line1 }}
                        @if ($order->shipping_line2)
                            , {{ $order->shipping_line2 }}
                        @endif
                    </p>
                    <p class="text-gray-500">
                        {{ $order->shipping_city ? $order->shipping_city . ',' : '' }}
                        {{ $order->shipping_zip }} {{ $order->shipping_country }}
                    </p>
                @else
                    <p class="italic text-gray-500">No shipping address provided.</p>
                @endif
            </div>

            <div class="border rounded-lg shadow-sm p-5 bg-white">
                <h3 class="font-semibold text-gray-800 mb-2">Billing Address</h3>
                <hr class="mb-3">
                @if ($order->billing_name || $order->billing_line1)
                    <p class="text-gray-700">{{ $order->billing_name }}</p>
                    <p class="text-gray-700">
                        {{ $order->billing_line1 }}
                        @if ($order->billing_line2)
                            , {{ $order->billing_line2 }}
                        @endif
                    </p>
                    <p class="text-gray-500">
                        {{ $order->billing_city ? $order->billing_city . ',' : '' }}
                        {{ $order->billing_zip }} {{ $order->billing_country }}
                    </p>
                @else
                    <p class="italic text-gray-500">No billing address provided.</p>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div>
            <h3 class="font-semibold text-gray-800 mb-3">Items</h3>
            <div class="space-y-3">
                @forelse ($order->items as $item)
                    <div class="flex items-center bg-white border rounded-lg shadow-sm p-4">
                        {{-- Image --}}
                        <img src="{{ $item->product->image_path
                            ? asset('storage/' . $item->product->image_path)
                            : asset('images/placeholder.png') }}"
                            alt="{{ $item->product->name }}" class="w-20 h-20 object-cover rounded-md">

                        {{-- Info --}}
                        <div class="ml-4 flex-1">
                            <h4 class="font-semibold text-gray-900">{{ $item->product->name }}</h4>
                            <p class="text-sm text-gray-500">Quantity: {{ $item->quantity }}</p>
                        </div>

                        {{-- Price --}}
                        <div class="text-right font-semibold text-gray-800">
                            ${{ number_format(($item->price ?? ($item->product->price ?? 0)) * $item->quantity, 2) }}
                        </div>
                    </div>
                @empty
                    <p class="italic text-gray-500 text-sm">No items found for this order.</p>
                @endforelse
            </div>
        </div>

        {{-- Summary --}}
        <div class="border rounded-lg shadow-sm bg-white p-4 flex justify-between items-center">
            <span class="font-semibold text-gray-800 text-lg">Total:</span>
            <span class="font-bold text-gray-900 text-xl">${{ number_format($order->total, 2) }}</span>
        </div>

        {{-- Back --}}
        <div class="pt-4">
            <a href="{{ route('user.orders.index') }}"
                class="inline-block px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                ← Back to Orders
            </a>
        </div>

    </div>
@endsection
