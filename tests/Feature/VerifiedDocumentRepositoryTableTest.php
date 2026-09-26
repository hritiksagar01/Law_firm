<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VerifiedDocumentRepositoryTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test Verified Document Repository table renders the requested 10 headers:
     * Doc ID, File Name, Doc Title, Type, Matter, Client, Tag, Confidentiality, Visibility, Doc Status
     * and removes Privilege Assertion, Size, and SHA-256 Checksum.
     */
    public function test_verified_document_repository_renders_requested_columns_and_removes_old_columns(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($lawyer)->get(route('documents.index'));

        $response->assertStatus(200);

        // Assert presence of the 10 requested columns
        $response->assertSee('Doc ID');
        $response->assertSee('File Name');
        $response->assertSee('Doc Title');
        $response->assertSee('Type');
        $response->assertSee('Matter');
        $response->assertSee('Client');
        $response->assertSee('Tag');
        $response->assertSee('Confidentiality');
        $response->assertSee('Visibility');
        $response->assertSee('Doc Status');

        // Assert removed columns are NOT in the table headers
        $response->assertDontSee('<th class="py-3 px-4 font-medium">Privilege Assertion</th>', false);
        $response->assertDontSee('<th class="py-3 px-4 font-medium">Size</th>', false);
        $response->assertDontSee('<th class="py-3 px-4 font-medium">SHA-256 Checksum</th>', false);
    }

    /**
     * Test Verified Document Repository table renders document particulars accurately.
     */
    public function test_verified_document_repository_displays_document_data_correctly(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();
        $matter = Matter::where('firm_id', $lawyer->firm_id)->firstOrFail();
        $client = Client::where('id', $matter->client_id)->firstOrFail();

        $doc = Document::create([
            'firm_id' => $lawyer->firm_id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'user_id' => $lawyer->id,
            'document_number' => 'DOC-2026-7788',
            'title' => 'Written Arguments on Interim Mandatory Relief',
            'filename' => 'interim_mandatory_arguments.pdf',
            'file_path' => 'documents/test_sample.pdf',
            'file_size' => 125000,
            'mime_type' => 'application/pdf',
            'sha256' => hash('sha256', 'sample_content'),
            'category' => 'Pleading',
            'document_type' => 'Pleading',
            'privilege' => 'Confidential',
            'classification' => 'confidential',
            'visibility' => 'client_visible',
            'document_status' => 'final',
            'tags' => ['injunction', 'urgent-hearing'],
            'is_client_visible' => true,
            'version' => 1,
        ]);

        $response = $this->actingAs($lawyer)->get(route('documents.index'));

        $response->assertStatus(200);

        // Check document data appears in the repository
        $response->assertSee('DOC-2026-7788');
        $response->assertSee('interim_mandatory_arguments.pdf');
        $response->assertSee('Written Arguments on Interim Mandatory Relief');
        $response->assertSee('Pleading');
        $response->assertSee($matter->case_number);
        $response->assertSee($client->name);
        $response->assertSee('#injunction');
        $response->assertSee('#urgent-hearing');
        $response->assertSee('Confidential');
        $response->assertSee('Client Visible');
        $response->assertSee('Final');
    }

    /**
     * Test uploading a filing into the vault assigns document number, client, tags, and classification.
     */
    public function test_can_upload_document_with_tags_classification_and_status(): void
    {
        Storage::fake('local');

        $lawyer = User::where('role', 'partner')->firstOrFail();
        $matter = Matter::where('firm_id', $lawyer->firm_id)->firstOrFail();

        $file = UploadedFile::fake()->create('replication_affidavit.pdf', 500, 'application/pdf');

        $postData = [
            'matter_id' => $matter->id,
            'title' => 'Replication to Written Statement',
            'category' => 'Pleading',
            'privilege' => 'Confidential',
            'classification' => 'confidential',
            'document_status' => 'final',
            'tags' => 'replication, rejoinder, civil-suit',
            'is_client_visible' => '1',
            'file' => $file,
        ];

        $response = $this->actingAs($lawyer)->post(route('documents.upload'), $postData);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('documents', [
            'matter_id' => $matter->id,
            'client_id' => $matter->client_id,
            'title' => 'Replication to Written Statement',
            'filename' => 'replication_affidavit.pdf',
            'classification' => 'confidential',
            'document_status' => 'final',
            'is_client_visible' => true,
        ]);

        $uploadedDoc = Document::where('title', 'Replication to Written Statement')->firstOrFail();
        $this->assertNotNull($uploadedDoc->document_number);
        $this->assertStringStartsWith('DOC-', $uploadedDoc->document_number);
        $this->assertContains('replication', $uploadedDoc->tags);
        $this->assertContains('civil-suit', $uploadedDoc->tags);
    }
}
