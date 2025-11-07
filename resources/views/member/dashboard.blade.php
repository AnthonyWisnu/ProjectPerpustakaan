@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome, {{ auth()->user()->name }}!</h1>
    <p class="text-gray-600 mb-4">Member Number: <span class="font-semibold">{{ auth()->user()->member_number }}</span></p>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="bg-blue-100 p-4 rounded">
            <h3 class="font-semibold text-blue-800">Active Loans</h3>
            <p class="text-2xl font-bold text-blue-600">0</p>
        </div>
        <div class="bg-green-100 p-4 rounded">
            <h3 class="font-semibold text-green-800">Reservations</h3>
            <p class="text-2xl font-bold text-green-600">0</p>
        </div>
        <div class="bg-purple-100 p-4 rounded">
            <h3 class="font-semibold text-purple-800">Cart</h3>
            <p class="text-2xl font-bold text-purple-600">0</p>
        </div>
    </div>

    <div class="mt-8">
        <p class="text-gray-500 italic">Full member features coming soon...</p>
    </div>
</div>
@endsection
