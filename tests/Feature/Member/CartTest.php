
<?php

namespace Tests\Feature\Member;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->create(['role' => 'member', 'status' => 'active']);
    }

    /**
     * Test member can view cart
     */
    public function test_member_can_view_cart(): void
    {
        $response = $this->actingAs($this->member)->get('/member/cart');

        $response->assertStatus(200);
        $response->assertViewIs('member.cart.index');
    }

    /**
     * Test member can add book to cart
     */
    public function test_member_can_add_book_to_cart(): void
    {
        $book = Book::factory()->create(['available_stock' => 5]);

        $response = $this->actingAs($this->member)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->member->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * Test member cannot add more than 3 books to cart
     */
    public function test_member_cannot_add_more_than_three_books_to_cart(): void
    {
        $books = Book::factory()->count(4)->create(['available_stock' => 5]);

        // Add 3 books successfully
        foreach ($books->take(3) as $book) {
            $this->actingAs($this->member)->post('/member/cart/add', [
                'book_id' => $book->id,
            ]);
        }

        // Try to add 4th book - should fail
        $response = $this->actingAs($this->member)->post('/member/cart/add', [
            'book_id' => $books->last()->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->member->id,
            'book_id' => $books->last()->id,
        ]);
    }

    /**
     * Test member cannot add book with no stock
     */
    public function test_member_cannot_add_book_with_no_stock(): void
    {
        $book = Book::factory()->create(['available_stock' => 0]);

        $response = $this->actingAs($this->member)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->member->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * Test member cannot add duplicate book to cart
     */
    public function test_member_cannot_add_duplicate_book_to_cart(): void
    {
        $book = Book::factory()->create(['available_stock' => 5]);

        // Add book first time
        $this->actingAs($this->member)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        // Try to add same book again
        $response = $this->actingAs($this->member)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test member can remove book from cart
     */
    public function test_member_can_remove_book_from_cart(): void
    {
        $book = Book::factory()->create(['available_stock' => 5]);

        // Add book to cart
        $this->actingAs($this->member)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        // Remove book from cart
        $response = $this->actingAs($this->member)->delete("/member/cart/{$book->id}");

        $response->assertSuccessful();
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->member->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * Test member can clear entire cart
     */
    public function test_member_can_clear_cart(): void
    {
        $books = Book::factory()->count(3)->create(['available_stock' => 5]);

        // Add books to cart
        foreach ($books as $book) {
            $this->actingAs($this->member)->post('/member/cart/add', [
                'book_id' => $book->id,
            ]);
        }

        // Clear cart
        $response = $this->actingAs($this->member)->delete('/member/cart');

        $response->assertSuccessful();
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->member->id,
        ]);
    }

    /**
     * Test cart shows correct book count
     */
    public function test_cart_shows_correct_book_count(): void
    {
        $books = Book::factory()->count(2)->create(['available_stock' => 5]);

        foreach ($books as $book) {
            $this->actingAs($this->member)->post('/member/cart/add', [
                'book_id' => $book->id,
            ]);
        }

        $response = $this->actingAs($this->member)->get('/member/cart');

        $response->assertViewHas('items', function ($items) {
            return count($items) === 2;
        });
    }

    /**
     * Test guest cannot access cart
     */
    public function test_guest_cannot_access_cart(): void
    {
        $response = $this->get('/member/cart');

        $response->assertRedirect('/login');
    }

    /**
     * Test admin cannot access member cart
     */
    public function test_admin_cannot_access_member_cart(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/member/cart');

        $response->assertStatus(403);
    }

    /**
     * Test suspended member cannot add to cart
     */
    public function test_suspended_member_cannot_add_to_cart(): void
    {
        $suspendedMember = User::factory()->create(['role' => 'member', 'status' => 'suspended']);
        $book = Book::factory()->create(['available_stock' => 5]);

        $response = $this->actingAs($suspendedMember)->post('/member/cart/add', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test cart is cleared after creating reservation
     */
    public function test_cart_is_cleared_after_creating_reservation(): void
    {
        $books = Book::factory()->count(2)->create(['available_stock' => 5]);

        foreach ($books as $book) {
            $this->actingAs($this->member)->post('/member/cart/add', [
                'book_id' => $book->id,
            ]);
        }

        // Create reservation from cart
        $this->actingAs($this->member)->post('/member/reservations/from-cart');

        // Cart should be empty
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->member->id,
        ]);
    }
}
