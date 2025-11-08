<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService
    ) {}

    /**
     * Display user's loans.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Loan::where('user_id', auth()->id())
            ->with(['book.category', 'reservation'])
            ->orderBy('created_at', 'desc');

        if ($status === 'active') {
            $query->whereNull('returned_at');
        } elseif ($status === 'returned') {
            $query->whereNotNull('returned_at');
        } elseif ($status === 'overdue') {
            $query->whereNull('returned_at')
                ->where('due_date', '<', now());
        }

        $loans = $query->paginate(10);

        // Count by status for filter tabs
        $statusCounts = [
            'all' => Loan::where('user_id', auth()->id())->count(),
            'active' => Loan::where('user_id', auth()->id())->whereNull('returned_at')->count(),
            'overdue' => Loan::where('user_id', auth()->id())
                ->whereNull('returned_at')
                ->where('due_date', '<', now())
                ->count(),
            'returned' => Loan::where('user_id', auth()->id())->whereNotNull('returned_at')->count(),
        ];

        return view('member.loans.index', compact('loans', 'statusCounts', 'status'));
    }

    /**
     * Display loan details.
     */
    public function show($id)
    {
        $loan = Loan::where('user_id', auth()->id())
            ->with(['book.category', 'reservation'])
            ->findOrFail($id);

        return view('member.loans.show', compact('loan'));
    }

    /**
     * Request loan extension.
     */
    public function extend($id, Request $request)
    {
        try {
            $loan = Loan::where('user_id', auth()->id())
                ->findOrFail($id);

            if (!$loan->canBeExtended()) {
                return redirect()
                    ->back()
                    ->with('error', 'This loan cannot be extended. It may already be extended, overdue, or returned.');
            }

            $extendedLoan = $this->loanService->extendLoan($loan);

            return redirect()
                ->route('member.loans.show', $extendedLoan->id)
                ->with('success', 'Loan extended successfully. New due date: ' . $extendedLoan->due_date->format('d M Y'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
