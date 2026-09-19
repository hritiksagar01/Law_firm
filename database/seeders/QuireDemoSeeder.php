<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\DefaultDocumentCategory;
use App\Models\DeliveryLog;
use App\Models\Document;
use App\Models\Firm;
use App\Models\Matter;
use App\Models\Message;
use App\Models\Permission;
use App\Models\PlatformSetting;
use App\Models\Role;
use App\Models\SignInHistory;
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

        // 7. Notification Delivery Logs
        if (DeliveryLog::count() === 0) {
            $deliveries = [
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 'e•••@example.com', 'notification' => 'Appointment reminder', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-19 13:57:00'],
                ['channel' => 'sms', 'firm_id' => $firm->id, 'recipient' => '+1 ••• ••• 0133', 'notification' => 'Appointment reminder', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-19 13:57:00'],
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 'd•••@hartwellokafor.example', 'notification' => 'A deadline or hearing is approaching', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-19 13:57:00'],
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 'p•••@hartwellokafor.example', 'notification' => 'A deadline or hearing is approaching', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-19 13:57:00'],
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 'l•••@hartwellokafor.example', 'notification' => 'A deadline or hearing is approaching', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-19 13:57:00'],
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 's•••@northgatefreight.example', 'notification' => 'A deadline or hearing is approaching', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-19 13:57:00'],
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 'l•••@hartwellokafor.example', 'notification' => 'A task is assigned', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-17 13:57:00'],
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 'm•••@hartwellokafor.example', 'notification' => 'A task is assigned', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-15 13:57:00'],
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 'd•••@hartwellokafor.example', 'notification' => 'A client sends a message', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-13 04:59:00'],
                ['channel' => 'sms', 'firm_id' => $firm->id, 'recipient' => '+1 ••• ••• 0133', 'notification' => 'A document is requested from a client', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-12 13:00:00'],
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 'e•••@example.com', 'notification' => 'A document is requested from a client', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-12 13:00:00'],
                ['channel' => 'email', 'firm_id' => $firm->id, 'recipient' => 'h•••@hartwellokafor.example', 'notification' => 'Portal invitation', 'provider' => 'None', 'status' => 'logged_only', 'created_at' => '2026-09-11 14:00:00'],
            ];
            foreach ($deliveries as $d) {
                DeliveryLog::create($d);
            }
        }

        // 8. Audit Logs
        if (AuditLog::count() === 0) {
            $audits = [
                ['action' => 'auth.login', 'action_label' => 'Signed in', 'actor_name' => 'Rowan Blake', 'actor_email' => 'admin@sharmalegal.in', 'firm_id' => null, 'record_type' => 'User account', 'ip_address' => '49.43.114.131', 'created_at' => '2026-09-19 17:38:00'],
                ['action' => 'auth.login', 'action_label' => 'Signed in', 'actor_name' => 'Margaret Hartwell', 'actor_email' => 'mhartwell@hartwellokafor.example', 'firm_id' => $firm->id, 'record_type' => 'User account', 'ip_address' => '122.161.172.219', 'created_at' => '2026-09-19 13:25:00'],
                ['action' => 'document.downloaded', 'action_label' => 'Downloaded document', 'actor_name' => 'Client portal user', 'actor_email' => 'elena.marsh@marshholdings.com', 'firm_id' => $firm->id, 'record_type' => 'Document · matter 2026-0118', 'ip_address' => '144.6.61.46', 'created_at' => '2026-09-13 07:03:00'],
                ['action' => 'document.viewed', 'action_label' => 'Previewed document', 'actor_name' => 'Margaret Hartwell', 'actor_email' => 'mhartwell@hartwellokafor.example', 'firm_id' => $firm->id, 'record_type' => 'Document · matter 2026-0118', 'ip_address' => '144.6.61.46', 'created_at' => '2026-09-13 07:03:00'],
                ['action' => 'document.uploaded', 'action_label' => 'Uploaded document', 'actor_name' => 'Client portal user', 'actor_email' => null, 'firm_id' => $firm->id, 'record_type' => 'Document · matter 2026-0118', 'ip_address' => null, 'created_at' => '2026-09-13 01:00:00'],
                ['action' => 'message.sent', 'action_label' => 'Sent message', 'actor_name' => 'Priya Raman', 'actor_email' => 'praman@hartwellokafor.example', 'firm_id' => $firm->id, 'record_type' => 'Message thread · matter 2026-0118', 'ip_address' => null, 'created_at' => '2026-09-12 13:40:00'],
                ['action' => 'thread.created', 'action_label' => 'Started thread', 'actor_name' => 'Client portal user', 'actor_email' => null, 'firm_id' => $firm->id, 'record_type' => 'Message thread · matter 2026-0118', 'ip_address' => null, 'created_at' => '2026-09-11 22:12:00'],
                ['action' => 'user.invited', 'action_label' => 'Invited user', 'actor_name' => 'Margaret Hartwell', 'actor_email' => 'mhartwell@hartwellokafor.example', 'firm_id' => $firm->id, 'record_type' => 'User account', 'ip_address' => null, 'created_at' => '2026-09-11 14:00:00'],
                ['action' => 'matter.update_posted', 'action_label' => 'Posted case update', 'actor_name' => 'Daniel Okafor', 'actor_email' => 'dokafor@hartwellokafor.example', 'firm_id' => $firm->id, 'record_type' => 'Matter · matter 2026-0118', 'ip_address' => null, 'created_at' => '2026-09-10 14:00:00'],
                ['action' => 'payment.recorded', 'action_label' => 'Recorded payment', 'actor_name' => 'Luis Ortega', 'actor_email' => 'lortega@hartwellokafor.example', 'firm_id' => $firm->id, 'record_type' => 'Invoice · matter 2026-0124', 'ip_address' => null, 'created_at' => '2026-08-29 15:00:00'],
                ['action' => 'request.accepted', 'action_label' => 'Accepted submission', 'actor_name' => 'Luis Ortega', 'actor_email' => 'lortega@hartwellokafor.example', 'firm_id' => $firm->id, 'record_type' => 'Document request · matter 2026-0118', 'ip_address' => null, 'created_at' => '2026-08-29 14:00:00'],
                ['action' => 'invoice.sent', 'action_label' => 'Sent invoice', 'actor_name' => 'Margaret Hartwell', 'actor_email' => 'mhartwell@hartwellokafor.example', 'firm_id' => $firm->id, 'record_type' => 'Invoice · matter 2026-0124', 'ip_address' => null, 'created_at' => '2026-08-19 14:00:00'],
                ['action' => 'payment.succeeded', 'action_label' => 'Payment received', 'actor_name' => 'Client portal user', 'actor_email' => null, 'firm_id' => $firm->id, 'record_type' => 'Invoice · matter 2026-0118', 'ip_address' => null, 'created_at' => '2026-08-04 12:14:00'],
                ['action' => 'matter.created', 'action_label' => 'Opened matter', 'actor_name' => 'Anjali Mehta', 'actor_email' => 'anjali@meridianlaw.example', 'firm_id' => $meridianFirm->id, 'record_type' => 'Matter · matter MLC/2026/017', 'ip_address' => null, 'created_at' => '2026-07-17 14:00:00'],
            ];
            foreach ($audits as $a) {
                AuditLog::create($a);
            }
        }

        // 9. Sign-in History
        if (SignInHistory::count() === 0) {
            $signIns = [
                ['email' => 'admin@sharmalegal.in', 'is_client' => false, 'result' => 'signed_in', 'failure_reason' => null, 'ip_address' => '49.43.114.131', 'device' => 'Chrome on Windows', 'firm_id' => null, 'created_at' => '2026-09-19 17:38:00'],
                ['email' => 'mhartwell@hartwellokafor.example', 'is_client' => false, 'result' => 'signed_in', 'failure_reason' => null, 'ip_address' => '122.161.172.219', 'device' => 'Chrome on Windows', 'firm_id' => $firm->id, 'created_at' => '2026-09-19 13:25:00'],
                ['email' => 'admin@sharmalegal.in', 'is_client' => false, 'result' => 'signed_in', 'failure_reason' => null, 'ip_address' => '106.216.80.207', 'device' => 'Chrome on macOS', 'firm_id' => null, 'created_at' => '2026-09-14 05:27:00'],
                ['email' => 'e•••@example.com', 'is_client' => true, 'result' => 'signed_in', 'failure_reason' => null, 'ip_address' => '49.43.114.131', 'device' => 'Chrome on Windows', 'firm_id' => $firm->id, 'created_at' => '2026-09-13 20:11:00'],
                ['email' => 'abc@gmail.com', 'is_client' => false, 'result' => 'failed', 'failure_reason' => 'No such account', 'ip_address' => '49.43.114.131', 'device' => 'Chrome on Windows', 'firm_id' => null, 'created_at' => '2026-09-13 16:05:00'],
                ['email' => 'mhartwell@hartwellokafor.example', 'is_client' => false, 'result' => 'signed_in', 'failure_reason' => null, 'ip_address' => '157.50.101.205', 'device' => 'Safari on iOS', 'firm_id' => $firm->id, 'created_at' => '2026-09-13 15:45:00'],
                ['email' => 'k•••@example.in', 'is_client' => true, 'result' => 'signed_in', 'failure_reason' => null, 'ip_address' => '49.204.212.107', 'device' => 'Chrome on Windows', 'firm_id' => $meridianFirm->id, 'created_at' => '2026-09-13 10:58:00'],
                ['email' => 'e•••@example.com', 'is_client' => true, 'result' => 'failed', 'failure_reason' => 'Wrong password', 'ip_address' => '198.51.100.23', 'device' => 'iOS', 'firm_id' => $firm->id, 'created_at' => '2026-09-12 23:03:00'],
                ['email' => 'admin@hartwellokafor.example', 'is_client' => false, 'result' => 'failed', 'failure_reason' => 'No such account', 'ip_address' => '185.220.101.9', 'device' => 'python-requests', 'firm_id' => null, 'created_at' => '2026-09-09 07:11:00'],
                ['email' => 'k•••@example.in', 'is_client' => true, 'result' => 'signed_in', 'failure_reason' => null, 'ip_address' => '49.36.12.8', 'device' => 'Android', 'firm_id' => $meridianFirm->id, 'created_at' => '2026-09-09 01:00:00'],
            ];
            foreach ($signIns as $s) {
                SignInHistory::create($s);
            }
        }

        // 10. Default Document Categories
        if (DefaultDocumentCategory::count() === 0) {
            $categories = [
                ['sort_order' => 0, 'name' => 'Engagement', 'description' => 'Engagement letters, fee agreements, conflict waivers'],
                ['sort_order' => 1, 'name' => 'Pleadings', 'description' => 'Complaints, answers, motions and briefs'],
                ['sort_order' => 2, 'name' => 'Court orders', 'description' => 'Orders, judgments and notices from the court'],
                ['sort_order' => 3, 'name' => 'Correspondence', 'description' => 'Letters to and from opposing counsel and third parties'],
                ['sort_order' => 4, 'name' => 'Work product', 'description' => 'Internal memos, research and strategy — never share with clients'],
                ['sort_order' => 5, 'name' => 'Discovery', 'description' => 'Requests, responses, deposition transcripts'],
                ['sort_order' => 6, 'name' => 'Evidence & exhibits', 'description' => 'Photos, records and exhibits'],
                ['sort_order' => 7, 'name' => 'Contracts', 'description' => 'Agreements, leases, amendments'],
                ['sort_order' => 8, 'name' => 'Client records', 'description' => 'Identification, financial and medical records from the client'],
                ['sort_order' => 9, 'name' => 'Billing', 'description' => 'Statements and receipts'],
            ];
            foreach ($categories as $cat) {
                DefaultDocumentCategory::create($cat);
            }
        }

        // 11. Platform Settings
        PlatformSetting::set('platform_name', config('legal.app_name', 'Vennamraj Associates'));
        PlatformSetting::set('support_email', 'contact@vennamraj.com');
        PlatformSetting::set('idle_timeout', 12);
        if (PlatformSetting::get('maintenance_notice') === null) {
            PlatformSetting::set('maintenance_notice', '');
        }

        // 12. Quire Standard Permissions Matrix
        $quirePermissions = [
            // Matters
            ['module' => 'Matters', 'name' => 'See every matter, not only assigned ones', 'slug' => 'matters.view_all', 'attorney' => false, 'paralegal' => false],
            ['module' => 'Matters', 'name' => 'Open new matters', 'slug' => 'matters.create', 'attorney' => true, 'paralegal' => false],
            ['module' => 'Matters', 'name' => 'Edit matter details, team and status', 'slug' => 'matters.edit', 'attorney' => true, 'paralegal' => false],
            // Clients
            ['module' => 'Clients', 'name' => 'View client records', 'slug' => 'clients.view', 'attorney' => true, 'paralegal' => true],
            ['module' => 'Clients', 'name' => 'Create and edit clients', 'slug' => 'clients.manage', 'attorney' => true, 'paralegal' => false],
            ['module' => 'Clients', 'name' => 'Invite clients to the portal', 'slug' => 'clients.invite', 'attorney' => true, 'paralegal' => false],
            // Documents
            ['module' => 'Documents', 'name' => 'Upload documents and new versions', 'slug' => 'documents.upload', 'attorney' => true, 'paralegal' => true],
            ['module' => 'Documents', 'name' => 'Share documents with clients', 'slug' => 'documents.share', 'attorney' => true, 'paralegal' => false],
            ['module' => 'Documents', 'name' => 'Restrict documents to named people', 'slug' => 'documents.restrict', 'attorney' => true, 'paralegal' => false],
            ['module' => 'Documents', 'name' => 'Archive documents', 'slug' => 'documents.archive', 'attorney' => true, 'paralegal' => false],
            ['module' => 'Documents', 'name' => 'Request and review client documents', 'slug' => 'requests.manage', 'attorney' => true, 'paralegal' => true],
            // Communication
            ['module' => 'Communication', 'name' => 'Message clients', 'slug' => 'messages.client', 'attorney' => true, 'paralegal' => true],
            ['module' => 'Communication', 'name' => 'Internal team threads', 'slug' => 'messages.internal', 'attorney' => true, 'paralegal' => true],
            ['module' => 'Communication', 'name' => 'Write internal notes', 'slug' => 'notes.manage', 'attorney' => true, 'paralegal' => true],
            ['module' => 'Communication', 'name' => 'Post case updates to clients', 'slug' => 'updates.post', 'attorney' => true, 'paralegal' => false],
            // Work
            ['module' => 'Work', 'name' => 'Create and assign tasks and dates', 'slug' => 'tasks.manage', 'attorney' => true, 'paralegal' => true],
            ['module' => 'Work', 'name' => 'Build forms and send them to clients', 'slug' => 'forms.manage', 'attorney' => true, 'paralegal' => false],
            // Billing
            ['module' => 'Billing', 'name' => 'Record time', 'slug' => 'time.log', 'attorney' => true, 'paralegal' => true],
            ['module' => 'Billing', 'name' => 'View invoices and payments', 'slug' => 'billing.view', 'attorney' => true, 'paralegal' => true],
            ['module' => 'Billing', 'name' => 'Create, send and void invoices; record payments', 'slug' => 'billing.manage', 'attorney' => true, 'paralegal' => false],
            ['module' => 'Billing', 'name' => 'Issue refunds', 'slug' => 'payments.refund', 'attorney' => false, 'paralegal' => false],
            // Administration
            ['module' => 'Administration', 'name' => 'Read the firm audit log', 'slug' => 'audit.view', 'attorney' => false, 'paralegal' => false],
            ['module' => 'Administration', 'name' => 'Invite staff and change roles', 'slug' => 'team.manage', 'attorney' => false, 'paralegal' => false],
            ['module' => 'Administration', 'name' => 'Firm settings, categories, payments, notifications', 'slug' => 'settings.manage', 'attorney' => false, 'paralegal' => false],
        ];

        $attorneyPermIds = [];
        $paralegalPermIds = [];
        $adminPermIds = [];

        foreach ($quirePermissions as $qp) {
            $perm = Permission::updateOrCreate(
                ['slug' => $qp['slug']],
                ['name' => $qp['name'], 'module' => $qp['module']]
            );
            $adminPermIds[] = $perm->id;
            if ($qp['attorney']) {
                $attorneyPermIds[] = $perm->id;
            }
            if ($qp['paralegal']) {
                $paralegalPermIds[] = $perm->id;
            }
        }

        // Attach default permissions to system roles if present
        $adminRole = Role::where('slug', 'admin')->whereNull('firm_id')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync($adminPermIds);
        }

        $lawyerRole = Role::whereIn('slug', ['lawyer', 'attorney'])->whereNull('firm_id')->first();
        if ($lawyerRole) {
            $lawyerRole->permissions()->sync($attorneyPermIds);
        }

        $paralegalRole = Role::where('slug', 'paralegal')->whereNull('firm_id')->first();
        if ($paralegalRole) {
            $paralegalRole->permissions()->sync($paralegalPermIds);
        }
    }
}
