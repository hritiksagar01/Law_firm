<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Document;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\Message;
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

        // 6. Matters & Meridian Law Chambers
        $meridianFirm = Firm::firstOrCreate(
            ['slug' => 'meridian-law-chambers'],
            [
                'name' => 'Meridian Law Chambers',
                'email' => 'admin@meridianlaw.example',
                'phone' => '+1 (415) 555-0188',
                'address' => '200 California Street, Suite 500, San Francisco, CA 94111',
                'practice_areas' => ['Consumer & regulatory complaints', 'Commercial litigation'],
                'currency' => 'USD',
                'status' => 'active',
            ]
        );

        $mattersData = [
            [
                'case_number' => '2026-0139',
                'firm_id' => $firm->id,
                'client_id' => $clientElena->id,
                'lead_attorney_id' => $daniel->id,
                'title' => 'Estate Administration & Trust Execution',
                'practice_area' => 'Estates & trusts',
                'stage' => 'Intake',
                'status' => 'intake',
                'billing_type' => 'hourly',
                'budget' => 75000.00,
                'opened_at' => '2026-09-04',
                'closed_at' => null,
                'docs_count' => 1,
                'msgs_count' => 0,
                'updated_at' => '2026-09-06 14:30:00',
            ],
            [
                'case_number' => '2026-0131',
                'firm_id' => $firm->id,
                'client_id' => $clientElena->id,
                'lead_attorney_id' => $daniel->id,
                'title' => 'Commercial Contract & Supply Chain Litigation',
                'practice_area' => 'Commercial litigation',
                'stage' => 'Discovery',
                'status' => 'open',
                'billing_type' => 'hourly',
                'budget' => 250000.00,
                'opened_at' => '2026-08-04',
                'closed_at' => null,
                'docs_count' => 3,
                'msgs_count' => 2,
                'updated_at' => '2026-09-09 16:45:00',
            ],
            [
                'case_number' => 'MLC/2026/017',
                'firm_id' => $meridianFirm->id,
                'client_id' => $clientSam->id,
                'lead_attorney_id' => $margaret->id,
                'title' => 'State Regulatory Compliance & Consumer Defense',
                'practice_area' => 'Consumer & regulatory complaints',
                'stage' => 'Preliminary',
                'status' => 'open',
                'billing_type' => 'hourly',
                'budget' => 120000.00,
                'opened_at' => '2026-07-17',
                'closed_at' => null,
                'docs_count' => 1,
                'msgs_count' => 0,
                'updated_at' => '2026-09-07 10:20:00',
            ],
            [
                'case_number' => '2026-0124',
                'firm_id' => $firm->id,
                'client_id' => $clientSam->id,
                'lead_attorney_id' => $margaret->id,
                'title' => 'Commercial Property Acquisition & Title Conveyance',
                'practice_area' => 'Real estate',
                'stage' => 'Pending Review',
                'status' => 'pending',
                'billing_type' => 'flat_fee',
                'budget' => 95000.00,
                'opened_at' => '2026-07-03',
                'closed_at' => null,
                'docs_count' => 1,
                'msgs_count' => 2,
                'updated_at' => '2026-09-10 17:00:00',
            ],
            [
                'case_number' => '2026-0118',
                'firm_id' => $firm->id,
                'client_id' => $clientElena->id,
                'lead_attorney_id' => $daniel->id,
                'title' => 'Civil Partnership Dissolution & Asset Settlement',
                'practice_area' => 'Civil litigation',
                'stage' => 'Pleadings',
                'status' => 'open',
                'billing_type' => 'hourly',
                'budget' => 180000.00,
                'opened_at' => '2026-06-11',
                'closed_at' => null,
                'docs_count' => 9,
                'msgs_count' => 8,
                'updated_at' => '2026-09-13 15:10:00',
            ],
            [
                'case_number' => '2026-0102',
                'firm_id' => $firm->id,
                'client_id' => $clientKiran->id,
                'lead_attorney_id' => $daniel->id,
                'title' => 'Executive Severance Arbitration & Non-Compete',
                'practice_area' => 'Employment',
                'stage' => 'Resolved',
                'status' => 'closed',
                'billing_type' => 'flat_fee',
                'budget' => 85000.00,
                'opened_at' => '2026-02-20',
                'closed_at' => '2026-08-24',
                'docs_count' => 0,
                'msgs_count' => 0,
                'updated_at' => '2026-08-24 16:00:00',
            ],
        ];

        foreach ($mattersData as $m) {
            $docsCount = $m['docs_count'];
            $msgsCount = $m['msgs_count'];
            unset($m['docs_count'], $m['msgs_count']);

            $matterModel = Matter::updateOrCreate(
                ['case_number' => $m['case_number']],
                $m
            );

            // Seed placeholder docs if needed
            $existingDocs = Document::where('matter_id', $matterModel->id)->count();
            for ($i = $existingDocs; $i < $docsCount; $i++) {
                Document::create([
                    'firm_id' => $matterModel->firm_id,
                    'matter_id' => $matterModel->id,
                    'user_id' => $matterModel->lead_attorney_id,
                    'title' => 'Document '.($i + 1).' - '.$matterModel->case_number,
                    'filename' => 'document_'.($i + 1).'.pdf',
                    'file_path' => 'documents/demo_'.$matterModel->id.'_'.$i.'.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => 1024 * 250,
                ]);
            }

            // Seed placeholder messages if needed
            $existingMsgs = Message::where('matter_id', $matterModel->id)->count();
            for ($j = $existingMsgs; $j < $msgsCount; $j++) {
                Message::create([
                    'firm_id' => $matterModel->firm_id,
                    'matter_id' => $matterModel->id,
                    'sender_id' => $matterModel->lead_attorney_id,
                    'body' => 'Docket Notice '.($j + 1).': Status update regarding matter '.$matterModel->case_number,
                    'is_privileged' => true,
                ]);
            }
        }
    }
}
