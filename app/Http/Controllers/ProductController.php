<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Check if user can view any products
        $this->authorize('viewAny', Product::class);
        
        $query = Product::with('category');
        
        // Only show active products to non-admin users
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            $query->active();
        }
        
        $q = $request->input('q');
        $categoryId = $request->input('category_id');

        if ($q) {
            $query->search($q);
        }
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->latest()->paginate(12);
        $categories = Category::all();

        return view('product.index', compact('products', 'categories', 'q', 'categoryId'));
    }

    public function show(Product $product)
    {
        // Check if user can view this product
        $this->authorize('view', $product);

        // Increment views (optional, but good for popular products)
        $product->increment('views');

        // Load relations needed for the view
        $product->loadMissing(['category', 'variants.options', 'reviews.user']);

        // Fetch related products
        $related = Product::query()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id);
            
        // Only show active products to non-admin users
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            $related->active();
        }
            
        $related = $related->inRandomOrder()
            ->limit(4)
            ->get();

        // Fetch also bought products (example, adjust logic)
        $alsoBought = Product::query()
            ->where('id', '!=', $product->id);
            
        // Only show active products to non-admin users
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            $alsoBought->active();
        }
            
        $alsoBought = $alsoBought->inRandomOrder()
            ->limit(4)
            ->get();

        return view('product.show', compact('product', 'related', 'alsoBought'));
    }
    /**
     * Display products for a specific category (for web view).
     */
    public function category(Category $category, Request $request)
    {
        $query = $category->products()->active();
        // Add any filters for category products here
        $products = $query->paginate(12);

        $q = $request->input('q'); // For persistent search in filter
        $categories = Category::all(); // For filter sidebar

        return view('product.index', compact('products', 'category', 'categories', 'q'));
    }

    /**
     * Search products (for web view).
     */
    public function search(Request $request)
    {
        $q = $request->input('q');
        $categoryId = $request->input('category_id');

        $query = Product::with('category')->active();

        if ($q) {
            $query->search($q); // Assuming a search scope on Product model
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(12);
        $categories = Category::all();

        return view('product.index', compact('products', 'categories', 'q', 'categoryId'));
    }
}
