<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientOnboardingAndDocumentGovernanceTest extends TestCase
{
    protected User $partnerFirm1;

    protected User $partnerFirm2;

    protected Firm $firm1;

    protected Firm $firm2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);

        $this->partnerFirm1 = User::where('email', 'rajesh@sharmalegal.in')->firstOrFail();
        $this->partnerFirm2 = User::where('email', 'sanjeev@sjmlegal.in')->firstOrFail();

        $this->firm1 = $this->partnerFirm1->firm;
        $this->firm2 = $this->partnerFirm2->firm;
    }

    /**
     * 1. Test onboarding a single individual client with Indian statutory KYC.
     */
    public function test_can_onboard_single_individual_client_with_indian_kyc(): void
    {
        $response = $this->actingAs($this->partnerFirm1)->post(route('clients.store'), [
            'category' => 'individual',
            'onboarding_mode' => 'portal_online',
            'name' => 'Gopal Krishna Tiwari',
            'father_husband_name' => 'S/o Late Shri Radheshyam Tiwari',
            'email' => 'gopal.tiwari@example.in',
            'phone' => '+91 98111 22334',
            'pan' => 'ABCDE1234F',
            'aadhaar_last_four' => '7890',
            'age' => 48,
            'gender' => 'male',
            'occupation' => 'Civil Contractor',
            'address_line_1' => 'Plot No. 12, Jagriti Enclave',
            'district' => 'East Delhi',
            'state' => 'Delhi',
            'pincode' => '110092',
            'police_station' => 'Anand Vihar',
            'primary_attorney_id' => $this->partnerFirm1->id,
            'trust_balance' => 25000.00,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('clients', [
            'name' => 'Gopal Krishna Tiwari',
            'father_husband_name' => 'S/o Late Shri Radheshyam Tiwari',
            'pan' => 'ABCDE1234F',
            'aadhaar_last_four' => '7890',
            'police_station' => 'Anand Vihar',
            'firm_id' => $this->firm1->id,
        ]);

        $client = Client::where('email', 'gopal.tiwari@example.in')->firstOrFail();
        $this->assertEquals('XXXX-XXXX-7890', $client->masked_aadhaar);
        $this->assertEquals('ABCDE1234F', $client->pan);
        $this->assertTrue($client->isIndividual());
        $this->assertTrue($client->hasActiveInvitation());
    }

    /**
     * 2. Test onboarding joint litigants (2+ persons / co-petitioners).
     */
    public function test_can_onboard_joint_clients_with_multiple_members(): void
    {
        $response = $this->actingAs($this->partnerFirm1)->post(route('clients.store'), [
            'category' => 'joint',
            'onboarding_mode' => 'portal_online',
            'name' => 'Sunil Verma & Family',
            'email' => 'sunil.verma@example.com',
            'phone' => '+91 98222 33445',
            'district' => 'South Delhi',
            'state' => 'Delhi',
            'pincode' => '110017',
            'police_station' => 'Malviya Nagar',
            'members' => [
                [
                    'name' => 'Sunil Verma',
                    'relationship' => 'Primary Petitioner',
                    'phone' => '+91 98222 33445',
                    'pan' => 'BKLPV1234M',
                    'aadhaar_last_four' => '1122',
                    'is_primary_signatory' => true,
                ],
                [
                    'name' => 'Anita Verma',
                    'relationship' => 'Spouse & Co-petitioner',
                    'phone' => '+91 98222 33446',
                    'pan' => 'BKLPA5678N',
                    'aadhaar_last_four' => '3344',
                    'is_primary_signatory' => false,
                ],
                [
                    'name' => 'Rohan Verma',
                    'relationship' => 'Son & Co-owner',
                    'phone' => '+91 98222 33447',
                    'pan' => 'BKLPR9012K',
                    'aadhaar_last_four' => '5566',
                    'is_primary_signatory' => false,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $client = Client::where('email', 'sunil.verma@example.com')->firstOrFail();
        $this->assertTrue($client->isJoint());
        $this->assertCount(3, $client->members);

        $this->assertDatabaseHas('client_members', [
            'client_id' => $client->id,
            'name' => 'Anita Verma',
            'relationship' => 'Spouse & Co-petitioner',
            'aadhaar_last_four' => '3344',
        ]);
    }

    /**
     * 3. Test onboarding corporate client with CIN, GSTIN, and Authorized Signatory.
     */
    public function test_can_onboard_corporate_client_with_cin_gstin_and_authorized_signatory(): void
    {
        $response = $this->actingAs($this->partnerFirm1)->post(route('clients.store'), [
            'category' => 'corporate',
            'onboarding_mode' => 'portal_online',
            'name' => 'Bharat Tech Horizons Pvt Ltd',
            'contact_person' => 'Siddharth Roy, Director',
            'email' => 'legal@bharattech.in',
            'phone' => '+91 (11) 4000-8900',
            'cin' => 'u72200dl2021ptc123456',
            'pan' => 'aaacb1234f',
            'gstin' => '07aaacb1234f1z5',
            'district' => 'New Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'police_station' => 'Barakhamba Road',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'name' => 'Bharat Tech Horizons Pvt Ltd',
            'cin' => 'U72200DL2021PTC123456',
            'pan' => 'AAACB1234F',
            'gstin' => '07AAACB1234F1Z5',
        ]);

        $client = Client::where('email', 'legal@bharattech.in')->firstOrFail();
        $this->assertTrue($client->isCorporate());
    }

    /**
     * 4. Test onboarding an assisted offline client with NO email (illiterate/POA/elderly).
     */
    public function test_can_onboard_assisted_offline_client_without_email(): void
    {
        $response = $this->actingAs($this->partnerFirm1)->post(route('clients.store'), [
            'category' => 'individual',
            'onboarding_mode' => 'assisted_offline',
            'name' => 'Munni Devi',
            'father_husband_name' => 'W/o Late Shri Ram Swaroop',
            'phone' => '+91 94120 11223',
            'district' => 'Varanasi',
            'state' => 'Uttar Pradesh',
            'pincode' => '221001',
            'police_station' => 'Cantonment',
            'representation_mode' => 'poa_holder',
            'representative_name' => 'Manoj Swaroop',
            'representative_relation' => 'Elder Son & General Attorney',
            'representative_phone' => '+91 94120 11224',
            'poa_registration_number' => 'IV-Book4-892/2021',
            'poa_sub_registrar_office' => 'Sub-Registrar II, Varanasi',
        ]);

        $response->assertSessionHasNoErrors();

        $client = Client::where('name', 'Munni Devi')->firstOrFail();
        $this->assertNull($client->email);
        $this->assertTrue($client->isOfflineOnly());
        $this->assertEquals('offline_only', $client->portal_status);
        $this->assertEquals('poa_holder', $client->representation_mode);
        $this->assertEquals('Manoj Swaroop', $client->representative_name);
    }

    /**
     * 5. Test portal invitation token lifecycle and client self-activation.
     */
    public function test_portal_invitation_flow_and_token_activation(): void
    {
        $client = Client::create([
            'firm_id' => $this->firm1->id,
            'name' => 'Deepak Malhotra',
            'category' => 'individual',
            'onboarding_mode' => 'portal_online',
            'email' => 'deepak.malhotra@example.com',
            'phone' => '+91 99111 88776',
            'status' => 'active',
        ]);

        $token = $client->generateInvitationToken();

        // 1. Visit invitation acceptance page
        $viewResponse = $this->get(route('portal.invitation.accept', ['token' => $token]));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Activate Client Portal');
        $viewResponse->assertSee('Deepak Malhotra');

        // 2. Complete password setup
        $completeResponse = $this->post(route('portal.invitation.complete', ['token' => $token]), [
            'password' => 'ChambersPass@2026',
            'password_confirmation' => 'ChambersPass@2026',
        ]);

        $completeResponse->assertRedirect(route('portal.dashboard'));

        $client->refresh();
        $this->assertEquals('active', $client->portal_status);
        $this->assertNull($client->invitation_token);
        $this->assertNotNull($client->user_id);

        $user = User::find($client->user_id);
        $this->assertTrue(Hash::check('ChambersPass@2026', $user->password));
        $this->assertTrue($user->isClient());
    }

    /**
     * 6. Test advocate can initiate a document request with Indian presets.
     */
    public function test_advocate_can_request_specific_document_from_client(): void
    {
        $client = Client::where('firm_id', $this->firm1->id)->firstOrFail();
        $matter = Matter::where('firm_id', $this->firm1->id)->where('client_id', $client->id)->first()
            ?? Matter::where('firm_id', $this->firm1->id)->firstOrFail();

        // Ensure client owns matter
        $matter->update(['client_id' => $client->id]);

        $response = $this->actingAs($this->partnerFirm1)->post(route('document-requests.store'), [
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'title' => 'Registered Sale Deed (Original Stamp Paper)',
            'description' => 'Color scan of all 14 pages including back endorsements.',
            'category' => 'Property Documents',
            'priority' => 'urgent',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('document_requests', [
            'title' => 'Registered Sale Deed (Original Stamp Paper)',
            'category' => 'Property Documents',
            'priority' => 'urgent',
            'status' => 'pending',
            'client_id' => $client->id,
        ]);
    }

    /**
     * 7. Test client can ONLY upload files against an explicit active document request.
     */
    public function test_client_can_only_upload_against_explicit_active_request(): void
    {
        Storage::fake('local');

        $client = Client::where('firm_id', $this->firm1->id)->firstOrFail();
        $matter = Matter::where('firm_id', $this->firm1->id)->firstOrFail();
        $matter->update(['client_id' => $client->id]);

        // Create portal user for client
        $portalUser = User::create([
            'firm_id' => $this->firm1->id,
            'name' => $client->name,
            'email' => 'client.portal.test@domain.in',
            'password' => Hash::make('password123'),
            'role' => 'client',
        ]);
        $client->update(['user_id' => $portalUser->id, 'email' => $portalUser->email]);

        // Create active request
        $docRequest = DocumentRequest::create([
            'firm_id' => $this->firm1->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'requested_by' => $this->partnerFirm1->id,
            'title' => '7/12 Extract (Satbara)',
            'status' => 'pending',
        ]);

        $fakeFile = UploadedFile::fake()->create('satbara_extract.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($portalUser)->post(route('portal.requests.upload', $docRequest->id), [
            'file' => $fakeFile,
            'client_notes' => 'Certified copy obtained from Tahsildar Office on 15th Sept.',
        ]);

        $response->assertSessionHasNoErrors();

        $docRequest->refresh();
        $this->assertEquals('submitted', $docRequest->status);
        $this->assertNotNull($docRequest->document_id);

        $document = Document::find($docRequest->document_id);
        $this->assertNotNull($document);
        $this->assertNotNull($document->sha256);
        $this->assertEquals(64, strlen($document->sha256)); // Valid SHA-256 hash
    }

    /**
     * 8. Test strict document isolation (Section 126 Evidence Act Privilege).
     * Advocate from Firm 2 cannot access Firm 1's client dossier.
     */
    public function test_strict_document_isolation_prevents_unauthorized_access(): void
    {
        $clientFirm1 = Client::where('firm_id', $this->firm1->id)->firstOrFail();

        // Advocate from Firm 2 attempts to view client dossier of Firm 1
        $response = $this->actingAs($this->partnerFirm2)->get(route('clients.show', $clientFirm1->id));
        $response->assertStatus(403);
    }

    /**
     * 9. Test advocate review workflow: Accept & auto-file or Reject with feedback.
     */
    public function test_advocate_can_accept_or_reject_client_document_submission(): void
    {
        $client = Client::where('firm_id', $this->firm1->id)->firstOrFail();
        $matter = Matter::where('firm_id', $this->firm1->id)->firstOrFail();

        $docRequest = DocumentRequest::create([
            'firm_id' => $this->firm1->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'requested_by' => $this->partnerFirm1->id,
            'title' => 'Affidavit in Support of Injunction',
            'status' => 'submitted',
        ]);

        // 1. Rejection
        $rejectResponse = $this->actingAs($this->partnerFirm1)->post(route('document-requests.review', $docRequest->id), [
            'status' => 'rejected',
            'rejection_reason' => 'Notary seal is missing on page 2. Please re-scan with seal.',
        ]);

        $rejectResponse->assertSessionHasNoErrors();
        $docRequest->refresh();
        $this->assertEquals('rejected', $docRequest->status);
        $this->assertEquals('Notary seal is missing on page 2. Please re-scan with seal.', $docRequest->rejection_reason);

        // 2. Acceptance
        $acceptResponse = $this->actingAs($this->partnerFirm1)->post(route('document-requests.review', $docRequest->id), [
            'status' => 'completed',
            'review_notes' => 'Verified with notary register. Filed into case dossier.',
        ]);

        $acceptResponse->assertSessionHasNoErrors();
        $docRequest->refresh();
        $this->assertEquals('completed', $docRequest->status);
        $this->assertEquals($this->partnerFirm1->id, $docRequest->reviewed_by);
    }

    /**
     * 10. Test advocate/clerk assisted upload on behalf of offline client.
     */
    public function test_advocate_assisted_upload_on_behalf_of_offline_client(): void
    {
        Storage::fake('local');

        $client = Client::where('firm_id', $this->firm1->id)->firstOrFail();
        $matter = Matter::where('firm_id', $this->firm1->id)->firstOrFail();

        $docRequest = DocumentRequest::create([
            'firm_id' => $this->firm1->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'requested_by' => $this->partnerFirm1->id,
            'title' => 'Physical Sale Deed Original Inspection',
            'status' => 'pending',
        ]);

        $fakeFile = UploadedFile::fake()->create('physical_sale_deed_scan.pdf', 2048, 'application/pdf');

        $response = $this->actingAs($this->partnerFirm1)->post(route('document-requests.assisted-upload', $docRequest->id), [
            'file' => $fakeFile,
            'clerk_notes' => 'Physically handed over by client in chamber. Scanned and returned original to client.',
        ]);

        $response->assertSessionHasNoErrors();

        $docRequest->refresh();
        $this->assertEquals('submitted', $docRequest->status);
        $this->assertTrue($docRequest->is_assisted_submission);
        $this->assertEquals($this->partnerFirm1->id, $docRequest->assisted_by_user_id);
    }

    /**
     * 11. Test printable chamber intake docket sheet (Basta Slip) renders cleanly.
     */
    public function test_printable_intake_slip_renders_properly(): void
    {
        $client = Client::where('firm_id', $this->firm1->id)->firstOrFail();

        $response = $this->actingAs($this->partnerFirm1)->get(route('clients.intake-slip', $client->id));
        $response->assertStatus(200);
        $response->assertSee('Physical Litigation Dossier &amp; Intake Sheet', false);
        $response->assertSee($client->name);
    }

    /**
     * 12. Test client show view renders Initiate Document Request button even when client has 0 matters.
     */
    public function test_client_show_renders_initiate_document_request_button_even_without_matters(): void
    {
        $newClient = Client::create([
            'firm_id' => $this->firm1->id,
            'name' => 'Sumanth K. Rao',
            'email' => 'sumanth.rao@example.com',
            'phone' => '+91 99887 76655',
            'category' => 'individual',
            'onboarding_mode' => 'portal_online',
            'primary_attorney_id' => $this->partnerFirm1->id,
        ]);

        $this->assertCount(0, $newClient->matters);

        $response = $this->actingAs($this->partnerFirm1)->get(route('clients.show', $newClient->id));
        $response->assertStatus(200);
        $response->assertSee('Request Document');
        $response->assertSee('Initiate Document Request');
        $response->assertSee('No document requests initiated yet');
    }

    /**
     * 13. Test initiating document request with auto_create matter provisions an onboarding dossier docket.
     */
    public function test_can_initiate_document_request_for_client_without_matters_via_auto_intake_dossier(): void
    {
        $newClient = Client::create([
            'firm_id' => $this->firm1->id,
            'name' => 'Kavita Verma',
            'email' => 'kavita.verma@example.com',
            'phone' => '+91 91234 56789',
            'category' => 'individual',
            'onboarding_mode' => 'portal_online',
            'primary_attorney_id' => $this->partnerFirm1->id,
        ]);

        $response = $this->actingAs($this->partnerFirm1)->post(route('document-requests.store'), [
            'client_id' => $newClient->id,
            'matter_id' => 'auto_create',
            'title' => 'Self-Attested Aadhaar & PAN Card',
            'category' => 'KYC & Identification',
            'priority' => 'high',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'description' => 'Please upload clear color scans of your Aadhaar card and PAN card.',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('matters', [
            'firm_id' => $this->firm1->id,
            'client_id' => $newClient->id,
            'title' => 'Client Onboarding & Intake Dossier',
            'stage' => 'Intake',
        ]);

        $createdMatter = Matter::where('firm_id', $this->firm1->id)
            ->where('client_id', $newClient->id)
            ->firstOrFail();

        $this->assertDatabaseHas('document_requests', [
            'firm_id' => $this->firm1->id,
            'client_id' => $newClient->id,
            'matter_id' => $createdMatter->id,
            'title' => 'Self-Attested Aadhaar & PAN Card',
            'priority' => 'high',
        ]);
    }
}
