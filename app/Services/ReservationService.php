<?php

namespace App\Services;

use App\Jobs\SendReservationConfirmationEmail;
use App\Jobs\SendReservationReadyEmail;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationService
{
    public function __construct(protected CartService $cartService)
    {
    }

    /**
     * Create reservation from cart.
     */
    public function createFromCart(User $user): Reservation
    {
        return DB::transaction(function () use ($user) {
            // Validate checkout
            $this->cartService->validateCheckout($user);

            $cartItems = $this->cartService->getCartItems($user);

            // Calculate expiration time
            $expiryHours = Setting::get('reservation_expiry_hours', 24);
            $expiredAt = now()->addHours($expiryHours);

            // Create reservation
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'reservation_code' => $this->generateReservationCode(),
                'status' => 'pending',
                'total_books' => $cartItems->count(),
                'reserved_at' => now(),
                'expired_at' => $expiredAt,
            ]);

            // Create reservation items and lock stock
            foreach ($cartItems as $cartItem) {
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'book_id' => $cartItem->book_id,
                    'status' => 'available',
                ]);

                // Decrement available stock
                $cartItem->book->decrementStock();
            }

            // Clear cart
            $this->cartService->clearCart($user);

            // Dispatch email notification
            SendReservationConfirmationEmail::dispatch($reservation->fresh(['user', 'items.book']));

            return $reservation;
        });
    }

    /**
     * Cancel reservation.
     */
    public function cancel(Reservation $reservation, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($reservation, $reason) {
            // Restore stock for all items
            foreach ($reservation->items as $item) {
                $item->book->incrementStock();
            }

            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason ?? 'Cancelled by user',
            ]);

            return true;
        });
    }

    /**
     * Mark reservation as ready for pickup.
     */
    public function markAsReady(Reservation $reservation): bool
    {
        $updated = $reservation->update(['status' => 'ready']);

        if ($updated) {
            // Dispatch email notification
            SendReservationReadyEmail::dispatch($reservation->fresh(['user', 'items.book']));
        }

        return $updated;
    }

    /**
     * Auto-cancel expired reservations.
     */
    public function autoCancel

ExpiredReservations(): int
    {
        $expiredReservations = Reservation::expired()->get();
        $count = 0;

        foreach ($expiredReservations as $reservation) {
            $this->cancel($reservation, 'Auto-cancelled: Reservation expired');
            $count++;
        }

        return $count;
    }

    /**
     * Generate unique reservation code.
     */
    protected function generateReservationCode(): string
    {
        do {
            $code = 'RSV' . strtoupper(Str::random(8));
        } while (Reservation::where('reservation_code', $code)->exists());

        return $code;
    }
}
