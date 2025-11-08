<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard with statistics.
     */
    public function index()
    {
        // General Stats
        $stats = [
            'total_books' => Book::count(),
            'total_available' => Book::sum('available_stock'),
            'total_borrowed' => Book::sum('total_stock') - Book::sum('available_stock'),
            'total_members' => User::where('role', 'member')->count(),
            'active_members' => User::where('role', 'member')->where('status', 'active')->count(),
        ];

        // Loan Stats
        $loanStats = [
            'total_loans' => Loan::count(),
            'active_loans' => Loan::whereNull('returned_at')->count(),
            'overdue_loans' => Loan::whereNull('returned_at')->where('due_date', '<', now())->count(),
            'total_fines' => Loan::sum('fine_amount'),
            'unpaid_fines' => Loan::where('fine_paid', false)->sum('fine_amount'),
        ];

        // Reservation Stats
        $reservationStats = [
            'total_reservations' => Reservation::count(),
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'ready_reservations' => Reservation::where('status', 'ready')->count(),
            'expired_reservations' => Reservation::where('status', 'expired')->count(),
        ];

        // Recent Activities
        $recentLoans = Loan::with(['user', 'book'])
            ->latest()
            ->limit(5)
            ->get();

        $recentReservations = Reservation::with(['user'])
            ->latest()
            ->limit(5)
            ->get();

        // Overdue Loans
        $overdueLoans = Loan::with(['user', 'book'])
            ->whereNull('returned_at')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        // Popular Books (most borrowed)
        $popularBooks = Book::withCount(['loans' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }])
            ->orderBy('loans_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'loanStats',
            'reservationStats',
            'recentLoans',
            'recentReservations',
            'overdueLoans',
            'popularBooks'
        ));
    }
}
