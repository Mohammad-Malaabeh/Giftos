@extends('layouts.app')

@section('title', 'Your cart')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Shopping cart</h1>

    @if ($items->count())
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                @foreach ($items as $item)
                    <div class="bg-white border border-gray-200 rounded-xl p-4 flex gap-4">
                        <div class="h-20 w-20 bg-gray-100 rounded-md overflow-hidden">
                            @if ($item->product?->image_path)
                                <img src="{{ asset('storage/' . $item->product->image_path) }}" class="h-full w-full object-cover"
                                    alt="">
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $item->product?->title ?? 'Product deleted' }}
                                    </div>
                                    @if ($item->variant)
                                        <div class="text-xs text-gray-500">
                                            Variant: {{ $item->variant->sku }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-700">${{ number_format($item->unit_price, 2) }}</div>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <form action="{{ route('cart.update', $item) }}" method="post" class="flex items-center gap-2">
                                    @method('PATCH') @csrf
                                    <input type="number" name="quantity" min="1" value="{{ $item->quantity }}"
                                        class="w-24 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <x-button size="sm" type="submit">Update</x-button>
                                </form>

                                <x-delete-with-confirm :action="route('cart.remove', $item)"
                                    message="Remove this item from your cart?" confirm-text="Remove"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Remove
                                </x-delete-with-confirm>
                            </div>
                        </div>
                    </div>
                @endforeach
                <x-delete-with-confirm :action="route('cart.clear')" message="Remove all items from your cart?"
                    confirm-text="Clear Cart"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Clear cart
                </x-delete-with-confirm>
            </div>

            <div class="space-y-4">
                <x-section title="Coupon">
                    <form action="{{ route('cart.coupon') }}" method="post" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" value="{{ session('coupon') }}"
                            class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="Enter coupon">
                        <x-button type="submit">Apply</x-button>
                    </form>
                    @if (session('coupon'))
                        <div class="mt-2">
                            <x-delete-with-confirm :action="route('cart.coupon.remove')" method="POST"
                                message="Remove the applied coupon?" confirm-text="Remove Coupon"
                                class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                Remove coupon
                            </x-delete-with-confirm>
                        </div>
                    @endif
                </x-section>


                <x-section title="Estimate shipping">
                    <form action="{{ route('cart.estimate') }}" method="post" class="flex gap-2">
                        @csrf
                        <input type="text" name="country" maxlength="2" value="{{ session('checkout_country') }}"
                            class="w-28 uppercase rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="US">
                        <x-button type="submit">Update</x-button>
                    </form>
                </x-section>

                <x-section title="Summary">
                    <dl class="text-sm space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Subtotal</dt>
                            <dd>${{ number_format($totals['subtotal'], 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Discount</dt>
                            <dd>- ${{ number_format($totals['discount'], 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Tax</dt>
                            <dd>${{ number_format($totals['tax'], 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Shipping</dt>
                            <dd>${{ number_format($totals['shipping'], 2) }}</dd>
                        </div>
                        <div class="border-t pt-2 flex justify-between font-semibold text-gray-900">
                            <dt>Total</dt>
                            <dd>${{ number_format($totals['total'], 2) }}</dd>
                        </div>
                    </dl>

                    @auth
                        <a href="{{ route('checkout.create') }}" class="mt-4 inline-flex w-full justify-center">
                            <x-button class="w-full">Proceed to checkout</x-button>
                        </a>
                    @else
                        <div class="mt-4 text-sm text-gray-600">Please <a class="text-indigo-600 underline"
                                href="{{ route('login') }}">login</a> to checkout.</div>
                    @endauth
                </x-section>
            </div>
        </div>
    @else
        <x-alert type="info">Your cart is empty.</x-alert>
    @endif
@endsection