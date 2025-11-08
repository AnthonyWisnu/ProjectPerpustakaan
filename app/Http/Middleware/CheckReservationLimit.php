
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CartItem;
use App\Models\Reservation;
use App\Models\Setting;

class CheckReservationLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get max reservation limit from settings (default: 3)
        $maxReservations = Setting::get('max_books_per_reservation', 3);

        // Count active reservations (pending or ready)
        $activeReservationsCount = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'ready'])
            ->count();

        // Count items in cart
        $cartCount = CartItem::where('user_id', $user->id)->count();

        $totalCount = $activeReservationsCount + $cartCount;

        if ($totalCount >= $maxReservations) {
            return back()->with('error', "Anda sudah mencapai batas maksimal {$maxReservations} buku. Selesaikan reservasi aktif atau kosongkan keranjang terlebih dahulu.");
        }

        return $next($request);
    }
}
