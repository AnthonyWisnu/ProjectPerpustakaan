
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    /**
     * Display a listing of members with search and filters.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'member');

        // Search by name, email, or member number
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('member_number', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date joined
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $members = $query->withCount(['loans', 'reservations'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Statistics
        $statistics = [
            'total_members' => User::where('role', 'member')->count(),
            'active_members' => User::where('role', 'member')->where('status', 'active')->count(),
            'inactive_members' => User::where('role', 'member')->where('status', 'inactive')->count(),
            'suspended_members' => User::where('role', 'member')->where('status', 'suspended')->count(),
        ];

        return view('admin.members.index', compact('members', 'statistics'));
    }

    /**
     * Show the form for creating a new member.
     */
    public function create()
    {
        return view('admin.members.create');
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        // Generate unique member number
        $validated['member_number'] = $this->generateMemberNumber();

        // Set role as member
        $validated['role'] = 'member';

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        $member = User::create($validated);

        // Log activity
        \App\Models\ActivityLog::log(
            'member_created',
            "New member registered: {$member->name} ({$member->member_number})",
            $member
        );

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member created successfully.');
    }

    /**
     * Display the specified member with loan and reservation history.
     */
    public function show($id)
    {
        $member = User::where('role', 'member')
            ->with([
                'loans' => function ($query) {
                    $query->with('book')->latest()->limit(10);
                },
                'reservations' => function ($query) {
                    $query->with('items.book')->latest()->limit(10);
                }
            ])
            ->withCount([
                'loans',
                'loans as active_loans_count' => function ($query) {
                    $query->whereNull('returned_at');
                },
                'loans as overdue_loans_count' => function ($query) {
                    $query->whereNull('returned_at')->where('due_date', '<', now());
                },
                'reservations'
            ])
            ->findOrFail($id);

        // Get unpaid fines
        $unpaidFines = $member->loans()
            ->where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->with('book')
            ->get();

        $totalUnpaidFines = $unpaidFines->sum('fine_amount');

        return view('admin.members.show', compact('member', 'unpaidFines', 'totalUnpaidFines'));
    }

    /**
     * Show the form for editing the specified member.
     */
    public function edit($id)
    {
        $member = User::where('role', 'member')->findOrFail($id);
        return view('admin.members.edit', compact('member'));
    }

    /**
     * Update the specified member in storage.
     */
    public function update(Request $request, $id)
    {
        $member = User::where('role', 'member')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $member->update($validated);

        // Log activity
        \App\Models\ActivityLog::log(
            'member_updated',
            "Member updated: {$member->name} ({$member->member_number})",
            $member
        );

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Soft delete the specified member from storage.
     */
    public function destroy($id)
    {
        $member = User::where('role', 'member')->findOrFail($id);

        // Check if member has active loans
        if ($member->loans()->whereNull('returned_at')->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete member with active loans.');
        }

        // Check if member has unpaid fines
        $unpaidFines = $member->loans()
            ->where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->sum('fine_amount');

        if ($unpaidFines > 0) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete member with unpaid fines. Total unpaid: Rp ' . number_format($unpaidFines, 0, ',', '.'));
        }

        $memberName = $member->name;
        $memberNumber = $member->member_number;

        $member->delete();

        // Log activity
        \App\Models\ActivityLog::log(
            'member_deleted',
            "Member deleted: {$memberName} ({$memberNumber})"
        );

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'Member deleted successfully.');
    }

    /**
     * Generate a unique member number.
     */
    protected function generateMemberNumber(): string
    {
        do {
            $memberNumber = 'MBR-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (User::where('member_number', $memberNumber)->exists());

        return $memberNumber;
    }
}
