<?php

namespace Tests\Feature;

use App\Models\Firm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_registration_screen_can_be_rendered(): void
    {
        Firm::create([
            'name' => 'Sharma & Associates',
            'slug' => 'sharma-associates',
            'email' => 'info@sharmalegal.in',
        ]);

        $response = $this->get('/register/client');

        $response->assertStatus(200);
        $response->assertSee('Client Portal Registration');
    }

    public function test_new_client_can_register_successfully(): void
    {
        $firm = Firm::create([
            'name' => 'Sharma & Associates',
            'slug' => 'sharma-associates-2',
            'email' => 'info2@sharmalegal.in',
        ]);

        $response = $this->post('/register/client', [
            'category' => 'individual',
            'firm_id' => $firm->id,
            'name' => 'Vikram Malhotra',
            'contact_person' => 'Vikram Malhotra',
            'email' => 'vikram.test@gmail.com',
            'phone' => '+91 98765 43210',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertRedirect('/portal/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'vikram.test@gmail.com',
            'role' => 'client',
            'firm_id' => $firm->id,
        ]);

        $this->assertDatabaseHas('clients', [
            'email' => 'vikram.test@gmail.com',
            'name' => 'Vikram Malhotra',
            'firm_id' => $firm->id,
            'portal_status' => 'active',
        ]);
    }
}
