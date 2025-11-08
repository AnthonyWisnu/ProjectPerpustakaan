<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected ReservationService $reservationService
    ) {}

    /**
     * Display user's cart.
     */
    public function index()
    {
        $cartItems = $this->cartService->getCartItems(auth()->user());
        $cartCount = $cartItems->count();

        return view('member.cart.index', compact('cartItems', 'cartCount'));
    }

    /**
     * Add book to cart.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
        ]);

        try {
            $this->cartService->addToCart(auth()->user(), $request->book_id);

            return redirect()
                ->back()
                ->with('success', 'Book added to cart successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove item from cart.
     */
    public function destroy($id)
    {
        try {
            $removed = $this->cartService->removeFromCart(auth()->user(), $id);

            if ($removed) {
                return redirect()
                    ->back()
                    ->with('success', 'Book removed from cart.');
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to remove book from cart.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Clear entire cart.
     */
    public function clear()
    {
        try {
            $this->cartService->clearCart(auth()->user());

            return redirect()
                ->route('member.cart.index')
                ->with('success', 'Cart cleared successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show checkout page.
     */
    public function checkout()
    {
        try {
            $this->cartService->validateCheckout(auth()->user());
            $cartItems = $this->cartService->getCartItems(auth()->user());

            return view('member.cart.checkout', compact('cartItems'));
        } catch (\Exception $e) {
            return redirect()
                ->route('member.cart.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Process checkout and create reservation.
     */
    public function processCheckout(Request $request)
    {
        try {
            $reservation = $this->reservationService->createFromCart(auth()->user());

            return redirect()
                ->route('member.reservations.show', $reservation->id)
                ->with('success', 'Reservation created successfully! Please pickup your books within 24 hours.');
        } catch (\Exception $e) {
            return redirect()
                ->route('member.cart.index')
                ->with('error', $e->getMessage());
        }
    }
}
