@extends('layouts.app')

@section('title', 'My Loans')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">My Loans</h1>
    <p class="text-gray-600 mt-2">Track your borrowed books and due dates</p>
</div>

<!-- Status Filter Tabs -->
<div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
    <div class="flex overflow-x-auto">
        <a
            href="{{ route('member.loans.index', ['status' => 'all']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'all' ? 'border-blue-500 text-blue-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            All
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'all' ? 'bg-blue-100' : 'bg-gray-100' }}">
                {{ $statusCounts['all'] }}
            </span>
        </a>

        <a
            href="{{ route('member.loans.index', ['status' => 'active']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'active' ? 'border-green-500 text-green-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            Active
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'active' ? 'bg-green-100' : 'bg-gray-100' }}">
                {{ $statusCounts['active'] }}
            </span>
        </a>

        <a
            href="{{ route('member.loans.index', ['status' => 'overdue']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'overdue' ? 'border-red-500 text-red-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            Overdue
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'overdue' ? 'bg-red-100' : 'bg-gray-100' }}">
                {{ $statusCounts['overdue'] }}
            </span>
        </a>

        <a
            href="{{ route('member.loans.index', ['status' => 'returned']) }}"
            class="flex-1 px-6 py-4 text-center border-b-2 {{ $status == 'returned' ? 'border-gray-500 text-gray-600 font-semibold' : 'border-transparent text-gray-600 hover:text-gray-800' }}"
        >
            Returned
            <span class="ml-1 px-2 py-1 text-xs rounded-full {{ $status == 'returned' ? 'bg-gray-100' : 'bg-gray-100' }}">
                {{ $statusCounts['returned'] }}
            </span>
        </a>
    </div>
</div>

<!-- Loans List -->
@if($loans->count() > 0)
    <div class="space-y-4">
        @foreach($loans as $loan)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <h3 class="font-bold text-lg text-gray-800">
                                    {{ $loan->loan_code }}
                                </h3>

                                @if(!$loan->returned_at)
                                    @if($loan->isOverdue())
                                        <span class="px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                            Overdue
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                            Active
                                        </span>
                                    @endif
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">
                                        Returned
                                    </span>
                                @endif
                            </div>

                            <!-- Loan Info -->
                            <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                                <span>Borrowed {{ $loan->borrowed_at->format('d M Y') }}</span>
                                <span>"</span>
                                <span>Due {{ $loan->due_date->format('d M Y') }}</span>
                                @if($loan->returned_at)
                                    <span>"</span>
                                    <span>Returned {{ $loan->returned_at->format('d M Y') }}</span>
                                @endif
                            </div>

                            <!-- Book Details -->
                            <div class="flex items-start">
                                <div class="w-12 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>

                                <div class="ml-3">
                                    <h4 class="font-semibold text-gray-800">
                                        <a href="{{ route('member.books.show', $loan->book->id) }}" class="hover:text-blue-600">
                                            {{ $loan->book->title }}
                                        </a>
                                    </h4>
                                    <p class="text-sm text-gray-600">by {{ $loan->book->author }}</p>
                                </div>
                            </div>

                            <!-- Due Date Warning -->
                            @if(!$loan->returned_at)
                                @if($loan->isOverdue())
                                    <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded">
                                        <p class="text-sm text-red-700 font-medium">
                                            Overdue by {{ $loan->getDaysOverdue() }} day(s)
                                        </p>
                                        @if($loan->fine_amount > 0)
                                            <p class="text-sm text-red-600 mt-1">
                                                Current fine: Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded">
                                        <p class="text-sm text-blue-700">
                                            Due {{ $loan->due_date->diffForHumans() }}
                                            @if($loan->extended_at)
                                                <span class="text-xs">(Extended)</span>
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            @elseif($loan->fine_amount > 0)
                                <div class="mt-3 p-3 {{ $loan->fine_paid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border rounded">
                                    <p class="text-sm {{ $loan->fine_paid ? 'text-green-700' : 'text-red-700' }}">
                                        Fine: Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                                        @if($loan->fine_paid)
                                            <span class="font-medium">(Paid)</span>
                                        @else
                                            <span class="font-medium">(Unpaid)</span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="ml-4 flex flex-col space-y-2">
                            <a
                                href="{{ route('member.loans.show', $loan->id) }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium text-center"
                            >
                                View Details
                            </a>

                            @if($loan->canBeExtended())
                                <form method="POST" action="{{ route('member.loans.extend', $loan->id) }}" onsubmit="return confirm('Extend this loan?')">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium"
                                    >
                                        Extend
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $loans->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">No loans found</h2>
        <p class="text-gray-600 mb-6">You don't have any {{ $status != 'all' ? $status : '' }} loans yet</p>
        <a
            href="{{ route('member.books.index') }}"
            class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium"
        >
            Browse Books
        </a>
    </div>
@endif
@endsection
