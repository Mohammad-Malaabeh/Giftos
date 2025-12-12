@props(['paginator'])
@if ($paginator->hasPages())
    <div class="mt-6">
        {{ $paginator->onEachSide(1)->links() }}
    </div>
@endif
