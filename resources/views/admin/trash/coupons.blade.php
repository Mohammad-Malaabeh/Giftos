@extends('layouts.admin')

@section('page_title', 'Trash · Coupons')

@section('content')
    <x-section>
        <x-table>
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Code</th>
                    <th class="px-4 py-2 text-left">Deleted at</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            @foreach ($coupons as $cp)
                <tr>
                    <td class="px-4 py-2">{{ $cp->code }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $cp->deleted_at }}</td>
                    <td class="px-4 py-2 text-right">
                        {{-- Restore Form (POST) --}}
                        <form action="{{ route('admin.trash.coupons.restore', $cp->id) }}" method="POST" class="inline-block">
                            @csrf
                            <x-button size="sm" variant="secondary" type="submit">Restore</x-button>
                        </form>

                        {{-- Purge Form (DELETE) --}}
                        <form action="{{ route('admin.trash.coupons.purge', $cp->id) }}" method="POST" class="inline-block"
                            onsubmit="return confirm('Permanently delete? This action cannot be undone.');">
                            @csrf
                            @method('DELETE') {{-- Important for DELETE requests --}}
                            <x-button size="sm" variant="danger" type="submit">Purge</x-button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-table>
        <x-pagination :paginator="$coupons" />
    </x-section>
@endsection
