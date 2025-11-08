
<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Services\StockManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagerTest extends TestCase
{
    use RefreshDatabase;

    protected StockManager $stockManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockManager = new StockManager();
    }

    /**
     * Test increment stock increases total and available
     */
    public function test_increment_stock_increases_both_totals(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 10]);

        $result = $this->stockManager->incrementStock($book, 5);

        $this->assertTrue($result);
        $this->assertEquals(15, $book->total_stock);
        $this->assertEquals(15, $book->available_stock);
    }

    /**
     * Test increment stock with zero quantity returns false
     */
    public function test_increment_stock_with_zero_returns_false(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 10]);

        $result = $this->stockManager->incrementStock($book, 0);

        $this->assertFalse($result);
        $this->assertEquals(10, $book->fresh()->total_stock);
    }

    /**
     * Test increment stock with negative quantity returns false
     */
    public function test_increment_stock_with_negative_returns_false(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 10]);

        $result = $this->stockManager->incrementStock($book, -5);

        $this->assertFalse($result);
        $this->assertEquals(10, $book->fresh()->total_stock);
    }

    /**
     * Test decrement stock decreases available
     */
    public function test_decrement_stock_decreases_available(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 10]);

        $result = $this->stockManager->decrementStock($book, 3);

        $this->assertTrue($result);
        $this->assertEquals(10, $book->total_stock);
        $this->assertEquals(7, $book->available_stock);
    }

    /**
     * Test cannot decrement more than available stock
     */
    public function test_cannot_decrement_more_than_available(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 5]);

        $result = $this->stockManager->decrementStock($book, 10);

        $this->assertFalse($result);
        $this->assertEquals(5, $book->fresh()->available_stock);
    }

    /**
     * Test reserve stock works like decrement
     */
    public function test_reserve_stock_decrements_available(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 10]);

        $result = $this->stockManager->reserveStock($book, 2);

        $this->assertTrue($result);
        $this->assertEquals(8, $book->available_stock);
    }

    /**
     * Test release stock increases available
     */
    public function test_release_stock_increases_available(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 7]);

        $result = $this->stockManager->releaseStock($book, 2);

        $this->assertTrue($result);
        $this->assertEquals(9, $book->available_stock);
    }

    /**
     * Test cannot release more than total stock
     */
    public function test_cannot_release_more_than_total(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 9]);

        $result = $this->stockManager->releaseStock($book, 5);

        $this->assertFalse($result);
        $this->assertEquals(9, $book->fresh()->available_stock);
    }

    /**
     * Test is in stock returns true when sufficient
     */
    public function test_is_in_stock_returns_true_when_sufficient(): void
    {
        $book = Book::factory()->create(['available_stock' => 5]);

        $result = $this->stockManager->isInStock($book, 3);

        $this->assertTrue($result);
    }

    /**
     * Test is in stock returns false when insufficient
     */
    public function test_is_in_stock_returns_false_when_insufficient(): void
    {
        $book = Book::factory()->create(['available_stock' => 2]);

        $result = $this->stockManager->isInStock($book, 5);

        $this->assertFalse($result);
    }

    /**
     * Test is low stock returns true
     */
    public function test_is_low_stock_returns_true(): void
    {
        $book = Book::factory()->create(['available_stock' => 2]);

        $result = $this->stockManager->isLowStock($book);

        $this->assertTrue($result);
    }

    /**
     * Test is low stock returns false for adequate stock
     */
    public function test_is_low_stock_returns_false_for_adequate(): void
    {
        $book = Book::factory()->create(['available_stock' => 10]);

        $result = $this->stockManager->isLowStock($book);

        $this->assertFalse($result);
    }

    /**
     * Test is low stock returns false for zero stock
     */
    public function test_is_low_stock_returns_false_for_zero(): void
    {
        $book = Book::factory()->create(['available_stock' => 0]);

        $result = $this->stockManager->isLowStock($book);

        $this->assertFalse($result);
    }

    /**
     * Test is out of stock returns true
     */
    public function test_is_out_of_stock_returns_true(): void
    {
        $book = Book::factory()->create(['available_stock' => 0]);

        $result = $this->stockManager->isOutOfStock($book);

        $this->assertTrue($result);
    }

    /**
     * Test is out of stock returns false
     */
    public function test_is_out_of_stock_returns_false(): void
    {
        $book = Book::factory()->create(['available_stock' => 5]);

        $result = $this->stockManager->isOutOfStock($book);

        $this->assertFalse($result);
    }

    /**
     * Test get stock status out of stock
     */
    public function test_get_stock_status_out_of_stock(): void
    {
        $book = Book::factory()->create(['available_stock' => 0]);

        $status = $this->stockManager->getStockStatus($book);

        $this->assertEquals('out-of-stock', $status);
    }

    /**
     * Test get stock status low stock
     */
    public function test_get_stock_status_low_stock(): void
    {
        $book = Book::factory()->create(['available_stock' => 2]);

        $status = $this->stockManager->getStockStatus($book);

        $this->assertEquals('low-stock', $status);
    }

    /**
     * Test get stock status in stock
     */
    public function test_get_stock_status_in_stock(): void
    {
        $book = Book::factory()->create(['available_stock' => 10]);

        $status = $this->stockManager->getStockStatus($book);

        $this->assertEquals('in-stock', $status);
    }

    /**
     * Test get stock percentage
     */
    public function test_get_stock_percentage(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 5]);

        $percentage = $this->stockManager->getStockPercentage($book);

        $this->assertEquals(50, $percentage);
    }

    /**
     * Test get stock percentage returns zero for zero total
     */
    public function test_get_stock_percentage_zero_total(): void
    {
        $book = Book::factory()->create(['total_stock' => 0, 'available_stock' => 0]);

        $percentage = $this->stockManager->getStockPercentage($book);

        $this->assertEquals(0, $percentage);
    }

    /**
     * Test update total stock increases both
     */
    public function test_update_total_stock_increases_both(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 10]);

        $result = $this->stockManager->updateTotalStock($book, 15);

        $this->assertTrue($result);
        $this->assertEquals(15, $book->total_stock);
        $this->assertEquals(15, $book->available_stock);
    }

    /**
     * Test update total stock decreases properly
     */
    public function test_update_total_stock_decreases_properly(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 10]);

        $result = $this->stockManager->updateTotalStock($book, 5);

        $this->assertTrue($result);
        $this->assertEquals(5, $book->total_stock);
        $this->assertEquals(5, $book->available_stock);
    }

    /**
     * Test update total stock with negative returns false
     */
    public function test_update_total_stock_negative_returns_false(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 10]);

        $result = $this->stockManager->updateTotalStock($book, -5);

        $this->assertFalse($result);
        $this->assertEquals(10, $book->fresh()->total_stock);
    }

    /**
     * Test update total stock doesn't make available negative
     */
    public function test_update_total_stock_prevents_negative_available(): void
    {
        $book = Book::factory()->create(['total_stock' => 10, 'available_stock' => 3]);

        // Decrease total by 8, available should become 0 (not -5)
        $result = $this->stockManager->updateTotalStock($book, 2);

        $this->assertTrue($result);
        $this->assertEquals(2, $book->total_stock);
        $this->assertEquals(0, $book->available_stock);
    }
}
