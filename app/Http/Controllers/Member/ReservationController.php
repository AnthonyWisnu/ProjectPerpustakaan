<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService
    ) {}

    /**
     * Display user's reservations.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Reservation::where('user_id', auth()->id())
            ->with(['items.book.category'])
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $reservations = $query->paginate(10);

        // Count by status for filter tabs
        $statusCounts = [
            'all' => Reservation::where('user_id', auth()->id())->count(),
            'pending' => Reservation::where('user_id', auth()->id())->where('status', 'pending')->count(),
            'ready' => Reservation::where('user_id', auth()->id())->where('status', 'ready')->count(),
            'picked_up' => Reservation::where('user_id', auth()->id())->where('status', 'picked_up')->count(),
            'cancelled' => Reservation::where('user_id', auth()->id())->where('status', 'cancelled')->count(),
            'expired' => Reservation::where('user_id', auth()->id())->where('status', 'expired')->count(),
        ];

        return view('member.reservations.index', compact('reservations', 'statusCounts', 'status'));
    }

    /**
     * Display reservation details.
     */
    public function show($id)
    {
        $reservation = Reservation::where('user_id', auth()->id())
            ->with(['items.book.category'])
            ->findOrFail($id);

        return view('member.reservations.show', compact('reservation'));
    }

    /**
     * Cancel a reservation.
     */
    public function cancel($id, Request $request)
    {
        try {
            $reservation = Reservation::where('user_id', auth()->id())
                ->findOrFail($id);

            if (!in_array($reservation->status, ['pending', 'ready'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Only pending or ready reservations can be cancelled.');
            }

            $this->reservationService->cancel($reservation, $request->get('reason'));

            return redirect()
                ->route('member.reservations.index')
                ->with('success', 'Reservation cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
