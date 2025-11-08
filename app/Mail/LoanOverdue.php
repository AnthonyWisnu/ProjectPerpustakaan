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
 * Mail notification sent when a loan becomes overdue.
 *
 * This email warns the user that their borrowed book is late
 * and includes information about accumulated fines.
 */
class LoanOverdue extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The loan instance.
     *
     * @var \App\Models\Loan
     */
    public Loan $loan;

    /**
     * The number of days overdue.
     *
     * @var int
     */
    public int $daysOverdue;

    /**
     * The calculated fine amount.
     *
     * @var float
     */
    public float $fineAmount;

    /**
     * The fine rate per day.
     *
     * @var float
     */
    public float $fineRatePerDay = 1000;

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

        // Calculate overdue information
        $this->daysOverdue = $this->loan->getDaysOverdue();
        $this->fineAmount = $this->loan->calculateFine($this->fineRatePerDay);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Peringatan: Buku Terlambat Dikembalikan - {$this->loan->book->title}",
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
            markdown: 'emails.loan-overdue',
            with: [
                'loan' => $this->loan,
                'userName' => $this->loan->user->name,
                'bookTitle' => $this->loan->book->title,
                'bookAuthor' => $this->loan->book->author,
                'loanCode' => $this->loan->loan_code,
                'borrowedAt' => $this->loan->borrowed_at,
                'dueDate' => $this->loan->due_date,
                'daysOverdue' => $this->daysOverdue,
                'fineAmount' => $this->fineAmount,
                'fineRatePerDay' => $this->fineRatePerDay,
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
