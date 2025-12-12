@extends('layouts.app')

@section('title', 'Your Addresses')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Manage Your Addresses</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <x-section title="Your Saved Addresses" class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                    @if ($addresses->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($addresses as $address)
                                <div
                                    class="relative bg-gray-50 border border-gray-200 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow duration-200 ease-in-out">
                                    <div class="flex items-start justify-between mb-3">
                                        <h3 class="text-xl font-semibold text-gray-900">
                                            {{ $address->label ?? 'Address #' . $address->id }}
                                        </h3>
                                        <div class="flex flex-wrap gap-2 mt-1 sm:mt-0">
                                            @if ($address->is_default_shipping)
                                                <x-badge color="blue">Default Shipping</x-badge>
                                            @endif
                                            @if ($address->is_default_billing)
                                                <x-badge color="green">Default Billing</x-badge>
                                            @endif
                                        </div>
                                    </div>
                                    <address class="mt-2 text-base text-gray-800 not-italic space-y-1">
                                        <p class="font-medium">{{ $address->name }}</p>
                                        <p>{{ $address->line1 }} @if ($address->line2)
                                            , {{ $address->line2 }}
                                        @endif
                                        </p>
                                        <p>{{ $address->city }}, {{ $address->zip }}</p>
                                        <p>{{ $address->country }}</p>
                                    </address>
                                    <div
                                        class="mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-gray-200 pt-4">
                                        {{-- Make default buttons --}}
                                        <div class="flex flex-wrap gap-3">
                                            @unless ($address->is_default_shipping)
                                                <form action="{{ route('addresses.set-default', $address) }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="type" value="shipping">
                                                    <x-button type="submit" size="sm" variant="secondary">Set as Default
                                                        Shipping</x-button>
                                                </form>
                                            @endunless

                                            @unless ($address->is_default_billing)
                                                <form action="{{ route('addresses.set-default', $address) }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="type" value="billing">
                                                    <x-button type="submit" size="sm" variant="secondary">Set as Default
                                                        Billing</x-button>
                                                </form>
                                            @endunless
                                        </div>

                                        {{-- Delete button --}}
                                        <x-delete-with-confirm :action="route('addresses.destroy', $address)"
                                            message="Are you sure you want to delete this address?" confirm-text="Delete"
                                            class="ml-0 sm:ml-auto inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Delete
                                        </x-delete-with-confirm>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-alert type="info" class="text-center py-6">
                            <p class="text-lg">You haven't added any addresses yet.</p>
                            <p class="mt-2 text-sm">Use the form on the right to add your first address!</p>
                        </x-alert>
                    @endif
                </x-section>
            </div>

            <div>
                <x-section title="Add New Address" class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                    @include('addresses._form', ['address' => null])
                </x-section>
            </div>
        </div>
    </div>
@endsection