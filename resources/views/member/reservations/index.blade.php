@extends('layouts.app')

@section('title', 'My Reservations')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">My Reservations</h1>
    <p class="text-gray-600 mt-2">Track your book reservations</p>
</div>

<!-- Status Filter Tabs -->
<div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
    <div class="flex overflow-x-auto">
        <a
            href="{{ route('member.reservations.index', ['status' => 'all']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'all' ? 'border-blue-500 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            All
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'all' ? 'bg-blue-100' : 'bg-gray-100' }}">
                {{ $statusCounts['all'] }}
            </span>
        </a>

        <a
            href="{{ route('member.reservations.index', ['status' => 'pending']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'pending' ? 'border-yellow-500 text-yellow-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            Pending
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'pending' ? 'bg-yellow-100' : 'bg-gray-100' }}">
                {{ $statusCounts['pending'] }}
            </span>
        </a>

        <a
            href="{{ route('member.reservations.index', ['status' => 'ready']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'ready' ? 'border-green-500 text-green-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            Ready
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'ready' ? 'bg-green-100' : 'bg-gray-100' }}">
                {{ $statusCounts['ready'] }}
            </span>
        </a>

        <a
            href="{{ route('member.reservations.index', ['status' => 'picked_up']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'picked_up' ? 'border-blue-500 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            Picked Up
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'picked_up' ? 'bg-blue-100' : 'bg-gray-100' }}">
                {{ $statusCounts['picked_up'] }}
            </span>
        </a>

        <a
            href="{{ route('member.reservations.index', ['status' => 'cancelled']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'cancelled' ? 'border-red-500 text-red-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            Cancelled
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'cancelled' ? 'bg-red-100' : 'bg-gray-100' }}">
                {{ $statusCounts['cancelled'] }}
            </span>
        </a>

        <a
            href="{{ route('member.reservations.index', ['status' => 'expired']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'expired' ? 'border-gray-500 text-gray-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            Expired
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'expired' ? 'bg-gray-100' : 'bg-gray-100' }}">
                {{ $statusCounts['expired'] }}
            </span>
        </a>
    </div>
</div>

<!-- Reservations List -->
@if($reservations->count() > 0)
    <div class="space-y-4">
        @foreach($reservations as $reservation)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="flex items-center space-x-3 mb-2">
                                <h3 class="font-bold text-lg text-gray-800">
                                    {{ $reservation->reservation_code }}
                                </h3>

                                @if($reservation->status == 'pending')
                                    <span class="px-3 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full">
                                        Pending
                                    </span>
                                @elseif($reservation->status == 'ready')
                                    <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                        Ready for Pickup
                                    </span>
                                @elseif($reservation->status == 'picked_up')
                                    <span class="px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">
                                        Picked Up
                                    </span>
                                @elseif($reservation->status == 'cancelled')
                                    <span class="px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                        Cancelled
                                    </span>
                                @elseif($reservation->status == 'expired')
                                    <span class="px-3 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">
                                        Expired
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <span>{{ $reservation->total_books }} book(s)</span>
                                <span>"</span>
                                <span>Reserved {{ $reservation->reserved_at->format('d M Y, H:i') }}</span>
                            </div>

                            @if(in_array($reservation->status, ['pending', 'ready']))
                                <div class="mt-2">
                                    @if($reservation->isExpired())
                                        <p class="text-sm text-red-600 font-medium">
                                            Expired
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-600">
                                            Valid until: <span class="font-medium">{{ $reservation->expired_at->format('d M Y, H:i') }}</span>
                                            <span class="text-gray-500">({{ $reservation->expired_at->diffForHumans() }})</span>
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <a
                            href="{{ route('member.reservations.show', $reservation->id) }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium"
                        >
                            View Details
                        </a>
                    </div>

                    <!-- Books in Reservation -->
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600 mb-2">Books:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($reservation->items->take(3) as $item)
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm">
                                    {{ $item->book->title }}
                                </span>
                            @endforeach
                            @if($reservation->items->count() > 3)
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm">
                                    +{{ $reservation->items->count() - 3 }} more
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $reservations->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">No reservations found</h2>
        <p class="text-gray-600 mb-6">You don't have any {{ $status != 'all' ? $status : '' }} reservations yet</p>
        <a
            href="{{ route('member.books.index') }}"
            class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium"
        >
            Browse Books
        </a>
    </div>
@endif
@endsection
