<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Coupon;
use App\Services\Activity;

class TrashController extends Controller
{
    /**
     * Display all trashed products.
     */
    public function products()
    {
        $products = Product::onlyTrashed()
            ->with(['category']) // Eager load to prevent N+1
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.trash.products', [
            'products' => $products,
        ]);
    }

    /**
     * Restore a trashed product.
     */
    public function restoreProduct(int $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        Activity::log('product.restored', $product);

        return back()->with('success', 'Product restored successfully.');
    }

    /**
     * Permanently delete a product.
     */
    public function purgeProduct(int $id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $title = $product->title;

        Activity::log('product.purged', $product, ['title' => $title]);
        $product->forceDelete();

        return back()->with('success', 'Product permanently deleted.');
    }

    /**
     * Display all trashed categories.
     */
    public function categories()
    {
        $categories = Category::onlyTrashed()
            ->withCount(['products']) // Eager load product count
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.trash.categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Restore a trashed category.
     */
    public function restoreCategory(int $id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        Activity::log('category.restored', $category);

        return back()->with('success', 'Category restored successfully.');
    }

    /**
     * Permanently delete a category.
     */
    public function purgeCategory(int $id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $name = $category->name;

        Activity::log('category.purged', $category, ['name' => $name]);
        $category->forceDelete();

        return back()->with('success', 'Category permanently deleted.');
    }

    /**
     * Display all trashed coupons.
     */
    public function coupons()
    {
        $coupons = Coupon::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.trash.coupons', [
            'coupons' => $coupons,
        ]);
    }

    /**
     * Restore a trashed coupon.
     */
    public function restoreCoupon(int $id)
    {
        $coupon = Coupon::onlyTrashed()->findOrFail($id);
        $coupon->restore();
        Activity::log('coupon.restored', $coupon);

        return back()->with('success', 'Coupon restored successfully.');
    }

    /**
     * Permanently delete a coupon.
     */
    public function purgeCoupon(int $id)
    {
        $coupon = Coupon::onlyTrashed()->findOrFail($id);
        $code = $coupon->code;

        Activity::log('coupon.purged', $coupon, ['code' => $code]);
        $coupon->forceDelete();

        return back()->with('success', 'Coupon permanently deleted.');
    }
}
