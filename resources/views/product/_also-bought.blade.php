@props(['items' => collect()])
@if ($items->count())
    <x-section title="Customers also bought" class="mt-10">
        @include('partials.product-grid', ['products' => $items])
    </x-section>
@endif
