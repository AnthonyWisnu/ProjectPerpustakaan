
<?php

namespace Tests\Feature\Member;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    }

    /**
     * Test member can view loans list
     */
    public function test_member_can_view_loans_list(): void
    {
        $response = $this->actingAs($this->member)->get('/member/loans');

        $response->assertStatus(200);
        $response->assertViewIs('member.loans.index');
    }

    /**
     * Test member can view loan details
     */
    public function test_member_can_view_loan_details(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
        ]);

        $response = $this->actingAs($this->member)->get("/member/loans/{$loan->id}");

        $response->assertStatus(200);
        $response->assertViewIs('member.loans.show');
        $response->assertViewHas('loan');
    }

    /**
     * Test member cannot view other member's loan
     */
    public function test_member_cannot_view_other_members_loan(): void
    {
        $otherMember = User::factory()->create(['role' => 'member']);
        $loan = Loan::factory()->create([
            'user_id' => $otherMember->id,
        ]);

        $response = $this->actingAs($this->member)->get("/member/loans/{$loan->id}");

        $response->assertStatus(403);
    }

    /**
     * Test member cannot have more than 5 active loans
     */
    public function test_member_cannot_have_more_than_five_active_loans(): void
    {
        // Create 5 active loans
        Loan::factory()->count(5)->create([
            'user_id' => $this->member->id,
            'status' => 'active',
        ]);

        $this->assertEquals(5, $this->member->loans()->active()->count());
    }

    /**
     * Test loan has correct due date
     */
    public function test_loan_has_correct_due_date(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
        ]);

        $expectedDueDate = $loan->borrowed_at->addDays(config('library.loan.duration_days', 7));

        $this->assertEquals(
            $expectedDueDate->format('Y-m-d'),
            $loan->due_date->format('Y-m-d')
        );
    }

    /**
     * Test member can extend loan if allowed
     */
    public function test_member_can_extend_loan(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'extensions_count' => 0,
        ]);

        $originalDueDate = $loan->due_date;

        $response = $this->actingAs($this->member)->post("/member/loans/{$loan->id}/extend");

        $response->assertRedirect();
        $loan->refresh();

        $this->assertEquals(1, $loan->extensions_count);
        $this->assertTrue($loan->due_date->greaterThan($originalDueDate));
    }

    /**
     * Test member cannot extend loan beyond maximum extensions
     */
    public function test_member_cannot_extend_loan_beyond_maximum(): void
    {
        $maxExtensions = config('library.loan.max_extensions', 1);

        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'extensions_count' => $maxExtensions,
        ]);

        $response = $this->actingAs($this->member)->post("/member/loans/{$loan->id}/extend");

        $response->assertStatus(422);
        $loan->refresh();
        $this->assertEquals($maxExtensions, $loan->extensions_count);
    }

    /**
     * Test member cannot extend overdue loan
     */
    public function test_member_cannot_extend_overdue_loan(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'due_date' => now()->subDays(5),
            'extensions_count' => 0,
        ]);

        $response = $this->actingAs($this->member)->post("/member/loans/{$loan->id}/extend");

        $response->assertStatus(422);
    }

    /**
     * Test member can view loan history
     */
    public function test_member_can_view_loan_history(): void
    {
        Loan::factory()->count(3)->create([
            'user_id' => $this->member->id,
            'status' => 'returned',
        ]);

        $response = $this->actingAs($this->member)->get('/member/loans/history');

        $response->assertStatus(200);
        $response->assertViewHas('loans', function ($loans) {
            return $loans->count() === 3;
        });
    }

    /**
     * Test active loans show remaining days
     */
    public function test_active_loan_shows_remaining_days(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'due_date' => now()->addDays(3),
        ]);

        $remainingDays = $loan->due_date->diffInDays(now());

        $this->assertEquals(3, $remainingDays);
    }

    /**
     * Test overdue loan shows overdue days
     */
    public function test_overdue_loan_shows_overdue_days(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'due_date' => now()->subDays(5),
        ]);

        $this->assertTrue($loan->isOverdue());
        $this->assertEquals(5, $loan->overdueDays());
    }

    /**
     * Test member can filter loans by status
     */
    public function test_member_can_filter_loans_by_status(): void
    {
        Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
        ]);

        Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'returned',
        ]);

        $response = $this->actingAs($this->member)->get('/member/loans?status=active');

        $response->assertStatus(200);
        $response->assertViewHas('loans', function ($loans) {
            return $loans->every(fn($l) => $l->status === 'active');
        });
    }

    /**
     * Test guest cannot access loans
     */
    public function test_guest_cannot_access_loans(): void
    {
        $response = $this->get('/member/loans');

        $response->assertRedirect('/login');
    }

    /**
     * Test member with overdue loans receives notification
     */
    public function test_member_with_overdue_loan_can_see_notification(): void
    {
        Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'due_date' => now()->subDays(3),
        ]);

        $response = $this->actingAs($this->member)->get('/member/dashboard');

        $response->assertStatus(200);
        $response->assertSee('overdue', false);
    }

    /**
     * Test loan generates fine when overdue
     */
    public function test_overdue_loan_generates_fine(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'due_date' => now()->subDays(5),
        ]);

        $fineRate = config('library.fine.rate_per_day', 1000);
        $expectedFine = 5 * $fineRate;

        $this->assertEquals($expectedFine, $loan->calculateFine());
    }

    /**
     * Test fine does not exceed maximum amount
     */
    public function test_fine_does_not_exceed_maximum(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'due_date' => now()->subDays(100),
        ]);

        $maxFine = config('library.fine.max_amount', 50000);
        $calculatedFine = $loan->calculateFine();

        $this->assertLessThanOrEqual($maxFine, $calculatedFine);
    }

    /**
     * Test member can view books in loan
     */
    public function test_loan_contains_correct_books(): void
    {
        $books = Book::factory()->count(2)->create();

        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
        ]);

        $loan->books()->attach($books->pluck('id'));

        $response = $this->actingAs($this->member)->get("/member/loans/{$loan->id}");

        $response->assertStatus(200);
        $response->assertViewHas('loan', function ($viewLoan) use ($books) {
            return $viewLoan->books->count() === 2;
        });
    }

    /**
     * Test returned loan shows return date
     */
    public function test_returned_loan_shows_return_date(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        $this->assertNotNull($loan->returned_at);
        $this->assertEquals('returned', $loan->status);
    }
}
