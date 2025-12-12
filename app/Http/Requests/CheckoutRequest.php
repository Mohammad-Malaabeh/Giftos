<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipping.name' => ['required', 'string', 'max:255'],
            'shipping.line1' => ['required', 'string', 'max:255'],
            'shipping.city' => ['required', 'string', 'max:255'],
            'shipping.country' => ['required', 'string', 'max:2'],
            'shipping.zip' => ['required', 'string', 'max:20'],

            'billing_same' => ['nullable', 'boolean'],

            'billing.name' => ['nullable', 'string', 'max:255', 'required_without:billing_same'],
            'billing.line1' => ['nullable', 'string', 'max:255', 'required_without:billing_same'],
            'billing.city' => ['nullable', 'string', 'max:255', 'required_without:billing_same'],
            'billing.country' => ['nullable', 'string', 'max:2', 'required_without:billing_same'],
            'billing.zip' => ['nullable', 'string', 'max:20', 'required_without:billing_same'],

            'payment_method' => ['required', 'in:cod,stripe'],
        ];
    }

    public function prepareForValidation(): void
    {
        // Normalize shipping_address to shipping for backward compatibility
        if ($this->has('shipping_address') && !$this->has('shipping')) {
            $this->merge([
                'shipping' => $this->input('shipping_address'),
            ]);
        }

        // Normalize billing_address to billing
        if ($this->has('billing_address') && !$this->has('billing')) {
            $this->merge([
                'billing' => $this->input('billing_address'),
            ]);
        }

        $this->merge([
            'billing_same' => (bool) $this->boolean('billing_same', true), // Default to true if not provided
        ]);
    }
}
