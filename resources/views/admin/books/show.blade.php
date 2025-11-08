@extends('layouts.app')

@section('title', 'Book Details')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('admin.books.index') }}" class="text-blue-500 hover:text-blue-700">
        &larr; Back to Books
    </a>
    <div class="space-x-2">
        <a href="{{ route('admin.books.edit', $book->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Edit Book
        </a>
        <form method="POST" action="{{ route('admin.books.destroy', $book->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this book?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Delete Book
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Book Details -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $book->title }}</h1>
                <p class="text-lg text-gray-600">by {{ $book->author }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Publisher Information -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Publisher Information</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">Publisher</p>
                            <p class="text-sm font-medium text-gray-900">{{ $book->publisher }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Publication Year</p>
                            <p class="text-sm font-medium text-gray-900">{{ $book->publication_year }}</p>
                        </div>
                        @if($book->isbn)
                            <div>
                                <p class="text-xs text-gray-500">ISBN</p>
                                <p class="text-sm font-medium text-gray-900">{{ $book->isbn }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Book Details -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Book Details</h3>
                    <div class="space-y-2">
                        @if($book->category)
                            <div>
                                <p class="text-xs text-gray-500">Category</p>
                                <span class="inline-block mt-1 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                                    {{ $book->category->name }}
                                </span>
                            </div>
                        @endif
                        @if($book->pages)
                            <div>
                                <p class="text-xs text-gray-500">Pages</p>
                                <p class="text-sm font-medium text-gray-900">{{ $book->pages }}</p>
                            </div>
                        @endif
                        @if($book->language)
                            <div>
                                <p class="text-xs text-gray-500">Language</p>
                                <p class="text-sm font-medium text-gray-900">{{ $book->language }}</p>
                            </div>
                        @endif
                        @if($book->shelf_location)
                            <div>
                                <p class="text-xs text-gray-500">Shelf Location</p>
                                <p class="text-sm font-medium text-gray-900">{{ $book->shelf_location }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($book->description)
                <div class="mt-6 pt-6 border-t">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Description</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $book->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Stock & Status Information -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Stock Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Stock Information</h3>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Total Stock</span>
                    <span class="text-lg font-bold text-gray-900">{{ $book->total_stock }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Available</span>
                    <span class="text-lg font-bold text-green-600">{{ $book->available_stock }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Currently Borrowed</span>
                    <span class="text-lg font-bold text-blue-600">{{ $book->total_stock - $book->available_stock }}</span>
                </div>

                <div class="pt-4 border-t">
                    @if($book->available_stock > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Available
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Out of Stock
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Metadata Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Metadata</h3>

            <div class="space-y-3 text-sm">
                @if($book->barcode)
                    <div>
                        <p class="text-xs text-gray-500">Barcode</p>
                        <p class="font-mono text-gray-900">{{ $book->barcode }}</p>
                    </div>
                @endif

                <div>
                    <p class="text-xs text-gray-500">Added</p>
                    <p class="text-gray-900">{{ $book->created_at->format('d M Y, H:i') }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Last Updated</p>
                    <p class="text-gray-900">{{ $book->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="mt-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>

        @if($book->loans->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($book->loans->take(10) as $loan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $loan->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $loan->user->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $loan->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $loan->due_date->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($loan->returned_at)
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">
                                            Returned
                                        </span>
                                    @elseif($loan->isOverdue())
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                                            Overdue
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.loans.show', $loan->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No loan history available for this book.</p>
        @endif
    </div>
</div>
@endsection
