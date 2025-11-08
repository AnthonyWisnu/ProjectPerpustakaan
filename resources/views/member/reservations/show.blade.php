@extends('layouts.app')

@section('title', 'Reservation Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('member.reservations.index') }}" class="text-blue-500 hover:text-blue-700">
        &larr; Back to My Reservations
    </a>
</div>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $reservation->reservation_code }}</h1>
            <p class="text-gray-600 mt-2">Reservation details and pickup information</p>
        </div>

        @if($reservation->status == 'pending')
            <span class="px-4 py-2 text-sm font-semibold text-yellow-700 bg-yellow-100 rounded-full">
                Pending
            </span>
        @elseif($reservation->status == 'ready')
            <span class="px-4 py-2 text-sm font-semibold text-green-700 bg-green-100 rounded-full">
                Ready for Pickup
            </span>
        @elseif($reservation->status == 'picked_up')
            <span class="px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-100 rounded-full">
                Picked Up
            </span>
        @elseif($reservation->status == 'cancelled')
            <span class="px-4 py-2 text-sm font-semibold text-red-700 bg-red-100 rounded-full">
                Cancelled
            </span>
        @elseif($reservation->status == 'expired')
            <span class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-full">
                Expired
            </span>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Reservation Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Reservation Information</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Reservation Code</p>
                    <p class="font-medium text-gray-800">{{ $reservation->reservation_code }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="font-medium text-gray-800 capitalize">{{ $reservation->status }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Reserved At</p>
                    <p class="font-medium text-gray-800">{{ $reservation->reserved_at->format('d M Y, H:i') }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Total Books</p>
                    <p class="font-medium text-gray-800">{{ $reservation->total_books }}</p>
                </div>

                @if(in_array($reservation->status, ['pending', 'ready']))
                    <div>
                        <p class="text-sm text-gray-600">Valid Until</p>
                        <p class="font-medium {{ $reservation->isExpired() ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $reservation->expired_at->format('d M Y, H:i') }}
                        </p>
                        @if(!$reservation->isExpired())
                            <p class="text-xs text-gray-500">{{ $reservation->expired_at->diffForHumans() }}</p>
                        @else
                            <p class="text-xs text-red-600">Expired</p>
                        @endif
                    </div>
                @endif

                @if($reservation->picked_up_at)
                    <div>
                        <p class="text-sm text-gray-600">Picked Up At</p>
                        <p class="font-medium text-gray-800">{{ $reservation->picked_up_at->format('d M Y, H:i') }}</p>
                    </div>
                @endif

                @if($reservation->cancelled_at)
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Cancelled At</p>
                        <p class="font-medium text-gray-800">{{ $reservation->cancelled_at->format('d M Y, H:i') }}</p>
                        @if($reservation->cancellation_reason)
                            <p class="text-sm text-gray-600 mt-1">Reason: {{ $reservation->cancellation_reason }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Reserved Books -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Reserved Books</h2>

            <div class="space-y-4">
                @foreach($reservation->items as $item)
                    <div class="flex items-start p-4 {{ !$loop->last ? 'border-b' : '' }}">
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

                            <h3 class="font-bold text-gray-800">
                                <a href="{{ route('member.books.show', $item->book->id) }}" class="hover:text-blue-600">
                                    {{ $item->book->title }}
                                </a>
                            </h3>

                            <p class="text-gray-600 text-sm">by {{ $item->book->author }}</p>

                            <div class="flex items-center space-x-4 text-xs text-gray-500 mt-2">
                                <span>{{ $item->book->publisher }}</span>
                                <span>"</span>
                                <span>{{ $item->book->publication_year }}</span>
                                @if($item->book->isbn)
                                    <span>"</span>
                                    <span>ISBN: {{ $item->book->isbn }}</span>
                                @endif
                            </div>

                            @if($item->book->shelf_location)
                                <p class="text-sm text-blue-600 mt-2">
                                    Shelf Location: {{ $item->book->shelf_location }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- QR Code (Placeholder) -->
        @if(in_array($reservation->status, ['ready', 'pending']))
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Pickup QR Code</h3>

                <!-- QR Code Placeholder -->
                <div class="w-48 h-48 bg-gray-100 mx-auto mb-4 flex items-center justify-center rounded">
                    <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>

                <p class="text-sm text-gray-600">
                    Show this QR code to the librarian when picking up your books
                </p>
            </div>
        @endif

        <!-- Actions -->
        @if(in_array($reservation->status, ['pending', 'ready']))
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Actions</h3>

                <form method="POST" action="{{ route('member.reservations.cancel', $reservation->id) }}" onsubmit="return confirm('Are you sure you want to cancel this reservation?')">
                    @csrf
                    @method('POST')
                    <button
                        type="submit"
                        class="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-lg font-medium"
                    >
                        Cancel Reservation
                    </button>
                </form>
            </div>
        @endif

        <!-- Important Notes -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold text-blue-800 mb-3">Important Notes</h3>
            <ul class="text-sm text-blue-700 space-y-2">
                @if(in_array($reservation->status, ['pending', 'ready']))
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Pick up before expiration time</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Bring your QR code or reservation number</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Return books within 7 days to avoid fines</span>
                    </li>
                @else
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>This reservation is {{ $reservation->status }}</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection
