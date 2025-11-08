
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\User;
use App\Services\FineService;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function __construct(
        protected FineService $fineService
    ) {}

    /**
     * Display a listing of fines with filters.
     */
    public function index(Request $request)
    {
        $query = Loan::with(['user', 'book'])
            ->where('fine_amount', '>', 0);

        // Filter by payment status
        if ($request->filled('status')) {
            if ($request->status === 'paid') {
                $query->where('fine_paid', true);
            } elseif ($request->status === 'unpaid') {
                $query->where('fine_paid', false);
            }
        }

        // Filter by member
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('returned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('returned_at', '<=', $request->date_to);
        }

        // Search by loan code or member
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('loan_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($query) use ($request) {
                      $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%')
                            ->orWhere('member_number', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('book', function ($query) use ($request) {
                      $query->where('title', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $fines = $query->latest('returned_at')->paginate(15)->withQueryString();

        // Statistics
        $statistics = [
            'total_fines' => Loan::where('fine_amount', '>', 0)->sum('fine_amount'),
            'paid_fines' => Loan::where('fine_paid', true)->sum('fine_amount'),
            'unpaid_fines' => Loan::where('fine_paid', false)->sum('fine_amount'),
            'total_count' => Loan::where('fine_amount', '>', 0)->count(),
            'paid_count' => Loan::where('fine_paid', true)->count(),
            'unpaid_count' => Loan::where('fine_paid', false)->count(),
        ];

        // Get members with fines for filter dropdown
        $members = User::where('role', 'member')
            ->whereHas('loans', function ($query) {
                $query->where('fine_amount', '>', 0);
            })
            ->orderBy('name')
            ->get();

        return view('admin.fines.index', compact('fines', 'statistics', 'members'));
    }

    /**
     * Display the specified fine details.
     */
    public function show($id)
    {
        $loan = Loan::with(['user', 'book', 'returnedBy'])->findOrFail($id);

        if ($loan->fine_amount <= 0) {
            return redirect()
                ->route('admin.fines.index')
                ->with('error', 'This loan has no fine.');
        }

        return view('admin.fines.show', compact('loan'));
    }

    /**
     * Process fine payment for a loan.
     */
    public function processPayment(Request $request, $loanId)
    {
        try {
            $loan = Loan::findOrFail($loanId);

            if ($loan->fine_amount <= 0) {
                return redirect()
                    ->back()
                    ->with('error', 'This loan has no fine.');
            }

            if ($loan->fine_paid) {
                return redirect()
                    ->back()
                    ->with('error', 'Fine has already been paid.');
            }

            $validated = $request->validate([
                'payment_method' => ['nullable', 'string', 'in:cash,transfer,card'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);

            $loan->update([
                'fine_paid' => true,
                'fine_paid_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Log the payment
            \App\Models\ActivityLog::log(
                'fine_payment',
                "Fine payment of Rp " . number_format($loan->fine_amount, 0, ',', '.') . " processed for loan {$loan->loan_code}",
                $loan
            );

            return redirect()
                ->route('admin.fines.show', $loan->id)
                ->with('success', 'Fine payment processed successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Waive fine for a loan with reason.
     */
    public function waive(Request $request, $loanId)
    {
        try {
            $loan = Loan::findOrFail($loanId);

            if ($loan->fine_amount <= 0) {
                return redirect()
                    ->back()
                    ->with('error', 'This loan has no fine.');
            }

            if ($loan->fine_paid) {
                return redirect()
                    ->back()
                    ->with('error', 'Fine has already been paid.');
            }

            $validated = $request->validate([
                'reason' => ['required', 'string', 'max:500'],
            ]);

            $originalAmount = $loan->fine_amount;
            $this->fineService->waiveFine($loan, $validated['reason']);

            // Log the waiver
            \App\Models\ActivityLog::log(
                'fine_waived',
                "Fine of Rp " . number_format($originalAmount, 0, ',', '.') . " waived for loan {$loan->loan_code}. Reason: {$validated['reason']}",
                $loan
            );

            return redirect()
                ->route('admin.fines.show', $loan->id)
                ->with('success', 'Fine waived successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Export fines report.
     * Note: This is a placeholder for Excel/PDF export functionality.
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');

        // TODO: Implement Excel/PDF export using packages like:
        // - maatwebsite/excel for Excel
        // - barryvdh/laravel-dompdf for PDF

        return redirect()
            ->route('admin.fines.index')
            ->with('info', 'Export functionality will be implemented soon.');
    }
}
