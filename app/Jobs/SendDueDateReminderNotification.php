<?php

namespace App\Jobs;

use App\Mail\LoanDueDateReminder;
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
 * Send due date reminder notification to user
 *
 * This job reminds users that their borrowed book
 * is due soon (typically sent 1-2 days before due date).
 */
class SendDueDateReminderNotification implements ShouldQueue
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
            // Check if loan is still active (not returned yet)
            if ($this->loan->status !== 'borrowed') {
                Log::info('Skipping due date reminder - loan no longer active', [
                    'loan_id' => $this->loan->id,
                    'status' => $this->loan->status,
                ]);
                return;
            }

            // Load relationships if not already loaded
            $this->loan->loadMissing(['user', 'book']);

            // Send email notification
            Mail::to($this->loan->user->email)
                ->send(new LoanDueDateReminder($this->loan));

            // Create in-app notification
            Notification::create([
                'user_id' => $this->loan->user_id,
                'type' => 'loan_due_soon',
                'title' => 'Book Due Soon',
                'message' => "The book '{$this->loan->book->title}' is due on " . $this->loan->due_date->format('d M Y') . ". Please return it on time to avoid late fees.",
                'related_type' => Loan::class,
                'related_id' => $this->loan->id,
                'is_read' => false,
            ]);

            Log::info('Due date reminder notification sent', [
                'loan_id' => $this->loan->id,
                'user_id' => $this->loan->user_id,
                'due_date' => $this->loan->due_date,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send due date reminder notification', [
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
        Log::error('SendDueDateReminderNotification job failed', [
            'loan_id' => $this->loan->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
