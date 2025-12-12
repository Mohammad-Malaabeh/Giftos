@extends('layouts.admin')

@section('page_title', 'Coupons')

@section('actions')
    <a href="{{ route('admin.coupons.create') }}">
        <x-button size="sm">New coupon</x-button>
    </a>
@endsection

@section('content')
    <x-section>
        {{-- Filter --}}
        <form method="get" class="flex gap-2 mb-4">
            <x-input name="q" label="Search code" value="{{ $q }}" />
            <x-button type="submit">Search</x-button>
        </form>

        {{-- Table --}}
        <x-table>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Code</th>
                    <th class="px-4 py-2 text-left">Type</th>
                    <th class="px-4 py-2 text-right">Value</th>
                    <th class="px-4 py-2 text-right">Used / Limit</th>
                    <th class="px-4 py-2 text-left">Active</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            @foreach ($coupons as $c)
                <tr>
                    <td class="px-4 py-2 font-medium">{{ $c->code }}</td>
                    <td class="px-4 py-2">{{ ucfirst($c->type) }}</td>
                    <td class="px-4 py-2 text-right">
                        @if ($c->type === 'percent')
                            {{ rtrim(rtrim(number_format((float) $c->value, 2, '.', ''), '0'), '.') }}%
                        @else
                            ${{ number_format((float) $c->value, 2) }}
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right">
                        {{ (int) $c->used_count }}{{ $c->usage_limit ? ' / ' . (int) $c->usage_limit : '' }}
                    </td>
                    <td class="px-4 py-2">
                        @if ($c->active)
                            <x-badge color="green">Active</x-badge>
                        @else
                            <x-badge color="gray">Inactive</x-badge>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right space-x-3">
                        <a href="{{ route('admin.coupons.edit', $c) }}" class="text-sm text-indigo-600">Edit</a>

                        <x-delete-with-confirm :action="route('admin.coupons.destroy', $c)"
                            message="Delete coupon {{ $c->code }}? This moves it to trash." confirm-text="Delete"
                            class="text-sm text-rose-600 hover:underline">
                            Delete
                        </x-delete-with-confirm>
                    </td>
                </tr>
            @endforeach
        </x-table>



        <x-pagination :paginator="$coupons" />
    </x-section>
    <x-section title="Trash" class="mt-6">
        <a href="{{ route('admin.trash.coupons') }}" class="text-sm text-indigo-600 hover:underline">
            View deleted coupons
        </a>
    </x-section>
@endsection