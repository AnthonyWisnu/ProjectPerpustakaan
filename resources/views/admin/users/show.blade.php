@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-blue-500 hover:text-blue-700">
        &larr; Back to Users
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main User Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- User Information Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
                    <p class="text-gray-600 mt-1">{{ $user->email }}</p>
                    @if($user->member_number)
                        <p class="text-sm text-gray-500 mt-1 font-mono">Member #{{ $user->member_number }}</p>
                    @endif
                </div>
                <div class="flex items-center space-x-2">
                    @if($user->role === 'super_admin')
                        <span class="px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 rounded">
                            Super Admin
                        </span>
                    @elseif($user->role === 'admin')
                        <span class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded">
                            Admin
                        </span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded">
                            Member
                        </span>
                    @endif

                    @if($user->status === 'active')
                        <span class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded">
                            Active
                        </span>
                    @elseif($user->status === 'suspended')
                        <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded">
                            Suspended
                        </span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded">
                            Inactive
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6 border-t">
                <div>
                    <p class="text-xs text-gray-500">Member Since</p>
                    <p class="text-sm font-medium text-gray-900">{{ $user->created_at->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Last Updated</p>
                    <p class="text-sm font-medium text-gray-900">{{ $user->updated_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistics</h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total_loans'] }}</p>
                    <p class="text-xs text-gray-600 mt-1">Total Loans</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-2xl font-bold text-green-600">{{ $stats['active_loans'] }}</p>
                    <p class="text-xs text-gray-600 mt-1">Active Loans</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <p class="text-2xl font-bold text-red-600">{{ $stats['overdue_loans'] }}</p>
                    <p class="text-xs text-gray-600 mt-1">Overdue</p>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['active_reservations'] }}</p>
                    <p class="text-xs text-gray-600 mt-1">Reservations</p>
                </div>
            </div>

            @if($stats['unpaid_fines'] > 0)
                <div class="mt-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <p class="text-sm font-medium text-orange-800">
                        Outstanding Fines: Rp {{ number_format($stats['unpaid_fines'], 0, ',', '.') }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Recent Loans -->
        @if($user->loans->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Loans</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($user->loans->take(5) as $loan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $loan->book->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $loan->loan_code }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $loan->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $loan->due_date->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($loan->returned_at)
                                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">
                                                Returned
                                            </span>
                                        @elseif($loan->isOverdue())
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                                                Overdue
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">
                                                Active
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Cart Items -->
        @if($user->cartItems->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Cart ({{ $user->cartItems->count() }} items)</h3>
                <div class="space-y-2">
                    @foreach($user->cartItems as $item)
                        <div class="flex items-center justify-between py-2 border-b last:border-b-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $item->book->title }}</p>
                                <p class="text-xs text-gray-500">by {{ $item->book->author }}</p>
                            </div>
                            <p class="text-xs text-gray-500">Added {{ $item->created_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Actions Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Update Status -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Update Status</h3>
            <form method="POST" action="{{ route('admin.users.updateStatus', $user->id) }}">
                @csrf
                <select
                    name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-3"
                    onchange="this.form.submit()"
                >
                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </form>
            <p class="text-xs text-gray-500">Change user account status</p>
        </div>

        <!-- Update Role -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Update Role</h3>
            <form method="POST" action="{{ route('admin.users.updateRole', $user->id) }}">
                @csrf
                <select
                    name="role"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-3"
                    onchange="this.form.submit()"
                >
                    <option value="member" {{ $user->role === 'member' ? 'selected' : '' }}>Member</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </form>
            <p class="text-xs text-gray-500">Change user role/permissions</p>
        </div>

        <!-- Reset Password -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Reset Password</h3>
            <button
                type="button"
                onclick="document.getElementById('resetPasswordModal').classList.remove('hidden')"
                class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium"
            >
                Reset Password
            </button>
            <p class="text-xs text-gray-500 mt-3">Set a new password for this user</p>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reset Password for {{ $user->name }}</h3>
            <form method="POST" action="{{ route('admin.users.resetPassword', $user->id) }}">
                @csrf
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="8"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        minlength="8"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <button
                        type="button"
                        onclick="document.getElementById('resetPasswordModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg"
                    >
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
