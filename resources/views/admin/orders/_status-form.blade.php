@props(['order'])
<x-section title="Update order">
    <form method="post" action="{{ route('admin.orders.update', $order) }}" class="space-y-3">
        @csrf
        @method('PUT')
        <x-select name="status" label="Status">
            @foreach (['pending', 'paid', 'shipped', 'completed', 'canceled', 'refunded'] as $s)
                <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </x-select>
        <x-select name="payment_status" label="Payment status">
            @foreach (['unpaid', 'paid', 'refunded', 'failed'] as $ps)
                <option value="{{ $ps }}" @selected($order->payment_status === $ps)>{{ ucfirst($ps) }}</option>
            @endforeach
        </x-select>
        <div class="grid grid-cols-2 gap-3">
            <x-input name="shipping" label="Shipping" type="number" step="0.01"
                value="{{ old('shipping', $order->shipping) }}" />
            <x-input name="tax" label="Tax" type="number" step="0.01" value="{{ old('tax', $order->tax) }}" />
            <x-input name="discount" label="Discount" type="number" step="0.01"
                value="{{ old('discount', $order->discount) }}" />
            <x-input name="carrier" label="Carrier" value="{{ old('carrier', $order->carrier) }}" />
            <x-input name="tracking_number" label="Tracking number"
                value="{{ old('tracking_number', $order->tracking_number) }}" />
        </div>
        <x-button type="submit">Save</x-button>
    </form>
</x-section>
