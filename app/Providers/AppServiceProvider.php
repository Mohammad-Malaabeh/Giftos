<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Laravel\Telescope\TelescopeServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (
            $this->app->environment('local') &&
            class_exists(TelescopeServiceProvider::class) &&
            !$this->app->environment('testing')
        ) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // Register policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Product::class, \App\Policies\ProductPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Order::class, \App\Policies\OrderPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Category::class, \App\Policies\CategoryPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Coupon::class, \App\Policies\CouponPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Review::class, \App\Policies\ReviewPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);

        // Super admin bypass
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });

        // Register middleware alias for role checks (web + API)
        Route::aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);

        // Register observers
        if (!$this->app->environment('testing')) {
            \App\Models\Product::observe(\App\Observers\ProductObserver::class);
            \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        }
    }
}
