<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\CommentController;
use App\Jobs\AcceptCookiesJob;

// Public shop routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('product.show');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'add'])->name('cart.store'); // Points to add() method
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::match(['PUT', 'PATCH'], '/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.remove'); // Points to destroy() method
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/estimate', [CartController::class, 'estimateShipping'])->name('cart.estimate');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::post('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// Wishlist routes (require authentication)
Route::middleware('auth')->prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/add/{product}', [WishlistController::class, 'add'])->name('add');
    Route::delete('/remove/{product}', [WishlistController::class, 'remove'])->name('remove');
    Route::post('/move-to-cart/{product}', [WishlistController::class, 'moveToCart'])->name('move-to-cart');
});

// Order routes (require authentication)
Route::middleware('auth')->prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');

    // Admin/Manager only routes
    Route::middleware('can:admin.access')->group(function () {
        Route::post('/{order}/refund', [OrderController::class, 'refund'])->name('refund');
    });
});

// Comment routes
Route::post('/comments', [CommentController::class, 'store'])->middleware('auth')->name('comments.store');

// Checkout routes (guest accessible until payment)
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::get('/create', [CheckoutController::class, 'create'])->name('create');
    Route::post('/process', [CheckoutController::class, 'store'])->name('store');
    Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('cancel');
    Route::get('/stripe/{order}', [CheckoutController::class, 'stripe'])->name('stripe');
});

// Stripe payment routes
Route::post('/stripe/pay', [StripeController::class, 'pay'])->name('stripe.pay');
Route::post('/stripe/intent', [StripeController::class, 'intent'])->middleware('auth')->name('stripe.intent');
Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

// Reviews (guest can view, authenticated can create)
Route::prefix('reviews')->name('reviews.')->group(function () {
    Route::get('/', [ReviewController::class, 'index'])->name('index');
    Route::post('/products/{product}', [ReviewController::class, 'store'])->name('store')->middleware('auth');
    Route::get('/{review}/edit', [ReviewController::class, 'edit'])->name('edit')->middleware('auth');
    Route::patch('/{review}', [ReviewController::class, 'update'])->name('update')->middleware('auth');
    Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy')->middleware('auth');
});

// Cookie consent
Route::post('/cookies/accept', function (\Illuminate\Http\Request $request) {
    session(['cookie_consent' => true]);

    AcceptCookiesJob::dispatch(
        auth()->id(),
        $request->ip(),
        $request->userAgent()
    );

    return response()->json(['status' => 'ok']);
})->name('cookies.accept');

// Authenticated user routes
Route::middleware('auth')->group(function () {
    // User addresses
    Route::prefix('addresses')->name('addresses.')->group(function () {
        Route::get('/', [UserAddressController::class, 'index'])->name('index');
        Route::post('/', [UserAddressController::class, 'store'])->name('store');
        Route::put('/{address}', [UserAddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [UserAddressController::class, 'destroy'])->name('destroy');
        Route::post('/{address}/set-default', [UserAddressController::class, 'setDefault'])->name('set-default');
    });

    // User orders
    Route::prefix('my-orders')->name('user.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    });
});