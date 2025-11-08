@extends('layouts.app')

@section('title', 'Loan Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('member.loans.index') }}" class="text-blue-500 hover:text-blue-700">
        &larr; Back to My Loans
    </a>
</div>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $loan->loan_code }}</h1>
            <p class="text-gray-600 mt-2">Loan details and return information</p>
        </div>

        @if(!$loan->returned_at)
            @if($loan->isOverdue())
                <span class="px-4 py-2 text-sm font-semibold text-red-700 bg-red-100 rounded-full">
                    Overdue
                </span>
            @else
                <span class="px-4 py-2 text-sm font-semibold text-green-700 bg-green-100 rounded-full">
                    Active
                </span>
            @endif
        @else
            <span class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-full">
                Returned
            </span>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Loan Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Loan Information</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Loan Code</p>
                    <p class="font-medium text-gray-800">{{ $loan->loan_code }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="font-medium text-gray-800">
                        @if(!$loan->returned_at)
                            @if($loan->isOverdue())
                                Overdue
                            @else
                                Active
                            @endif
                        @else
                            Returned
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Borrowed At</p>
                    <p class="font-medium text-gray-800">{{ $loan->borrowed_at->format('d M Y, H:i') }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Due Date</p>
                    <p class="font-medium {{ $loan->isOverdue() && !$loan->returned_at ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $loan->due_date->format('d M Y') }}
                    </p>
                    @if(!$loan->returned_at)
                        @if($loan->isOverdue())
                            <p class="text-xs text-red-600">Overdue by {{ $loan->getDaysOverdue() }} day(s)</p>
                        @else
                            <p class="text-xs text-gray-500">{{ $loan->due_date->diffForHumans() }}</p>
                        @endif
                    @endif
                </div>

                @if($loan->extended_at)
                    <div>
                        <p class="text-sm text-gray-600">Extended At</p>
                        <p class="font-medium text-gray-800">{{ $loan->extended_at->format('d M Y, H:i') }}</p>
                    </div>
                @endif

                @if($loan->returned_at)
                    <div>
                        <p class="text-sm text-gray-600">Returned At</p>
                        <p class="font-medium text-gray-800">{{ $loan->returned_at->format('d M Y, H:i') }}</p>
                    </div>
                @endif

                @if($loan->reservation)
                    <div>
                        <p class="text-sm text-gray-600">Reservation Code</p>
                        <p class="font-medium text-gray-800">
                            <a href="{{ route('member.reservations.show', $loan->reservation->id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $loan->reservation->reservation_code }}
                            </a>
                        </p>
                    </div>
                @endif
            </div>

            <!-- Fine Information -->
            @if($loan->fine_amount > 0)
                <div class="mt-6 p-4 {{ $loan->fine_paid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border rounded">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold {{ $loan->fine_paid ? 'text-green-800' : 'text-red-800' }} mb-1">
                                Fine Amount
                            </p>
                            <p class="text-2xl font-bold {{ $loan->fine_paid ? 'text-green-700' : 'text-red-700' }}">
                                Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                            </p>
                            @if($loan->fine_paid)
                                <p class="text-sm text-green-600 mt-1">
                                    Paid on {{ $loan->fine_paid_at?->format('d M Y, H:i') }}
                                </p>
                            @else
                                <p class="text-sm text-red-600 mt-1">
                                    Please pay this fine at the library counter
                                </p>
                            @endif
                        </div>

                        @if($loan->fine_paid)
                            <span class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-full">
                                PAID
                            </span>
                        @else
                            <span class="px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded-full">
                                UNPAID
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Book Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Book Information</h2>

            <div class="flex items-start">
                <!-- Book Icon -->
                <div class="w-24 h-32 bg-gradient-to-br from-blue-100 to-blue-200 rounded flex items-center justify-center flex-shrink-0">
                    <svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>

                <!-- Book Details -->
                <div class="ml-6 flex-1">
                    @if($loan->book->category)
                        <span class="inline-block px-3 py-1 text-sm font-semibold text-blue-600 bg-blue-100 rounded mb-2">
                            {{ $loan->book->category->name }}
                        </span>
                    @endif

                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                        <a href="{{ route('member.books.show', $loan->book->id) }}" class="hover:text-blue-600">
                            {{ $loan->book->title }}
                        </a>
                    </h3>

                    <p class="text-lg text-gray-600 mb-4">by {{ $loan->book->author }}</p>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Publisher</p>
                            <p class="font-medium text-gray-800">{{ $loan->book->publisher }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">Publication Year</p>
                            <p class="font-medium text-gray-800">{{ $loan->book->publication_year }}</p>
                        </div>

                        @if($loan->book->isbn)
                            <div>
                                <p class="text-gray-600">ISBN</p>
                                <p class="font-medium text-gray-800">{{ $loan->book->isbn }}</p>
                            </div>
                        @endif

                        @if($loan->book->shelf_location)
                            <div>
                                <p class="text-gray-600">Shelf Location</p>
                                <p class="font-medium text-gray-800">{{ $loan->book->shelf_location }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Actions -->
        @if(!$loan->returned_at && $loan->canBeExtended())
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Actions</h3>

                <form method="POST" action="{{ route('member.loans.extend', $loan->id) }}" onsubmit="return confirm('Extend this loan by 7 days?')">
                    @csrf
                    <button
                        type="submit"
                        class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-medium mb-2"
                    >
                        Extend Loan
                    </button>
                </form>

                <p class="text-xs text-gray-600 text-center">
                    Extend loan period by 7 days (only available once)
                </p>
            </div>
        @endif

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Timeline</h3>

            <div class="space-y-4">
                <!-- Borrowed -->
                <div class="flex items-start">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-800">Borrowed</p>
                        <p class="text-sm text-gray-600">{{ $loan->borrowed_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <!-- Extended (if applicable) -->
                @if($loan->extended_at)
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-800">Extended</p>
                            <p class="text-sm text-gray-600">{{ $loan->extended_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Due Date -->
                <div class="flex items-start">
                    <div class="w-8 h-8 {{ $loan->isOverdue() && !$loan->returned_at ? 'bg-red-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 {{ $loan->isOverdue() && !$loan->returned_at ? 'text-red-600' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium {{ $loan->isOverdue() && !$loan->returned_at ? 'text-red-600' : 'text-gray-800' }}">
                            Due Date
                        </p>
                        <p class="text-sm text-gray-600">{{ $loan->due_date->format('d M Y') }}</p>
                    </div>
                </div>

                <!-- Returned (if applicable) -->
                @if($loan->returned_at)
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-800">Returned</p>
                            <p class="text-sm text-gray-600">{{ $loan->returned_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Important Notes -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="font-semibold text-yellow-800 mb-3">Important Notes</h3>
            <ul class="text-sm text-yellow-700 space-y-2">
                @if(!$loan->returned_at)
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span>Return the book by the due date to avoid fines</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span>Late fee: Rp 1,000 per day</span>
                    </li>
                    @if(!$loan->extended_at)
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <span>You can extend this loan once before the due date</span>
                        </li>
                    @endif
                @else
                    <li class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>This book has been returned</span>
                    </li>
                    @if($loan->fine_amount > 0 && !$loan->fine_paid)
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>Please pay the outstanding fine at the library counter</span>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection
