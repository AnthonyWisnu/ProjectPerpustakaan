
<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show profile edit form.
     */
    public function edit(Request $request)
    {
        return view('member.profile.edit', [
            'user' => $request->user()
        ]);
    }

    /**
     * Update profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'profile_photo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $path = $request->file('profile_photo')->store('profiles', 'public');
            $validated['profile_photo'] = $path;
        }

        $user->update($validated);

        return redirect()->route('member.profile.edit')
            ->with('success', 'Profile berhasil diperbarui.');
    }

    /**
     * Show password change form.
     */
    public function password()
    {
        return view('member.profile.password');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('member.profile.password')
            ->with('success', 'Password berhasil diubah.');
    }

    /**
     * Show member card with QR code.
     */
    public function memberCard(Request $request)
    {
        $user = $request->user();

        return view('member.profile.member-card', [
            'user' => $user
        ]);
    }

    /**
     * Download member card as PDF.
     */
    public function downloadMemberCard(Request $request)
    {
        // This would use a PDF library like DomPDF or similar
        // For now, we'll just redirect back
        return redirect()->route('member.profile.member-card')
            ->with('info', 'Fitur download PDF akan segera tersedia.');
    }
}
