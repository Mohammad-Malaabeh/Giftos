<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Http\Requests\Api\V1\Product\ProductSearchRequest;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function index(Request $request): ProductCollection
    {
        $products = $this->productRepository
            ->with(['category', 'variants', 'tags'])
            ->withAverageRating()
            ->withCount('variants')
            ->active();

        if ($request->has('search')) {
            $products = $products->search($request->search);
        }

        if ($request->has('category')) {
            $products = $products->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('category_id')) {
            $products = $products->where('category_id', $request->category_id);
        }

        if ($request->boolean('featured')) {
            $products = $products->featured();
        }

        if ($request->boolean('on_sale')) {
            $products = $products->onSale();
        }

        if ($request->has('price_min') || $request->has('price_max')) {
            $products = $products->priceRange($request->price_min, $request->price_max);
        }

        if ($request->has('tags')) {
            $tags = explode(',', $request->tags);
            $products = $products->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }

        // Apply sorting
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        match ($sort) {
            'price' => $products = $products->orderBy('price', $order),
            'name' => $products = $products->orderBy('title', $order),
            'rating' => $products = $products->orderBy('avg_rating', $order),
            'popularity' => $products = $products->orderBy('views', $order),
            default => $products = $products->latest(),
        };

        $perPage = min($request->get('per_page', 15), 50); // Max 50 per page
        $paginatedProducts = $products->paginate($perPage);

        return new ProductCollection($paginatedProducts);
    }

    public function show(string $slug): ProductResource|JsonResponse
    {
        $product = $this->productRepository
            ->with(['category', 'variants.options', 'reviews.user', 'media', 'tags'])
            ->withAverageRating()
            ->withCount('reviews')
            ->findBySlug($slug);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->authorize('view', $product);
        $this->productRepository->incrementViews($product->id);

        return new ProductResource($product);
    }

    public function search(ProductSearchRequest $request): ProductCollection
    {
        $products = $this->productRepository
            ->search($request->validated('q'))
            ->with(['category', 'variants', 'tags'])
            ->withAverageRating()
            ->active()
            ->paginate(20);

        return new ProductCollection($products);
    }

    public function featured(Request $request): ProductCollection
    {
        $limit = min($request->get('limit', 8), 50);

        $products = $this->productRepository
            ->featured()
            ->active()
            ->with(['category', 'variants', 'media'])
            ->withAverageRating()
            ->take($limit)
            ->get();

        // Convert to paginated collection for consistency
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $products,
            $products->count(),
            $limit,
            1
        );

        return new ProductCollection($paginated);
    }

    public function latest(Request $request): ProductCollection
    {
        $limit = min($request->get('limit', 12), 50);

        $products = $this->productRepository
            ->latest()
            ->active()
            ->with(['category', 'variants', 'media'])
            ->withAverageRating()
            ->take($limit)
            ->get();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $products,
            $products->count(),
            $limit,
            1
        );

        return new ProductCollection($paginated);
    }

    public function onSale(Request $request): ProductCollection
    {
        $products = $this->productRepository
            ->onSale()
            ->active()
            ->with(['category', 'variants', 'media'])
            ->withAverageRating()
            ->paginate(20);

        return new ProductCollection($products);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        $this->authorize('create', \App\Models\Product::class);

        $product = $this->productRepository->create($request->validated());

        // Handle tags if provided
        if ($request->has('tags')) {
            $tags = collect($request->tags)->map(function ($tag) {
                return \App\Models\Tag::findOrCreateByName($tag);
            });
            $product->tags()->attach($tags->pluck('id'));
        }

        return new ProductResource(
            $product->load(['category', 'variants', 'media', 'tags'])
        );
    }

    public function update(UpdateProductRequest $request, int $id): ProductResource|JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->authorize('update', $product);

        $product = $this->productRepository->update($id, $request->validated());

        // Update tags if provided
        if ($request->has('tags')) {
            $tags = collect($request->tags)->map(function ($tag) {
                return \App\Models\Tag::findOrCreateByName($tag);
            });
            $product->tags()->sync($tags->pluck('id'));
        }

        return new ProductResource(
            $product->load(['category', 'variants', 'media', 'tags'])
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->authorize('delete', $product);

        $this->productRepository->delete($id);

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
