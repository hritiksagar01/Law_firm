<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Firm;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Database\Seeders\QuireDemoSeeder;
use Tests\TestCase;

class ClientDashboardSamplesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
        $this->seed(QuireDemoSeeder::class);
    }

    /**
     * Test that client dashboard renders sample pending document requests for Quire demo clients.
     */
    public function test_client_dashboard_renders_pending_document_requests_for_quire_client(): void
    {
        $elena = User::where('email', 'elena.marsh@marshholdings.com')->firstOrFail();

        $response = $this->actingAs($elena)->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pending Document Requests');
        $response->assertSee('Action Item');
        $response->assertSee('Certificate of Good Standing');
        $response->assertSee('Audited Financial Ledger');
        $response->assertSee('Upload Now');
        $response->assertDontSee('All Document Requests Completed');
    }

    /**
     * Test that client document requests page (/portal/requests) renders sample items with tabs.
     */
    public function test_client_requests_page_renders_samples_with_filters(): void
    {
        $sam = User::where('email', 'sam.whitaker@whitakertech.io')->firstOrFail();

        $response = $this->actingAs($sam)->get(route('portal.requests.index'));

        $response->assertStatus(200);
        $response->assertSee('Document Requests');
        $response->assertSee('Action Required');
        $response->assertSee('All Requests');
        $response->assertSee('Pending');
        $response->assertSee('Certificate of Good Standing');
        $response->assertSee('Master Services Agreement');
    }

    /**
     * Test that a newly registered client with 0 requests automatically gets sample document requests.
     */
    public function test_client_dashboard_auto_populates_sample_requests_for_new_client(): void
    {
        $firm = Firm::firstOrFail();

        $newUser = User::create([
            'firm_id' => $firm->id,
            'name' => 'Aditya Sen',
            'email' => 'aditya.'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'role' => 'client',
            'status' => 'active',
        ]);

        $newClient = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $newUser->id,
            'name' => 'Sen & Sons Global Logistics',
            'contact_person' => 'Aditya Sen',
            'email' => $newUser->email,
            'phone' => '+91 99887 66554',
            'category' => 'corporate',
            'type' => 'corporate',
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $response = $this->actingAs($newUser)->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pending Document Requests');
        $response->assertSee('Action Item');
        $response->assertSee('Board Resolution Authorizing');
        $response->assertSee('Audited Balance Sheets');
        $response->assertSee('Upload Now');
        $response->assertDontSee('All Document Requests Completed');
    }
}
