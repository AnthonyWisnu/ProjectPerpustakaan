@extends('layouts.app')

@section('title', 'Book Catalog')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Book Catalog</h1>
    <p class="text-gray-600 mt-2">Browse our collection of books</p>
</div>

<!-- Search and Filter -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" action="{{ route('member.books.index') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                    Search
                </button>
                <a href="{{ route('member.books.index') }}" class="text-gray-600 hover:text-gray-800">
                    Reset
                </a>
            </div>

            <!-- Sort -->
            <div class="flex items-center space-x-2">
                <label for="sort" class="text-sm text-gray-700">Sort by:</label>
                <select
                    id="sort"
                    name="sort"
                    onchange="this.form.submit()"
                    class="px-3 py-1 border border-gray-300 rounded text-sm"
                >
                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Title</option>
                    <option value="author" {{ request('sort') == 'author' ? 'selected' : '' }}>Author</option>
                    <option value="publication_year" {{ request('sort') == 'publication_year' ? 'selected' : '' }}>Year</option>
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Newest</option>
                </select>
            </div>
        </div>
    </form>
</div>

<!-- Results Count -->
<div class="mb-4">
    <p class="text-gray-600">
        Showing {{ $books->firstItem() ?? 0 }} to {{ $books->lastItem() ?? 0 }} of {{ $books->total() }} books
    </p>
</div>

<!-- Book Grid -->
@if($books->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($books as $book)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <!-- Book Cover Placeholder -->
                <div class="h-48 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                    <svg class="w-20 h-20 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>

                <div class="p-4">
                    <!-- Category Badge -->
                    @if($book->category)
                        <span class="inline-block px-2 py-1 text-xs font-semibold text-blue-600 bg-blue-100 rounded mb-2">
                            {{ $book->category->name }}
                        </span>
                    @endif

                    <!-- Title -->
                    <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-2">
                        {{ $book->title }}
                    </h3>

                    <!-- Author -->
                    <p class="text-gray-600 text-sm mb-2">by {{ $book->author }}</p>

                    <!-- Publisher & Year -->
                    <p class="text-gray-500 text-xs mb-3">
                        {{ $book->publisher }} " {{ $book->publication_year }}
                    </p>

                    <!-- Stock -->
                    <div class="mb-3">
                        @if($book->available_stock > 0)
                            <span class="text-green-600 text-sm font-medium">
                                {{ $book->available_stock }} available
                            </span>
                        @else
                            <span class="text-red-600 text-sm font-medium">
                                Out of stock
                            </span>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-2">
                        <a
                            href="{{ route('member.books.show', $book->id) }}"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 text-center py-2 rounded text-sm font-medium"
                        >
                            View Details
                        </a>

                        @if($book->available_stock > 0)
                            @if(in_array($book->id, $cartBookIds))
                                <button
                                    disabled
                                    class="flex-1 bg-gray-300 text-gray-500 py-2 rounded text-sm font-medium cursor-not-allowed"
                                >
                                    In Cart
                                </button>
                            @else
                                <form method="POST" action="{{ route('member.cart.store') }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <button
                                        type="submit"
                                        class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded text-sm font-medium"
                                    >
                                        Add to Cart
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $books->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M12 12h.01M12 12h.01M12 21a9 9 0 100-18 9 9 0 000 18z" />
        </svg>
        <p class="text-gray-600 text-lg">No books found</p>
        <p class="text-gray-500 text-sm mt-2">Try adjusting your search or filters</p>
    </div>
@endif
@endsection
