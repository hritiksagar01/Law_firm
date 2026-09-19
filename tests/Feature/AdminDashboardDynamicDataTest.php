<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\SignInHistory;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\QuireDemoSeeder;
use Tests\TestCase;

class AdminDashboardDynamicDataTest extends TestCase
{
    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(QuireDemoSeeder::class);

        $this->superadmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();
    }

    /**
     * Test super admin dashboard renders with live dynamic data.
     */
    public function test_superadmin_dashboard_renders_with_dynamic_data(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Platform overview');
        $response->assertSee('Platform Tenant Telemetry');
        $response->assertSee('Tenants Active');
        $response->assertSee('Payments collected by month');
        $response->assertSee('Sign-ins, last 7 days');
        $response->assertSee('Recent activity');
        $response->assertSee('Firms needing attention');
    }

    /**
     * Test sign-in telemetry reflects live database records.
     */
    public function test_sign_in_telemetry_reflects_live_records(): void
    {
        // Insert a new failed and successful sign-in
        SignInHistory::create([
            'email' => 'unknown@attacker.example',
            'is_client' => false,
            'result' => 'failed',
            'failure_reason' => 'No such account',
            'ip_address' => '203.0.113.42',
            'device' => 'curl/7.68.0',
            'created_at' => Carbon::now(),
        ]);

        SignInHistory::create([
            'email' => $this->superadmin->email,
            'is_client' => false,
            'result' => 'signed_in',
            'failure_reason' => null,
            'ip_address' => '127.0.0.1',
            'device' => 'Chrome on Windows',
            'created_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('1 failed in the last 24 hours');
    }

    /**
     * Test recent activity feed renders dynamic audit logs.
     */
    public function test_recent_activity_renders_audit_logs(): void
    {
        AuditLog::create([
            'action' => 'firm.provisioned',
            'action_label' => 'Provisioned new firm',
            'actor_name' => 'Super Administrator Test',
            'actor_email' => $this->superadmin->email,
            'record_type' => 'Firm Tenant',
            'created_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Provisioned new firm');
        $response->assertSee('Super Administrator Test');
    }

    /**
     * Test firms needing attention lists suspended firms.
     */
    public function test_firms_needing_attention_displays_suspended_firm(): void
    {
        $suspendedFirm = Firm::create([
            'name' => 'Suspended Chambers LLP',
            'slug' => 'suspended-chambers',
            'email' => 'ops@suspendedchambers.com',
            'status' => 'suspended',
            'currency' => 'USD',
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Suspended Chambers LLP');
        $response->assertSee('Suspended · Requires administrator review');
    }

    /**
     * Test payments collected by month calculates live sums.
     */
    public function test_payments_collected_calculates_sums(): void
    {
        $firm = Firm::first();
        $this->assertNotNull($firm);

        $matter = Matter::first();

        Transaction::create([
            'firm_id' => $firm->id,
            'matter_id' => $matter->id,
            'type' => 'payment',
            'amount' => 5400.00,
            'payment_method' => 'bank_transfer',
            'date' => Carbon::now()->toDateString(),
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('$5.4K');
    }
}
