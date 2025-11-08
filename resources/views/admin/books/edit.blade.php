@extends('layouts.app')

@section('title', 'Edit Book')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.books.index') }}" class="text-blue-500 hover:text-blue-700">
        &larr; Back to Books
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Book</h1>

    <form method="POST" action="{{ route('admin.books.update', $book->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Title -->
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Title <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $book->title) }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                >
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Author -->
            <div>
                <label for="author" class="block text-sm font-medium text-gray-700 mb-2">
                    Author <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="author"
                    name="author"
                    value="{{ old('author', $book->author) }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('author') border-red-500 @enderror"
                >
                @error('author')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Publisher -->
            <div>
                <label for="publisher" class="block text-sm font-medium text-gray-700 mb-2">
                    Publisher <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="publisher"
                    name="publisher"
                    value="{{ old('publisher', $book->publisher) }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('publisher') border-red-500 @enderror"
                >
                @error('publisher')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- ISBN -->
            <div>
                <label for="isbn" class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                <input
                    type="text"
                    id="isbn"
                    name="isbn"
                    value="{{ old('isbn', $book->isbn) }}"
                    maxlength="20"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('isbn') border-red-500 @enderror"
                >
                @error('isbn')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Publication Year -->
            <div>
                <label for="publication_year" class="block text-sm font-medium text-gray-700 mb-2">
                    Publication Year <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    id="publication_year"
                    name="publication_year"
                    value="{{ old('publication_year', $book->publication_year) }}"
                    min="1900"
                    max="{{ date('Y') + 1 }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('publication_year') border-red-500 @enderror"
                >
                @error('publication_year')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select
                    id="category_id"
                    name="category_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('category_id') border-red-500 @enderror"
                >
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pages -->
            <div>
                <label for="pages" class="block text-sm font-medium text-gray-700 mb-2">Pages</label>
                <input
                    type="number"
                    id="pages"
                    name="pages"
                    value="{{ old('pages', $book->pages) }}"
                    min="1"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('pages') border-red-500 @enderror"
                >
                @error('pages')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Language -->
            <div>
                <label for="language" class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                <input
                    type="text"
                    id="language"
                    name="language"
                    value="{{ old('language', $book->language) }}"
                    maxlength="50"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('language') border-red-500 @enderror"
                >
                @error('language')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Total Stock -->
            <div>
                <label for="total_stock" class="block text-sm font-medium text-gray-700 mb-2">
                    Total Stock <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    id="total_stock"
                    name="total_stock"
                    value="{{ old('total_stock', $book->total_stock) }}"
                    min="0"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('total_stock') border-red-500 @enderror"
                >
                @error('total_stock')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    Currently: {{ $book->available_stock }} available / {{ $book->total_stock }} total
                </p>
            </div>

            <!-- Shelf Location -->
            <div>
                <label for="shelf_location" class="block text-sm font-medium text-gray-700 mb-2">Shelf Location</label>
                <input
                    type="text"
                    id="shelf_location"
                    name="shelf_location"
                    value="{{ old('shelf_location', $book->shelf_location) }}"
                    maxlength="50"
                    placeholder="e.g., A-01, B-12"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('shelf_location') border-red-500 @enderror"
                >
                @error('shelf_location')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                >{{ old('description', $book->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end space-x-4 mt-6 pt-6 border-t">
            <a href="{{ route('admin.books.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium">
                Update Book
            </button>
        </div>
    </form>
</div>
@endsection
