
<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Determine whether the user can view any loans.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Members can view their own, admins can view all
        return $user->isMember() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the loan.
     *
     * @param User $user
     * @param Loan $loan
     * @return bool
     */
    public function view(User $user, Loan $loan): bool
    {
        // Owner or admin can view
        return $user->id === $loan->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create loans.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Only admins can create loans (loans are created by admin)
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the loan.
     *
     * @param User $user
     * @param Loan $loan
     * @return bool
     */
    public function update(User $user, Loan $loan): bool
    {
        // Only admins can update loans
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the loan.
     *
     * @param User $user
     * @param Loan $loan
     * @return bool
     */
    public function delete(User $user, Loan $loan): bool
    {
        // Only admins can delete loans
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can extend the loan.
     *
     * @param User $user
     * @param Loan $loan
     * @return bool
     */
    public function extend(User $user, Loan $loan): bool
    {
        // Admins can always extend
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can extend if:
        // 1. They are the loan owner
        // 2. Loan is not overdue
        // 3. Loan has not been extended already
        return $user->id === $loan->user_id && $loan->canBeExtended();
    }
}
