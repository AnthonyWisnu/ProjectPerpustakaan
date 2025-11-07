<?php

namespace App\Services;

use App\Models\Book;
use App\Models\CartItem;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Add book to user's cart.
     *
     * @throws \Exception
     */
    public function addToCart(User $user, int $bookId): CartItem
    {
        $book = Book::findOrFail($bookId);

        // Validations
        $this->validateAddToCart($user, $book);

        return CartItem::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'added_at' => now(),
        ]);
    }

    /**
     * Validate add to cart operation.
     *
     * @throws \Exception
     */
    protected function validateAddToCart(User $user, Book $book): void
    {
        // Check if book is available
        if (!$book->isAvailable()) {
            throw new \Exception('This book is currently out of stock.');
        }

        // Check if already in cart
        if ($this->isInCart($user, $book->id)) {
            throw new \Exception('This book is already in your cart.');
        }

        // Check total limit (cart + active reservations)
        $maxReservations = Setting::get('max_active_reservations', 3);
        $cartCount = $this->getCartCount($user);
        $activeReservations = $this->getActiveReservationsCount($user);

        if (($cartCount + $activeReservations) >= $maxReservations) {
            throw new \Exception("You can only reserve up to {$maxReservations} books at a time.");
        }

        // Check if user has active loan or reservation for this book
        if ($this->hasActiveBookTransaction($user, $book->id)) {
            throw new \Exception('You already have an active loan or reservation for this book.');
        }

        // Check if user has unpaid fines
        if ($this->hasUnpaidFines($user)) {
            throw new \Exception('Please pay your outstanding fines before making a reservation.');
        }
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(User $user, int $cartItemId): bool
    {
        return CartItem::where('user_id', $user->id)
            ->where('id', $cartItemId)
            ->delete() > 0;
    }

    /**
     * Clear all items from cart.
     */
    public function clearCart(User $user): bool
    {
        return CartItem::where('user_id', $user->id)->delete() > 0;
    }

    /**
     * Get user's cart items with book details.
     */
    public function getCartItems(User $user): Collection
    {
        return CartItem::where('user_id', $user->id)
            ->with(['book.category'])
            ->get();
    }

    /**
     * Get cart count for user.
     */
    public function getCartCount(User $user): int
    {
        return CartItem::where('user_id', $user->id)->count();
    }

    /**
     * Check if book is in cart.
     */
    public function isInCart(User $user, int $bookId): bool
    {
        return CartItem::where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->exists();
    }

    /**
     * Get active reservations count.
     */
    protected function getActiveReservationsCount(User $user): int
    {
        return Reservation::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'ready'])
            ->where('expired_at', '>', now())
            ->count();
    }

    /**
     * Check if user has active transaction for this book.
     */
    protected function hasActiveBookTransaction(User $user, int $bookId): bool
    {
        // Check active loans
        $hasActiveLoan = Loan::where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->whereNull('returned_at')
            ->exists();

        if ($hasActiveLoan) {
            return true;
        }

        // Check active reservations
        $hasActiveReservation = DB::table('reservation_items')
            ->join('reservations', 'reservation_items.reservation_id', '=', 'reservations.id')
            ->where('reservations.user_id', $user->id)
            ->where('reservation_items.book_id', $bookId)
            ->whereIn('reservations.status', ['pending', 'ready'])
            ->where('reservations.expired_at', '>', now())
            ->exists();

        return $hasActiveReservation;
    }

    /**
     * Check if user has unpaid fines.
     */
    protected function hasUnpaidFines(User $user): bool
    {
        return Loan::where('user_id', $user->id)
            ->where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->exists();
    }

    /**
     * Validate checkout before creating reservation.
     *
     * @throws \Exception
     */
    public function validateCheckout(User $user): void
    {
        $cartItems = $this->getCartItems($user);

        if ($cartItems->isEmpty()) {
            throw new \Exception('Your cart is empty.');
        }

        // Re-validate each book
        foreach ($cartItems as $item) {
            if (!$item->book->isAvailable()) {
                throw new \Exception("Book '{$item->book->title}' is no longer available.");
            }
        }

        // Check unpaid fines again
        if ($this->hasUnpaidFines($user)) {
            throw new \Exception('Please pay your outstanding fines before checkout.');
        }
    }
}
