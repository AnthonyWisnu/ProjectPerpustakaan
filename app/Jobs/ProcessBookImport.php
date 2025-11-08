<?php

namespace App\Jobs;

use App\Models\Book;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Process book import from Excel/CSV file
 *
 * This job handles bulk importing of books from uploaded files.
 * It validates data, creates/updates books, and notifies the admin
 * when the import process is complete.
 *
 * Note: Requires maatwebsite/excel package for Excel processing
 * Install with: composer require maatwebsite/excel
 */
class ProcessBookImport implements ShouldQueue
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
    public $timeout = 600; // 10 minutes for large imports

    /**
     * The file path to import
     *
     * @var string
     */
    protected $filePath;

    /**
     * The user ID who initiated the import
     *
     * @var int
     */
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param string $filePath
     * @param int $userId
     */
    public function __construct(string $filePath, int $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        try {
            // Check if file exists
            if (!Storage::exists($this->filePath)) {
                throw new \Exception("Import file not found: {$this->filePath}");
            }

            Log::info('Starting book import', [
                'file_path' => $this->filePath,
                'user_id' => $this->userId,
            ]);

            // TODO: Install maatwebsite/excel package
            // composer require maatwebsite/excel
            //
            // Use Excel::import() or Excel::toArray() to read the file
            // Example:
            // $rows = Excel::toArray(new BooksImport, $this->filePath)[0];

            // For demonstration, we'll use a simple CSV reader
            $file = Storage::get($this->filePath);
            $rows = array_map('str_getcsv', explode("\n", $file));
            $header = array_shift($rows); // Remove header row

            foreach ($rows as $index => $row) {
                if (empty($row[0])) {
                    continue; // Skip empty rows
                }

                try {
                    // Map CSV columns (adjust based on your format)
                    $data = [
                        'title' => $row[0] ?? null,
                        'author' => $row[1] ?? null,
                        'isbn' => $row[2] ?? null,
                        'publisher' => $row[3] ?? null,
                        'publication_year' => $row[4] ?? null,
                        'category_name' => $row[5] ?? null,
                        'total_stock' => $row[6] ?? 1,
                        'description' => $row[7] ?? null,
                    ];

                    // Validate data
                    $validator = Validator::make($data, [
                        'title' => 'required|string|max:255',
                        'author' => 'required|string|max:255',
                        'isbn' => 'nullable|string|unique:books,isbn',
                        'publisher' => 'nullable|string|max:255',
                        'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                        'category_name' => 'nullable|string',
                        'total_stock' => 'required|integer|min:1',
                    ]);

                    if ($validator->fails()) {
                        $failedCount++;
                        $errors[] = [
                            'row' => $index + 2, // +2 for header and 0-index
                            'data' => $data,
                            'errors' => $validator->errors()->all(),
                        ];
                        continue;
                    }

                    // Find or create category
                    $categoryId = null;
                    if (!empty($data['category_name'])) {
                        $category = Category::firstOrCreate(
                            ['name' => $data['category_name']],
                            ['description' => 'Auto-created from import']
                        );
                        $categoryId = $category->id;
                    }

                    // Create or update book
                    $bookData = [
                        'title' => $data['title'],
                        'author' => $data['author'],
                        'isbn' => $data['isbn'],
                        'publisher' => $data['publisher'],
                        'publication_year' => $data['publication_year'],
                        'category_id' => $categoryId,
                        'total_stock' => $data['total_stock'],
                        'available_stock' => $data['total_stock'],
                        'description' => $data['description'],
                    ];

                    if (!empty($data['isbn'])) {
                        Book::updateOrCreate(
                            ['isbn' => $data['isbn']],
                            $bookData
                        );
                    } else {
                        Book::create($bookData);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'row' => $index + 2,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Clean up the uploaded file
            Storage::delete($this->filePath);

            // Send notification to admin
            $message = "Book import completed. Success: {$successCount}, Failed: {$failedCount}";

            Notification::create([
                'user_id' => $this->userId,
                'type' => 'import_completed',
                'title' => 'Book Import Completed',
                'message' => $message,
                'is_read' => false,
            ]);

            Log::info('Book import completed', [
                'user_id' => $this->userId,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process book import', [
                'file_path' => $this->filePath,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            // Send error notification to admin
            Notification::create([
                'user_id' => $this->userId,
                'type' => 'import_failed',
                'title' => 'Book Import Failed',
                'message' => "Import failed: {$e->getMessage()}",
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
        Log::error('ProcessBookImport job failed', [
            'file_path' => $this->filePath,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);

        // Send failure notification
        Notification::create([
            'user_id' => $this->userId,
            'type' => 'import_failed',
            'title' => 'Book Import Failed',
            'message' => "Import job failed after multiple retries: {$exception->getMessage()}",
            'is_read' => false,
        ]);
    }
}
