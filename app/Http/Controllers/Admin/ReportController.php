
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Generate loan reports with date filters.
     */
    public function loans(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $status = $request->input('status', 'all');

        $query = Loan::with(['user', 'book'])
            ->whereBetween('borrowed_at', [$dateFrom, $dateTo]);

        // Filter by status
        if ($status === 'active') {
            $query->whereNull('returned_at');
        } elseif ($status === 'returned') {
            $query->whereNotNull('returned_at');
        } elseif ($status === 'overdue') {
            $query->whereNull('returned_at')->where('due_date', '<', now());
        }

        $loans = $query->latest('borrowed_at')->get();

        // Statistics
        $statistics = [
            'total_loans' => $loans->count(),
            'active_loans' => $loans->where('returned_at', null)->count(),
            'returned_loans' => $loans->whereNotNull('returned_at')->count(),
            'overdue_loans' => $loans->where('returned_at', null)
                ->where('due_date', '<', now())->count(),
            'total_fines' => $loans->sum('fine_amount'),
            'paid_fines' => $loans->where('fine_paid', true)->sum('fine_amount'),
            'unpaid_fines' => $loans->where('fine_paid', false)->sum('fine_amount'),
        ];

        // Trend by day
        $loanTrend = $loans->groupBy(function ($loan) {
            return $loan->borrowed_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->count();
        })->sortKeys();

        return view('admin.reports.loans', compact(
            'loans',
            'statistics',
            'loanTrend',
            'dateFrom',
            'dateTo',
            'status'
        ));
    }

    /**
     * Generate financial reports (fines collected).
     */
    public function finances(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Get all loans with fines in the date range
        $fines = Loan::with(['user', 'book'])
            ->where('fine_amount', '>', 0)
            ->whereBetween('returned_at', [$dateFrom, $dateTo])
            ->get();

        // Statistics
        $statistics = [
            'total_fines_generated' => $fines->sum('fine_amount'),
            'total_fines_paid' => $fines->where('fine_paid', true)->sum('fine_amount'),
            'total_fines_unpaid' => $fines->where('fine_paid', false)->sum('fine_amount'),
            'total_transactions' => $fines->count(),
            'paid_transactions' => $fines->where('fine_paid', true)->count(),
            'unpaid_transactions' => $fines->where('fine_paid', false)->count(),
        ];

        // Daily collection trend
        $dailyCollection = $fines->where('fine_paid', true)
            ->groupBy(function ($fine) {
                return $fine->fine_paid_at ? $fine->fine_paid_at->format('Y-m-d') : null;
            })
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('fine_amount'),
                ];
            })
            ->filter(function ($value, $key) {
                return $key !== null;
            })
            ->sortKeys();

        // Top members with highest fines
        $topFineMembers = User::where('role', 'member')
            ->withSum(['loans as total_fines' => function ($query) use ($dateFrom, $dateTo) {
                $query->where('fine_amount', '>', 0)
                    ->whereBetween('returned_at', [$dateFrom, $dateTo]);
            }], 'fine_amount')
            ->having('total_fines', '>', 0)
            ->orderByDesc('total_fines')
            ->limit(10)
            ->get();

        return view('admin.reports.finances', compact(
            'fines',
            'statistics',
            'dailyCollection',
            'topFineMembers',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Generate book inventory report.
     */
    public function inventory(Request $request)
    {
        $categoryId = $request->input('category_id');
        $availability = $request->input('availability');

        $query = Book::with('category')
            ->withCount([
                'loans',
                'loans as active_loans_count' => function ($q) {
                    $q->whereNull('returned_at');
                }
            ]);

        // Filter by category
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Filter by availability
        if ($availability === 'available') {
            $query->where('available_stock', '>', 0);
        } elseif ($availability === 'out_of_stock') {
            $query->where('available_stock', '=', 0);
        } elseif ($availability === 'low_stock') {
            $query->whereRaw('available_stock > 0 AND available_stock <= total_stock * 0.2');
        }

        $books = $query->get();

        // Statistics
        $statistics = [
            'total_books' => $books->count(),
            'total_stock' => $books->sum('total_stock'),
            'available_stock' => $books->sum('available_stock'),
            'borrowed_stock' => $books->sum('active_loans_count'),
            'out_of_stock_count' => $books->where('available_stock', 0)->count(),
            'low_stock_count' => $books->filter(function ($book) {
                return $book->available_stock > 0 &&
                       $book->available_stock <= ($book->total_stock * 0.2);
            })->count(),
        ];

        // Most borrowed books
        $mostBorrowed = $books->sortByDesc('loans_count')->take(10);

        // Categories for filter
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('admin.reports.inventory', compact(
            'books',
            'statistics',
            'mostBorrowed',
            'categories',
            'categoryId',
            'availability'
        ));
    }

    /**
     * Generate member activity report.
     */
    public function members(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $status = $request->input('status', 'all');

        $query = User::where('role', 'member')
            ->withCount([
                'loans as total_loans_count',
                'loans as period_loans_count' => function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('borrowed_at', [$dateFrom, $dateTo]);
                },
                'loans as active_loans_count' => function ($q) {
                    $q->whereNull('returned_at');
                },
                'reservations as total_reservations_count',
            ])
            ->withSum([
                'loans as total_fines' => function ($q) {
                    $q->where('fine_amount', '>', 0);
                }
            ], 'fine_amount')
            ->withSum([
                'loans as unpaid_fines' => function ($q) {
                    $q->where('fine_amount', '>', 0)->where('fine_paid', false);
                }
            ], 'fine_amount');

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $members = $query->latest()->get();

        // Statistics
        $statistics = [
            'total_members' => $members->count(),
            'active_members' => $members->where('status', 'active')->count(),
            'inactive_members' => $members->where('status', 'inactive')->count(),
            'suspended_members' => $members->where('status', 'suspended')->count(),
            'members_with_active_loans' => $members->where('active_loans_count', '>', 0)->count(),
            'members_with_fines' => $members->where('unpaid_fines', '>', 0)->count(),
            'new_members' => User::where('role', 'member')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
        ];

        // Most active members (by loans in period)
        $mostActiveMembers = $members->where('period_loans_count', '>', 0)
            ->sortByDesc('period_loans_count')
            ->take(10);

        return view('admin.reports.members', compact(
            'members',
            'statistics',
            'mostActiveMembers',
            'dateFrom',
            'dateTo',
            'status'
        ));
    }

    /**
     * Export report based on type.
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:loans,finances,inventory,members'],
            'format' => ['required', 'string', 'in:excel,pdf'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $type = $validated['type'];
        $format = $validated['format'];

        // TODO: Implement export functionality using packages like:
        // - maatwebsite/excel for Excel export
        // - barryvdh/laravel-dompdf for PDF export
        // The ReportService can handle the logic for generating exports

        return redirect()
            ->back()
            ->with('info', "Export functionality for {$type} report in {$format} format will be implemented soon.");
    }
}
