@props(['product'])
@php $variants = $product->variants()->orderBy('id')->get(); @endphp

<x-section title="Variants">
    <div class="space-y-4">
        {{-- Existing variants --}}
        @forelse($variants as $v)
            <div class="border rounded-lg p-4 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-3 min-w-0">
                        <x-input name="variants[{{ $v->id }}][sku]" label="SKU"
                            value="{{ old("variants.$v->id.sku", $v->sku) }}" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input name="variants[{{ $v->id }}][price]" label="Price" type="number" step="0.01"
                            value="{{ old("variants.$v->id.price", $v->price) }}" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input name="variants[{{ $v->id }}][sale_price]" label="Sale price" type="number"
                            step="0.01" value="{{ old("variants.$v->id.sale_price", $v->sale_price) }}" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input name="variants[{{ $v->id }}][stock]" label="Stock" type="number"
                            value="{{ old("variants.$v->id.stock", $v->stock) }}" />
                    </div>
                    <div class="md:col-span-2">
                        <x-select name="variants[{{ $v->id }}][status]" label="Status">
                            <option value="1" @selected(old("variants.$v->id.status", $v->status))>Active</option>
                            <option value="0" @selected(!old("variants.$v->id.status", $v->status))>Inactive</option>
                        </x-select>
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <button type="button" class="text-sm text-rose-600 hover:underline"
                            onclick="addVariantToRemove('{{ $v->id }}')">
                            Delete
                        </button>
                    </div>

                    <div class="md:col-span-12">
                        <x-textarea name="variants[{{ $v->id }}][options]" label="Options JSON" rows="3">
                            {!! old("variants.$v->id.options", json_encode($v->options ?? (object) [])) !!}
                        </x-textarea>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-600">No variants yet.</p>
        @endforelse

        {{-- New variants container --}}
        <div id="variant-new-container" class="space-y-4"></div>

        <div class="flex items-center gap-3">
            <x-button type="button" variant="secondary" onclick="addVariantCard()">Add variant</x-button>
        </div>

        <input type="hidden" name="remove_variants" id="remove_variants" value="">
    </div>

    <script>
        let variantNewIndex = 0;

        function addVariantCard() {
            const id = 'new_' + (variantNewIndex++);
            const tpl = `
            <div class="border rounded-lg p-4 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">SKU</label>
                        <input name="variants[${id}][sku]"
                               class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" step="0.01" name="variants[${id}][price]"
                               class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Sale price</label>
                        <input type="number" step="0.01" name="variants[${id}][sale_price]"
                               class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="variants[${id}][stock]" value="0"
                               class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="variants[${id}][status]"
                                class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm bg-white">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <button type="button" class="text-sm text-rose-600 hover:underline"
                                onclick="this.closest('.border.rounded-lg').remove()">
                            Remove
                        </button>
                    </div>

                    <div class="md:col-span-12">
                        <label class="block text-sm font-medium text-gray-700">Options JSON</label>
                        <textarea name="variants[${id}][options]" rows="3"
                                    class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">{}</textarea>
                    </div>
                </div>
            </div>`;
            document.getElementById('variant-new-container').insertAdjacentHTML('beforeend', tpl);
        }

        function addVariantToRemove(id) {
            const input = document.getElementById('remove_variants');
            const existing = input.value ? input.value.split(',') : [];
            if (!existing.includes(id)) existing.push(id);
            input.value = existing.join(',');
            alert('Variant will be deleted when you save.');
        }
    </script>
</x-section>
