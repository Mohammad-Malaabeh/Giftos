@extends('layouts.admin')

@section('page_title', 'Activity log')

@section('content')
    <x-section title="Activity">
        <form method="get" class="flex gap-2 mb-4">
            <input name="q" value="{{ $q }}" placeholder="Search actions, subjects..."
                class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <x-button type="submit">Search</x-button>
        </form>

        <x-table>
            <thead class="bg-gray-50 text-gray-700 text-sm">
                <tr>
                    <th class="px-4 py-2 text-left">When</th>
                    <th class="px-4 py-2 text-left">User</th>
                    <th class="px-4 py-2 text-left">Action</th>
                    <th class="px-4 py-2 text-left">Subject</th>
                    <th class="px-4 py-2 text-left">IP</th>
                </tr>
            </thead>
            @forelse($logs as $log)
                <tr>
                    <td class="px-4 py-2 text-gray-500">{{ $log->created_at }}</td>
                    <td class="px-4 py-2">{{ $log->user?->email ?? '—' }}</td>
                    <td class="px-4 py-2"><x-badge color="indigo">{{ $log->action }}</x-badge></td>
                    <td class="px-4 py-2 text-gray-700">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                    </td>
                    <td class="px-4 py-2 text-gray-500">{{ $log->ip }}</td>
                </tr>
            @empty
                <tr>
                    <td class="px-4 py-3 text-gray-500" colspan="5">No activity.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$logs" />
    </x-section>
@endsection
