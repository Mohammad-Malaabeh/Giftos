<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Variant;
use App\Models\User;
use App\Events\OrderPlaced;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Create Order Action
 * 
 * Encapsulates complex order creation logic in a single, testable action class.
 */
class CreateOrderAction
{
    /**
     * Execute the order creation
     * 
     * @param User|null $user
     * @param array $orderData
     * @param array $items
     * @return Order
     * @throws \RuntimeException
     */
    public function execute(?User $user, array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($user, $orderData, $items) {
            // Create the order
            $order = Order::create([
                'user_id' => $user?->id,
                'number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'payment_method' => $orderData['payment_method'],
                'payment_status' => 'unpaid',
                'billing_address' => $orderData['billing_address'],
                'shipping_address' => $orderData['shipping_address'],
                'subtotal' => 0, // Will be calculated
                'discount' => $orderData['discount'] ?? 0,
                'shipping' => $orderData['shipping'] ?? 0,
                'tax' => $orderData['tax'] ?? 0,
                'total' => 0, // Will be calculated
                'coupon_code' => $orderData['coupon_code'] ?? null,
            ]);

            // Create order items and decrement stock
            foreach ($items as $itemData) {
                $product = Product::where('id', $itemData['product_id'])->lockForUpdate()->first();

                if (!$product) {
                    throw new \RuntimeException("Product not found: {$itemData['product_id']}");
                }

                $variant = isset($itemData['variant_id'])
                    ? Variant::where('id', $itemData['variant_id'])
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first()
                    : null;

                $stockSource = $variant ?? $product;

                // Check stock availability
                if ($stockSource->stock < $itemData['quantity'] && !$stockSource->backorder_allowed) {
                    throw new \RuntimeException("Not enough stock for {$product->title}");
                }

                // Decrement stock
                $stockSource->decrement('stock', $itemData['quantity']);

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'title' => $product->title,
                    'sku' => $variant?->sku ?? $product->sku,
                    'image_path' => $product->image_path,
                    'variant_options' => $variant?->options,
                    'unit_price' => $product->sale_price ?? $product->price,
                    'quantity' => $itemData['quantity'],
                    'total' => round(($product->sale_price ?? $product->price) * $itemData['quantity'], 2),
                ]);
            }

            // Recalculate totals
            $order->recalcTotals();

            // Fire event
            event(new OrderPlaced($order));

            return $order->fresh();
        });
    }

    /**
     * Generate a unique order number
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . strtoupper(Str::random(10));
        } while (Order::where('number', $number)->exists());

        return $number;
    }
}
