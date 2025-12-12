@props(['head' => null])
<div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm">
    <table class="min-w-full divide-y divide-gray-200">
        @if ($head)
            <thead class="bg-gray-50 text-gray-700 text-sm">
                {{ $head }}
            </thead>
        @endif
        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
            {{ $slot }}
        </tbody>
    </table>
</div>
