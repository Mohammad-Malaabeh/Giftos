<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Jobs\GenerateImageVariants;
use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->filled('status') ? (int) $request->status : null;
        $categoryId = $request->integer('category_id');

        $lowStock = $request->boolean('low_stock');
        $products = Product::query()
            ->when($lowStock, fn($q) => $q->where('stock', '<=', 5))
            ->with('category')
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->when(!is_null($status), fn($qq) => $qq->where('status', $status))
            ->when($categoryId, fn($qq) => $qq->where('category_id', $categoryId))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'categories', 'q', 'status', 'categoryId'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $product = new Product();
        return view('admin.products.create', compact('categories', 'product'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        // Slug
        if (empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['title']);
        }

        // Main image
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
            // NEW: optimize + create webp
            $this->optimizeImageAndCreateWebp($data['image_path']);


            dispatch(new GenerateImageVariants($data['image_path']))->onQueue('media');
        }

        // Gallery
        $gallery = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('products', 'public');
                // NEW: optimize + create webp
                $this->optimizeImageAndCreateWebp($path);
                dispatch(new GenerateImageVariants($path))->onQueue('media');
                $gallery[] = $path;
            }
        }
        $data['gallery'] = $gallery ?: null;

        $product = Product::create($data);
        Activity::log('product.created', $product, ['title' => $product->title]);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // Slug
        if (empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['title'], $product->id);
        }

        // Main image replace
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
                // Optionally also delete webp sibling of old image
                $oldWebp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $product->image_path);
                if ($oldWebp) Storage::disk('public')->delete($oldWebp);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
            // NEW: optimize + create webp
            $this->optimizeImageAndCreateWebp($data['image_path']);

            dispatch(new GenerateImageVariants($data['image_path']))->onQueue('media');
        }

        // Gallery additions
        $gallery = $product->gallery ?? [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('products', 'public');
                // NEW: optimize + create webp
                $this->optimizeImageAndCreateWebp($path);

                dispatch(new GenerateImageVariants($path))->onQueue('media');

                $gallery[] = $path;
            }
        }

        // Gallery removals
        $remove = $request->input('remove_gallery', []);
        if (!empty($remove)) {
            foreach ($remove as $path) {
                if (in_array($path, $gallery, true)) {
                    Storage::disk('public')->delete($path);
                    // Also delete webp sibling
                    $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
                    if ($webp) Storage::disk('public')->delete($webp);
                    $gallery = array_values(array_diff($gallery, [$path]));
                }
            }
        }

        $data['gallery'] = $gallery ?: null;

        $product->update($data);

        // Handle variants
        $removeIds = array_filter(explode(',', (string)$request->input('remove_variants', '')));
        if (!empty($removeIds)) {
            Variant::where('product_id', $product->id)
                ->whereIn('id', $removeIds)
                ->delete();
        }

        if ($request->has('variants')) {
            foreach ($request->input('variants') as $key => $payload) {
                $payload = array_merge([
                    'sku' => null,
                    'price' => null,
                    'sale_price' => null,
                    'stock' => 0,
                    'backorder_allowed' => false,
                    'backorder_eta' => null,
                    'options' => null,
                    'status' => true,
                ], $payload);

                // Normalize types
                $payload['backorder_allowed'] = (bool) $payload['backorder_allowed'];
                $payload['status'] = (bool) $payload['status'];
                $payload['options'] = $this->normalizeOptionsJson($payload['options']);

                // Skip empty variants
                $isEmpty =
                    (empty($payload['sku']) || trim((string)$payload['sku']) === '') &&
                    (is_null($payload['price']) || $payload['price'] === '') &&
                    (is_null($payload['sale_price']) || $payload['sale_price'] === '') &&
                    ((int) $payload['stock'] === 0) &&
                    (empty($payload['options']) || (is_array($payload['options']) && count($payload['options']) === 0));

                if ($isEmpty) {
                    // If it's an existing variant key, we don't touch it; if it's new_, we ignore creation
                    if (!str_starts_with($key, 'new_')) {
                        // no-op: leave existing as-is when "empty" payload
                    }
                    continue;
                }

                if (str_starts_with($key, 'new_')) {
                    Variant::create(array_merge($payload, ['product_id' => $product->id]));
                } else {
                    $variant = Variant::where('id', $key)->where('product_id', $product->id)->first();
                    if ($variant) $variant->update($payload);
                }
            }
        }
        Activity::log('product.updated', $product, ['dirty' => array_keys($product->getChanges())]);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete images
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $oldWebp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $product->image_path);
            if ($oldWebp) Storage::disk('public')->delete($oldWebp);
        }
        if (is_array($product->gallery)) {
            foreach ($product->gallery as $path) {
                Storage::disk('public')->delete($path);
                $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
                if ($webp) Storage::disk('public')->delete($webp);
            }
        }

        $product->delete();

        Activity::log('product.deleted', $product, ['title' => $product->title]);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    public function deleteLink(Product $product)
    {
        // Delete images (main + webp sibling)
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $oldWebp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $product->image_path);
            if ($oldWebp) Storage::disk('public')->delete($oldWebp);
        }
        if (is_array($product->gallery)) {
            foreach ($product->gallery as $path) {
                Storage::disk('public')->delete($path);
                $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
                if ($webp) Storage::disk('public')->delete($webp);
            }
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (
            Product::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', 'in:activate,deactivate'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);
        $status = $data['action'] === 'activate';
        Product::whereIn('id', $data['ids'])->update(['status' => $status]);
        return back()->with('success', 'Products updated.');
    }


    /**
     * Optimize an image file in public storage and create a .webp sibling.
     * Returns the stored relative path (unchanged) for DB, plus ensures webp exists.
     */
    private function optimizeImageAndCreateWebp(string $relativePath): void
    {
        $full = storage_path('app/public/' . ltrim($relativePath, '/'));
        if (!is_file($full)) {
            return;
        }

        try {
            // Resize to max width (e.g., 1200), keep aspect ratio; compress to ~85 quality
            $img = Image::make($full)->orientate();
            if ($img->width() > 1200) {
                $img->resize(1200, null, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });
            }
            $img->save($full, 85); // overwrite JPEG/PNG with compressed version

            // Create webp next to it
            $webpFull = preg_replace('/\.(jpe?g|png)$/i', '.webp', $full);
            if ($webpFull && $webpFull !== $full) {
                $img->encode('webp', 80)->save($webpFull);
            }
        } catch (Throwable $e) {
            // silently ignore, or log if you prefer
            // \Log::warning('Image optimize failed: '.$e->getMessage());
        }
    }

    private function normalizeOptionsJson($val): ?array
    {
        if (!$val) return null;
        if (is_array($val)) return $val;
        try {
            $arr = json_decode((string)$val, true, 512, JSON_THROW_ON_ERROR);
            return is_array($arr) ? $arr : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}
