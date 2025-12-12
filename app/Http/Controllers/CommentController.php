<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        $comment = new Comment();
        $comment->content = $request->content;
        $comment->user_id = Auth::id();

        // Save polymorphic relationship
        $product->comments()->save($comment);

        // Auto-approve
        $comment->approve();

        return back()->with('success', 'Comment posted successfully!');
    }
}
