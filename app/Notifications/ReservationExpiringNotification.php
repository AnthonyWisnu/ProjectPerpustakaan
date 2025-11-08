<?php

namespace App\Notifications;

use App\Mail\ReservationExpiring;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when a reservation is about to expire.
 * This is a high priority notification.
 */
class ReservationExpiringNotification extends Notification implements ShouldQueue
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
        // Set high priority for urgent notifications
        $this->onQueue('high');
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
     * @return ReservationExpiring
     */
    public function toMail(object $notifiable): ReservationExpiring
    {
        return new ReservationExpiring($this->reservation);
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
        $hoursRemaining = $timeRemaining ? now()->diffInHours($timeRemaining) : 0;

        return [
            'reservation_id' => $this->reservation->id,
            'reservation_code' => $this->reservation->reservation_code,
            'total_books' => $this->reservation->total_books,
            'expired_at' => $this->reservation->expired_at?->toDateTimeString(),
            'hours_remaining' => $hoursRemaining,
            'status' => $this->reservation->status,
            'priority' => 'high',
            'urgent' => true,
            'action_url' => route('member.reservations.show', $this->reservation->id),
            'message' => "Your reservation {$this->reservation->reservation_code} will expire in {$hoursRemaining} hours. Please pick up your books soon.",
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
            'expired_at' => $this->reservation->expired_at?->toDateTimeString(),
            'priority' => 'high',
            'urgent' => true,
        ];
    }
}
