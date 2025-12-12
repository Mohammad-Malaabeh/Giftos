<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\WishlistController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\FeedbackController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\MonitoringController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Version 1
Route::prefix('v1')->group(function () {

    // Public routes (no authentication required)
    // Auth routes at root level (for API tests)
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');

    // Auth routes under /auth prefix (for consistency)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot_password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset_password');
        Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('auth.verify_email');
    });

    // Public product and category routes
    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('api.products.show');
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('api.categories.show');

    // Product reviews (public read)
    Route::get('/products/{product:slug}/reviews', [ReviewController::class, 'index'])->name('products.reviews');

    // Product search and filtering
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('/products/featured', [ProductController::class, 'featured'])->name('products.featured');
    Route::get('/products/latest', [ProductController::class, 'latest'])->name('products.latest');
    Route::get('/products/on-sale', [ProductController::class, 'onSale'])->name('products.on_sale');
    Route::get('/categories/{category}/products', [CategoryController::class, 'products'])->name('categories.products');
    // Public feedback API (anonymous or authenticated)
    Route::post('/feedback', [FeedbackController::class, 'store'])
        ->middleware(['throttle:6,60'])
        ->name('api.feedback.store');

    // Protected routes (authentication required)
    Route::middleware(['auth:sanctum'])->group(function () {

        // User management
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout_all');
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
            Route::get('/tokens', [AuthController::class, 'tokens'])->name('auth.tokens');
            Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken'])->name('auth.revoke_token');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.update_profile');
            Route::put('/password', [AuthController::class, 'updatePassword'])->name('auth.update_password');
            Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('auth.resend_verification');
        });

        // Media management
        Route::prefix('media')->group(function () {

            // Discount and shipping
            Route::post('/apply-discount', [CartController::class, 'applyDiscount'])->name('api.cart.apply_discount');
            Route::delete('/discount', [CartController::class, 'removeDiscount'])->name('api.cart.remove_discount');
            Route::post('/shipping-estimate', [CartController::class, 'estimateShipping'])->name('api.cart.shipping_estimate');

            // Saved carts
            Route::post('/save-for-later', [CartController::class, 'saveForLater'])->name('api.cart.save_for_later');
            Route::post('/restore/{id}', [CartController::class, 'restoreSavedCart'])->name('api.cart.restore');

            // Bulk operations
            Route::post('/merge', [CartController::class, 'merge'])->name('api.cart.merge');
            Route::delete('/bulk', [CartController::class, 'bulkDelete'])->name('api.cart.bulk_delete');
            Route::put('/bulk', [CartController::class, 'bulkUpdate'])->name('api.cart.bulk_update');
        });

        // Cart management
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('api.cart.index');
            Route::post('/', [CartController::class, 'store'])->name('api.cart.store');
            Route::patch('/{cartItem}', [CartController::class, 'update'])->name('api.cart.update');
            Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('api.cart.destroy');
            Route::delete('/', [CartController::class, 'clear'])->name('api.cart.clear');
        });

        // Wishlist management
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index'])->name('api.wishlist.index');
            Route::post('/', [WishlistController::class, 'add'])->name('api.wishlist.store');
            Route::delete('/{product}', [WishlistController::class, 'remove'])->name('api.wishlist.destroy');
            Route::get('/check/{product}', [WishlistController::class, 'check'])->name('api.wishlist.check')->whereNumber('product');
            Route::delete('/clear', [WishlistController::class, 'clear'])->name('api.wishlist.clear');
            Route::delete('/bulk', [WishlistController::class, 'bulk'])->name('api.wishlist.bulk');
            Route::get('/stats', [WishlistController::class, 'stats'])->name('api.wishlist.stats');
            Route::post('/{product}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('api.wishlist.move_to_cart')->whereNumber('product');
        });

        // Order management
        Route::prefix('orders')->name('api.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('orders.index');
            Route::post('/', [OrderController::class, 'store'])->name('orders.store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::put('/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
            Route::get('/{order}/status', [OrderController::class, 'status'])->name('orders.status');
        });

        // Review management (create and update)
        Route::prefix('reviews')->name('api.')->group(function () {
            Route::post('/', [ReviewController::class, 'store'])->name('reviews.store');
            Route::patch('/{review}', [ReviewController::class, 'update'])->name('reviews.update');
            Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        });

        // Checkout process
        Route::prefix('checkout')->group(function () {
            Route::post('/process', [CheckoutController::class, 'process'])->middleware('can:orders.create')->name('checkout.process');
            Route::get('/shipping-methods', [CheckoutController::class, 'shippingMethods'])->name('checkout.shipping_methods');
            Route::get('/payment-methods', [CheckoutController::class, 'paymentMethods'])->name('checkout.payment_methods');
            Route::post('/apply-coupon', [CheckoutController::class, 'applyCoupon'])->middleware('can:orders.create')->name('checkout.apply_coupon');
            Route::delete('/remove-coupon', [CheckoutController::class, 'removeCoupon'])->middleware('can:orders.create')->name('checkout.remove_coupon');
        });

        // Admin routes (require admin permissions)
        Route::middleware(['can:admin.access'])->prefix('admin')->group(function () {

            // Product management
            Route::apiResource('products', ProductController::class)->except(['index', 'show'])
                ->names('api.admin.products')
                ->middleware(['can:products.*']);
            Route::post('/products/{product}/restore', [ProductController::class, 'restore'])->middleware('can:products.delete');
            Route::delete('/products/{product}/force-delete', [ProductController::class, 'forceDelete'])->middleware('can:products.delete');
            Route::get('/products/trashed', [ProductController::class, 'trashed'])->middleware('can:products.view');

            // Category management
            Route::apiResource('categories', CategoryController::class)->except(['index', 'show'])
                ->names('api.admin.categories')
                ->middleware(['can:categories.*']);
            Route::post('/categories/{category}/restore', [CategoryController::class, 'restore'])->middleware('can:categories.delete');
            Route::delete('/categories/{category}/force-delete', [CategoryController::class, 'forceDelete'])->middleware('can:categories.delete');
            Route::get('/categories/trashed', [CategoryController::class, 'trashed'])->middleware('can:categories.view');

            // Order management
            Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'update'])
                ->middleware(['can:orders.*'])
                ->names('api.admin.orders');
            Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('can:orders.update');
            Route::get('/orders/stats', [OrderController::class, 'stats'])->middleware('can:analytics.orders');
            Route::get('/orders/export', [OrderController::class, 'export'])->middleware('can:export.orders');

            // User management
            Route::apiResource('users', UserController::class)->only(['index', 'show', 'destroy'])
                ->middleware(['can:users.*'])
                ->names('api.admin.users');
            Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->middleware('can:users.roles');
            Route::put('/users/{user}/ban', [UserController::class, 'ban'])->middleware('can:users.manage');
            Route::put('/users/{user}/unban', [UserController::class, 'unban'])->middleware('can:users.manage');

            // Review management
            Route::apiResource('reviews', ReviewController::class)->only(['index', 'show', 'destroy'])
                ->middleware(['can:reviews.*'])
                ->names('api.admin.reviews');
            Route::put('/reviews/{review}/approve', [ReviewController::class, 'approve'])->middleware('can:reviews.approve');
            Route::put('/reviews/{review}/reject', [ReviewController::class, 'reject'])->middleware('can:reviews.approve');

            // Monitoring and health checks
            Route::prefix('monitoring')->group(function () {
                Route::get('/health', [MonitoringController::class, 'health'])->middleware('can:system.monitoring');
                Route::get('/metrics', [MonitoringController::class, 'metrics'])->middleware('can:system.monitoring');
                Route::get('/metrics/history', [MonitoringController::class, 'metricsHistory'])->middleware('can:system.monitoring');
                Route::get('/logs', [MonitoringController::class, 'logs'])->middleware('can:system.logs');
                Route::get('/cache/stats', [MonitoringController::class, 'cacheStats'])->middleware('can:system.monitoring');
                Route::post('/cache/clear', [MonitoringController::class, 'clearCache'])->middleware('can:system.maintenance');
                Route::post('/cache/warm', [MonitoringController::class, 'warmCache'])->middleware('can:system.maintenance');
                Route::get('/database/stats', [MonitoringController::class, 'databaseStats'])->middleware('can:system.monitoring');
                Route::get('/queue/stats', [MonitoringController::class, 'queueStats'])->middleware('can:queues.view');
                Route::get('/user/stats', [MonitoringController::class, 'userStats'])->middleware('can:analytics.users');
            });
        });
    });
});
