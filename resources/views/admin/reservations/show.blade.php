@extends('layouts.app')

@section('title', 'Reservation Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.reservations.index') }}" class="text-blue-500 hover:text-blue-700">
        &larr; Back to Reservations
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Reservation Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Reservation Info Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Reservation {{ $reservation->reservation_code }}</h1>
                    <p class="text-gray-600 mt-1">{{ $reservation->user->name }} ({{ $reservation->user->email }})</p>
                    @if($reservation->user->member_number)
                        <p class="text-sm text-gray-500 mt-1 font-mono">Member #{{ $reservation->user->member_number }}</p>
                    @endif
                </div>
                <div>
                    @if($reservation->status === 'pending')
                        <span class="px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded">
                            Pending
                        </span>
                    @elseif($reservation->status === 'ready')
                        <span class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded">
                            Ready for Pickup
                        </span>
                    @elseif($reservation->status === 'picked_up')
                        <span class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded">
                            Picked Up
                        </span>
                    @elseif($reservation->status === 'cancelled')
                        <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded">
                            Cancelled
                        </span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded">
                            Expired
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6 border-t">
                <div>
                    <p class="text-xs text-gray-500">Reserved At</p>
                    <p class="text-sm font-medium text-gray-900">{{ $reservation->reserved_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Expires At</p>
                    <p class="text-sm font-medium text-gray-900">{{ $reservation->expired_at->format('d M Y, H:i') }}</p>
                    @if(in_array($reservation->status, ['pending', 'ready']))
                        @php
                            $hoursLeft = now()->diffInHours($reservation->expired_at, false);
                        @endphp
                        @if($hoursLeft > 0)
                            <p class="text-xs mt-1 {{ $hoursLeft < 6 ? 'text-orange-600' : 'text-gray-500' }}">
                                {{ $hoursLeft }} hours remaining
                            </p>
                        @else
                            <p class="text-xs mt-1 text-red-600 font-medium">Expired!</p>
                        @endif
                    @endif
                </div>
                @if($reservation->picked_up_at)
                    <div>
                        <p class="text-xs text-gray-500">Picked Up At</p>
                        <p class="text-sm font-medium text-gray-900">{{ $reservation->picked_up_at->format('d M Y, H:i') }}</p>
                    </div>
                @endif
                @if($reservation->cancelled_at)
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500">Cancellation Reason</p>
                        <p class="text-sm font-medium text-gray-900">{{ $reservation->cancellation_reason ?? 'No reason provided' }}</p>
                        <p class="text-xs text-gray-500 mt-1">Cancelled at {{ $reservation->cancelled_at->format('d M Y, H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Reserved Books -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                Reserved Books ({{ $reservation->items->count() }})
            </h3>

            <div class="space-y-4">
                @foreach($reservation->items as $item)
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">{{ $item->book->title }}</h4>
                            <p class="text-sm text-gray-600 mt-1">by {{ $item->book->author }}</p>
                            <div class="flex items-center gap-3 mt-2">
                                @if($item->book->isbn)
                                    <p class="text-xs text-gray-500">ISBN: {{ $item->book->isbn }}</p>
                                @endif
                                @if($item->book->shelf_location)
                                    <p class="text-xs text-gray-500">Shelf: {{ $item->book->shelf_location }}</p>
                                @endif
                            </div>
                        </div>
                        <div>
                            @if($item->status === 'available')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                    Available
                                </span>
                            @elseif($item->status === 'picked_up')
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                                    Picked Up
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Created Loans (if picked up) -->
        @if($reservation->status === 'picked_up' && $reservation->loans && $reservation->loans->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Created Loans</h3>
                <div class="space-y-2">
                    @foreach($reservation->loans as $loan)
                        <div class="flex items-center justify-between py-3 border-b last:border-b-0">
                            <div>
                                <p class="font-medium text-gray-900">{{ $loan->book->title }}</p>
                                <p class="text-xs text-gray-500">Loan Code: {{ $loan->loan_code }}</p>
                            </div>
                            <a href="{{ route('admin.loans.show', $loan->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Loan
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Actions Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- QR Code (Placeholder) -->
        @if(in_array($reservation->status, ['pending', 'ready']))
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">QR Code</h3>
                <div class="flex items-center justify-center bg-gray-100 h-48 rounded-lg">
                    <p class="text-gray-400 text-sm">QR Code Placeholder</p>
                </div>
                <p class="text-xs text-gray-500 mt-3 text-center">Member can use this for pickup</p>
            </div>
        @endif

        <!-- Actions -->
        @if($reservation->status === 'pending')
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Actions</h3>

                <!-- Mark as Ready -->
                <form method="POST" action="{{ route('admin.reservations.markReady', $reservation->id) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm font-medium">
                        Mark as Ready
                    </button>
                </form>

                <!-- Process Pickup -->
                <form method="POST" action="{{ route('admin.reservations.processPickup', $reservation->id) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium">
                        Process Pickup
                    </button>
                </form>

                <!-- Cancel -->
                <button
                    type="button"
                    onclick="document.getElementById('cancelModal').classList.remove('hidden')"
                    class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium"
                >
                    Cancel Reservation
                </button>
            </div>
        @elseif($reservation->status === 'ready')
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Actions</h3>

                <!-- Process Pickup -->
                <form method="POST" action="{{ route('admin.reservations.processPickup', $reservation->id) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium">
                        Process Pickup
                    </button>
                </form>

                <!-- Cancel -->
                <button
                    type="button"
                    onclick="document.getElementById('cancelModal').classList.remove('hidden')"
                    class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium"
                >
                    Cancel Reservation
                </button>
            </div>
        @endif

        <!-- Member Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Member Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Name</p>
                    <p class="text-sm font-medium text-gray-900">{{ $reservation->user->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Email</p>
                    <p class="text-sm text-gray-900">{{ $reservation->user->email }}</p>
                </div>
                @if($reservation->user->member_number)
                    <div>
                        <p class="text-xs text-gray-500">Member Number</p>
                        <p class="text-sm font-mono text-gray-900">{{ $reservation->user->member_number }}</p>
                    </div>
                @endif
                <div class="pt-3 border-t">
                    <a href="{{ route('admin.users.show', $reservation->user->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View Member Profile ’
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Cancel Reservation</h3>
            <form method="POST" action="{{ route('admin.reservations.cancel', $reservation->id) }}">
                @csrf
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                    <textarea
                        id="reason"
                        name="reason"
                        rows="3"
                        placeholder="Enter cancellation reason..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <button
                        type="button"
                        onclick="document.getElementById('cancelModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                    >
                        Close
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg"
                    >
                        Cancel Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
