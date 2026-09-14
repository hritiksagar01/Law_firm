<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Matter;
use App\Models\Message;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Firm (Indian Law Firm / Chambers)
        $firm = Firm::create([
            'name' => 'Sharma & Associates, Advocates & Solicitors',
            'slug' => 'sharma-associates',
            'email' => 'chambers@sharmalegal.in',
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
        ]);

        // 2. Create Users (Advocates, Clerks, and Client Portal)
        $rajesh = User::create([
            'firm_id' => $firm->id,
            'name' => 'Adv. Rajesh Sharma',
            'email' => 'rajesh@sharmalegal.in',
            'password' => Hash::make('password123'),
            'role' => 'partner',
            'title' => 'Senior Advocate & Managing Partner',
            'hourly_rate' => 12000.00,
            'phone' => '+91 98110 23411',
            'avatar_url' => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?w=150&auto=format&fit=crop&q=80',
        ]);

        $priya = User::create([
            'firm_id' => $firm->id,
            'name' => 'Adv. Priya Nair',
            'email' => 'priya@sharmalegal.in',
            'password' => Hash::make('password123'),
            'role' => 'associate',
            'title' => 'Senior Associate Counsel',
            'hourly_rate' => 5500.00,
            'phone' => '+91 98712 55432',
            'avatar_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80',
        ]);

        $amit = User::create([
            'firm_id' => $firm->id,
            'name' => 'Amit Verma',
            'email' => 'amit@sharmalegal.in',
            'password' => Hash::make('password123'),
            'role' => 'paralegal',
            'title' => 'Law Clerk & Court Munshi',
            'hourly_rate' => 1500.00,
            'phone' => '+91 99100 88721',
            'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
        ]);

        $vikramClient = User::create([
            'firm_id' => $firm->id,
            'name' => 'Vikram Malhotra',
            'email' => 'vikram@malhotragroup.in',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'title' => 'Managing Director, Malhotra Enterprises Pvt Ltd',
            'phone' => '+91 98200 11928',
            'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
        ]);

        // 3. Create Clients
        $clientMalhotra = Client::create([
            'firm_id' => $firm->id,
            'user_id' => $vikramClient->id,
            'type' => 'corporate',
            'name' => 'Malhotra Enterprises Pvt Ltd',
            'contact_person' => 'Vikram Malhotra',
            'email' => 'vikram@malhotragroup.in',
            'phone' => '+91 98200 11928',
            'tax_id' => '07AAACM1234F1Z5',
            'address' => 'Plot 48, Okhla Industrial Area Phase III, New Delhi 110020',
            'trust_balance' => 250000.00,
            'status' => 'active',
        ]);

        $clientKavita = Client::create([
            'firm_id' => $firm->id,
            'type' => 'individual',
            'name' => 'Kavita Rao',
            'contact_person' => 'Kavita Rao',
            'email' => 'kavita.rao@delhirealestate.in',
            'phone' => '+91 98101 44521',
            'tax_id' => 'ABCPR1294K',
            'address' => 'C-14, Vasant Vihar, New Delhi 110057',
            'trust_balance' => 85000.00,
            'status' => 'active',
        ]);

        $clientApex = Client::create([
            'firm_id' => $firm->id,
            'type' => 'corporate',
            'name' => 'Apex Logistics India Ltd',
            'contact_person' => 'Sanjay Singhania',
            'email' => 'sanjay@apexlogistics.in',
            'phone' => '+91 (22) 6789-3200',
            'tax_id' => '27AAACA9812E1Z3',
            'address' => 'Nariman Point, Marine Drive, Mumbai 400021',
            'trust_balance' => 450000.00,
            'status' => 'active',
        ]);

        $clientDelta = Client::create([
            'firm_id' => $firm->id,
            'type' => 'corporate',
            'name' => 'Delta Infra Projects Pvt Ltd',
            'contact_person' => 'Anand Mehta',
            'email' => 'anand@deltainfra.in',
            'phone' => '+91 (11) 4100-9988',
            'tax_id' => '07AABCD8821C1Z1',
            'address' => 'Connaught Circus, New Delhi 110001',
            'trust_balance' => 600000.00,
            'status' => 'active',
        ]);

        // 4. Create Indian Court Matters
        $matterMalhotra = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $clientMalhotra->id,
            'case_number' => 'CS (COMM) 412/2026',
            'title' => 'Malhotra Enterprises v. Apex Commercial Bank',
            'practice_area' => 'Commercial Litigation & Arbitration',
            'court_name' => 'High Court of Delhi, New Delhi',
            'judge_name' => 'Hon. Justice C. Hari Shankar',
            'stage' => 'Notice & Pleadings',
            'status' => 'active',
            'lead_attorney_id' => $rajesh->id,
            'billing_type' => 'hourly',
            'budget' => 450000.00,
            'opened_at' => '2026-01-15',
        ]);

        $matterKavita = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $clientKavita->id,
            'case_number' => 'W.P.(C) 1892/2026',
            'title' => 'Kavita Rao v. Delhi Development Authority (DDA)',
            'practice_area' => 'Constitutional & Writ Petitions (Art 226/32)',
            'court_name' => 'High Court of Delhi, New Delhi',
            'judge_name' => 'Hon. Justice Subramonium Prasad',
            'stage' => 'Pleadings',
            'status' => 'active',
            'lead_attorney_id' => $rajesh->id,
            'billing_type' => 'hourly',
            'budget' => 350000.00,
            'opened_at' => '2026-02-10',
        ]);

        $matterDelta = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $clientDelta->id,
            'case_number' => 'CP (IB) 74/2026',
            'title' => 'In re: Delta Infra Projects Ltd (CIRP Insolvency)',
            'practice_area' => 'Corporate & Insolvency (IBC / NCLT)',
            'court_name' => 'National Company Law Tribunal (NCLT), Principal Bench',
            'judge_name' => 'Hon. Chief Justice (Retd.) Ramalingam Sudhakar',
            'stage' => 'Evidence & Arguments',
            'status' => 'active',
            'lead_attorney_id' => $priya->id,
            'billing_type' => 'hourly',
            'budget' => 1200000.00,
            'opened_at' => '2026-03-01',
        ]);

        $matterApex = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $clientApex->id,
            'case_number' => 'ARB.P. 88/2026',
            'title' => 'Apex Logistics v. Northern Railway Freight Division',
            'practice_area' => 'Commercial Litigation & Arbitration',
            'court_name' => 'Arbitration Tribunal, New Delhi',
            'judge_name' => 'Hon. Justice (Retd.) A.K. Sikri, Sole Arbitrator',
            'stage' => 'Final Hearing & Order',
            'status' => 'active',
            'lead_attorney_id' => $priya->id,
            'billing_type' => 'hourly',
            'budget' => 850000.00,
            'opened_at' => '2026-04-05',
        ]);

        $matterMalhotraArb = Matter::create([
            'firm_id' => $firm->id,
            'client_id' => $clientMalhotra->id,
            'case_number' => 'ARB.P. 104/2026',
            'title' => 'Malhotra Enterprises v. Delhi Metro Rail Corp (DMRC)',
            'practice_area' => 'Commercial Litigation & Arbitration',
            'court_name' => 'Arbitration Tribunal, New Delhi',
            'judge_name' => 'Hon. Justice (Retd.) Badar Durrez Ahmed',
            'stage' => 'Evidence & Arguments',
            'status' => 'active',
            'lead_attorney_id' => $rajesh->id,
            'billing_type' => 'hourly',
            'budget' => 600000.00,
            'opened_at' => '2026-02-20',
        ]);

        // Attach advocates to matters
        $matterMalhotra->users()->attach([$rajesh->id, $priya->id, $amit->id]);
        $matterMalhotraArb->users()->attach([$rajesh->id, $priya->id]);
        $matterKavita->users()->attach([$rajesh->id, $amit->id]);
        $matterDelta->users()->attach([$priya->id]);
        $matterApex->users()->attach([$priya->id, $amit->id]);

        // 5. Documents & Court Filings
        Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'user_id' => $rajesh->id,
            'title' => 'Plaint under Order VII Rule 1 CPC with Affidavit',
            'filename' => 'Plaint_Commercial_Suit_Malhotra_v_Apex.pdf',
            'file_path' => 'documents/malhotra/plaint_signed.pdf',
            'file_size' => 4410290,
            'mime_type' => 'application/pdf',
            'sha256' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
            'category' => 'Pleadings',
            'privilege' => 'Advocate-Client Privileged',
            'version' => 1,
        ]);

        Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'user_id' => $priya->id,
            'title' => 'Written Statement & Statement of Truth by Defendant Bank',
            'filename' => 'Written_Statement_Apex_Bank.pdf',
            'file_path' => 'documents/malhotra/written_statement.pdf',
            'file_size' => 8940000,
            'mime_type' => 'application/pdf',
            'sha256' => '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8',
            'category' => 'Discovery',
            'privilege' => 'Confidential',
            'version' => 1,
        ]);

        Document::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterDelta->id,
            'user_id' => $priya->id,
            'title' => 'Section 7 Application under Insolvency & Bankruptcy Code 2016',
            'filename' => 'Sec_7_IBC_Application_NCLT.pdf',
            'file_path' => 'documents/delta/sec_7_ibc.pdf',
            'file_size' => 12400000,
            'mime_type' => 'application/pdf',
            'sha256' => '4b227777d4dd1fc61c6f884f48641d02b4d121d3fd328cb08b5531fcacdabf8a',
            'category' => 'Evidence',
            'privilege' => 'Advocate-Client Privileged',
            'version' => 1,
        ]);

        // 6. Time Entries & Professional Fee Register
        TimeEntry::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'user_id' => $rajesh->id,
            'hours' => 2.50,
            'rate' => 12000.00,
            'total_amount' => 30000.00,
            'activity_code' => 'L120',
            'activity_name' => 'Court Appearance & Senior Counsel Arguments',
            'narrative' => 'Appeared before Hon. High Court on interim injunction application under Order 39 Rules 1 & 2 CPC.',
            'is_billable' => true,
            'status' => 'unbilled',
            'entry_date' => now()->toDateString(),
        ]);

        TimeEntry::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'user_id' => $priya->id,
            'hours' => 3.00,
            'rate' => 5500.00,
            'total_amount' => 16500.00,
            'activity_code' => 'L110',
            'activity_name' => 'Plaint & Interlocutory Drafting',
            'narrative' => 'Drafted rejoinder to written statement and verified annexures / audited balance sheets.',
            'is_billable' => true,
            'status' => 'unbilled',
            'entry_date' => now()->toDateString(),
        ]);

        TimeEntry::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterDelta->id,
            'user_id' => $priya->id,
            'hours' => 4.00,
            'rate' => 5500.00,
            'total_amount' => 22000.00,
            'activity_code' => 'L330',
            'activity_name' => 'NCLT Arguments & Committee of Creditors Protocol',
            'narrative' => 'Prepared resolution plan review and appeared before Principal Bench NCLT.',
            'is_billable' => true,
            'status' => 'unbilled',
            'entry_date' => now()->subDay()->toDateString(),
        ]);

        // 7. Events & Indian Court Docket
        Event::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'user_id' => $rajesh->id,
            'title' => 'Interim Injunction Hearing (Item No. 18, Court No. 24)',
            'event_type' => 'Court Hearing',
            'start_time' => now()->addDays(2)->setTime(10, 30),
            'end_time' => now()->addDays(2)->setTime(12, 30),
            'location' => 'Courtroom 24, Main Block, Delhi High Court',
            'is_statutory_deadline' => false,
            'notes' => 'Senior Advocate Adv. Rajesh Sharma leading arguments. Adv. Priya Nair to keep authorities ready.',
        ]);

        Event::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'user_id' => $priya->id,
            'title' => 'Rejoinder Filing Deadline (Registry Closes 4:30 PM)',
            'event_type' => 'Filing Deadline',
            'start_time' => now()->addDay()->setTime(16, 30),
            'location' => 'Delhi High Court e-Filing Portal',
            'is_statutory_deadline' => true,
            'notes' => 'Statutory deadline pursuant to High Court Original Side Rules. Advance copy served on opposing counsel.',
        ]);

        Event::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterDelta->id,
            'user_id' => $priya->id,
            'title' => 'NCLT Section 7 Hearing (Item 04, Courtroom 1)',
            'event_type' => 'Court Hearing',
            'start_time' => now()->addDays(4)->setTime(11, 0),
            'end_time' => now()->addDays(4)->setTime(13, 0),
            'location' => 'Block 3, CGO Complex, Lodhi Road, New Delhi',
            'is_statutory_deadline' => false,
        ]);

        // 8. Invoices & GST Bills
        Invoice::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'client_id' => $clientMalhotra->id,
            'invoice_number' => 'GST-2026-0042',
            'issue_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->addDays(20)->toDateString(),
            'subtotal' => 75000.00,
            'tax_rate' => 18.00,
            'tax_amount' => 13500.00,
            'total_amount' => 88500.00,
            'amount_paid' => 0.00,
            'status' => 'sent',
        ]);

        Invoice::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterApex->id,
            'client_id' => $clientApex->id,
            'invoice_number' => 'GST-2026-0038',
            'issue_date' => now()->subDays(30)->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
            'subtotal' => 125000.00,
            'tax_rate' => 18.00,
            'tax_amount' => 22500.00,
            'total_amount' => 147500.00,
            'amount_paid' => 147500.00,
            'status' => 'paid',
        ]);

        // 9. Tasks & Registry Protocols
        Task::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'assigned_to' => $priya->id,
            'created_by' => $rajesh->id,
            'title' => 'Draft Rejoinder & Comparative Statement of Claims',
            'description' => 'Address preliminary objections raised in Bank written statement; cite Hon. SC ruling in Union of India v. D.N. Revri.',
            'priority' => 'urgent',
            'status' => 'in_progress',
            'due_date' => now()->addDays(1)->toDateString(),
        ]);

        Task::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'assigned_to' => $amit->id,
            'created_by' => $rajesh->id,
            'title' => 'Inspect Court File at High Court Registry & Obtain Certified Copies',
            'description' => 'Check if notice report from Process Server has been uploaded in Registry cause file.',
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        // 10. Privileged Counsel & Client Messages
        Message::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'sender_id' => $rajesh->id,
            'body' => 'Good evening Mr. Malhotra. The Commercial Suit hearing is confirmed before Court 24 for Thursday at 10:30 AM. We have prepared the injunction arguments.',
            'is_privileged' => true,
        ]);

        Message::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'sender_id' => $vikramClient->id,
            'body' => 'Thank you Adv. Rajesh. Our CFO has retrieved the original bank correspondence. We will upload the loan sanction documents by tomorrow afternoon.',
            'is_privileged' => true,
        ]);

        Message::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'sender_id' => $priya->id,
            'body' => 'Noted sir. Please ensure the Board Resolution authorizing the arbitration filing is also uploaded in the Document Requests portal.',
            'is_privileged' => true,
        ]);

        // 11. Document Requests (F-07 & F-08 Client Portal Workflow)
        DocumentRequest::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotraArb->id,
            'client_id' => $clientMalhotra->id,
            'requested_by' => $rajesh->id,
            'title' => 'Board Resolution authorizing Section 11 Arbitration Filing',
            'description' => 'Certified extract of the Board resolution passed under Section 179 of the Companies Act 2013 authorizing Mr. Vikram Malhotra to sign and file the petition against DMRC.',
            'category' => 'Corporate Authorizations',
            'status' => 'pending',
            'priority' => 'urgent',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        DocumentRequest::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'client_id' => $clientMalhotra->id,
            'requested_by' => $priya->id,
            'title' => 'Original Loan Sanction & Security Agreement with Apex Commercial Bank',
            'description' => 'Signed loan facility agreement executed at the Parliament Street branch along with schedule of hypothecated goods.',
            'category' => 'Contracts & Agreements',
            'status' => 'submitted',
            'priority' => 'high',
            'due_date' => now()->subDay()->toDateString(),
            'submitted_at' => now()->subHours(4),
            'client_notes' => 'Uploaded scanned copy of the facility agreement dated 14 May 2022 along with schedule of hypothecation.',
        ]);

        DocumentRequest::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotra->id,
            'client_id' => $clientMalhotra->id,
            'requested_by' => $rajesh->id,
            'title' => 'Audited Balance Sheets & Profit & Loss Statement FY 2024-25',
            'description' => 'Audited financial statements certified by statutory auditors showing working capital cycle and disputed interest deductions.',
            'category' => 'Financial Statements',
            'status' => 'pending',
            'priority' => 'normal',
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        DocumentRequest::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotraArb->id,
            'client_id' => $clientMalhotra->id,
            'requested_by' => $priya->id,
            'title' => 'GST Invoices & Work Orders for Disputed DMRC Contract Deliverables',
            'description' => 'Set of running invoices Nos. ME-2024/091 to 105 submitted to DMRC engineering cell.',
            'category' => 'Tax & Invoices',
            'status' => 'completed',
            'priority' => 'high',
            'due_date' => now()->subDays(5)->toDateString(),
            'submitted_at' => now()->subDays(3),
            'review_notes' => 'Verified and matched with delivery challans. Added to compilation of documents.',
        ]);

        // Second Invoice for Malhotra (Paid retainer invoice)
        Invoice::create([
            'firm_id' => $firm->id,
            'matter_id' => $matterMalhotraArb->id,
            'client_id' => $clientMalhotra->id,
            'invoice_number' => 'GST-2026-0019',
            'issue_date' => now()->subDays(35)->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
            'subtotal' => 50000.00,
            'tax_rate' => 18.00,
            'tax_amount' => 9000.00,
            'total_amount' => 59000.00,
            'amount_paid' => 59000.00,
            'status' => 'paid',
        ]);
    }
}
