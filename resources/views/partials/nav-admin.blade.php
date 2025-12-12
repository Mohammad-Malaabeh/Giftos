<div class="p-4 border-b border-slate-200">
    <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold text-slate-900">
        Admin · {{ config('app.name', 'YOUR_APP_NAME') }}
    </a>
</div>
<nav class="p-3 space-y-1">
    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-slate-100">Admin
        Dashboard</a>
    <a href="{{ route('admin.orders.index') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-slate-100">Orders</a>
    <a href="{{ route('admin.products.index') }}"
        class="block px-3 py-2 rounded-md text-sm hover:bg-slate-100">Products</a>
    <a href="{{ route('admin.categories.index') }}"
        class="block px-3 py-2 rounded-md text-sm hover:bg-slate-100">Categories</a>
    <a href="{{ route('admin.coupons.index') }}"
        class="block px-3 py-2 rounded-md text-sm hover:bg-slate-100">Coupons</a>
    <a href="{{ route('admin.feedback.index') }}"
        class="block px-3 py-2 rounded-md text-sm hover:bg-slate-100">Feedback</a>
    <a href="{{ route('admin.activity.index') }}"
        class="block px-3 py-2 rounded-md text-sm hover:bg-slate-100">Activity</a>
    <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-sm hover:bg-slate-100">← Back to site</a>
</nav>