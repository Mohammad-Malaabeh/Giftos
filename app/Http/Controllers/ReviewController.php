<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'approved' => true
        ]);

        return redirect()->route('product.show', $product->slug)
            ->with('success', 'Review submitted successfully!');
    }

    public function edit(Review $review)
    {
        $this->authorize('update', $review);
        
        return view('reviews.edit', [
            'review' => $review,
            'product' => $review->product
        ]);
    }

    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);
        
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $review->update($data);

        return redirect()->route('product.show', $review->product->slug)
            ->with('success', 'Review updated successfully!');
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);
        
        $productSlug = $review->product->slug;
        $review->delete();

        return redirect()->route('product.show', $productSlug)
            ->with('success', 'Review deleted successfully!');
    }
}
