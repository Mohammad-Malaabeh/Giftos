@props(['address' => null])
<form action="{{ $address ? route('addresses.update', $address) : route('addresses.store') }}" method="post"
    class="space-y-3">
    @csrf
    @if ($address)
        @method('PUT')
    @endif

    <x-input name="label" label="Label (optional)" value="{{ old('label', $address->label ?? '') }}" />
    <x-input name="name" label="Full name" value="{{ old('name', $address->name ?? auth()->user()->name) }}" />
    <x-input name="line1" label="Address line 1" value="{{ old('line1', $address->line1 ?? '') }}" />
    <x-input name="line2" label="Address line 2" value="{{ old('line2', $address->line2 ?? '') }}" />
    <x-input name="city" label="City" value="{{ old('city', $address->city ?? '') }}" />
    <x-input name="zip" label="ZIP" value="{{ old('zip', $address->zip ?? '') }}" />
    <x-input name="country" label="Country (ISO2)" value="{{ old('country', $address->country ?? '') }}" />

    <div class="flex items-center gap-3">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_default_shipping" value="1" class="text-indigo-600"
                @checked(old('is_default_shipping', $address?->is_default_shipping))>
            Default shipping
        </label>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_default_billing" value="1" class="text-indigo-600"
                @checked(old('is_default_billing', $address?->is_default_billing))>
            Default billing
        </label>
    </div>

    <x-button type="submit">{{ $address ? 'Update' : 'Save' }}</x-button>
</form>
