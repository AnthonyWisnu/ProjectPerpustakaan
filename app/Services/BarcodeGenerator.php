
<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * BarcodeGenerator Service
 *
 * NOTE: This service requires the picqer/php-barcode-generator package.
 * Install with: composer require picqer/php-barcode-generator
 */
class BarcodeGenerator
{
    /**
     * Generate a barcode image.
     *
     * @param string $data The data to encode in the barcode
     * @param string $type The barcode type (CODE128, EAN13, etc.)
     * @return string|null Base64 encoded image or null on failure
     */
    public function generate(string $data, string $type = 'CODE128'): ?string
    {
        // Requires picqer/php-barcode-generator package
        // Example implementation:
        /*
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $barcode = $generator->getBarcode($data, $generator::TYPE_CODE_128);
            return base64_encode($barcode);
        } catch (\Exception $e) {
            \Log::error('Barcode generation failed: ' . $e->getMessage());
            return null;
        }
        */

        // Placeholder implementation - returns a simple base64 encoded placeholder
        \Log::warning('BarcodeGenerator::generate() called but picqer/php-barcode-generator is not installed.');

        return null;
    }

    /**
     * Generate and save a barcode for a book.
     *
     * @param Book $book
     * @return string|null The path to the saved barcode file
     */
    public function generateForBook(Book $book): ?string
    {
        // Use ISBN or generate a unique code
        $data = $book->isbn ?: 'BOOK-' . str_pad($book->id, 8, '0', STR_PAD_LEFT);

        // Generate barcode
        $barcodeImage = $this->generate($data);

        if (!$barcodeImage) {
            return null;
        }

        // Generate filename
        $filename = 'barcodes/books/' . Str::slug($book->title) . '-' . $book->id . '.png';

        // Save to storage
        try {
            Storage::disk('public')->put($filename, base64_decode($barcodeImage));

            // Update book record with barcode path
            $book->update(['barcode' => $filename]);

            \Log::info("Barcode generated for book: {$book->title}", ['path' => $filename]);

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Failed to save barcode: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the full storage path for a barcode file.
     *
     * @param string $filename
     * @return string
     */
    public function getPath(string $filename): string
    {
        return Storage::disk('public')->path($filename);
    }

    /**
     * Get the public URL for a barcode file.
     *
     * @param string $filename
     * @return string
     */
    public function getUrl(string $filename): string
    {
        return Storage::disk('public')->url($filename);
    }

    /**
     * Delete a barcode file.
     *
     * @param string $filename
     * @return bool
     */
    public function delete(string $filename): bool
    {
        try {
            if (Storage::disk('public')->exists($filename)) {
                Storage::disk('public')->delete($filename);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Failed to delete barcode: ' . $e->getMessage());
            return false;
        }
    }
}
