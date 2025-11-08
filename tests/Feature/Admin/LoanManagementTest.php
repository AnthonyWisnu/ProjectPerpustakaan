
<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    }

    /**
     * Test admin can view loans list
     */
    public function test_admin_can_view_loans_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/loans');

        $response->assertStatus(200);
        $response->assertViewIs('admin.loans.index');
    }

    /**
     * Test admin can view loan details
     */
    public function test_admin_can_view_loan_details(): void
    {
        $loan = Loan::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/loans/{$loan->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.loans.show');
        $response->assertViewHas('loan');
    }

    /**
     * Test admin can create manual loan
     */
    public function test_admin_can_create_manual_loan(): void
    {
        $books = Book::factory()->count(2)->create(['available_stock' => 5]);

        $response = $this->actingAs($this->admin)->post('/admin/loans', [
            'user_id' => $this->member->id,
            'book_ids' => $books->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('loans', [
            'user_id' => $this->member->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test admin can mark loan as returned
     */
    public function test_admin_can_mark_loan_as_returned(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/return");

        $response->assertRedirect();
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'returned',
        ]);
        $loan->refresh();
        $this->assertNotNull($loan->returned_at);
    }

    /**
     * Test books return to stock when loan returned
     */
    public function test_books_return_to_stock_when_loan_returned(): void
    {
        $book = Book::factory()->create(['total_stock' => 5, 'available_stock' => 4]);

        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
        ]);

        $loan->books()->attach($book->id);

        $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/return");

        $book->refresh();
        $this->assertEquals(5, $book->available_stock);
    }

    /**
     * Test admin can filter loans by status
     */
    public function test_admin_can_filter_loans_by_status(): void
    {
        Loan::factory()->create(['status' => 'active']);
        Loan::factory()->create(['status' => 'returned']);

        $response = $this->actingAs($this->admin)->get('/admin/loans?status=active');

        $response->assertStatus(200);
        $response->assertViewHas('loans', function ($loans) {
            return $loans->every(fn($l) => $l->status === 'active');
        });
    }

    /**
     * Test admin can filter loans by member
     */
    public function test_admin_can_filter_loans_by_member(): void
    {
        $member1 = User::factory()->create(['role' => 'member']);
        $member2 = User::factory()->create(['role' => 'member']);

        Loan::factory()->create(['user_id' => $member1->id]);
        Loan::factory()->create(['user_id' => $member2->id]);

        $response = $this->actingAs($this->admin)->get("/admin/loans?user={$member1->id}");

        $response->assertStatus(200);
        $response->assertViewHas('loans', function ($loans) use ($member1) {
            return $loans->every(fn($l) => $l->user_id === $member1->id);
        });
    }

    /**
     * Test admin can view overdue loans
     */
    public function test_admin_can_view_overdue_loans(): void
    {
        Loan::factory()->create([
            'status' => 'active',
            'due_date' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/loans?status=overdue');

        $response->assertStatus(200);
    }

    /**
     * Test admin can extend loan
     */
    public function test_admin_can_extend_loan(): void
    {
        $loan = Loan::factory()->create([
            'status' => 'active',
            'extensions_count' => 0,
        ]);

        $originalDueDate = $loan->due_date;

        $response = $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/extend");

        $response->assertRedirect();
        $loan->refresh();
        $this->assertEquals(1, $loan->extensions_count);
        $this->assertTrue($loan->due_date->greaterThan($originalDueDate));
    }

    /**
     * Test admin can mark loan as lost
     */
    public function test_admin_can_mark_loan_as_lost(): void
    {
        $loan = Loan::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/mark-lost");

        $response->assertRedirect();
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'lost',
        ]);
    }

    /**
     * Test admin can process fine payment
     */
    public function test_admin_can_process_fine_payment(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'due_date' => now()->subDays(10),
        ]);

        $fine = $loan->fines()->create([
            'amount' => 10000,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/fines/{$fine->id}/pay", [
            'amount' => 10000,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fines', [
            'id' => $fine->id,
            'status' => 'paid',
        ]);
    }

    /**
     * Test admin can view loan statistics
     */
    public function test_admin_can_view_loan_statistics(): void
    {
        Loan::factory()->count(5)->create(['status' => 'active']);
        Loan::factory()->count(3)->create(['status' => 'returned']);
        Loan::factory()->count(2)->create([
            'status' => 'active',
            'due_date' => now()->subDays(3),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/loans');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    /**
     * Test admin can search loans by member name
     */
    public function test_admin_can_search_loans_by_member_name(): void
    {
        $member1 = User::factory()->create(['name' => 'John Doe', 'role' => 'member']);
        $member2 = User::factory()->create(['name' => 'Jane Smith', 'role' => 'member']);

        Loan::factory()->create(['user_id' => $member1->id]);
        Loan::factory()->create(['user_id' => $member2->id]);

        $response = $this->actingAs($this->admin)->get('/admin/loans?search=John');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    /**
     * Test member cannot access loan management
     */
    public function test_member_cannot_access_loan_management(): void
    {
        $response = $this->actingAs($this->member)->get('/admin/loans');

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot access loan management
     */
    public function test_guest_cannot_access_loan_management(): void
    {
        $response = $this->get('/admin/loans');

        $response->assertRedirect('/login');
    }

    /**
     * Test admin can export loans
     */
    public function test_admin_can_export_loans(): void
    {
        Loan::factory()->count(10)->create();

        $response = $this->actingAs($this->admin)->get('/admin/loans/export');

        $response->assertStatus(200);
        $response->assertDownload();
    }

    /**
     * Test admin can send reminder for due loans
     */
    public function test_admin_can_send_reminder_for_due_loans(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'active',
            'due_date' => now()->addDays(1),
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/send-reminder");

        $response->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->member->id,
        ]);
    }

    /**
     * Test returned loan cannot be returned again
     */
    public function test_returned_loan_cannot_be_returned_again(): void
    {
        $loan = Loan::factory()->create([
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/loans/{$loan->id}/return");

        $response->assertStatus(422);
    }

    /**
     * Test admin cannot create loan for suspended member
     */
    public function test_admin_cannot_create_loan_for_suspended_member(): void
    {
        $suspendedMember = User::factory()->create(['role' => 'member', 'status' => 'suspended']);
        $book = Book::factory()->create(['available_stock' => 5]);

        $response = $this->actingAs($this->admin)->post('/admin/loans', [
            'user_id' => $suspendedMember->id,
            'book_ids' => [$book->id],
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test admin can view member loan history
     */
    public function test_admin_can_view_member_loan_history(): void
    {
        Loan::factory()->count(3)->create([
            'user_id' => $this->member->id,
            'status' => 'returned',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/users/{$this->member->id}/loans");

        $response->assertStatus(200);
        $response->assertViewHas('loans', function ($loans) {
            return $loans->count() === 3;
        });
    }
}
