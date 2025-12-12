<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Coupon;
use App\Services\CartService;
use App\Events\OrderPlaced;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = CartService::fromRequest();
        $items = $cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $totals = $cart->totals(session('coupon'));
        $defaultShipping = auth()->user()?->defaultShippingAddress;
        $defaultBilling = auth()->user()?->defaultBillingAddress ?? $defaultShipping;

        return view('checkout.create', compact('items', 'totals', 'defaultShipping', 'defaultBilling'));
    }

    public function create()
    {
        return $this->index();
    }

    public function store(CheckoutRequest $request)
    {
        $cart = CartService::fromRequest();
        $items = $cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $totals = $cart->totals(session('coupon'));
        $data = $request->validated();

        $shipping = $data['shipping'];
        $billing = $data['billing_same'] ? $shipping : ($data['billing'] ?? $shipping);

        $order = DB::transaction(function () use ($items, $totals, $data, $shipping, $billing) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'shipping' => $totals['shipping'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'billing_address' => $billing,
                'shipping_address' => $shipping,
            ]);

            Log::info('Order created', ['id' => $order->id]);

            $order->coupon_code = session('coupon');
            $order->save();

            foreach ($items as $ci) {
                $product = Product::where('id', $ci->product_id)->lockForUpdate()->first();
                $variant = $ci->variant_id
                    ? Variant::where('id', $ci->variant_id)->where('product_id', $ci->product_id)->lockForUpdate()->first()
                    : null;

                if (!$product)
                    continue;

                $stockSource = $variant ?? $product;
                if ($stockSource->stock < $ci->quantity && !$stockSource->backorder_allowed) {
                    throw new RuntimeException("Not enough stock for " . ($variant?->sku ?? $product->sku ?? $product->title));
                }

                $stockSource->decrement('stock', $ci->quantity);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'title' => $product->title,
                    'sku' => $variant?->sku ?? $product->sku,
                    'image_path' => $product->image_path,
                    'variant_options' => $variant?->options,
                    'unit_price' => $ci->unit_price,
                    'quantity' => $ci->quantity,
                    'total' => round($ci->unit_price * $ci->quantity, 2),
                ]);
            }

            $order->recalcTotals();

            return $order;
        });

        // COD payment
        if ($data['payment_method'] === 'cod') {
            if (session('coupon')) {
                $coupon = Coupon::where('code', session('coupon'))->first();
                if ($coupon)
                    $coupon->increment('used_count');
            }

            $cart->clear();
            event(new OrderPlaced($order));

            return redirect()->route('checkout.success', $order)->with('success', 'Order placed successfully.');
        }

        // Stripe payment
        if ($data['payment_method'] === 'stripe') {
            return response()->json([
                'status' => 'pending',
                'order_id' => $order->id,
                'total' => $order->total,
            ]);
        }

        return redirect()->route('checkout.success', $order)->with('success', 'Order placed successfully.');
    }

    public function success(Order $order)
    {
        // Allow access if: user owns the order OR order belongs to a guest (user_id is null) and user is not authenticated
        if (auth()->check()) {
            abort_unless($order->user_id === auth()->id(), 403);
        } else {
            // Guest users can only access orders that belong to guests (user_id is null)
            abort_unless($order->user_id === null, 403);
        }

        if ($order->payment_method === 'stripe' && $order->payment_status === 'paid') {
            $cart = CartService::fromRequest();
            $cart->clear();
            session()->forget('coupon');
        }

        return view('checkout.success', compact('order'));
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . strtoupper(Str::random(10));
        } while (Order::where('number', $number)->exists());

        return $number;
    }

    /**
     * Display Stripe payment page
     */
    public function stripe(Order $order)
    {
        $cartTotals = [
            'subtotal' => (float) $order->subtotal,
            'total' => (float) $order->total,
            'tax' => (float) $order->tax,
            'shipping' => (float) $order->shipping,
            'discount' => (float) $order->discount,
        ];

        return view('checkout.stripe', ['order' => $order, 'totals' => $cartTotals]);
    }
}
