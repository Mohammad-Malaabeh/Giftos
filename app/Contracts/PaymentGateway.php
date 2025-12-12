<?php

namespace App\Contracts;

use App\Models\Order;

/**
 * Payment Gateway Interface
 * 
 * Defines the contract that all payment gateway implementations must follow.
 * This allows easy switching between payment providers (Stripe, PayPal, Square, etc.)
 */
interface PaymentGateway
{
    /**
     * Create a payment intent for an order
     * 
     * @param Order $order
     * @return array ['client_secret' => string, 'intent_id' => string]
     */
    public function createIntent(Order $order): array;

    /**
     * Charge a payment using the payment method
     * 
     * @param Order $order
     * @param array $paymentData Payment method details (token, card info, etc.)
     * @return array ['status' => string, 'transaction_id' => string]
     */
    public function charge(Order $order, array $paymentData): array;

    /**
     * Refund a payment
     * 
     * @param Order $order
     * @param float $amount Amount to refund (null for full refund)
     * @return array ['status' => string, 'refund_id' => string]
     */
    public function refund(Order $order, ?float $amount = null): array;

    /**
     * Get the name of the payment gateway
     * 
     * @return string
     */
    public function getName(): string;
}
