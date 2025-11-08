@extends('layouts.app')

@section('title', $book->title)

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('member.books.index') }}" class="text-blue-500 hover:text-blue-700">
        &larr; Back to Catalog
    </a>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="md:flex">
        <!-- Book Cover -->
        <div class="md:w-1/3 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center p-8">
            <svg class="w-48 h-48 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>

        <!-- Book Details -->
        <div class="md:w-2/3 p-8">
            <!-- Category Badge -->
            @if($book->category)
                <span class="inline-block px-3 py-1 text-sm font-semibold text-blue-600 bg-blue-100 rounded mb-4">
                    {{ $book->category->name }}
                </span>
            @endif

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $book->title }}</h1>

            <!-- Author -->
            <p class="text-xl text-gray-600 mb-4">by {{ $book->author }}</p>

            <!-- Stock Status -->
            <div class="mb-6">
                @if($book->available_stock > 0)
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-green-600 font-medium">
                            Available ({{ $book->available_stock }} of {{ $book->total_stock }} in stock)
                        </span>
                    </div>
                @else
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        <span class="text-red-600 font-medium">Out of Stock</span>
                    </div>
                @endif
            </div>

            <!-- Book Information -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-600">ISBN</p>
                    <p class="font-medium text-gray-800">{{ $book->isbn ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Publisher</p>
                    <p class="font-medium text-gray-800">{{ $book->publisher }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Publication Year</p>
                    <p class="font-medium text-gray-800">{{ $book->publication_year }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Pages</p>
                    <p class="font-medium text-gray-800">{{ $book->pages ?? 'N/A' }}</p>
                </div>

                @if($book->shelf_location)
                    <div>
                        <p class="text-sm text-gray-600">Shelf Location</p>
                        <p class="font-medium text-gray-800">{{ $book->shelf_location }}</p>
                    </div>
                @endif

                @if($book->language)
                    <div>
                        <p class="text-sm text-gray-600">Language</p>
                        <p class="font-medium text-gray-800">{{ $book->language }}</p>
                    </div>
                @endif
            </div>

            <!-- Description -->
            @if($book->description)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Description</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $book->description }}</p>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex space-x-4">
                @if($book->available_stock > 0)
                    @if($isInCart)
                        <a
                            href="{{ route('member.cart.index') }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium"
                        >
                            Already in Cart - View Cart
                        </a>
                    @else
                        <form method="POST" action="{{ route('member.cart.store') }}">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <button
                                type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium"
                            >
                                Add to Cart
                            </button>
                        </form>
                    @endif
                @else
                    <button
                        disabled
                        class="bg-gray-300 text-gray-500 px-6 py-3 rounded-lg font-medium cursor-not-allowed"
                    >
                        Out of Stock
                    </button>
                @endif

                <a
                    href="{{ route('member.books.index') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-medium"
                >
                    Browse More Books
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
