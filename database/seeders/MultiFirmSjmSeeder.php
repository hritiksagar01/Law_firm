<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Matter;
use App\Models\Opinion;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MultiFirmSjmSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed SaaS Subscription Plans (PDF Pages 3, 20, 21)
        $starterPlan = Plan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter Chambers Tier',
                'description' => 'Ideal for solo advocates and boutique chambers managing up to 3 counsel.',
                'price' => 4999.00,
                'interval' => 'monthly',
                'max_users' => 3,
                'max_matters' => 50,
                'max_storage_gb' => 10,
                'features' => [
                    'Case Dossier Management',
                    'Client Portal Access',
                    'Basic Cause List & Calendar',
                    'Standard Invoicing',
                ],
                'is_active' => true,
            ]
        );

        $proPlan = Plan::firstOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Professional Chambers Practice',
                'description' => 'For fast-growing litigation teams with up to 15 advocates and advanced docketing.',
                'price' => 14999.00,
                'interval' => 'monthly',
                'max_users' => 15,
                'max_matters' => 300,
                'max_storage_gb' => 100,
                'features' => [
                    'All Starter Features',
                    'Full RBAC Granular Permissions',
                    '4-Stage Legal Opinions Workflow',
                    'Client Appointment System',
                    'GST Invoicing & Expense Vault',
                    'Bank Activity Reports',
                ],
                'is_active' => true,
            ]
        );

        $enterprisePlan = Plan::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise Law Firm Platform',
                'description' => 'Unlimited capacity, multi-branch chambers, dedicated audit logs, and priority SLA.',
                'price' => 39999.00,
                'interval' => 'monthly',
                'max_users' => 100,
                'max_matters' => 5000,
                'max_storage_gb' => 1000,
                'features' => [
                    'All Professional Features',
                    'Unlimited Advocates & Clients',
                    'Multi-Branch Consolidated Ledgers',
                    'Automated Court Cause List Sync',
                    'Custom Legal ID Generation',
                    'Supabase High-Speed Transaction Pooler',
                    'Dedicated 24/7 Priority SLA',
                ],
                'is_active' => true,
            ]
        );

        // 2. Firm 1: Vennamraj Associates, Advocates & Legal Consultants
        $firm1 = Firm::firstOrCreate(
            ['slug' => 'vennamraj-associates'],
            [
                'name' => 'Vennamraj Associates, Advocates & Legal Consultants',
                'email' => 'contact@vennamraj.com',
                'phone' => '+91 (11) 4920-8100',
                'address' => 'Chamber No. 412, Lawyers Chambers Block, High Court of Delhi, New Delhi 110003',
                'practice_areas' => [
                    'Commercial Litigation & Arbitration',
                    'Corporate & Insolvency (IBC / NCLT)',
                    'Criminal Defense & Bail Matters',
                    'Banking, Cheque Bounce (Sec 138 NI Act) & DRT',
                ],
                'currency' => 'INR',
                'default_hourly_rate' => 7500.00,
                'status' => 'active',
            ]
        );

        // Subscription for Firm 1
        Subscription::updateOrCreate(
            ['firm_id' => $firm1->id],
            [
                'plan_id' => $enterprisePlan->id,
                'status' => 'active',
                'starts_at' => Carbon::now()->subMonths(3),
                'ends_at' => Carbon::now()->addMonths(9),
            ]
        );

        // Bank Account for Firm 1
        $bank1 = BankAccount::firstOrCreate(
            ['firm_id' => $firm1->id, 'account_number' => '50200039281745'],
            [
                'bank_name' => 'HDFC Bank Ltd',
                'account_name' => 'Vennamraj Associates Chambers Current Account',
                'account_type' => 'current',
                'ifsc_code' => 'HDFC0000043',
                'branch' => 'High Court of Delhi Branch',
                'opening_balance' => 500000.00,
                'current_balance' => 842500.00,
                'is_active' => true,
            ]
        );

        // Staff User for Firm 1
        $vennamrajStaff = User::firstOrCreate(
            ['email' => 'sunita@sharmalegal.in'],
            [
                'firm_id' => $firm1->id,
                'name' => 'Sunita Rao',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'title' => 'Chambers Accountant & Billing Manager',
                'hourly_rate' => 1200.00,
                'phone' => '+91 98111 44556',
            ]
        );

        // 3. Firm 2: SJM Legal Chambers Pvt Ltd (Second Multi-Tenant Law Firm)
        $firm2 = Firm::firstOrCreate(
            ['slug' => 'sjm-legal-chambers'],
            [
                'name' => 'SJM Legal Chambers Pvt Ltd',
                'email' => 'contact@sjmlegal.in',
                'phone' => '+91 (11) 2334-9988',
                'address' => 'Suite 804, Barakhamba Tower, Barakhamba Road, Connaught Place, New Delhi 110001',
                'practice_areas' => [
                    'Corporate Advisory & M&A',
                    'Domestic & International Commercial Arbitration',
                    'Insolvency, Bankruptcy & Restructuring (NCLT / NCLAT)',
                    'Constitutional & Writ Jurisdiction',
                    'Intellectual Property & Technology Disputes',
                ],
                'currency' => 'INR',
                'default_hourly_rate' => 8500.00,
                'status' => 'active',
            ]
        );

        // Subscription for Firm 2
        Subscription::updateOrCreate(
            ['firm_id' => $firm2->id],
            [
                'plan_id' => $proPlan->id,
                'status' => 'active',
                'starts_at' => Carbon::now()->subMonths(1),
                'ends_at' => Carbon::now()->addMonths(11),
            ]
        );

        // Bank Account for Firm 2
        $bank2 = BankAccount::firstOrCreate(
            ['firm_id' => $firm2->id, 'account_number' => '000705039281'],
            [
                'bank_name' => 'ICICI Bank Ltd',
                'account_name' => 'SJM Legal Chambers Pvt Ltd Operating Account',
                'account_type' => 'current',
                'ifsc_code' => 'ICIC0000007',
                'branch' => 'Connaught Place Branch, New Delhi',
                'opening_balance' => 200000.00,
                'current_balance' => 520000.00,
                'is_active' => true,
            ]
        );

        // Firm 2 Lawyers & Personnel
        $sanjeev = User::firstOrCreate(
            ['email' => 'sanjeev@sjmlegal.in'],
            [
                'firm_id' => $firm2->id,
                'name' => 'Adv. Sanjeev J. Mathur',
                'password' => Hash::make('password123'),
                'role' => 'partner',
                'title' => 'Managing Director & Lead Counsel',
                'hourly_rate' => 14000.00,
                'phone' => '+91 98101 22334',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $neha = User::firstOrCreate(
            ['email' => 'neha@sjmlegal.in'],
            [
                'firm_id' => $firm2->id,
                'name' => 'Adv. Neha Singhania',
                'password' => Hash::make('password123'),
                'role' => 'associate',
                'title' => 'Senior Associate (Arbitration & NCLT)',
                'hourly_rate' => 6500.00,
                'phone' => '+91 98202 33445',
                'avatar_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $rohan = User::firstOrCreate(
            ['email' => 'rohan@sjmlegal.in'],
            [
                'firm_id' => $firm2->id,
                'name' => 'Rohan Deshmukh',
                'password' => Hash::make('password123'),
                'role' => 'paralegal',
                'title' => 'Senior Legal Researcher & Munshi',
                'hourly_rate' => 1800.00,
                'phone' => '+91 98303 44556',
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $aarti = User::firstOrCreate(
            ['email' => 'aarti@sjmlegal.in'],
            [
                'firm_id' => $firm2->id,
                'name' => 'Aarti Kapoor',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'title' => 'Practice Administrator & Registrar',
                'hourly_rate' => 1500.00,
                'phone' => '+91 98404 55667',
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $deepakUser = User::firstOrCreate(
            ['email' => 'deepak@apexindustrial.in'],
            [
                'firm_id' => $firm2->id,
                'name' => 'Deepak Gupta',
                'password' => Hash::make('password123'),
                'role' => 'client',
                'title' => 'Executive Director, Apex Industrial Solutions',
                'phone' => '+91 99201 88776',
            ]
        );

        // Firm 2 Clients
        $clientApex = Client::firstOrCreate(
            ['firm_id' => $firm2->id, 'email' => 'deepak@apexindustrial.in'],
            [
                'user_id' => $deepakUser->id,
                'name' => 'Apex Industrial Solutions Pvt Ltd',
                'type' => 'corporate',
                'contact_person' => 'Deepak Gupta',
                'phone' => '+91 99201 88776',
                'address' => 'Plot 42, Sector 18, Udyog Vihar, Gurugram 122015',
                'tax_id' => '07AAACA8876K1Z3',
                'trust_balance' => 150000.00,
                'status' => 'active',
            ]
        );

        $clientSynergy = Client::firstOrCreate(
            ['firm_id' => $firm2->id, 'email' => 'ritu@synergyfintech.io'],
            [
                'name' => 'Synergy FinTech Labs LLP',
                'type' => 'corporate',
                'contact_person' => 'Ritu Mehra',
                'phone' => '+91 98114 99001',
                'address' => 'Level 5, DLF Cyber City, Cyber Hub, Gurugram 122002',
                'tax_id' => '06AAGCS9912F1Z8',
                'trust_balance' => 75000.00,
                'status' => 'active',
            ]
        );

        $clientHorizon = Client::firstOrCreate(
            ['firm_id' => $firm2->id, 'email' => 'karan@bluehorizonrealty.in'],
            [
                'name' => 'Blue Horizon Realty Developers',
                'type' => 'corporate',
                'contact_person' => 'Karan Varma',
                'phone' => '+91 98711 33221',
                'address' => '210 South Extension Part-II, New Delhi 110049',
                'tax_id' => '07AABCB4455D1Z5',
                'trust_balance' => 0.00,
                'status' => 'active',
            ]
        );

        // Firm 2 Matters
        $matterArb = Matter::firstOrCreate(
            ['case_number' => 'SJM/2026/ARB/001'],
            [
                'firm_id' => $firm2->id,
                'client_id' => $clientApex->id,
                'lead_attorney_id' => $sanjeev->id,
                'title' => 'Apex Industrial Solutions vs Northern Infra Rail Ltd',
                'practice_area' => 'Commercial Litigation & Arbitration',
                'court_name' => 'Delhi International Arbitration Centre (DIAC)',
                'judge_name' => 'Hon\'ble Justice (Retd.) A. K. Sikri',
                'status' => 'active',
                'stage' => 'arguments',
                'billing_type' => 'hourly',
                'budget' => 4850000.00,
                'opened_at' => Carbon::now()->subMonths(2)->toDateString(),
            ]
        );

        $matterNclt = Matter::firstOrCreate(
            ['case_number' => 'SJM/2026/NCLT/004'],
            [
                'firm_id' => $firm2->id,
                'client_id' => $clientApex->id,
                'lead_attorney_id' => $neha->id,
                'title' => 'Insolvency Resolution Application against Apex Logistics Co',
                'practice_area' => 'Corporate & Insolvency (IBC / NCLT)',
                'court_name' => 'National Company Law Tribunal (NCLT) Principal Bench',
                'judge_name' => 'Chief Justice (Retd.) Ramalingam Sudhakar',
                'status' => 'active',
                'stage' => 'admission_hearing',
                'billing_type' => 'fixed',
                'budget' => 1950000.00,
                'opened_at' => Carbon::now()->subMonth()->toDateString(),
            ]
        );

        $matterWrit = Matter::firstOrCreate(
            ['case_number' => 'SJM/2026/HC/012'],
            [
                'firm_id' => $firm2->id,
                'client_id' => $clientSynergy->id,
                'lead_attorney_id' => $sanjeev->id,
                'title' => 'Synergy FinTech vs Reserve Bank of India & Anr',
                'practice_area' => 'Constitutional & Writ Jurisdiction',
                'court_name' => 'High Court of Delhi',
                'judge_name' => 'Hon\'ble Justice Prathiba M. Singh',
                'status' => 'active',
                'stage' => 'pleadings_complete',
                'billing_type' => 'hourly',
                'budget' => 1200000.00,
                'opened_at' => Carbon::now()->subWeeks(3)->toDateString(),
            ]
        );

        // Firm 2 Opinions (PDF Page 10 4-Stage Workflow)
        Opinion::firstOrCreate(
            ['opinion_number' => 'OP-SJM-2026-0001'],
            [
                'firm_id' => $firm2->id,
                'matter_id' => $matterArb->id,
                'client_id' => $clientApex->id,
                'author_id' => $neha->id,
                'reviewer_id' => $sanjeev->id,
                'title' => 'Arbitrability of Non-Signatory Parent Entity under Group of Companies Doctrine',
                'type' => 'case_strategy',
                'summary' => 'Client seeks advice on impleading Northern Holdings Singapore as alter-ego guarantor in DIAC arbitration.',
                'body' => "Based on the Constitution Bench judgment of the Supreme Court of India in Cox and Kings v SAP India (2023), non-signatory entities within a corporate group may be joined to the arbitration agreement where commercial reality and common intent exist.\n\nRECOMMENDATION: File preliminary joinder application under DIAC Rules 2024 with documentary evidence of parent company board guarantees.",
                'recommendations' => 'File joinder application before arbitrator within 14 days.',
                'precedents_cited' => 'Cox and Kings v SAP India (2023) 14 SCC 1; Chloro Controls (2013) 1 SCC 641',
                'status' => 'approved',
            ]
        );

        Opinion::firstOrCreate(
            ['opinion_number' => 'OP-SJM-2026-0002'],
            [
                'firm_id' => $firm2->id,
                'matter_id' => $matterWrit->id,
                'client_id' => $clientSynergy->id,
                'author_id' => $sanjeev->id,
                'reviewer_id' => null,
                'title' => 'Constitutional Validity of RBI Digital Lending Master Direction',
                'type' => 'legal_advice',
                'summary' => 'Review of Section 45JA of RBI Act and Article 19(1)(g) grounds for interim relief in High Court.',
                'body' => "The retrospective application of the lending circular infringes legitimate expectations and procedural fairness under Article 14 and 19(1)(g) of the Constitution of India.\n\nRECOMMENDATION: Move urgent interim stay application before the Commercial Division Bench.",
                'recommendations' => 'Seek ex-parte ad-interim stay against penalty notices.',
                'precedents_cited' => 'Internet and Mobile Association of India v RBI (2020) 10 SCC 274',
                'status' => 'published',
                'published_at' => Carbon::now()->subHours(12),
            ]
        );

        // Firm 2 Appointments (PDF Page 12)
        Appointment::firstOrCreate(
            ['firm_id' => $firm2->id, 'title' => 'Arbitration Strategy & DIAC Witness Preparation'],
            [
                'matter_id' => $matterArb->id,
                'client_id' => $clientApex->id,
                'user_id' => $sanjeev->id,
                'type' => 'case_conference',
                'scheduled_at' => Carbon::now()->addDays(3)->setTime(16, 30),
                'duration_minutes' => 60,
                'location' => 'Main Boardroom, SJM Legal Chambers, Connaught Place',
                'status' => 'scheduled',
                'notes' => 'Cross-examination dry run with technical expert witness Mr. K. Sharma.',
            ]
        );

        // Firm 2 Calendar Docket Events
        Event::firstOrCreate(
            ['firm_id' => $firm2->id, 'title' => 'DIAC Preliminary Hearing - Evidence Framing'],
            [
                'matter_id' => $matterArb->id,
                'user_id' => $sanjeev->id,
                'event_type' => 'Court Hearing',
                'start_time' => Carbon::now()->addDays(9)->setTime(14, 0),
                'location' => 'DIAC Chamber 3, Delhi High Court',
                'is_statutory_deadline' => false,
            ]
        );

        // Firm 2 Tasks
        Task::firstOrCreate(
            ['firm_id' => $firm2->id, 'title' => 'File DIAC Statement of Claim & Expert Documents'],
            [
                'matter_id' => $matterArb->id,
                'assigned_to' => $neha->id,
                'created_by' => $sanjeev->id,
                'priority' => 'urgent',
                'due_date' => Carbon::now()->addDays(5)->toDateString(),
                'status' => 'todo',
            ]
        );

        // Firm 2 Time Entries
        TimeEntry::firstOrCreate(
            ['matter_id' => $matterArb->id, 'user_id' => $sanjeev->id, 'narrative' => 'Preparation and drafting of DIAC Statement of Defense and Counter-Claim'],
            [
                'firm_id' => $firm2->id,
                'hours' => 4.5,
                'rate' => 14000.00,
                'total_amount' => 4.5 * 14000.00,
                'activity_code' => 'L120',
                'activity_name' => 'Analysis & Strategy',
                'entry_date' => Carbon::now()->subDays(4)->toDateString(),
                'is_billable' => true,
                'status' => 'billed',
            ]
        );

        TimeEntry::firstOrCreate(
            ['matter_id' => $matterArb->id, 'user_id' => $neha->id, 'narrative' => 'Legal research on Cox & Kings doctrine and drafting index of authorities'],
            [
                'firm_id' => $firm2->id,
                'hours' => 6.0,
                'rate' => 6500.00,
                'total_amount' => 6.0 * 6500.00,
                'activity_code' => 'L110',
                'activity_name' => 'Fact Investigation / Research',
                'entry_date' => Carbon::now()->subDays(3)->toDateString(),
                'is_billable' => true,
                'status' => 'billed',
            ]
        );

        // Firm 2 Expenses
        Expense::firstOrCreate(
            ['firm_id' => $firm2->id, 'matter_id' => $matterArb->id, 'title' => 'DIAC Arbitral Tribunal Administrative Fee'],
            [
                'user_id' => $sanjeev->id,
                'category' => 'Court Fees',
                'amount' => 50000.00,
                'date' => Carbon::now()->subDays(5)->toDateString(),
                'description' => 'Official DIAC registration and tribunal fee receipt #2026-DIAC-994.',
                'is_billable' => true,
                'status' => 'billed',
            ]
        );

        // Firm 2 Invoices
        $invoice1 = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-SJM-2026-001'],
            [
                'firm_id' => $firm2->id,
                'matter_id' => $matterArb->id,
                'client_id' => $clientApex->id,
                'issue_date' => Carbon::now()->subDays(2)->toDateString(),
                'due_date' => Carbon::now()->addDays(28)->toDateString(),
                'subtotal' => 152000.00,
                'tax_rate' => 18.00,
                'tax_amount' => 27360.00,
                'total_amount' => 179360.00,
                'amount_paid' => 179360.00,
                'status' => 'paid',
            ]
        );

        // Firm 2 Transactions (Ledger)
        Transaction::firstOrCreate(
            ['reference_number' => 'HDFCR920260914001'],
            [
                'firm_id' => $firm2->id,
                'client_id' => $clientApex->id,
                'matter_id' => $matterArb->id,
                'invoice_id' => $invoice1->id,
                'type' => 'payment',
                'amount' => 179360.00,
                'payment_method' => 'bank_transfer',
                'date' => Carbon::now()->subDays(1)->toDateString(),
                'notes' => 'Full settlement of Invoice INV-SJM-2026-001 (DIAC Arbitration Fee & Drafting)',
            ]
        );

        // Firm 1 Expenses & Time Entries (for Vennamraj Associates)
        $matter1 = Matter::where('firm_id', $firm1->id)->first();
        $client1 = Client::where('firm_id', $firm1->id)->first();
        if ($matter1 && $client1) {
            Expense::firstOrCreate(
                ['firm_id' => $firm1->id, 'matter_id' => $matter1->id, 'title' => 'High Court Court Fee Stamps & Process Fee'],
                [
                    'user_id' => 1,
                    'category' => 'Court Fees',
                    'amount' => 15400.00,
                    'date' => Carbon::now()->subDays(2)->toDateString(),
                    'description' => 'Delhi High Court process fee and court fee stamps for written statement.',
                    'is_billable' => true,
                    'status' => 'unbilled',
                ]
            );

            Transaction::firstOrCreate(
                ['reference_number' => 'SBINR20260912998'],
                [
                    'firm_id' => $firm1->id,
                    'client_id' => $client1->id,
                    'matter_id' => $matter1->id,
                    'type' => 'advance_deposit',
                    'amount' => 250000.00,
                    'payment_method' => 'bank_transfer',
                    'date' => Carbon::now()->subDays(3)->toDateString(),
                    'notes' => 'Escrow Retainer Advance credited for Commercial Suit CS(COMM) 291/2026',
                ]
            );
        }

        // 4. Ensure Practice Areas, Holidays, Templates for both firms
        $this->call(PracticeSettingsSeeder::class);
    }
}
