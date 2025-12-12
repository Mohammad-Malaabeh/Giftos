<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderBulkController;
use App\Http\Controllers\Admin\ProductBulkController;
use App\Http\Controllers\Admin\CategoryBulkController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Models\Product;
use App\Models\Category;
use App\Models\Coupon;
use App\Services\Activity;

Route::middleware(['auth', 'can:admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Bulk operations
        Route::post('orders-bulk', [OrderBulkController::class, 'bulk'])->name('orders.bulk');
        Route::post('products-bulk', [ProductBulkController::class, 'bulk'])->name('products.bulk');
        Route::post('categories-bulk', [CategoryBulkController::class, 'bulk'])->name('categories.bulk');

        // Resources
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('products', AdminProductController::class);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
        Route::resource('users', AdminUserController::class);

        // Export/Import routes
        Route::get('orders-export', [OrderBulkController::class, 'export'])->name('orders.export');
        Route::get('product-export', [ProductBulkController::class, 'export'])->name('products.export');
        Route::post('orders-import', [OrderBulkController::class, 'import'])->name('orders.import');
        Route::post('products-import', [ProductBulkController::class, 'import'])->name('products.import');

        // Signed delete links
        Route::get('categories/{category}/delete', [AdminCategoryController::class, 'deleteLink'])
            ->name('categories.delete')
            ->middleware('signed');

        Route::get('products/{product}/delete', [AdminProductController::class, 'deleteLink'])
            ->name('products.delete')
            ->middleware('signed');

        // Search
        Route::get('search', [SearchController::class, 'index'])->name('search');

        // Coupons
        Route::resource('coupons', CouponController::class)->except(['show']);

        // Activity logs
        Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');

        // Trash management
        Route::prefix('trash')->name('trash.')->group(function () {
            // Products trash
            Route::get('products', [TrashController::class, 'products'])->name('products');
            Route::post('products/{id}/restore', [TrashController::class, 'restoreProduct'])->name('products.restore');
            Route::delete('products/{id}/purge', [TrashController::class, 'purgeProduct'])->name('products.purge');

            // Categories trash
            Route::get('categories', [TrashController::class, 'categories'])->name('categories');
            Route::post('categories/{id}/restore', [TrashController::class, 'restoreCategory'])->name('categories.restore');
            Route::delete('categories/{id}/purge', [TrashController::class, 'purgeCategory'])->name('categories.purge');

            // Coupons trash
            Route::get('coupons', [TrashController::class, 'coupons'])->name('coupons');
            Route::post('coupons/{id}/restore', [TrashController::class, 'restoreCoupon'])->name('coupons.restore');
            Route::delete('coupons/{id}/purge', [TrashController::class, 'purgeCoupon'])->name('coupons.purge');
        });
    });
