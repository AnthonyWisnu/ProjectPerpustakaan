
<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        Storage::fake('public');
    }

    /**
     * Test admin can view books list
     */
    public function test_admin_can_view_books_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/books');

        $response->assertStatus(200);
        $response->assertViewIs('admin.books.index');
    }

    /**
     * Test admin can view create book form
     */
    public function test_admin_can_view_create_book_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/books/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.books.create');
    }

    /**
     * Test admin can create new book
     */
    public function test_admin_can_create_new_book(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/books', [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '1234567890123',
            'publisher' => 'Test Publisher',
            'publication_year' => 2024,
            'category_id' => $category->id,
            'total_stock' => 10,
            'description' => 'Test description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '1234567890123',
        ]);
    }

    /**
     * Test admin can upload book cover
     */
    public function test_admin_can_upload_book_cover(): void
    {
        $category = Category::factory()->create();
        $cover = UploadedFile::fake()->image('cover.jpg');

        $response = $this->actingAs($this->admin)->post('/admin/books', [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '1234567890123',
            'publisher' => 'Test Publisher',
            'publication_year' => 2024,
            'category_id' => $category->id,
            'total_stock' => 10,
            'cover_image' => $cover,
        ]);

        $response->assertRedirect();
        Storage::disk('public')->assertExists('covers/' . $cover->hashName());
    }

    /**
     * Test admin can view book details
     */
    public function test_admin_can_view_book_details(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.books.show');
        $response->assertViewHas('book');
    }

    /**
     * Test admin can view edit book form
     */
    public function test_admin_can_view_edit_book_form(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/books/{$book->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.books.edit');
        $response->assertViewHas('book');
    }

    /**
     * Test admin can update book
     */
    public function test_admin_can_update_book(): void
    {
        $book = Book::factory()->create(['title' => 'Old Title']);

        $response = $this->actingAs($this->admin)->put("/admin/books/{$book->id}", [
            'title' => 'Updated Title',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'publisher' => $book->publisher,
            'publication_year' => $book->publication_year,
            'category_id' => $book->category_id,
            'total_stock' => $book->total_stock,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
        ]);
    }

    /**
     * Test admin can delete book
     */
    public function test_admin_can_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/books/{$book->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('books', [
            'id' => $book->id,
        ]);
    }

    /**
     * Test admin cannot delete book with active loans
     */
    public function test_admin_cannot_delete_book_with_active_loans(): void
    {
        $book = Book::factory()->create();
        $book->loans()->create([
            'user_id' => User::factory()->create()->id,
            'status' => 'active',
            'borrowed_at' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/books/{$book->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    /**
     * Test admin can search books
     */
    public function test_admin_can_search_books(): void
    {
        Book::factory()->create(['title' => 'Laravel Book']);
        Book::factory()->create(['title' => 'PHP Book']);

        $response = $this->actingAs($this->admin)->get('/admin/books?search=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel Book');
        $response->assertDontSee('PHP Book');
    }

    /**
     * Test admin can filter books by category
     */
    public function test_admin_can_filter_books_by_category(): void
    {
        $category1 = Category::factory()->create(['name' => 'Fiction']);
        $category2 = Category::factory()->create(['name' => 'Non-Fiction']);

        Book::factory()->create(['category_id' => $category1->id]);
        Book::factory()->create(['category_id' => $category2->id]);

        $response = $this->actingAs($this->admin)->get("/admin/books?category={$category1->id}");

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) use ($category1) {
            return $books->every(fn($b) => $b->category_id === $category1->id);
        });
    }

    /**
     * Test admin can update book stock
     */
    public function test_admin_can_update_book_stock(): void
    {
        $book = Book::factory()->create(['total_stock' => 5, 'available_stock' => 5]);

        $response = $this->actingAs($this->admin)->post("/admin/books/{$book->id}/stock", [
            'total_stock' => 10,
        ]);

        $response->assertRedirect();
        $book->refresh();
        $this->assertEquals(10, $book->total_stock);
    }

    /**
     * Test title field is required
     */
    public function test_title_field_is_required(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/books', [
            'author' => 'Test Author',
        ]);

        $response->assertSessionHasErrors('title');
    }

    /**
     * Test ISBN must be unique
     */
    public function test_isbn_must_be_unique(): void
    {
        Book::factory()->create(['isbn' => '1234567890123']);

        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->post('/admin/books', [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'isbn' => '1234567890123',
            'publisher' => 'Test Publisher',
            'publication_year' => 2024,
            'category_id' => $category->id,
            'total_stock' => 10,
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    /**
     * Test member cannot access book management
     */
    public function test_member_cannot_access_book_management(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($member)->get('/admin/books');

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot access book management
     */
    public function test_guest_cannot_access_book_management(): void
    {
        $response = $this->get('/admin/books');

        $response->assertRedirect('/login');
    }

    /**
     * Test admin can export books to Excel
     */
    public function test_admin_can_export_books(): void
    {
        Book::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get('/admin/books/export');

        $response->assertStatus(200);
        $response->assertDownload();
    }

    /**
     * Test admin can import books from Excel
     */
    public function test_admin_can_import_books(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('books.xlsx');

        $response = $this->actingAs($this->admin)->post('/admin/books/import', [
            'file' => $file,
        ]);

        $response->assertRedirect();
        Storage::disk('local')->assertExists('imports/' . $file->hashName());
    }
}
