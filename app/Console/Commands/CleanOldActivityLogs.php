<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class CleanOldActivityLogs extends Command
{
    protected $signature = 'activity:clean {--days=90 : Number of days to keep logs}';
    protected $description = 'Clean old activity logs older than specified days';

    public function handle()
    {
        $days = $this->option('days');
        $this->info("Cleaning activity logs older than {$days} days...");

        $deletedCount = ActivityLog::where('created_at', '<', now()->subDays($days))
            ->delete();

        if ($deletedCount > 0) {
            $this->info("✓ {$deletedCount} old activity log(s) deleted successfully.");
        } else {
            $this->info('No old activity logs to clean.');
        }

        return Command::SUCCESS;
    }
}
