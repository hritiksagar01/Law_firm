<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Firm;
use App\Models\SignInHistory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SuperAdminUserManagementAndAuditTest extends TestCase
{
    private User $superadmin;

    private Firm $firm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(PermissionSeeder::class);

        $this->superadmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $this->firm = Firm::firstOrCreate(
            ['slug' => 'test-firm-users'],
            [
                'name' => 'Apex Legal Chambers',
                'email' => 'apex@chambers.com',
                'status' => 'active',
                'currency' => 'INR',
            ]
        );
    }

    public function test_superadmin_can_view_users_index_with_enhanced_fields(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Users');
        $response->assertSee('Platform administrators, firm advocates, staff, and client portal accounts');
        $response->assertSee('Invite a staff member');
        $response->assertSee('Mobile Phone');
        $response->assertSee('Username');
    }

    public function test_superadmin_can_invite_user_with_username_and_mobile_and_generates_uuid(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('admin.users.store'), [
            'firm_id' => $this->firm->id,
            'role' => 'associate',
            'name' => 'Aarav Sharma',
            'first_name' => 'Aarav',
            'middle_name' => 'Kumar',
            'surname' => 'Sharma',
            'username' => 'aarav_sharma',
            'email' => 'aarav@apexlegal.in',
            'mobile' => '+91 98765 43210',
            'title' => 'Senior Associate',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user = User::where('email', 'aarav@apexlegal.in')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->uuid);
        $this->assertEquals('aarav_sharma', $user->username);
        $this->assertEquals('+91 98765 43210', $user->mobile);
        $this->assertEquals('Aarav Kumar Sharma', $user->full_display_name);

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.invited',
            'firm_id' => $this->firm->id,
        ]);
    }

    public function test_superadmin_can_view_user_show_page_with_complete_spec_data(): void
    {
        $user = User::create([
            'firm_id' => $this->firm->id,
            'role' => 'associate',
            'name' => 'Devika Mehra',
            'first_name' => 'Devika',
            'middle_name' => 'Rani',
            'surname' => 'Mehra',
            'username' => 'devika_mehra',
            'email' => 'devika@apexlegal.in',
            'mobile' => '+91 91234 56789',
            'phone' => '+91 11 2345 6789',
            'title' => 'Associate Partner',
            'password' => bcrypt('secret123'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create sign in history
        SignInHistory::create([
            'firm_id' => $this->firm->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'result' => 'signed_in',
            'ip_address' => '103.21.244.2',
            'device' => 'Safari on macOS',
        ]);

        // Create an audit action
        AuditLog::create([
            'firm_id' => $this->firm->id,
            'user_id' => $user->id,
            'action' => 'document.downloaded',
            'action_label' => 'Downloaded document',
            'actor_name' => $user->name,
            'actor_email' => $user->email,
            'record_type' => 'Case brief · MLC/2026/017',
            'ip_address' => '103.21.244.2',
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $response->assertSee($user->full_display_name);
        $response->assertSee('devika_mehra');
        $response->assertSee('Safari on macOS');
        $response->assertSee('103.21.244.2');
        $response->assertSee('Case brief · MLC/2026/017');
        $response->assertSee('Edit Profile');
        $response->assertSee('Resend Invite');
        $response->assertSee('Security Controls');
    }

    public function test_superadmin_can_update_user_details(): void
    {
        $user = User::create([
            'firm_id' => $this->firm->id,
            'role' => 'associate',
            'name' => 'Pooja Nair',
            'email' => 'pooja@apexlegal.in',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->superadmin)->put(route('admin.users.update', $user), [
            'name' => 'Pooja K Nair',
            'first_name' => 'Pooja',
            'middle_name' => 'K',
            'surname' => 'Nair',
            'username' => 'pooja_nair',
            'email' => 'pooja.nair@apexlegal.in',
            'mobile' => '+91 98888 77777',
            'phone' => '+91 11 9999 8888',
            'title' => 'Counsel',
            'firm_id' => $this->firm->id,
            'role' => 'partner',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Pooja K Nair', $user->name);
        $this->assertEquals('pooja_nair', $user->username);
        $this->assertEquals('pooja.nair@apexlegal.in', $user->email);
        $this->assertEquals('partner', $user->role);
        $this->assertEquals('+91 98888 77777', $user->mobile);

        // Verify audit log captured previous and new values
        $log = AuditLog::where('action', 'user.updated')->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('Administration', $log->module);
        $this->assertIsArray($log->previous_value);
        $this->assertIsArray($log->new_value);
    }

    public function test_superadmin_can_change_user_status_and_resend_invitation(): void
    {
        $user = User::create([
            'firm_id' => $this->firm->id,
            'role' => 'associate',
            'name' => 'Rohan Sen',
            'email' => 'rohan@apexlegal.in',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        // Suspend user
        $suspendResponse = $this->actingAs($this->superadmin)->post(route('admin.users.update-status', $user), [
            'status' => 'suspended',
        ]);
        $suspendResponse->assertRedirect();
        $user->refresh();
        $this->assertEquals('suspended', $user->status);

        // Reactivate user
        $activeResponse = $this->actingAs($this->superadmin)->post(route('admin.users.update-status', $user), [
            'status' => 'active',
        ]);
        $activeResponse->assertRedirect();
        $user->refresh();
        $this->assertEquals('active', $user->status);

        // Resend invitation
        $resendResponse = $this->actingAs($this->superadmin)->post(route('admin.users.resend-invitation', $user));
        $resendResponse->assertRedirect();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.invitation_resent',
            'actor_name' => $this->superadmin->name,
        ]);
    }

    public function test_superadmin_can_force_logout_user_sessions(): void
    {
        $user = User::create([
            'firm_id' => $this->firm->id,
            'role' => 'associate',
            'name' => 'Vikram Sethi',
            'email' => 'vikram@apexlegal.in',
            'password' => bcrypt('secret123'),
            'remember_token' => 'old_remember_token',
        ]);

        // Insert dummy session
        DB::table('sessions')->insert([
            'id' => 'dummy_session_123',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
            'payload' => 'payload',
            'last_activity' => time(),
        ]);

        $this->assertEquals(1, DB::table('sessions')->where('user_id', $user->id)->count());

        $response = $this->actingAs($this->superadmin)->post(route('admin.users.sign-out-everywhere', $user));

        $response->assertRedirect();
        $this->assertEquals(0, DB::table('sessions')->where('user_id', $user->id)->count());

        $user->refresh();
        $this->assertNotEquals('old_remember_token', $user->remember_token);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.force_logout',
        ]);
    }

    public function test_granular_rbac_permissions_seeded_and_checked(): void
    {
        $specPermissions = [
            'matter.view',
            'matter.create',
            'matter.edit',
            'matter.close',
            'document.view',
            'document.upload',
            'document.download',
            'document.delete',
            'document.share',
            'document.request',
            'message.view',
            'message.send',
            'task.view',
            'task.create',
            'task.assign',
            'task.complete',
            'note.view',
            'note.create',
            'calendar.view',
            'client.view',
        ];

        foreach ($specPermissions as $permSlug) {
            $this->assertDatabaseHas('permissions', ['slug' => $permSlug]);
        }

        // Test User hasPermission fallback & check
        $lawyer = User::create([
            'firm_id' => $this->firm->id,
            'role' => 'lawyer',
            'name' => 'Advocate Priya',
            'email' => 'priya@apexlegal.in',
            'password' => bcrypt('secret123'),
        ]);

        $this->assertTrue($lawyer->hasPermission('matter.view'));
        $this->assertTrue($lawyer->hasPermission('matter.close'));
        $this->assertTrue($lawyer->hasPermission('document.download'));
        $this->assertTrue($lawyer->hasPermission('task.complete'));
        $this->assertTrue($lawyer->hasPermission('note.create'));
    }

    public function test_audit_logs_support_module_filter_and_enhanced_csv_export(): void
    {
        AuditLog::create([
            'firm_id' => $this->firm->id,
            'user_id' => $this->superadmin->id,
            'action' => 'matter.created',
            'action_label' => 'Opened matter',
            'actor_name' => $this->superadmin->name,
            'actor_email' => $this->superadmin->email,
            'record_type' => 'Matter · 2026-0099',
            'module' => 'Matters',
            'entity_type' => 'App\Models\Matter',
            'entity_id' => 99,
            'description' => 'Created High Court commercial suit dossier',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'previous_value' => null,
            'new_value' => ['stage' => 'pleadings', 'court' => 'Delhi High Court'],
        ]);

        // Filter by module
        $response = $this->actingAs($this->superadmin)->get(route('admin.audit.index', ['module' => 'Matters']));
        $response->assertStatus(200);
        $response->assertSee('Matters');
        $response->assertSee('Opened matter');

        // CSV export with new headers
        $export = $this->actingAs($this->superadmin)->get(route('admin.audit.export', ['module' => 'Matters']));
        $export->assertStatus(200);
        $content = $export->streamedContent();

        $this->assertStringContainsString('Audit ID', $content);
        $this->assertStringContainsString('Module', $content);
        $this->assertStringContainsString('Entity ID', $content);
        $this->assertStringContainsString('Description', $content);
        $this->assertStringContainsString('Previous Value', $content);
        $this->assertStringContainsString('New Value', $content);
        $this->assertStringContainsString('User Agent', $content);
    }

    public function test_admin_layout_has_top_header_bar_with_notifications_and_settings(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Platform Notifications');
        $response->assertSee('System Settings');
        $response->assertSee(route('admin.system.index'));
        $response->assertSee('Users');
    }
}
