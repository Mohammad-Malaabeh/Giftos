@props(['coupon', 'route', 'method' => 'POST'])
<x-section>
    <x-form-errors />
    <form method="post" action="{{ $route }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <x-input label="Code" name="code" value="{{ old('code', $coupon->code) }}" required />
        <x-select label="Type" name="type">
            <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed</option>
            <option value="percent" @selected(old('type', $coupon->type) === 'percent')>Percent</option>
        </x-select>
        <x-input label="Value" name="value" type="number" step="0.01" value="{{ old('value', $coupon->value) }}"
            required />
        <x-input label="Max discount (for percent)" name="max_discount" type="number" step="0.01"
            value="{{ old('max_discount', $coupon->max_discount) }}" />
        <x-input label="Usage limit (optional)" name="usage_limit" type="number"
            value="{{ old('usage_limit', $coupon->usage_limit) }}" />
        <x-input label="Starts at" name="starts_at" type="datetime-local"
            value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}" />
        <x-input label="Expires at" name="expires_at" type="datetime-local"
            value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d\TH:i')) }}" />
        <x-select label="Active" name="active">
            <option value="1" @selected(old('active', (int) $coupon->active) === 1)>Yes</option>
            <option value="0" @selected(old('active', (int) $coupon->active) === 0)>No</option>
        </x-select>

        <div class="md:col-span-2">
            <x-button type="submit">{{ $method === 'POST' ? 'Create' : 'Update' }}</x-button>
        </div>
    </form>
</x-section>
