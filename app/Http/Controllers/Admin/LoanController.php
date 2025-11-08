<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\LoanService;
use App\Services\FineService;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService,
        protected FineService $fineService
    ) {}

    /**
     * Display a listing of loans.
     */
    public function index(Request $request)
    {
        $query = Loan::with(['user', 'book']);

        // Search by loan code or user
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('loan_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($query) use ($request) {
                      $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('book', function ($query) use ($request) {
                      $query->where('title', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('returned_at');
            } elseif ($request->status === 'returned') {
                $query->whereNotNull('returned_at');
            } elseif ($request->status === 'overdue') {
                $query->whereNull('returned_at')->where('due_date', '<', now());
            }
        }

        $loans = $query->latest()->paginate(15)->withQueryString();

        // Status counts
        $statusCounts = [
            'all' => Loan::count(),
            'active' => Loan::whereNull('returned_at')->count(),
            'overdue' => Loan::whereNull('returned_at')->where('due_date', '<', now())->count(),
            'returned' => Loan::whereNotNull('returned_at')->count(),
        ];

        return view('admin.loans.index', compact('loans', 'statusCounts'));
    }

    /**
     * Display the specified loan.
     */
    public function show($id)
    {
        $loan = Loan::with(['user', 'book', 'reservation'])->findOrFail($id);
        return view('admin.loans.show', compact('loan'));
    }

    /**
     * Process book return.
     */
    public function processReturn(Request $request, $id)
    {
        try {
            $loan = Loan::findOrFail($id);

            if ($loan->returned_at) {
                return redirect()
                    ->back()
                    ->with('error', 'This book has already been returned.');
            }

            $returnedLoan = $this->loanService->returnBook($loan, auth()->user());

            $message = 'Book returned successfully.';
            if ($returnedLoan->fine_amount > 0) {
                $message .= ' Fine amount: Rp ' . number_format($returnedLoan->fine_amount, 0, ',', '.');
            }

            return redirect()
                ->route('admin.loans.show', $returnedLoan->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mark fine as paid.
     */
    public function payFine(Request $request, $id)
    {
        try {
            $loan = Loan::findOrFail($id);

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

            $loan->update([
                'fine_paid' => true,
                'fine_paid_at' => now(),
            ]);

            return redirect()
                ->back()
                ->with('success', 'Fine marked as paid successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Waive fine for a loan.
     */
    public function waiveFine(Request $request, $id)
    {
        try {
            $loan = Loan::findOrFail($id);

            if ($loan->fine_amount <= 0) {
                return redirect()
                    ->back()
                    ->with('error', 'This loan has no fine.');
            }

            $validated = $request->validate([
                'reason' => ['required', 'string', 'max:255'],
            ]);

            $this->fineService->waiveFine($loan, $validated['reason']);

            return redirect()
                ->back()
                ->with('success', 'Fine waived successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Extend loan due date.
     */
    public function extend(Request $request, $id)
    {
        try {
            $loan = Loan::findOrFail($id);

            if (!$loan->canBeExtended()) {
                return redirect()
                    ->back()
                    ->with('error', 'This loan cannot be extended. It may already be extended, overdue, or returned.');
            }

            $extendedLoan = $this->loanService->extendLoan($loan);

            return redirect()
                ->route('admin.loans.show', $extendedLoan->id)
                ->with('success', 'Loan extended successfully. New due date: ' . $extendedLoan->due_date->format('d M Y'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
