@props(['label' => null, 'name' => null, 'rows' => 4, 'hint' => null])
<div {{ $attributes->only('class')->merge(['class' => 'space-y-1']) }}>
    @if ($label)
        <label for="{{ $attributes->get('id') ?? $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif
    <textarea name="{{ $name }}" id="{{ $attributes->get('id') ?? $name }}" rows="{{ $rows }}"
        {{ $attributes->except('class')->merge([
            'class' => 'block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm',
        ]) }}>{{ $slot }}</textarea>
    @error($name)
        <p class="text-sm text-rose-600">{{ $message }}</p>
    @enderror
    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif
</div>
