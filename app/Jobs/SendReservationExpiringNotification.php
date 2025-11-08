<?php

namespace App\Jobs;

use App\Mail\SendReservationExpiring;
use App\Models\Notification;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Send expiring reservation notification to user
 *
 * This job sends a reminder to users when their reservation
 * is about to expire (typically sent a few hours before expiration).
 */
class SendReservationExpiringNotification implements ShouldQueue
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
    public $timeout = 120;

    /**
     * The reservation instance
     *
     * @var Reservation
     */
    protected $reservation;

    /**
     * Create a new job instance.
     *
     * @param Reservation $reservation
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Check if reservation is still active/pending
            if (!in_array($this->reservation->status, ['pending', 'ready'])) {
                Log::info('Skipping expiring notification - reservation no longer active', [
                    'reservation_id' => $this->reservation->id,
                    'status' => $this->reservation->status,
                ]);
                return;
            }

            // Load relationships if not already loaded
            $this->reservation->loadMissing(['user', 'book']);

            // Send email notification
            Mail::to($this->reservation->user->email)
                ->send(new SendReservationExpiring($this->reservation));

            // Create in-app notification
            Notification::create([
                'user_id' => $this->reservation->user_id,
                'type' => 'reservation_expiring',
                'title' => 'Reservation Expiring Soon',
                'message' => "Your reservation for '{$this->reservation->book->title}' will expire on " . $this->reservation->expired_at->format('d M Y H:i') . ". Please pick it up soon or it will be cancelled.",
                'related_type' => Reservation::class,
                'related_id' => $this->reservation->id,
                'is_read' => false,
            ]);

            Log::info('Reservation expiring notification sent', [
                'reservation_id' => $this->reservation->id,
                'user_id' => $this->reservation->user_id,
                'expires_at' => $this->reservation->expired_at,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send reservation expiring notification', [
                'reservation_id' => $this->reservation->id,
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
        Log::error('SendReservationExpiringNotification job failed', [
            'reservation_id' => $this->reservation->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
