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
     * Ensure Client Vikram user and linked client representation dossier exist.
     */
    public function up(): void
    {
        $firm = Firm::first();
        if (! $firm) {
            return;
        }

        // 1. Ensure User exists and has role 'client'
        $user = User::where('email', 'hritik.srivastava28@gmail.com')->first();
        if ($user) {
            $user->update([
                'name' => 'Vikram Malhotra',
                'role' => 'client',
                'password' => Hash::make('12345678'),
                'firm_id' => $user->firm_id ?: $firm->id,
                'status' => 'active',
            ]);
        } else {
            $user = User::create([
                'name' => 'Vikram Malhotra',
                'email' => 'hritik.srivastava28@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'client',
                'firm_id' => $firm->id,
                'status' => 'active',
            ]);
        }

        // 2. Ensure Client record exists and is linked to this user
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', 'hritik.srivastava28@gmail.com')
            ->first();

        if ($client) {
            $client->update([
                'firm_id' => $client->firm_id ?: $firm->id,
                'user_id' => $user->id,
                'name' => $client->name ?: 'Malhotra Enterprises Pvt Ltd',
                'contact_person' => 'Vikram Malhotra',
                'email' => 'hritik.srivastava28@gmail.com',
                'portal_status' => 'active',
                'status' => 'active',
            ]);
        } else {
            $client = Client::create([
                'firm_id' => $firm->id,
                'user_id' => $user->id,
                'name' => 'Malhotra Enterprises Pvt Ltd',
                'contact_person' => 'Vikram Malhotra',
                'email' => 'hritik.srivastava28@gmail.com',
                'phone' => '+91 98200 11928',
                'category' => 'corporate',
                'type' => 'corporate',
                'onboarding_mode' => 'portal_online',
                'portal_status' => 'active',
                'status' => 'active',
                'trust_balance' => 250000.00,
            ]);
        }

        // 3. Ensure this client has at least one matter in the portal
        $hasMatter = Matter::where('client_id', $client->id)->exists();
        if (! $hasMatter) {
            $leadAttorney = User::where('firm_id', $firm->id)
                ->whereIn('role', ['partner', 'associate'])
                ->first();

            Matter::create([
                'firm_id' => $firm->id,
                'client_id' => $client->id,
                'lead_attorney_id' => $leadAttorney?->id,
                'case_number' => 'DEL-COM/2026/019',
                'title' => 'Malhotra Enterprises v. Union of India & Ors.',
                'stage' => 'Pleadings Complete',
                'status' => 'active',
                'practice_area' => 'Commercial Arbitration & Contracts',
                'open_date' => now()->subMonths(2)->toDateString(),
                'court' => 'High Court of Delhi, New Delhi',
                'judge' => 'Hon\'ble Justice K. S. Muralidhar',
                'internal_notes' => 'Commercial contract enforcement and liquidated damages claim.',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Preserved
    }
};
