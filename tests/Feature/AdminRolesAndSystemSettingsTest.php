<?php

namespace Tests\Feature;

use App\Models\DefaultDocumentCategory;
use App\Models\PlatformSetting;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
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

    /**
     * Test super admin can upload a new platform logo and reset to default.
     */
    public function test_super_admin_can_upload_and_reset_platform_logo(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $fakeLogo = UploadedFile::fake()->image('custom_firm_logo.png', 300, 100);

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->post(route('admin.system.update'), [
                'platform_name' => 'Apex Legal Chambers',
                'support_email' => 'support@apexlegal.in',
                'idle_timeout' => 12,
                'logo' => $fakeLogo,
            ]);

        $response->assertRedirect(route('admin.system.index'));
        $response->assertSessionHas('success');

        $storedLogo = PlatformSetting::get('platform_logo');
        $this->assertNotNull($storedLogo);
        $this->assertStringContainsString('uploads/branding/logo_', $storedLogo);
        $this->assertFileExists(public_path($storedLogo));
        $this->assertStringContainsString($storedLogo, PlatformSetting::logoUrl());

        // Reset logo back to default
        $resetResponse = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->post(route('admin.system.reset-logo'));

        $resetResponse->assertRedirect(route('admin.system.index'));
        $resetResponse->assertSessionHas('success');

        $this->assertNull(PlatformSetting::get('platform_logo'));
        $this->assertEquals(asset('logo.png'), PlatformSetting::logoUrl());
    }

    /**
     * Test super admin can configure Amazon S3 / R2 storage settings.
     */
    public function test_super_admin_can_update_s3_storage_settings(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->post(route('admin.system.update'), [
                'platform_name' => 'Apex Legal Chambers',
                'support_email' => 'support@apexlegal.in',
                'idle_timeout' => 12,
                'storage_driver' => 's3',
                'aws_access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
                'aws_secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
                'aws_default_region' => 'ap-south-1',
                'aws_bucket' => 'apex-litigation-vault',
                'aws_endpoint' => 'https://custom.s3.cloudflarestorage.com',
                'aws_use_path_style_endpoint' => 1,
            ]);

        $response->assertRedirect(route('admin.system.index'));

        $this->assertEquals('s3', PlatformSetting::get('storage_driver'));
        $this->assertEquals('AKIAIOSFODNN7EXAMPLE', PlatformSetting::get('aws_access_key_id'));
        $this->assertEquals('wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY', PlatformSetting::get('aws_secret_access_key'));
        $this->assertEquals('ap-south-1', PlatformSetting::get('aws_default_region'));
        $this->assertEquals('apex-litigation-vault', PlatformSetting::get('aws_bucket'));
        $this->assertEquals('https://custom.s3.cloudflarestorage.com', PlatformSetting::get('aws_endpoint'));
        $this->assertTrue(PlatformSetting::get('aws_use_path_style_endpoint'));

        // Re-saving with masked secret must NOT overwrite the stored secret
        $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->post(route('admin.system.update'), [
                'platform_name' => 'Apex Legal Chambers',
                'support_email' => 'support@apexlegal.in',
                'idle_timeout' => 12,
                'aws_secret_access_key' => '••••••••••••••••',
            ]);

        $this->assertEquals('wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY', PlatformSetting::get('aws_secret_access_key'));
    }

    /**
     * Test S3 connection test endpoint.
     */
    public function test_s3_connection_test_endpoint(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        // 1. Missing credentials -> returns 422 JSON
        $invalidResponse = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->postJson(route('admin.system.test-s3'), [
                'aws_access_key_id' => '',
                'aws_secret_access_key' => '',
                'aws_bucket' => '',
            ]);

        $invalidResponse->assertStatus(422);
        $invalidResponse->assertJson(['success' => false]);

        // 2. Successful mock response
        Http::fake([
            '*' => Http::response('<LocationConstraint>ap-south-1</LocationConstraint>', 200),
        ]);

        $successResponse = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->postJson(route('admin.system.test-s3'), [
                'aws_access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
                'aws_secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
                'aws_default_region' => 'ap-south-1',
                'aws_bucket' => 'test-vault',
            ]);

        $successResponse->assertStatus(200);
        $successResponse->assertJson(['success' => true]);
        $this->assertStringContainsString('Successfully connected', $successResponse->json('message'));
    }

    /**
     * Test super admin can update public footer and legal CMS content.
     */
    public function test_super_admin_can_update_public_and_footer_content(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->post(route('admin.system.update'), [
                'platform_name' => 'Apex Legal Chambers',
                'support_email' => 'support@apexlegal.in',
                'idle_timeout' => 12,
                'footer_headline' => 'Advocacy · Integrity · Counsel',
                'footer_description' => 'Serving High Court and Supreme Court appellate jurisdictions.',
                'footer_copyright' => '© 2026 Apex Legal Chambers. All rights reserved.',
                'about_headline' => 'About Apex Legal Chambers',
                'about_content' => 'Apex Legal Chambers is a premier litigation practice based in New Delhi.',
                'contact_headline' => 'Chambers Registry & Inquiries',
                'contact_email' => 'registry@apexlegal.in',
                'contact_phone' => '+91 11 9876 5432',
                'privacy_headline' => 'Privilege & Confidentiality Policy',
                'terms_headline' => 'Client Engagement Terms',
            ]);

        $response->assertRedirect(route('admin.system.index'));

        $this->assertEquals('Advocacy · Integrity · Counsel', PlatformSetting::get('footer_headline'));
        $this->assertEquals('About Apex Legal Chambers', PlatformSetting::get('about_headline'));
        $this->assertEquals('+91 11 9876 5432', PlatformSetting::get('contact_phone'));
        $this->assertEquals('Privilege & Confidentiality Policy', PlatformSetting::get('privacy_headline'));
        $this->assertEquals('Client Engagement Terms', PlatformSetting::get('terms_headline'));
    }

    /**
     * Test public pages render configured content.
     */
    public function test_public_pages_render_configured_content(): void
    {
        PlatformSetting::set('platform_name', 'Vennamraj Judicial Chambers');
        PlatformSetting::set('about_headline', 'Excellence in Appellate Advocacy');
        PlatformSetting::set('contact_phone', '+91 11 4455 6677');
        PlatformSetting::set('privacy_headline', 'Advocate Privilege Guarantee Under Indian Law');
        PlatformSetting::set('terms_headline', 'Chambers Standard Retainer Terms');

        // 1. About Page
        $aboutResponse = $this->get(route('public.about'));
        $aboutResponse->assertStatus(200);
        $aboutResponse->assertSee('Excellence in Appellate Advocacy');
        $aboutResponse->assertSee('Vennamraj Judicial Chambers');

        // 2. Contact Page
        $contactResponse = $this->get(route('public.contact'));
        $contactResponse->assertStatus(200);
        $contactResponse->assertSee('+91 11 4455 6677');

        // 3. Privacy Page
        $privacyResponse = $this->get(route('public.privacy'));
        $privacyResponse->assertStatus(200);
        $privacyResponse->assertSee('Advocate Privilege Guarantee Under Indian Law');

        // 4. Terms Page
        $termsResponse = $this->get(route('public.terms'));
        $termsResponse->assertStatus(200);
        $termsResponse->assertSee('Chambers Standard Retainer Terms');

        // 5. Login Page footer
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Vennamraj Judicial Chambers');
        $loginResponse->assertSee(route('public.about'));
        $loginResponse->assertSee(route('public.privacy'));
    }
}
