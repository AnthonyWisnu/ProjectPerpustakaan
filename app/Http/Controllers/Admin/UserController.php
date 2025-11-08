<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users/members.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('member_number', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the specified user details.
     */
    public function show($id)
    {
        $user = User::with(['loans', 'reservations', 'cartItems.book'])->findOrFail($id);

        $stats = [
            'total_loans' => $user->loans()->count(),
            'active_loans' => $user->loans()->whereNull('returned_at')->count(),
            'overdue_loans' => $user->loans()->whereNull('returned_at')->where('due_date', '<', now())->count(),
            'total_fines' => $user->loans()->sum('fine_amount'),
            'unpaid_fines' => $user->loans()->where('fine_paid', false)->sum('fine_amount'),
            'total_reservations' => $user->reservations()->count(),
            'active_reservations' => $user->reservations()->whereIn('status', ['pending', 'ready'])->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Update user status.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,suspended'],
        ]);

        $user->update($validated);

        return redirect()
            ->back()
            ->with('success', 'User status updated successfully.');
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => ['required', 'in:member,admin,super_admin'],
        ]);

        $user->update($validated);

        return redirect()
            ->back()
            ->with('success', 'User role updated successfully.');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Password reset successfully.');
    }
}
