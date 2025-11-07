<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        // Generate unique member number
        $memberNumber = $this->generateMemberNumber();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'member',
            'member_number' => $memberNumber,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => 'active',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect('/')->with('success', 'Registration successful! Welcome to the library.');
    }

    /**
     * Generate unique member number.
     */
    protected function generateMemberNumber(): string
    {
        do {
            $number = 'MBR' . rand(10000, 99999);
        } while (User::where('member_number', $number)->exists());

        return $number;
    }
}
