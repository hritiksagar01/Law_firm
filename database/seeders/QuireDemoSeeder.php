<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QuireDemoSeeder extends Seeder
{
    /**
     * Run the Quire Demo Seeder to provide instant 1-click credentials.
     */
    public function run(): void
    {
        // 1. Ensure Demo Firm exists
        $firm = Firm::firstOrCreate(
            ['slug' => 'hartwell-okafor-llp'],
            [
                'name' => 'Hartwell & Okafor LLP',
                'email' => 'contact@hartwellokafor.com',
                'phone' => '+1 (212) 555-0198',
                'address' => '350 Fifth Avenue, 42nd Floor, New York, NY 10118',
                'practice_areas' => [
                    'Commercial Litigation & Appellate',
                    'Corporate Securities & Governance',
                    'Cross-Border Mergers & Acquisitions',
                    'White Collar Defense & Investigations',
                ],
                'currency' => 'USD',
                'default_hourly_rate' => 850.00,
            ]
        );

        $passwordHash = Hash::make('Quire-demo-2026');

        // 2. Firm Partners & Paralegal
        $margaret = User::updateOrCreate(
            ['email' => 'margaret.hartwell@hartwellokafor.com'],
            [
                'firm_id' => $firm->id,
                'name' => 'Margaret Hartwell',
                'password' => $passwordHash,
                'role' => 'partner',
                'title' => 'Managing Partner',
                'hourly_rate' => 1200.00,
                'phone' => '+1 (212) 555-0101',
                'status' => 'active',
            ]
        );

        $daniel = User::updateOrCreate(
            ['email' => 'daniel.okafor@hartwellokafor.com'],
            [
                'firm_id' => $firm->id,
                'name' => 'Daniel Okafor',
                'password' => $passwordHash,
                'role' => 'partner',
                'title' => 'Senior Retained Counsel',
                'hourly_rate' => 1050.00,
                'phone' => '+1 (212) 555-0102',
                'status' => 'active',
            ]
        );

        $luis = User::updateOrCreate(
            ['email' => 'luis.ortega@hartwellokafor.com'],
            [
                'firm_id' => $firm->id,
                'name' => 'Luis Ortega',
                'password' => $passwordHash,
                'role' => 'paralegal',
                'title' => 'Senior Paralegal · Docket Registry',
                'hourly_rate' => 250.00,
                'phone' => '+1 (212) 555-0103',
                'status' => 'active',
            ]
        );

        // 3. Client Users
        $elenaUser = User::updateOrCreate(
            ['email' => 'elena.marsh@marshholdings.com'],
            [
                'firm_id' => $firm->id,
                'name' => 'Elena Marsh',
                'password' => $passwordHash,
                'role' => 'client',
                'title' => 'CEO, Marsh Holdings Corp.',
                'phone' => '+1 (212) 555-0201',
                'status' => 'active',
            ]
        );

        $samUser = User::updateOrCreate(
            ['email' => 'sam.whitaker@whitakertech.io'],
            [
                'firm_id' => $firm->id,
                'name' => 'Sam Whitaker',
                'password' => $passwordHash,
                'role' => 'client',
                'title' => 'Founder & CTO, Whitaker Technologies',
                'phone' => '+1 (415) 555-0301',
                'status' => 'active',
            ]
        );

        $kiranUser = User::updateOrCreate(
            ['email' => 'kiran.rao@raoconsulting.com'],
            [
                'firm_id' => $firm->id,
                'name' => 'Kiran Rao',
                'password' => $passwordHash,
                'role' => 'client',
                'title' => 'Principal, Rao Strategic Advisory',
                'phone' => '+1 (650) 555-0401',
                'status' => 'active',
            ]
        );

        // 4. Platform Admin
        User::updateOrCreate(
            ['email' => 'rowan.blake@quirelegal.com'],
            [
                'firm_id' => $firm->id,
                'name' => 'Rowan Blake',
                'password' => $passwordHash,
                'role' => 'superadmin',
                'title' => 'Platform Administrator',
                'phone' => '+1 (212) 555-0001',
                'status' => 'active',
            ]
        );

        // 5. Client Profiles
        $clientElena = Client::updateOrCreate(
            ['email' => 'elena.marsh@marshholdings.com'],
            [
                'firm_id' => $firm->id,
                'user_id' => $elenaUser->id,
                'type' => 'corporate',
                'name' => 'Marsh Holdings Corp.',
                'contact_person' => 'Elena Marsh',
                'phone' => '+1 (212) 555-0201',
                'tax_id' => 'EIN-12-9876543',
                'address' => '745 Fifth Avenue, New York, NY 10151',
                'trust_balance' => 450000.00,
                'status' => 'active',
            ]
        );

        $clientSam = Client::updateOrCreate(
            ['email' => 'sam.whitaker@whitakertech.io'],
            [
                'firm_id' => $firm->id,
                'user_id' => $samUser->id,
                'type' => 'corporate',
                'name' => 'Whitaker Technologies Inc.',
                'contact_person' => 'Sam Whitaker',
                'phone' => '+1 (415) 555-0301',
                'tax_id' => 'EIN-94-1234567',
                'address' => '500 Howard Street, San Francisco, CA 94105',
                'trust_balance' => 180000.00,
                'status' => 'active',
            ]
        );

        $clientKiran = Client::updateOrCreate(
            ['email' => 'kiran.rao@raoconsulting.com'],
            [
                'firm_id' => $firm->id,
                'user_id' => $kiranUser->id,
                'type' => 'individual',
                'name' => 'Kiran Rao',
                'contact_person' => 'Kiran Rao',
                'phone' => '+1 (650) 555-0401',
                'tax_id' => 'SSN-XXX-XX-8821',
                'address' => '100 University Avenue, Palo Alto, CA 94301',
                'trust_balance' => 75000.00,
                'status' => 'active',
            ]
        );

        // 6. Matters
        Matter::updateOrCreate(
            ['case_number' => 'NY-COMM-2026-0941'],
            [
                'firm_id' => $firm->id,
                'client_id' => $clientElena->id,
                'lead_attorney_id' => $daniel->id,
                'title' => 'Marsh v. Castellan Pharmaceuticals Inc.',
                'practice_area' => 'Commercial Litigation',
                'stage' => 'Interim Injunction',
                'status' => 'active',
                'court_name' => 'New York Supreme Court (Commercial Division)',
                'judge_name' => 'Hon. Evelyn Keller',
                'billing_type' => 'hourly',
                'budget' => 350000.00,
                'opened_at' => now()->subMonths(3),
            ]
        );

        Matter::updateOrCreate(
            ['case_number' => 'DEL-NCLT-2026-1048'],
            [
                'firm_id' => $firm->id,
                'client_id' => $clientSam->id,
                'lead_attorney_id' => $margaret->id,
                'title' => 'Whitaker Technologies Refinancing & Debt Restructuring',
                'practice_area' => 'Corporate Restructuring',
                'stage' => 'Pleadings Filed',
                'status' => 'active',
                'court_name' => 'Delaware Court of Chancery',
                'judge_name' => 'Chancellor Kathaleen McCormick',
                'billing_type' => 'flat_fee',
                'budget' => 220000.00,
                'opened_at' => now()->subMonth(),
            ]
        );
    }
}
