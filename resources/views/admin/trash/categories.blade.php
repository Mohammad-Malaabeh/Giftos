@extends('layouts.admin')

@section('page_title', 'Trash · Categories')

@section('content')
    <x-section>
        <x-table>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Deleted at</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            @foreach ($categories as $c)
                <tr>
                    <td class="px-4 py-2">{{ $c->name }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $c->deleted_at }}</td>
                    <td class="px-4 py-2 text-right">
                        {{-- Restore Form (POST) --}}
                        <form action="{{ route('admin.trash.categories.restore', $c->id) }}" method="POST"
                            class="inline-block">
                            @csrf
                            <x-button size="sm" variant="secondary" type="submit">Restore</x-button>
                        </form>

                        {{-- Purge Form (DELETE) --}}
                        <form action="{{ route('admin.trash.categories.purge', $c->id) }}" method="POST"
                            class="inline-block"
                            onsubmit="return confirm('Permanently delete? This action cannot be undone.');">
                            @csrf
                            @method('DELETE') {{-- Important for DELETE requests --}}
                            <x-button size="sm" variant="danger" type="submit">Purge</x-button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-table>
        <x-pagination :paginator="$categories" />
    </x-section>
@endsection
