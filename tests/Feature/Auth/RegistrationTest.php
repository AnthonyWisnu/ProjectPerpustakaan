
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration page is accessible
     */
    public function test_registration_page_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * Test new user can register successfully
     */
    public function test_new_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/member/dashboard');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'member',
            'status' => 'active',
        ]);
    }

    /**
     * Test member number is generated automatically
     */
    public function test_member_number_is_generated_on_registration(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotNull($user->member_number);
        $this->assertStringStartsWith('MBR', $user->member_number);
        $this->assertEquals(9, strlen($user->member_number)); // MBR + 6 digits
    }

    /**
     * Test name field is required
     */
    public function test_name_field_is_required(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    /**
     * Test email field is required
     */
    public function test_email_field_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test email must be valid format
     */
    public function test_email_must_be_valid_format(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test email must be unique
     */
    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test password field is required
     */
    public function test_password_field_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test password must be at least 8 characters
     */
    public function test_password_must_be_at_least_8_characters(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test password confirmation must match
     */
    public function test_password_confirmation_must_match(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test phone field validation
     */
    public function test_phone_must_be_valid_format(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => 'invalid-phone',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    /**
     * Test new user is assigned member role
     */
    public function test_new_user_is_assigned_member_role(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $this->assertEquals('member', $user->role);
    }

    /**
     * Test new user is active by default
     */
    public function test_new_user_is_active_by_default(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $this->assertEquals('active', $user->status);
    }

    /**
     * Test authenticated user cannot access registration page
     */
    public function test_authenticated_user_cannot_access_registration_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/member/dashboard');
    }

    /**
     * Test name has maximum length
     */
    public function test_name_has_maximum_length(): void
    {
        $response = $this->post('/register', [
            'name' => str_repeat('a', 256), // More than 255 characters
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    /**
     * Test address field is optional
     */
    public function test_address_field_is_optional(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/member/dashboard');
    }
}
