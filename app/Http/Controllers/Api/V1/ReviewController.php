<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    /**
     * Display reviews for a product
     */
    public function index(Product $product, Request $request): JsonResponse
    {
        //Publicly accessible, but maybe filter approved?
        // Policy might say viewAny is public, but we usually only show approved reviews to public.

        $reviews = $product->reviews()
            ->approved() // Scope
            ->with('user')
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
                'average_rating' => (float) $product->reviews()->approved()->avg('rating'),
                'total_reviews' => $product->reviews()->approved()->count(),
            ]
        ]);
    }

    /**
     * Store a new review
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Review::class);

        $validator = validator($request->all(), [
            'product_id' => ['required', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);

        // Check if user has already reviewed this product
        $existingReview = $product->reviews()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this product'
            ], 422);
        }

        $review = $product->reviews()->create([
            'user_id' => $request->user()->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'approved' => true,
        ]);

        return response()->json([
            'message' => 'Review created successfully',
            'data' => $review->load('user')
        ], 201);
    }

    /**
     * Update a review
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        $validator = validator($request->all(), [
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'comment' => ['sometimes', 'string', 'min:10', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->only(['rating', 'comment']));

        return response()->json([
            'message' => 'Review updated successfully',
            'data' => $review->load('user')
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy(Request $request, Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Approve a review (admin only)
     */
    public function approve(Review $review): JsonResponse
    {
        $this->authorize('approve', $review); //Custom ability

        $review->update(['approved' => true]);

        return response()->json([
            'message' => 'Review approved',
            'data' => $review->load('user')
        ]);
    }

    /**
     * Reject a review (admin only)
     */
    public function reject(Review $review): JsonResponse
    {
        $this->authorize('approve', $review); // Re-use approve ability or create 'reject'

        $review->update(['approved' => false]);

        return response()->json([
            'message' => 'Review rejected',
            'data' => $review->load('user')
        ]);
    }
}
