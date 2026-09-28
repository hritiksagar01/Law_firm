<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Firm;
use App\Models\Matter;
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

    /**
     * Test that client dashboard displays all 7 required components with sample data.
     */
    public function test_client_dashboard_displays_all_seven_required_components(): void
    {
        $elena = User::where('email', 'elena.marsh@marshholdings.com')->firstOrFail();

        $response = $this->actingAs($elena)->get(route('portal.dashboard'));

        $response->assertStatus(200);
        // 1. Active matters
        $response->assertSee('Active Cases');
        $response->assertSee('Active Matters');
        // 2. Recent documents
        $response->assertSee('Recent Documents');
        $response->assertSee('Vault Documents');
        // 3. Pending document requests
        $response->assertSee('Pending Document Requests');
        $response->assertSee('Pending requests');
        // 4. Upcoming events / Court calendar
        $response->assertSee('Upcoming events');
        $response->assertSee('Court Calendar');
        // 5. Recent messages
        $response->assertSee('Recent Messages');
        $response->assertSee('Counsel Channel');
        // 6. Tasks requiring client action
        $response->assertSee('Tasks Requiring Client Action');
        $response->assertSee('Client action tasks');
        // 7. Notifications
        $response->assertSee('Notifications');
        $response->assertSee('Live');
    }

    /**
     * Test that client matter view displays overview specs and all 8 tabs with client-safe content.
     */
    public function test_client_matter_view_displays_overview_and_all_eight_tabs(): void
    {
        $elena = User::where('email', 'elena.marsh@marshholdings.com')->firstOrFail();
        $client = Client::where('user_id', $elena->id)->firstOrFail();
        $matter = Matter::where('client_id', $client->id)->firstOrFail();

        $response = $this->actingAs($elena)->get(route('portal.matters.show', $matter->id));

        $response->assertStatus(200);

        // Overview specifications
        $response->assertSee($matter->case_number);
        $response->assertSee($matter->title);
        $response->assertSee('Lead Arguing Counsel');
        $response->assertSee('Chambers Team Assigned');
        $response->assertSee('Official Docket Specifications');

        // All 8 Tabs present in navigation
        $response->assertSee('Overview');
        $response->assertSee('Documents');
        $response->assertSee('Requests');
        $response->assertSee('Messages');
        $response->assertSee('Tasks');
        $response->assertSee('Calendar');
        $response->assertSee('Permitted Notes');
        $response->assertSee('Activity');

        // Tab contents present
        $response->assertSee('Client-Authorized Document Vault');
        $response->assertSee('Document Requests for this Case');
        $response->assertSee('Permitted Briefing Notes');
        $response->assertSee('Procedural Activity Log');
    }

    /**
     * Test client isolation: Clients cannot access matters belonging to other clients.
     */
    public function test_client_cannot_access_other_clients_matter(): void
    {
        $elena = User::where('email', 'elena.marsh@marshholdings.com')->firstOrFail();
        $sam = User::where('email', 'sam.whitaker@whitakertech.io')->firstOrFail();
        $samClient = Client::where('user_id', $sam->id)->firstOrFail();
        $samMatter = Matter::where('client_id', $samClient->id)->firstOrFail();

        // Elena attempts to view Sam's matter -> Forbidden 403
        $response = $this->actingAs($elena)->get(route('portal.matters.show', $samMatter->id));
        $response->assertStatus(403);
    }
}
