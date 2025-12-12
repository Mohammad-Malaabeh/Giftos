@props(['products'])
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
    @foreach ($products as $p)
        @include('partials.product-card', ['product' => $p])
    @endforeach
</div>
