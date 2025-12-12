<header class="bg-white border-b border-gray-200">
    <x-container class="py-3">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="text-lg font-semibold text-gray-900">
                {{ config('app.name', 'YOUR_APP_NAME') }}
            </a>
            <form action="{{ route('products.index') }}" method="get" class="hidden md:block flex-1 max-w-xl">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..."
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </form>
            <nav class="flex items-center gap-4">
                <a href="{{ route('addresses.index') }}" class="text-sm text-gray-700 hover:text-gray-900">address</a>
                <a href="{{ route('user.orders.index') }}" class="text-sm text-gray-700 hover:text-gray-900">My
                    Orders</a>
                <a href="{{ route('products.index') }}" class="text-sm text-gray-700 hover:text-gray-900">Shop</a>
                <a href="{{ route('cart.index') }}" class="text-sm text-gray-700 hover:text-gray-900">Cart</a>
                @auth
                    {{-- Debug: Show current user data --}}
                    @php
                        $user = auth()->user();
                        logger('Current User ID: ' . $user->id);
                        logger('Current User Email: ' . $user->email);
                        logger('User Role: ' . $user->role);
                        logger('Has Admin Role: ' . ($user->hasRole('admin') ? 'true' : 'false'));
                        logger('Is Admin: ' . ($user->isAdmin() ? 'true' : 'false'));
                        logger('Can Admin Access: ' . (Gate::allows('admin.access') ? 'true' : 'false'));
                    @endphp
                    
                    <a href="{{ route('wishlist.index') }}" class="text-sm text-gray-700 hover:text-gray-900">Wishlist</a>
                    @can('admin.access')
                        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-700 hover:text-gray-900">Admin
                            Dashboard</a>
                    @endcan
                    <a href="{{ route('profile.edit') }}" class="text-sm text-gray-700 hover:text-gray-900">
                        Profile
                    </a>
                    <form action="{{ route('logout') }}" method="post" class="inline">
                        @csrf
                        <button class="text-sm text-gray-700 hover:text-gray-900">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">Login</a>
                    <a href="{{ route('register') }}" class="text-sm text-gray-700 hover:text-gray-900">Register</a>
                @endauth
            </nav>
        </div>
    </x-container>
</header>
