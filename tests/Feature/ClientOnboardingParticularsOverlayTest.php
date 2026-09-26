<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class ClientOnboardingParticularsOverlayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test client onboarding overlay does NOT contain father/mother name,
     * contains Preferred Communication Method, Date of Birth instead of Age,
     * and includes Client Status, Client Type, and Referral Source.
     */
    public function test_client_onboarding_overlay_renders_requested_fields(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($lawyer)->get(route('clients.index'));

        $response->assertStatus(200);

        // Assert father / mother name is removed from the onboarding modal
        $response->assertDontSee("Title &amp; Father's / Mother's Name", false);
        $response->assertDontSee("Father's / Mother's Name", false);
        $response->assertDontSee('name="father_husband_name"', false);

        // Assert Preferred Communication Method is present
        $response->assertSee('Preferred Communication Method');
        $response->assertSee('name="preferred_communication_method"', false);
        $response->assertSee('Email Dispatch');
        $response->assertSee('WhatsApp Message');
        $response->assertSee('Phone Call');
        $response->assertSee('Online Client Portal');

        // Assert Date of Birth is present instead of Age (Years)
        $response->assertSee('Date of Birth');
        $response->assertSee('name="date_of_birth"', false);
        $response->assertDontSee('name="age"', false);

        // Assert Client Status, Client Type, and Referral Source are present
        $response->assertSee('Client Status');
        $response->assertSee('Client Type');
        $response->assertSee('Referral Source');
        $response->assertSee('name="client_type"', false);
        $response->assertSee('name="referral_source"', false);
    }

    /**
     * Test onboarding a client with preferred communication method, date of birth,
     * client status, client type, and referral source.
     */
    public function test_can_onboard_client_with_preferred_comm_and_dob_and_status(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();
        $dob = '1990-05-15';
        $expectedAge = Carbon::parse($dob)->age;

        $postData = [
            'category' => 'individual',
            'onboarding_mode' => 'portal_online',
            'name' => 'Kavita Sundaram',
            'email' => 'kavita.sundaram@example.in',
            'phone' => '+91 98765 43219',
            'preferred_communication_method' => 'whatsapp',
            'date_of_birth' => $dob,
            'gender' => 'female',
            'occupation' => 'Architect & Town Planner',
            'status' => 'prospective',
            'client_type' => 'petitioner',
            'referral_source' => 'website',
            'primary_attorney_id' => $lawyer->id,
        ];

        $response = $this->actingAs($lawyer)->post(route('clients.store'), $postData);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'email' => 'kavita.sundaram@example.in',
            'preferred_communication_method' => 'whatsapp',
            'age' => $expectedAge,
            'status' => 'prospective',
            'client_type' => 'petitioner',
            'referral_source' => 'website',
        ]);

        $client = Client::where('email', 'kavita.sundaram@example.in')->firstOrFail();
        $this->assertEquals('1990-05-15', $client->date_of_birth?->format('Y-m-d'));
        $this->assertEquals('WhatsApp', $client->preferred_communication_method_label);
        $this->assertEquals($expectedAge, $client->age);
    }

    /**
     * Test updating client preferred communication method and date of birth.
     */
    public function test_can_update_client_preferred_comm_and_date_of_birth(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();
        $client = Client::where('firm_id', $lawyer->firm_id)->firstOrFail();

        $updateData = [
            'name' => $client->name,
            'category' => $client->category ?? 'individual',
            'onboarding_mode' => $client->onboarding_mode ?? 'portal_online',
            'status' => 'active',
            'client_type' => 'plaintiff',
            'referral_source' => 'bar_association',
            'preferred_communication_method' => 'portal',
            'date_of_birth' => '1985-08-20',
        ];

        $response = $this->actingAs($lawyer)->put(route('clients.update', $client->id), $updateData);

        $response->assertSessionHasNoErrors();
        $client->refresh();

        $this->assertEquals('portal', $client->preferred_communication_method);
        $this->assertEquals('Client Portal', $client->preferred_communication_method_label);
        $this->assertEquals('1985-08-20', $client->date_of_birth?->format('Y-m-d'));
        $this->assertEquals(Carbon::parse('1985-08-20')->age, $client->age);
        $this->assertEquals('plaintiff', $client->client_type);
        $this->assertEquals('bar_association', $client->referral_source);
    }
}
