<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get daily loan statistics for a date range.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return array
     */
    public function getLoanTrends($startDate, $endDate): array
    {
        $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $trends = Loan::whereBetween('borrowed_at', [$startDate, $endDate])
            ->selectRaw('DATE(borrowed_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('Y-m-d'),
                    'total' => $item->total,
                ];
            })
            ->toArray();

        return $trends;
    }

    /**
     * Get most borrowed books.
     *
     * @param int $limit
     * @param string|Carbon|null $startDate
     * @param string|Carbon|null $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getPopularBooks(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = Loan::select('book_id', DB::raw('COUNT(*) as borrow_count'))
            ->with(['book.category'])
            ->groupBy('book_id')
            ->orderByDesc('borrow_count');

        if ($startDate && $endDate) {
            $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
            $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
            $query->whereBetween('borrowed_at', [$startDate, $endDate]);
        }

        return $query->limit($limit)->get()->map(function ($item) {
            return [
                'book_id' => $item->book_id,
                'book' => $item->book,
                'borrow_count' => $item->borrow_count,
            ];
        });
    }

    /**
     * Get statistics per category.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCategoryPerformance()
    {
        return Category::select('categories.*')
            ->withCount('books')
            ->with(['books' => function ($query) {
                $query->withCount('loans');
            }])
            ->get()
            ->map(function ($category) {
                $totalLoans = $category->books->sum('loans_count');

                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'total_books' => $category->books_count,
                    'total_loans' => $totalLoans,
                    'average_loans_per_book' => $category->books_count > 0
                        ? round($totalLoans / $category->books_count, 2)
                        : 0,
                ];
            })
            ->sortByDesc('total_loans')
            ->values();
    }

    /**
     * Get active member statistics.
     *
     * @param int $limit
     * @param string|Carbon|null $startDate
     * @param string|Carbon|null $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getActiveMemberStats(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = User::select('users.*')
            ->where('role', 'member')
            ->withCount(['loans' => function ($q) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
                    $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);
                    $q->whereBetween('borrowed_at', [$startDate, $endDate]);
                }
            }])
            ->having('loans_count', '>', 0)
            ->orderByDesc('loans_count');

        return $query->limit($limit)->get()->map(function ($user) {
            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'member_number' => $user->member_number,
                'total_loans' => $user->loans_count,
            ];
        });
    }

    /**
     * Get dashboard overview statistics.
     *
     * @param string|Carbon|null $startDate
     * @param string|Carbon|null $endDate
     * @return array
     */
    public function getOverviewStats($startDate = null, $endDate = null): array
    {
        $startDate = $startDate ? ($startDate instanceof Carbon ? $startDate : Carbon::parse($startDate)) : null;
        $endDate = $endDate ? ($endDate instanceof Carbon ? $endDate : Carbon::parse($endDate)) : null;

        // Build loan query with optional date filter
        $loanQuery = Loan::query();
        if ($startDate && $endDate) {
            $loanQuery->whereBetween('borrowed_at', [$startDate, $endDate]);
        }

        return [
            'total_books' => Book::count(),
            'available_books' => Book::where('available_stock', '>', 0)->count(),
            'total_members' => User::where('role', 'member')->count(),
            'active_members' => User::where('role', 'member')
                ->where('status', 'active')
                ->count(),
            'total_loans' => (clone $loanQuery)->count(),
            'active_loans' => (clone $loanQuery)->active()->count(),
            'overdue_loans' => (clone $loanQuery)->overdue()->count(),
            'returned_loans' => (clone $loanQuery)->returned()->count(),
            'total_fines' => (clone $loanQuery)->sum('fine_amount'),
            'unpaid_fines' => (clone $loanQuery)->unpaidFines()->sum('fine_amount'),
            'total_categories' => Category::count(),
            'low_stock_books' => Book::where('available_stock', '<=', 5)
                ->where('available_stock', '>', 0)
                ->count(),
            'out_of_stock_books' => Book::where('available_stock', 0)->count(),
        ];
    }
}
