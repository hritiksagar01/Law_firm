<?php

namespace Tests\Feature;

use App\Models\CaseNote;
use App\Models\DocumentRequest;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class ParalegalAndStaffModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test 26: Paralegal can access Assigned Matters dashboard with metrics and tabbed interface.
     */
    public function test_paralegal_can_access_assigned_matters_dashboard_with_metrics_and_tabs(): void
    {
        $paralegal = User::where('role', 'paralegal')->firstOrFail();
        $assignedMatter = $paralegal->assignedMatters()->firstOrFail();

        $response = $this->actingAs($paralegal)->get(route('paralegal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Paralegal Practice Workspace');
        $response->assertSee('Assigned Matters');
        $response->assertSee('Open Tasks');
        $response->assertSee('Pending Client Docs');
        $response->assertSee('Court Hearings &amp; Deadlines', false);
        $response->assertSee('Unread Messages');
        $response->assertSee($assignedMatter->title);
    }

    /**
     * Test 27: Staff / Legal Assistant can access Staff Dashboard.
     */
    public function test_staff_can_access_staff_dashboard(): void
    {
        $firm = Firm::firstOrFail();
        $staffUser = User::create([
            'firm_id' => $firm->id,
            'name' => 'Sunita Sharma (Legal Assistant)',
            'email' => 'sunita.staff@sharmalegal.in',
            'password' => bcrypt('password123'),
            'role' => 'legal_assistant',
            'title' => 'Legal Assistant & Case Coordinator',
        ]);

        $matter = Matter::where('firm_id', $firm->id)->firstOrFail();
        $matter->users()->attach($staffUser->id);

        $response = $this->actingAs($staffUser)->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Legal Assistant &amp; Staff Workspace', false);
        $response->assertSee($matter->title);
    }

    /**
     * Test Dashboard displays open & overdue tasks, pending client documents, and upcoming deadlines/hearings.
     */
    public function test_dashboard_displays_overdue_tasks_pending_documents_and_upcoming_hearings(): void
    {
        $paralegal = User::where('role', 'paralegal')->firstOrFail();
        $matter = $paralegal->assignedMatters()->firstOrFail();

        // 1. Create overdue task on assigned matter
        $overdueTask = Task::create([
            'firm_id' => $paralegal->firm_id,
            'matter_id' => $matter->id,
            'assigned_to' => $paralegal->id,
            'created_by' => $paralegal->id,
            'title' => 'Urgent Docket Process Service Verification',
            'status' => 'pending',
            'priority' => 'urgent',
            'due_date' => now()->subDays(2),
        ]);

        // 2. Create pending client document request
        $docRequest = DocumentRequest::create([
            'firm_id' => $paralegal->firm_id,
            'matter_id' => $matter->id,
            'client_id' => $matter->client_id,
            'requested_by' => $paralegal->id,
            'title' => 'Certified Commercial Lease Agreement 2026',
            'description' => 'Original signed copy required for high court filing',
            'status' => 'pending',
            'due_date' => now()->addDays(4),
        ]);

        // 3. Set upcoming hearing date on matter
        $matter->update([
            'hearing_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($paralegal)->get(route('paralegal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee($overdueTask->title);
        $response->assertSee('Overdue');
        $response->assertSee($docRequest->title);
        $response->assertSee('Pending Client Docs');
        $response->assertSee($matter->court_name);
    }

    /**
     * Test 27: Paralegal and Staff can update administrative matter fields.
     */
    public function test_paralegal_and_staff_can_update_administrative_matter_fields(): void
    {
        $paralegal = User::where('role', 'paralegal')->firstOrFail();
        $matter = $paralegal->assignedMatters()->firstOrFail();

        $updateData = [
            'title' => 'Malhotra Enterprises v. Apex Commercial Bank (Updated Admin)',
            'case_number' => 'CS (COMM) 999/2026',
            'docket_number' => 'DOCK-2026-999',
            'court_name' => 'High Court of Delhi — Court No. 4',
            'court_type' => 'High Court',
            'jurisdiction' => 'Commercial Division',
            'judge_name' => 'Hon. Justice C. Hari Shankar',
            'stage' => 'Evidence & Arguments',
            'priority' => 'high',
            'filing_date' => '2026-01-20',
            'hearing_date' => '2026-10-15',
            'statute_references' => 'Commercial Courts Act, 2015 s. 12A',
            'description' => 'Administrative docket details maintained by paralegal.',
        ];

        $response = $this->actingAs($paralegal)->put(route('matters.administrative.update', $matter), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $matter->refresh();
        $this->assertSame('Malhotra Enterprises v. Apex Commercial Bank (Updated Admin)', $matter->title);
        $this->assertSame('CS (COMM) 999/2026', $matter->case_number);
        $this->assertSame('DOCK-2026-999', $matter->docket_number);
        $this->assertSame('High Court of Delhi — Court No. 4', $matter->court_name);
        $this->assertSame('Commercial Division', $matter->jurisdiction);
        $this->assertSame('Hon. Justice C. Hari Shankar', $matter->judge_name);
        $this->assertSame('Evidence & Arguments', $matter->stage);
        $this->assertSame('high', $matter->priority);

        // Verify MatterActivity recorded
        $this->assertTrue(
            MatterActivity::where('matter_id', $matter->id)
                ->where('activity_type', 'matter_updated')
                ->where('description', 'like', '%updated case administrative metadata%')
                ->exists()
        );
    }

    /**
     * Security & Privilege Guard: Staff should NOT have access to matter closure.
     */
    public function test_staff_cannot_close_matters_but_attorney_can(): void
    {
        $paralegal = User::where('role', 'paralegal')->firstOrFail();
        $partner = User::where('role', 'partner')->firstOrFail();
        $matter = Matter::where('firm_id', $paralegal->firm_id)->where('status', 'active')->firstOrFail();

        // 1. Paralegal attempts to close matter -> 403 Forbidden
        $staffAttempt = $this->actingAs($paralegal)->patch(route('matters.status.update', $matter), [
            'status' => 'closed',
            'closing_notes' => 'Attempted staff closure without authorization.',
        ]);

        $staffAttempt->assertStatus(403);
        $matter->refresh();
        $this->assertSame('active', $matter->status);

        // 2. Attorney / Partner closes matter -> Success 302
        $attorneyAttempt = $this->actingAs($partner)->patch(route('matters.status.update', $matter), [
            'status' => 'closed',
            'closing_notes' => 'Authorized advocate disposition and decree settlement.',
        ]);

        $attorneyAttempt->assertRedirect();
        $matter->refresh();
        $this->assertSame('closed', $matter->status);
    }

    /**
     * Security & Privilege Guard: Staff should NOT have unrestricted access to privileged attorney notes.
     */
    public function test_staff_cannot_view_or_create_privileged_attorney_notes(): void
    {
        $paralegal = User::where('role', 'paralegal')->firstOrFail();
        $partner = User::where('role', 'partner')->firstOrFail();
        $matter = $paralegal->assignedMatters()->firstOrFail();

        // 1. Partner creates privileged attorney work-product note
        $privilegedNote = CaseNote::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'user_id' => $partner->id,
            'title' => 'Privileged Senior Counsel Trial Assessment',
            'body' => 'Confidential attorney-client mental impressions on settlement leverage.',
            'type' => 'internal',
            'is_privileged' => true,
        ]);

        // 2. Partner creates non-privileged routine note
        $routineNote = CaseNote::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'user_id' => $partner->id,
            'title' => 'Service of Process Speed Post Tracking Note',
            'body' => 'Process server confirmed service on respondents.',
            'type' => 'internal',
            'is_privileged' => false,
        ]);

        // 3. Paralegal views notes index -> Sees routine note, does NOT see privileged note
        $indexResponse = $this->actingAs($paralegal)->get(route('notes.index', ['matter_id' => $matter->id]));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($routineNote->title);
        $indexResponse->assertDontSee($privilegedNote->title);

        // 4. Paralegal views matter detail view -> Does NOT see privileged note
        $matterShowResponse = $this->actingAs($paralegal)->get(route('matters.show', $matter));
        $matterShowResponse->assertStatus(200);
        $matterShowResponse->assertSee($routineNote->title);
        $matterShowResponse->assertDontSee($privilegedNote->title);

        // 5. Paralegal attempts to create a privileged note -> 403 Forbidden
        $createPrivilegedAttempt = $this->actingAs($paralegal)->post(route('notes.store'), [
            'matter_id' => $matter->id,
            'title' => 'Paralegal Attempting Privileged Note',
            'body' => 'Unauthorized privileged note content.',
            'type' => 'internal',
            'is_privileged' => 1,
        ]);
        $createPrivilegedAttempt->assertStatus(403);

        // 6. Paralegal creates a standard internal note -> 302 Success
        $createSafeNote = $this->actingAs($paralegal)->post(route('notes.store'), [
            'matter_id' => $matter->id,
            'title' => 'Paralegal Certified Copy Inspection Note',
            'body' => 'Inspected court file at Registry counter 3 today.',
            'type' => 'internal',
            'is_privileged' => 0,
        ]);
        $createSafeNote->assertRedirect();

        $this->assertTrue(
            CaseNote::where('matter_id', $matter->id)
                ->where('title', 'Paralegal Certified Copy Inspection Note')
                ->where('is_privileged', false)
                ->exists()
        );
    }

    /**
     * Security Guard: Staff should NOT have access to security settings or user permissions.
     */
    public function test_staff_cannot_access_security_settings_or_user_permissions(): void
    {
        $paralegal = User::where('role', 'paralegal')->firstOrFail();
        $partner = User::where('role', 'partner')->firstOrFail();

        // 1. Staff access to /settings is blocked with 403
        $settingsResponse = $this->actingAs($paralegal)->get(route('settings.index'));
        $settingsResponse->assertStatus(403);

        // 2. Staff access to /users is blocked with 403
        $usersResponse = $this->actingAs($paralegal)->get(route('users.index'));
        $usersResponse->assertStatus(403);

        // 3. Staff access to /user-groups is blocked with 403
        $userGroupsResponse = $this->actingAs($paralegal)->get(route('user-groups.index'));
        $userGroupsResponse->assertStatus(403);

        // 4. Partner can access settings and users successfully
        $partnerSettings = $this->actingAs($partner)->get(route('settings.index'));
        $partnerSettings->assertStatus(200);

        $partnerUsers = $this->actingAs($partner)->get(route('users.index'));
        $partnerUsers->assertStatus(200);
    }

    /**
     * Test Staff can communicate with clients where permitted and manage tasks/todos.
     */
    public function test_staff_can_communicate_with_clients_and_manage_tasks_on_assigned_matter(): void
    {
        $paralegal = User::where('role', 'paralegal')->firstOrFail();
        $matter = $paralegal->assignedMatters()->firstOrFail();

        // 1. Communicate with client on assigned matter
        $messageResponse = $this->actingAs($paralegal)->post(route('messages.store'), [
            'matter_id' => $matter->id,
            'body' => 'Dear Client, the certified copy of order has been applied for at the registry.',
            'is_privileged' => 0,
        ]);

        $messageResponse->assertRedirect();
        $this->assertTrue(
            Message::where('matter_id', $matter->id)
                ->where('sender_id', $paralegal->id)
                ->where('body', 'like', '%certified copy of order has been applied for%')
                ->exists()
        );

        // 2. Create task on assigned matter
        $taskResponse = $this->actingAs($paralegal)->post(route('tasks.store'), [
            'matter_id' => $matter->id,
            'title' => 'Obtain Certified Copy from Registry',
            'description' => 'Pay court fee and file inspection slip.',
            'assigned_to' => $paralegal->id,
            'priority' => 'high',
            'status' => 'not_started',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $taskResponse->assertRedirect();
        $this->assertTrue(
            Task::where('matter_id', $matter->id)
                ->where('title', 'Obtain Certified Copy from Registry')
                ->exists()
        );
    }
}
