@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Welcome, {{ auth()->user()->name }}!</h1>
    <p class="text-gray-600 mt-2">Member Number: <span class="font-semibold">{{ auth()->user()->member_number }}</span></p>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <a href="{{ route('member.cart.index') }}" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Cart</p>
                <p class="text-3xl font-bold text-purple-600">{{ auth()->user()->cartItems()->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
    </a>

    <a href="{{ route('member.reservations.index', ['status' => 'pending']) }}" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Active Reservations</p>
                <p class="text-3xl font-bold text-yellow-600">{{ auth()->user()->reservations()->whereIn('status', ['pending', 'ready'])->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
    </a>

    <a href="{{ route('member.loans.index', ['status' => 'active']) }}" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Active Loans</p>
                <p class="text-3xl font-bold text-blue-600">{{ auth()->user()->loans()->whereNull('returned_at')->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        </div>
    </a>

    <a href="{{ route('member.loans.index', ['status' => 'overdue']) }}" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Overdue</p>
                <p class="text-3xl font-bold text-red-600">{{ auth()->user()->loans()->whereNull('returned_at')->where('due_date', '<', now())->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </a>
</div>

<!-- Quick Actions -->
<div class="bg-white shadow rounded-lg p-6 mb-8">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('member.books.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Browse Books</p>
                <p class="text-sm text-gray-600">Search and reserve books</p>
            </div>
        </a>

        <a href="{{ route('member.cart.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">View Cart</p>
                <p class="text-sm text-gray-600">Review selected books</p>
            </div>
        </a>

        <a href="{{ route('member.reservations.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-800">My Reservations</p>
                <p class="text-sm text-gray-600">Track reservations</p>
            </div>
        </a>
    </div>
</div>

<!-- Recent Activity / Upcoming Due Dates -->
@php
    $upcomingLoans = auth()->user()->loans()
        ->whereNull('returned_at')
        ->where('due_date', '>=', now())
        ->orderBy('due_date', 'asc')
        ->limit(5)
        ->get();
@endphp

@if($upcomingLoans->count() > 0)
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Upcoming Due Dates</h2>
    <div class="space-y-3">
        @foreach($upcomingLoans as $loan)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">{{ $loan->book->title }}</p>
                    <p class="text-sm text-gray-600">by {{ $loan->book->author }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium {{ $loan->due_date->diffInDays(now()) <= 2 ? 'text-red-600' : 'text-gray-600' }}">
                        Due {{ $loan->due_date->format('d M Y') }}
                    </p>
                    <p class="text-xs text-gray-500">{{ $loan->due_date->diffForHumans() }}</p>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4 text-center">
        <a href="{{ route('member.loans.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            View All Loans &rarr;
        </a>
    </div>
</div>
@endif
@endsection
