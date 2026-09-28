<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class LawAdminDashboardSamplesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test that law admin dashboard renders sample matters requiring attention with hearing alerts and action items.
     */
    public function test_law_admin_dashboard_renders_matters_requiring_attention_samples(): void
    {
        $partner = User::where('email', 'rajesh@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($partner)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Matters requiring attention');
        $response->assertSee('CS (COMM) 412/2026');
        $response->assertSee('Malhotra Enterprises v. Apex Commercial Bank');
        $response->assertSee('W.P.(C) 1892/2026');
        $response->assertSee('Kavita Rao v. Delhi Development Authority (DDA)');
        $response->assertSee('Hearing/Event:');
        $response->assertSee('action item');
        $response->assertSee('Review Matter &rarr;', false);
    }

    /**
     * Test that law admin dashboard renders rich sample tasks with varied priorities, due dates, and matter linkages.
     */
    public function test_law_admin_dashboard_renders_your_tasks_samples(): void
    {
        $partner = User::where('email', 'rajesh@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($partner)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Your tasks');
        $response->assertSee('File Sworn Statement of Truth &amp; Vakalatnama', false);
        $response->assertSee('Draft Rejoinder &amp; Comparative Statement of Claims', false);
        $response->assertSee('Prepare Cross-Examination Brief &amp; Exhibit Dossier', false);
        $response->assertSee('Review Resolution Plan &amp; Committee of Creditors Protocol', false);
        $response->assertSee('Start');
    }

    /**
     * Test that User 12 (hritiksagar.tech@gmail.com) sees sample tasks assigned to them.
     */
    public function test_law_admin_dashboard_renders_tasks_for_active_user(): void
    {
        $user = User::where('email', 'hritiksagar.tech@gmail.com')->firstOrFail();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Your tasks');
        $response->assertSee('Assigned to you');
        $response->assertDontSee("No pending tasks assigned. You're all caught up!");
    }

    /**
     * Test that a newly created firm or firm without prior tasks automatically gets sample tasks.
     */
    public function test_dashboard_auto_populates_sample_tasks_for_firm_with_no_tasks(): void
    {
        $firm = Firm::create([
            'name' => 'Sterling & Associates Chambers',
            'slug' => 'sterling-associates-'.uniqid(),
            'currency' => 'USD',
            'status' => 'active',
        ]);

        $lawyer = User::create([
            'firm_id' => $firm->id,
            'name' => 'Adv. Jonathan Sterling',
            'email' => 'jonathan.'.uniqid().'@sterlingchambers.com',
            'password' => bcrypt('password123'),
            'role' => 'partner',
        ]);

        $response = $this->actingAs($lawyer)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Your tasks');
        $response->assertSee('Assigned to you');
        $response->assertDontSee("No pending tasks assigned. You're all caught up!");
        $response->assertSee('File Notice of Appearance &amp; Corporate Disclosure Statement', false);
    }
}
