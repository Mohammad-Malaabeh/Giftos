@props(['product'])
<x-section title="Gallery">
    @if (is_array($product->gallery) && count($product->gallery))
        <div class="grid grid-cols-3 gap-2">
            @foreach ($product->gallery as $g)
                <div class="relative group">
                    <img src="{{ asset('storage/' . $g) }}" class="rounded border">
                    <label class="absolute top-2 right-2 hidden group-hover:block">
                        <input type="checkbox" name="remove_gallery[]" value="{{ $g }}" class="text-rose-600">
                        <span class="text-xs bg-white/90 rounded px-2 py-0.5 border">Remove</span>
                    </label>
                </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-500 mt-2">Check “Remove” and save to delete images.</p>
    @else
        <p class="text-sm text-gray-600">No gallery images.</p>
    @endif
</x-section>
