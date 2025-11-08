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
 * Mail notification sent as a reminder before loan due date.
 *
 * This email reminds the user that a borrowed book must be
 * returned soon to avoid late fees.
 */
class LoanDueDateReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The loan instance.
     *
     * @var \App\Models\Loan
     */
    public Loan $loan;

    /**
     * The due date.
     *
     * @var \Carbon\Carbon
     */
    public Carbon $dueDate;

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
        $this->dueDate = Carbon::parse($this->loan->due_date);
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pengingat: Buku Harus Dikembalikan Besok - {$this->loan->book->title}",
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
            markdown: 'emails.loan-due-reminder',
            with: [
                'loan' => $this->loan,
                'userName' => $this->loan->user->name,
                'bookTitle' => $this->loan->book->title,
                'bookAuthor' => $this->loan->book->author,
                'loanCode' => $this->loan->loan_code,
                'borrowedAt' => $this->loan->borrowed_at,
                'dueDate' => $this->dueDate,
                'canExtend' => $this->loan->canBeExtended(),
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
