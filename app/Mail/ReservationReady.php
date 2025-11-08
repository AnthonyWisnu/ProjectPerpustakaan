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
 * Mail notification sent when reserved books are ready for pickup.
 *
 * This email notifies the user that their reserved books have been
 * prepared and are ready to be collected from the library.
 */
class ReservationReady extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The reservation instance.
     *
     * @var \App\Models\Reservation
     */
    public Reservation $reservation;

    /**
     * The pickup deadline.
     *
     * @var \Carbon\Carbon
     */
    public Carbon $pickupDeadline;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return void
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load('items.book', 'user');

        // Set pickup deadline (expired_at is the deadline)
        $this->pickupDeadline = Carbon::parse($this->reservation->expired_at);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Buku Siap Diambil - {$this->reservation->reservation_code}",
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
            markdown: 'emails.reservation-ready',
            with: [
                'reservation' => $this->reservation,
                'userName' => $this->reservation->user->name,
                'reservationCode' => $this->reservation->reservation_code,
                'totalBooks' => $this->reservation->total_books,
                'pickupDeadline' => $this->pickupDeadline,
                'items' => $this->reservation->items,
                'qrCodePath' => $this->reservation->qr_code_path,
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
