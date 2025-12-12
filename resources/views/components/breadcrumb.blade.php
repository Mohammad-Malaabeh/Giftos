@props(['items' => []])
<nav class="text-sm" aria-label="Breadcrumb">
    <ol class="flex items-center gap-2 text-gray-600">
        @foreach ($items as $i => $item)
            @if ($i > 0)
                <li class="text-gray-400">/</li>
            @endif
            <li>
                @if (!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-gray-900">{{ $item['label'] }}</a>
                @else
                    <span class="text-gray-900 font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
