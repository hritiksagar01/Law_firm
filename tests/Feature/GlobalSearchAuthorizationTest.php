<?php

namespace Tests\Feature;

use App\Models\CaseNote;
use App\Models\Client;
use App\Models\Document;
use App\Models\Event;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class GlobalSearchAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test staff can search across all 8 entities within their firm:
     * Clients, Matters, Documents, Parties, Messages, Tasks, Notes, and Calendar Events.
     */
    public function test_staff_can_search_across_all_eight_entities_within_firm(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);
        $attorney = User::factory()->create(['firm_id' => $firm->id, 'role' => 'attorney']);

        // 1. Client
        $clientUser = User::factory()->create(['firm_id' => $firm->id, 'role' => 'client']);
        $client = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientUser->id,
            'name' => 'Zephyr Global Conglomerate',
            'email' => 'legal@zephyrglobal.test',
            'status' => 'active',
            'category' => 'corporate',
            'primary_attorney_id' => $attorney->id,
        ]);

        // 2. Matter & 4. Party (opposing party)
        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'lead_attorney_id' => $attorney->id,
            'case_number' => 'MAT-2026-ZEPHYR-01',
            'title' => 'Zephyr Global vs Horizon Syndicate Litigation',
            'opposing_party' => 'Horizon Syndicate Litigants',
            'opposing_counsel' => 'Senior Counsel Verma',
            'status' => 'Open',
            'court_name' => 'High Court of Delhi',
            'filing_date' => now()->toDateString(),
        ]);

        // 3. Document
        $document = Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'user_id' => $staff->id,
            'title' => 'Zephyr Commercial Injunction Plaint',
            'filename' => 'zephyr_injunction_petition.pdf',
            'file_size' => 2048,
            'mime_type' => 'application/pdf',
            'category' => 'Pleading',
            'document_type' => 'Pleading',
            'version' => 1,
            'tags' => ['zephyr_tag', 'litigation'],
            'is_client_visible' => true,
        ]);

        // 5. Message
        $thread = MessageThread::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'subject' => 'Zephyr Settlement Deliberations',
            'thread_type' => 'client_communication',
            'created_by' => $staff->id,
            'last_message_at' => now(),
        ]);

        $message = Message::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
            'sender_id' => $staff->id,
            'recipient_id' => $clientUser->id,
            'subject' => 'Zephyr Settlement Deliberations',
            'body' => 'Notice regarding Zephyr interlocutory injunction arguments next week.',
            'is_internal' => false,
            'sent_at' => now(),
        ]);

        // 6. Task
        $task = Task::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'related_client_id' => $client->id,
            'assigned_to' => $staff->id,
            'created_by' => $staff->id,
            'title' => 'Prepare Zephyr Chronology and Docket Record',
            'description' => 'Synthesize evidence records for Zephyr hearing.',
            'priority' => Task::PRIORITY_HIGH,
            'status' => Task::STATUS_IN_PROGRESS,
            'due_date' => now()->addDays(5)->toDateString(),
            'tags' => ['zephyr_tag', 'evidence'],
            'related_party_name' => 'Witness: Dr. K. Zephyr',
        ]);

        // 7. Note
        $note = CaseNote::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'user_id' => $staff->id,
            'title' => 'Zephyr Strategy Memo',
            'body' => 'Confidential strategy on Zephyr contractual remedies and jurisdiction.',
            'type' => 'internal',
        ]);

        // 8. Calendar Event
        $event = Event::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'user_id' => $staff->id,
            'title' => 'Zephyr Preliminary Hearing on Injunction',
            'event_type' => 'Court Hearing',
            'start_time' => now()->addDays(3),
            'location' => 'Courtroom 4, High Court',
        ]);

        $response = $this->actingAs($staff)->get(route('search', ['q' => 'Zephyr']));

        $response->assertStatus(200);
        $response->assertSee('Global Practice Search Engine');
        $response->assertSee('Zephyr Global Conglomerate'); // Client
        $response->assertSee('MAT-2026-ZEPHYR-01'); // Matter
        $response->assertSee('Zephyr Commercial Injunction Plaint'); // Document
        $response->assertSee('Horizon Syndicate Litigants'); // Party
        $response->assertSee('Zephyr Settlement Deliberations'); // Message
        $response->assertSee('Prepare Zephyr Chronology and Docket Record'); // Task
        $response->assertSee('Zephyr Strategy Memo'); // Note
        $response->assertSee('Zephyr Preliminary Hearing on Injunction'); // Calendar Event
    }

    /**
     * Test search filters: matter_number, category, status, tag, attorney, date.
     */
    public function test_precision_filters_work_accurately(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);
        $attorney = User::factory()->create(['firm_id' => $firm->id, 'role' => 'attorney']);

        $client = Client::create([
            'firm_id' => $firm->id,
            'name' => 'Apex Tech Solutions',
            'email' => 'apex@tech.test',
            'status' => 'active',
            'category' => 'corporate',
            'primary_attorney_id' => $attorney->id,
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'lead_attorney_id' => $attorney->id,
            'case_number' => 'MAT-SPEC-2026-77',
            'title' => 'Apex Patent Infringement Action',
            'status' => 'Open',
        ]);

        $doc = Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'user_id' => $staff->id,
            'title' => 'Patent Specification Exhibit A',
            'filename' => 'patent_spec.pdf',
            'category' => 'Evidence',
            'document_type' => 'Evidence',
            'tags' => ['patent_tag', 'priority_review'],
            'is_client_visible' => true,
        ]);

        $task = Task::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'related_client_id' => $client->id,
            'assigned_to' => $attorney->id,
            'created_by' => $staff->id,
            'title' => 'Review Prior Art References',
            'status' => Task::STATUS_WAITING,
            'priority' => Task::PRIORITY_URGENT,
            'due_date' => '2026-10-15',
            'tags' => ['patent_tag'],
        ]);

        // Filter by matter_number
        $resMatterNum = $this->actingAs($staff)->get(route('search', ['matter_number' => 'MAT-SPEC-2026-77']));
        $resMatterNum->assertStatus(200);
        $resMatterNum->assertSee('Apex Patent Infringement Action');

        // Filter by category / doc type
        $resDocType = $this->actingAs($staff)->get(route('search', ['document_type' => 'Evidence']));
        $resDocType->assertStatus(200);
        $resDocType->assertSee('Patent Specification Exhibit A');

        // Filter by document title
        $resDoc = $this->actingAs($staff)->get(route('search', ['document' => 'Patent Specification']));
        $resDoc->assertStatus(200);
        $resDoc->assertSee('Patent Specification Exhibit A');

        // Filter by tag
        $resTag = $this->actingAs($staff)->get(route('search', ['tag' => 'patent_tag']));
        $resTag->assertStatus(200);
        $resTag->assertSee('Review Prior Art References');

        // Filter by attorney
        $resAttorney = $this->actingAs($staff)->get(route('search', ['attorney' => $attorney->id]));
        $resAttorney->assertStatus(200);
        $resAttorney->assertSee('Apex Tech Solutions');
        $resAttorney->assertSee('Apex Patent Infringement Action');

        // Filter by status
        $resStatus = $this->actingAs($staff)->get(route('search', ['status' => Task::STATUS_WAITING]));
        $resStatus->assertStatus(200);
        $resStatus->assertSee('Review Prior Art References');

        // Filter by date
        $resDate = $this->actingAs($staff)->get(route('search', ['date' => '2026-10-15']));
        $resDate->assertStatus(200);
        $resDate->assertSee('Review Prior Art References');
    }

    /**
     * Test paralegal filter and party search across opposing parties and counsel.
     */
    public function test_paralegal_filter_and_party_search(): void
    {
        $firm = Firm::first() ?: Firm::create(['name' => 'Chambers Paralegal Test', 'slug' => 'chambers-paralegal-test', 'email' => 'para@test.com']);
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);
        $paralegal = User::factory()->create(['firm_id' => $firm->id, 'role' => 'paralegal', 'name' => 'Kavita Paralegal']);

        $client = Client::create([
            'firm_id' => $firm->id,
            'name' => 'Tata Steel Europe',
            'status' => 'active',
            'assigned_paralegal_id' => $paralegal->id,
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'assigned_paralegal_id' => $paralegal->id,
            'case_number' => 'MAT-TATA-2026',
            'title' => 'Tata Steel vs Arcelor Mittal Group',
            'opposing_party' => 'Arcelor Mittal Group',
            'opposing_counsel' => 'Senior Advocate Harish Salve',
            'status' => 'Open',
        ]);

        // Filter by paralegal
        $resPara = $this->actingAs($staff)->get(route('search', ['paralegal' => $paralegal->id]));
        $resPara->assertStatus(200);
        $resPara->assertSee('Tata Steel Europe');
        $resPara->assertSee('MAT-TATA-2026');

        // Search opposing counsel in parties
        $resCounsel = $this->actingAs($staff)->get(route('search', ['q' => 'Harish Salve']));
        $resCounsel->assertStatus(200);
        $resCounsel->assertSee('Senior Advocate Harish Salve');
        $resCounsel->assertSee('Opposing Counsel');
    }

    /**
     * Test landing on global search without a query displays index overview without redirect.
     */
    public function test_landing_on_search_without_query_displays_index(): void
    {
        $firm = Firm::first();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);

        $response = $this->actingAs($staff)->get(route('search'));
        $response->assertStatus(200);
        $response->assertSee('Global Practice Search Engine');
        $response->assertSee('Indexed Matches');
    }

    /**
     * CRITICAL SECURITY RULE:
     * Cross-firm Multi-tenancy Isolation:
     * Staff from Firm A must NEVER discover records belonging to Firm B through search.
     */
    public function test_cross_firm_multi_tenant_isolation_staff_cannot_search_other_firm_records(): void
    {
        $firmA = Firm::create(['name' => 'Chambers Alpha', 'slug' => 'chambers-alpha', 'email' => 'alpha@chambers.test', 'status' => 'active']);
        $firmB = Firm::create(['name' => 'Chambers Beta', 'slug' => 'chambers-beta', 'email' => 'beta@chambers.test', 'status' => 'active']);

        $staffA = User::factory()->create(['firm_id' => $firmA->id, 'role' => 'advocate']);
        $staffB = User::factory()->create(['firm_id' => $firmB->id, 'role' => 'advocate']);

        // Firm B Secret Records
        $clientB = Client::create([
            'firm_id' => $firmB->id,
            'name' => 'TopSecret Corp Beta',
            'email' => 'secret@beta.test',
            'status' => 'active',
        ]);

        $matterB = Matter::create([
            'firm_id' => $firmB->id,
            'client_id' => $clientB->id,
            'case_number' => 'SECRET-BETA-999',
            'title' => 'Beta Classified Corporate Merger',
            'opposing_party' => 'Rival Hostile Acquirer',
            'status' => 'Open',
        ]);

        $docB = Document::create([
            'firm_id' => $firmB->id,
            'matter_id' => $matterB->id,
            'client_id' => $clientB->id,
            'user_id' => $staffB->id,
            'title' => 'Classified Merger Term Sheet',
            'filename' => 'secret_merger.pdf',
            'category' => 'Contract',
            'is_client_visible' => false,
        ]);

        $noteB = CaseNote::create([
            'firm_id' => $firmB->id,
            'matter_id' => $matterB->id,
            'user_id' => $staffB->id,
            'title' => 'Secret Anti-Trust Vulnerability Memo',
            'body' => 'High risk regulatory exposure identified.',
            'type' => 'internal',
        ]);

        // Staff from Firm A searches for Firm B's secret keyword
        $response = $this->actingAs($staffA)->get(route('search', ['q' => 'Secret']));

        $response->assertStatus(200);
        $response->assertDontSee('TopSecret Corp Beta');
        $response->assertDontSee('SECRET-BETA-999');
        $response->assertDontSee('Beta Classified Corporate Merger');
        $response->assertDontSee('Rival Hostile Acquirer');
        $response->assertDontSee('Classified Merger Term Sheet');
        $response->assertDontSee('Secret Anti-Trust Vulnerability Memo');
    }

    /**
     * CRITICAL SECURITY RULE:
     * Client User Isolation:
     * A client user can ONLY search their own records and matters.
     * They must NEVER discover other clients or other clients' matters.
     */
    public function test_client_user_cannot_discover_other_clients_or_other_matters(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();

        // Client A
        $userA = User::factory()->create(['firm_id' => $firm->id, 'role' => 'client']);
        $clientA = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $userA->id,
            'name' => 'Alice Representation Services',
            'email' => $userA->email,
            'status' => 'active',
        ]);
        $matterA = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $clientA->id,
            'case_number' => 'ALICE-MAT-01',
            'title' => 'Alice Property Settlement',
            'status' => 'Open',
        ]);

        // Client B (Separate party in same firm)
        $userB = User::factory()->create(['firm_id' => $firm->id, 'role' => 'client']);
        $clientB = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $userB->id,
            'name' => 'Bob Private Defense Retainer',
            'email' => $userB->email,
            'status' => 'active',
        ]);
        $matterB = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $clientB->id,
            'case_number' => 'BOB-DEF-99',
            'title' => 'Bob Criminal Defense Investigation',
            'opposing_party' => 'State Enforcement Directorate',
            'status' => 'Open',
        ]);

        // Client A searches for "Bob"
        $resBob = $this->actingAs($userA)->get(route('search', ['q' => 'Bob']));
        $resBob->assertStatus(200);
        $resBob->assertDontSee('Bob Private Defense Retainer');
        $resBob->assertDontSee('BOB-DEF-99');
        $resBob->assertDontSee('Bob Criminal Defense Investigation');
        $resBob->assertDontSee('State Enforcement Directorate');

        // Client A searches for "Alice" -> should see their own records
        $resAlice = $this->actingAs($userA)->get(route('search', ['q' => 'Alice']));
        $resAlice->assertStatus(200);
        $resAlice->assertSee('Alice Representation Services');
        $resAlice->assertSee('ALICE-MAT-01');
        $resAlice->assertSee('Alice Property Settlement');
    }

    /**
     * CRITICAL SECURITY RULE:
     * Client User Data Classification Guard:
     * Even on their own matter, clients CANNOT discover internal documents,
     * internal lawyer strategy notes, or internal communication threads!
     */
    public function test_client_user_cannot_discover_internal_documents_notes_or_messages(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);

        $clientUser = User::factory()->create(['firm_id' => $firm->id, 'role' => 'client']);
        $client = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientUser->id,
            'name' => 'Nexus BioTech Client',
            'email' => $clientUser->email,
            'status' => 'active',
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'NEX-2026-001',
            'title' => 'Nexus BioTech Commercial Advisory',
            'status' => 'Open',
        ]);

        // Client-Visible Document
        $publicDoc = Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'user_id' => $staff->id,
            'title' => 'Nexus Public Court Filing Copy',
            'filename' => 'nexus_public_filing.pdf',
            'category' => 'Court Filing',
            'is_client_visible' => true,
            'visibility' => 'client_visible',
        ]);

        // Strictly Internal Document (Internal Draft / Memo)
        $internalDoc = Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'user_id' => $staff->id,
            'title' => 'Nexus Internal Counsel Risk Assessment Memo',
            'filename' => 'nexus_internal_risk.pdf',
            'category' => 'Legal Research',
            'is_client_visible' => false,
            'visibility' => 'internal_only',
        ]);

        // Client-Visible Note
        $publicNote = CaseNote::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'user_id' => $staff->id,
            'title' => 'Nexus Client Next Steps Update',
            'body' => 'Filing completed at registry. Please review next steps.',
            'type' => 'client_visible',
        ]);

        // Strictly Internal Note (Internal Attorney Work Product)
        $internalNote = CaseNote::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'user_id' => $staff->id,
            'title' => 'Nexus Secret Strategy On Judge Bench Bias',
            'body' => 'Do not reveal to client: severe procedural weakness on jurisdiction.',
            'type' => 'internal',
        ]);

        // Message Thread
        $thread = MessageThread::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'subject' => 'Nexus Case Matters',
            'thread_type' => 'client_communication',
            'created_by' => $staff->id,
        ]);

        // Client-Visible Message
        $clientMsg = Message::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
            'sender_id' => $staff->id,
            'recipient_id' => $clientUser->id,
            'subject' => 'Nexus Client Advisory Note',
            'body' => 'Dear Nexus client, here is the approved schedule.',
            'is_internal' => false,
        ]);

        // Strictly Internal Lawyer Discussion Message
        $internalMsg = Message::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'thread_id' => $thread->id,
            'sender_id' => $staff->id,
            'recipient_id' => $staff->id,
            'subject' => 'Nexus Chambers Internal Deliberation',
            'body' => 'Colleagues only: we need to verify the billing dispute before informing Nexus.',
            'is_internal' => true,
        ]);

        // Client searches for "Nexus"
        $response = $this->actingAs($clientUser)->get(route('search', ['q' => 'Nexus']));

        $response->assertStatus(200);

        // Client-visible records MUST be found:
        $response->assertSee('Nexus Public Court Filing Copy');
        $response->assertSee('Nexus Client Next Steps Update');
        $response->assertSee('Nexus Client Advisory Note');

        // Internal records MUST NEVER be discoverable or seen by client:
        $response->assertDontSee('Nexus Internal Counsel Risk Assessment Memo');
        $response->assertDontSee('nexus_internal_risk.pdf');
        $response->assertDontSee('Nexus Secret Strategy On Judge Bench Bias');
        $response->assertDontSee('severe procedural weakness on jurisdiction');
        $response->assertDontSee('Nexus Chambers Internal Deliberation');
    }

    /**
     * Test entity tab navigation preserves query parameters and renders entity slices.
     */
    public function test_entity_tabs_filtering(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staff = User::factory()->create(['firm_id' => $firm->id, 'role' => 'advocate']);

        $client = Client::create([
            'firm_id' => $firm->id,
            'name' => 'Delta Aerospace',
            'status' => 'active',
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'DELTA-AIR-101',
            'title' => 'Delta Aerospace Regulatory Dispute',
            'status' => 'Open',
        ]);

        // Request only 'matters' tab
        $resMatters = $this->actingAs($staff)->get(route('search', ['q' => 'Delta', 'type' => 'matters']));
        $resMatters->assertStatus(200);
        $resMatters->assertSee('Delta Aerospace Regulatory Dispute');

        // Request only 'clients' tab
        $resClients = $this->actingAs($staff)->get(route('search', ['q' => 'Delta', 'type' => 'clients']));
        $resClients->assertStatus(200);
        $resClients->assertSee('Delta Aerospace');
    }
}
