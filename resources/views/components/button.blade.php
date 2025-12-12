@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])
@php
    $base =
        'inline-flex items-center justify-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition';
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];
    $variants = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
        'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500',
        'ghost' => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-indigo-500',
    ];
@endphp
<button type="{{ $type }}" {{ $attributes->merge(['class' => "$base " . $sizes[$size] . ' ' . $variants[$variant]]) }}>
    {{ $slot }}
</button>
