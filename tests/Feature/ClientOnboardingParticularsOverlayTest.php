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

        // Assert '05 · Client Status, Litigation Role & Referral Details' is removed from modal
        $response->assertDontSee('05 · Client Status, Litigation Role &amp; Referral Details', false);
        $response->assertDontSee('05 · Client Status, Litigation Role & Referral Details', false);
        $response->assertSee('name="status"', false);
        $response->assertSee('name="primary_attorney_id"', false);
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

    /**
     * Test onboarding overlay renders corporate fields (official entity name, authorized contact person
     * with salutation, registration number, tax id, industry, website) and joint-only repeater.
     */
    public function test_client_onboarding_overlay_renders_corporate_and_joint_fields(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($lawyer)->get(route('clients.index'));

        $response->assertStatus(200);

        // Corporate entity & contact person fields
        $response->assertSee('Official Entity / Company Name *');
        $response->assertSee('Authorized Contact Person Name');
        $response->assertSee('name="contact_salutation"', false);
        $response->assertSee('name="contact_person"', false);

        // Registration number, tax id, industry, website
        $response->assertSee('name="registration_number"', false);
        $response->assertSee('name="tax_id"', false);
        $response->assertSee('name="industry"', false);
        $response->assertSee('name="website"', false);

        // Joint Co-Litigants repeater is visible strictly when joint is selected
        $response->assertSee('x-show="category === \'joint\'"', false);
        $response->assertDontSee("x-show=\"category === 'joint' || members.length > 0\"", false);
    }

    /**
     * Test onboarding a corporate/trust entity with clean official entity name (no Mr.),
     * authorized contact person formatted with salutation, registration number, tax id, industry, and website.
     */
    public function test_can_onboard_corporate_client_with_authorized_contact_and_registration_tax_id_industry_website(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();

        $postData = [
            'category' => 'corporate',
            'onboarding_mode' => 'portal_online',
            'name' => 'Malhotra Enterprises Pvt Ltd',
            'salutation' => 'Mr.', // Modal might have salutation bound, but it must NOT be prepended to company name
            'contact_salutation' => 'Mr.',
            'contact_person' => 'Vikram Malhotra',
            'email' => 'contact@malhotraenterprises.com',
            'phone' => '+91 98110 02233',
            'preferred_communication_method' => 'email',
            'registration_number' => 'U74999DL2020PTC123456',
            'tax_id' => '07AAAAA0000A1Z5',
            'industry' => 'Real Estate & Infrastructure',
            'website' => 'https://malhotraenterprises.com',
            'status' => 'active',
            'client_type' => 'petitioner',
            'referral_source' => 'referral',
            'primary_attorney_id' => $lawyer->id,
        ];

        $response = $this->actingAs($lawyer)->post(route('clients.store'), $postData);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'name' => 'Malhotra Enterprises Pvt Ltd', // Must NOT be 'Mr. Malhotra Enterprises Pvt Ltd'
            'contact_person' => 'Mr. Vikram Malhotra', // Authorized contact person has salutation
            'registration_number' => 'U74999DL2020PTC123456',
            'tax_id' => '07AAAAA0000A1Z5',
            'industry' => 'Real Estate & Infrastructure',
            'website' => 'https://malhotraenterprises.com',
            'category' => 'corporate',
        ]);
    }

    /**
     * Test updating a corporate client's registration number, tax id, industry, website, and contact person.
     */
    public function test_can_update_corporate_client_particulars(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();
        $client = Client::where('firm_id', $lawyer->firm_id)->firstOrFail();

        $updateData = [
            'name' => 'Apex Global Logistics Pvt Ltd',
            'category' => 'corporate',
            'onboarding_mode' => 'portal_online',
            'status' => 'active',
            'contact_salutation' => 'Dr.',
            'contact_person' => 'Arun Sen',
            'registration_number' => 'U63090DL2018PTC998877',
            'tax_id' => '07BBBBB1111B2Z6',
            'industry' => 'Supply Chain & Logistics',
            'website' => 'https://apexlogistic.example.com',
            'preferred_communication_method' => 'email',
        ];

        $response = $this->actingAs($lawyer)->put(route('clients.update', $client->id), $updateData);

        $response->assertSessionHasNoErrors();
        $client->refresh();

        $this->assertEquals('Apex Global Logistics Pvt Ltd', $client->name);
        $this->assertEquals('Dr. Arun Sen', $client->contact_person);
        $this->assertEquals('U63090DL2018PTC998877', $client->registration_number);
        $this->assertEquals('07BBBBB1111B2Z6', $client->tax_id);
        $this->assertEquals('Supply Chain & Logistics', $client->industry);
        $this->assertEquals('https://apexlogistic.example.com', $client->website);
    }
}
