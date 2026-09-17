<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class SystemSimulationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * 1. Guest redirect to login
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/portal/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * 2. Advocate Dashboard renders with firm-scoped data
     */
    public function test_advocate_dashboard_renders_with_firm_scoped_data(): void
    {
        $advocate = User::where('role', 'partner')->first();
        $this->assertNotNull($advocate, 'Partner advocate user must exist.');

        $response = $this->actingAs($advocate)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Active Case Dossiers');
        $response->assertSee('Chambers Docket');
    }

    /**
     * 3. Client Portal Dashboard renders for client
     */
    public function test_client_portal_dashboard_renders_for_client(): void
    {
        $clientUser = User::where('role', 'client')->first();
        $this->assertNotNull($clientUser, 'Client user must exist.');

        $response = $this->actingAs($clientUser)->get('/portal/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Active Cases');
        $response->assertSee('Vault Documents');
    }

    /**
     * 4. Super Admin Dashboard renders
     */
    public function test_super_admin_dashboard_renders(): void
    {
        $admin = User::where('role', 'superadmin')->first();
        $this->assertNotNull($admin, 'Superadmin user must exist.');

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Platform Tenant Telemetry');
    }

    /**
     * 5. Billing & Financial Hub renders
     */
    public function test_billing_hub_renders_for_firm_staff(): void
    {
        $advocate = User::where('role', 'partner')->first();
        $this->assertNotNull($advocate);

        $response = $this->actingAs($advocate)->get('/billing');
        $response->assertStatus(200);
        $response->assertSee('Billing');
    }

    /**
     * 6. Operational Reports Hub and Sub-Reports render
     */
    public function test_operational_reports_render(): void
    {
        $advocate = User::where('role', 'partner')->first();
        $this->assertNotNull($advocate);

        $response = $this->actingAs($advocate)->get('/reports');
        $response->assertStatus(200);

        $response = $this->actingAs($advocate)->get('/reports/cases');
        $response->assertStatus(200);

        $response = $this->actingAs($advocate)->get('/reports/hearings');
        $response->assertStatus(200);

        $response = $this->actingAs($advocate)->get('/reports/clients');
        $response->assertStatus(200);

        $response = $this->actingAs($advocate)->get('/reports/workload');
        $response->assertStatus(200);

        $response = $this->actingAs($advocate)->get('/reports/bank-activity');
        $response->assertStatus(200);
    }

    /**
     * 7. Daily Broadsheet Executive Briefing renders
     */
    public function test_daily_broadsheet_briefing_renders(): void
    {
        $advocate = User::where('role', 'partner')->first();
        $this->assertNotNull($advocate);

        $response = $this->actingAs($advocate)->get('/briefing');
        $response->assertStatus(200);
        $response->assertSee('The Chambers Broadsheet');
    }

    /**
     * 8. Cross-Tenant Isolation: Firm 1 staff cannot access Firm 2 matter dossier
     */
    public function test_cross_tenant_isolation_blocks_unauthorized_matter_access(): void
    {
        $firm1 = Firm::where('slug', 'vennamraj-associates')->first();
        $firm2 = Firm::where('slug', 'sjm-legal-chambers')->first();

        $this->assertNotNull($firm1, 'Firm 1 must exist');
        $this->assertNotNull($firm2, 'Firm 2 must exist');

        $firm1Advocate = User::where('firm_id', $firm1->id)->where('role', 'partner')->first();
        $firm2Matter = Matter::where('firm_id', $firm2->id)->first();

        $this->assertNotNull($firm1Advocate, 'Firm 1 partner must exist');
        $this->assertNotNull($firm2Matter, 'Firm 2 matter must exist');

        $response = $this->actingAs($firm1Advocate)->get('/matters/' . $firm2Matter->id);
        $response->assertStatus(403);
    }

    /**
     * 9. Cross-Client Isolation: Client cannot access another client's portal matter
     */
    public function test_cross_client_isolation_blocks_other_client_matter(): void
    {
        $clientUser = User::where('role', 'client')->first();
        $client = Client::where('user_id', $clientUser->id)->first() 
            ?? Client::where('email', $clientUser->email)->first();

        $this->assertNotNull($clientUser);
        $this->assertNotNull($client);

        $otherMatter = Matter::where('client_id', '!=', $client->id)->first();
        $this->assertNotNull($otherMatter);

        $response = $this->actingAs($clientUser)->get('/portal/matters/' . $otherMatter->id);
        $response->assertStatus(403);
    }

    /**
     * 10. Automated Test Management Audit runs 23 suites successfully
     */
    public function test_automated_test_management_system_audit(): void
    {
        $tms = new \App\Services\TestManagementSystem();
        $results = $tms->runAll();

        $this->assertEquals('PASSED', $results['status']);
        $this->assertEquals(0, $results['failed']);
        $this->assertGreaterThanOrEqual(23, $results['total_suites']);
        $this->assertGreaterThanOrEqual(60, $results['total_tests']);
    }

    /**
     * 11. Custom 404 page renders for nonexistent route
     */
    public function test_custom_404_error_page(): void
    {
        $response = $this->get('/nonexistent-docket-url-404');
        $response->assertStatus(404);
        $response->assertSee('Docket Not Found');
    }
}
