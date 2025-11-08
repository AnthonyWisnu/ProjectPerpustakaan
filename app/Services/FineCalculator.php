
<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Setting;
use Carbon\Carbon;

class FineCalculator
{
    /**
     * Calculate fine amount for an overdue loan.
     *
     * @param Loan $loan
     * @return float
     */
    public function calculate(Loan $loan): float
    {
        // Don't calculate fine if loan is already returned or not overdue
        if ($loan->returned_at !== null || !$loan->isOverdue()) {
            return 0.00;
        }

        $daysOverdue = $this->calculateOverdueDays($loan);
        $gracePeriod = $this->getGracePeriod();

        // Apply grace period
        $chargeableDays = max(0, $daysOverdue - $gracePeriod);

        if ($chargeableDays <= 0) {
            return 0.00;
        }

        return $this->calculateForDays($chargeableDays);
    }

    /**
     * Calculate fine for a given number of days.
     *
     * @param int $days
     * @return float
     */
    public function calculateForDays(int $days): float
    {
        if ($days <= 0) {
            return 0.00;
        }

        $rate = $this->getRate();
        $maxAmount = $this->getMaxAmount();
        $calculatedFine = $days * $rate;

        // Apply maximum fine limit if set
        if ($maxAmount > 0 && $calculatedFine > $maxAmount) {
            return $maxAmount;
        }

        return round($calculatedFine, 2);
    }

    /**
     * Get fine rate per day from settings.
     *
     * @return float
     */
    public function getRate(): float
    {
        return (float) Setting::get('fine_rate_per_day', 1000);
    }

    /**
     * Get grace period in days from settings.
     *
     * @return int
     */
    public function getGracePeriod(): int
    {
        return (int) Setting::get('fine_grace_period', 0);
    }

    /**
     * Get maximum fine amount from settings.
     *
     * @return float
     */
    public function getMaxAmount(): float
    {
        return (float) Setting::get('fine_max_amount', 0);
    }

    /**
     * Calculate the number of overdue days for a loan.
     *
     * @param Loan $loan
     * @return int
     */
    protected function calculateOverdueDays(Loan $loan): int
    {
        if (!$loan->isOverdue()) {
            return 0;
        }

        $dueDate = Carbon::parse($loan->due_date)->endOfDay();
        $comparisonDate = $loan->returned_at
            ? Carbon::parse($loan->returned_at)
            : Carbon::now();

        return max(0, $dueDate->diffInDays($comparisonDate, false) * -1);
    }

    /**
     * Update the fine amount for a loan.
     *
     * @param Loan $loan
     * @return bool
     */
    public function updateLoanFine(Loan $loan): bool
    {
        $fineAmount = $this->calculate($loan);

        if ($loan->fine_amount != $fineAmount) {
            $loan->update(['fine_amount' => $fineAmount]);

            \Log::info("Fine updated for loan #{$loan->id}", [
                'loan_code' => $loan->loan_code,
                'old_fine' => $loan->fine_amount,
                'new_fine' => $fineAmount,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get fine calculation breakdown.
     *
     * @param Loan $loan
     * @return array
     */
    public function getBreakdown(Loan $loan): array
    {
        $daysOverdue = $this->calculateOverdueDays($loan);
        $gracePeriod = $this->getGracePeriod();
        $chargeableDays = max(0, $daysOverdue - $gracePeriod);
        $rate = $this->getRate();
        $calculatedFine = $chargeableDays * $rate;
        $maxAmount = $this->getMaxAmount();
        $finalFine = $this->calculate($loan);

        return [
            'due_date' => $loan->due_date?->format('Y-m-d'),
            'days_overdue' => $daysOverdue,
            'grace_period' => $gracePeriod,
            'chargeable_days' => $chargeableDays,
            'rate_per_day' => $rate,
            'calculated_fine' => $calculatedFine,
            'max_fine_amount' => $maxAmount,
            'final_fine' => $finalFine,
            'is_capped' => $maxAmount > 0 && $calculatedFine > $maxAmount,
        ];
    }
}
