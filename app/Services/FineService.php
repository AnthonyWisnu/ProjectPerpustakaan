<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Setting;
use App\Models\User;

class FineService
{
    /**
     * Calculate fine for a loan.
     */
    public function calculateFine(Loan $loan): float
    {
        if (!$loan->isOverdue()) {
            return 0;
        }

        $ratePerDay = Setting::get('fine_rate_per_day', 1000);
        $gracePeriod = Setting::get('fine_grace_period_days', 0);
        $maxFine = Setting::get('max_fine_amount', 50000);

        $daysOverdue = $loan->getDaysOverdue() - $gracePeriod;

        if ($daysOverdue <= 0) {
            return 0;
        }

        $fine = $daysOverdue * $ratePerDay;

        return min($fine, $maxFine);
    }

    /**
     * Get total unpaid fines for a user.
     */
    public function getUnpaidFines(User $user): float
    {
        return Loan::where('user_id', $user->id)
            ->where('fine_paid', false)
            ->sum('fine_amount');
    }

    /**
     * Get all loans with unpaid fines for a user.
     */
    public function getLoansWithUnpaidFines(User $user)
    {
        return Loan::where('user_id', $user->id)
            ->where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->with('book')
            ->get();
    }

    /**
     * Waive fine for a loan.
     */
    public function waiveFine(Loan $loan, string $reason): Loan
    {
        $loan->update([
            'fine_amount' => 0,
            'fine_paid' => true,
            'fine_paid_at' => now(),
            'notes' => "Fine waived: {$reason}",
        ]);

        return $loan;
    }
}
