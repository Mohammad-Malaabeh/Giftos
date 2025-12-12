@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-6xl mx-auto mt-10">
    <form id="checkout-form" action="{{ route('checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        <!-- Left column: Shipping, Billing, Payment -->
        <div class="lg:col-span-2 space-y-6">

            {{-- Shipping Address --}}
            <x-section title="Shipping Address">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input name="shipping[name]" label="Full Name" value="{{ old('shipping.name', auth()->user()?->name) }}" />
                    <x-input name="shipping[line1]" label="Address Line 1" value="{{ old('shipping.line1', $defaultShipping->line1 ?? '') }}" />
                    <x-input name="shipping[city]" label="City" value="{{ old('shipping.city', $defaultShipping->city ?? '') }}" />
                    <x-input name="shipping[country]" label="Country (ISO2)" value="{{ old('shipping.country', $defaultShipping->country ?? '') }}" />
                    <x-input name="shipping[zip]" label="ZIP / Postal Code" value="{{ old('shipping.zip', $defaultShipping->zip ?? '') }}" />
                </div>
            </x-section>

            {{-- Billing Address --}}
            <x-section title="Billing Address">
                <div x-data="{ same: {{ old('billing_same', true) ? 'true' : 'false' }} }" class="space-y-3">
                    <x-checkbox name="billing_same" label="Billing same as shipping" :checked="old('billing_same', true)" @change="same = $event.target.checked" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="!same">
                        <x-input name="billing[name]" label="Full Name" value="{{ old('billing.name', $defaultBilling->name ?? '') }}" />
                        <x-input name="billing[line1]" label="Address Line 1" value="{{ old('billing.line1', $defaultBilling->line1 ?? '') }}" />
                        <x-input name="billing[city]" label="City" value="{{ old('billing.city', $defaultBilling->city ?? '') }}" />
                        <x-input name="billing[country]" label="Country (ISO2)" value="{{ old('billing.country', $defaultBilling->country ?? '') }}" />
                        <x-input name="billing[zip]" label="ZIP / Postal Code" value="{{ old('billing.zip', $defaultBilling->zip ?? '') }}" />
                    </div>
                </div>
            </x-section>

            {{-- Payment Method --}}
            <x-section title="Payment Method">
                <div class="space-y-2">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="payment_method" value="cod" class="text-indigo-600" checked>
                        <span>Cash on Delivery (COD)</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="radio" name="payment_method" value="stripe" class="text-indigo-600">
                        <span>Credit/Debit Card (Stripe)</span>
                    </label>

                    {{-- Stripe Card Element --}}
                    <div id="stripe-fields" class="mt-3 hidden space-y-2">
                        <div id="card-element" class="border rounded p-3 bg-white"></div>
                        <div id="card-errors" class="text-red-600 text-sm mt-1"></div>
                        <x-button type="button" id="pay-stripe" class="w-full">Pay with Card</x-button>
                    </div>
                </div>
            </x-section>

        </div>

        <!-- Right column: Order Summary -->
        <div class="space-y-6">
            <x-section title="Order Summary">
                <ul class="text-sm space-y-2">
                    @foreach ($items as $item)
                        <li class="flex justify-between">
                            <span>{{ $item->product?->title ?? 'Item' }} × {{ $item->quantity }}</span>
                            <span>${{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="text-sm space-y-2 mt-4">
                    <div class="flex justify-between"><dt>Subtotal</dt><dd>${{ number_format($totals['subtotal'], 2) }}</dd></div>
                    <div class="flex justify-between"><dt>Discount</dt><dd>-${{ number_format($totals['discount'], 2) }}</dd></div>
                    <div class="flex justify-between"><dt>Tax</dt><dd>${{ number_format($totals['tax'], 2) }}</dd></div>
                    <div class="flex justify-between"><dt>Shipping</dt><dd>${{ number_format($totals['shipping'], 2) }}</dd></div>
                    <div class="border-t pt-2 flex justify-between font-semibold text-gray-900">
                        <dt>Total</dt><dd>${{ number_format($totals['total'], 2) }}</dd>
                    </div>
                </dl>

                {{-- COD Submit Button --}}
                <x-button type="submit" id="cod-submit" class="mt-4 w-full">Place Order</x-button>
            </x-section>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const stripeRadio = document.querySelector('input[value="stripe"]');
    const codRadio = document.querySelector('input[value="cod"]');
    const stripeFields = document.getElementById('stripe-fields');
    const codButton = document.getElementById('cod-submit');
    const form = document.getElementById('checkout-form');
    const payStripeBtn = document.getElementById('pay-stripe');
    const cardErrors = document.getElementById('card-errors');
    let stripe, cardElement;

    // Toggle payment method
    function togglePayment() {
        if (stripeRadio.checked) {
            stripeFields.classList.remove('hidden');
            codButton.classList.add('hidden');
            if (!stripe) initStripe();
        } else {
            stripeFields.classList.add('hidden');
            codButton.classList.remove('hidden');
        }
    }
    codRadio.addEventListener('change', togglePayment);
    stripeRadio.addEventListener('change', togglePayment);
    togglePayment();

    // Initialize Stripe
    function initStripe() {
        stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        cardElement = elements.create('card', { style: { base: { fontSize: '16px', color: '#32325d' }, invalid: { color: '#fa755a' } } });
        cardElement.mount('#card-element');
        cardElement.on('change', e => { cardErrors.textContent = e.error ? e.error.message : ''; });

        payStripeBtn.addEventListener('click', async () => {
            payStripeBtn.disabled = true;
            payStripeBtn.textContent = 'Processing...';

            // Create PaymentMethod
            const { paymentMethod, error } = await stripe.createPaymentMethod({ type: 'card', card: cardElement });
            if (error) {
                cardErrors.textContent = error.message;
                payStripeBtn.disabled = false;
                payStripeBtn.textContent = 'Pay with Card';
                return;
            }

            // Gather form data
            const formData = new FormData(form);
            formData.append('payment_method_id', paymentMethod.id);
            formData.append('payment_method', 'stripe');

            // First, create the order on the backend
            const createOrder = await fetch('{{ route("checkout.store") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });
            const orderData = await createOrder.json();

            if (!orderData.order_id) {
                cardErrors.textContent = orderData.message || 'Failed to create order';
                payStripeBtn.disabled = false;
                payStripeBtn.textContent = 'Pay with Card';
                return;
            }

            // Then, pay via Stripe
            const res = await fetch('{{ route("stripe.pay") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ order_id: orderData.order_id, payment_method_id: paymentMethod.id })
            });

            const data = await res.json();

            if (data.status === 'success') {
                window.location.href = `/checkout/success/${orderData.order_id}`;
            } else if (data.status === 'requires_action') {
                const { error: confirmError } = await stripe.confirmCardPayment(data.client_secret);
                if (!confirmError) {
                    window.location.href = `/checkout/success/${orderData.order_id}`;
                } else {
                    cardErrors.textContent = confirmError.message;
                }
            } else {
                cardErrors.textContent = data.message || 'Payment failed';
            }

            payStripeBtn.disabled = false;
            payStripeBtn.textContent = 'Pay with Card';
        });
    }

    // Prevent default form submission for Stripe
    form.addEventListener('submit', e => {
        if (stripeRadio.checked) e.preventDefault();
    });
});
</script>
@endpush
@endsection
