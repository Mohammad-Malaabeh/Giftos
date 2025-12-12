<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->string('q')->toString());
        $products = $categories = $orders = collect();

        if ($q !== '') {
            $products = Product::query()
                ->select(['id','title','sku'])
                ->where('title','like',"%{$q}%")
                ->orWhere('sku','like',"%{$q}%")
                ->orderBy('id','desc')->take(10)->get();

            $categories = Category::query()
                ->select(['id','name','slug'])
                ->where('name','like',"%{$q}%")
                ->orWhere('slug','like',"%{$q}%")
                ->orderBy('name')->take(10)->get();

            $orders = Order::query()
                ->select(['id','number','user_id','total','created_at'])
                ->where('number','like',"%{$q}%")
                ->orWhereHas('user', fn($uq)=>$uq->where('email','like',"%{$q}%"))
                ->orderBy('id','desc')->take(10)->get();
        }

        return view('admin.search.index', compact('q','products','categories','orders'));
    }
}