<?php

namespace App\Notifications;

use App\Mail\LoanDueDateReminder;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent to remind users about upcoming loan due dates.
 */
class DueDateReminderNotification extends Notification implements ShouldQueue
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
     * @return LoanDueDateReminder
     */
    public function toMail(object $notifiable): LoanDueDateReminder
    {
        return new LoanDueDateReminder($this->loan);
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $daysUntilDue = now()->diffInDays($this->loan->due_date, false);
        $dueDateFormatted = $this->loan->due_date?->format('d M Y');

        return [
            'loan_id' => $this->loan->id,
            'loan_code' => $this->loan->loan_code,
            'book_id' => $this->loan->book_id,
            'book_title' => $this->loan->book?->title,
            'due_date' => $this->loan->due_date?->toDateString(),
            'due_date_formatted' => $dueDateFormatted,
            'days_until_due' => max(0, $daysUntilDue),
            'borrowed_at' => $this->loan->borrowed_at?->toDateTimeString(),
            'action_url' => route('member.loans.index'),
            'message' => "Reminder: Your loan for '{$this->loan->book?->title}' is due on {$dueDateFormatted}.",
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
            'due_date' => $this->loan->due_date?->toDateString(),
        ];
    }
}
