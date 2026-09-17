<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminConsoleTest extends TestCase
{
    private User $superadmin;
    private Firm $firm;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

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

        $this->plan = Plan::firstOrCreate(
            ['slug' => 'starter-test'],
            [
                'name' => 'Starter Practice Test',
                'price' => 5000,
                'interval' => 'monthly',
                'max_users' => 5,
                'max_matters' => 50,
                'max_storage_gb' => 10,
                'is_active' => true,
            ]
        );
    }

    public function test_superadmin_can_view_firms_index_with_page3_fields(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.firms.index'));
        $response->assertStatus(200);
        $response->assertSee('Login Name');
        $response->assertSee('Display Name');
        $response->assertSee('Subscription Plan');
        $response->assertSee('Valid Upto');
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

    public function test_superadmin_can_change_firm_subscription(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('admin.firms.change-subscription', $this->firm), [
            'plan_id' => $this->plan->id,
            'duration_months' => 6,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sub = $this->firm->fresh()->currentSubscription;
        $this->assertNotNull($sub);
        $this->assertEquals($this->plan->id, $sub->plan_id);
        $this->assertEquals('active', $sub->status);
        $this->assertTrue($sub->ends_at->isFuture());
    }

    public function test_superadmin_can_view_subscriptions_renewals_page(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.subscriptions.index'));
        $response->assertStatus(200);
        $response->assertSee('Subscription Lifecycles');
        $response->assertSee('Tenant Subscriptions Registry');
    }

    public function test_superadmin_can_quick_renew_subscription(): void
    {
        $sub = Subscription::create([
            'firm_id' => $this->firm->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addDays(5),
        ]);

        $response = $this->actingAs($this->superadmin)->post(route('admin.subscriptions.renew', $sub), [
            'duration_months' => 3,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sub->refresh();
        $this->assertTrue($sub->ends_at->isFuture());
        $this->assertTrue($sub->ends_at->gt(now()->addMonths(2)));
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

    public function test_superadmin_can_toggle_and_delete_plan(): void
    {
        $plan = Plan::create([
            'name' => 'Temp Plan',
            'slug' => 'temp-plan-' . uniqid(),
            'price' => 1000,
            'interval' => 'monthly',
            'max_users' => 2,
            'max_matters' => 10,
            'max_storage_gb' => 1,
            'is_active' => true,
        ]);

        // Toggle active
        $toggleResponse = $this->actingAs($this->superadmin)->post(route('admin.plans.toggle-active', $plan));
        $toggleResponse->assertRedirect();
        $this->assertFalse($plan->fresh()->is_active);

        // Delete
        $deleteResponse = $this->actingAs($this->superadmin)->delete(route('admin.plans.destroy', $plan));
        $deleteResponse->assertRedirect(route('admin.plans.index'));
        $this->assertNull(Plan::find($plan->id));
    }
}
