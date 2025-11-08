<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Generate various types of reports
 *
 * This job generates reports asynchronously and notifies
 * the user when the report is ready for download.
 *
 * Supported report types:
 * - loans: Loan/borrowing statistics and history
 * - finances: Financial reports including fines
 * - inventory: Book inventory and stock reports
 * - members: Member statistics and activity
 */
class GenerateReport implements ShouldQueue
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
    public $timeout = 600; // 10 minutes for large reports

    /**
     * The report type
     *
     * @var string
     */
    protected $reportType;

    /**
     * Report filters and parameters
     *
     * @var array
     */
    protected $parameters;

    /**
     * The user ID who requested the report
     *
     * @var int
     */
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param string $reportType
     * @param array $parameters
     * @param int $userId
     */
    public function __construct(string $reportType, array $parameters, int $userId)
    {
        $this->reportType = $reportType;
        $this->parameters = $parameters;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @param ReportService $reportService
     * @return void
     */
    public function handle(ReportService $reportService)
    {
        try {
            Log::info('Starting report generation', [
                'type' => $this->reportType,
                'user_id' => $this->userId,
                'parameters' => $this->parameters,
            ]);

            $reportPath = null;
            $format = $this->parameters['format'] ?? 'pdf';

            // Generate report based on type
            switch ($this->reportType) {
                case 'loans':
                    $reportPath = $reportService->generateLoansReport(
                        $this->parameters,
                        $format
                    );
                    break;

                case 'finances':
                    $reportPath = $reportService->generateFinancesReport(
                        $this->parameters,
                        $format
                    );
                    break;

                case 'inventory':
                    $reportPath = $reportService->generateInventoryReport(
                        $this->parameters,
                        $format
                    );
                    break;

                case 'members':
                    $reportPath = $reportService->generateMembersReport(
                        $this->parameters,
                        $format
                    );
                    break;

                default:
                    throw new \InvalidArgumentException("Invalid report type: {$this->reportType}");
            }

            // Verify report file was created
            if (!$reportPath || !Storage::exists($reportPath)) {
                throw new \Exception('Report file was not generated successfully');
            }

            // Generate download URL
            $downloadUrl = route('admin.reports.download', [
                'filename' => basename($reportPath),
            ]);

            // Send notification to user with download link
            Notification::create([
                'user_id' => $this->userId,
                'type' => 'report_ready',
                'title' => 'Report Ready',
                'message' => "Your {$this->reportType} report is ready. Click here to download.",
                'action_url' => $downloadUrl,
                'is_read' => false,
            ]);

            Log::info('Report generation completed', [
                'type' => $this->reportType,
                'user_id' => $this->userId,
                'report_path' => $reportPath,
                'file_size' => Storage::size($reportPath),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate report', [
                'type' => $this->reportType,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            // Send error notification to user
            Notification::create([
                'user_id' => $this->userId,
                'type' => 'report_failed',
                'title' => 'Report Generation Failed',
                'message' => "Failed to generate {$this->reportType} report: {$e->getMessage()}",
                'is_read' => false,
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
        Log::error('GenerateReport job failed', [
            'type' => $this->reportType,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);

        // Send failure notification
        Notification::create([
            'user_id' => $this->userId,
            'type' => 'report_failed',
            'title' => 'Report Generation Failed',
            'message' => "Report generation failed after multiple retries: {$exception->getMessage()}",
            'is_read' => false,
        ]);
    }
}
