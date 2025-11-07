<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendDueDateReminders extends Command
{
    protected $signature = 'loans:send-reminders';
    protected $description = 'Send due date reminders to members';

    public function handle()
    {
        // Implementation will be added later
        $this->info('Reminders sent successfully.');
    }
}
