@extends('layouts.guest')

@section('title','Login')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Sign in</h1>
    <x-form-errors />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <x-input label="Email" name="email" type="email" value="{{ old('email') }}" required autofocus />
        <x-input label="Password" name="password" type="password" required />

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" class="text-indigo-600">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:underline">Forgot password?</a>
        </div>

        <x-button type="submit" class="w-full">Sign in</x-button>

        <p class="text-sm text-gray-600 mt-3">
            New here?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Create an account</a>
        </p>
    </form>
@endsection