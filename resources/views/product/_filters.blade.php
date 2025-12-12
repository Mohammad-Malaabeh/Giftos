<form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-3">
    <div class="md:col-span-2">
        <x-input name="q" label="Search" value="{{ $q }}" />
    </div>
    <div>
        <x-select name="category_id" label="Category">
            <option value="">All</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected((int) $categoryId === (int) $c->id)>{{ $c->name }}</option>
            @endforeach
        </x-select>
    </div>
    <div class="flex items-end">
        <x-button type="submit" class="w-full">Filter</x-button>
    </div>
</form>
