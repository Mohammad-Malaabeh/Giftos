<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    /**
     * Process checkout
     */
    public function process(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'shipping_address_id' => ['required', 'exists:user_addresses,id'],
            'payment_method' => ['required', 'string', 'in:stripe,square,cod'],
            'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],
            'save_card' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 422);
        }

        // Verify shipping address belongs to user
        $shippingAddress = UserAddress::where('id', $request->shipping_address_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$shippingAddress) {
            return response()->json([
                'message' => 'Invalid shipping address'
            ], 422);
        }

        // Check stock availability
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => "Product '{$item->product->title}' has insufficient stock"
                ], 422);
            }
        }

        // Calculate totals
        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->product->sale_price ?? $item->product->price);
        });

        $shipping = $this->calculateShipping($shippingAddress, $cartItems);
        $discount = 0;

        // Apply coupon if provided
        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('active', true)
                ->first();

            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                return response()->json([
                    'message' => 'Invalid or expired coupon'
                ], 422);
            }
        }

        $tax = ($subtotal - $discount) * 0.08; // 8% tax
        $total = $subtotal + $shipping + $tax - $discount;

        // Create order (this would typically be handled by OrderController)
        $order = $user->orders()->create([
            'shipping_address_id' => $request->shipping_address_id,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
        ]);

        // Create order items
        foreach ($cartItems as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
                'price' => $item->product->sale_price ?? $item->product->price,
            ]);

            // Update product stock
            $item->product->decrement('stock', $item->quantity);
        }

        // Clear cart
        $user->cartItems()->delete();

        // Process payment based on method
        $paymentResult = $this->processPayment($order, $request->payment_method);

        if (!$paymentResult['success']) {
            // If payment fails, restore stock and cancel order
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
            $order->update(['status' => 'canceled']);

            return response()->json([
                'message' => 'Payment failed',
                'error' => $paymentResult['error']
            ], 422);
        }

        return response()->json([
            'message' => 'Order placed successfully',
            'data' => [
                'order' => $order->load(['items.product']),
                'payment_id' => $paymentResult['payment_id'] ?? null,
            ]
        ], 201);
    }

    /**
     * Get available shipping methods
     */
    public function shippingMethods(Request $request): JsonResponse
    {
        $user = $request->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 422);
        }

        $methods = [
            [
                'id' => 'standard',
                'name' => 'Standard Shipping',
                'description' => '5-7 business days',
                'cost' => 10.00,
                'estimated_days' => 5,
            ],
            [
                'id' => 'express',
                'name' => 'Express Shipping',
                'description' => '2-3 business days',
                'cost' => 25.00,
                'estimated_days' => 2,
            ],
            [
                'id' => 'overnight',
                'name' => 'Overnight Shipping',
                'description' => 'Next business day',
                'cost' => 50.00,
                'estimated_days' => 1,
            ],
        ];

        return response()->json([
            'data' => $methods
        ]);
    }

    /**
     * Get available payment methods
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        $methods = [
            [
                'id' => 'stripe',
                'name' => 'Credit/Debit Card',
                'type' => 'card',
                'description' => 'Pay securely with Stripe',
                'icon' => 'credit-card',
            ],
            [
                'id' => 'square',
                'name' => 'Square Payments',
                'type' => 'card',
                'description' => 'Pay with Square',
                'icon' => 'credit-card',
            ],
            [
                'id' => 'cod',
                'name' => 'Cash on Delivery',
                'type' => 'cash',
                'description' => 'Pay when you receive',
                'icon' => 'cash',
            ],
        ];

        return response()->json([
            'data' => $methods
        ]);
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'code' => ['required', 'string', 'exists:coupons,code'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 422);
        }

        $coupon = Coupon::where('code', $request->code)
            ->where('active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'message' => 'Invalid coupon code'
            ], 404);
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->product->sale_price ?? $item->product->price);
        });

        if (!$coupon->isValid($subtotal)) {
            return response()->json([
                'message' => 'Coupon is not valid for this order'
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'message' => 'Coupon applied successfully',
            'data' => [
                'coupon' => [
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'discount_amount' => $discount,
                ],
                'discount' => $discount,
                'subtotal' => $subtotal,
                'total_after_discount' => $subtotal - $discount,
            ]
        ]);
    }

    /**
     * Remove coupon
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        // In a real implementation, you would remove the coupon from the session
        // or update the order to remove the discount

        return response()->json([
            'message' => 'Coupon removed successfully'
        ]);
    }

    /**
     * Calculate shipping cost
     */
    private function calculateShipping(UserAddress $address, $cartItems): float
    {
        $weight = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->product->weight ?? 1);
        });

        // Simple shipping calculation based on weight and location
        $baseCost = 10.00;
        $weightCost = max(0, $weight - 5) * 2; // $2 per kg over 5kg

        // Add extra cost for remote areas (simplified)
        $locationCost = 0;
        if (in_array($address->state, ['AK', 'HI', 'PR'])) {
            $locationCost = 15.00;
        }

        return $baseCost + $weightCost + $locationCost;
    }

    /**
     * Process payment
     */
    private function processPayment($order, string $paymentMethod): array
    {
        // This would integrate with actual payment gateways
        // For now, we'll simulate the process

        if ($paymentMethod === 'cod') {
            return ['success' => true];
        }

        // Simulate payment processing
        if (rand(1, 100) <= 95) { // 95% success rate
            return [
                'success' => true,
                'payment_id' => 'pay_' . uniqid(),
                'transaction_id' => 'txn_' . uniqid(),
            ];
        }

        return [
            'success' => false,
            'error' => 'Payment processing failed',
        ];
    }
}
