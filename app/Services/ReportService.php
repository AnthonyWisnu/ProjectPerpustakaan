
<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ReportService
 *
 * NOTE: PDF and Excel export methods require additional packages:
 * - PDF: composer require barryvdh/laravel-dompdf
 * - Excel: composer require maatwebsite/excel
 */
class ReportService
{
    /**
     * Generate loan report with statistics.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param array $filters
     * @return array
     */
    public function generateLoanReport($startDate, $endDate, array $filters = []): array
    {
        $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $query = Loan::with(['user', 'book.category'])
            ->whereBetween('borrowed_at', [$startDate, $endDate]);

        // Apply filters
        if (isset($filters['status'])) {
            switch ($filters['status']) {
                case 'active':
                    $query->active();
                    break;
                case 'returned':
                    $query->returned();
                    break;
                case 'overdue':
                    $query->overdue();
                    break;
            }
        }

        if (isset($filters['category_id'])) {
            $query->whereHas('book', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $loans = $query->get();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_loans' => $loans->count(),
                'active_loans' => $loans->where('returned_at', null)->count(),
                'returned_loans' => $loans->whereNotNull('returned_at')->count(),
                'overdue_loans' => $loans->filter(fn($loan) => $loan->isOverdue())->count(),
                'total_fines' => $loans->sum('fine_amount'),
                'unpaid_fines' => $loans->where('fine_paid', false)->sum('fine_amount'),
                'paid_fines' => $loans->where('fine_paid', true)->sum('fine_amount'),
            ],
            'loans' => $loans->map(function ($loan) {
                return [
                    'loan_code' => $loan->loan_code,
                    'book_title' => $loan->book->title,
                    'user_name' => $loan->user->name,
                    'borrowed_at' => $loan->borrowed_at?->format('Y-m-d H:i'),
                    'due_date' => $loan->due_date?->format('Y-m-d'),
                    'returned_at' => $loan->returned_at?->format('Y-m-d H:i'),
                    'fine_amount' => $loan->fine_amount,
                    'fine_paid' => $loan->fine_paid,
                    'status' => $loan->isActive() ? ($loan->isOverdue() ? 'overdue' : 'active') : 'returned',
                ];
            })->toArray(),
            'filters' => $filters,
        ];
    }

    /**
     * Generate financial report.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return array
     */
    public function generateFinanceReport($startDate, $endDate): array
    {
        $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $loans = Loan::whereBetween('borrowed_at', [$startDate, $endDate])->get();

        // Group fines by month
        $finesByMonth = $loans->groupBy(function ($loan) {
            return Carbon::parse($loan->borrowed_at)->format('Y-m');
        })->map(function ($monthLoans) {
            return [
                'total_fines' => $monthLoans->sum('fine_amount'),
                'paid_fines' => $monthLoans->where('fine_paid', true)->sum('fine_amount'),
                'unpaid_fines' => $monthLoans->where('fine_paid', false)->sum('fine_amount'),
                'loan_count' => $monthLoans->count(),
            ];
        })->toArray();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_fines_generated' => $loans->sum('fine_amount'),
                'total_fines_collected' => $loans->where('fine_paid', true)->sum('fine_amount'),
                'total_fines_pending' => $loans->where('fine_paid', false)->sum('fine_amount'),
                'total_loans' => $loans->count(),
                'loans_with_fines' => $loans->where('fine_amount', '>', 0)->count(),
                'collection_rate' => $loans->sum('fine_amount') > 0
                    ? round(($loans->where('fine_paid', true)->sum('fine_amount') / $loans->sum('fine_amount')) * 100, 2)
                    : 0,
            ],
            'by_month' => $finesByMonth,
        ];
    }

    /**
     * Generate inventory report.
     *
     * @param array $filters
     * @return array
     */
    public function generateInventoryReport(array $filters = []): array
    {
        $query = Book::with('category');

        // Apply filters
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'available':
                    $query->where('available_stock', '>', 0);
                    break;
                case 'low_stock':
                    $query->where('available_stock', '>', 0)
                          ->where('available_stock', '<=', 5);
                    break;
                case 'out_of_stock':
                    $query->where('available_stock', 0);
                    break;
            }
        }

        $books = $query->get();

        // Category breakdown
        $byCategory = Category::withCount('books')
            ->with(['books' => function ($q) {
                $q->select('category_id', DB::raw('SUM(total_stock) as total_stock_sum'), DB::raw('SUM(available_stock) as available_stock_sum'))
                  ->groupBy('category_id');
            }])
            ->get()
            ->map(function ($category) {
                $totalStock = $category->books->sum('total_stock');
                $availableStock = $category->books->sum('available_stock');

                return [
                    'category_name' => $category->name,
                    'total_books' => $category->books_count,
                    'total_stock' => $totalStock,
                    'available_stock' => $availableStock,
                    'borrowed_stock' => $totalStock - $availableStock,
                ];
            })
            ->toArray();

        return [
            'summary' => [
                'total_books' => Book::count(),
                'total_stock' => Book::sum('total_stock'),
                'available_stock' => Book::sum('available_stock'),
                'borrowed_stock' => Book::sum('total_stock') - Book::sum('available_stock'),
                'low_stock_books' => Book::where('available_stock', '>', 0)
                    ->where('available_stock', '<=', 5)
                    ->count(),
                'out_of_stock_books' => Book::where('available_stock', 0)->count(),
            ],
            'books' => $books->map(function ($book) {
                return [
                    'title' => $book->title,
                    'author' => $book->author,
                    'category' => $book->category->name,
                    'isbn' => $book->isbn,
                    'total_stock' => $book->total_stock,
                    'available_stock' => $book->available_stock,
                    'borrowed_stock' => $book->total_stock - $book->available_stock,
                    'stock_status' => $this->getStockStatus($book),
                ];
            })->toArray(),
            'by_category' => $byCategory,
            'filters' => $filters,
        ];
    }

    /**
     * Generate member activity report.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param array $filters
     * @return array
     */
    public function generateMemberReport($startDate, $endDate, array $filters = []): array
    {
        $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $query = User::where('role', 'member')
            ->withCount(['loans' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('borrowed_at', [$startDate, $endDate]);
            }])
            ->withCount(['reservations' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('reserved_at', [$startDate, $endDate]);
            }]);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['has_activity']) && $filters['has_activity']) {
            $query->having('loans_count', '>', 0);
        }

        $members = $query->get();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_members' => User::where('role', 'member')->count(),
                'active_members' => $members->where('loans_count', '>', 0)->count(),
                'inactive_members' => $members->where('loans_count', 0)->count(),
                'total_loans' => $members->sum('loans_count'),
                'total_reservations' => $members->sum('reservations_count'),
            ],
            'members' => $members->map(function ($member) {
                return [
                    'member_number' => $member->member_number,
                    'name' => $member->name,
                    'email' => $member->email,
                    'status' => $member->status,
                    'total_loans' => $member->loans_count,
                    'total_reservations' => $member->reservations_count,
                ];
            })->toArray(),
            'filters' => $filters,
        ];
    }

    /**
     * Export report to PDF (placeholder).
     *
     * @param array $data
     * @param string $type
     * @return mixed
     */
    public function exportToPDF(array $data, string $type)
    {
        // Requires barryvdh/laravel-dompdf package
        // Example implementation:
        /*
        $pdf = \PDF::loadView("reports.pdf.{$type}", ['data' => $data]);
        return $pdf->download("{$type}_report_" . now()->format('Y-m-d') . ".pdf");
        */

        \Log::warning('ReportService::exportToPDF() called but barryvdh/laravel-dompdf is not installed.');

        return null;
    }

    /**
     * Export report to Excel (placeholder).
     *
     * @param array $data
     * @param string $type
     * @return mixed
     */
    public function exportToExcel(array $data, string $type)
    {
        // Requires maatwebsite/excel package
        // Example implementation:
        /*
        return \Excel::download(new ReportExport($data, $type), "{$type}_report_" . now()->format('Y-m-d') . ".xlsx");
        */

        \Log::warning('ReportService::exportToExcel() called but maatwebsite/excel is not installed.');

        return null;
    }

    /**
     * Get stock status label for a book.
     *
     * @param Book $book
     * @return string
     */
    protected function getStockStatus(Book $book): string
    {
        if ($book->available_stock <= 0) {
            return 'out_of_stock';
        } elseif ($book->available_stock <= 5) {
            return 'low_stock';
        } else {
            return 'available';
        }
    }
}
