<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class AutoCancelExpiredReservations extends Command
{
    protected $signature = 'reservations:auto-cancel';
    protected $description = 'Automatically cancel expired reservations';

    public function __construct(
        protected ReservationService $reservationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Checking for expired reservations...');

        $count = $this->reservationService->autoCancelExpired();

        if ($count > 0) {
            $this->info("✓ {$count} expired reservation(s) cancelled successfully.");
        } else {
            $this->info('No expired reservations found.');
        }

        return Command::SUCCESS;
    }
}
