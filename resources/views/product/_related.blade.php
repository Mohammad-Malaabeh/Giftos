@props(['items' => collect()])
@if ($items->count())
    <x-section title="Related products" class="mt-10">
        @include('partials.product-grid', ['products' => $items])
    </x-section>
@endif
