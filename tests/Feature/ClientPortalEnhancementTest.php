<?php

namespace Tests\Feature;

use App\Models\CaseNote;
use App\Models\Client;
use App\Models\Document;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class ClientPortalEnhancementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test client can view their dashboard.
     */
    public function test_client_can_view_portal_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $client = Client::create([
            'firm_id' => $user->firm_id ?: (Firm::first()?->id ?: 1),
            'user_id' => $user->id,
            'name' => 'Acme Corporation',
            'email' => $user->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Client Dashboard');
        $response->assertSee('Active Cases');
        $response->assertSee('Vault Documents');
    }

    /**
     * Test client can view their matter dossier with the 8 tabs.
     */
    public function test_client_can_view_matter_dossier_with_all_tabs(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $firmId = $user->firm_id ?: (Firm::first()?->id ?: 1);

        $client = Client::create([
            'firm_id' => $firmId,
            'user_id' => $user->id,
            'name' => 'Acme Representation',
            'email' => $user->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $matter = Matter::create([
            'firm_id' => $firmId,
            'client_id' => $client->id,
            'case_number' => 'CS(COMM)/789/2026',
            'title' => 'Acme Corp vs Global Logistics Ltd',
            'practice_area' => 'Commercial Litigation',
            'court_name' => 'High Court of Delhi',
            'stage' => 'Notice & Pleadings',
            'status' => 'Open',
            'priority' => 'High',
            'description' => 'Dispute regarding commercial contracts and freight deliveries.',
        ]);

        // Client visible document
        $visibleDoc = Document::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'user_id' => $user->id,
            'title' => 'Plaint and Statement of Truth',
            'filename' => 'plaint_statement_truth.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'category' => 'Pleading',
            'is_client_visible' => true,
            'version' => 1,
        ]);

        // Internal only document (must NOT be shown)
        $internalDoc = Document::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'user_id' => $user->id,
            'title' => 'Internal Strategy Memorandum',
            'filename' => 'internal_strategy_memo.pdf',
            'file_size' => 2048,
            'mime_type' => 'application/pdf',
            'category' => 'Memorandum',
            'is_client_visible' => false,
            'version' => 1,
        ]);

        // Client visible note
        CaseNote::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'user_id' => $user->id,
            'title' => 'Notice Served on Defendant',
            'body' => 'Official notice has been served via registered speed post.',
            'type' => 'client_visible',
            'is_pinned' => true,
        ]);

        // Internal note (must NOT be shown)
        CaseNote::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'user_id' => $user->id,
            'title' => 'Confidential Counsel Discussion',
            'body' => 'Internal evaluation of defendant financial solvency.',
            'type' => 'internal',
            'is_pinned' => false,
        ]);

        $response = $this->actingAs($user)->get(route('portal.matters.show', $matter->id));

        $response->assertStatus(200);
        $response->assertSee('Acme Corp vs Global Logistics Ltd');
        $response->assertSee('CS(COMM)/789/2026');

        // Verify tabs exist
        $response->assertSee('Overview');
        $response->assertSee('Documents');
        $response->assertSee('Requests');
        $response->assertSee('Messages');
        $response->assertSee('Tasks');
        $response->assertSee('Calendar');
        $response->assertSee('Permitted Notes');
        $response->assertSee('Activity');

        // Verify client-visible document is seen, internal is NOT seen
        $response->assertSee('Plaint and Statement of Truth');
        $response->assertDontSee('Internal Strategy Memorandum');

        // Verify client-visible note is seen, internal is NOT seen
        $response->assertSee('Notice Served on Defendant');
        $response->assertDontSee('Confidential Counsel Discussion');

        // Verify no billing / hourly rate leakage
        $response->assertDontSee('hourly_rate');
        $response->assertDontSee('TimeEntry');
    }

    /**
     * Test client cannot access another client's matter (strict data isolation).
     */
    public function test_client_cannot_access_another_clients_matter(): void
    {
        $firmId = Firm::first()?->id ?: 1;

        $clientUser1 = User::factory()->create(['role' => 'client']);
        $client1 = Client::create([
            'firm_id' => $firmId,
            'user_id' => $clientUser1->id,
            'name' => 'Client One',
            'email' => $clientUser1->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $clientUser2 = User::factory()->create(['role' => 'client']);
        $client2 = Client::create([
            'firm_id' => $firmId,
            'user_id' => $clientUser2->id,
            'name' => 'Client Two',
            'email' => $clientUser2->email,
            'status' => 'active',
            'portal_status' => 'active',
        ]);

        $matterClient2 = Matter::create([
            'firm_id' => $firmId,
            'client_id' => $client2->id,
            'case_number' => 'ARB/999/2026',
            'title' => 'Confidential Arbitration Matter',
            'practice_area' => 'Arbitration',
            'court_name' => 'Delhi International Arbitration Centre',
            'stage' => 'Notice & Pleadings',
            'status' => 'Open',
        ]);

        // Client 1 tries to access Client 2's matter
        $response = $this->actingAs($clientUser1)->get(route('portal.matters.show', $matterClient2->id));

        $response->assertStatus(403);
    }
}
