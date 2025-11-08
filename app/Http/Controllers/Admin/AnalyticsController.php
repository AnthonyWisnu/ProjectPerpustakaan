
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Display analytics dashboard with charts data.
     */
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Loan trends over time
        $loanTrends = Loan::whereBetween('borrowed_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(borrowed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Popular books (most borrowed)
        $popularBooks = Book::withCount(['loans' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('borrowed_at', [$dateFrom, $dateTo]);
            }])
            ->having('loans_count', '>', 0)
            ->orderByDesc('loans_count')
            ->limit(10)
            ->get();

        // Category performance
        $categoryPerformance = Category::withCount(['books' => function ($query) {
                $query->withCount('loans');
            }])
            ->with(['books' => function ($query) use ($dateFrom, $dateTo) {
                $query->withCount(['loans' => function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('borrowed_at', [$dateFrom, $dateTo]);
                }]);
            }])
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'total_books' => $category->books->count(),
                    'total_loans' => $category->books->sum('loans_count'),
                ];
            })
            ->sortByDesc('total_loans')
            ->values();

        // Member activity (active members with most loans)
        $memberActivity = User::where('role', 'member')
            ->withCount(['loans' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('borrowed_at', [$dateFrom, $dateTo]);
            }])
            ->having('loans_count', '>', 0)
            ->orderByDesc('loans_count')
            ->limit(10)
            ->get();

        // Overview statistics
        $statistics = [
            'total_loans' => Loan::whereBetween('borrowed_at', [$dateFrom, $dateTo])->count(),
            'active_loans' => Loan::whereNull('returned_at')->count(),
            'overdue_loans' => Loan::whereNull('returned_at')
                ->where('due_date', '<', now())
                ->count(),
            'total_fines' => Loan::whereBetween('borrowed_at', [$dateFrom, $dateTo])
                ->sum('fine_amount'),
            'paid_fines' => Loan::whereBetween('borrowed_at', [$dateFrom, $dateTo])
                ->where('fine_paid', true)
                ->sum('fine_amount'),
            'unpaid_fines' => Loan::where('fine_paid', false)
                ->sum('fine_amount'),
            'active_members' => User::where('role', 'member')
                ->where('status', 'active')
                ->count(),
            'new_members' => User::where('role', 'member')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
        ];

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'loan_trends' => $loanTrends,
                'popular_books' => $popularBooks,
                'category_performance' => $categoryPerformance,
                'member_activity' => $memberActivity,
                'statistics' => $statistics,
            ]);
        }

        // Return view for page load
        return view('admin.analytics.index', compact(
            'loanTrends',
            'popularBooks',
            'categoryPerformance',
            'memberActivity',
            'statistics',
            'dateFrom',
            'dateTo'
        ));
    }
}
