<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Product::active()->inRandomOrder()->take(8)->get();
        $newArrivals = Product::active()->latest('id')->take(8)->get();
        $categories = Category::active()->withCount('products')->orderBy('name')->take(10)->get();

        return view('home', compact('featured', 'newArrivals', 'categories'));
    }
}
