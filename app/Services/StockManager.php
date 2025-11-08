<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\DB;

/**
 * StockManager Service
 *
 * Manages book stock operations with database transactions
 * to ensure data consistency and prevent race conditions.
 */
class StockManager
{
    /**
     * Check if a book has available stock.
     *
     * @param Book $book
     * @param int $quantity
     * @return bool
     */
    public function checkAvailability(Book $book, int $quantity = 1): bool
    {
        // Refresh to get latest stock count
        $book->refresh();

        return $book->available_stock >= $quantity;
    }

    /**
     * Reserve stock for a reservation (decrement available stock).
     *
     * @param Book $book
     * @param int $quantity
     * @return bool
     * @throws \Exception
     */
    public function reserve(Book $book, int $quantity = 1): bool
    {
        return DB::transaction(function () use ($book, $quantity) {
            // Lock the row for update to prevent race conditions
            $book = Book::lockForUpdate()->find($book->id);

            if (!$book) {
                throw new \Exception("Book not found");
            }

            if ($book->available_stock < $quantity) {
                throw new \Exception("Insufficient stock. Available: {$book->available_stock}, Requested: {$quantity}");
            }

            $book->decrement('available_stock', $quantity);

            \Log::info("Stock reserved for book: {$book->title}", [
                'book_id' => $book->id,
                'quantity' => $quantity,
                'remaining_stock' => $book->fresh()->available_stock,
            ]);

            return true;
        });
    }

    /**
     * Release reserved stock (increment available stock) when reservation is cancelled.
     *
     * @param Book $book
     * @param int $quantity
     * @return bool
     * @throws \Exception
     */
    public function release(Book $book, int $quantity = 1): bool
    {
        return DB::transaction(function () use ($book, $quantity) {
            // Lock the row for update
            $book = Book::lockForUpdate()->find($book->id);

            if (!$book) {
                throw new \Exception("Book not found");
            }

            // Don't allow available stock to exceed total stock
            $newAvailableStock = $book->available_stock + $quantity;
            if ($newAvailableStock > $book->total_stock) {
                \Log::warning("Attempted to release more stock than total stock", [
                    'book_id' => $book->id,
                    'current_available' => $book->available_stock,
                    'total_stock' => $book->total_stock,
                    'quantity_to_release' => $quantity,
                ]);

                // Adjust to maximum allowed
                $quantity = $book->total_stock - $book->available_stock;
            }

            if ($quantity > 0) {
                $book->increment('available_stock', $quantity);

                \Log::info("Stock released for book: {$book->title}", [
                    'book_id' => $book->id,
                    'quantity' => $quantity,
                    'new_stock' => $book->fresh()->available_stock,
                ]);
            }

            return true;
        });
    }

    /**
     * Borrow a book (decrement stock for loan, if not from reservation).
     * If the loan is from a reservation, stock is already decremented.
     *
     * @param Book $book
     * @return bool
     * @throws \Exception
     */
    public function borrow(Book $book): bool
    {
        return $this->reserve($book, 1);
    }

    /**
     * Return a book (increment stock when book is returned).
     *
     * @param Book $book
     * @return bool
     * @throws \Exception
     */
    public function returnBook(Book $book): bool
    {
        return $this->release($book, 1);
    }

    /**
     * Get books with low stock.
     *
     * @param int $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockBooks(int $threshold = 5)
    {
        return Book::with('category')
            ->where('available_stock', '>', 0)
            ->where('available_stock', '<=', $threshold)
            ->orderBy('available_stock', 'asc')
            ->get();
    }

    /**
     * Get out of stock books.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOutOfStockBooks()
    {
        return Book::with('category')
            ->where('available_stock', 0)
            ->get();
    }

    /**
     * Adjust stock manually (for admin operations).
     *
     * @param Book $book
     * @param int $newTotalStock
     * @param string|null $reason
     * @return bool
     * @throws \Exception
     */
    public function adjustStock(Book $book, int $newTotalStock, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($book, $newTotalStock, $reason) {
            // Lock the row for update
            $book = Book::lockForUpdate()->find($book->id);

            if (!$book) {
                throw new \Exception("Book not found");
            }

            if ($newTotalStock < 0) {
                throw new \Exception("Stock cannot be negative");
            }

            $oldTotalStock = $book->total_stock;
            $borrowedStock = $book->total_stock - $book->available_stock;

            // Calculate new available stock
            $newAvailableStock = max(0, $newTotalStock - $borrowedStock);

            $book->update([
                'total_stock' => $newTotalStock,
                'available_stock' => $newAvailableStock,
            ]);

            \Log::info("Stock adjusted for book: {$book->title}", [
                'book_id' => $book->id,
                'old_total_stock' => $oldTotalStock,
                'new_total_stock' => $newTotalStock,
                'new_available_stock' => $newAvailableStock,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Get stock summary for a book.
     *
     * @param Book $book
     * @return array
     */
    public function getStockSummary(Book $book): array
    {
        $book->refresh();

        return [
            'book_id' => $book->id,
            'title' => $book->title,
            'total_stock' => $book->total_stock,
            'available_stock' => $book->available_stock,
            'borrowed_stock' => $book->total_stock - $book->available_stock,
            'utilization_rate' => $book->total_stock > 0
                ? round((($book->total_stock - $book->available_stock) / $book->total_stock) * 100, 2)
                : 0,
            'status' => $this->getStockStatus($book),
        ];
    }

    /**
     * Get overall stock statistics.
     *
     * @return array
     */
    public function getOverallStatistics(): array
    {
        $totalBooks = Book::count();
        $totalStock = Book::sum('total_stock');
        $availableStock = Book::sum('available_stock');
        $borrowedStock = $totalStock - $availableStock;

        return [
            'total_books' => $totalBooks,
            'total_stock' => $totalStock,
            'available_stock' => $availableStock,
            'borrowed_stock' => $borrowedStock,
            'utilization_rate' => $totalStock > 0
                ? round(($borrowedStock / $totalStock) * 100, 2)
                : 0,
            'low_stock_books' => $this->getLowStockBooks()->count(),
            'out_of_stock_books' => $this->getOutOfStockBooks()->count(),
        ];
    }

    /**
     * Get stock status for a book.
     *
     * @param Book $book
     * @return string
     */
    protected function getStockStatus(Book $book): string
    {
        if ($book->available_stock <= 0) {
            return 'out_of_stock';
        } elseif ($book->available_stock <= 5) {
            return 'low_stock';
        } else {
            return 'available';
        }
    }

    /**
     * Sync stock for a book (recalculate available stock based on active loans).
     * This is useful for fixing stock discrepancies.
     *
     * @param Book $book
     * @return bool
     * @throws \Exception
     */
    public function syncStock(Book $book): bool
    {
        return DB::transaction(function () use ($book) {
            // Lock the row for update
            $book = Book::lockForUpdate()->find($book->id);

            if (!$book) {
                throw new \Exception("Book not found");
            }

            // Count active loans for this book
            $activeLoansCount = $book->loans()->active()->count();

            // Calculate correct available stock
            $correctAvailableStock = max(0, $book->total_stock - $activeLoansCount);

            $oldAvailableStock = $book->available_stock;

            $book->update(['available_stock' => $correctAvailableStock]);

            \Log::info("Stock synced for book: {$book->title}", [
                'book_id' => $book->id,
                'old_available_stock' => $oldAvailableStock,
                'new_available_stock' => $correctAvailableStock,
                'active_loans' => $activeLoansCount,
            ]);

            return true;
        });
    }
}
