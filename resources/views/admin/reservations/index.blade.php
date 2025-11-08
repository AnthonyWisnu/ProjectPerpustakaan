@extends('layouts.app')

@section('title', 'Manage Reservations')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manage Reservations</h1>
        <p class="text-gray-600 mt-2">Track and process book reservations</p>
    </div>
    <form method="POST" action="{{ route('admin.reservations.autoCancelExpired') }}">
        @csrf
        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-medium text-sm">
            Auto-Cancel Expired
        </button>
    </form>
</div>

<!-- Status Tabs -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.reservations.index') }}"
           class="px-4 py-2 rounded-lg {{ !request('status') ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            All ({{ $statusCounts['all'] }})
        </a>
        <a href="{{ route('admin.reservations.index', ['status' => 'pending']) }}"
           class="px-4 py-2 rounded-lg {{ request('status') === 'pending' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Pending ({{ $statusCounts['pending'] }})
        </a>
        <a href="{{ route('admin.reservations.index', ['status' => 'ready']) }}"
           class="px-4 py-2 rounded-lg {{ request('status') === 'ready' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Ready ({{ $statusCounts['ready'] }})
        </a>
        <a href="{{ route('admin.reservations.index', ['status' => 'picked_up']) }}"
           class="px-4 py-2 rounded-lg {{ request('status') === 'picked_up' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Picked Up ({{ $statusCounts['picked_up'] }})
        </a>
        <a href="{{ route('admin.reservations.index', ['status' => 'cancelled']) }}"
           class="px-4 py-2 rounded-lg {{ request('status') === 'cancelled' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Cancelled ({{ $statusCounts['cancelled'] }})
        </a>
        <a href="{{ route('admin.reservations.index', ['status' => 'expired']) }}"
           class="px-4 py-2 rounded-lg {{ request('status') === 'expired' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Expired ({{ $statusCounts['expired'] }})
        </a>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('admin.reservations.index') }}" class="mt-4">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <div class="flex gap-2">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search by reservation code, member name, or email..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.reservations.index', request()->except('search')) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Reservations Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    @if($reservations->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Books</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reserved</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($reservations as $reservation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-mono text-sm font-semibold text-gray-900">{{ $reservation->reservation_code }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $reservation->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $reservation->user->email }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">
                                    {{ $reservation->total_books }} {{ Str::plural('book', $reservation->total_books) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $reservation->reserved_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ $reservation->expired_at->format('d M Y, H:i') }}</p>
                                @if($reservation->status === 'pending' || $reservation->status === 'ready')
                                    @php
                                        $hoursLeft = now()->diffInHours($reservation->expired_at, false);
                                    @endphp
                                    @if($hoursLeft > 0)
                                        <p class="text-xs {{ $hoursLeft < 6 ? 'text-orange-600' : 'text-gray-500' }}">
                                            {{ $hoursLeft }}h left
                                        </p>
                                    @else
                                        <p class="text-xs text-red-600">Expired</p>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($reservation->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">
                                        Pending
                                    </span>
                                @elseif($reservation->status === 'ready')
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                        Ready
                                    </span>
                                @elseif($reservation->status === 'picked_up')
                                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                                        Picked Up
                                    </span>
                                @elseif($reservation->status === 'cancelled')
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">
                                        Expired
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $reservations->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-600 text-lg">No reservations found</p>
            <p class="text-gray-500 text-sm mt-2">
                @if(request('search'))
                    Try adjusting your search
                @else
                    Reservations will appear here when members make them
                @endif
            </p>
        </div>
    @endif
</div>
@endsection
