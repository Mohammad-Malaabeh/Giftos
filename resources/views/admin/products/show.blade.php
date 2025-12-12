@extends('layouts.admin')

@section('page_title', 'Product details')

@section('content')
    <x-section>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                @if ($product->image_path)
                    <img class="rounded-lg border" src="{{ asset('storage/' . $product->image_path) }}" alt="">
                @endif
                @if (is_array($product->gallery) && count($product->gallery))
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach ($product->gallery as $g)
                            <img class="rounded border" src="{{ asset('storage/' . $g) }}" alt="">
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-500">Title:</span> <span class="font-medium">{{ $product->title }}</span></div>
                <div><span class="text-gray-500">SKU:</span> <span class="font-medium">{{ $product->sku ?? '—' }}</span>
                </div>
                <div><span class="text-gray-500">Category:</span> <span
                        class="font-medium">{{ $product->category?->name ?? '—' }}</span></div>
                <div><span class="text-gray-500">Price:</span> <span
                        class="font-medium">${{ number_format($product->price, 2) }}</span></div>
                @if ($product->sale_price)
                    <div><span class="text-gray-500">Sale:</span> <span
                            class="font-medium">${{ number_format($product->sale_price, 2) }}</span></div>
                @endif
                <div><span class="text-gray-500">Stock:</span> <span class="font-medium">{{ $product->stock }}</span></div>
                <div><span class="text-gray-500">Status:</span>
                    @if ($product->status)
                    <x-badge color="green">Active</x-badge>@else<x-badge color="gray">Inactive</x-badge>
                    @endif
                </div>
            </div>
        </div>
    </x-section>
@endsection
