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

    public function test_joint_co_clients_can_register_with_dynamic_members(): void
    {
        $firm = Firm::create([
            'name' => 'Sharma & Associates',
            'slug' => 'sharma-associates-3',
            'email' => 'info3@sharmalegal.in',
        ]);

        $response = $this->post('/register/client', [
            'category' => 'joint',
            'firm_id' => $firm->id,
            'name' => 'Vikram & Rajesh (Joint Litigants)',
            'contact_person' => 'Vikram Malhotra',
            'email' => 'joint.test@gmail.com',
            'phone' => '+91 98110 99887',
            'password' => '123456',
            'password_confirmation' => '123456',
            'members' => [
                [
                    'name' => 'Rajesh Sharma',
                    'relationship' => 'Co-petitioner',
                    'phone' => '+91 98765 11223',
                    'email' => 'rajesh.sharma@gmail.com',
                ],
                [
                    'name' => 'Sanjay Malhotra',
                    'relationship' => 'Co-litigant',
                    'phone' => '+91 98100 44556',
                    'email' => 'sanjay.m@gmail.com',
                ],
            ],
        ]);

        $response->assertRedirect('/portal/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('client_members', [
            'name' => 'Rajesh Sharma',
            'relationship' => 'Co-petitioner',
        ]);

        $this->assertDatabaseHas('client_members', [
            'name' => 'Sanjay Malhotra',
            'relationship' => 'Co-litigant',
        ]);
    }

    public function test_client_registration_supports_demographics_and_digital_literacy(): void
    {
        $firm = Firm::create([
            'name' => 'Sharma & Associates',
            'slug' => 'sharma-associates-4',
            'email' => 'info4@sharmalegal.in',
        ]);

        $response = $this->post('/register/client', [
            'category' => 'individual',
            'onboarding_mode' => 'assisted_offline',
            'firm_id' => $firm->id,
            'name' => 'John Smith',
            'contact_person' => 'John Smith',
            'email' => 'john.us@example.com',
            'phone' => '+1 2025550143',
            'age' => 36,
            'gender' => 'male',
            'occupation' => 'Architect',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertRedirect('/portal/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('clients', [
            'email' => 'john.us@example.com',
            'name' => 'John Smith',
            'phone' => '+1 2025550143',
            'age' => 36,
            'gender' => 'male',
            'occupation' => 'Architect',
            'onboarding_mode' => 'assisted_offline',
        ]);
    }
}
