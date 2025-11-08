<?php

namespace App\Jobs;

use App\Mail\SendReservationNotification as SendReservationNotificationMail;
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
 * Send reservation confirmation notification to user
 *
 * This job sends an email and creates an in-app notification
 * when a new reservation is created or its status changes.
 */
class SendReservationNotification implements ShouldQueue
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
            // Load relationships if not already loaded
            $this->reservation->loadMissing(['user', 'book']);

            // Send email notification
            Mail::to($this->reservation->user->email)
                ->send(new SendReservationNotificationMail($this->reservation));

            // Create in-app notification
            Notification::create([
                'user_id' => $this->reservation->user_id,
                'type' => 'reservation',
                'title' => 'Reservation Confirmed',
                'message' => "Your reservation for '{$this->reservation->book->title}' has been confirmed. Please pick it up before " . $this->reservation->expired_at->format('d M Y H:i'),
                'related_type' => Reservation::class,
                'related_id' => $this->reservation->id,
                'is_read' => false,
            ]);

            Log::info('Reservation notification sent', [
                'reservation_id' => $this->reservation->id,
                'user_id' => $this->reservation->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send reservation notification', [
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
        Log::error('SendReservationNotification job failed', [
            'reservation_id' => $this->reservation->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
