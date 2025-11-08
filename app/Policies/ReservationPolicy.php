
<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Determine whether the user can view any reservations.
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
     * Determine whether the user can view the reservation.
     *
     * @param User $user
     * @param Reservation $reservation
     * @return bool
     */
    public function view(User $user, Reservation $reservation): bool
    {
        // Owner or admin can view
        return $user->id === $reservation->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create reservations.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Only active members can create reservations
        return $user->isMember() && $user->isActive();
    }

    /**
     * Determine whether the user can update the reservation.
     *
     * @param User $user
     * @param Reservation $reservation
     * @return bool
     */
    public function update(User $user, Reservation $reservation): bool
    {
        // Only admins can update reservations
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the reservation.
     *
     * @param User $user
     * @param Reservation $reservation
     * @return bool
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        // Only admins can delete reservations
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can cancel the reservation.
     *
     * @param User $user
     * @param Reservation $reservation
     * @return bool
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        // Owner can cancel if reservation is pending
        if ($user->id === $reservation->user_id && $reservation->isPending()) {
            return true;
        }

        // Admin can cancel any reservation
        return $user->isAdmin();
    }
}
