@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="flex items-center justify-center min-h-[calc(100vh-16rem)]">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Register as Member</h2>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('email') border-red-500 @enderror">
                    @error('email')<p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone (Optional)</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                    <input id="password" type="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('password') border-red-500 @enderror">
                    @error('password')<p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">Register</button>
            </form>

            <div class="text-center mt-4">
                <p class="text-gray-600 text-sm">Already have an account? <a href="{{ route('login') }}" class="text-blue-500 hover:text-blue-700 font-semibold">Login here</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
