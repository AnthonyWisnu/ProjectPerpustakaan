@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="mb-6">
    <a href="{{ route('member.cart.index') }}" class="text-blue-500 hover:text-blue-700">
        &larr; Back to Cart
    </a>
</div>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Checkout</h1>
    <p class="text-gray-600 mt-2">Review and confirm your reservation</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Books List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Books to Reserve</h2>

            <div class="space-y-4">
                @foreach($cartItems as $item)
                    <div class="flex items-start pb-4 {{ !$loop->last ? 'border-b' : '' }}">
                        <!-- Book Icon -->
                        <div class="w-16 h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded flex items-center justify-center flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>

                        <!-- Book Details -->
                        <div class="ml-4 flex-1">
                            @if($item->book->category)
                                <span class="inline-block px-2 py-1 text-xs font-semibold text-blue-600 bg-blue-100 rounded mb-1">
                                    {{ $item->book->category->name }}
                                </span>
                            @endif

                            <h3 class="font-bold text-gray-800">{{ $item->book->title }}</h3>
                            <p class="text-gray-600 text-sm">by {{ $item->book->author }}</p>
                            <p class="text-gray-500 text-xs mt-1">
                                {{ $item->book->publisher }} " {{ $item->book->publication_year }}
                            </p>

                            @if($item->book->shelf_location)
                                <p class="text-blue-600 text-xs mt-2">
                                    Shelf: {{ $item->book->shelf_location }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Important Information -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-semibold text-yellow-800 mb-2">Important Information</h3>
                    <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                        <li>Your reservation will be valid for 24 hours</li>
                        <li>Please pick up the books from the library within this period</li>
                        <li>You will receive a confirmation with a QR code for pickup</li>
                        <li>Books not picked up within 24 hours will be automatically returned to stock</li>
                        <li>Standard loan period is 7 days from pickup date</li>
                        <li>Late returns are subject to fines (Rp 1,000 per day per book)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Summary -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Summary</h2>

            <div class="space-y-4 mb-6">
                <div class="flex justify-between pb-3 border-b">
                    <span class="text-gray-600">Total Books</span>
                    <span class="font-bold text-gray-800">{{ $cartItems->count() }}</span>
                </div>

                <div>
                    <p class="text-sm text-gray-600 mb-1">Reservation Valid Until</p>
                    <p class="font-medium text-gray-800">{{ now()->addHours(24)->format('d M Y, H:i') }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600 mb-1">Expected Return Date</p>
                    <p class="font-medium text-gray-800">{{ now()->addDays(7)->format('d M Y') }}</p>
                    <p class="text-xs text-gray-500 mt-1">(7 days from pickup)</p>
                </div>
            </div>

            <!-- Confirm Button -->
            <form method="POST" action="{{ route('member.cart.processCheckout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-medium mb-3"
                >
                    Confirm Reservation
                </button>
            </form>

            <a
                href="{{ route('member.cart.index') }}"
                class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-center py-3 rounded-lg font-medium"
            >
                Back to Cart
            </a>

            <!-- Terms Notice -->
            <div class="mt-6 p-4 bg-gray-50 rounded">
                <p class="text-xs text-gray-600 leading-relaxed">
                    By confirming this reservation, you agree to pick up the books within 24 hours and return them by the due date to avoid fines.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
