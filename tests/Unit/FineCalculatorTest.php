
<?php

namespace Tests\Unit;

use App\Services\FineCalculator;
use Carbon\Carbon;
use Tests\TestCase;

class FineCalculatorTest extends TestCase
{
    protected FineCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new FineCalculator();
    }

    /**
     * Test no fine for on-time return
     */
    public function test_no_fine_for_on_time_return(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $returnDate = Carbon::parse('2024-01-10');

        $fine = $this->calculator->calculateFine($dueDate, $returnDate);

        $this->assertEquals(0, $fine);
    }

    /**
     * Test no fine for early return
     */
    public function test_no_fine_for_early_return(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $returnDate = Carbon::parse('2024-01-08');

        $fine = $this->calculator->calculateFine($dueDate, $returnDate);

        $this->assertEquals(0, $fine);
    }

    /**
     * Test fine calculation for overdue loan
     */
    public function test_fine_calculation_for_overdue_loan(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $returnDate = Carbon::parse('2024-01-15'); // 5 days overdue

        $fine = $this->calculator->calculateFine($dueDate, $returnDate);

        $this->assertEquals(5000, $fine); // 5 days * 1000/day
    }

    /**
     * Test fine with grace period
     */
    public function test_fine_with_grace_period(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $returnDate = Carbon::parse('2024-01-15'); // 5 days overdue
        $gracePeriod = 2;

        $fine = $this->calculator->calculateFine($dueDate, $returnDate, $gracePeriod);

        $this->assertEquals(3000, $fine); // (5 - 2) days * 1000/day
    }

    /**
     * Test no fine when overdue is within grace period
     */
    public function test_no_fine_within_grace_period(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $returnDate = Carbon::parse('2024-01-12'); // 2 days overdue
        $gracePeriod = 3;

        $fine = $this->calculator->calculateFine($dueDate, $returnDate, $gracePeriod);

        $this->assertEquals(0, $fine);
    }

    /**
     * Test fine capped at maximum amount
     */
    public function test_fine_capped_at_maximum(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $returnDate = Carbon::parse('2024-03-10'); // ~60 days overdue

        $fine = $this->calculator->calculateFine($dueDate, $returnDate);

        $maxAmount = config('library.fine.max_amount', 50000);
        $this->assertEquals($maxAmount, $fine);
    }

    /**
     * Test calculate fine by days
     */
    public function test_calculate_fine_by_days(): void
    {
        $overdueDays = 10;

        $fine = $this->calculator->calculateFineByDays($overdueDays);

        $this->assertEquals(10000, $fine); // 10 days * 1000/day
    }

    /**
     * Test calculate fine by days with grace period
     */
    public function test_calculate_fine_by_days_with_grace_period(): void
    {
        $overdueDays = 10;
        $gracePeriod = 3;

        $fine = $this->calculator->calculateFineByDays($overdueDays, $gracePeriod);

        $this->assertEquals(7000, $fine); // (10 - 3) days * 1000/day
    }

    /**
     * Test is overdue returns true for overdue loan
     */
    public function test_is_overdue_returns_true(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $currentDate = Carbon::parse('2024-01-15');

        $isOverdue = $this->calculator->isOverdue($dueDate, $currentDate);

        $this->assertTrue($isOverdue);
    }

    /**
     * Test is overdue returns false for on-time loan
     */
    public function test_is_overdue_returns_false(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $currentDate = Carbon::parse('2024-01-08');

        $isOverdue = $this->calculator->isOverdue($dueDate, $currentDate);

        $this->assertFalse($isOverdue);
    }

    /**
     * Test get overdue days
     */
    public function test_get_overdue_days(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $returnDate = Carbon::parse('2024-01-20');

        $days = $this->calculator->getOverdueDays($dueDate, $returnDate);

        $this->assertEquals(10, $days);
    }

    /**
     * Test get overdue days returns zero for on-time
     */
    public function test_get_overdue_days_returns_zero(): void
    {
        $dueDate = Carbon::parse('2024-01-10');
        $returnDate = Carbon::parse('2024-01-08');

        $days = $this->calculator->getOverdueDays($dueDate, $returnDate);

        $this->assertEquals(0, $days);
    }

    /**
     * Test get daily rate
     */
    public function test_get_daily_rate(): void
    {
        $rate = $this->calculator->getDailyRate();

        $this->assertEquals(1000, $rate);
    }

    /**
     * Test get maximum amount
     */
    public function test_get_maximum_amount(): void
    {
        $maxAmount = $this->calculator->getMaxAmount();

        $this->assertEquals(50000, $maxAmount);
    }

    /**
     * Test get grace period
     */
    public function test_get_grace_period(): void
    {
        $gracePeriod = $this->calculator->getGracePeriod();

        $this->assertEquals(0, $gracePeriod);
    }

    /**
     * Test fine calculation with zero overdue days
     */
    public function test_fine_calculation_with_zero_days(): void
    {
        $fine = $this->calculator->calculateFineByDays(0);

        $this->assertEquals(0, $fine);
    }

    /**
     * Test fine calculation with negative days returns zero
     */
    public function test_fine_calculation_with_negative_days(): void
    {
        $fine = $this->calculator->calculateFineByDays(-5);

        $this->assertEquals(0, $fine);
    }
}
