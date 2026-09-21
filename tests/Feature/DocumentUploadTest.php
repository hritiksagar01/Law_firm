<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test uploading a standard legal filing (500 KB) with SHA-256 integrity.
     */
    public function test_lawyer_can_upload_legal_document_with_sha256(): void
    {
        Storage::fake('local');
        $user = User::where('role', 'partner')->first();
        $matter = Matter::where('firm_id', $user->firm_id)->first();

        $content = '%PDF-1.4 TEST PLEADING '.str_repeat('A', 500 * 1024);
        $expectedSha = hash('sha256', $content);
        $file = UploadedFile::fake()->createWithContent('verified_pleading.pdf', $content);

        $response = $this->actingAs($user)->post(route('documents.upload'), [
            'matter_id' => $matter->id,
            'title' => 'Plaintiff Affidavit in Support of Injunction',
            'category' => 'Pleading',
            'privilege' => 'Attorney-Client',
            'file' => $file,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $doc = Document::where('title', 'Plaintiff Affidavit in Support of Injunction')->first();
        $this->assertNotNull($doc);
        $this->assertEquals($expectedSha, $doc->sha256);
        $this->assertEquals('verified_pleading.pdf', $doc->filename);
        $this->assertEquals($user->firm_id, $doc->firm_id);
    }

    /**
     * Test uploading a multi-megabyte bundle (5 MB) that previously exceeded default 2MB limits.
     */
    public function test_uploading_multi_megabyte_evidence_bundle(): void
    {
        Storage::fake('local');
        $user = User::where('role', 'partner')->first();
        $matter = Matter::where('firm_id', $user->firm_id)->first();

        $content = '%PDF-1.4 HEAVY EVIDENCE BUNDLE '.str_repeat('B', 5 * 1024 * 1024);
        $expectedSha = hash('sha256', $content);
        $file = UploadedFile::fake()->createWithContent('heavy_evidence_bundle.pdf', $content);

        $response = $this->actingAs($user)->post(route('documents.upload'), [
            'matter_id' => $matter->id,
            'title' => 'Exhibit Volume III - High Court Evidence Record',
            'category' => 'Exhibit',
            'privilege' => 'Confidential',
            'file' => $file,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $doc = Document::where('title', 'Exhibit Volume III - High Court Evidence Record')->first();
        $this->assertNotNull($doc);
        $this->assertEquals($expectedSha, $doc->sha256);
    }
}
