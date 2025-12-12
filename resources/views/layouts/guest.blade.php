<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @hasSection('title')
            @yield('title') ·
        @endif {{ config('app.name', 'YOUR_APP_NAME') }}
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

<body class="min-h-screen flex flex-col">
    @include('partials.nav')

    <main class="flex-1">
        <x-container class="py-10">
            <div class="mx-auto w-full max-w-xl bg-white rounded-xl shadow-sm p-6">
                @yield('content')
            </div>
        </x-container>
    </main>

    @include('partials.footer')
    @include('partials.cookie-consent')

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
