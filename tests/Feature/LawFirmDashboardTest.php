<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class LawFirmDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test /dashboard renders successfully with full dynamic metrics and Quire layout components
     */
    public function test_law_firm_dashboard_renders_with_dynamic_metrics_and_quire_layout(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();
        $firm = $partner->firm;

        $response = $this->actingAs($partner)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee($partner->name);
        $response->assertSee($firm->name);
        $response->assertSee('Your tasks');
        $response->assertSee('Waiting on you');
        $response->assertSee('Clients awaiting a reply');
        $response->assertSee('Client uploads to review');
        $response->assertSee('Recent activity on your matters');
        $response->assertSee('Next two weeks');
        $response->assertSee('open matter');
        $response->assertSee('outstanding');
        $response->assertDontSee('Quire Legal Practice');
    }

    /**
     * Test /app route redirects to /dashboard
     */
    public function test_app_route_redirects_to_dashboard(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($partner)->get('/app');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test task completion toggling from dashboard
     */
    public function test_dashboard_task_toggle(): void
    {
        $partner = User::where('role', 'partner')->firstOrFail();
        $task = Task::where('firm_id', $partner->firm_id)->firstOrFail();
        $initialStatus = $task->status;

        $response = $this->actingAs($partner)->post(route('tasks.toggle', $task->id));

        $response->assertRedirect();
        $task->refresh();
        $this->assertNotEquals($initialStatus, $task->status);
    }
}
