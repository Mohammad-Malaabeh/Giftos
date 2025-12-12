<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Order;

class StripeController extends Controller
{
    public function pay(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'payment_method_id' => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::create([
                'amount' => intval($order->total * 100),
                'currency' => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirm' => true,
                'description' => "Order #{$order->number}",
                'metadata' => ['order_id' => $order->id],
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);

            if ($intent->status === 'succeeded') {
                $order->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'stripe',
                    'transaction_id' => $intent->id,
                    'status' => 'paid',
                ]);

                return response()->json(['status' => 'success']);
            }

            return response()->json([
                'status' => 'requires_action',
                'client_secret' => $intent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a Stripe PaymentIntent and return client secret.
     */
    public function intent(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $order = Order::where('number', $request->order_number)->firstOrFail();

        // Ensure authenticated user owns the order when auth is present
        if (auth()->check() && auth()->id() !== $order->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::create([
                'amount' => intval($order->total * 100),
                'currency' => 'usd',
                'metadata' => ['order_number' => $order->number],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return response()->json(['clientSecret' => $intent->client_secret]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Stripe intent create failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error creating payment intent'], 500);
        }
    }

    /**
     * Handle incoming Stripe webhooks.
     * Verifies signature using `services.stripe.webhook_secret` and updates order status.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['message' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            // Generic error
            \Illuminate\Support\Facades\Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['message' => 'Webhook error'], 500);
        }

        $object = $event->data->object ?? null;

        // Extract possible metadata keys for order lookup
        $metadata = $object->metadata ?? null;
        $order = null;

        try {
            if ($metadata) {
                $orderId = $metadata->order_id ?? null;
                $orderNumber = $metadata->order_number ?? null;

                if ($orderId) {
                    $order = Order::find($orderId);
                } elseif ($orderNumber) {
                    $order = Order::where('number', $orderNumber)->first();
                }
            }

            if ($order) {
                // Handle common successful payment events
                if (in_array($event->type, ['payment_intent.succeeded', 'charge.succeeded'])) {
                    $transactionId = $object->id ?? $object->payment_intent ?? null;
                    // markPaid will set payment_status, transaction_id, paid_at and status
                    $order->markPaid($transactionId);
                    $order->payment_method = 'stripe';
                    $order->save();
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Stripe webhook processing failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Webhook processing failed'], 500);
        }

        return response()->json(['message' => 'Webhook processed'], 200);
    }
}
