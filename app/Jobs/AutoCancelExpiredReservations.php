<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Automatically cancel expired reservations
 *
 * This scheduled job runs periodically to find and cancel
 * all reservations that have passed their expiration date.
 * It also releases the reserved stock back to available inventory.
 */
class AutoCancelExpiredReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $cancelledCount = 0;

            // Find all expired reservations
            $expiredReservations = Reservation::where('status', 'pending')
                ->where('expired_at', '<', now())
                ->orWhere(function ($query) {
                    $query->where('status', 'ready')
                        ->where('expired_at', '<', now());
                })
                ->get();

            Log::info('Auto-cancelling expired reservations', [
                'found_count' => $expiredReservations->count(),
            ]);

            foreach ($expiredReservations as $reservation) {
                DB::transaction(function () use ($reservation, &$cancelledCount) {
                    // Load relationships
                    $reservation->loadMissing(['user', 'book']);

                    // Cancel the reservation
                    $reservation->update([
                        'status' => 'expired',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Automatically cancelled due to expiration',
                    ]);

                    // Release stock back to available inventory
                    $reservation->book->increment('available_stock');

                    // Send notification to user
                    Notification::create([
                        'user_id' => $reservation->user_id,
                        'type' => 'reservation_expired',
                        'title' => 'Reservation Expired',
                        'message' => "Your reservation for '{$reservation->book->title}' has been automatically cancelled because it was not picked up before the expiration time.",
                        'related_type' => Reservation::class,
                        'related_id' => $reservation->id,
                        'is_read' => false,
                    ]);

                    $cancelledCount++;

                    Log::info('Reservation auto-cancelled', [
                        'reservation_id' => $reservation->id,
                        'user_id' => $reservation->user_id,
                        'book_id' => $reservation->book_id,
                        'expired_at' => $reservation->expired_at,
                    ]);
                });
            }

            Log::info('Auto-cancel expired reservations completed', [
                'cancelled_count' => $cancelledCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to auto-cancel expired reservations', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('AutoCancelExpiredReservations job failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
