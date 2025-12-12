<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = Wishlist::with('product')->where('user_id', $request->user()->id)->latest()->get();
        return view('wishlist.index', compact('items'));
    }

    public function store(Request $request, Product $product)
    {
        Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);
        return back()->with('success', 'Saved to wishlist.');
    }

    public function destroy(Request $request, Product $product)
    {
        Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->delete();
        return back()->with('success', 'Removed from wishlist.');
    }

    /**
     * Add a product to wishlist
     */
    public function add(Request $request, Product $product)
    {
        Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);
        return back()->with('success', 'Saved to wishlist.');
    }

    /**
     * Remove a product from wishlist
     */
    public function remove(Request $request, Product $product)
    {
        Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->delete();
        return back()->with('success', 'Removed from wishlist.');
    }

    /**
     * Move wishlist item to cart
     */
    public function moveToCart(Request $request, Product $product)
    {
        // Remove from wishlist
        Wishlist::where('user_id', $request->user()->id)->where('product_id', $product->id)->delete();
        
        // Add to cart using CartService directly
        $cart = \App\Services\CartService::fromRequest();
        $cart->add($product, 1);
        
        return back()->with('success', 'Moved to cart.');
    }
}
