<?php

namespace App\Jobs;

use App\Mail\DueDateReminder;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDueDateReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Loan $loan,
        public int $daysUntilDue
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->loan->user->email)
            ->send(new DueDateReminder($this->loan, $this->daysUntilDue));
    }
}
