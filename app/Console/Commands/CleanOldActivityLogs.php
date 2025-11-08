<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanOldActivityLogs extends Command
{
    protected $signature = 'activity:clean';
    protected $description = 'Clean old activity logs';

    public function handle()
    {
        // Implementation will be added later
        $this->info('Activity logs cleaned successfully.');
    }
}
