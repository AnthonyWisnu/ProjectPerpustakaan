<?php

namespace App\Jobs;

use App\Services\QRCodeGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generate QR code for reservations or member cards
 *
 * This job generates QR codes for various purposes:
 * - Reservation pickup verification
 * - Member card identification
 */
class GenerateQRCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * The model instance (Reservation or User)
     *
     * @var Model
     */
    protected $model;

    /**
     * The type of QR code to generate
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new job instance.
     *
     * @param Model $model The model instance (Reservation or User)
     * @param string $type The QR code type ('reservation' or 'member_card')
     */
    public function __construct(Model $model, string $type)
    {
        $this->model = $model;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(QRCodeGenerator $qrCodeGenerator)
    {
        try {
            $qrCodePath = null;

            switch ($this->type) {
                case 'reservation':
                    // Generate QR code for reservation
                    $data = json_encode([
                        'type' => 'reservation',
                        'id' => $this->model->id,
                        'reservation_code' => $this->model->reservation_code,
                        'user_id' => $this->model->user_id,
                        'book_id' => $this->model->book_id,
                    ]);

                    $filename = "reservation_{$this->model->id}.png";
                    $qrCodePath = $qrCodeGenerator->generate($data, $filename);

                    // Update reservation with QR code path
                    $this->model->update(['qr_code_path' => $qrCodePath]);

                    Log::info('QR code generated for reservation', [
                        'reservation_id' => $this->model->id,
                        'qr_code_path' => $qrCodePath,
                    ]);
                    break;

                case 'member_card':
                    // Generate QR code for member card
                    $data = json_encode([
                        'type' => 'member',
                        'id' => $this->model->id,
                        'member_code' => $this->model->member_code ?? $this->model->id,
                        'name' => $this->model->name,
                        'email' => $this->model->email,
                    ]);

                    $filename = "member_{$this->model->id}.png";
                    $qrCodePath = $qrCodeGenerator->generate($data, $filename);

                    // Update user with QR code path
                    $this->model->update(['qr_code_path' => $qrCodePath]);

                    Log::info('QR code generated for member card', [
                        'user_id' => $this->model->id,
                        'qr_code_path' => $qrCodePath,
                    ]);
                    break;

                default:
                    throw new \InvalidArgumentException("Invalid QR code type: {$this->type}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate QR code', [
                'model_type' => get_class($this->model),
                'model_id' => $this->model->id,
                'type' => $this->type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('GenerateQRCode job failed', [
            'model_type' => get_class($this->model),
            'model_id' => $this->model->id,
            'type' => $this->type,
            'error' => $exception->getMessage(),
        ]);
    }
}
