@props(['product', 'wishlistItem' => null])

<div class="group bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow transition relative">
    {{-- Wishlist Remove Button (if in wishlist context) --}}
    @if($wishlistItem)
        <div class="absolute top-2 right-2 z-10">
            <x-delete-with-confirm :action="route('wishlist.remove', $product)"
                message="Remove {{ $product->title }} from your wishlist?" confirm-text="Remove"
                class="bg-white/90 backdrop-blur-sm p-2 rounded-full border border-gray-200 hover:bg-red-50 hover:border-red-300 transition">
                <svg class="w-5 h-5 text-gray-600 hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </x-delete-with-confirm>
        </div>
    @endif

    {{-- Product Link --}}
    <a href="{{ route('product.show', $product->slug) }}" class="block">
        <div class="aspect-square bg-gray-50 overflow-hidden">
            @if ($product->image_path)
                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->title }}"
                    class="h-full w-full object-cover group-hover:scale-105 transition">
            @else
                <div class="h-full w-full flex items-center justify-center text-gray-400">No image</div>
            @endif
        </div>
        <div class="p-4">
            <h3 class="text-sm font-medium text-gray-900 line-clamp-2">{{ $product->title }}</h3>
            <div class="mt-2 flex items-center justify-between">
                <div class="flex items-baseline gap-2">
                    @if ($product->sale_price)
                        <div class="text-indigo-600 font-semibold">${{ number_format($product->sale_price, 2) }}</div>
                        <div class="text-xs line-through text-gray-500">${{ number_format($product->price, 2) }}</div>
                    @else
                        <div class="text-gray-900 font-semibold">${{ number_format($product->price, 2) }}</div>
                    @endif
                </div>
                @if ($product->reviews_count ?? false)
                    <div class="text-xs text-gray-500">{{ $product->reviews_count }} reviews</div>
                @endif
            </div>
        </div>
    </a>
</div>