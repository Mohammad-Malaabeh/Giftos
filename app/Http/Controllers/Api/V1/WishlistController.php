<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\CartItemResource;

class WishlistController extends Controller
{
    /**
     * Display user's wishlist
     */
    public function index(Request $request): JsonResponse
    {
        // Clean up orphaned wishlist items (products deleted)
        $request->user()->wishlist()->whereDoesntHave('product')->delete();

        $query = $request->user()
            ->wishlist()
            ->with(['product.category', 'product']);

        // Filters: category, search, sort, pagination
        if ($request->filled('category')) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $request->category));
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('product', fn($q) => $q->where(fn($qq) => $qq->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")));
        }

        if ($request->filled('sort')) {
            $direction = $request->get('order', 'desc');
            if ($request->sort === 'price') {
                $query->orderBy(Product::select('price')->whereColumn('products.id', 'wishlists.product_id'), $direction);
            } elseif ($request->sort === 'created_at') {
                $query->orderBy('wishlists.created_at', $direction)->orderBy('wishlists.id', $direction);
            } else {
                $query->orderBy($request->sort, $direction);
            }
        }

        if (!$request->filled('sort')) {
            $query->latest('wishlists.created_at');
        }

        $perPage = (int) $request->get('per_page', 15);
        $items = $query->paginate($perPage)->appends($request->query());

        if ($request->filled('sort') && $request->sort === 'created_at') {
            $ids = collect($items->items())->pluck('id')->toArray();
            Log::debug('Wishlist sort order (created_at): ' . implode(',', $ids));
            try {
                Log::debug('Wishlist SQL: ' . $query->toSql() . ' | bindings: ' . json_encode($query->getBindings()));
                Log::debug('Wishlist orders: ' . json_encode($query->getQuery()->orders));
            } catch (\Throwable $e) {
                Log::debug('Wishlist SQL debug failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
            ]
        ]);
    }

    /**
     * Add product to wishlist
     */
    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('products', 'id')->where(fn($q) => $q->where('status', true))
            ],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $existing = $request->user()->wishlist()->where('product_id', $product->id)->first();
        if ($existing) {
            return response()->json(['message' => 'Product already in wishlist'], 422);
        }

        $item = $request->user()->wishlist()->create(['product_id' => $product->id]);

        return response()->json([
            'data' => $item->load('product')
        ], 201);
    }

    /**
     * Remove product from wishlist
     */
    public function remove(Request $request, Product $product): JsonResponse
    {
        $wishlistItem = $request->user()->wishlist()->where('product_id', $product->id)->first();

        if (!$wishlistItem) {
            return response()->json(['message' => 'Product not found in wishlist'], 404);
        }

        $wishlistItem->delete();

        return response()->json(['message' => 'Product removed from wishlist']);
    }

    /**
     * Move product from wishlist to cart
     */
    public function moveToCart(Request $request, Product $product): JsonResponse
    {
        $wishlistItem = $request->user()
            ->wishlist()
            ->where('product_id', $product->id)
            ->first();

        if (!$wishlistItem) {
            return response()->json([
                'message' => 'Product not found in wishlist'
            ], 404);
        }

        // Check stock
        if ($product->stock < 1) {
            return response()->json([
                'message' => 'Product is out of stock'
            ], 422);
        }

        // Add to cart (atomic increment or create)
        $cartItem = null;
        DB::transaction(function () use ($request, $product, &$cartItem) {
            $cartItem = $request->user()->cartItems()
                ->where('product_id', $product->id)
                ->whereNull('variant_id')
                ->lockForUpdate()
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity');
                $cartItem->refresh();
            } else {
                $cartItem = $request->user()->cartItems()->create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'quantity' => 1,
                    'unit_price' => $product->price,
                ]);
            }

            // Ensure we don't exceed stock
            if ($cartItem->quantity > $product->stock) {
                $cartItem->quantity = $product->stock;
                $cartItem->save();
            }
        });

        // Remove from wishlist
        $wishlistItem->delete();

        return response()->json([
            'message' => 'Product moved to cart successfully',
            'data' => ['moved' => true]
        ]);
    }

    /**
     * Get wishlist count
     */
    public function count(Request $request): JsonResponse
    {
        $count = $request->user()->wishlist()->count();
    }

    public function bulk(Request $request): JsonResponse
    {
        $data = $request->validate(['product_ids' => 'required|array']);
        $ids = $data['product_ids'];
        $removed = $request->user()->wishlist()->whereIn('product_id', $ids)->delete();
        return response()->json(['message' => 'Products removed from wishlist', 'data' => ['removed_count' => $removed]]);
    }

    public function stats(Request $request): JsonResponse
    {
        $total = $request->user()->wishlist()->count();
        $totalValue = (float) $request->user()->wishlist()->with('product')->get()->sum(fn($i) => $i->product?->price ?? 0.0);
        $categories = $request->user()->wishlist()->with('product.category')->get()->pluck('product.category.id')->unique()->filter()->values()->all();
        $recent = $request->user()->wishlist()->latest('wishlists.created_at')->limit(5)->get()->map(fn($i) => ['id' => $i->id, 'product_id' => $i->product_id])->toArray();

        return response()->json(['data' => ['total_items' => $total, 'total_value' => $totalValue, 'categories_count' => $categories, 'recently_added' => $recent]]);
    }
}
