@props(['product', 'categories', 'route', 'method' => 'POST'])
<x-section>
    <x-form-errors />
    <form method="post" action="{{ $route }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2 space-y-4">
                <x-input label="Title" name="title" value="{{ old('title', $product->title) }}" required />
                <x-input label="Slug (optional)" name="slug" value="{{ old('slug', $product->slug) }}" />
                <x-textarea label="Description" name="description"
                    rows="6">{{ old('description', $product->description) }}</x-textarea>
                <div class="grid grid-cols-2 gap-4">
                    <x-input label="Price" name="price" type="number" step="0.01"
                        value="{{ old('price', $product->price) }}" required />
                    <x-input label="Sale price" name="sale_price" type="number" step="0.01"
                        value="{{ old('sale_price', $product->sale_price) }}" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-input label="Stock" name="stock" type="number" value="{{ old('stock', $product->stock) }}" />
                    <x-input label="SKU" name="sku" value="{{ old('sku', $product->sku) }}" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-select label="Category" name="category_id">
                        <option value="">—</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-select label="Status" name="status">
                        <option value="1" @selected(old('status', (int) $product->status) === 1)>Active</option>
                        <option value="0" @selected(old('status', (int) $product->status) === 0)>Inactive</option>
                    </x-select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <label class="text-sm text-gray-700">Main image
                        <input type="file" name="image" class="mt-1 block w-full text-sm">
                    </label>
                    <label class="text-sm text-gray-700">Gallery (multiple)
                        <input type="file" name="gallery[]" multiple class="mt-1 block w-full text-sm">
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="backorder_allowed" value="1" class="text-indigo-600"
                            @checked(old('backorder_allowed', $product->backorder_allowed))>
                        Backorder allowed
                    </label>
                    <x-input label="Backorder ETA" name="backorder_eta" type="date"
                        value="{{ old('backorder_eta', optional($product->backorder_eta)->toDateString()) }}" />
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <x-input label="Meta title" name="meta_title"
                        value="{{ old('meta_title', $product->meta_title) }}" />
                    <x-textarea label="Meta description"
                        name="meta_description">{{ old('meta_description', $product->meta_description) }}</x-textarea>
                </div>
            </div>

            <div class="space-y-6">
                @if ($product->exists && $product->image_path)
                    <div>
                        <div class="text-sm font-medium text-gray-700 mb-1">Current image</div>
                        <img src="{{ asset('storage/' . $product->image_path) }}" class="rounded border">
                    </div>
                @endif

                @if ($product->exists)
                    @include('admin.products._gallery-manager', ['product' => $product])
                    @include('admin.products._variants-manager', ['product' => $product])
                @endif
            </div>
        </div>

        <x-button type="submit">{{ $method === 'POST' ? 'Create' : 'Update' }}</x-button>
    </form>
</x-section>
