<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use Carbon\Carbon;

class CleanOldActivityLogs extends Command
{
    protected $signature = 'activity:clean {--days=90 : Number of days to keep}';
    protected $description = 'Clean old activity logs older than specified days';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning activity logs older than {$days} days (before {$cutoffDate->toDateString()})...");

        $count = ActivityLog::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$count} old activity log(s) successfully.");
        return 0;
    }
}
