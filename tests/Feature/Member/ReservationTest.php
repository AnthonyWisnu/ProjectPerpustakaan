
<?php

namespace Tests\Feature\Member;

use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    }

    /**
     * Test member can view reservations list
     */
    public function test_member_can_view_reservations_list(): void
    {
        $response = $this->actingAs($this->member)->get('/member/reservations');

        $response->assertStatus(200);
        $response->assertViewIs('member.reservations.index');
    }

    /**
     * Test member can create reservation from cart
     */
    public function test_member_can_create_reservation_from_cart(): void
    {
        $books = Book::factory()->count(2)->create(['available_stock' => 5]);

        // Add books to cart
        foreach ($books as $book) {
            $this->actingAs($this->member)->post('/member/cart/add', [
                'book_id' => $book->id,
            ]);
        }

        $response = $this->actingAs($this->member)->post('/member/reservations/from-cart');

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->member->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test member cannot create reservation with empty cart
     */
    public function test_member_cannot_create_reservation_with_empty_cart(): void
    {
        $response = $this->actingAs($this->member)->post('/member/reservations/from-cart');

        $response->assertStatus(422);
    }

    /**
     * Test member cannot have more than 3 active reservations
     */
    public function test_member_cannot_have_more_than_three_active_reservations(): void
    {
        // Create 3 active reservations
        Reservation::factory()->count(3)->create([
            'user_id' => $this->member->id,
            'status' => 'pending',
        ]);

        $book = Book::factory()->create(['available_stock' => 5]);

        $this->actingAs($this->member)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($this->member)->post('/member/reservations/from-cart');

        $response->assertStatus(422);
    }

    /**
     * Test member can view reservation details
     */
    public function test_member_can_view_reservation_details(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->member->id,
        ]);

        $response = $this->actingAs($this->member)->get("/member/reservations/{$reservation->id}");

        $response->assertStatus(200);
        $response->assertViewIs('member.reservations.show');
        $response->assertViewHas('reservation');
    }

    /**
     * Test member cannot view other member's reservation
     */
    public function test_member_cannot_view_other_members_reservation(): void
    {
        $otherMember = User::factory()->create(['role' => 'member']);
        $reservation = Reservation::factory()->create([
            'user_id' => $otherMember->id,
        ]);

        $response = $this->actingAs($this->member)->get("/member/reservations/{$reservation->id}");

        $response->assertStatus(403);
    }

    /**
     * Test member can cancel pending reservation
     */
    public function test_member_can_cancel_pending_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->member)->post("/member/reservations/{$reservation->id}/cancel");

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Test member cannot cancel ready or completed reservation
     */
    public function test_member_cannot_cancel_ready_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->member)->post("/member/reservations/{$reservation->id}/cancel");

        $response->assertStatus(422);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'ready',
        ]);
    }

    /**
     * Test reservation has QR code
     */
    public function test_reservation_has_qr_code(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->member->id,
        ]);

        $this->assertNotNull($reservation->qr_code);
        $this->assertStringStartsWith('RSV', $reservation->qr_code);
    }

    /**
     * Test reservation expires after configured hours
     */
    public function test_reservation_has_expiry_date(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'ready',
        ]);

        $this->assertNotNull($reservation->expires_at);
        $this->assertTrue($reservation->expires_at->isFuture());
    }

    /**
     * Test member can filter reservations by status
     */
    public function test_member_can_filter_reservations_by_status(): void
    {
        Reservation::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'pending',
        ]);

        Reservation::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->member)->get('/member/reservations?status=pending');

        $response->assertStatus(200);
        $response->assertViewHas('reservations', function ($reservations) {
            return $reservations->every(fn($r) => $r->status === 'pending');
        });
    }

    /**
     * Test guest cannot access reservations
     */
    public function test_guest_cannot_access_reservations(): void
    {
        $response = $this->get('/member/reservations');

        $response->assertRedirect('/login');
    }

    /**
     * Test suspended member cannot create reservation
     */
    public function test_suspended_member_cannot_create_reservation(): void
    {
        $suspendedMember = User::factory()->create(['role' => 'member', 'status' => 'suspended']);
        $book = Book::factory()->create(['available_stock' => 5]);

        $this->actingAs($suspendedMember)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($suspendedMember)->post('/member/reservations/from-cart');

        $response->assertStatus(403);
    }

    /**
     * Test member with unpaid fines cannot create reservation
     */
    public function test_member_with_unpaid_fines_cannot_create_reservation(): void
    {
        // Create user with unpaid fines
        $memberWithFines = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $memberWithFines->fines()->create([
            'amount' => 5000,
            'status' => 'unpaid',
        ]);

        $book = Book::factory()->create(['available_stock' => 5]);

        $this->actingAs($memberWithFines)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($memberWithFines)->post('/member/reservations/from-cart');

        $response->assertStatus(422);
    }

    /**
     * Test reservation books are reserved from stock
     */
    public function test_reservation_books_are_reserved_from_stock(): void
    {
        $book = Book::factory()->create(['total_stock' => 5, 'available_stock' => 5]);

        $this->actingAs($this->member)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        $this->actingAs($this->member)->post('/member/reservations/from-cart');

        $book->refresh();
        $this->assertEquals(4, $book->available_stock);
    }
}
