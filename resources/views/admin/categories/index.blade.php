@extends('layouts.admin')

@section('page_title', 'Categories')

@section('actions')
    <a href="{{ route('admin.categories.create') }}">
        <x-button size="sm">New category</x-button>
    </a>
@endsection

@section('content')
    <x-section>
        {{-- Filters --}}
        <form method="get" class="flex flex-wrap items-end gap-3 mb-4">
            <x-input name="q" label="Search" value="{{ $q }}" />
            <x-select name="status" label="Status">
                <option value="">All</option>
                <option value="1" @selected((string) $status === '1')>Active</option>
                <option value="0" @selected((string) $status === '0')>Inactive</option>
            </x-select>
            <x-button type="submit">Filter</x-button>
        </form>

        {{-- Bulk actions (activate/deactivate) - Form Declaration --}}
        <form action="{{ route('admin.categories.bulk') }}" method="post" id="bulk-actions-form" class="hidden">
            @csrf
            <input type="hidden" name="action" id="bulk_action">
        </form>

        {{-- Bulk Action Buttons --}}
        <div class="flex items-center gap-2 mb-2">
            <x-button type="button" variant="secondary" form="bulk-actions-form"
                onclick="document.getElementById('bulk_action').value='activate'; document.getElementById('bulk-actions-form').submit();">
                Activate
            </x-button>
            <x-button type="button" variant="secondary" form="bulk-actions-form"
                onclick="document.getElementById('bulk_action').value='deactivate'; document.getElementById('bulk-actions-form').submit();">
                Deactivate
            </x-button>
        </div>

        <x-table>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2">
                        <input type="checkbox" form="bulk-actions-form"
                            onclick="document.querySelectorAll('.rowcheck').forEach(cb=>cb.checked=this.checked)">
                    </th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Slug</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-right">Products</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>

            @foreach ($categories as $c)
                <tr>
                    <td class="px-4 py-2">
                        <input class="rowcheck" type="checkbox" name="ids[]" value="{{ $c->id }}" form="bulk-actions-form">
                    </td>
                    <td class="px-4 py-2 font-medium">{{ $c->name }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $c->slug }}</td>
                    <td class="px-4 py-2">
                        @if ($c->status)
                            <x-badge color="green">Active</x-badge>
                        @else
                            <x-badge color="gray">Inactive</x-badge>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right">{{ $c->products_count }}</td>
                    <td class="px-4 py-2 text-right space-x-3">
                        <a href="{{ route('admin.categories.edit', $c) }}" class="text-sm text-indigo-600">Edit</a>

                        <x-delete-with-confirm :action="route('admin.categories.destroy', $c)"
                            message="Delete {{ $c->name }}? This moves it to trash." confirm-text="Delete"
                            class="text-sm text-rose-600 hover:underline">
                            Delete
                        </x-delete-with-confirm>
                    </td>
                </tr>
            @endforeach
        </x-table>



        <x-pagination :paginator="$categories" />
    </x-section>

    <x-section title="Trash" class="mt-6">
        <a href="{{ route('admin.trash.categories') }}" class="text-sm text-indigo-600 hover:underline">
            View deleted categories
        </a>
    </x-section>
@endsection