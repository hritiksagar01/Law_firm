<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Document;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class DocumentIsolationAndDownloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * 1. Test that downloading any document returns a 100% valid PDF with application/pdf header.
     */
    public function test_document_download_returns_valid_pdf(): void
    {
        $partner = User::where('email', 'rajesh@sharmalegal.in')->first();
        $doc = Document::where('firm_id', $partner->firm_id)->first();
        $this->assertNotNull($doc);

        $response = $this->actingAs($partner)->get(route('documents.download', $doc->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        $content = $response->streamedContent();
        $this->assertStringStartsWith('%PDF-1.', $content);
        $this->assertStringContainsString('%%EOF', $content);
    }

    /**
     * 2. Test dynamic PDF fallback when a physical file is removed from disk.
     */
    public function test_download_fallback_generates_valid_pdf_when_file_missing(): void
    {
        $partner = User::where('email', 'rajesh@sharmalegal.in')->first();
        $doc = Document::create([
            'firm_id' => $partner->firm_id,
            'matter_id' => Matter::where('firm_id', $partner->firm_id)->first()->id,
            'user_id' => $partner->id,
            'title' => 'Missing File Dynamic Fallback Pleading',
            'filename' => 'missing_pleading.pdf',
            'file_path' => 'documents/non_existent_file_'.uniqid().'.pdf',
            'file_size' => 1000,
            'mime_type' => 'application/pdf',
            'sha256' => hash('sha256', 'fallback'),
            'category' => 'Pleadings',
            'privilege' => 'Attorney-Client',
            'is_client_visible' => true,
            'version' => 1,
        ]);

        $response = $this->actingAs($partner)->get(route('documents.download', $doc->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-1.4', $content);
        $this->assertStringContainsString('%%EOF', $content);
        $this->assertStringContainsString('Missing File Dynamic Fallback Pleading', $content);
    }

    /**
     * 3. Test Cross-Firm Isolation: Firm 2 lawyer cannot download Firm 1 documents (403 Forbidden).
     */
    public function test_cross_firm_document_access_is_forbidden(): void
    {
        $firm1Doc = Document::where('firm_id', 1)->first();
        $firm2Lawyer = User::where('email', 'sanjeev@sjmlegal.in')->first(); // Firm 2 / SJM Legal
        $this->assertNotNull($firm1Doc);
        $this->assertNotNull($firm2Lawyer);
        $this->assertNotEquals($firm1Doc->firm_id, $firm2Lawyer->firm_id);

        $response = $this->actingAs($firm2Lawyer)->get(route('documents.download', $firm1Doc->id));
        $response->assertStatus(403);
    }

    /**
     * 4. Test Cross-Client Isolation: Client B cannot download Client A's documents (403 Forbidden).
     */
    public function test_cross_client_document_access_in_same_firm_is_forbidden(): void
    {
        $firm = Firm::first();

        // Client A
        $clientAUser = User::factory()->create(['role' => 'client', 'firm_id' => $firm->id]);
        $clientA = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientAUser->id,
            'name' => 'Client A Corp',
            'email' => $clientAUser->email,
            'type' => 'corporate',
        ]);
        $matterA = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $clientA->id,
            'case_number' => 'TEST-A-001',
            'title' => 'Matter for Client A',
            'stage' => 'Discovery',
        ]);
        $docA = Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterA->id,
            'user_id' => $clientAUser->id,
            'title' => 'Client A Confidential Trade Secrets',
            'filename' => 'client_a_secrets.pdf',
            'file_path' => 'documents/test_client_a.pdf',
            'file_size' => 1200,
            'mime_type' => 'application/pdf',
            'sha256' => hash('sha256', 'trade secrets'),
            'category' => 'Discovery',
            'privilege' => 'Confidential',
            'is_client_visible' => true,
            'version' => 1,
        ]);

        // Client B (in same firm)
        $clientBUser = User::factory()->create(['role' => 'client', 'firm_id' => $firm->id]);
        $clientB = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientBUser->id,
            'name' => 'Client B Corp',
            'email' => $clientBUser->email,
            'type' => 'corporate',
        ]);

        // Client B attempts to download Client A's document via client portal
        $response = $this->actingAs($clientBUser)->get(route('portal.documents.download', $docA->id));
        $response->assertStatus(403);
    }

    /**
     * 5. Test Client Visibility Filter: Client cannot access documents marked internal (is_client_visible = false).
     */
    public function test_client_cannot_download_internal_chambers_documents(): void
    {
        $firm = Firm::first();
        $clientUser = User::factory()->create(['role' => 'client', 'firm_id' => $firm->id]);
        $client = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientUser->id,
            'name' => 'Target Client',
            'email' => $clientUser->email,
            'type' => 'corporate',
        ]);
        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'TEST-VIS-001',
            'title' => 'Litigation Strategy Test',
            'stage' => 'Pleadings',
        ]);

        $internalDoc = Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'user_id' => User::where('role', 'partner')->first()->id,
            'title' => 'Internal Legal Strategy & Weakness Assessment',
            'filename' => 'internal_strategy.pdf',
            'file_path' => 'documents/internal_strategy.pdf',
            'file_size' => 1200,
            'mime_type' => 'application/pdf',
            'sha256' => hash('sha256', 'strategy'),
            'category' => 'Work Product',
            'privilege' => 'Work Product',
            'is_client_visible' => false, // Strictly chambers internal!
            'version' => 1,
        ]);

        // Client attempts to download internal document of their own case
        $response = $this->actingAs($clientUser)->get(route('portal.documents.download', $internalDoc->id));
        $response->assertStatus(403);

        // Verify document is also excluded from portal documents list
        $listResponse = $this->actingAs($clientUser)->get(route('portal.documents.index'));
        $listResponse->assertStatus(200);
        $listResponse->assertDontSee('Internal Legal Strategy & Weakness Assessment');
    }

    /**
     * 6. Test Lawyer Assignment Isolation: Associate not assigned to matter cannot access its dossier.
     */
    public function test_unassigned_associate_cannot_access_matter_dossier(): void
    {
        $firm = Firm::first();
        $partner = User::where('role', 'partner')->where('firm_id', $firm->id)->first();
        $client = Client::where('firm_id', $firm->id)->first();

        // Create an exclusive matter assigned only to Partner
        $privateMatter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'EXCLUSIVE-001',
            'title' => 'Confidential Merger Review',
            'stage' => 'Pre-Trial',
            'lead_attorney_id' => $partner->id,
        ]);

        // Create an unassigned associate in same firm
        $associate = User::factory()->create([
            'firm_id' => $firm->id,
            'role' => 'associate',
            'name' => 'Adv. Unassigned Junior',
        ]);

        // Unassigned associate attempts to view exclusive matter
        $response = $this->actingAs($associate)->get(route('matters.show', $privateMatter->id));
        $response->assertStatus(403);
    }

    /**
     * 7. Test Clean Slate command purges transactional data while preserving firms and staff.
     */
    public function test_clean_data_command_purges_sample_data_safely(): void
    {
        $this->assertGreaterThan(0, Matter::count());
        $this->assertGreaterThan(0, Document::count());
        $this->assertGreaterThan(0, Client::count());

        $this->artisan('legal:clean-data', ['--force' => true])->assertSuccessful();

        $this->assertEquals(0, Matter::count());
        $this->assertEquals(0, Document::count());
        $this->assertEquals(0, Client::count());

        // Firms and staff users remain intact
        $this->assertGreaterThan(0, Firm::count());
        $this->assertNotNull(User::where('role', 'superadmin')->first());
        $this->assertNotNull(User::where('role', 'partner')->first());
    }
}
