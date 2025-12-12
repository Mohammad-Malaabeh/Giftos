@extends('layouts.guest')

@section('title', 'Verify email')

@section('content')
    <h1 class="text-xl font-semibold mb-2">Verify your email</h1>
    <p class="text-sm text-gray-600 mb-4">
        Thanks for signing up! Please verify your email by clicking the link we just emailed you.
        If you didn’t receive the email, we’ll gladly send you another.
    </p>

    <div class="flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button type="submit">Resend verification email</x-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button variant="secondary">Log out</x-button>
        </form>
    </div>
@endsection
