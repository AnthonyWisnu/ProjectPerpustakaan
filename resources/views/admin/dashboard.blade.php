@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
    <p class="text-gray-600 mt-2">Library management overview</p>
</div>

<!-- Main Statistics Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Books -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Books</p>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['total_books'] }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $stats['total_available'] }} available</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Members -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Members</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['total_members'] }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $stats['active_members'] }} active</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Active Loans -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Active Loans</p>
                <p class="text-3xl font-bold text-purple-600">{{ $loanStats['active_loans'] }}</p>
                <p class="text-sm text-red-500 mt-1">{{ $loanStats['overdue_loans'] }} overdue</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Fines -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total Fines</p>
                <p class="text-3xl font-bold text-orange-600">Rp {{ number_format($loanStats['total_fines'], 0, ',', '.') }}</p>
                <p class="text-sm text-gray-500 mt-1">Rp {{ number_format($loanStats['unpaid_fines'], 0, ',', '.') }} unpaid</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Reservations Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <p class="text-yellow-800 text-sm font-medium">Pending Reservations</p>
        <p class="text-2xl font-bold text-yellow-600">{{ $reservationStats['pending_reservations'] }}</p>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <p class="text-green-800 text-sm font-medium">Ready for Pickup</p>
        <p class="text-2xl font-bold text-green-600">{{ $reservationStats['ready_reservations'] }}</p>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-800 text-sm font-medium">Expired Reservations</p>
        <p class="text-2xl font-bold text-red-600">{{ $reservationStats['expired_reservations'] }}</p>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-blue-800 text-sm font-medium">Total Reservations</p>
        <p class="text-2xl font-bold text-blue-600">{{ $reservationStats['total_reservations'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Overdue Loans -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">Overdue Loans</h2>
                <a href="{{ route('admin.loans.index', ['status' => 'overdue']) }}" class="text-sm text-blue-600 hover:text-blue-800">
                    View All &rarr;
                </a>
            </div>
        </div>
        <div class="p-6">
            @if($overdueLoans->count() > 0)
                <div class="space-y-3">
                    @foreach($overdueLoans as $loan)
                        <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">{{ $loan->book->title }}</p>
                                <p class="text-sm text-gray-600">{{ $loan->user->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-red-600">
                                    {{ $loan->getDaysOverdue() }} days overdue
                                </p>
                                <p class="text-xs text-gray-500">Due: {{ $loan->due_date->format('d M Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No overdue loans</p>
            @endif
        </div>
    </div>

    <!-- Popular Books -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800">Popular Books (Last 30 Days)</h2>
        </div>
        <div class="p-6">
            @if($popularBooks->count() > 0)
                <div class="space-y-3">
                    @foreach($popularBooks as $book)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">{{ $book->title }}</p>
                                <p class="text-sm text-gray-600">{{ $book->author }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-blue-600">
                                    {{ $book->loans_count }} loans
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No data available</p>
            @endif
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Loans -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">Recent Loans</h2>
                <a href="{{ route('admin.loans.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    View All &rarr;
                </a>
            </div>
        </div>
        <div class="p-6">
            @if($recentLoans->count() > 0)
                <div class="space-y-3">
                    @foreach($recentLoans as $loan)
                        <div class="flex items-start p-3 bg-gray-50 rounded">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">{{ $loan->book->title }}</p>
                                <p class="text-sm text-gray-600">{{ $loan->user->name }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $loan->created_at->diffForHumans() }} • Due: {{ $loan->due_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No recent loans</p>
            @endif
        </div>
    </div>

    <!-- Recent Reservations -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800">Recent Reservations</h2>
                <a href="{{ route('admin.reservations.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    View All &rarr;
                </a>
            </div>
        </div>
        <div class="p-6">
            @if($recentReservations->count() > 0)
                <div class="space-y-3">
                    @foreach($recentReservations as $reservation)
                        <div class="flex items-start p-3 bg-gray-50 rounded">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">{{ $reservation->reservation_code }}</p>
                                <p class="text-sm text-gray-600">{{ $reservation->user->name }} • {{ $reservation->total_books }} book(s)</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-xs text-gray-500">{{ $reservation->created_at->diffForHumans() }}</p>
                                    @if($reservation->status == 'pending')
                                        <span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded">Pending</span>
                                    @elseif($reservation->status == 'ready')
                                        <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded">Ready</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No recent reservations</p>
            @endif
        </div>
    </div>
</div>
@endsection
