<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class AdminMailSettingsTest extends TestCase
{
    protected ?string $envBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (file_exists(base_path('.env'))) {
            $this->envBackup = file_get_contents(base_path('.env'));
        }
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        if ($this->envBackup !== null) {
            file_put_contents(base_path('.env'), $this->envBackup);
        }
        parent::tearDown();
    }

    /**
     * Test super admin can access the mail settings view.
     */
    public function test_super_admin_can_view_mail_settings(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->get(route('admin.settings.mail'));

        $response->assertStatus(200);
        $response->assertSee('Platform Mail &amp; SMTP Gateway', false);
        $response->assertSee('Save Mail Configuration');
        $response->assertSee('Clear All Fields');
    }

    /**
     * Test updating mail configuration.
     */
    public function test_super_admin_can_update_mail_settings(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->post(route('admin.settings.mail.update'), [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.testrelay.io',
                'mail_port' => 587,
                'mail_username' => 'testuser@relay.com',
                'mail_password' => 'secret123',
                'mail_encryption' => 'tls',
                'mail_from_address' => 'noreply@vennamraj.com',
                'mail_from_name' => 'Vennamraj Associates Law Firm',
            ]);

        $response->assertRedirect(route('admin.settings.mail'));
        $response->assertSessionHas('success');

        // Assert runtime config was updated
        $this->assertEquals('smtp.testrelay.io', config('mail.mailers.smtp.host'));
        $this->assertEquals(587, config('mail.mailers.smtp.port'));
        $this->assertEquals('testuser@relay.com', config('mail.mailers.smtp.username'));
        $this->assertEquals('secret123', config('mail.mailers.smtp.password'));
        $this->assertEquals('tls', config('mail.mailers.smtp.encryption'));
        $this->assertEquals('noreply@vennamraj.com', config('mail.from.address'));
        $this->assertEquals('Vennamraj Associates Law Firm', config('mail.from.name'));
    }

    /**
     * Test test-mail endpoint with log driver.
     */
    public function test_super_admin_can_send_test_email(): void
    {
        $superAdmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $response = $this->actingAs($superAdmin)
            ->withSession(['is_super_admin' => true])
            ->postJson(route('admin.settings.mail.test'), [
                'test_email' => 'partner@vennamraj.com',
                'mailer' => 'log',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
