<?php

namespace App\Notifications;

use App\Mail\ReservationReady;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when a reservation is ready for pickup.
 */
class ReservationReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Reservation $reservation
     */
    public function __construct(
        public Reservation $reservation
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return ReservationReady
     */
    public function toMail(object $notifiable): ReservationReady
    {
        return new ReservationReady($this->reservation);
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $timeRemaining = $this->reservation->getTimeRemaining();
        $pickupDeadline = $this->reservation->expired_at?->format('d M Y H:i');

        return [
            'reservation_id' => $this->reservation->id,
            'reservation_code' => $this->reservation->reservation_code,
            'total_books' => $this->reservation->total_books,
            'status' => $this->reservation->status,
            'pickup_deadline' => $pickupDeadline,
            'expired_at' => $this->reservation->expired_at?->toDateTimeString(),
            'qr_code_path' => $this->reservation->qr_code_path,
            'action_url' => route('member.reservations.show', $this->reservation->id),
            'message' => "Your reservation {$this->reservation->reservation_code} is ready for pickup. Please collect your books before {$pickupDeadline}.",
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'reservation_code' => $this->reservation->reservation_code,
            'status' => $this->reservation->status,
            'pickup_deadline' => $this->reservation->expired_at?->format('d M Y H:i'),
        ];
    }
}
