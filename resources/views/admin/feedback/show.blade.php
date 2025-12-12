@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Feedback Details</h1>
            <a href="{{ route('admin.feedback.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Message</h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <strong class="block text-sm font-medium text-gray-700 mb-1">Type:</strong>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ [
        'bug' => 'bg-red-100 text-red-800',
        'feature' => 'bg-green-100 text-green-800',
        'suggestion' => 'bg-blue-100 text-blue-800',
        'other' => 'bg-gray-100 text-gray-800'
    ][$feedback->type] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $types[$feedback->type] ?? ucfirst($feedback->type) }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <strong class="block text-sm font-medium text-gray-700 mb-1">Status:</strong>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ [
        'new' => 'bg-blue-100 text-blue-800',
        'read' => 'bg-gray-100 text-gray-800',
        'in_progress' => 'bg-yellow-100 text-yellow-800',
        'resolved' => 'bg-green-100 text-green-800'
    ][$feedback->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statuses[$feedback->status] ?? ucfirst($feedback->status) }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <strong class="block text-sm font-medium text-gray-700 mb-1">Submitted:</strong>
                            <span class="text-gray-900">{{ $feedback->created_at->format('M j, Y g:i A') }}</span>
                            <span class="text-gray-500 text-sm">({{ $feedback->created_at->diffForHumans() }})</span>
                        </div>

                        @if($feedback->page_url)
                            <div class="mb-4">
                                <strong class="block text-sm font-medium text-gray-700 mb-1">Page URL:</strong>
                                <a href="{{ $feedback->page_url }}" target="_blank"
                                    class="text-indigo-600 hover:text-indigo-900 break-all">
                                    {{ $feedback->page_url }}
                                </a>
                            </div>
                        @endif

                        <div class="bg-gray-50 p-4 rounded-md text-gray-900 whitespace-pre-wrap">
                            {{ $feedback->message }}
                        </div>

                        @if($feedback->user)
                            <div class="mt-6 border-t border-gray-200 pt-6">
                                <h4 class="text-md font-medium text-gray-900 mb-3">User Information</h4>
                                <div class="bg-white border border-gray-200 rounded-md p-4">
                                    <p class="mb-1 text-sm"><strong class="font-medium text-gray-700">Name:</strong>
                                        {{ $feedback->user->name }}</p>
                                    <p class="mb-1 text-sm"><strong class="font-medium text-gray-700">Email:</strong>
                                        {{ $feedback->user->email }}</p>
                                    <p class="mb-0 text-sm">
                                        <strong class="font-medium text-gray-700">Member since:</strong>
                                        {{ $feedback->user->created_at->format('M j, Y') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Update Status</h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('admin.feedback.update', $feedback) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ $feedback->status === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="admin_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="admin_notes" id="admin_notes" rows="5"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Add internal notes here...">{{ $feedback->metadata['admin_notes'] ?? '' }}</textarea>
                            </div>

                            <button type="submit"
                                class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Actions</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @if($feedback->user)
                                <a href="mailto:{{ $feedback->user->email }}"
                                    class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Email User
                                </a>
                            @endif

                            <form action="{{ route('admin.feedback.destroy', $feedback) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this feedback?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete Feedback
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection