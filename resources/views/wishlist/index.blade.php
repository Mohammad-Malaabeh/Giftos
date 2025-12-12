@extends('layouts.app')

@section('title', 'Wishlist')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Your wishlist</h1>
    @if ($items->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($items as $i)
                @if ($i->product)
                    @include('partials.product-card', ['product' => $i->product, 'wishlistItem' => $i])
                @endif
            @endforeach
        </div>
    @else
        <x-alert type="info">Your wishlist is empty.</x-alert>
    @endif
@endsection