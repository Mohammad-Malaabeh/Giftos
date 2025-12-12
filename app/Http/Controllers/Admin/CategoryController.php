<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        // allow "0" to be a valid inactive filter
        $status = $request->filled('status') ? (int) $request->status : null;

        $categories = Category::query()
            ->withCount('products')
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%");
                });
            })
            ->when(!is_null($status), fn($qq) => $qq->where('status', $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'q', 'status'));
    }

    public function create()
    {
        $parents = Category::orderBy('name')->get(['id', 'name']);
        $category = new Category();
        return view('admin.categories.create', compact('parents', 'category'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['name']);
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function show(Category $category)
    {
        $category->load('parent', 'children');
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $parents = Category::where('id', '!=', $category->id)
            ->orderBy('name')
            ->get(['id', 'name']);
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        }

        // Ensure slug is never null
        if (!isset($data['slug']) || $data['slug'] === null) {
            $data['slug'] = $this->uniqueSlug($data['name'] ?? $category->name, $category->id);
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        // Reassign or nullify children
        Category::where('parent_id', $category->id)->update(['parent_id' => null]);

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    public function deleteLink(Category $category, Request $request)
    {
        // Optional: simple CSRF-ish confirmation phrase you can pass as a query param
        // $request->validate(['confirm' => 'required|in:yes']);

        // Reassign or nullify children (keep your existing logic)
        Category::where('parent_id', $category->id)->update(['parent_id' => null]);

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (
            Category::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
