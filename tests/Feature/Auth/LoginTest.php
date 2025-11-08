
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login page is accessible
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test member can login with valid credentials
     */
    public function test_member_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'member@test.com',
            'password' => bcrypt('password123'),
            'role' => 'member',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'member@test.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/member/dashboard');
    }

    /**
     * Test admin can login with valid credentials
     */
    public function test_admin_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    /**
     * Test super admin can login with valid credentials
     */
    public function test_super_admin_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'superadmin@test.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    /**
     * Test user cannot login with invalid password
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'user@test.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test user cannot login with non-existent email
     */
    public function test_user_cannot_login_with_non_existent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test suspended user cannot login
     */
    public function test_suspended_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'suspended@test.com',
            'password' => bcrypt('password123'),
            'status' => 'suspended',
        ]);

        $response = $this->post('/login', [
            'email' => 'suspended@test.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    /**
     * Test inactive user cannot login
     */
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@test.com',
            'password' => bcrypt('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    /**
     * Test email field is required
     */
    public function test_email_field_is_required(): void
    {
        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test password field is required
     */
    public function test_password_field_is_required(): void
    {
        $response = $this->post('/login', [
            'email' => 'user@test.com',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test email must be valid format
     */
    public function test_email_must_be_valid_format(): void
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test authenticated user is redirected from login page
     */
    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/member/dashboard');
    }

    /**
     * Test remember me functionality
     */
    public function test_user_can_login_with_remember_me(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'user@test.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertCookie('remember_web_' . sha1(get_class($user)));
    }

    /**
     * Test logout functionality
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
