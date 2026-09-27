<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageThread;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientCommunicationThreadsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
        Storage::fake('local');
    }

    /**
     * Test staff can view the message console and thread types.
     */
    public function test_staff_can_view_messages_console_and_filter_threads(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staffUser = User::factory()->create([
            'firm_id' => $firm->id,
            'role' => 'advocate',
        ]);

        $clientUser = User::factory()->create(['role' => 'client', 'firm_id' => $firm->id]);
        $client = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientUser->id,
            'name' => 'Tata Sons Representation',
            'email' => $clientUser->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'CS(OS)/501/2026',
            'title' => 'Tata Sons vs Commercial Enterprise Ltd',
            'practice_area' => 'Corporate Litigation',
            'status' => 'Open',
        ]);

        // Create client communication thread
        $clientThread = MessageThread::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'created_by' => $staffUser->id,
            'subject' => 'Interim Injunction Hearing Date Discussion',
            'thread_type' => MessageThread::TYPE_CLIENT_COMMUNICATION,
            'is_internal' => false,
            'status' => 'open',
        ]);

        // Create internal chambers thread
        $internalThread = MessageThread::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => null,
            'created_by' => $staffUser->id,
            'subject' => 'Confidential Counsel Strategy & Bench Tendencies',
            'thread_type' => MessageThread::TYPE_INTERNAL_TEAM,
            'is_internal' => true,
            'status' => 'open',
        ]);

        $response = $this->actingAs($staffUser)->get(route('messages.index', ['matter_id' => $matter->id]));

        $response->assertStatus(200);
        $response->assertSee('Case Messages &amp; Communication Threads', false);
        $response->assertSee('Interim Injunction Hearing Date Discussion');
        $response->assertSee('Confidential Counsel Strategy &amp; Bench Tendencies', false);
        $response->assertSee('Internal Chambers');

        // Test filtering by internal
        $internalFilterResponse = $this->actingAs($staffUser)->get(route('messages.index', ['matter_id' => $matter->id, 'type' => 'internal']));
        $internalFilterResponse->assertStatus(200);
        $internalFilterResponse->assertSee('Confidential Counsel Strategy &amp; Bench Tendencies', false);
    }

    /**
     * Test staff can create an internal chambers thread and message with technical separation.
     */
    public function test_staff_can_create_internal_chambers_thread_and_messages(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staffUser = User::factory()->create([
            'firm_id' => $firm->id,
            'role' => 'advocate',
        ]);

        $clientUser = User::factory()->create(['role' => 'client', 'firm_id' => $firm->id]);
        $client = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientUser->id,
            'name' => 'Adani Port Logistics',
            'email' => $clientUser->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'WP(C)/882/2026',
            'title' => 'Adani Port vs Maritime Authority',
            'practice_area' => 'Maritime Law',
            'status' => 'Open',
        ]);

        $postData = [
            'matter_id' => $matter->id,
            'subject' => 'Senior Counsel Privilege Analysis on Precedent',
            'thread_type' => MessageThread::TYPE_INTERNAL_TEAM,
            'body' => 'Strictly internal: We need to evaluate limitation defense under Section 5.',
        ];

        $response = $this->actingAs($staffUser)->post(route('messages.threads.store'), $postData);

        $response->assertRedirect();

        $thread = MessageThread::where('subject', 'Senior Counsel Privilege Analysis on Precedent')->first();
        $this->assertNotNull($thread);
        $this->assertTrue($thread->is_internal);
        $this->assertEquals(MessageThread::TYPE_INTERNAL_TEAM, $thread->thread_type);
        $this->assertStringStartsWith('THR-', $thread->thread_number);

        $message = Message::where('thread_id', $thread->id)->first();
        $this->assertNotNull($message);
        $this->assertTrue($message->is_internal);
        $this->assertStringStartsWith('MSG-', $message->message_number);
    }

    /**
     * Test database authorization strictly separates internal communications from client queries.
     */
    public function test_client_database_isolation_strictly_prevents_client_from_querying_internal_communications(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staffUser = User::factory()->create([
            'firm_id' => $firm->id,
            'role' => 'advocate',
        ]);

        $clientUser = User::factory()->create(['role' => 'client', 'firm_id' => $firm->id]);
        $client = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientUser->id,
            'name' => 'Reliance Telecom',
            'email' => $clientUser->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'CA/102/2026',
            'title' => 'Reliance vs Telecom Regulatory Body',
            'practice_area' => 'Telecom Regulation',
            'status' => 'Open',
        ]);

        // Client visible thread
        $clientThread = MessageThread::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => $client->id,
            'created_by' => $staffUser->id,
            'subject' => 'Formal Client Advisory on Compliance Notice',
            'thread_type' => MessageThread::TYPE_CLIENT_COMMUNICATION,
            'is_internal' => false,
            'status' => 'open',
        ]);

        Message::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'thread_id' => $clientThread->id,
            'sender_id' => $staffUser->id,
            'recipient_id' => $clientUser->id,
            'subject' => 'Formal Client Advisory on Compliance Notice',
            'body' => 'Here is the formal advisory for your board of directors.',
            'is_internal' => false,
            'status' => 'sent',
        ]);

        // Internal thread
        $internalThread = MessageThread::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => null,
            'created_by' => $staffUser->id,
            'subject' => 'Secret Chamber Evaluation: High Risk Exposure',
            'thread_type' => MessageThread::TYPE_INTERNAL_TEAM,
            'is_internal' => true,
            'status' => 'open',
        ]);

        Message::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'thread_id' => $internalThread->id,
            'sender_id' => $staffUser->id,
            'recipient_id' => null,
            'subject' => 'Secret Chamber Evaluation: High Risk Exposure',
            'body' => 'DO NOT DISCLOSE TO CLIENT: Potential liability exceeds 50 crores.',
            'is_internal' => true,
            'status' => 'sent',
        ]);

        // Verify Eloquent Global Scope isolation when acting as Client
        $this->actingAs($clientUser);

        // Direct DB Eloquent query by client user must NEVER return internal threads
        $clientMatterThreads = MessageThread::where('matter_id', $matter->id)->get();
        $this->assertCount(1, $clientMatterThreads);
        $this->assertEquals('Formal Client Advisory on Compliance Notice', $clientMatterThreads->first()->subject);
        $this->assertFalse(MessageThread::all()->contains('id', $internalThread->id));

        // Direct DB Eloquent query by client user must NEVER return internal messages
        $clientMatterMessages = Message::where('matter_id', $matter->id)->get();
        $this->assertCount(1, $clientMatterMessages);
        $this->assertFalse(Message::all()->contains('id', $internalThread->messages()->first()?->id));
        $this->assertFalse(Message::all()->contains('subject', 'Secret Chamber Evaluation: High Risk Exposure'));

        // Direct HTTP request to Portal messages index must NOT render internal thread or messages
        $response = $this->get(route('portal.messages.index', ['matter_id' => $matter->id]));
        $response->assertStatus(200);
        $response->assertSee('Formal Client Advisory on Compliance Notice');
        $response->assertDontSee('Secret Chamber Evaluation: High Risk Exposure');
        $response->assertDontSee('DO NOT DISCLOSE TO CLIENT');

        // Client cannot access internal thread URL directly
        $blockedResponse = $this->get(route('portal.messages.index', ['matter_id' => $matter->id, 'thread_id' => $internalThread->id]));
        $blockedResponse->assertStatus(403);
    }

    /**
     * Test client can create a thread and upload attachments.
     */
    public function test_client_can_create_thread_with_attachments_and_view_stream(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staffUser = User::factory()->create([
            'firm_id' => $firm->id,
            'role' => 'advocate',
        ]);

        $clientUser = User::factory()->create(['role' => 'client', 'firm_id' => $firm->id]);
        $client = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientUser->id,
            'name' => 'Infosys Technology Services',
            'email' => $clientUser->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'ARB/404/2026',
            'title' => 'Infosys vs Vendor Solutions',
            'practice_area' => 'Arbitration',
            'status' => 'Open',
        ]);

        $dummyPdf = UploadedFile::fake()->create('contract_signed.pdf', 300, 'application/pdf');

        $response = $this->actingAs($clientUser)->post(route('portal.messages.threads.store'), [
            'matter_id' => $matter->id,
            'thread_type' => MessageThread::TYPE_DOCUMENT_REQUEST,
            'subject' => 'Transmitting Signed Agreement Document',
            'body' => 'Enclosed please find the executed counterpart as requested by lead counsel.',
            'attachments' => [$dummyPdf],
        ]);

        $response->assertRedirect();

        $thread = MessageThread::withoutGlobalScopes()->where('subject', 'Transmitting Signed Agreement Document')->first();
        $this->assertNotNull($thread);
        $this->assertFalse($thread->is_internal);
        $this->assertEquals(MessageThread::TYPE_DOCUMENT_REQUEST, $thread->thread_type);

        $msg = Message::withoutGlobalScopes()->where('thread_id', $thread->id)->first();
        $this->assertNotNull($msg);
        $this->assertEquals('sent', $msg->status);
        $this->assertNotNull($msg->sent_at);
        $this->assertFalse($msg->is_internal);

        $attachment = MessageAttachment::where('message_id', $msg->id)->first();
        $this->assertNotNull($attachment);
        $this->assertEquals('contract_signed.pdf', $attachment->file_name);
        $this->assertNotNull($attachment->sha256);

        // Client can download this attachment
        $downloadResponse = $this->actingAs($clientUser)->get(route('portal.messages.attachments.download', $attachment->id));
        $downloadResponse->assertStatus(200);
    }

    /**
     * Test client cannot download attachments belonging to internal threads.
     */
    public function test_client_forbidden_from_downloading_internal_chambers_attachments(): void
    {
        $firm = Firm::first() ?: Firm::factory()->create();
        $staffUser = User::factory()->create([
            'firm_id' => $firm->id,
            'role' => 'advocate',
        ]);

        $clientUser = User::factory()->create(['role' => 'client', 'firm_id' => $firm->id]);
        $client = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $clientUser->id,
            'name' => 'Wipro Technologies',
            'email' => $clientUser->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $matter = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'case_number' => 'WP/999/2026',
            'title' => 'Wipro Tax Assessment Dispute',
            'practice_area' => 'Taxation',
            'status' => 'Open',
        ]);

        // Internal thread and message with attachment
        $internalThread = MessageThread::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'client_id' => null,
            'created_by' => $staffUser->id,
            'subject' => 'Confidential Tax Risk Matrix',
            'thread_type' => MessageThread::TYPE_INTERNAL_TEAM,
            'is_internal' => true,
            'status' => 'open',
        ]);

        $fakeFile = UploadedFile::fake()->create('internal_tax_audit.pdf', 500, 'application/pdf');
        $storedPath = $fakeFile->store('messages/attachments', 'local');

        $internalMsg = Message::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'thread_id' => $internalThread->id,
            'sender_id' => $staffUser->id,
            'recipient_id' => null,
            'subject' => 'Confidential Tax Risk Matrix',
            'body' => 'Internal risk calculation.',
            'is_internal' => true,
            'status' => 'sent',
            'attachment_path' => $storedPath,
            'attachment_name' => 'internal_tax_audit.pdf',
        ]);

        $internalAtt = MessageAttachment::create([
            'firm_id' => $firm->id,
            'message_id' => $internalMsg->id,
            'file_path' => $storedPath,
            'file_name' => 'internal_tax_audit.pdf',
            'file_size' => 500,
            'mime_type' => 'application/pdf',
            'sha256' => hash('sha256', 'dummycontent'),
        ]);

        // Client attempts to download internal attachment
        $response = $this->actingAs($clientUser)->get(route('portal.messages.attachments.download', $internalAtt->id));
        $response->assertStatus(403);
    }
}
