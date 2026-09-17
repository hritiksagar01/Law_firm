<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test demo-login endpoint has been completely eradicated.
     */
    public function test_demo_login_endpoint_does_not_exist(): void
    {
        $response = $this->post('/demo-login', ['email' => 'admin@sharmalegal.in']);
        $response->assertStatus(404);
    }

    /**
     * Test unauthenticated access to super admin dashboard is blocked.
     */
    public function test_unauthenticated_user_cannot_access_super_admin(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test non-superadmin advocate cannot access super admin console.
     */
    public function test_regular_lawyer_cannot_access_super_admin(): void
    {
        $lawyer = User::where('email', 'rajesh@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($lawyer)->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test login requires real password and rejects invalid password for superadmin.
     */
    public function test_super_admin_login_rejects_wrong_password(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'admin@sharmalegal.in',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test super admin logs in with valid password and redirects to admin dashboard.
     */
    public function test_super_admin_authenticates_with_valid_password(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'admin@sharmalegal.in',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isSuperAdmin());
    }
}
