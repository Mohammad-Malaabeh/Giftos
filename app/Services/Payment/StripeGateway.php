<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

/**
 * Stripe Payment Gateway Implementation
 */
class StripeGateway implements PaymentGateway
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createIntent(Order $order): array
    {
        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount' => round($order->total * 100), // Convert to cents
                'currency' => config('app.currency', 'usd'),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->number,
                ],
            ]);

            return [
                'client_secret' => $intent->client_secret,
                'intent_id' => $intent->id,
            ];
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to create payment intent: ' . $e->getMessage());
        }
    }

    public function charge(Order $order, array $paymentData): array
    {
        try {
            $charge = $this->stripe->charges->create([
                'amount' => round($order->total * 100),
                'currency' => config('app.currency', 'usd'),
                'source' => $paymentData['token'] ?? null,
                'description' => "Order #{$order->number}",
                'metadata' => [
                    'order_id' => $order->id,
                ],
            ]);

            return [
                'status' => $charge->status === 'succeeded' ? 'success' : 'failed',
                'transaction_id' => $charge->id,
            ];
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Payment failed: ' . $e->getMessage());
        }
    }

    public function refund(Order $order, ?float $amount = null): array
    {
        try {
            $refundData = [
                'charge' => $order->transaction_id,
            ];

            if ($amount !== null) {
                $refundData['amount'] = round($amount * 100);
            }

            $refund = $this->stripe->refunds->create($refundData);

            return [
                'status' => $refund->status,
                'refund_id' => $refund->id,
            ];
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Refund failed: ' . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'stripe';
    }
}
