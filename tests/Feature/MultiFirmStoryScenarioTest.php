<?php

namespace Tests\Feature;

use App\Mail\AppointmentCancelledMail;
use App\Mail\AppointmentScheduledMail;
use App\Mail\DocumentRequestedMail;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\Message;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiFirmStoryScenarioTest extends TestCase
{
    protected Firm $firm1;

    protected Firm $firm2;

    protected Firm $firm3;

    protected User $lawyer1Partner;

    protected User $lawyer1Associate;

    protected User $client1User;

    protected Client $client1;

    protected Matter $matter1;

    protected User $lawyer2Partner;

    protected User $lawyer2Associate;

    protected User $client2User;

    protected Client $client2;

    protected Matter $matter2;

    protected User $lawyer3Partner;

    protected User $lawyer3Associate;

    protected User $client3User;

    protected Client $client3;

    protected Matter $matter3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);

        $this->setupThreeFirmsEnvironment();
    }

    /**
     * Setup 3 complete, realistic law firms with multiple lawyers and clients.
     */
    protected function setupThreeFirmsEnvironment(): void
    {
        // Firm 1: Vennamraj Associates
        $this->firm1 = Firm::where('slug', 'vennamraj-associates')->firstOrFail();
        $this->lawyer1Partner = User::where('firm_id', $this->firm1->id)->where('role', 'partner')->firstOrFail();
        $this->lawyer1Associate = User::where('firm_id', $this->firm1->id)->where('role', 'associate')->firstOrFail();
        $this->client1User = User::where('firm_id', $this->firm1->id)->where('role', 'client')->firstOrFail();
        $this->client1 = Client::where('firm_id', $this->firm1->id)->firstOrFail();
        $this->matter1 = Matter::where('firm_id', $this->firm1->id)->firstOrFail();

        // Firm 2: SJM Legal Chambers
        $this->firm2 = Firm::where('slug', 'sjm-legal-chambers')->firstOrFail();
        $this->lawyer2Partner = User::where('firm_id', $this->firm2->id)->where('role', 'partner')->firstOrFail();
        $this->lawyer2Associate = User::where('firm_id', $this->firm2->id)->where('role', 'associate')->firstOrFail();
        $this->client2User = User::where('firm_id', $this->firm2->id)->where('role', 'client')->firstOrFail();
        $this->client2 = Client::where('firm_id', $this->firm2->id)->firstOrFail();
        $this->matter2 = Matter::where('firm_id', $this->firm2->id)->firstOrFail();

        // Firm 3: Lex Juris & Partners (Created dynamically for full 3-firm coverage)
        $this->firm3 = Firm::firstOrCreate(
            ['slug' => 'lex-juris-partners'],
            [
                'name' => 'Lex Juris & Partners LLP',
                'email' => 'contact@lexjuris.com',
                'phone' => '+91 (11) 4567-8900',
                'address' => 'Barakhamba Road, Connaught Place, New Delhi',
                'currency' => 'INR',
                'default_hourly_rate' => 6000.00,
            ]
        );

        $this->lawyer3Partner = User::firstOrCreate(
            ['email' => 'sarah@lexjuris.com'],
            [
                'firm_id' => $this->firm3->id,
                'name' => 'Adv. Sarah Chen',
                'role' => 'partner',
                'title' => 'Senior Arbitration Partner',
                'password' => Hash::make('password123'),
            ]
        );

        $this->lawyer3Associate = User::firstOrCreate(
            ['email' => 'david@lexjuris.com'],
            [
                'firm_id' => $this->firm3->id,
                'name' => 'Adv. David Sterling',
                'role' => 'associate',
                'title' => 'Associate Counsel',
                'password' => Hash::make('password123'),
            ]
        );

        $this->client3User = User::firstOrCreate(
            ['email' => 'anita@heritageheights.in'],
            [
                'firm_id' => $this->firm3->id,
                'name' => 'Anita Desai',
                'role' => 'client',
                'title' => 'Director, Heritage Heights Realty',
                'password' => Hash::make('password123'),
            ]
        );

        $this->client3 = Client::firstOrCreate(
            ['email' => 'anita@heritageheights.in'],
            [
                'firm_id' => $this->firm3->id,
                'user_id' => $this->client3User->id,
                'name' => 'Heritage Heights Realty Ltd',
                'type' => 'corporate',
                'contact_person' => 'Anita Desai',
                'phone' => '+91 98110 55443',
                'tax_id' => '07AABCH8877E1Z5',
                'trust_balance' => 150000.00,
                'status' => 'active',
            ]
        );

        $this->matter3 = Matter::firstOrCreate(
            ['case_number' => 'ARB/2026/089'],
            [
                'firm_id' => $this->firm3->id,
                'client_id' => $this->client3->id,
                'lead_attorney_id' => $this->lawyer3Partner->id,
                'title' => 'Heritage Heights vs Metropolitan Development Authority',
                'court_name' => 'Delhi International Arbitration Centre',
                'practice_area' => 'Arbitration',
                'status' => 'active',
                'billing_type' => 'hourly',
            ]
        );
    }

    /**
     * ACT 1: Multi-Firm Chambers & Lawyer Rosters Verification
     */
    public function test_story_act_1_multi_firm_and_lawyer_roster_setup(): void
    {
        $this->assertCount(3, Firm::whereIn('id', [$this->firm1->id, $this->firm2->id, $this->firm3->id])->get());

        // Firm 1 roster
        $this->assertEquals($this->firm1->id, $this->lawyer1Partner->firm_id);
        $this->assertEquals($this->firm1->id, $this->lawyer1Associate->firm_id);
        $this->assertEquals($this->firm1->id, $this->client1->firm_id);

        // Firm 2 roster
        $this->assertEquals($this->firm2->id, $this->lawyer2Partner->firm_id);
        $this->assertEquals($this->firm2->id, $this->lawyer2Associate->firm_id);
        $this->assertEquals($this->firm2->id, $this->client2->firm_id);

        // Firm 3 roster
        $this->assertEquals($this->firm3->id, $this->lawyer3Partner->firm_id);
        $this->assertEquals($this->firm3->id, $this->lawyer3Associate->firm_id);
        $this->assertEquals($this->firm3->id, $this->client3->firm_id);
    }

    /**
     * ACT 2: Consultation Booking, Email Notification, and Cancellation
     */
    public function test_story_act_2_appointment_booking_email_and_cancellation(): void
    {
        Mail::fake();

        // Partner Rajesh Sharma books a consultation with client Vikram Malhotra
        $response = $this->actingAs($this->lawyer1Partner)->post(route('appointments.store'), [
            'matter_id' => $this->matter1->id,
            'client_id' => $this->client1->id,
            'user_id' => $this->lawyer1Partner->id,
            'title' => 'Strategy Conference: Supply Contract Injunction',
            'type' => 'client_consultation',
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'location' => 'Senior Partner Chambers, Vennamraj Associates',
            'notes' => 'Review section 9 arbitration interim relief petition.',
        ]);

        $response->assertRedirect(route('appointments.index'));

        $appointment = Appointment::where('title', 'Strategy Conference: Supply Contract Injunction')->first();
        $this->assertNotNull($appointment);
        $this->assertEquals('scheduled', $appointment->status);

        // Assert AppointmentScheduledMail was sent to client
        Mail::assertSent(AppointmentScheduledMail::class, function ($mail) {
            return $mail->hasTo($this->client1->email);
        });

        // Client requests rescheduling due to board meeting; Lawyer cancels current session
        $cancelResponse = $this->actingAs($this->lawyer1Partner)->post(
            route('appointments.update-status', $appointment),
            ['status' => 'cancelled']
        );

        $cancelResponse->assertStatus(302);
        $appointment->refresh();
        $this->assertEquals('cancelled', $appointment->status);

        // Assert AppointmentCancelledMail was sent to client
        Mail::assertSent(AppointmentCancelledMail::class, function ($mail) {
            return $mail->hasTo($this->client1->email);
        });
    }

    /**
     * ACT 3: Lawyer Document Request Dispatch & Client Email Notification
     */
    public function test_story_act_3_lawyer_document_request_dispatch_and_email(): void
    {
        Mail::fake();

        // Firm 2 Tax Counsel Neha Singhania requests audited balance sheet from Deepak Gupta
        $response = $this->actingAs($this->lawyer2Associate)->post(route('document-requests.store'), [
            'matter_id' => $this->matter2->id,
            'client_id' => $this->client2->id,
            'title' => 'Audited Balance Sheet & P&L FY 2023-24',
            'description' => 'Required for Form GST DRC-01 submission before appellate commissioner.',
            'category' => 'Financial Statements',
            'priority' => 'urgent',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertStatus(302);

        $docRequest = DocumentRequest::where('title', 'Audited Balance Sheet & P&L FY 2023-24')->first();
        $this->assertNotNull($docRequest);
        $this->assertEquals('pending', $docRequest->status);
        $this->assertEquals($this->firm2->id, $docRequest->firm_id);
        $this->assertEquals($this->client2->id, $docRequest->client_id);
        $this->assertEquals($this->lawyer2Associate->id, $docRequest->requested_by);

        // Assert email dispatched to client Deepak Gupta
        Mail::assertSent(DocumentRequestedMail::class, function ($mail) {
            return $mail->hasTo($this->client2->email);
        });
    }

    /**
     * ACT 4: Client S3 Cloud Upload & Cryptographic SHA-256 Checksum Verification
     */
    public function test_story_act_4_client_s3_upload_and_sha256_checksum(): void
    {
        // Setup S3 fake storage disk
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $docRequest = DocumentRequest::create([
            'firm_id' => $this->firm2->id,
            'matter_id' => $this->matter2->id,
            'client_id' => $this->client2->id,
            'requested_by' => $this->lawyer2Associate->id,
            'title' => 'Income Tax Assessment Order FY22-23',
            'category' => 'Financial Statements',
            'priority' => 'urgent',
            'status' => 'pending',
        ]);

        // Client logs into portal and views requests list
        $viewResponse = $this->actingAs($this->client2User)->get(route('portal.requests.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Income Tax Assessment Order FY22-23');

        // Create sample filing with known SHA-256 hash
        $fileContent = '%PDF-1.4 CERTIFIED SJM AUDIT REPORT HASH-VERIFIED 2026';
        $expectedSha256 = hash('sha256', $fileContent);
        $uploadedFile = UploadedFile::fake()->createWithContent('audit_report_fy23.pdf', $fileContent);

        // Client uploads requested document to S3
        $uploadResponse = $this->actingAs($this->client2User)->post(
            route('portal.requests.upload', $docRequest),
            [
                'file' => $uploadedFile,
                'client_notes' => 'Audited by Deloitte Haskins & Sells. Verified true copy.',
            ]
        );

        $uploadResponse->assertStatus(302);

        $docRequest->refresh();
        $this->assertEquals('submitted', $docRequest->status);
        $this->assertNotNull($docRequest->submitted_at);
        $this->assertNotNull($docRequest->document_id);

        $document = Document::find($docRequest->document_id);
        $this->assertNotNull($document);
        $this->assertEquals($expectedSha256, $document->sha256);
        $this->assertEquals('audit_report_fy23.pdf', $document->filename);

        // Assert file exists on S3 fake storage disk
        Storage::disk('s3')->assertExists($document->file_path);
    }

    /**
     * ACT 5: Lawyer Reviews & Downloads S3 Filing with Checksum Match
     */
    public function test_story_act_5_lawyer_s3_download_and_review_approval(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $fileContent = 'CONFIDENTIAL STATUTORY FILING WITH HIGH COURT REGISTRY';
        $sha256 = hash('sha256', $fileContent);
        $storedPath = Storage::disk('s3')->put('documents/client_uploads/statutory_filing.pdf', $fileContent);
        $actualPath = 'documents/client_uploads/statutory_filing.pdf';

        $document = Document::create([
            'firm_id' => $this->firm2->id,
            'matter_id' => $this->matter2->id,
            'user_id' => $this->client2User->id,
            'title' => 'Statutory Filing Receipt',
            'filename' => 'statutory_filing.pdf',
            'file_path' => $actualPath,
            'file_size' => strlen($fileContent),
            'mime_type' => 'application/pdf',
            'sha256' => $sha256,
            'category' => 'Client Submissions',
            'privilege' => 'Confidential',
            'version' => 1,
        ]);

        $docRequest = DocumentRequest::create([
            'firm_id' => $this->firm2->id,
            'matter_id' => $this->matter2->id,
            'client_id' => $this->client2->id,
            'requested_by' => $this->lawyer2Associate->id,
            'document_id' => $document->id,
            'title' => 'Statutory Filing Receipt',
            'status' => 'submitted',
        ]);

        // Lawyer Neha Singhania downloads file from S3
        $downloadResponse = $this->actingAs($this->lawyer2Associate)->get(route('documents.download', $document));
        $downloadResponse->assertStatus(200);

        // Verify downloaded content matches byte-for-byte
        ob_start();
        $downloadResponse->sendContent();
        $streamedContent = ob_get_clean();

        $this->assertEquals($fileContent, $streamedContent);
        $this->assertEquals($sha256, hash('sha256', $streamedContent));

        // Lawyer completes review and marks submission as completed
        $reviewResponse = $this->actingAs($this->lawyer2Associate)->post(
            route('document-requests.review', $docRequest),
            [
                'status' => 'completed',
                'review_notes' => 'Verified with High Court registry filing counter.',
            ]
        );

        $reviewResponse->assertStatus(302);
        $docRequest->refresh();
        $this->assertEquals('completed', $docRequest->status);
        $this->assertEquals('Verified with High Court registry filing counter.', $docRequest->review_notes);
    }

    /**
     * ACT 6: Two-Way Privileged Matter Chat Between Lawyer & Client
     */
    public function test_story_act_6_two_way_privileged_matter_chat(): void
    {
        // 1. Lawyer sends strategy query to client
        $lawyerMsg = 'Please confirm whether clause 12.3 includes the penalty clause for delay in delivery.';
        $lawyerResponse = $this->actingAs($this->lawyer2Associate)->post(route('messages.store'), [
            'matter_id' => $this->matter2->id,
            'body' => $lawyerMsg,
        ]);
        $lawyerResponse->assertStatus(302);

        // 2. Client logs into portal and replies
        $clientReply = 'Yes, Counsel. Clause 12.3 stipulates 0.5% per week penalty capped at 10% total contract value.';
        $clientResponse = $this->actingAs($this->client2User)->post(route('portal.messages.store'), [
            'matter_id' => $this->matter2->id,
            'body' => $clientReply,
        ]);
        $clientResponse->assertStatus(302);

        // Verify message thread on matter dossier
        $messages = Message::where('matter_id', $this->matter2->id)->orderBy('created_at')->get();
        $this->assertGreaterThanOrEqual(2, $messages->count());

        $firstMsg = $messages->firstWhere('body', $lawyerMsg);
        $this->assertNotNull($firstMsg);
        $this->assertEquals($this->lawyer2Associate->id, $firstMsg->sender_id);
        $this->assertTrue((bool) $firstMsg->is_privileged);

        $secondMsg = $messages->firstWhere('body', $clientReply);
        $this->assertNotNull($secondMsg);
        $this->assertEquals($this->client2User->id, $secondMsg->sender_id);
        $this->assertTrue((bool) $secondMsg->is_privileged);
    }

    /**
     * ACT 7: Client Downloads Lawyer Documents from S3 Portal
     */
    public function test_story_act_7_client_downloads_lawyer_s3_document_without_barriers(): void
    {
        Storage::fake('s3');
        config(['filesystems.default' => 's3']);

        $agreementContent = 'DELHI ARBITRATION CENTRE INTERIM CONSENT ORDER 2026';
        $sha256 = hash('sha256', $agreementContent);
        $filePath = 'documents/arbitration_agreement.pdf';
        Storage::disk('s3')->put($filePath, $agreementContent);

        // Lawyer Sarah Chen (Firm 3) uploads arbitration order
        $document = Document::create([
            'firm_id' => $this->firm3->id,
            'matter_id' => $this->matter3->id,
            'user_id' => $this->lawyer3Partner->id,
            'title' => 'Interim Consent Order',
            'filename' => 'arbitration_agreement.pdf',
            'file_path' => $filePath,
            'file_size' => strlen($agreementContent),
            'mime_type' => 'application/pdf',
            'sha256' => $sha256,
            'category' => 'Court Filings',
            'privilege' => 'Confidential',
            'version' => 1,
        ]);

        // Client Anita Desai downloads directly from portal
        $downloadResponse = $this->actingAs($this->client3User)->get(route('portal.documents.download', $document));

        // Assert client is NOT redirected to portal dashboard and gets direct file stream
        $downloadResponse->assertStatus(200);

        ob_start();
        $downloadResponse->sendContent();
        $streamedContent = ob_get_clean();

        $this->assertEquals($agreementContent, $streamedContent);
    }

    /**
     * ACT 8: Strict Multi-Tenant Isolation and Security Blocks
     */
    public function test_story_act_8_strict_multi_tenant_isolation_and_security(): void
    {
        // Firm 1 partner cannot download Firm 2's document -> 403 Forbidden
        $firm2Doc = Document::firstOrCreate(
            ['firm_id' => $this->firm2->id, 'title' => 'Confidential Tax Assessment Order'],
            [
                'matter_id' => $this->matter2->id,
                'user_id' => $this->lawyer2Partner->id,
                'filename' => 'tax_order.pdf',
                'file_path' => 'documents/tax_order.pdf',
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
                'sha256' => hash('sha256', 'Tax Order'),
                'category' => 'Court Filings',
                'privilege' => 'Confidential',
                'version' => 1,
            ]
        );

        $unauthorizedStaffDownload = $this->actingAs($this->lawyer1Partner)->get(route('documents.download', $firm2Doc));
        $unauthorizedStaffDownload->assertStatus(403);

        // Firm 1 client cannot download Firm 2's document -> 403 Forbidden
        $unauthorizedClientDownload = $this->actingAs($this->client1User)->get(route('portal.documents.download', $firm2Doc));
        $unauthorizedClientDownload->assertStatus(403);

        // Firm 1 partner cannot review Firm 2's document request -> 403 Forbidden
        $firm2Request = DocumentRequest::firstOrCreate(
            ['firm_id' => $this->firm2->id, 'title' => 'Cross-Firm Protected Request'],
            [
                'matter_id' => $this->matter2->id,
                'client_id' => $this->client2->id,
                'requested_by' => $this->lawyer2Partner->id,
                'status' => 'submitted',
            ]
        );

        $unauthorizedReview = $this->actingAs($this->lawyer1Partner)->post(
            route('document-requests.review', $firm2Request),
            ['status' => 'completed']
        );
        $unauthorizedReview->assertStatus(403);

        // Firm 1 client cannot post to Firm 2's matter
        $unauthorizedPost = $this->actingAs($this->client1User)->post(route('portal.messages.store'), [
            'matter_id' => $this->matter2->id,
            'body' => 'Attempted breach across client tenant boundaries.',
        ]);
        // Returns 404/403 because matter is scoped to client_id
        $this->assertContains($unauthorizedPost->getStatusCode(), [403, 404]);
    }
}
