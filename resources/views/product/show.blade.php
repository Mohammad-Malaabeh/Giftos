@extends('layouts.app')

@section('title', $product->title)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="aspect-square bg-gray-50">
                @if ($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->title }}"
                        class="h-full w-full object-cover">
                @endif
            </div>
            @if (is_array($product->gallery) && count($product->gallery))
                <div class="p-4 grid grid-cols-4 gap-2">
                    @foreach ($product->gallery as $g)
                        <img src="{{ asset('storage/' . $g) }}" class="h-20 w-full object-cover rounded-md border" alt="">
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $product->title }}</h1>
            <div class="mt-2 flex items-center gap-3">
                @include('partials.rating-stars', ['value' => $product->avg_rating ?? 0, 'size' => 'md'])
                <div class="text-sm text-gray-500">({{ $product->reviews_count ?? 0 }} reviews)</div>
            </div>

            <div class="mt-4 flex items-baseline gap-3">
                @if ($product->sale_price)
                    <div class="text-2xl text-indigo-600 font-semibold">${{ number_format($product->sale_price, 2) }}</div>
                    <div class="text-sm line-through text-gray-500">${{ number_format($product->price, 2) }}</div>
                @else
                    <div class="text-2xl text-gray-900 font-semibold">${{ number_format($product->price, 2) }}</div>
                @endif
            </div>

            @if ($product->hasVariants())
                @include('product._variant-picker', ['product' => $product])
            @endif

            <form action="{{ route('cart.add') }}" method="post" class="mt-6 space-y-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                @if ($product->hasVariants())
                    <input type="hidden" name="variant_id" id="variant_id">
                @endif

                <x-input label="Quantity" name="quantity" type="number" min="1" value="1" class="w-32" />
                <div class="flex items-center gap-2">
                    <x-button type="submit">Add to cart</x-button>
                    @auth
                        <x-button variant="secondary" type="submit" form="wishlist-form">Add to wishlist</x-button>
                    @endauth
                </div>
            </form>

            @auth
                <form id="wishlist-form" action="{{ route('wishlist.add', $product) }}" method="post">
                    @csrf
                    {{-- no content needed; the button above will submit this form --}}
                </form>
            @endauth

            @if ($product->description)
                <div class="mt-8 prose prose-sm max-w-none">
                    {!! nl2br(e($product->description)) !!}
                </div>
            @endif

            {{-- Reviews Link --}}
            <div class="mt-6">
                <a href="#reviews-section"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    View Reviews ({{ $product->reviews_count ?? 0 }})
                </a>
            </div>
        </div>
    </div>

    @include('product._also-bought', ['items' => $alsoBought ?? collect()])
    @include('product._related', ['items' => $related ?? collect()])

    {{-- Reviews Section --}}
    <div id="reviews-section" class="mt-12 scroll-mt-8">
        @auth
            @include('reviews._form', ['product' => $product])
        @endauth

        @include('reviews._list', ['product' => $product])
    </div>
@endsection