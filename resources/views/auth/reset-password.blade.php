@extends('layouts.guest')

@section('title', 'Reset password')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Reset password</h1>
    <x-form-errors />

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-input label="Email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus />
        <x-input label="New password" name="password" type="password" required />
        <x-input label="Confirm password" name="password_confirmation" type="password" required />
        <x-button type="submit" class="w-full">Reset password</x-button>
    </form>
@endsection
