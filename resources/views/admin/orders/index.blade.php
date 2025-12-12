@extends('layouts.admin')

@section('page_title', 'Orders')

@section('content')
    <x-section>
        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
            <x-input name="q" label="Search (number / txn / email)" value="{{ $q }}" class="md:col-span-2" />
            <x-input name="from" label="From" type="date" value="{{ $from?->toDateString() }}" />
            <x-input name="to" label="To" type="date" value="{{ $to?->toDateString() }}" />
            <x-select name="status" label="Status">
                <option value="">All</option>
                @foreach (['pending', 'paid', 'shipped', 'completed', 'canceled', 'refunded'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </x-select>
            <x-select name="payment_status" label="Payment">
                <option value="">All</option>
                @foreach (['unpaid', 'paid', 'refunded', 'failed'] as $ps)
                    <option value="{{ $ps }}" @selected($paymentStatus === $ps)>{{ ucfirst($ps) }}</option>
                @endforeach
            </x-select>
            <div class="md:col-span-5">
                <x-button type="submit">Filter</x-button>
                <a href="{{ route('admin.orders.index') }}" class="ml-2 text-sm text-gray-600 hover:underline">Reset</a>
            </div>
        </form>

        <div class="flex items-center gap-4 mb-4">
            {{-- Export Orders Link --}}
            <a href="{{ route('admin.orders.export') }}" class="text-sm text-indigo-600 hover:underline">
                Export Orders CSV
            </a>

            {{-- Import Orders Form (new) --}}
            <form action="{{ route('admin.orders.import') }}" method="POST" enctype="multipart/form-data"
                class="flex items-center gap-2">
                @csrf
                <input type="file" name="import_file" id="orders_import_file"
                    class="text-sm text-gray-700
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-indigo-50 file:text-indigo-700
                    hover:file:bg-indigo-100" />
                <x-button type="submit" size="sm" variant="primary">Import Orders</x-button>
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

        <form method="post" action="{{ route('admin.orders.bulk') }}" class="mb-3">
            @csrf
            <input type="hidden" name="action" id="bulk_action">
            <div class="flex items-center gap-2 mb-2">
                <x-button type="button" variant="secondary"
                    onclick="document.getElementById('bulk_action').value='mark_paid'; this.closest('form').submit();">Mark
                    paid</x-button>
                <x-button type="button" variant="secondary"
                    onclick="document.getElementById('bulk_action').value='mark_shipped'; this.closest('form').submit();">Mark
                    shipped</x-button>
            </div>

            <x-table>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2"><input type="checkbox"
                                onclick="document.querySelectorAll('.rowcheck').forEach(cb=>cb.checked=this.checked)"></th>
                        <th class="px-4 py-2 text-left">Order</th>
                        <th class="px-4 py-2 text-left">User</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Payment</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-left">Created</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                @foreach ($orders as $o)
                    <tr>
                        <td class="px-4 py-2"><input class="rowcheck" type="checkbox" name="ids[]"
                                value="{{ $o->id }}"></td>
                        <td class="px-4 py-2 font-medium">{{ $o->number }}</td>
                        <td class="px-4 py-2">{{ $o->user?->email ?? 'Guest' }}</td>
                        <td class="px-4 py-2"><x-badge color="indigo">{{ $o->status }}</x-badge></td>
                        <td class="px-4 py-2"><x-badge
                                color="{{ $o->payment_status === 'paid' ? 'green' : ($o->payment_status === 'failed' ? 'red' : 'gray') }}">{{ $o->payment_status }}</x-badge>
                        </td>
                        <td class="px-4 py-2 text-right">${{ number_format($o->total, 2) }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $o->created_at->toDateTimeString() }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.orders.show', $o) }}" class="text-sm text-indigo-600">View</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </form>

        <x-pagination :paginator="$orders" />
    </x-section>
@endsection
