<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(): JsonResponse
    {
        // Publicly accessible

        $categories = Category::withCount('products')
            ->active() // Scope
            ->parents() // Scope: only top level
            ->with([
                'children' => function ($q) {
                    $q->active()->withCount('products');
                }
            ])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories
        ]);
    }

    /**
     * Display the specified category
     */
    public function show(Category $category): JsonResponse
    {
        // Check if active or admin
        $this->authorize('view', $category);

        $category->load([
            'children' => function ($q) {
                $q->active()->withCount('products');
            },
            'products' => function ($q) {
                $q->take(10); // Limit to prevent overload, though test has only 3
            }
        ]);

        return response()->json([
            'data' => new \App\Http\Resources\CategoryResource($category)
        ]);
    }

    /**
     * Get products in a category
     */
    public function products(Category $category, Request $request): JsonResponse
    {
        $this->authorize('view', $category);

        $products = $category->products()
            ->active()
            ->with(['category', 'variants']) // Optimized eager load
            ->withAverageRating();

        if ($request->has('featured')) {
            $products->featured();
        }

        if ($request->has('on_sale')) {
            $products->onSale();
        }

        if ($request->has('price_min') || $request->has('price_max')) {
            $products->priceRange($request->price_min, $request->price_max);
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);

        $paginatedProducts = $products->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginatedProducts->items(),
            'meta' => [
                'current_page' => $paginatedProducts->currentPage(),
                'per_page' => $paginatedProducts->perPage(),
                'total' => $paginatedProducts->total(),
                'last_page' => $paginatedProducts->lastPage(),
            ],
            'category' => $category
        ]);
    }

    /**
     * Store a newly created category (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:categories'],
            'slug' => ['required', 'string', 'max:255', 'unique:categories'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'name',
            'slug',
            'description',
            'image',
            'is_active',
            'parent_id'
        ]);

        // Map is_active to status if needed
        if (isset($data['is_active'])) {
            $data['status'] = $data['is_active'];
            unset($data['is_active']);
        }

        $category = Category::create($data);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Update the specified category (admin only)
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $validator = validator($request->all(), [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'description' => ['sometimes', 'nullable', 'string'],
            'image' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'name',
            'slug',
            'description',
            'image',
            'is_active',
            'parent_id'
        ]);

        if (isset($data['is_active'])) {
            $data['status'] = $data['is_active'];
            unset($data['is_active']);
        }

        $category->update($data);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified category (admin only)
     */
    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Restore a soft-deleted category (admin only)
     */
    public function restore(int $id): JsonResponse
    {
        $category = Category::onlyTrashed()->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found in trash'], 404);
        }

        $this->authorize('restore', $category);

        $category->restore();

        return response()->json([
            'message' => 'Category restored successfully',
            'data' => $category
        ]);
    }

    /**
     * Force delete a category (admin only)
     */
    public function forceDelete(int $id): JsonResponse
    {
        $category = Category::onlyTrashed()->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found in trash'], 404);
        }

        $this->authorize('forceDelete', $category);

        $category->forceDelete();

        return response()->json([
            'message' => 'Category permanently deleted'
        ]);
    }

    /**
     * Get trashed categories (admin only)
     */
    public function trashed(): JsonResponse
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::onlyTrashed()
            ->withCount('products')
            ->latest('deleted_at')
            ->get();

        return response()->json([
            'data' => $categories
        ]);
    }
}
