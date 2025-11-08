<?php

namespace App\Jobs;

use App\Mail\FinePaymentReceived;
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
 * Send fine payment confirmation notification to user
 *
 * This job sends a payment confirmation when a user
 * has paid their overdue fine.
 */
class SendFinePaymentNotification implements ShouldQueue
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

            // Send email notification
            Mail::to($this->loan->user->email)
                ->send(new FinePaymentReceived($this->loan));

            // Create in-app notification
            Notification::create([
                'user_id' => $this->loan->user_id,
                'type' => 'fine_payment',
                'title' => 'Fine Payment Received',
                'message' => "Your payment of Rp " . number_format($this->loan->fine_amount, 0, ',', '.') . " for '{$this->loan->book->title}' has been received. Thank you!",
                'related_type' => Loan::class,
                'related_id' => $this->loan->id,
                'is_read' => false,
            ]);

            Log::info('Fine payment notification sent', [
                'loan_id' => $this->loan->id,
                'user_id' => $this->loan->user_id,
                'fine_amount' => $this->loan->fine_amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send fine payment notification', [
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
        Log::error('SendFinePaymentNotification job failed', [
            'loan_id' => $this->loan->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
