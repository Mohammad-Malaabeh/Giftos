@props(['color' => 'gray'])
@php
    $map = [
        'gray' => 'bg-gray-100 text-gray-800 ring-gray-200',
        'green' => 'bg-green-100 text-green-800 ring-green-200',
        'blue' => 'bg-blue-100 text-blue-800 ring-blue-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 ring-yellow-200',
        'red' => 'bg-rose-100 text-rose-800 ring-rose-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
    ];
@endphp
<span
    {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ' . $map[$color]]) }}>
    {{ $slot }}
</span>
