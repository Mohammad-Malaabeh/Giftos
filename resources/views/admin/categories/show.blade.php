@extends('layouts.admin')

@section('page_title', 'Category details')

@section('content')
    <x-section>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Name</span>
                <div class="font-medium">{{ $category->name }}</div>
            </div>
            <div><span class="text-gray-500">Slug</span>
                <div class="font-medium">{{ $category->slug }}</div>
            </div>
            <div><span class="text-gray-500">Status</span>
                <div>
                    @if ($category->status)
                    <x-badge color="green">Active</x-badge>@else<x-badge color="gray">Inactive</x-badge>
                    @endif
                </div>
            </div>
            <div><span class="text-gray-500">Parent</span>
                <div>{{ $category->parent?->name ?? '—' }}</div>
            </div>
        </div>
    </x-section>
@endsection
