<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @hasSection('title')
            @yield('title') ·
        @endif Admin · {{ config('app.name', 'YOUR_APP_NAME') }}
    </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js for interactive components --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Alpine utilities --}}
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('head')
</head>

<body class="min-h-screen flex">
    <aside class="hidden lg:block w-72 bg-white border-r border-slate-200">
        @include('partials.nav-admin')
    </aside>

    <div class="flex-1 min-w-0 flex flex-col">
        <header class="bg-white border-b border-slate-200">
            <x-container class="py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-800">@yield('page_title', 'Dashboard')</h1>
                        @hasSection('breadcrumb')
                            <div class="mt-1">
                                @yield('breadcrumb')
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @yield('actions')
                    </div>
                </div>
            </x-container>
        </header>

        <main class="flex-1">
            <x-container class="py-6">
                @yield('content')
            </x-container>
        </main>
    </div>

    {{-- Toast Notifications --}}
    <x-toast />

    {{-- Auto-show success flash messages as toasts --}}
    @if (session('success'))
        <div x-data x-init="$dispatch('toast', { message: '{{ session('success') }}', type: 'success' })"></div>
    @endif

    {{-- Auto-show error flash messages as toasts --}}
    @if (session('error'))
        <div x-data x-init="$dispatch('toast', { message: '{{ session('error') }}', type: 'error' })"></div>
    @endif

    {{-- Auto-show info flash messages as toasts --}}
    @if (session('info'))
        <div x-data x-init="$dispatch('toast', { message: '{{ session('info') }}', type: 'info' })"></div>
    @endif

    @stack('scripts')
</body>

</html>