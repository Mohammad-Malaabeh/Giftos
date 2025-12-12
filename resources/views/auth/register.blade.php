@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Create account</h1>
    <x-form-errors />

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <x-input label="Name" name="name" value="{{ old('name') }}" required autofocus />
        <x-input label="Email" name="email" type="email" value="{{ old('email') }}" required />
        <x-input label="Password" name="password" type="password" required />
        <x-input label="Confirm password" name="password_confirmation" type="password" required />
        <x-button type="submit" class="w-full">Create account</x-button>
        <p class="text-sm text-gray-600 mt-3">Already have an account?
            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Sign in</a>
        </p>
    </form>
@endsection
