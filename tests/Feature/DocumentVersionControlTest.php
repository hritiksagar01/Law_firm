<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Matter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVersionControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test Document Version Control section renders below Verified Document Repository
     * with the requested sequence, headers, and relationship indicators.
     */
    public function test_document_version_control_section_is_rendered(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($lawyer)->get(route('documents.index'));

        $response->assertStatus(200);

        // Section header and descriptive subtitle
        $response->assertSee('Document Version Control');
        $response->assertSee('Audit trail, revision lineage, change descriptions, and lifecycle tracking');

        // Typical sequence indicators
        $response->assertSee('Typical Sequence:');
        $response->assertSee('Draft v1');
        $response->assertSee('Draft v2');
        $response->assertSee('Review v3');
        $response->assertSee('Final');
        $response->assertSee('Executed');

        // Table headers matching prompt requirements
        $response->assertSee('Version');
        $response->assertSee('Document ID');
        $response->assertSee('Stored File');
        $response->assertSee('Uploaded By / Date');
        $response->assertSee('Change Description');
        $response->assertSee('Version Status &amp; Relationship', false);

        // Upload New Version modal trigger button
        $response->assertSee('Upload New Version');
    }

    /**
     * Test lawyer can upload a new version with status, change description, and link to previous version.
     */
    public function test_lawyer_can_upload_new_version_with_status_and_change_description(): void
    {
        Storage::fake('local');

        $lawyer = User::where('role', 'partner')->firstOrFail();
        $matter = Matter::where('firm_id', $lawyer->firm_id)->firstOrFail();
        $client = Client::where('id', $matter->client_id)->firstOrFail();

        $doc = Document::create([
            'firm_id' => $lawyer->firm_id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'user_id' => $lawyer->id,
            'document_number' => 'DOC-2026-9901',
            'title' => 'Joint Venture Agreement Draft',
            'filename' => 'joint_venture_v1.pdf',
            'file_path' => 'documents/jv_v1.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'sha256' => hash('sha256', 'v1-content'),
            'version' => 1,
            'document_status' => 'draft',
        ]);

        $v1 = DocumentVersion::create([
            'document_id' => $doc->id,
            'version_number' => 1,
            'filename' => 'joint_venture_v1.pdf',
            'version_status' => 'Draft',
            'file_path' => $doc->file_path,
            'file_size' => 102400,
            'file_hash' => $doc->sha256,
            'uploaded_by' => $lawyer->id,
            'change_summary' => 'Initial joint venture agreement draft',
            'change_description' => 'Initial joint venture agreement draft',
            'previous_version_id' => null,
        ]);

        $newFile = UploadedFile::fake()->create('joint_venture_v2_reviewed.pdf', 300, 'application/pdf');

        $response = $this->actingAs($lawyer)->post(route('documents.versions.upload'), [
            'document_id' => $doc->id,
            'version_status' => 'Review',
            'change_description' => 'Revised dispute resolution clause 21 per partner review',
            'file' => $newFile,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Verify version 2 record exists with correct relationship
        $this->assertDatabaseHas('document_versions', [
            'document_id' => $doc->id,
            'version_number' => 2,
            'filename' => 'joint_venture_v2_reviewed.pdf',
            'version_status' => 'Review',
            'change_description' => 'Revised dispute resolution clause 21 per partner review',
            'previous_version_id' => $v1->id,
        ]);

        // Verify parent document version incremented
        $doc->refresh();
        $this->assertEquals(2, $doc->version);
        $this->assertEquals('joint_venture_v2_reviewed.pdf', $doc->filename);
    }

    /**
     * Test lawyer can download a specific stored version file.
     */
    public function test_lawyer_can_download_stored_document_version(): void
    {
        Storage::fake('local');

        $lawyer = User::where('role', 'partner')->firstOrFail();
        $matter = Matter::where('firm_id', $lawyer->firm_id)->firstOrFail();

        $doc = Document::create([
            'firm_id' => $lawyer->firm_id,
            'matter_id' => $matter->id,
            'client_id' => $matter->client_id,
            'user_id' => $lawyer->id,
            'title' => 'Arbitration Claim Statement',
            'filename' => 'arbitration_v1.pdf',
            'file_path' => 'documents/arbitration_v1.pdf',
            'file_size' => 50000,
            'mime_type' => 'application/pdf',
            'sha256' => hash('sha256', 'sample'),
            'version' => 1,
        ]);

        $version = DocumentVersion::create([
            'document_id' => $doc->id,
            'version_number' => 1,
            'filename' => 'arbitration_v1.pdf',
            'version_status' => 'Draft',
            'file_path' => $doc->file_path,
            'file_size' => 50000,
            'file_hash' => $doc->sha256,
            'uploaded_by' => $lawyer->id,
            'change_description' => 'Initial arbitration claim draft',
            'previous_version_id' => null,
        ]);

        Storage::disk('local')->put($version->file_path, 'sample pdf binary content');

        $response = $this->actingAs($lawyer)->get(route('documents.versions.download', [
            'document' => $doc->id,
            'version' => $version->id,
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('arbitration_v1.pdf', (string) $response->headers->get('content-disposition'));
    }
}
