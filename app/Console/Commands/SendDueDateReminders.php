<?php

namespace App\Console\Commands;

use App\Jobs\SendDueDateReminderEmail;
use App\Models\Loan;
use Illuminate\Console\Command;

class SendDueDateReminders extends Command
{
    protected $signature = 'loans:send-reminders';
    protected $description = 'Send due date reminders to members with active loans';

    public function handle()
    {
        $this->info('Checking for loans requiring reminders...');

        // Get active loans (not returned)
        $activeLoans = Loan::with(['user', 'book'])
            ->whereNull('returned_at')
            ->where('due_date', '>=', now())
            ->get();

        $remindersSent = 0;

        foreach ($activeLoans as $loan) {
            $daysUntilDue = now()->diffInDays($loan->due_date, false);

            // Send reminder 3 days before and 1 day before
            if (in_array($daysUntilDue, [3, 1])) {
                SendDueDateReminderEmail::dispatch($loan, (int)$daysUntilDue);
                $this->line("Reminder sent to {$loan->user->name} for loan {$loan->loan_code} (due in {$daysUntilDue} days)");
                $remindersSent++;
            }
        }

        if ($remindersSent === 0) {
            $this->info('No reminders needed at this time.');
        } else {
            $this->info("✓ {$remindersSent} reminder(s) queued successfully.");
        }

        return Command::SUCCESS;
    }
}
