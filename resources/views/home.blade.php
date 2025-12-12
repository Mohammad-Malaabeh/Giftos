@extends('layouts.app')

@section('title', 'Home')

@section('content')
    @if (isset($categories) && $categories->count())
        <x-section title="Categories" class="mb-8">
            <div class="flex flex-wrap gap-2">
                @foreach ($categories as $c)
                    <a href="{{ route('products.index', ['category_id' => $c->id]) }}"
                        class="px-3 py-1.5 rounded-full bg-gray-100 hover:bg-gray-200 text-sm">
                        {{ $c->name }} @if ($c->products_count)
                            <span class="text-gray-500">({{ $c->products_count }})</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </x-section>
    @endif

    @if (isset($featured) && $featured->count())
        <x-section title="Featured" class="mb-8">
            @include('partials.product-grid', ['products' => $featured])
        </x-section>
    @endif

    @if (isset($newArrivals) && $newArrivals->count())
        <x-section title="New arrivals">
            @include('partials.product-grid', ['products' => $newArrivals])
        </x-section>
    @endif

@endsection
