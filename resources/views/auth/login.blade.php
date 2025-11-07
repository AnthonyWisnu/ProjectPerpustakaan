@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="flex items-center justify-center min-h-[calc(100vh-16rem)]">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Login to Library System</h2>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror"
                    >
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="mr-2">
                        <span class="text-sm text-gray-700">Remember me</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <button
                        type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                    >
                        Login
                    </button>
                </div>
            </form>

            <!-- Register Link -->
            <div class="text-center mt-4">
                <p class="text-gray-600 text-sm">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-blue-500 hover:text-blue-700 font-semibold">
                        Register here
                    </a>
                </p>
            </div>

            <!-- Test Credentials -->
            <div class="mt-6 p-4 bg-gray-100 rounded">
                <p class="text-xs text-gray-600 font-semibold mb-2">Test Credentials:</p>
                <p class="text-xs text-gray-600">Admin: admin@library.com / password</p>
            </div>
        </div>
    </div>
</div>
@endsection
