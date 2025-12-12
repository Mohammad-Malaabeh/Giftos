@props(['title' => null, 'description' => null, 'actions' => null, 'class' => ''])
<section {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-gray-200 ' . $class]) }}>
    @if ($title || $actions || $description)
        <div class="px-4 py-4 sm:px-6 border-b border-gray-100">
            <div class="flex items-start justify-between gap-4">
                <div>
                    @if ($title)
                        <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
                    @endif
                    @if ($description)
                        <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
                    @endif
                </div>
                @if ($actions)
                    <div class="shrink-0">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    <div class="px-4 py-5 sm:p-6">
        {{ $slot }}
    </div>
</section>
