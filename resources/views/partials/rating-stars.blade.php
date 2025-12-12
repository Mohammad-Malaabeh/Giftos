@props(['value' => 0, 'size' => 'sm'])
@php
    $sizeMap = ['sm' => 'h-4 w-4', 'md' => 'h-5 w-5', 'lg' => 'h-6 w-6'];
    $val = max(0, min(5, (float) $value));
@endphp
<div class="inline-flex items-center gap-0.5">
    @for ($i = 1; $i <= 5; $i++)
        @php $filled = $i <= $val; @endphp
        <svg class="{{ $sizeMap[$size] }} {{ $filled ? 'text-yellow-500' : 'text-gray-300' }}" viewBox="0 0 20 20"
            fill="currentColor" aria-hidden="true">
            <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.293c.3.922-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.838-.196-1.539-1.118l1.07-3.293a1 1 0 00-.364-1.118L2.88 8.72c-.783-.57-.38-1.81.588-1.81H6.93a1 1 0 00.95-.69l1.169-3.293z" />
        </svg>
    @endfor
</div>
