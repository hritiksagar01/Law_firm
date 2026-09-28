<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Document;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MatterActivityChronologyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test matter creation logs matter_created activity.
     */
    public function test_matter_creation_logs_activity(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $client = Client::where('firm_id', $firm->id)->first() ?: Client::factory()->create(['firm_id' => $firm->id]);

        $response = $this->actingAs($attorney)->post(route('matters.store'), [
            'title' => 'Supreme Court Constitutional Writ',
            'client_id' => $client->id,
            'lead_attorney_id' => $attorney->id,
            'practice_area' => 'Constitutional',
            'case_type' => 'Writ Petition',
            'stage' => 'Intake',
            'status' => 'active',
            'priority' => 'urgent',
            'filing_date' => now()->toDateString(),
        ]);

        $matter = Matter::where('title', 'Supreme Court Constitutional Writ')->first();
        $this->assertNotNull($matter);

        $activity = MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'matter_created')
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Initiated new case dossier', $activity->description);
    }

    /**
     * Test document lifecycle activities:
     * - document_uploaded
     * - document_viewed
     * - document_downloaded
     * - document_shared
     */
    public function test_document_lifecycle_activities_are_recorded(): void
    {
        Storage::fake('local');

        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $matter = Matter::where('firm_id', $firm->id)->first() ?: Matter::factory()->create([
            'firm_id' => $firm->id,
            'lead_attorney_id' => $attorney->id,
        ]);

        // 1. Upload document
        $file = UploadedFile::fake()->create('Petition_Draft_v1.pdf', 500, 'application/pdf');
        $uploadResponse = $this->actingAs($attorney)->post(route('documents.upload'), [
            'matter_id' => $matter->id,
            'title' => 'Verified Writ Petition',
            'category' => 'Pleadings',
            'privilege' => 'Work Product',
            'visibility' => 'internal_only',
            'file' => $file,
        ]);
        $uploadResponse->assertRedirect();

        $document = Document::where('matter_id', $matter->id)->where('title', 'Verified Writ Petition')->first();
        $this->assertNotNull($document);

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'document_uploaded')
            ->where('subject_id', $document->id)
            ->exists());

        // 2. View document
        $viewResponse = $this->actingAs($attorney)->get(route('documents.view', $document));
        $viewResponse->assertStatus(200);

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'document_viewed')
            ->where('subject_id', $document->id)
            ->exists());

        // 3. Download document
        $downloadResponse = $this->actingAs($attorney)->get(route('documents.download', $document));
        $downloadResponse->assertStatus(200);

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'document_downloaded')
            ->where('subject_id', $document->id)
            ->exists());

        // 4. Share document with client
        $shareResponse = $this->actingAs($attorney)->post(route('documents.share', $document));
        $shareResponse->assertRedirect();

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'document_shared')
            ->where('subject_id', $document->id)
            ->exists());
    }

    /**
     * Test task, note, message, status changed, assignment changed, and calendar event activities.
     */
    public function test_tasks_notes_status_assignment_and_calendar_activities_are_logged(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $colleague = User::factory()->create(['firm_id' => $firm->id, 'role' => 'associate']);
        $matter = Matter::where('firm_id', $firm->id)->first() ?: Matter::factory()->create([
            'firm_id' => $firm->id,
            'lead_attorney_id' => $attorney->id,
        ]);

        // 1. Team assignment
        $teamResponse = $this->actingAs($attorney)->post(route('matters.team.store', $matter), [
            'user_id' => $colleague->id,
            'role' => 'associate',
            'access_level' => 'write',
        ]);
        $teamResponse->assertRedirect();

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'assignment_changed')
            ->exists());

        // 2. Note added
        $noteResponse = $this->actingAs($attorney)->post(route('notes.store'), [
            'matter_id' => $matter->id,
            'title' => 'Hearing Preparation Notes',
            'body' => 'Review Section 138 authorities before division bench.',
            'type' => 'internal',
        ]);
        $noteResponse->assertRedirect();

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'note_added')
            ->exists());

        // 3. Calendar hearing scheduled
        $appointmentResponse = $this->actingAs($attorney)->post(route('appointments.store'), [
            'matter_id' => $matter->id,
            'client_id' => $matter->client_id,
            'user_id' => $attorney->id,
            'title' => 'Preliminary Motion Hearing',
            'type' => 'court_appearance',
            'scheduled_at' => now()->addDays(5)->toDateTimeString(),
            'duration_minutes' => 60,
            'location' => 'Court Hall 4',
        ]);
        $appointmentResponse->assertRedirect();

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'hearing_scheduled')
            ->exists());

        // 4. Status changed
        $statusResponse = $this->actingAs($attorney)->patch(route('matters.status.update', $matter), [
            'status' => 'closed',
            'closing_notes' => 'Decree granted by court.',
        ]);
        $statusResponse->assertRedirect();

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'status_changed')
            ->exists());
    }

    /**
     * Test Example Chronology Flow:
     * Client uploaded medical records → Attorney reviewed documents → Complaint drafted → Complaint filed → Hearing scheduled.
     */
    public function test_example_chronology_pipeline_progression(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $matter = Matter::where('firm_id', $firm->id)->first() ?: Matter::factory()->create([
            'firm_id' => $firm->id,
            'lead_attorney_id' => $attorney->id,
        ]);

        $baseTime = now()->subDays(10);

        // Milestone 1: Client uploaded medical records
        MatterActivity::log(
            matter: $matter,
            activityType: 'client_activity',
            description: 'Client uploaded medical records and hospital discharge summary',
            userId: $attorney->id,
            clientId: $matter->client_id,
            isClientSafe: true,
            occurredAt: (clone $baseTime)
        );

        // Milestone 2: Attorney reviewed documents
        MatterActivity::log(
            matter: $matter,
            activityType: 'medical_records_reviewed',
            description: 'Attorney reviewed documents and medical negligence opinion',
            userId: $attorney->id,
            clientId: $matter->client_id,
            isClientSafe: false,
            occurredAt: (clone $baseTime)->addDays(2)
        );

        // Milestone 3: Complaint drafted
        MatterActivity::log(
            matter: $matter,
            activityType: 'complaint_drafted',
            description: 'Complaint drafted and verified by senior advocate',
            userId: $attorney->id,
            clientId: $matter->client_id,
            isClientSafe: false,
            occurredAt: (clone $baseTime)->addDays(4)
        );

        // Milestone 4: Complaint filed
        MatterActivity::log(
            matter: $matter,
            activityType: 'complaint_filed',
            description: 'Complaint filed before Consumer Dispute Commission with initial docket',
            userId: $attorney->id,
            clientId: $matter->client_id,
            isClientSafe: true,
            occurredAt: (clone $baseTime)->addDays(6)
        );

        // Milestone 5: Hearing scheduled
        MatterActivity::log(
            matter: $matter,
            activityType: 'hearing_scheduled',
            description: 'Hearing scheduled for admission and interim relief',
            userId: $attorney->id,
            clientId: $matter->client_id,
            isClientSafe: true,
            occurredAt: (clone $baseTime)->addDays(8)
        );

        // Verify all 5 chronological milestones exist
        $chronology = MatterActivity::where('matter_id', $matter->id)
            ->chronological('asc')
            ->get();

        $this->assertGreaterThanOrEqual(5, $chronology->count());

        // Check chronological view displays the milestones
        $response = $this->actingAs($attorney)->get(route('matters.chronology', $matter));
        $response->assertStatus(200);
        $response->assertSee('Client uploaded medical records');
        $response->assertSee('Attorney reviewed documents');
        $response->assertSee('Complaint drafted');
        $response->assertSee('Complaint filed');
        $response->assertSee('Hearing scheduled');
    }

    /**
     * Test Client Portal strictly filters internal activities:
     * Only is_client_safe = true activities should be displayed on client portal.
     */
    public function test_client_portal_strict_activity_isolation(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $client = Client::where('firm_id', $firm->id)->first() ?: Client::factory()->create(['firm_id' => $firm->id]);
        $clientUser = User::factory()->create([
            'firm_id' => $firm->id,
            'role' => 'client',
        ]);
        $client->update(['user_id' => $clientUser->id]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'lead_attorney_id' => $attorney->id,
            'case_number' => 'MAT-TEST-778',
            'title' => 'Portal Activity Isolation Matter',
            'status' => 'active',
            'priority' => 'high',
        ]);

        // Client Safe event
        MatterActivity::log(
            matter: $matter,
            activityType: 'document_uploaded',
            description: 'Client Public Filing Uploaded: Form 16',
            userId: $attorney->id,
            clientId: $client->id,
            isClientSafe: true
        );

        // Internal privileged event (MUST NEVER leak to client portal)
        MatterActivity::log(
            matter: $matter,
            activityType: 'note_added',
            description: 'Confidential Internal Assessment: High exposure risk on liability claim',
            userId: $attorney->id,
            clientId: $client->id,
            isClientSafe: false
        );

        $portalResponse = $this->actingAs($clientUser)->get(route('portal.matters.show', $matter));
        $portalResponse->assertStatus(200);

        // Safe activity is visible
        $portalResponse->assertSee('Client Public Filing Uploaded: Form 16');

        // Internal privileged activity is STRICTLY NOT visible
        $portalResponse->assertDontSee('Confidential Internal Assessment: High exposure risk on liability claim');
    }

    /**
     * Test manual milestone creation via POST /matters/{matter}/chronology.
     */
    public function test_attorney_can_record_custom_chronology_milestone(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $matter = Matter::where('firm_id', $firm->id)->first() ?: Matter::factory()->create([
            'firm_id' => $firm->id,
            'lead_attorney_id' => $attorney->id,
        ]);

        $response = $this->actingAs($attorney)->post(route('matters.chronology.store', $matter), [
            'title' => 'Settlement Offer Tendered',
            'activity_type' => 'milestone',
            'occurred_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'description' => 'Defendant counsel made formal settlement tender of 25 Lakhs.',
            'is_client_safe' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'milestone')
            ->where('description', 'like', '%Settlement Offer Tendered%')
            ->where('is_client_safe', true)
            ->exists());
    }

    /**
     * Test chambers activity index displays audit trail.
     */
    public function test_activity_index_page_is_accessible(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);

        $response = $this->actingAs($attorney)->get(route('activity.index'));
        $response->assertStatus(200);
        $response->assertSee('Case Activity &amp; Audit Trail', false);
    }

    /**
     * Test client portal document upload and download log client safe activity.
     */
    public function test_client_portal_upload_and_download_logs_client_safe_activity(): void
    {
        Storage::fake('local');

        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $client = Client::where('firm_id', $firm->id)->first() ?: Client::factory()->create(['firm_id' => $firm->id]);
        $clientUser = User::factory()->create([
            'firm_id' => $firm->id,
            'role' => 'client',
        ]);
        $client->update(['user_id' => $clientUser->id]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'lead_attorney_id' => $attorney->id,
            'case_number' => 'MAT-CLIENT-991',
            'title' => 'Client Action Test Matter',
            'status' => 'active',
            'priority' => 'medium',
        ]);

        // Client uploads document
        $file = UploadedFile::fake()->create('Medical_Bill_Receipt.pdf', 300, 'application/pdf');
        $uploadResponse = $this->actingAs($clientUser)->post(route('portal.documents.upload'), [
            'matter_id' => $matter->id,
            'title' => 'Medical Bill Receipt',
            'category' => 'Medical Records',
            'file' => $file,
        ]);
        $uploadResponse->assertRedirect();

        $uploadedDoc = Document::where('matter_id', $matter->id)->where('title', 'Medical Bill Receipt')->first();
        $this->assertNotNull($uploadedDoc);

        $uploadActivity = MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'document_uploaded')
            ->where('subject_id', $uploadedDoc->id)
            ->first();
        $this->assertNotNull($uploadActivity);
        $this->assertTrue($uploadActivity->is_client_safe);

        // Client downloads document
        $downloadResponse = $this->actingAs($clientUser)->get(route('portal.documents.download', $uploadedDoc));
        $downloadResponse->assertStatus(200);

        $downloadActivity = MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'document_downloaded')
            ->where('subject_id', $uploadedDoc->id)
            ->first();
        $this->assertNotNull($downloadActivity);
        $this->assertTrue($downloadActivity->is_client_safe);
    }

    /**
     * Test chronology filtering by event type and visibility.
     */
    public function test_chronology_filtering_and_order(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $matter = Matter::where('firm_id', $firm->id)->first() ?: Matter::factory()->create([
            'firm_id' => $firm->id,
            'lead_attorney_id' => $attorney->id,
        ]);

        // Filter by type=documents
        $docFilterResponse = $this->actingAs($attorney)->get(route('matters.chronology', [
            'matter' => $matter,
            'type' => 'documents',
        ]));
        $docFilterResponse->assertStatus(200);

        // Filter by visibility=client_safe
        $safeFilterResponse = $this->actingAs($attorney)->get(route('matters.chronology', [
            'matter' => $matter,
            'visibility' => 'client_safe',
        ]));
        $safeFilterResponse->assertStatus(200);

        // Sort order=desc
        $descOrderResponse = $this->actingAs($attorney)->get(route('matters.chronology', [
            'matter' => $matter,
            'order' => 'desc',
        ]));
        $descOrderResponse->assertStatus(200);
    }

    /**
     * Test unauthorized advocate cannot access matter chronology.
     */
    public function test_unauthorized_advocate_cannot_access_matter_chronology(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $leadAttorney = User::factory()->create(['firm_id' => $firm->id, 'role' => 'associate']);
        $unassignedAttorney = User::factory()->create(['firm_id' => $firm->id, 'role' => 'associate']);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => Client::first()?->id ?: 1,
            'lead_attorney_id' => $leadAttorney->id,
            'case_number' => 'MAT-AUTH-001',
            'title' => 'Secret Dossier',
            'status' => 'active',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($unassignedAttorney)->get(route('matters.chronology', $matter));
        $response->assertStatus(403);
    }

    /**
     * Test chronology CSV export streams entries.
     */
    public function test_chronology_csv_export_streams_entries(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $matter = Matter::where('firm_id', $firm->id)->first() ?: Matter::factory()->create([
            'firm_id' => $firm->id,
            'lead_attorney_id' => $attorney->id,
        ]);

        MatterActivity::log(
            matter: $matter,
            activityType: 'complaint_drafted',
            description: 'Drafted constitutional writ prayer',
            userId: $attorney->id
        );

        $response = $this->actingAs($attorney)->get(route('matters.chronology', [
            'matter' => $matter,
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=', (string) $response->headers->get('Content-Disposition'));
    }

    /**
     * Test client toggling task logs client activity.
     */
    public function test_client_task_toggle_logs_activity(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $client = Client::where('firm_id', $firm->id)->first() ?: Client::factory()->create(['firm_id' => $firm->id]);
        $clientUser = User::factory()->create(['firm_id' => $firm->id, 'role' => 'client']);
        $client->update(['user_id' => $clientUser->id]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'lead_attorney_id' => User::where('firm_id', $firm->id)->first()->id,
            'case_number' => 'MAT-TASK-001',
            'title' => 'Client Task Test Case',
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $task = Task::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'assigned_to' => $clientUser->id,
            'created_by' => $clientUser->id,
            'title' => 'Sign Affidavit Form 4',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($clientUser)->post(route('portal.tasks.toggle', $task));
        $response->assertRedirect();

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'task_completed')
            ->where('subject_id', $task->id)
            ->exists());
    }

    /**
     * Test client viewing document logs document_viewed activity.
     */
    public function test_client_view_document_logs_activity(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $client = Client::where('firm_id', $firm->id)->first() ?: Client::factory()->create(['firm_id' => $firm->id]);
        $clientUser = User::factory()->create(['firm_id' => $firm->id, 'role' => 'client']);
        $client->update(['user_id' => $clientUser->id]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'lead_attorney_id' => User::where('firm_id', $firm->id)->first()->id,
            'case_number' => 'MAT-DOC-VIEW-001',
            'title' => 'Document View Test Case',
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $doc = Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'user_id' => $clientUser->id,
            'title' => 'Affidavit Copy',
            'filename' => 'affidavit.pdf',
            'file_size' => 1024,
            'category' => 'Pleadings',
            'is_client_visible' => true,
        ]);

        $response = $this->actingAs($clientUser)->get(route('portal.documents.view', $doc));
        $response->assertStatus(200);

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'document_viewed')
            ->where('subject_id', $doc->id)
            ->where('is_client_safe', true)
            ->exists());
    }

    /**
     * Test appointment status update logs activity.
     */
    public function test_appointment_status_update_logs_activity(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $matter = Matter::where('firm_id', $firm->id)->first() ?: Matter::factory()->create([
            'firm_id' => $firm->id,
            'lead_attorney_id' => $attorney->id,
        ]);

        $appointment = Appointment::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => $matter->client_id,
            'user_id' => $attorney->id,
            'title' => 'Motion for Directions',
            'type' => 'court_appearance',
            'scheduled_at' => now()->addDays(2),
            'duration_minutes' => 45,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($attorney)->post(route('appointments.update-status', $appointment), [
            'status' => 'adjourned',
        ]);
        $response->assertRedirect();

        $this->assertTrue(MatterActivity::where('matter_id', $matter->id)
            ->where('activity_type', 'hearing_scheduled')
            ->where('description', 'like', '%adjourned%')
            ->exists());
    }

    /**
     * Test internal messages are logged as internal activities.
     */
    public function test_internal_messages_are_logged_as_internal_activities(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $attorney = User::where('firm_id', $firm->id)->whereIn('role', ['admin', 'partner', 'lawyer'])->first()
            ?: User::factory()->create(['firm_id' => $firm->id, 'role' => 'partner']);
        $matter = Matter::where('firm_id', $firm->id)->first() ?: Matter::factory()->create([
            'firm_id' => $firm->id,
            'lead_attorney_id' => $attorney->id,
        ]);

        $response = $this->actingAs($attorney)->post(route('messages.threads.store'), [
            'matter_id' => $matter->id,
            'subject' => 'Confidential Strategy Review',
            'thread_type' => 'internal_team',
            'body' => 'Need to verify jurisdiction grounds with lead counsel.',
        ]);
        $response->assertRedirect();

        $activity = MatterActivity::where('matter_id', $matter->id)
            ->where('description', 'like', '%Confidential Strategy Review%')
            ->first();

        $this->assertNotNull($activity);
        $this->assertFalse($activity->is_client_safe);
    }
}
