@props(['label' => null, 'name' => null, 'checked' => false, 'hint' => null])
<label class="inline-flex items-start gap-2">
    <input type="checkbox" name="{{ $name }}" value="1" {{ $checked ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500']) }}>
    <span class="text-sm text-gray-700">{{ $label }}</span>
</label>
@error($name)
    <p class="text-sm text-rose-600">{{ $message }}</p>
@enderror
@if ($hint)
    <p class="text-xs text-gray-500">{{ $hint }}</p>
@endif
