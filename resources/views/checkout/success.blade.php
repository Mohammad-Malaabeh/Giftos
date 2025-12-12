@extends('layouts.app')

@section('title','Order placed')

@section('content')
    <div class="max-w-lg mx-auto">
        <x-section title="Thank you!">
            <p class="text-gray-700">Your order <span class="font-medium">{{ $order->number }}</span> has been placed.</p>
            <p class="mt-2 text-gray-600 text-sm">Status: <x-badge color="indigo">{{ $order->status }}</x-badge></p>
            <a href="{{ route('products.index') }}" class="mt-4 inline-flex">
                <x-button>Continue shopping</x-button>
            </a>
        </x-section>
    </div>
@endsection