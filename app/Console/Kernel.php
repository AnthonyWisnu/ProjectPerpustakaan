
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto-cancel expired reservations - every hour
        $schedule->command('reservations:auto-cancel')
            ->hourly()
            ->withoutOverlapping();

        // Send due date reminders - daily at 9 AM
        $schedule->command('loans:send-reminders')
            ->dailyAt('09:00')
            ->withoutOverlapping();

        // Send overdue notifications - daily at 10 AM
        $schedule->command('loans:send-overdue-notifications')
            ->dailyAt('10:00')
            ->withoutOverlapping();

        // Clean old activity logs - monthly on the 1st at 2 AM
        $schedule->command('activity:clean')
            ->monthlyOn(1, '02:00')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
