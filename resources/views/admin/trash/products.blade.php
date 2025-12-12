@extends('layouts.admin')

@section('page_title', 'Trash · Products')

@section('content')
    <x-section>
        <x-table>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Title</th>
                    <th class="px-4 py-2 text-left">Deleted at</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            @foreach ($products as $p)
                <tr>
                    <td class="px-4 py-2">{{ $p->title }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $p->deleted_at }}</td>
                    <td class="px-4 py-2 text-right">
                        {{-- Restore Form (POST) --}}
                        <form action="{{ route('admin.trash.products.restore', $p->id) }}" method="POST" class="inline-block">
                            @csrf
                            <x-button size="sm" variant="secondary" type="submit">Restore</x-button>
                        </form>

                        {{-- Purge Form (DELETE) --}}
                        <form action="{{ route('admin.trash.products.purge', $p->id) }}" method="POST" class="inline-block"
                            onsubmit="return confirm('Permanently delete? This action cannot be undone.');">
                            @csrf
                            @method('DELETE') {{-- Important for DELETE requests --}}
                            <x-button size="sm" variant="danger" type="submit">Purge</x-button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-table>
        <x-pagination :paginator="$products" />
    </x-section>
@endsection
