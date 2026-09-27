<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class ClientDashboardTest extends TestCase
{
    protected User $clientUser;

    protected Client $client;

    protected Firm $firm;

    protected Matter $matter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);

        $this->firm = Firm::first();

        $this->clientUser = User::create([
            'firm_id' => $this->firm->id,
            'name' => 'Meera Krishnan',
            'email' => 'meera.krishnan@testclient.in',
            'password' => bcrypt('password123'),
            'role' => 'client',
            'status' => 'active',
        ]);

        $this->client = Client::create([
            'firm_id' => $this->firm->id,
            'user_id' => $this->clientUser->id,
            'name' => 'Krishnan Tech Enterprises',
            'contact_person' => 'Meera Krishnan',
            'email' => 'meera.krishnan@testclient.in',
            'phone' => '+91 98333 44556',
            'category' => 'corporate',
            'type' => 'corporate',
            'status' => 'active',
            'portal_status' => 'active',
            'trust_balance' => 45000.00,
        ]);

        $this->matter = Matter::create([
            'firm_id' => $this->firm->id,
            'client_id' => $this->client->id,
            'case_number' => 'DEL/2026/HC/990',
            'title' => 'Krishnan Enterprises vs National Telecom Authority',
            'practice_area' => 'Commercial Litigation',
            'court_name' => 'High Court of Delhi',
            'judge_name' => 'Hon\'ble Presiding Justice Pratibha Singh',
            'stage' => 'Notice & Pleadings',
            'status' => 'active',
            'billing_type' => 'hourly',
        ]);
    }

    /**
     * Test client dashboard renders all 7 required components.
     */
    public function test_client_dashboard_renders_all_seven_required_components(): void
    {
        $attorney = User::where('role', '!=', 'client')->first();

        // 1. Recent document
        $doc = Document::create([
            'firm_id' => $this->firm->id,
            'matter_id' => $this->matter->id,
            'user_id' => $attorney->id,
            'title' => 'Affidavit of Undertaking and Evidence',
            'filename' => 'affidavit_evidence.pdf',
            'file_path' => 'documents/affidavit_evidence.pdf',
            'file_size' => 1048576,
            'mime_type' => 'application/pdf',
            'document_type' => 'Affidavit',
            'is_client_visible' => true,
        ]);

        // 2. Pending document request
        $docReq = DocumentRequest::create([
            'firm_id' => $this->firm->id,
            'matter_id' => $this->matter->id,
            'client_id' => $this->client->id,
            'requested_by' => $attorney->id,
            'title' => 'Certified Articles of Association 2025',
            'status' => 'pending',
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        // 3. Upcoming event
        $event = Event::create([
            'firm_id' => $this->firm->id,
            'matter_id' => $this->matter->id,
            'user_id' => $attorney->id,
            'title' => 'Notice Motion & Interim Stay Hearing',
            'event_type' => 'Court Hearing',
            'start_time' => now()->addDays(3)->setTime(10, 30),
            'end_time' => now()->addDays(3)->setTime(11, 30),
            'location' => 'Courtroom #14, Main Bench',
        ]);

        // 4. Recent message
        $message = Message::create([
            'firm_id' => $this->firm->id,
            'matter_id' => $this->matter->id,
            'sender_id' => $attorney->id,
            'body' => 'We have prepared the interim petition and need your signoff.',
            'is_privileged' => true,
        ]);

        // 5. Task requiring client action
        $task = Task::create([
            'firm_id' => $this->firm->id,
            'matter_id' => $this->matter->id,
            'assigned_to' => $this->clientUser->id,
            'created_by' => $attorney->id,
            'title' => 'Execute Digital Vakalatnama & Verification Certificate',
            'description' => 'Sign the verification certificate for Delhi High Court filing.',
            'priority' => 'urgent',
            'status' => 'todo',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $response = $this->actingAs($this->clientUser)->get(route('portal.dashboard'));

        $response->assertStatus(200);

        // Check 1: Active matters
        $response->assertSee('Active Matters');
        $response->assertSee('DEL/2026/HC/990');
        $response->assertSee('Krishnan Enterprises vs National Telecom Authority');

        // Check 2: Recent documents
        $response->assertSee('Recent Documents');
        $response->assertSee('Affidavit of Undertaking and Evidence');

        // Check 3: Pending document requests
        $response->assertSee('Pending Document Requests');
        $response->assertSee('Certified Articles of Association 2025');

        // Check 4: Upcoming events
        $response->assertSee('Upcoming Events');
        $response->assertSee('Notice Motion &amp; Interim Stay Hearing', false);

        // Check 5: Recent messages
        $response->assertSee('Recent Messages');
        $response->assertSee('We have prepared the interim petition and need your signoff.');

        // Check 6: Tasks requiring client action
        $response->assertSee('Tasks Requiring Client Action');
        $response->assertSee('Execute Digital Vakalatnama &amp; Verification Certificate', false);

        // Check 7: Notifications
        $response->assertSee('Notifications');

        // Verify Invoices & Billing is removed from client dashboard
        $response->assertDontSee('Invoices &amp; Billing', false);
        $response->assertDontSee('Invoices & Billing');
    }

    /**
     * Test client can toggle task status between completed and pending.
     */
    public function test_client_can_toggle_action_task_status(): void
    {
        $attorney = User::where('role', '!=', 'client')->first();

        $task = Task::create([
            'firm_id' => $this->firm->id,
            'matter_id' => $this->matter->id,
            'assigned_to' => $this->clientUser->id,
            'created_by' => $attorney->id,
            'title' => 'Verify Schedule of Commercial Claims',
            'priority' => 'high',
            'status' => 'todo',
        ]);

        // Toggle to completed
        $response = $this->actingAs($this->clientUser)->post(route('portal.tasks.toggle', $task->id));
        $response->assertSessionHas('success');
        $this->assertEquals('completed', $task->fresh()->status);

        // Toggle back to todo
        $response2 = $this->actingAs($this->clientUser)->post(route('portal.tasks.toggle', $task->id));
        $response2->assertSessionHas('success');
        $this->assertEquals('todo', $task->fresh()->status);
    }
}
