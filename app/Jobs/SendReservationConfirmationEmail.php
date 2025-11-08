<?php

namespace App\Jobs;

use App\Mail\ReservationConfirmation;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReservationConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Reservation $reservation
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->reservation->user->email)
            ->send(new ReservationConfirmation($this->reservation));
    }
}
