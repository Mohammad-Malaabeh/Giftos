@extends('layouts.admin')

@section('page_title', 'Products')

@section('actions')
    <a href="{{ route('admin.products.create') }}">
        <x-button size="sm">New product</x-button>
    </a>
@endsection

@section('content')
    <x-section>
        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
            <x-input name="q" label="Search" value="{{ $q }}" class="md:col-span-2" />
            <x-select name="status" label="Status">
                <option value="">All</option>
                <option value="1" @selected($status === '1')>Active</option>
                <option value="0" @selected($status === '0')>Inactive</option>
            </x-select>
            <x-select name="category_id" label="Category">
                <option value="">All</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected((int) $categoryId === (int) $c->id)>{{ $c->name }}</option>
                @endforeach
            </x-select>
            <label class="text-sm text-gray-700 flex items-end gap-2">
                <input type="checkbox" name="low_stock" value="1" @checked(request('low_stock')) class="text-indigo-600">
                Low stock
            </label>
            <div class="md:col-span-5">
                <x-button type="submit">Filter</x-button>
            </div>
        </form>

        <div class="flex items-center gap-4 mb-4">
            {{-- Export Products Link --}}
            <a href="{{ route('admin.products.export') }}" class="text-sm text-indigo-600 hover:underline">
                Export Products CSV
            </a>

            {{-- Import Products Form (new) --}}
            <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data"
                class="flex items-center gap-2">
                @csrf
                <input type="file" name="import_file" id="products_import_file" class="text-sm text-gray-700
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700
                                    hover:file:bg-indigo-100" />
                <x-button type="submit" size="sm" variant="primary">Import Products</x-button>
                @error('import_file')
                    <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror
            </form>
        </div>

        {{-- Display Validation Errors from Import --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Import failed!</strong>
                <span class="block sm:inline">Please check the following errors:</span>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Bulk actions (activate/deactivate) - Form Declaration --}}
        <form action="{{ route('admin.products.bulk') }}" method="post" id="bulk-actions-form" class="hidden">
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
                    <th class="px-4 py-2 text-left">Product</th>
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-right">Price</th>
                    <th class="px-4 py-2 text-right">Stock</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            @foreach ($products as $p)
                <tr>
                    <td class="px-4 py-2">
                        <input class="rowcheck" type="checkbox" name="ids[]" value="{{ $p->id }}" form="bulk-actions-form">
                    </td>
                    <td class="px-4 py-2">
                        <div class="font-medium">{{ $p->title }}</div>
                        <div class="text-xs text-gray-500">{{ $p->category?->name ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-2 text-gray-500">{{ $p->sku ?? '—' }}</td>
                    <td class="px-4 py-2 text-right">${{ number_format($p->price, 2) }}</td>
                    <td class="px-4 py-2 text-right">{{ $p->stock }}</td>
                    <td class="px-4 py-2">
                        @if ($p->status)
                            <x-badge color="green">Active</x-badge>
                        @else
                            <x-badge color="gray">Inactive</x-badge>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right space-x-3">
                        <a class="text-sm text-indigo-600" href="{{ route('admin.products.edit', $p) }}">Edit</a>

                        <x-delete-with-confirm :action="route('admin.products.destroy', $p)"
                            message="Delete {{ $p->title }}? This moves it to trash." confirm-text="Delete"
                            class="text-sm text-rose-600 hover:underline">
                            Delete
                        </x-delete-with-confirm>
                    </td>
                </tr>
            @endforeach
        </x-table>


        <x-pagination :paginator="$products" />
    </x-section>

    <x-section title="Trash" class="mt-6">
        <a href="{{ route('admin.trash.products') }}" class="text-sm text-indigo-600 hover:underline">
            View deleted products
        </a>
    </x-section>
@endsection