<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\QuireDemoSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminConsoleTest extends TestCase
{
    private User $superadmin;

    private Firm $firm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);

        $this->superadmin = User::where('email', 'admin@sharmalegal.in')->firstOrFail();

        $this->firm = Firm::firstOrCreate(
            ['slug' => 'test-firm-admin'],
            [
                'name' => 'Test Legal Chambers',
                'email' => 'test@chambers.com',
                'status' => 'active',
                'currency' => 'INR',
            ]
        );
    }

    public function test_superadmin_can_view_firms_index_with_page3_fields(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.firms.index'));
        $response->assertStatus(200);
        $response->assertSee('Login Name');
        $response->assertSee('Display Name');
        $response->assertSee('Firms');
    }

    public function test_superadmin_can_reset_firm_password(): void
    {
        $firmAdmin = User::firstOrCreate(
            ['email' => 'partner@testchambers.com'],
            [
                'firm_id' => $this->firm->id,
                'name' => 'Partner Admin',
                'password' => Hash::make('oldpassword'),
                'role' => 'partner',
            ]
        );

        $response = $this->actingAs($this->superadmin)->post(route('admin.firms.change-password', $this->firm), [
            'new_password' => 'newsecret123',
            'new_password_confirmation' => 'newsecret123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $firmAdmin->refresh();
        $this->assertTrue(Hash::check('newsecret123', $firmAdmin->password));
    }

    public function test_plans_and_subscriptions_routes_redirect_to_firms(): void
    {
        $plansResponse = $this->actingAs($this->superadmin)->get('/admin/plans');
        $plansResponse->assertRedirect(route('admin.firms.index'));

        $subscriptionsResponse = $this->actingAs($this->superadmin)->get('/admin/subscriptions');
        $subscriptionsResponse->assertRedirect(route('admin.firms.index'));
    }

    public function test_superadmin_can_view_and_update_profile(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.profile.index'));
        $response->assertStatus(200);
        $response->assertSee('User Profile');
        $response->assertSee('Identity Document');

        $updateResponse = $this->actingAs($this->superadmin)->put(route('admin.profile.update'), [
            'first_name' => 'Super',
            'surname' => 'Administrator',
            'name' => 'Super Administrator',
            'email' => $this->superadmin->email,
            'phone' => '+919911978651',
            'phone_type' => 'Home',
            'id_type' => 'Aadhar Card',
            'id_number' => 'LMVE03434',
            'street' => 'SJM Legal Complex',
            'user_city' => 'New Delhi',
            'user_state' => 'Delhi',
            'user_country' => 'India',
            'user_postal_code' => '110001',
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $this->superadmin->refresh();
        $this->assertEquals('Super', $this->superadmin->first_name);
        $this->assertEquals('Administrator', $this->superadmin->surname);
        $this->assertEquals('Aadhar Card', $this->superadmin->id_type);
        $this->assertEquals('LMVE03434', $this->superadmin->id_number);
    }

    public function test_superadmin_can_change_password_with_current_password(): void
    {
        $this->superadmin->update(['password' => Hash::make('mypassword123')]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.profile.change-password'), [
            'current_password' => 'mypassword123',
            'new_password' => 'brandnewpass123',
            'new_password_confirmation' => 'brandnewpass123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->superadmin->refresh();
        $this->assertTrue(Hash::check('brandnewpass123', $this->superadmin->password));
    }

    public function test_superadmin_fails_password_change_with_wrong_current_password(): void
    {
        $this->superadmin->update(['password' => Hash::make('mypassword123')]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.profile.change-password'), [
            'current_password' => 'wrongpassword',
            'new_password' => 'brandnewpass123',
            'new_password_confirmation' => 'brandnewpass123',
        ]);

        $response->assertSessionHasErrors(['current_password']);

        $this->superadmin->refresh();
        $this->assertFalse(Hash::check('brandnewpass123', $this->superadmin->password));
    }

    public function test_superadmin_can_view_matters_directory_and_filter(): void
    {
        $this->seed(QuireDemoSeeder::class);

        $response = $this->actingAs($this->superadmin)->get(route('admin.matters.index'));
        $response->assertStatus(200);
        $response->assertSee('Matters');
        $response->assertSee('Volume and activity across firms, for support and capacity planning');
        $response->assertSee('Platform staff see record types, counts, statuses and matter numbers');
        $response->assertSee('2026-0139');
        $response->assertSee('Hartwell &amp; Okafor LLP', false);

        // Test filter by matter number
        $filterResponse = $this->actingAs($this->superadmin)->get(route('admin.matters.index', ['q' => 'MLC/2026/017']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('MLC/2026/017');
        $filterResponse->assertDontSee('2026-0139');
    }

    public function test_payment_gateways_is_removed_from_admin_navigation(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.matters.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Payment gateways');
    }

    public function test_superadmin_can_view_email_and_sms_and_delivery_log(): void
    {
        $this->seed(QuireDemoSeeder::class);

        $response = $this->actingAs($this->superadmin)->get(route('admin.settings.mail'));
        $response->assertStatus(200);
        $response->assertSee('Email &amp; SMS', false);
        $response->assertSee('Outbound notification delivery for every firm');
        $response->assertSee('Channels');
        $response->assertSee('Send email notifications');
        $response->assertSee('Send SMS notifications');
        $response->assertSee('Providers');
        $response->assertSee('Send a test');
        $response->assertSee('Delivery log');
        $response->assertSee('Logged only');

        // Test sending test email
        $postEmail = $this->actingAs($this->superadmin)->post(route('admin.settings.mail.test-email'), [
            'test_email' => 'test-ops@firm.example',
        ]);
        $postEmail->assertRedirect(route('admin.settings.mail'));
        $this->assertDatabaseHas('delivery_logs', ['recipient' => 'test-ops@firm.example']);
    }

    public function test_superadmin_can_view_audit_log_and_export_csv(): void
    {
        $this->seed(QuireDemoSeeder::class);

        $response = $this->actingAs($this->superadmin)->get(route('admin.audit.index'));
        $response->assertStatus(200);
        $response->assertSee('Audit log');
        $response->assertSee('Append-only record of sign-ins, changes, document access and payments · UTC');
        $response->assertSee('Export CSV');
        $response->assertSee('Signed in');
        $response->assertSee('auth.login');

        // Test CSV export
        $exportResponse = $this->actingAs($this->superadmin)->get(route('admin.audit.export'));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('content-type'));
    }

    public function test_superadmin_can_view_sign_in_history(): void
    {
        $this->seed(QuireDemoSeeder::class);

        $response = $this->actingAs($this->superadmin)->get(route('admin.sign-ins.index'));
        $response->assertStatus(200);
        $response->assertSee('Sign-in history');
        $response->assertSee('Every attempt, successful or not · UTC');
        $response->assertSee('Failed sign-ins by IP, last 24 hours');
        $response->assertSee('Sign-in attempts');
        $response->assertSee('Signed in');
    }
}
