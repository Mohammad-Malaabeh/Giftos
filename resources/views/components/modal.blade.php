@props(['id', 'title' => null])
<div x-data="{ open: false }" x-on:open-{{ $id }}.window="open = true"
    x-on:close-{{ $id }}.window="open = false">
    <div x-show="open" class="fixed inset-0 z-40 bg-black/40" x-transition.opacity></div>
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition>
        <div class="w-full max-w-lg bg-white rounded-xl shadow-xl border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" x-on:click="open=false">✕</button>
            </div>
            <div class="p-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
