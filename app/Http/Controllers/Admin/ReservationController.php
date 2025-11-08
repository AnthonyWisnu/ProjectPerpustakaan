<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\LoanService;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService,
        protected LoanService $loanService
    ) {}

    /**
     * Display a listing of reservations.
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'items.book']);

        // Search by reservation code or user
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reservation_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($query) use ($request) {
                      $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->latest()->paginate(15)->withQueryString();

        // Status counts for filter tabs
        $statusCounts = [
            'all' => Reservation::count(),
            'pending' => Reservation::where('status', 'pending')->count(),
            'ready' => Reservation::where('status', 'ready')->count(),
            'picked_up' => Reservation::where('status', 'picked_up')->count(),
            'cancelled' => Reservation::where('status', 'cancelled')->count(),
            'expired' => Reservation::where('status', 'expired')->count(),
        ];

        return view('admin.reservations.index', compact('reservations', 'statusCounts'));
    }

    /**
     * Display the specified reservation.
     */
    public function show($id)
    {
        $reservation = Reservation::with(['user', 'items.book'])->findOrFail($id);
        return view('admin.reservations.show', compact('reservation'));
    }

    /**
     * Mark reservation as ready for pickup.
     */
    public function markReady($id)
    {
        try {
            $reservation = Reservation::findOrFail($id);

            if ($reservation->status !== 'pending') {
                return redirect()
                    ->back()
                    ->with('error', 'Only pending reservations can be marked as ready.');
            }

            $reservation->update(['status' => 'ready']);

            return redirect()
                ->back()
                ->with('success', 'Reservation marked as ready for pickup.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Process pickup and create loans.
     */
    public function processPickup(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);

            if (!in_array($reservation->status, ['pending', 'ready'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Only pending or ready reservations can be picked up.');
            }

            // Create loans from reservation
            $loans = $this->loanService->createFromReservation($reservation, auth()->user());

            return redirect()
                ->route('admin.reservations.show', $reservation->id)
                ->with('success', count($loans) . ' loan(s) created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);

            if (!in_array($reservation->status, ['pending', 'ready'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Only pending or ready reservations can be cancelled.');
            }

            $this->reservationService->cancel(
                $reservation,
                $request->get('reason', 'Cancelled by admin')
            );

            return redirect()
                ->route('admin.reservations.index')
                ->with('success', 'Reservation cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Auto-cancel expired reservations.
     */
    public function autoCancelExpired()
    {
        try {
            $count = $this->reservationService->autoCancelExpired();

            return redirect()
                ->back()
                ->with('success', $count . ' expired reservation(s) cancelled automatically.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
