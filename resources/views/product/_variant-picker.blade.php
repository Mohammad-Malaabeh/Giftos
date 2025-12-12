@props(['product'])

@php
    // Fetch only active variants
    $variants = $product->variants()->where('status', true)->get();
@endphp

@if ($variants->count())
    <div x-data="{ selectedVariant: null }" class="mt-6 space-y-4">
        <h3 class="text-xl font-semibold text-gray-900 leading-tight">Choose Your Options</h3>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($variants as $v)
                <label for="variant-{{ $v->id }}"
                    class="
                        relative
                        flex flex-col justify-between
                        p-5
                        border-2 border-gray-300 rounded-xl
                        shadow-sm cursor-pointer
                        hover:border-indigo-500
                        transition-all duration-200 ease-in-out
                        @if (!$v->stock && !$product->backorder_allowed) opacity-60 cursor-not-allowed @endif
                    "
                    :class="{
                        'border-indigo-600 ring-2 ring-indigo-500 shadow-md': selectedVariant == {{ $v->id }},
                        'bg-gray-50 text-gray-400 border-gray-200': !selectedVariant && !{{ $loop->first }} && !
                            {{ $v->stock && $product->backorder_allowed }},
                        'ring-1 ring-inset ring-gray-200': selectedVariant !== {{ $v->id }}
                    }">
                    <input type="radio" id="variant-{{ $v->id }}" name="variant_choice" value="{{ $v->id }}"
                        class="absolute top-4 right-4 h-5 w-5 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        @change="selectedVariant = $event.target.value; document.getElementById('variant_id').value=this.value"
                        {{-- Disable if out of stock and backorder not allowed --}} @if (!$v->stock && !$product->backorder_allowed) disabled @endif>

                    <div class="flex-grow"> {{-- Use flex-grow for content spacing --}}
                        <div class="font-bold text-lg text-gray-900">{{ $v->sku ?: 'Variant #' . $v->id }}</div>
                        @if (is_array($v->options) && count($v->options))
                            <div class="text-gray-600 text-sm mt-1">
                                {{ collect($v->options)->map(fn($val, $key) => "$key: $val")->implode(', ') }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xl font-extrabold text-gray-900">
                            ${{ number_format($v->effective_price, 2) }}
                        </span>
                        @if ($v->stock === 0 && !$product->backorder_allowed)
                            <span class="text-sm font-semibold text-red-600 ml-3 py-1 px-2 rounded-full bg-red-50">Out
                                of Stock</span>
                        @elseif ($v->stock < 5 && $v->stock > 0)
                            <span
                                class="text-sm font-semibold text-yellow-600 ml-3 py-1 px-2 rounded-full bg-yellow-50">Low
                                Stock ({{ $v->stock }} left)</span>
                        @elseif ($v->stock === 0 && $product->backorder_allowed)
                            <span
                                class="text-sm font-semibold text-orange-600 ml-3 py-1 px-2 rounded-full bg-orange-50">Backorder</span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>

        {{-- Hidden input for the selected variant ID --}}
        <input type="hidden" name="variant_id" id="variant_id">
    </div>
@endif
