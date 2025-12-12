<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\Media;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Feedback;
use App\Policies\ProductPolicy;
use App\Policies\OrderPolicy;
use App\Policies\UserPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\MediaPolicy;
use App\Policies\CommentPolicy;
use App\Policies\RolePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\FeedbackPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        User::class => UserPolicy::class,
        Category::class => CategoryPolicy::class,
        Media::class => MediaPolicy::class,
        Comment::class => CommentPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        Feedback::class => FeedbackPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerGates();
    }

    private function registerGates(): void
    {
        // Super admin gate (for system-level operations)
        Gate::define('superadmin', fn (User $user) => $user->isSuperAdmin());

        // Admin gates
        Gate::define('admin.access', fn (User $user) => $user->isAdmin());
        Gate::define('admin.users', fn (User $user) => $user->isAdmin());
        Gate::define('admin.settings', fn (User $user) => $user->isAdmin());
        Gate::define('admin.analytics', fn (User $user) => $user->isAdmin());
        Gate::define('admin.system', fn (User $user) => $user->isAdmin());
        Gate::define('admin.roles', fn (User $user) => $user->isAdmin());
        Gate::define('admin.permissions', fn (User $user) => $user->isAdmin());
        
        // Feedback and performance gates
        Gate::define('viewAny', [FeedbackPolicy::class, 'viewAny']);
        Gate::define('view', [FeedbackPolicy::class, 'view']);
        Gate::define('create', [FeedbackPolicy::class, 'create']);
        Gate::define('update', [FeedbackPolicy::class, 'update']);
        Gate::define('delete', [FeedbackPolicy::class, 'delete']);
        Gate::define('viewPerformanceDashboard', fn (User $user) => $user->isAdmin() || $user->hasPermission('view_performance_dashboard'));

        // Manager gates
        Gate::define('manager.access', fn (User $user) => $user->isManager());
        Gate::define('manager.products', fn (User $user) => $user->isManager());
        Gate::define('manager.orders', fn (User $user) => $user->isManager());
        Gate::define('manager.categories', fn (User $user) => $user->isManager());
        Gate::define('manager.reports', fn (User $user) => $user->isManager());
        Gate::define('manager.customers', fn (User $user) => $user->isManager());

        // Customer gates
        Gate::define('customer.access', fn (User $user) => true); // All authenticated users
        Gate::define('customer.orders', fn (User $user) => true);
        Gate::define('customer.profile', fn (User $user) => true);
        Gate::define('customer.reviews', fn (User $user) => true);
        Gate::define('customer.wishlist', fn (User $user) => true);
        Gate::define('customer.cart', fn (User $user) => true);

        // Product-specific gates
        Gate::define('products.view', fn (?User $user) => true);
        Gate::define('products.create', fn (User $user) => $user->hasPermission('products.create'));
        Gate::define('products.update', fn (User $user) => $user->hasPermission('products.update'));
        Gate::define('products.delete', fn (User $user) => $user->hasPermission('products.delete'));
        Gate::define('products.featured', fn (User $user) => $user->hasPermission('products.featured'));
        Gate::define('products.inventory', fn (User $user) => $user->hasPermission('products.inventory'));
        Gate::define('products.pricing', fn (User $user) => $user->hasPermission('products.pricing'));
        Gate::define('products.export', fn (User $user) => $user->hasPermission('products.export'));
        Gate::define('products.bulk', fn (User $user) => $user->hasPermission('products.bulk'));

        // Order-specific gates
        Gate::define('orders.view', fn (User $user, ?Order $order = null) => 
            $user->hasPermission('orders.view') || 
            ($order && $user->id === $order->user_id)
        );
        Gate::define('orders.create', fn (User $user) => $user->hasPermission('orders.create'));
        Gate::define('orders.update', fn (User $user) => $user->hasPermission('orders.update'));
        Gate::define('orders.cancel', fn (User $user, Order $order) => 
            $user->hasPermission('orders.cancel') || 
            ($user->id === $order->user_id && in_array($order->status, ['pending', 'processing']))
        );
        Gate::define('orders.refund', fn (User $user) => $user->hasPermission('orders.refund'));
        Gate::define('orders.export', fn (User $user) => $user->hasPermission('orders.export'));
        Gate::define('orders.bulk', fn (User $user) => $user->hasPermission('orders.bulk'));

        // Category-specific gates
        Gate::define('categories.view', fn (?User $user) => true);
        Gate::define('categories.create', fn (User $user) => $user->hasPermission('categories.create'));
        Gate::define('categories.update', fn (User $user) => $user->hasPermission('categories.update'));
        Gate::define('categories.delete', fn (User $user) => $user->hasPermission('categories.delete'));
        Gate::define('categories.reorder', fn (User $user) => $user->hasPermission('categories.reorder'));

        // Media-specific gates
        Gate::define('media.view', fn (?User $user) => true);
        Gate::define('media.upload', fn (User $user) => $user->hasPermission('media.upload'));
        Gate::define('media.update', fn (User $user) => $user->hasPermission('media.update'));
        Gate::define('media.delete', fn (User $user) => $user->hasPermission('media.delete'));
        Gate::define('media.download', fn (User $user, Media $media) => 
            $user->hasPermission('media.download') || $media->mediable_id === $user->id
        );

        // Comment-specific gates
        Gate::define('comments.view', fn (?User $user) => true);
        Gate::define('comments.create', fn (User $user) => $user->hasPermission('comments.create'));
        Gate::define('comments.update', fn (User $user, Comment $comment) => 
            $user->hasPermission('comments.update') || $user->id === $comment->user_id
        );
        Gate::define('comments.delete', fn (User $user, Comment $comment) => 
            $user->hasPermission('comments.delete') || $user->id === $comment->user_id
        );
        Gate::define('comments.moderate', fn (User $user) => $user->hasPermission('comments.moderate'));
        Gate::define('comments.approve', fn (User $user) => $user->hasPermission('comments.approve'));

        // Tag-specific gates
        Gate::define('tags.view', fn (?User $user) => true);
        Gate::define('tags.create', fn (User $user) => $user->hasPermission('tags.create'));
        Gate::define('tags.update', fn (User $user) => $user->hasPermission('tags.update'));
        Gate::define('tags.delete', fn (User $user) => $user->hasPermission('tags.delete'));
        Gate::define('tags.manage', fn (User $user) => $user->hasPermission('tags.manage'));

        // Review-specific gates
        Gate::define('reviews.create', fn (User $user) => $user->hasPermission('reviews.create'));
        Gate::define('reviews.update', fn (User $user, ?\App\Models\Review $review = null) => 
            $user->hasPermission('reviews.update') || ($review && $user->id === $review->user_id)
        );
        Gate::define('reviews.delete', fn (User $user, \App\Models\Review $review) => 
            $user->hasPermission('reviews.delete') || $user->id === $review->user_id
        );
        Gate::define('reviews.moderate', fn (User $user) => $user->hasPermission('reviews.moderate'));
        Gate::define('reviews.approve', fn (User $user) => $user->hasPermission('reviews.approve'));

        // User management gates
        Gate::define('users.view', fn (User $user, ?User $targetUser = null) => 
            $user->hasPermission('users.view') || ($targetUser && $user->id === $targetUser->id)
        );
        Gate::define('users.create', fn (User $user) => $user->hasPermission('users.create'));
        Gate::define('users.update', fn (User $user, User $targetUser) => 
            $user->hasPermission('users.update') || $user->id === $targetUser->id
        );
        Gate::define('users.delete', fn (User $user) => $user->hasPermission('users.delete'));
        Gate::define('users.manage', fn (User $user) => $user->hasPermission('users.manage'));
        Gate::define('users.roles', fn (User $user) => $user->hasPermission('users.roles'));
        Gate::define('users.export', fn (User $user) => $user->hasPermission('users.export'));

        // Role management gates
        Gate::define('roles.view', fn (User $user) => $user->hasPermission('roles.view'));
        Gate::define('roles.create', fn (User $user) => $user->hasPermission('roles.create'));
        Gate::define('roles.update', fn (User $user) => $user->hasPermission('roles.update'));
        Gate::define('roles.delete', fn (User $user) => $user->hasPermission('roles.delete'));
        Gate::define('roles.assign', fn (User $user) => $user->hasPermission('roles.assign'));
        Gate::define('roles.permissions', fn (User $user) => $user->hasPermission('roles.permissions'));

        // Permission management gates
        Gate::define('permissions.view', fn (User $user) => $user->hasPermission('permissions.view'));
        Gate::define('permissions.create', fn (User $user) => $user->hasPermission('permissions.create'));
        Gate::define('permissions.update', fn (User $user) => $user->hasPermission('permissions.update'));
        Gate::define('permissions.delete', fn (User $user) => $user->hasPermission('permissions.delete'));
        Gate::define('permissions.assign', fn (User $user) => $user->hasPermission('permissions.assign'));

        // Analytics gates
        Gate::define('analytics.view', fn (User $user) => $user->hasPermission('analytics.view'));
        Gate::define('analytics.products', fn (User $user) => $user->hasPermission('analytics.products'));
        Gate::define('analytics.orders', fn (User $user) => $user->hasPermission('analytics.orders'));
        Gate::define('analytics.users', fn (User $user) => $user->hasPermission('analytics.users'));
        Gate::define('analytics.revenue', fn (User $user) => $user->hasPermission('analytics.revenue'));
        Gate::define('analytics.export', fn (User $user) => $user->hasPermission('analytics.export'));

        // Export gates
        Gate::define('export.products', fn (User $user) => $user->hasPermission('export.products'));
        Gate::define('export.orders', fn (User $user) => $user->hasPermission('export.orders'));
        Gate::define('export.users', fn (User $user) => $user->hasPermission('export.users'));
        Gate::define('export.analytics', fn (User $user) => $user->hasPermission('export.analytics'));
        Gate::define('export.reports', fn (User $user) => $user->hasPermission('export.reports'));

        // API-specific gates
        Gate::define('api.access', fn (User $user) => $user->hasPermission('api.access'));
        Gate::define('api.admin', fn (User $user) => $user->hasPermission('api.admin'));
        Gate::define('api.manager', fn (User $user) => $user->hasPermission('api.manager'));
        Gate::define('api.throttle', fn (User $user) => $user->hasPermission('api.throttle'));

        // System gates
        Gate::define('system.backup', fn (User $user) => $user->hasPermission('system.backup'));
        Gate::define('system.restore', fn (User $user) => $user->hasPermission('system.restore'));
        Gate::define('system.maintenance', fn (User $user) => $user->hasPermission('system.maintenance'));
        Gate::define('system.logs', fn (User $user) => $user->hasPermission('system.logs'));
        Gate::define('system.monitoring', fn (User $user) => $user->hasPermission('system.monitoring'));

        // Content management gates
        Gate::define('content.pages', fn (User $user) => $user->hasPermission('content.pages'));
        Gate::define('content.blog', fn (User $user) => $user->hasPermission('content.blog'));
        Gate::define('content.news', fn (User $user) => $user->hasPermission('content.news'));
        Gate::define('content.seo', fn (User $user) => $user->hasPermission('content.seo'));

        // Notification gates
        Gate::define('notifications.send', fn (User $user) => $user->hasPermission('notifications.send'));
        Gate::define('notifications.manage', fn (User $user) => $user->hasPermission('notifications.manage'));
        Gate::define('notifications.templates', fn (User $user) => $user->hasPermission('notifications.templates'));

        // Queue and job gates
        Gate::define('queues.view', fn (User $user) => $user->hasPermission('queues.view'));
        Gate::define('queues.manage', fn (User $user) => $user->hasPermission('queues.manage'));
        Gate::define('jobs.monitor', fn (User $user) => $user->hasPermission('jobs.monitor'));
        Gate::define('jobs.retry', fn (User $user) => $user->hasPermission('jobs.retry'));

        // Wildcard permission gate (for dynamic permissions)
        Gate::before(function (User $user, string $ability) {
            // Super admin has access to everything
            if ($user->isSuperAdmin()) {
                return true;
            }

            // Check if user has the exact permission
            if ($user->hasPermission($ability)) {
                return true;
            }

            // Check wildcard permissions
            $parts = explode('.', $ability);
            if (count($parts) >= 2) {
                $wildcard = $parts[0] . '.*';
                if ($user->hasPermission($wildcard)) {
                    return true;
                }
            }
        });

        // After gates for additional logic
        Gate::after(function (User $user, string $ability, bool $result) {
            // Log authorization attempts for debugging
            if (config('app.debug')) {
                \Log::debug("Authorization check: {$ability} for user {$user->id} - " . ($result ? 'allowed' : 'denied'));
            }
            
            return $result;
        });
    }
}
