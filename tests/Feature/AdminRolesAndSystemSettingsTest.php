<?php

namespace Tests\Feature;

use App\Models\DefaultDocumentCategory;
use App\Models\PlatformSetting;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class AdminRolesAndSystemSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test super admin can access the roles & categories page.
     */
    public function test_super_admin_can_view_roles_and_categories_page(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->get(route('admin.roles.index'));

        $response->assertStatus(200);
        $response->assertSee('Roles &amp; categories', false);
        $response->assertSee('Default role permissions');
        $response->assertSee('Default document categories');
        $response->assertSee('Engagement');
        $response->assertSee('Pleadings');
    }

    /**
     * Test super admin can update default role permissions.
     */
    public function test_super_admin_can_update_default_role_permissions(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->post(route('admin.roles.permissions'), [
                'attorney_permissions' => ['matters.create', 'clients.view', 'time.log'],
                'paralegal_permissions' => ['clients.view', 'time.log'],
            ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $lawyerRole = Role::whereIn('slug', ['lawyer', 'attorney'])->whereNull('firm_id')->first();
        $this->assertNotNull($lawyerRole);
        $this->assertTrue($lawyerRole->hasPermission('matters.create'));
        $this->assertTrue($lawyerRole->hasPermission('clients.view'));
        $this->assertFalse($lawyerRole->hasPermission('matters.view_all'));

        $paralegalRole = Role::where('slug', 'paralegal')->whereNull('firm_id')->first();
        $this->assertNotNull($paralegalRole);
        $this->assertTrue($paralegalRole->hasPermission('clients.view'));
        $this->assertFalse($paralegalRole->hasPermission('matters.create'));
    }

    /**
     * Test super admin can create, update, and delete default document categories.
     */
    public function test_super_admin_can_manage_default_document_categories(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        // 1. Create category
        $createResponse = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->post(route('admin.roles.categories.store'), [
                'name' => 'Corporate Filings',
                'description' => 'SEC and state incorporation filings',
                'sort_order' => 15,
            ]);

        $createResponse->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('default_document_categories', [
            'name' => 'Corporate Filings',
            'sort_order' => 15,
        ]);

        $category = DefaultDocumentCategory::where('name', 'Corporate Filings')->firstOrFail();

        // 2. Update category
        $updateResponse = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->put(route('admin.roles.categories.update', $category->id), [
                'name' => 'Corporate & Regulatory Filings',
                'description' => 'Updated SEC filings',
                'sort_order' => 12,
            ]);

        $updateResponse->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('default_document_categories', [
            'id' => $category->id,
            'name' => 'Corporate & Regulatory Filings',
            'sort_order' => 12,
        ]);

        // 3. Delete category
        $deleteResponse = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->delete(route('admin.roles.categories.destroy', $category->id));

        $deleteResponse->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('default_document_categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test super admin can view system settings page.
     */
    public function test_super_admin_can_view_system_settings_page(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->get(route('admin.system.index'));

        $response->assertStatus(200);
        $response->assertSee('System settings');
        $response->assertSee('Platform-wide values');
        $response->assertSee('General');
        $response->assertSee('Deployment');
        $response->assertSee('Platform name');
        $response->assertSee('Support email');
        $response->assertSee('Idle session timeout');
    }

    /**
     * Test super admin can update system settings.
     */
    public function test_super_admin_can_update_system_settings(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->post(route('admin.system.update'), [
                'platform_name' => 'Quire Test Platform',
                'support_email' => 'ops@quiretest.example',
                'maintenance_notice' => 'System update scheduled for midnight.',
                'idle_timeout' => 24,
            ]);

        $response->assertRedirect(route('admin.system.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('Quire Test Platform', PlatformSetting::get('platform_name'));
        $this->assertEquals('ops@quiretest.example', PlatformSetting::get('support_email'));
        $this->assertEquals('System update scheduled for midnight.', PlatformSetting::get('maintenance_notice'));
        $this->assertEquals(24, PlatformSetting::get('idle_timeout'));
    }

    /**
     * Test non-admin user cannot access roles or system settings.
     */
    public function test_regular_user_cannot_access_roles_or_system_settings(): void
    {
        $regularUser = User::where('email', '!=', 'admin@sharmalegal.in')
            ->whereNotNull('firm_id')
            ->firstOrFail();

        $rolesResponse = $this->actingAs($regularUser)
            ->get(route('admin.roles.index'));
        $rolesResponse->assertRedirect();

        $systemResponse = $this->actingAs($regularUser)
            ->get(route('admin.system.index'));
        $systemResponse->assertRedirect();
    }
}
