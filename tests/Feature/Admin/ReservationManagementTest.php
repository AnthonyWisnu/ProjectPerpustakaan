
<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationManagementTest extends TestCase
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
     * Test admin can view reservations list
     */
    public function test_admin_can_view_reservations_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reservations');

        $response->assertStatus(200);
        $response->assertViewIs('admin.reservations.index');
    }

    /**
     * Test admin can view reservation details
     */
    public function test_admin_can_view_reservation_details(): void
    {
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/reservations/{$reservation->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.reservations.show');
        $response->assertViewHas('reservation');
    }

    /**
     * Test admin can mark reservation as ready
     */
    public function test_admin_can_mark_reservation_as_ready(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/reservations/{$reservation->id}/ready");

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'ready',
        ]);
    }

    /**
     * Test ready reservation gets expiry date
     */
    public function test_ready_reservation_gets_expiry_date(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)->post("/admin/reservations/{$reservation->id}/ready");

        $reservation->refresh();
        $this->assertNotNull($reservation->expires_at);
        $this->assertTrue($reservation->expires_at->isFuture());
    }

    /**
     * Test admin can verify QR code
     */
    public function test_admin_can_verify_qr_code(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => 'ready',
            'qr_code' => 'RSV123456',
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/reservations/verify-qr', [
            'code' => 'RSV123456',
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'reservation_id' => $reservation->id,
        ]);
    }

    /**
     * Test admin cannot verify invalid QR code
     */
    public function test_admin_cannot_verify_invalid_qr_code(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/reservations/verify-qr', [
            'code' => 'INVALID123',
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test admin can convert reservation to loan
     */
    public function test_admin_can_convert_reservation_to_loan(): void
    {
        $books = Book::factory()->count(2)->create(['available_stock' => 5]);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->member->id,
            'status' => 'ready',
        ]);

        $reservation->books()->attach($books->pluck('id'));

        $response = $this->actingAs($this->admin)->post("/admin/reservations/{$reservation->id}/convert-to-loan");

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('loans', [
            'user_id' => $this->member->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test admin can cancel reservation
     */
    public function test_admin_can_cancel_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/reservations/{$reservation->id}/cancel");

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Test admin can filter reservations by status
     */
    public function test_admin_can_filter_reservations_by_status(): void
    {
        Reservation::factory()->create(['status' => 'pending']);
        Reservation::factory()->create(['status' => 'ready']);

        $response = $this->actingAs($this->admin)->get('/admin/reservations?status=pending');

        $response->assertStatus(200);
        $response->assertViewHas('reservations', function ($reservations) {
            return $reservations->every(fn($r) => $r->status === 'pending');
        });
    }

    /**
     * Test admin can filter reservations by member
     */
    public function test_admin_can_filter_reservations_by_member(): void
    {
        $member1 = User::factory()->create(['role' => 'member']);
        $member2 = User::factory()->create(['role' => 'member']);

        Reservation::factory()->create(['user_id' => $member1->id]);
        Reservation::factory()->create(['user_id' => $member2->id]);

        $response = $this->actingAs($this->admin)->get("/admin/reservations?user={$member1->id}");

        $response->assertStatus(200);
        $response->assertViewHas('reservations', function ($reservations) use ($member1) {
            return $reservations->every(fn($r) => $r->user_id === $member1->id);
        });
    }

    /**
     * Test admin can search reservations by QR code
     */
    public function test_admin_can_search_reservations_by_qr_code(): void
    {
        Reservation::factory()->create(['qr_code' => 'RSV123456']);
        Reservation::factory()->create(['qr_code' => 'RSV789012']);

        $response = $this->actingAs($this->admin)->get('/admin/reservations?search=RSV123');

        $response->assertStatus(200);
        $response->assertSee('RSV123456');
    }

    /**
     * Test admin can view expired reservations
     */
    public function test_admin_can_view_expired_reservations(): void
    {
        Reservation::factory()->create([
            'status' => 'ready',
            'expires_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/reservations?status=expired');

        $response->assertStatus(200);
    }

    /**
     * Test member cannot access reservation management
     */
    public function test_member_cannot_access_reservation_management(): void
    {
        $response = $this->actingAs($this->member)->get('/admin/reservations');

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot access reservation management
     */
    public function test_guest_cannot_access_reservation_management(): void
    {
        $response = $this->get('/admin/reservations');

        $response->assertRedirect('/login');
    }

    /**
     * Test admin can view reservation statistics
     */
    public function test_admin_can_view_reservation_statistics(): void
    {
        Reservation::factory()->count(5)->create(['status' => 'pending']);
        Reservation::factory()->count(3)->create(['status' => 'ready']);
        Reservation::factory()->count(2)->create(['status' => 'completed']);

        $response = $this->actingAs($this->admin)->get('/admin/reservations');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    /**
     * Test admin receives notification for new reservations
     */
    public function test_admin_receives_notification_for_new_reservations(): void
    {
        $reservation = Reservation::factory()->create([
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->admin->id,
            'notifiable_type' => User::class,
        ]);
    }

    /**
     * Test reservation books return to stock when cancelled
     */
    public function test_reservation_books_return_to_stock_when_cancelled(): void
    {
        $book = Book::factory()->create(['total_stock' => 5, 'available_stock' => 4]);

        $reservation = Reservation::factory()->create([
            'status' => 'pending',
        ]);

        $reservation->books()->attach($book->id);

        $this->actingAs($this->admin)->post("/admin/reservations/{$reservation->id}/cancel");

        $book->refresh();
        $this->assertEquals(5, $book->available_stock);
    }

    /**
     * Test admin can export reservations
     */
    public function test_admin_can_export_reservations(): void
    {
        Reservation::factory()->count(10)->create();

        $response = $this->actingAs($this->admin)->get('/admin/reservations/export');

        $response->assertStatus(200);
        $response->assertDownload();
    }
}
