<?php

namespace App\Notifications;

use App\Mail\ReservationCreated;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when a new reservation is created.
 */
class ReservationCreatedNotification extends Notification implements ShouldQueue
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
     * @return ReservationCreated
     */
    public function toMail(object $notifiable): ReservationCreated
    {
        return new ReservationCreated($this->reservation);
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'reservation_code' => $this->reservation->reservation_code,
            'total_books' => $this->reservation->total_books,
            'reserved_at' => $this->reservation->reserved_at?->toDateTimeString(),
            'expired_at' => $this->reservation->expired_at?->toDateTimeString(),
            'status' => $this->reservation->status,
            'action_url' => route('member.reservations.show', $this->reservation->id),
            'message' => "Your reservation {$this->reservation->reservation_code} has been created successfully.",
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
            'total_books' => $this->reservation->total_books,
            'status' => $this->reservation->status,
        ];
    }
}
