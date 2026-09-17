<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Firm;
use App\Models\PracticeArea;
use App\Models\Holiday;
use App\Models\ActivityCategory;
use App\Models\LetterTemplate;
use App\Models\EmailTemplate;
use App\Models\IdType;

class PracticeSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $firms = Firm::all();
        if ($firms->isEmpty()) {
            return;
        }

        foreach ($firms as $firm) {
            // Practice Areas
            $practiceAreas = [
                ['name' => 'Civil Litigation', 'code' => 'CIVIL', 'description' => 'Original suits, appeals, revisions, and execution petitions.'],
                ['name' => 'Criminal Defense & Bail', 'code' => 'CRIM', 'description' => 'Anticipatory bail, trial defense, criminal revisions, quashing petitions.'],
                ['name' => 'Constitutional & Writ Practice', 'code' => 'CONST', 'description' => 'Article 226 & 32 writ petitions, public interest litigations.'],
                ['name' => 'Corporate & Commercial Disputes', 'code' => 'CORP', 'description' => 'NCLT matters, arbitration, contract enforcement, shareholder disputes.'],
                ['name' => 'Real Estate & Property Law', 'code' => 'PROP', 'description' => 'Title search, partition suits, specific performance, RERA appeals.'],
                ['name' => 'Intellectual Property (IPR)', 'code' => 'IPR', 'description' => 'Trademark infringement, patent oppositions, copyright actions.'],
                ['name' => 'Family & Matrimonial Law', 'code' => 'FAM', 'description' => 'Divorce, custody, maintenance petitions, domestic violence appeals.'],
            ];

            foreach ($practiceAreas as $pa) {
                PracticeArea::firstOrCreate(
                    ['firm_id' => $firm->id, 'name' => $pa['name']],
                    ['code' => $pa['code'], 'description' => $pa['description'], 'is_active' => true]
                );
            }

            // Holidays & Court Vacations
            $year = date('Y');
            $holidays = [
                ['name' => 'Republic Day', 'date' => "{$year}-01-26", 'is_court_vacation' => true, 'description' => 'National Holiday - High Courts & Subordinate Courts Closed'],
                ['name' => 'Independence Day', 'date' => "{$year}-08-15", 'is_court_vacation' => true, 'description' => 'National Holiday - Courts Closed'],
                ['name' => 'Gandhi Jayanti', 'date' => "{$year}-10-02", 'is_court_vacation' => true, 'description' => 'Gazetted Holiday'],
                ['name' => 'High Court Summer Vacation', 'date' => "{$year}-05-15", 'is_court_vacation' => true, 'description' => 'Annual Summer Recess (Vacation Benches active only for urgent matters)'],
                ['name' => 'High Court Winter Recess', 'date' => "{$year}-12-24", 'is_court_vacation' => true, 'description' => 'Annual Winter Break'],
            ];

            foreach ($holidays as $h) {
                Holiday::firstOrCreate(
                    ['firm_id' => $firm->id, 'name' => $h['name'], 'date' => $h['date']],
                    ['is_court_vacation' => $h['is_court_vacation'], 'description' => $h['description']]
                );
            }

            // Activity Categories
            $activities = [
                ['name' => 'Court Hearing & Argument', 'code' => 'COURT_HEARING', 'color' => '#8B263E', 'description' => 'Physical or virtual appearance before Judge / Bench'],
                ['name' => 'Client Consultation', 'code' => 'CONSULTATION', 'color' => '#9F8349', 'description' => 'Case strategy briefing and client conference'],
                ['name' => 'Legal Research & Case Law', 'code' => 'LEGAL_RESEARCH', 'color' => '#2C5E7A', 'description' => 'SCC Online / Manupatra research and precedent extraction'],
                ['name' => 'Pleading & Document Drafting', 'code' => 'DRAFTING', 'color' => '#3D7A5A', 'description' => 'Plaints, written statements, writ petitions, and affidavits'],
                ['name' => 'Registry Filing & Scrutiny', 'code' => 'FILING', 'color' => '#B8860B', 'description' => 'Court registry filing, clearing defects, obtaining CNR'],
            ];

            foreach ($activities as $act) {
                ActivityCategory::firstOrCreate(
                    ['firm_id' => $firm->id, 'name' => $act['name']],
                    ['code' => $act['code'], 'color' => $act['color'], 'description' => $act['description']]
                );
            }

            // Letter Templates
            $letters = [
                [
                    'name' => 'Legal Notice - Cheque Bounce (Sec 138 NI Act)',
                    'category' => 'legal_notice',
                    'body_html' => '<h3>LEGAL NOTICE UNDER SECTION 138 OF THE NEGOTIABLE INSTRUMENTS ACT, 1881</h3><p>To,<br><strong>{{recipient_name}}</strong><br>{{recipient_address}}</p><p>Under instructions from and on behalf of my client, <strong>{{client_name}}</strong>, I hereby serve upon you the following legal notice:</p><p>1. That you issued Cheque No. <strong>{{cheque_number}}</strong> dated <strong>{{cheque_date}}</strong> drawn on {{bank_name}} for an amount of Rs. <strong>{{amount}}</strong> towards discharge of lawful debt.</p><p>2. That upon presentation, the said cheque was returned dishonoured by your bank with remarks "{{bounce_reason}}" vide return memo dated {{memo_date}}.</p><p>Therefore, I hereby call upon you to make the payment within 15 days of receipt of this notice, failing which criminal proceedings will be instituted under Section 138 of the NI Act.</p>',
                    'variables' => ['recipient_name', 'recipient_address', 'client_name', 'cheque_number', 'cheque_date', 'bank_name', 'amount', 'bounce_reason', 'memo_date'],
                ],
                [
                    'name' => 'Cease and Desist Notice - Trademark Infringement',
                    'category' => 'cease_and_desist',
                    'body_html' => '<h3>CEASE AND DESIST DEMAND NOTICE</h3><p>Dear {{recipient_name}},</p><p>We represent <strong>{{client_name}}</strong>, the registered proprietor of the mark <strong>{{trademark_name}}</strong> (Registration No. {{reg_no}}). It has come to our client\'s attention that you are unlawfully utilizing a deceptively similar mark in respect of identical goods/services.</p><p>You are hereby demanded to forthwith cease and desist from advertising, marketing, or selling any goods under the impugned mark within 7 days hereof.</p>',
                    'variables' => ['recipient_name', 'client_name', 'trademark_name', 'reg_no'],
                ],
                [
                    'name' => 'Advocate Retainer Agreement',
                    'category' => 'retainer_agreement',
                    'body_html' => '<h3>MEMORANDUM OF ADVOCATE ENGAGEMENT</h3><p>This Engagement Agreement is entered between <strong>{{firm_name}}</strong> (Advocates & Legal Consultants) and <strong>{{client_name}}</strong> regarding representation in <strong>{{matter_title}}</strong>.</p><p>1. <strong>Scope of Services:</strong> Representation before {{court_name}}, filing pleadings, preparing witness lists, and conducting oral arguments.</p><p>2. <strong>Professional Conduct:</strong> Counsel shall adhere to the Advocates Act, 1961 and Bar Council rules.</p>',
                    'variables' => ['firm_name', 'client_name', 'matter_title', 'court_name'],
                ],
            ];

            foreach ($letters as $let) {
                LetterTemplate::firstOrCreate(
                    ['firm_id' => $firm->id, 'name' => $let['name']],
                    ['category' => $let['category'], 'body_html' => $let['body_html'], 'variables' => $let['variables']]
                );
            }

            // Email Templates
            $emails = [
                [
                    'name' => 'Hearing Appearance Alert',
                    'subject' => 'Court Hearing Scheduled: {{matter_title}} [{{case_number}}]',
                    'type' => 'hearing_alert',
                    'body_html' => '<p>Dear {{client_name}},</p><p>Please be advised that your matter <strong>{{matter_title}}</strong> has been listed for hearing before <strong>{{court_name}}</strong>, {{bench_court_number}} on <strong>{{hearing_date}}</strong>.</p><p>Stage of Case: <strong>{{stage}}</strong></p><p>Our advocacy team will appear on your behalf. If physical attendance is required, our office will notify you separately.</p>',
                    'variables' => ['client_name', 'matter_title', 'case_number', 'court_name', 'bench_court_number', 'hearing_date', 'stage'],
                ],
                [
                    'name' => 'Consultation Booking Confirmation',
                    'subject' => 'Legal Consultation Confirmed: {{date_time}}',
                    'type' => 'appointment_reminder',
                    'body_html' => '<p>Dear {{client_name}},</p><p>Your consultation with Advocate <strong>{{lawyer_name}}</strong> has been scheduled for <strong>{{date_time}}</strong> ({{location_mode}}).</p><p>Agenda: {{appointment_title}}</p><p>Please have all relevant documents and contracts accessible prior to the meeting.</p>',
                    'variables' => ['client_name', 'lawyer_name', 'date_time', 'location_mode', 'appointment_title'],
                ],
            ];

            foreach ($emails as $em) {
                EmailTemplate::firstOrCreate(
                    ['firm_id' => $firm->id, 'name' => $em['name']],
                    ['subject' => $em['subject'], 'type' => $em['type'], 'body_html' => $em['body_html'], 'variables' => $em['variables']]
                );
            }

            // ID Types
            $idTypes = [
                ['name' => 'Litigation Matter File ID', 'prefix' => 'MAT', 'format_mask' => '{PREFIX}-{YYYY}-{SEQ}', 'next_number' => 101],
                ['name' => 'Client Reference Code', 'prefix' => 'CLN', 'format_mask' => '{PREFIX}-{YYYY}-{SEQ}', 'next_number' => 201],
                ['name' => 'Legal Opinion Tracking Code', 'prefix' => 'OPN', 'format_mask' => '{PREFIX}-{YYYY}-{SEQ}', 'next_number' => 51],
            ];

            foreach ($idTypes as $idt) {
                IdType::firstOrCreate(
                    ['firm_id' => $firm->id, 'name' => $idt['name']],
                    ['prefix' => $idt['prefix'], 'format_mask' => $idt['format_mask'], 'next_number' => $idt['next_number']]
                );
            }
        }
    }
}
