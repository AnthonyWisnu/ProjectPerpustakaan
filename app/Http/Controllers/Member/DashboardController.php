
<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\CartItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display member dashboard with overview statistics.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get statistics
        $stats = [
            'active_loans' => Loan::where('user_id', $user->id)
                ->whereNull('returned_at')
                ->count(),

            'active_reservations' => Reservation::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'ready'])
                ->count(),

            'cart_items' => CartItem::where('user_id', $user->id)->count(),

            'overdue_books' => Loan::where('user_id', $user->id)
                ->whereNull('returned_at')
                ->where('due_date', '<', now())
                ->count(),

            'unpaid_fines' => Loan::where('user_id', $user->id)
                ->where('fine_amount', '>', 0)
                ->where('fine_paid', false)
                ->sum('fine_amount'),
        ];

        // Get recent active loans (top 5)
        $recentLoans = Loan::with(['book'])
            ->where('user_id', $user->id)
            ->whereNull('returned_at')
            ->orderBy('borrowed_at', 'desc')
            ->limit(5)
            ->get();

        // Get active reservations
        $activeReservations = Reservation::with(['items.book'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'ready'])
            ->orderBy('reserved_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent notifications (if notification model is ready)
        // $notifications = $user->notifications()->latest()->limit(5)->get();

        return view('member.dashboard', compact(
            'stats',
            'recentLoans',
            'activeReservations'
        ));
    }
}
