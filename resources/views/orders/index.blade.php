@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
    <div class="max-w-6xl mx-auto py-10">
        <h2 class="text-2xl font-semibold mb-6">Your Orders</h2>

        @if ($orders->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($orders as $order)
                    <div class="border rounded-lg shadow-sm bg-white p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-900">Order #{{ $order->id }}</span>
                                <span class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>

                            <div class="space-y-1 text-sm text-gray-700">
                                <div>
                                    Status:
                                    @if ($order->status === 'pending')
                                        <span class="text-yellow-700 font-semibold">Pending</span>
                                    @elseif($order->status === 'completed')
                                        <span class="text-green-700 font-semibold">Completed</span>
                                    @elseif($order->status === 'paid')
                                        <span class="text-blue-700 font-semibold">Paid</span>
                                    @elseif($order->status === 'canceled')
                                        <span class="text-red-700 font-semibold">Canceled</span>
                                    @else
                                        <span class="text-gray-600 font-semibold">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </div>
                                <div>Total: ${{ number_format($order->total, 2) }}</div>
                                <div>Items: {{ $order->items_count ?? $order->items->count() }}</div>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('user.orders.show', $order) }}"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @else
            <div class="p-6 text-center text-gray-500 border rounded-lg bg-gray-50">
                You have no orders yet.
            </div>
        @endif
    </div>
@endsection
