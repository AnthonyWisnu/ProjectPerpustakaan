@extends('layouts.app')

@section('title', 'Manage Loans')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manage Loans</h1>
    <p class="text-gray-600 mt-2">Track and manage book loans</p>
</div>

<!-- Status Tabs -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.loans.index') }}"
           class="px-4 py-2 rounded-lg {{ !request('status') ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            All ({{ $statusCounts['all'] }})
        </a>
        <a href="{{ route('admin.loans.index', ['status' => 'active']) }}"
           class="px-4 py-2 rounded-lg {{ request('status') === 'active' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Active ({{ $statusCounts['active'] }})
        </a>
        <a href="{{ route('admin.loans.index', ['status' => 'overdue']) }}"
           class="px-4 py-2 rounded-lg {{ request('status') === 'overdue' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Overdue ({{ $statusCounts['overdue'] }})
        </a>
        <a href="{{ route('admin.loans.index', ['status' => 'returned']) }}"
           class="px-4 py-2 rounded-lg {{ request('status') === 'returned' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Returned ({{ $statusCounts['returned'] }})
        </a>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('admin.loans.index') }}" class="mt-4">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <div class="flex gap-2">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search by loan code, member name, or book title..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.loans.index', request()->except('search')) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Loans Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    @if($loans->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($loans as $loan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-mono text-sm font-semibold text-gray-900">{{ $loan->loan_code }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $loan->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $loan->user->email }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $loan->book->title }}</p>
                                    <p class="text-xs text-gray-500">by {{ $loan->book->author }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $loan->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ $loan->due_date->format('d M Y') }}</p>
                                @if(!$loan->returned_at)
                                    @php
                                        $daysUntilDue = now()->diffInDays($loan->due_date, false);
                                    @endphp
                                    @if($daysUntilDue < 0)
                                        <p class="text-xs text-red-600 font-medium">{{ abs($daysUntilDue) }} days overdue</p>
                                    @elseif($daysUntilDue <= 3)
                                        <p class="text-xs text-orange-600">Due in {{ $daysUntilDue }} days</p>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($loan->returned_at)
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">
                                        Returned
                                    </span>
                                @elseif($loan->isOverdue())
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                                        Overdue
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                        Active
                                    </span>
                                @endif

                                @if($loan->fine_amount > 0)
                                    <div class="mt-1">
                                        @if($loan->fine_paid)
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                                Fine Paid
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded">
                                                Fine: Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.loans.show', $loan->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
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
            {{ $loans->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-gray-600 text-lg">No loans found</p>
            <p class="text-gray-500 text-sm mt-2">
                @if(request('search'))
                    Try adjusting your search
                @else
                    Loans will appear here when books are borrowed
                @endif
            </p>
        </div>
    @endif
</div>
@endsection
