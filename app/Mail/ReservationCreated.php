<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mail notification sent when a new reservation is created.
 *
 * This email confirms the reservation creation and provides
 * details about the reserved books and pickup deadline.
 */
class ReservationCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The reservation instance.
     *
     * @var \App\Models\Reservation
     */
    public Reservation $reservation;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return void
     */
    public function __construct(Reservation $reservation)
    {
        // Eager load the items with book relationship
        $this->reservation = $reservation->load('items.book', 'user');
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reservasi Buku Berhasil - {$this->reservation->reservation_code}",
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
            markdown: 'emails.reservation-created',
            with: [
                'reservation' => $this->reservation,
                'userName' => $this->reservation->user->name,
                'reservationCode' => $this->reservation->reservation_code,
                'totalBooks' => $this->reservation->total_books,
                'expiredAt' => $this->reservation->expired_at,
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
