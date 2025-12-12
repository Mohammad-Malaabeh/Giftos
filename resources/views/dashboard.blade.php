@extends('layouts.app')

@section('title','Dashboard')

@section('content')
    <x-section title="Welcome back">
        <p class="text-gray-700">Hello, {{ auth()->user()->name }}.</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('user.orders.index', absolute: false) }}" class="hidden"></a>
            <a href="{{ route('products.index') }}"><x-button>Browse products</x-button></a>
            <a href="{{ route('cart.index') }}"><x-button variant="secondary">View cart</x-button></a>
        </div>
    </x-section>
@endsection