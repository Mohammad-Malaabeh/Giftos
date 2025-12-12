@props(['product'])
<x-section title="Write a review" class="mt-10">
    <form action="/reviews/products/{{ $product->id }}" method="post" class="space-y-4">
        @csrf
        <x-select name="rating" label="Rating">
            @for($i=5;$i>=1;$i--)
                <option value="{{ $i }}">{{ $i }} star{{ $i>1?'s':'' }}</option>
            @endfor
        </x-select>
        <x-textarea name="comment" label="Comment" rows="4" />
        <x-button type="submit">Submit review</x-button>
    </form>
</x-section>