@extends('layouts.guest')

@section('title', 'Confirm password')

@section('content')
    <h1 class="text-xl font-semibold mb-2">Confirm your password</h1>
    <p class="text-sm text-gray-600 mb-4">For your security, please confirm your password to continue.</p>
    <x-form-errors />

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf
        <x-input label="Password" name="password" type="password" required autofocus />
        <x-button type="submit" class="w-full">Confirm</x-button>
    </form>
@endsection
