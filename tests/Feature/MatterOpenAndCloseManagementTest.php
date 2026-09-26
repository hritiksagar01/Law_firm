<?php

namespace Tests\Feature;

use App\Models\Matter;
use App\Models\MatterActivity;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class MatterOpenAndCloseManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test matters index displays Open and Closed filter pills and Status column.
     */
    public function test_matters_index_displays_open_and_closed_pills_and_status_column(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($partner)->get(route('matters.index'));

        $response->assertStatus(200);
        $response->assertSee('Open (');
        $response->assertSee('Closed (');
        $response->assertSee('All (');
        $response->assertSee('Status');
    }

    /**
     * Test filtering matters by Open and Closed status.
     */
    public function test_can_filter_matters_by_open_and_closed_status(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();

        // 1. Filter by open
        $openResponse = $this->actingAs($partner)->get(route('matters.index', ['status' => 'open']));
        $openResponse->assertStatus(200);

        // 2. Filter by closed
        $closedResponse = $this->actingAs($partner)->get(route('matters.index', ['status' => 'closed']));
        $closedResponse->assertStatus(200);
    }

    /**
     * Test attorney can close a matter with disposition notes and reopen it.
     */
    public function test_attorney_can_close_and_reopen_matter(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();
        $matter = Matter::where('firm_id', $partner->firm_id)->where('status', 'active')->firstOrFail();

        // 1. Close matter
        $closeResponse = $this->actingAs($partner)->patch(route('matters.status.update', $matter->id), [
            'status' => 'closed',
            'closing_notes' => 'Settlement achieved and decree drawn.',
        ]);

        $closeResponse->assertRedirect();
        $matter->refresh();
        $this->assertSame('closed', $matter->status);
        $this->assertNotNull($matter->closed_at);

        // Assert MatterActivity was recorded
        $this->assertTrue(
            MatterActivity::where('matter_id', $matter->id)
                ->where('activity_type', 'status_changed')
                ->exists()
        );

        // Assert closure case note was created
        $this->assertTrue(
            $matter->caseNotes()
                ->where('title', 'Case Disposition & Closure Note')
                ->exists()
        );

        // 2. Reopen matter
        $reopenResponse = $this->actingAs($partner)->patch(route('matters.status.update', $matter->id), [
            'status' => 'open',
        ]);

        $reopenResponse->assertRedirect();
        $matter->refresh();
        $this->assertSame('active', $matter->status);
        $this->assertNull($matter->closed_at);
    }

    /**
     * Test matter dossier show view renders close and reopen controls.
     */
    public function test_matter_show_displays_close_and_reopen_controls(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();
        $matter = Matter::where('firm_id', $partner->firm_id)->firstOrFail();

        // When active
        $matter->update(['status' => 'active', 'closed_at' => null]);
        $response = $this->actingAs($partner)->get(route('matters.show', $matter->id));
        $response->assertStatus(200);
        $response->assertSee('Close Matter');

        // When closed
        $matter->update(['status' => 'closed', 'closed_at' => now()]);
        $responseClosed = $this->actingAs($partner)->get(route('matters.show', $matter->id));
        $responseClosed->assertStatus(200);
        $responseClosed->assertSee('Reopen Matter');
        $responseClosed->assertSee('This matter dossier is Closed');
    }

    /**
     * Test dashboard renders links to Open and Closed matters.
     */
    public function test_dashboard_links_to_open_and_closed_matters(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($partner)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('matters.index', ['status' => 'open']));
        $response->assertSee(route('matters.index', ['status' => 'closed']));
    }
}
