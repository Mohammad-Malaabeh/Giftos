@props(['category', 'parents', 'route', 'method' => 'POST'])
<x-section>
    <x-form-errors />
    <form method="post" action="{{ $route }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <x-input label="Name" name="name" value="{{ old('name', $category->name) }}" required />
        <x-input label="Slug (optional)" name="slug" value="{{ old('slug', $category->slug) }}" />
        <x-select label="Parent" name="parent_id">
            <option value="">None</option>
            @foreach ($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id) == $p->id)>{{ $p->name }}</option>
            @endforeach
        </x-select>
        <x-select label="Status" name="status">
            <option value="1" @selected(old('status', (int) $category->status) === 1)>Active</option>
            <option value="0" @selected(old('status', (int) $category->status) === 0)>Inactive</option>
        </x-select>

        <div class="md:col-span-2">
            <x-input label="Meta title" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" />
            <x-textarea label="Meta description"
                name="meta_description">{{ old('meta_description', $category->meta_description) }}</x-textarea>
        </div>

        <div class="md:col-span-2 flex justify-between items-center">
            <x-button type="submit">{{ $method === 'POST' ? 'Create' : 'Update' }}</x-button>
            
            @if ($method === 'PUT')
                <div>
                    <a href="{{ route('admin.categories.delete', $category) }}" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                       onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.');">
                        Delete Category
                    </a>
                </div>
            @endif
        </div>
    </form>
</x-section>
