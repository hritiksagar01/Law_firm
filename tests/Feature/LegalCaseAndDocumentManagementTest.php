<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConflictCheck;
use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class LegalCaseAndDocumentManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Verify Dashboard renders 3-week calendar, matters requiring attention, and required metric segments without receivables
     */
    public function test_dashboard_renders_enhanced_calendar_and_attention_matters(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($partner)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Previous Week');
        $response->assertSee('This Week');
        $response->assertSee('Next Week');
        $response->assertSee('Matters requiring attention');
        $response->assertSee('Unread messages');
        $response->assertSee('Uploads to review');
        $response->assertDontSee('outstanding receivables');
    }

    /**
     * Verify all 16 required navigation modules exist in the attorney workspace
     */
    public function test_sidebar_navigation_contains_all_required_modules(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($partner)->get(route('dashboard'));

        $response->assertStatus(200);

        // Core 16 modules requested by the user
        $response->assertSee('Clients');
        $response->assertSee('Matters');
        $response->assertSee('Documents');
        $response->assertSee('Document Requests');
        $response->assertSee('Messages');
        $response->assertSee('Tasks');
        $response->assertSee('My Todos');
        $response->assertSee('Notes');
        $response->assertSee('Calendar');
        $response->assertSee('Search');
        $response->assertSee('Analytics');
        $response->assertSee('Notifications');
        $response->assertSee('Activity');
        $response->assertSee('Profile');
        $response->assertSee('Settings');
        $response->assertSee('Conflict Checks');
    }

    /**
     * Verify Client Lifecycle statuses and counsel assignment
     */
    public function test_client_lifecycle_and_counsel_assignment(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();
        $associate = User::where('firm_id', $partner->firm_id)->where('id', '!=', $partner->id)->firstOrFail();

        // Create client with lifecycle details
        $client = Client::create([
            'firm_id' => $partner->firm_id,
            'name' => 'Meera Nambiar & Associates',
            'category' => 'corporate',
            'client_type' => 'corporate',
            'status' => Client::STATUS_INTAKE,
            'intake_status' => 'in_review',
            'conflict_check_status' => 'pending',
            'preferred_attorney_id' => $partner->id,
            'assigned_paralegal_id' => $associate->id,
            'referral_source' => 'Bombay Bar Council',
            'onboarding_mode' => 'portal_online',
            'email' => 'meera@nambiar-legal.in',
            'phone' => '+91 98200 11223',
        ]);

        $this->assertEquals(Client::STATUS_INTAKE, $client->status);
        $this->assertEquals($partner->id, $client->preferredAttorney->id);
        $this->assertEquals($associate->id, $client->assignedParalegal->id);

        $response = $this->actingAs($partner)->get(route('clients.show', $client->id));
        $response->assertStatus(200);
        $response->assertSee('Meera Nambiar & Associates');
        $response->assertSee('Intake');
        $response->assertSee('Bombay Bar Council');
    }

    /**
     * Verify Structured Conflict-Check creation, live search, and reviewer sign-off
     */
    public function test_conflict_check_workflow_and_clearance(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();
        $client = Client::where('firm_id', $partner->firm_id)->firstOrFail();
        $matter = Matter::where('firm_id', $partner->firm_id)->firstOrFail();

        // 1. Live Conflict Scanner API
        $searchResponse = $this->actingAs($partner)->get(route('conflict-checks.search', ['q' => substr($client->name, 0, 4)]));
        $searchResponse->assertStatus(200);
        $searchResponse->assertJsonStructure(['search_term', 'matches_found', 'results']);

        // 2. Conflict Check Record Creation
        $storeResponse = $this->actingAs($partner)->post(route('conflict-checks.store'), [
            'client_id' => $client->id,
            'matter_id' => $matter->id,
            'search_terms' => 'Acme Corp, Rajesh Sharma, Opposing Bank',
            'status' => ConflictCheck::STATUS_CLEAR,
            'conflict_description' => 'Cross-referenced adverse witness list and registry. No conflicts found.',
            'resolution' => 'Unconditional ethical representation cleared.',
        ]);

        $storeResponse->assertRedirect();
        $check = ConflictCheck::where('client_id', $client->id)->latest()->firstOrFail();
        $this->assertStringStartsWith('CC-', $check->check_number);
        $this->assertEquals(ConflictCheck::STATUS_CLEAR, $check->status);

        // 3. View Dossier
        $showResponse = $this->actingAs($partner)->get(route('conflict-checks.show', $check->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($check->check_number);
        $showResponse->assertSee('Cleared');
    }

    /**
     * Verify Matter team assignment, roles, and automated MatterActivity feed
     */
    public function test_matter_team_assignment_and_activity_feed(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();
        $associate = User::where('firm_id', $partner->firm_id)->where('id', '!=', $partner->id)->firstOrFail();
        $matter = Matter::where('firm_id', $partner->firm_id)->firstOrFail();

        // Assign associate to matter
        $assignResponse = $this->actingAs($partner)->post(route('matters.team.store', $matter->id), [
            'user_id' => $associate->id,
            'role' => 'associate',
            'access_level' => 'write',
        ]);

        $assignResponse->assertRedirect();
        $this->assertTrue($matter->users()->where('users.id', $associate->id)->exists());

        // Verify activity logged
        $activity = MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'assignment_changed')
            ->latest()
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString($associate->name, $activity->description);

        // Remove team member
        $removeResponse = $this->actingAs($partner)->delete(route('matters.team.destroy', ['matter' => $matter->id, 'user' => $associate->id]));
        $removeResponse->assertRedirect();

        // Verify removal recorded
        $this->assertEquals(0, $matter->users()->where('users.id', $associate->id)->wherePivot('is_active', true)->count());
    }

    /**
     * Verify newly linked modules (Messages, Document Requests, Notes, Analytics, Activity)
     */
    public function test_practice_management_modules_render_properly(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();

        $this->actingAs($partner)->get(route('messages.index'))->assertStatus(200);
        $this->actingAs($partner)->get(route('document-requests.index'))->assertStatus(200);
        $this->actingAs($partner)->get(route('notes.index'))->assertStatus(200);
        $this->actingAs($partner)->get(route('analytics.index'))->assertStatus(200);
        $this->actingAs($partner)->get(route('activity.index'))->assertStatus(200);
        $this->actingAs($partner)->get(route('conflict-checks.index'))->assertStatus(200);
    }
}
