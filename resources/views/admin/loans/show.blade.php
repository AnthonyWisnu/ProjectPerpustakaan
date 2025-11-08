@extends('layouts.app')

@section('title', 'Loan Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.loans.index') }}" class="text-blue-500 hover:text-blue-700">
        &larr; Back to Loans
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Loan Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Loan Info Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Loan {{ $loan->loan_code }}</h1>
                    <p class="text-gray-600 mt-1">{{ $loan->user->name }} ({{ $loan->user->email }})</p>
                </div>
                <div>
                    @if($loan->returned_at)
                        <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded">
                            Returned
                        </span>
                    @elseif($loan->isOverdue())
                        <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded">
                            Overdue
                        </span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded">
                            Active
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6 border-t">
                <div>
                    <p class="text-xs text-gray-500">Loan Date</p>
                    <p class="text-sm font-medium text-gray-900">{{ $loan->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Due Date</p>
                    <p class="text-sm font-medium text-gray-900">{{ $loan->due_date->format('d M Y') }}</p>
                    @if(!$loan->returned_at)
                        @php
                            $daysUntilDue = now()->diffInDays($loan->due_date, false);
                        @endphp
                        @if($daysUntilDue < 0)
                            <p class="text-xs mt-1 text-red-600 font-medium">{{ abs($daysUntilDue) }} days overdue</p>
                        @elseif($daysUntilDue <= 3)
                            <p class="text-xs mt-1 text-orange-600">Due in {{ $daysUntilDue }} days</p>
                        @endif
                    @endif
                </div>
                @if($loan->extended_at)
                    <div>
                        <p class="text-xs text-gray-500">Extended At</p>
                        <p class="text-sm font-medium text-gray-900">{{ $loan->extended_at->format('d M Y, H:i') }}</p>
                        <p class="text-xs text-blue-600 mt-1">Loan was extended once</p>
                    </div>
                @endif
                @if($loan->returned_at)
                    <div>
                        <p class="text-xs text-gray-500">Returned At</p>
                        <p class="text-sm font-medium text-gray-900">{{ $loan->returned_at->format('d M Y, H:i') }}</p>
                        @if($loan->returned_at > $loan->due_date)
                            <p class="text-xs text-red-600 mt-1">Returned late</p>
                        @else
                            <p class="text-xs text-green-600 mt-1">Returned on time</p>
                        @endif
                    </div>
                @endif
            </div>

            @if($loan->processed_by_user_id && $loan->processedBy)
                <div class="mt-4 pt-4 border-t">
                    <p class="text-xs text-gray-500">Processed By</p>
                    <p class="text-sm font-medium text-gray-900">{{ $loan->processedBy->name }}</p>
                </div>
            @endif
        </div>

        <!-- Book Details -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Book Details</h3>

            <div class="flex items-start gap-4">
                <div class="flex-1">
                    <h4 class="text-xl font-semibold text-gray-900">{{ $loan->book->title }}</h4>
                    <p class="text-gray-600 mt-1">by {{ $loan->book->author }}</p>
                    <div class="flex flex-wrap items-center gap-3 mt-3">
                        @if($loan->book->isbn)
                            <p class="text-sm text-gray-500">ISBN: {{ $loan->book->isbn }}</p>
                        @endif
                        @if($loan->book->publisher)
                            <p class="text-sm text-gray-500">Publisher: {{ $loan->book->publisher }}</p>
                        @endif
                        @if($loan->book->shelf_location)
                            <p class="text-sm text-gray-500">Shelf: {{ $loan->book->shelf_location }}</p>
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.books.show', $loan->book->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    View Book ’
                </a>
            </div>
        </div>

        <!-- Fine Information -->
        @if($loan->fine_amount > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Fine Information</h3>

                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-gray-700">Fine Amount</p>
                        <p class="text-2xl font-bold text-orange-600">Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}</p>
                    </div>

                    @if($loan->fine_paid)
                        <div class="flex items-center text-green-700 mt-2">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Fine has been paid</span>
                        </div>
                        @if($loan->fine_paid_at)
                            <p class="text-xs text-gray-600 mt-1">Paid on {{ $loan->fine_paid_at->format('d M Y, H:i') }}</p>
                        @endif
                    @else
                        <div class="flex items-center text-orange-700 mt-2">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Fine is unpaid</span>
                        </div>
                    @endif
                </div>

                @if($loan->fine_waived)
                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm font-medium text-blue-800">Fine was waived</p>
                        @if($loan->fine_waive_reason)
                            <p class="text-xs text-blue-700 mt-1">Reason: {{ $loan->fine_waive_reason }}</p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- Related Reservation -->
        @if($loan->reservation)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Related Reservation</h3>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $loan->reservation->reservation_code }}</p>
                        <p class="text-sm text-gray-600">Reserved on {{ $loan->reservation->reserved_at->format('d M Y, H:i') }}</p>
                    </div>
                    <a href="{{ route('admin.reservations.show', $loan->reservation->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View Reservation ’
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Actions Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Actions -->
        @if(!$loan->returned_at)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Actions</h3>

                <!-- Process Return -->
                <form method="POST" action="{{ route('admin.loans.processReturn', $loan->id) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium">
                        Process Return
                    </button>
                </form>

                <!-- Extend Loan -->
                @if($loan->canBeExtended())
                    <form method="POST" action="{{ route('admin.loans.extend', $loan->id) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm font-medium">
                            Extend Loan
                        </button>
                    </form>
                @endif
            </div>
        @endif

        <!-- Fine Actions -->
        @if($loan->fine_amount > 0 && !$loan->fine_paid && !$loan->fine_waived)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Fine Actions</h3>

                <!-- Mark Fine as Paid -->
                <form method="POST" action="{{ route('admin.loans.payFine', $loan->id) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm font-medium">
                        Mark Fine as Paid
                    </button>
                </form>

                <!-- Waive Fine -->
                <button
                    type="button"
                    onclick="document.getElementById('waiveFineModal').classList.remove('hidden')"
                    class="w-full px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium"
                >
                    Waive Fine
                </button>
            </div>
        @endif

        <!-- Member Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Member Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Name</p>
                    <p class="text-sm font-medium text-gray-900">{{ $loan->user->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Email</p>
                    <p class="text-sm text-gray-900">{{ $loan->user->email }}</p>
                </div>
                @if($loan->user->member_number)
                    <div>
                        <p class="text-xs text-gray-500">Member Number</p>
                        <p class="text-sm font-mono text-gray-900">{{ $loan->user->member_number }}</p>
                    </div>
                @endif
                <div class="pt-3 border-t">
                    <a href="{{ route('admin.users.show', $loan->user->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View Member Profile ’
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Waive Fine Modal -->
<div id="waiveFineModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Waive Fine</h3>
            <form method="POST" action="{{ route('admin.loans.waiveFine', $loan->id) }}">
                @csrf
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason <span class="text-red-500">*</span></label>
                    <textarea
                        id="reason"
                        name="reason"
                        rows="3"
                        required
                        placeholder="Enter reason for waiving fine..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <button
                        type="button"
                        onclick="document.getElementById('waiveFineModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg"
                    >
                        Waive Fine
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
