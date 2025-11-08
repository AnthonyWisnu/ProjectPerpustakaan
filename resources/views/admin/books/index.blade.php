@extends('layouts.app')

@section('title', 'Manage Books')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manage Books</h1>
        <p class="text-gray-600 mt-2">Add, edit, and manage library books</p>
    </div>
    <a href="{{ route('admin.books.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium">
        + Add New Book
    </a>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" action="{{ route('admin.books.index') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by title, author, or ISBN..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select
                    id="category_id"
                    name="category_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Availability Filter -->
            <div>
                <label for="availability" class="block text-sm font-medium text-gray-700 mb-2">Availability</label>
                <select
                    id="availability"
                    name="availability"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All</option>
                    <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="out_of_stock" {{ request('availability') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                Filter
            </button>
            <a href="{{ route('admin.books.index') }}" class="text-gray-600 hover:text-gray-800">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Books Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    @if($books->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Publisher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($books as $book)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $book->title }}</p>
                                    <p class="text-sm text-gray-600">by {{ $book->author }}</p>
                                    @if($book->isbn)
                                        <p class="text-xs text-gray-500 mt-1">ISBN: {{ $book->isbn }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($book->category)
                                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                                        {{ $book->category->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ $book->publisher }}</p>
                                <p class="text-xs text-gray-500">{{ $book->publication_year }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $book->available_stock }} / {{ $book->total_stock }}
                                    </p>
                                    <p class="text-xs text-gray-500">Available / Total</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($book->available_stock > 0)
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                        Available
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                                        Out of Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.books.show', $book->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View
                                </a>
                                <a href="{{ route('admin.books.edit', $book->id) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.books.destroy', $book->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this book?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $books->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <p class="text-gray-600 text-lg">No books found</p>
            <p class="text-gray-500 text-sm mt-2">Try adjusting your search or filters</p>
            <a href="{{ route('admin.books.create') }}" class="inline-block mt-4 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                Add First Book
            </a>
        </div>
    @endif
</div>
@endsection
