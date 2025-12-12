@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-section title="Profile information" description="Update your account’s profile details.">

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <x-input label="Name" name="name" value="{{ old('name', $user->name) }}" required />
                <x-input label="Email" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                <x-button type="submit">Save</x-button>
            </form>
        </x-section>

        <x-section title="Update password" description="Use a strong and unique password.">

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-input label="Current password" name="current_password" type="password" required />
                <x-input label="New password" name="password" type="password" required />
                <x-input label="Confirm password" name="password_confirmation" type="password" required />
                <x-button type="submit">Change password</x-button>
            </form>
        </x-section>

        <x-section title="Delete account" class="lg:col-span-2" description="Permanently delete your account.">
            <form x-data="{ open: false }" x-ref="form" method="POST" action="{{ route('profile.destroy') }}"
                class="space-y-4" @submit.prevent="open = true">
                @csrf
                @method('DELETE')

                <x-input label="Confirm password" name="password" type="password" required />

                @error('password', 'userDeletion')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <x-button type="submit" variant="danger">Delete account</x-button>

                {{-- Custom Confirmation Modal --}}
                <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto"
                    style="display: none;">

                    <div @click="open = false" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="relative bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
                            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>

                            <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Delete Account?</h3>
                            <p class="text-sm text-center text-gray-500 mb-6">
                                Are you sure you want to delete your account? This action cannot be undone and all your data
                                will be permanently lost.
                            </p>

                            <div class="flex justify-end gap-3">
                                <button type="button" @click="open = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancel
                                </button>
                                <button type="button" @click="$refs.form.submit()"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Delete Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </x-section>

    </div>
@endsection