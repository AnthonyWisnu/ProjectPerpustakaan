<?php

namespace App\Jobs;

use App\Mail\OverdueNotification;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOverdueNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Loan $loan,
        public int $daysOverdue
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->loan->user->email)
            ->send(new OverdueNotification($this->loan, $this->daysOverdue));
    }
}
