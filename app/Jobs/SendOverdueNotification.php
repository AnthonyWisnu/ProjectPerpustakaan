<?php

namespace App\Jobs;

use App\Mail\LoanOverdue;
use App\Models\Loan;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Send overdue notification to user
 *
 * This job notifies users that their borrowed book
 * is overdue and calculates the applicable fine.
 */
class SendOverdueNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * The loan instance
     *
     * @var Loan
     */
    protected $loan;

    /**
     * Create a new job instance.
     *
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Load relationships if not already loaded
            $this->loan->loadMissing(['user', 'book']);

            // Calculate fine amount
            $daysOverdue = now()->diffInDays($this->loan->due_date, false);
            $daysOverdue = abs($daysOverdue); // Get positive number of overdue days

            // Get fine rate from settings (default: 1000 per day)
            $finePerDay = setting('fine_per_day', 1000);
            $totalFine = $daysOverdue * $finePerDay;

            // Update loan with calculated fine
            $this->loan->update([
                'fine_amount' => $totalFine,
                'status' => 'overdue',
            ]);

            // Send email notification
            Mail::to($this->loan->user->email)
                ->send(new LoanOverdue($this->loan, $daysOverdue, $totalFine));

            // Create in-app notification
            Notification::create([
                'user_id' => $this->loan->user_id,
                'type' => 'loan_overdue',
                'title' => 'Book Overdue',
                'message' => "The book '{$this->loan->book->title}' is overdue by {$daysOverdue} day(s). Fine: Rp " . number_format($totalFine, 0, ',', '.') . ". Please return it immediately.",
                'related_type' => Loan::class,
                'related_id' => $this->loan->id,
                'is_read' => false,
            ]);

            Log::info('Overdue notification sent', [
                'loan_id' => $this->loan->id,
                'user_id' => $this->loan->user_id,
                'days_overdue' => $daysOverdue,
                'fine_amount' => $totalFine,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send overdue notification', [
                'loan_id' => $this->loan->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SendOverdueNotification job failed', [
            'loan_id' => $this->loan->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
