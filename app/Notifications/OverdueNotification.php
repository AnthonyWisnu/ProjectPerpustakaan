<?php

namespace App\Notifications;

use App\Mail\LoanOverdue;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when a loan becomes overdue.
 * This is a high priority notification.
 */
class OverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Loan $loan
     */
    public function __construct(
        public Loan $loan
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
     * @return LoanOverdue
     */
    public function toMail(object $notifiable): LoanOverdue
    {
        return new LoanOverdue($this->loan);
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $daysOverdue = $this->loan->getDaysOverdue();
        $fineAmount = $this->loan->fine_amount ?? $this->loan->calculateFine();
        $dueDateFormatted = $this->loan->due_date?->format('d M Y');

        return [
            'loan_id' => $this->loan->id,
            'loan_code' => $this->loan->loan_code,
            'book_id' => $this->loan->book_id,
            'book_title' => $this->loan->book?->title,
            'due_date' => $this->loan->due_date?->toDateString(),
            'due_date_formatted' => $dueDateFormatted,
            'days_overdue' => $daysOverdue,
            'fine_amount' => $fineAmount,
            'fine_paid' => $this->loan->fine_paid,
            'priority' => 'high',
            'urgent' => true,
            'action_url' => route('member.loans.index'),
            'message' => "Your loan for '{$this->loan->book?->title}' is overdue by {$daysOverdue} days. Fine amount: Rp " . number_format($fineAmount, 0, ',', '.'),
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
            'loan_id' => $this->loan->id,
            'loan_code' => $this->loan->loan_code,
            'book_title' => $this->loan->book?->title,
            'days_overdue' => $this->loan->getDaysOverdue(),
            'fine_amount' => $this->loan->fine_amount ?? $this->loan->calculateFine(),
            'priority' => 'high',
            'urgent' => true,
        ];
    }
}
