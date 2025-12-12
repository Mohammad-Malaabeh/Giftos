@extends('layouts.admin')

@section('page_title', 'Search')

@section('content')
    <x-section title="Search">
        <form method="get" class="flex gap-2 mb-4">
            <input type="text" name="q" value="{{ $q }}" placeholder="Order number, SKU, email, category..."
                class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <x-button type="submit">Search</x-button>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Products</h3>
                <x-table>
                    @forelse($products as $p)
                        <tr>
                            <td class="px-4 py-2">{{ $p->title }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $p->sku }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-3 text-gray-500">No results</td>
                        </tr>
                    @endforelse
                </x-table>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Categories</h3>
                <x-table>
                    @forelse($categories as $c)
                        <tr>
                            <td class="px-4 py-2">{{ $c->name }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $c->slug }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-3 text-gray-500">No results</td>
                        </tr>
                    @endforelse
                </x-table>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Orders</h3>
                <x-table>
                    @forelse($orders as $o)
                        <tr>
                            <td class="px-4 py-2">{{ $o->number }}</td>
                            <td class="px-4 py-2 text-right">${{ number_format($o->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-3 text-gray-500">No results</td>
                        </tr>
                    @endforelse
                </x-table>
            </div>
        </div>
    </x-section>
@endsection
