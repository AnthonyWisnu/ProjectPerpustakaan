<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Jobs\SendDueDateReminderNotification;
use Carbon\Carbon;

class SendDueDateReminders extends Command
{
    protected $signature = 'loans:send-reminders';
    protected $description = 'Send due date reminders to members with loans due soon';

    public function handle()
    {
        $this->info('Checking for loans due soon...');

        // Get loans due in 1 day (tomorrow) that are still active
        $tomorrow = Carbon::tomorrow()->toDateString();

        $loansToRemind = Loan::with(['user', 'book'])
            ->whereNull('returned_at')
            ->whereDate('due_date', $tomorrow)
            ->get();

        if ($loansToRemind->isEmpty()) {
            $this->info('No loans found that are due tomorrow.');
            return 0;
        }

        $count = 0;
        foreach ($loansToRemind as $loan) {
            // Dispatch job to send reminder notification
            SendDueDateReminderNotification::dispatch($loan);
            $count++;
        }

        $this->info("Sent {$count} due date reminder(s) successfully.");
        return 0;
    }
}
