
<?php

namespace App\Services;

use App\Models\Book;

class StockManager
{
    /**
     * Increment book stock
     */
    public function incrementStock(Book $book, int $quantity = 1): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $book->total_stock += $quantity;
        $book->available_stock += $quantity;

        return $book->save();
    }

    /**
     * Decrement book stock
     */
    public function decrementStock(Book $book, int $quantity = 1): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        if ($book->available_stock < $quantity) {
            return false;
        }

        $book->available_stock -= $quantity;

        return $book->save();
    }

    /**
     * Reserve stock (for reservations)
     */
    public function reserveStock(Book $book, int $quantity = 1): bool
    {
        return $this->decrementStock($book, $quantity);
    }

    /**
     * Release reserved stock (cancel reservation)
     */
    public function releaseStock(Book $book, int $quantity = 1): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        // Don't exceed total stock
        if ($book->available_stock + $quantity > $book->total_stock) {
            return false;
        }

        $book->available_stock += $quantity;

        return $book->save();
    }

    /**
     * Check if book is in stock
     */
    public function isInStock(Book $book, int $quantity = 1): bool
    {
        return $book->available_stock >= $quantity;
    }

    /**
     * Check if book is low stock
     */
    public function isLowStock(Book $book): bool
    {
        $threshold = config('library.stock.low_stock_threshold', 2);

        return $book->available_stock <= $threshold && $book->available_stock > 0;
    }

    /**
     * Check if book is out of stock
     */
    public function isOutOfStock(Book $book): bool
    {
        return $book->available_stock === 0;
    }

    /**
     * Get stock status
     */
    public function getStockStatus(Book $book): string
    {
        if ($this->isOutOfStock($book)) {
            return 'out-of-stock';
        }

        if ($this->isLowStock($book)) {
            return 'low-stock';
        }

        return 'in-stock';
    }

    /**
     * Get stock percentage
     */
    public function getStockPercentage(Book $book): int
    {
        if ($book->total_stock === 0) {
            return 0;
        }

        return (int) round(($book->available_stock / $book->total_stock) * 100);
    }

    /**
     * Update total stock
     */
    public function updateTotalStock(Book $book, int $newTotal): bool
    {
        if ($newTotal < 0) {
            return false;
        }

        $difference = $newTotal - $book->total_stock;

        $book->total_stock = $newTotal;
        $book->available_stock += $difference;

        // Ensure available stock doesn't go negative
        if ($book->available_stock < 0) {
            $book->available_stock = 0;
        }

        return $book->save();
    }
}
