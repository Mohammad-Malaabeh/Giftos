<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Variant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = CartService::fromRequest();
        $items = $cart->items();
        $totals = $cart->totals();

        return view('cart.index', compact('items', 'totals'));
    }



    public function update(Request $request, CartItem $cartItem)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Optional stock check
        if ($cartItem->product && $cartItem->product->stock < $data['quantity']) {
            return back()->withErrors(['quantity' => 'Not enough stock.']);
        }

        $cart = CartService::fromRequest();
        $cart->updateQuantity($cartItem, $data['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem)
    {
        $cart = CartService::fromRequest();
        $cart->remove($cartItem);

        return back()->with('success', 'Item removed.');
    }

    public function clear()
    {
        $cart = CartService::fromRequest();
        $cart->clear();

        return back()->with('success', 'Cart cleared.');
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $qty = $data['quantity'] ?? 1;
        $product = Product::findOrFail($data['product_id']);
        $variant = null;
        if (!empty($data['variant_id'])) {
            $variant = Variant::where('id', $data['variant_id'])
                ->where('product_id', $product->id)
                ->firstOrFail();
        }

        // Stock checks
        $stockSource = $variant ?? $product;
        if ($stockSource->stock < $qty && !$stockSource->backorder_allowed) {
            return back()->withErrors(['quantity' => 'Not enough stock for selected item.']);
        }

        return $this->performAdd($product, $variant, $qty);
    }

    /**
     * Shared helper to perform add operation to the cart.
     */
    protected function performAdd(Product $product, ?Variant $variant, int $qty)
    {
        $cart = CartService::fromRequest();
        if ($variant) {
            $cart->addVariant($product, $variant, $qty);
            return redirect()->route('cart.index')->with('success', 'Variant added to cart.');
        }

        $cart->add($product, $qty);
        return redirect()->route('cart.index')->with('success', 'Added to cart.');
    }



    /**
     * Apply coupon code
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:255']
        ]);

        $code = strtoupper(trim($request->input('code')));

        if (session('coupon') === $code) {
            return back()->with('info', 'Coupon already applied.');
        }

        // Find valid coupon
        $coupon = \App\Models\Coupon::byCode($code)->valid()->available()->first();

        if (!$coupon) {
            return back()->with('error', 'Invalid or expired coupon code.');
        }

        // Store coupon in session
        session(['coupon' => $code]);

        return back()->with('success', 'Coupon applied successfully!');
    }

    /**
     * Remove coupon
     */
    public function removeCoupon()
    {
        session()->forget('coupon');

        return back()->with('success', 'Coupon removed.');
    }

    /**
     * Estimate shipping cost
     */
    public function estimateShipping(Request $request)
    {
        $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        // Store shipping info in session
        session([
            'checkout_country' => $request->input('country'),
            'checkout_postal_code' => $request->input('postal_code'),
        ]);

        // For now, just return a success message
        // In a real implementation, you would calculate shipping based on the country/postal code
        return back()->with('success', 'Shipping estimated successfully.');
    }
}
