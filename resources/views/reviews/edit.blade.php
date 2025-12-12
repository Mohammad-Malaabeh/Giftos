@extends('layouts.app')

@section('title', 'Edit Review')

@section('content')
    <div class="max-w-2xl mx-auto">
        <x-section title="Edit Review">
            <form action="/reviews/{{ $review->id }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                
                <x-select name="rating" label="Rating" :value="$review->rating">
                    @for($i=5;$i>=1;$i--)
                        <option value="{{ $i }}" {{ $i == $review->rating ? 'selected' : '' }}>
                            {{ $i }} star{{ $i>1?'s':'' }}
                        </option>
                    @endfor
                </x-select>
                
                <x-textarea name="comment" label="Comment" rows="4">{{ $review->comment }}</x-textarea>
                
                <div class="flex gap-3">
                    <x-button type="submit">Update Review</x-button>
                    <a href="{{ route('product.show', $product->slug) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </x-section>
    </div>
@endsection
