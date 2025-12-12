@props(['product'])
@php $reviews = $product->approvedReviews()->with('user')->latest()->get(); @endphp
@if ($reviews->count())
    <x-section title="Customer reviews" class="mt-8">
        <div class="space-y-4">
            @foreach ($reviews as $r)
                <div class="border rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    @include('partials.rating-stars', ['value' => $r->rating])
                                    <div class="text-sm font-medium text-gray-900">{{ $r->user->name }}</div>
                                </div>
                                <div class="text-xs text-gray-500">{{ $r->created_at->diffForHumans() }}</div>
                            </div>
                            @if ($r->comment)
                                <p class="text-sm text-gray-700">{{ $r->comment }}</p>
                            @endif
                        </div>
                        
                        @auth
                            @if (auth()->user()->id === $r->user_id)
                                <div class="flex gap-2 ml-4">
                                    <a href="/reviews/{{ $r->id }}/edit" 
                                       class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Edit
                                    </a>
                                    <x-delete-with-confirm :action="'/reviews/' . $r->id" 
                                        message="Delete your review?" 
                                        confirm-text="Delete"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </x-delete-with-confirm>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>
    </x-section>
@endif
