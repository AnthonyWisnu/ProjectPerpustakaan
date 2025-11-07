@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Admin Dashboard</h1>
    <p class="text-gray-600 mb-4">Welcome, {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</p>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <div class="bg-blue-100 p-4 rounded">
            <h3 class="font-semibold text-blue-800">Total Books</h3>
            <p class="text-2xl font-bold text-blue-600">{{ \App\Models\Book::count() }}</p>
        </div>
        <div class="bg-green-100 p-4 rounded">
            <h3 class="font-semibold text-green-800">Total Members</h3>
            <p class="text-2xl font-bold text-green-600">{{ \App\Models\User::where('role', 'member')->count() }}</p>
        </div>
        <div class="bg-yellow-100 p-4 rounded">
            <h3 class="font-semibold text-yellow-800">Active Loans</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ \App\Models\Loan::active()->count() }}</p>
        </div>
        <div class="bg-purple-100 p-4 rounded">
            <h3 class="font-semibold text-purple-800">Categories</h3>
            <p class="text-2xl font-bold text-purple-600">{{ \App\Models\Category::count() }}</p>
        </div>
    </div>

    <div class="mt-8">
        <p class="text-gray-500 italic">Full admin features coming soon...</p>
    </div>
</div>
@endsection
