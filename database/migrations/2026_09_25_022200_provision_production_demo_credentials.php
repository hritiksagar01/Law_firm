<?php

use App\Models\Client;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations to guarantee demo quick-fill credentials exist.
     */
    public function up(): void
    {
        // 1. Ensure primary firm exists
        $firm = Firm::firstOrCreate(
            ['slug' => 'vennamraj-associates'],
            [
                'name' => 'Vennamraj Associates, Advocates & Legal Consultants',
                'email' => 'contact@vennamraj.com',
                'phone' => '+91 (11) 4920-8100',
                'address' => 'Chamber No. 412, Lawyers Chambers Block, High Court of Delhi, New Delhi 110003',
                'status' => 'active',
                'currency' => 'INR',
                'default_hourly_rate' => 7500.00,
            ]
        );

        // 2. Super Admin Credentials
        User::updateOrCreate(
            ['email' => 'admin@sharmalegal.in'],
            [
                'firm_id' => $firm->id,
                'name' => 'Platform Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
                'status' => 'active',
                'title' => 'Chief Platform Administrator',
            ]
        );

        // 3. Advocate / Law Firm Credentials
        User::updateOrCreate(
            ['email' => 'hritiksagar.tech@gmail.com'],
            [
                'firm_id' => $firm->id,
                'name' => 'Adv. Rajesh Sharma',
                'password' => Hash::make('12345678'),
                'role' => 'partner',
                'status' => 'active',
                'title' => 'Senior Advocate & Managing Partner',
                'hourly_rate' => 12000.00,
            ]
        );

        // Legacy / fallback advocate account
        User::updateOrCreate(
            ['email' => 'rajesh@sharmalegal.in'],
            [
                'firm_id' => $firm->id,
                'name' => 'Adv. Rajesh Sharma',
                'password' => Hash::make('password123'),
                'role' => 'partner',
                'status' => 'active',
                'title' => 'Senior Advocate & Managing Partner',
                'hourly_rate' => 12000.00,
            ]
        );

        // 4. Client Portal Credentials
        $clientUser = User::updateOrCreate(
            ['email' => 'hritik.srivastava28@gmail.com'],
            [
                'firm_id' => $firm->id,
                'name' => 'Vikram Malhotra',
                'password' => Hash::make('12345678'),
                'role' => 'client',
                'status' => 'active',
                'title' => 'Managing Director, Malhotra Enterprises Pvt Ltd',
            ]
        );

        // 5. Ensure Client Profile Exists & Linked to User
        $client = Client::updateOrCreate(
            ['email' => 'hritik.srivastava28@gmail.com'],
            [
                'firm_id' => $firm->id,
                'user_id' => $clientUser->id,
                'type' => 'corporate',
                'name' => 'Malhotra Enterprises Pvt Ltd',
                'contact_person' => 'Vikram Malhotra',
                'phone' => '+91 98200 11928',
                'status' => 'active',
                'trust_balance' => 250000.00,
            ]
        );

        // 6. Ensure at least one matter exists for the client
        Matter::firstOrCreate(
            ['case_number' => 'DEL-HC-2026-0042'],
            [
                'firm_id' => $firm->id,
                'client_id' => $client->id,
                'title' => 'Malhotra Enterprises v. Union of India & Ors.',
                'practice_area' => 'Commercial Litigation',
                'court_name' => 'High Court of Delhi',
                'judge_name' => 'Hon. Justice Rajiv Shakdher',
                'stage' => 'Discovery',
                'status' => 'active',
                'budget' => 500000.00,
                'opened_at' => now()->subMonths(3)->toDateString(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
