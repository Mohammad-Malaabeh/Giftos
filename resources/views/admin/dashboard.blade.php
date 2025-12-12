@extends('layouts.admin')

@section('page_title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-4">
            <div class="text-sm text-gray-500">Orders today</div>
            <div class="text-2xl font-semibold">{{ $ordersToday }}</div>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <div class="text-sm text-gray-500">Revenue today</div>
            <div class="text-2xl font-semibold">${{ number_format($revenueToday, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <div class="text-sm text-gray-500">Total orders</div>
            <div class="text-2xl font-semibold">{{ $ordersTotal }}</div>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <div class="text-sm text-gray-500">Total revenue</div>
            <div class="text-2xl font-semibold">${{ number_format($revenueTotal, 2) }}</div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-section title="Sales summary (range)">
            <form method="get" class="flex flex-wrap items-end gap-3 mb-4">
                <x-input label="From" name="from" type="date" value="{{ optional($from)->toDateString() }}" />
                <x-input label="To" name="to" type="date" value="{{ optional($to)->toDateString() }}" />
                <x-button type="submit">Apply</x-button>
            </form>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 rounded p-3">
                    <dt class="text-gray-600">Orders</dt>
                    <dd class="text-lg font-semibold">{{ $ordersCount }}</dd>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <dt class="text-gray-600">Revenue</dt>
                    <dd class="text-lg font-semibold">${{ number_format($revenue, 2) }}</dd>
                </div>
            </dl>
        </x-section>

        <x-section title="Top products">
            <x-table>
                @foreach ($topProducts as $tp)
                    <tr>
                        <td class="px-4 py-2">{{ $tp->title }}</td>
                        <td class="px-4 py-2 text-right">{{ $tp->qty }}</td>
                    </tr>
                @endforeach
            </x-table>
        </x-section>

        <x-section title="Top customers" class="lg:col-span-2">
            <x-table>
                @foreach ($topCustomers as $c)
                    <tr>
                        <td class="px-4 py-2">User #{{ $c->user_id }}</td>
                        <td class="px-4 py-2">{{ $c->orders }} orders</td>
                        <td class="px-4 py-2 text-right">${{ number_format($c->spend, 2) }}</td>
                    </tr>
                @endforeach
            </x-table>
            <div class="mt-3 text-sm text-gray-600">
                Repeat customers: {{ $repeatCustomers }}
            </div>
        </x-section>
    </div>
@endsection
