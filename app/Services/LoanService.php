<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanService
{
    public function __construct(protected FineService $fineService)
    {
    }

    /**
     * Create loan from reservation pickup.
     */
    public function createFromReservation(Reservation $reservation, User $confirmedBy): array
    {
        return DB::transaction(function () use ($reservation, $confirmedBy) {
            $loans = [];
            $loanDuration = Setting::get('loan_duration_days', 7);

            foreach ($reservation->items as $item) {
                $loan = Loan::create([
                    'user_id' => $reservation->user_id,
                    'book_id' => $item->book_id,
                    'reservation_id' => $reservation->id,
                    'loan_code' => $this->generateLoanCode(),
                    'borrowed_at' => now(),
                    'due_date' => now()->addDays($loanDuration),
                    'confirmed_by' => $confirmedBy->id,
                ]);

                $loans[] = $loan;
            }

            // Update reservation status
            $reservation->update([
                'status' => 'picked_up',
                'picked_up_at' => now(),
            ]);

            return $loans;
        });
    }

    /**
     * Return a book.
     */
    public function returnBook(Loan $loan, User $returnedBy): Loan
    {
        return DB::transaction(function () use ($loan, $returnedBy) {
            // Calculate fine if overdue
            if ($loan->isOverdue()) {
                $fineRate = Setting::get('fine_rate_per_day', 1000);
                $fineAmount = $loan->calculateFine($fineRate);

                $loan->fine_amount = min($fineAmount, Setting::get('max_fine_amount', 50000));
            }

            $loan->update([
                'returned_at' => now(),
                'returned_by' => $returnedBy->id,
            ]);

            // Increment book stock
            $loan->book->incrementStock();

            return $loan;
        });
    }

    /**
     * Extend loan due date.
     */
    public function extendLoan(Loan $loan, int $days = null): Loan
    {
        if (!$loan->canBeExtended()) {
            throw new \Exception('This loan cannot be extended.');
        }

        $days = $days ?? Setting::get('loan_duration_days', 7);

        $loan->update([
            'due_date' => $loan->due_date->addDays($days),
            'extended_at' => now(),
        ]);

        return $loan;
    }

    /**
     * Pay fine for a loan.
     */
    public function payFine(Loan $loan): Loan
    {
        $loan->update([
            'fine_paid' => true,
            'fine_paid_at' => now(),
        ]);

        return $loan;
    }

    /**
     * Generate unique loan code.
     */
    protected function generateLoanCode(): string
    {
        do {
            $code = 'LOAN' . strtoupper(Str::random(8));
        } while (Loan::where('loan_code', $code)->exists());

        return $code;
    }
}
