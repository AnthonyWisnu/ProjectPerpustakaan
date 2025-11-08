<?php

namespace App\Mail;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

/**
 * Mail notification sent when a fine payment is received.
 *
 * This email confirms the payment of late return fines
 * and provides a receipt for the transaction.
 */
class FinePaymentReceived extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The loan instance.
     *
     * @var \App\Models\Loan
     */
    public Loan $loan;

    /**
     * The payment date.
     *
     * @var \Carbon\Carbon
     */
    public Carbon $paymentDate;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Loan  $loan
     * @return void
     */
    public function __construct(Loan $loan)
    {
        // Eager load relationships
        $this->loan = $loan->load('book', 'user');

        // Set payment date
        $this->paymentDate = $this->loan->fine_paid_at
            ? Carbon::parse($this->loan->fine_paid_at)
            : Carbon::now();
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Konfirmasi Pembayaran Denda - {$this->loan->book->title}",
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
            markdown: 'emails.fine-payment',
            with: [
                'loan' => $this->loan,
                'userName' => $this->loan->user->name,
                'bookTitle' => $this->loan->book->title,
                'bookAuthor' => $this->loan->book->author,
                'loanCode' => $this->loan->loan_code,
                'fineAmount' => $this->loan->fine_amount,
                'paymentDate' => $this->paymentDate,
                'borrowedAt' => $this->loan->borrowed_at,
                'dueDate' => $this->loan->due_date,
                'returnedAt' => $this->loan->returned_at,
                'daysOverdue' => $this->loan->getDaysOverdue(),
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
