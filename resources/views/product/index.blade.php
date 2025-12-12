@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <x-section class="mb-6" :title="'Browse products'">
        @include('product._filters', [
            'q' => $q ?? '',
            'categoryId' => $categoryId ?? null,
            'categories' => $categories ?? collect(),
        ])
    </x-section>

    @if ($products->count())
        @include('partials.product-grid', ['products' => $products])
        <x-pagination :paginator="$products" />
    @else
        <x-alert type="info">No products found.</x-alert>
    @endif
@endsection
