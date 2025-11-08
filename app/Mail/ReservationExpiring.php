<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

/**
 * Mail notification sent when a reservation is about to expire.
 *
 * This warning email alerts the user that their reservation
 * will expire soon and needs to be picked up.
 */
class ReservationExpiring extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The reservation instance.
     *
     * @var \App\Models\Reservation
     */
    public Reservation $reservation;

    /**
     * The time remaining before expiration.
     *
     * @var string
     */
    public string $timeRemaining;

    /**
     * The hours remaining before expiration.
     *
     * @var int
     */
    public int $hoursRemaining;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return void
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load('items.book', 'user');

        // Calculate time remaining
        $now = Carbon::now();
        $expiredAt = Carbon::parse($this->reservation->expired_at);

        $this->hoursRemaining = $now->diffInHours($expiredAt);
        $this->timeRemaining = $now->diffForHumans($expiredAt, [
            'parts' => 2,
            'short' => false,
        ]);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Peringatan: Reservasi Akan Berakhir - {$this->reservation->reservation_code}",
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservation-expiring',
            with: [
                'reservation' => $this->reservation,
                'userName' => $this->reservation->user->name,
                'reservationCode' => $this->reservation->reservation_code,
                'expiredAt' => $this->reservation->expired_at,
                'timeRemaining' => $this->timeRemaining,
                'hoursRemaining' => $this->hoursRemaining,
                'items' => $this->reservation->items,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
