@extends('layouts.app')

@section('title', 'My Cart')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Shopping Cart</h1>
    <p class="text-gray-600 mt-2">Review your selected books before checkout</p>
</div>

@if($cartItems->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cart Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md">
                @foreach($cartItems as $item)
                    <div class="p-6 {{ !$loop->last ? 'border-b' : '' }}">
                        <div class="flex items-start">
                            <!-- Book Icon -->
                            <div class="w-20 h-28 bg-gradient-to-br from-blue-100 to-blue-200 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>

                            <!-- Book Details -->
                            <div class="ml-6 flex-1">
                                @if($item->book->category)
                                    <span class="inline-block px-2 py-1 text-xs font-semibold text-blue-600 bg-blue-100 rounded mb-2">
                                        {{ $item->book->category->name }}
                                    </span>
                                @endif

                                <h3 class="font-bold text-lg text-gray-800 mb-1">
                                    <a href="{{ route('member.books.show', $item->book->id) }}" class="hover:text-blue-600">
                                        {{ $item->book->title }}
                                    </a>
                                </h3>

                                <p class="text-gray-600 text-sm mb-2">by {{ $item->book->author }}</p>

                                <div class="flex items-center space-x-4 text-xs text-gray-500 mb-3">
                                    <span>{{ $item->book->publisher }}</span>
                                    <span>"</span>
                                    <span>{{ $item->book->publication_year }}</span>
                                    @if($item->book->isbn)
                                        <span>"</span>
                                        <span>ISBN: {{ $item->book->isbn }}</span>
                                    @endif
                                </div>

                                <!-- Stock Status -->
                                @if($item->book->available_stock > 0)
                                    <div class="flex items-center text-sm">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                        <span class="text-green-600">In Stock ({{ $item->book->available_stock }} available)</span>
                                    </div>
                                @else
                                    <div class="flex items-center text-sm">
                                        <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                        <span class="text-red-600">Out of Stock</span>
                                    </div>
                                @endif

                                <p class="text-xs text-gray-500 mt-2">
                                    Added {{ $item->added_at->diffForHumans() }}
                                </p>
                            </div>

                            <!-- Remove Button -->
                            <div class="ml-4">
                                <form method="POST" action="{{ route('member.cart.destroy', $item->id) }}" onsubmit="return confirm('Remove this book from cart?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Clear Cart Button -->
            <div class="mt-4">
                <form method="POST" action="{{ route('member.cart.clear') }}" onsubmit="return confirm('Clear all items from cart?')">
                    @csrf
                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                        Clear Cart
                    </button>
                </form>
            </div>
        </div>

        <!-- Cart Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Cart Summary</h2>

                <div class="space-y-3 mb-6">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Books</span>
                        <span class="font-semibold text-gray-800">{{ $cartCount }}</span>
                    </div>

                    <div class="border-t pt-3">
                        <p class="text-sm text-gray-500">
                            You can reserve up to 3 books at a time
                        </p>
                    </div>
                </div>

                <!-- Checkout Button -->
                <a
                    href="{{ route('member.cart.checkout') }}"
                    class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center py-3 rounded-lg font-medium"
                >
                    Proceed to Checkout
                </a>

                <a
                    href="{{ route('member.books.index') }}"
                    class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-center py-3 rounded-lg font-medium mt-3"
                >
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
@else
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Your cart is empty</h2>
        <p class="text-gray-600 mb-6">Browse our collection and add books to your cart</p>
        <a
            href="{{ route('member.books.index') }}"
            class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium"
        >
            Browse Books
        </a>
    </div>
@endif
@endsection
