
<?php

namespace App\Services;

use Carbon\Carbon;

class FineCalculator
{
    /**
     * Calculate fine for overdue loan
     */
    public function calculateFine(Carbon $dueDate, ?Carbon $returnDate = null, int $gracePeriod = 0): int
    {
        $returnDate = $returnDate ?? now();

        // If not overdue, no fine
        if ($returnDate->lte($dueDate)) {
            return 0;
        }

        // Calculate overdue days
        $overdueDays = $dueDate->diffInDays($returnDate);

        // Apply grace period
        $chargeableDays = max(0, $overdueDays - $gracePeriod);

        if ($chargeableDays === 0) {
            return 0;
        }

        // Get rate per day from config
        $ratePerDay = config('library.fine.rate_per_day', 1000);
        $maxAmount = config('library.fine.max_amount', 50000);

        // Calculate fine
        $fineAmount = $chargeableDays * $ratePerDay;

        // Cap at maximum amount
        return min($fineAmount, $maxAmount);
    }

    /**
     * Calculate fine based on overdue days
     */
    public function calculateFineByDays(int $overdueDays, int $gracePeriod = 0): int
    {
        $chargeableDays = max(0, $overdueDays - $gracePeriod);

        if ($chargeableDays === 0) {
            return 0;
        }

        $ratePerDay = config('library.fine.rate_per_day', 1000);
        $maxAmount = config('library.fine.max_amount', 50000);

        $fineAmount = $chargeableDays * $ratePerDay;

        return min($fineAmount, $maxAmount);
    }

    /**
     * Get daily fine rate
     */
    public function getDailyRate(): int
    {
        return config('library.fine.rate_per_day', 1000);
    }

    /**
     * Get maximum fine amount
     */
    public function getMaxAmount(): int
    {
        return config('library.fine.max_amount', 50000);
    }

    /**
     * Get grace period
     */
    public function getGracePeriod(): int
    {
        return config('library.fine.fine_grace_period_days', 0);
    }

    /**
     * Check if loan is overdue
     */
    public function isOverdue(Carbon $dueDate, ?Carbon $currentDate = null): bool
    {
        $currentDate = $currentDate ?? now();

        return $currentDate->gt($dueDate);
    }

    /**
     * Get overdue days
     */
    public function getOverdueDays(Carbon $dueDate, ?Carbon $returnDate = null): int
    {
        $returnDate = $returnDate ?? now();

        if ($returnDate->lte($dueDate)) {
            return 0;
        }

        return $dueDate->diffInDays($returnDate);
    }
}
