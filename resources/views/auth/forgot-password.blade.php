@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Forgot password</h1>
    <x-form-errors />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <x-input label="Email" name="email" type="email" value="{{ old('email') }}" required autofocus />
        <x-button type="submit" class="w-full">Send reset link</x-button>
        <p class="text-sm text-gray-600 mt-3">
            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Back to login</a>
        </p>
    </form>
@endsection
