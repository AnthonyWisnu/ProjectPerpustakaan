<?php

namespace App\Console\Commands;

use App\Jobs\SendOverdueNotificationEmail;
use App\Models\Loan;
use Illuminate\Console\Command;

class SendOverdueNotifications extends Command
{
    protected $signature = 'loans:send-overdue-notifications';
    protected $description = 'Send overdue notifications to members with overdue loans';

    public function handle()
    {
        $this->info('Checking for overdue loans...');

        // Get overdue loans (not returned and past due date)
        $overdueLoans = Loan::with(['user', 'book'])
            ->whereNull('returned_at')
            ->where('due_date', '<', now())
            ->get();

        $notificationsSent = 0;

        foreach ($overdueLoans as $loan) {
            $daysOverdue = now()->diffInDays($loan->due_date);

            // Send notification on day 1, 7, 14, 21, 30 overdue
            if (in_array($daysOverdue, [1, 7, 14, 21, 30])) {
                SendOverdueNotificationEmail::dispatch($loan, (int)$daysOverdue);
                $this->line("Overdue notification sent to {$loan->user->name} for loan {$loan->loan_code} ({$daysOverdue} days overdue)");
                $notificationsSent++;
            }
        }

        if ($notificationsSent === 0) {
            $this->info('No overdue notifications needed at this time.');
        } else {
            $this->info("✓ {$notificationsSent} overdue notification(s) queued successfully.");
        }

        return Command::SUCCESS;
    }
}
