<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rajesh = User::where('role', 'partner')->first() ?? User::first();
        $priya = User::where('role', 'associate')->first() ?? $rajesh;

        $doc1 = Document::find(1);
        if ($doc1 && DocumentVersion::where('document_id', $doc1->id)->count() === 0) {
            $v1_1 = DocumentVersion::create([
                'document_id' => $doc1->id,
                'version_number' => 1,
                'filename' => 'Plaint_Commercial_Suit_draft_v1.pdf',
                'version_status' => 'Draft',
                'file_path' => $doc1->file_path,
                'file_size' => 4210000,
                'file_hash' => hash('sha256', 'v1-doc1-sample'),
                'uploaded_by' => $rajesh->id,
                'change_summary' => 'Initial draft of plaint under Order VII Rule 1 CPC with supporting affidavits',
                'change_description' => 'Initial draft of plaint under Order VII Rule 1 CPC with supporting affidavits',
                'previous_version_id' => null,
                'created_at' => now()->subDays(14),
            ]);

            $v1_2 = DocumentVersion::create([
                'document_id' => $doc1->id,
                'version_number' => 2,
                'filename' => 'Plaint_draft_v2_redline.pdf',
                'version_status' => 'Draft',
                'file_path' => $doc1->file_path,
                'file_size' => 4320000,
                'file_hash' => hash('sha256', 'v2-doc1-sample'),
                'uploaded_by' => $priya->id,
                'change_summary' => 'Incorporated senior counsel redline amendments to paras 14-18 regarding jurisdiction',
                'change_description' => 'Incorporated senior counsel redline amendments to paras 14-18 regarding jurisdiction',
                'previous_version_id' => $v1_1->id,
                'created_at' => now()->subDays(9),
            ]);

            $v1_3 = DocumentVersion::create([
                'document_id' => $doc1->id,
                'version_number' => 3,
                'filename' => 'Plaint_review_v3_partner_edits.pdf',
                'version_status' => 'Review',
                'file_path' => $doc1->file_path,
                'file_size' => 4380000,
                'file_hash' => hash('sha256', 'v3-doc1-sample'),
                'uploaded_by' => $rajesh->id,
                'change_summary' => 'Pre-filing internal review and annexure validation with client verification records',
                'change_description' => 'Pre-filing internal review and annexure validation with client verification records',
                'previous_version_id' => $v1_2->id,
                'created_at' => now()->subDays(4),
            ]);

            $v1_4 = DocumentVersion::create([
                'document_id' => $doc1->id,
                'version_number' => 4,
                'filename' => $doc1->filename,
                'version_status' => 'Final',
                'file_path' => $doc1->file_path,
                'file_size' => $doc1->file_size ?: 4410290,
                'file_hash' => $doc1->sha256 ?: hash('sha256', 'v4-final'),
                'uploaded_by' => $rajesh->id,
                'change_summary' => 'Court authenticated e-filing plaint with High Court registry seal and hash stamp',
                'change_description' => 'Court authenticated e-filing plaint with High Court registry seal and hash stamp',
                'previous_version_id' => $v1_3->id,
                'created_at' => now()->subDays(1),
            ]);

            $doc1->update([
                'version' => 4,
                'document_number' => $doc1->document_number ?: 'DOC-2026-0001',
                'document_status' => 'final',
            ]);
        }

        $doc2 = Document::find(2);
        if ($doc2 && DocumentVersion::where('document_id', $doc2->id)->count() === 0) {
            $v2_1 = DocumentVersion::create([
                'document_id' => $doc2->id,
                'version_number' => 1,
                'filename' => 'Written_Statement_draft_v1.pdf',
                'version_status' => 'Draft',
                'file_path' => $doc2->file_path,
                'file_size' => 8800000,
                'file_hash' => hash('sha256', 'v1-doc2-sample'),
                'uploaded_by' => $priya->id,
                'change_summary' => 'Preliminary grounds of defense and preliminary objections on limitation period',
                'change_description' => 'Preliminary grounds of defense and preliminary objections on limitation period',
                'previous_version_id' => null,
                'created_at' => now()->subDays(10),
            ]);

            $v2_2 = DocumentVersion::create([
                'document_id' => $doc2->id,
                'version_number' => 2,
                'filename' => 'Written_Statement_v2_review.pdf',
                'version_status' => 'Review',
                'file_path' => $doc2->file_path,
                'file_size' => 8900000,
                'file_hash' => hash('sha256', 'v2-doc2-sample'),
                'uploaded_by' => $rajesh->id,
                'change_summary' => 'Partner review on jurisdictional objection and Statement of Truth compliance',
                'change_description' => 'Partner review on jurisdictional objection and Statement of Truth compliance',
                'previous_version_id' => $v2_1->id,
                'created_at' => now()->subDays(5),
            ]);

            $v2_3 = DocumentVersion::create([
                'document_id' => $doc2->id,
                'version_number' => 3,
                'filename' => $doc2->filename,
                'version_status' => 'Final',
                'file_path' => $doc2->file_path,
                'file_size' => $doc2->file_size ?: 8940000,
                'file_hash' => $doc2->sha256 ?: hash('sha256', 'v3-doc2-final'),
                'uploaded_by' => $priya->id,
                'change_summary' => 'Final signed written statement along with sworn statement of truth',
                'change_description' => 'Final signed written statement along with sworn statement of truth',
                'previous_version_id' => $v2_2->id,
                'created_at' => now()->subDays(2),
            ]);

            $doc2->update([
                'version' => 3,
                'document_number' => $doc2->document_number ?: 'DOC-2026-0002',
                'document_status' => 'final',
            ]);
        }

        $doc3 = Document::find(3);
        if ($doc3 && DocumentVersion::where('document_id', $doc3->id)->count() === 0) {
            $v3_1 = DocumentVersion::create([
                'document_id' => $doc3->id,
                'version_number' => 1,
                'filename' => 'Sec_7_IBC_draft_v1.pdf',
                'version_status' => 'Draft',
                'file_path' => $doc3->file_path,
                'file_size' => 12100000,
                'file_hash' => hash('sha256', 'v1-doc3-sample'),
                'uploaded_by' => $priya->id,
                'change_summary' => 'Initial petition under Section 7 of Insolvency and Bankruptcy Code 2016',
                'change_description' => 'Initial petition under Section 7 of Insolvency and Bankruptcy Code 2016',
                'previous_version_id' => null,
                'created_at' => now()->subDays(12),
            ]);

            $v3_2 = DocumentVersion::create([
                'document_id' => $doc3->id,
                'version_number' => 2,
                'filename' => 'Sec_7_IBC_Application_NCLT.pdf',
                'version_status' => 'Final',
                'file_path' => $doc3->file_path,
                'file_size' => $doc3->file_size ?: 12400000,
                'file_hash' => $doc3->sha256 ?: hash('sha256', 'v2-doc3-final'),
                'uploaded_by' => $priya->id,
                'change_summary' => 'Final NCLT petition along with NeSL record of default and Form 1 schedule',
                'change_description' => 'Final NCLT petition along with NeSL record of default and Form 1 schedule',
                'previous_version_id' => $v3_1->id,
                'created_at' => now()->subDays(6),
            ]);

            $v3_3 = DocumentVersion::create([
                'document_id' => $doc3->id,
                'version_number' => 3,
                'filename' => 'Sec_7_IBC_Executed_Affidavits.pdf',
                'version_status' => 'Executed',
                'file_path' => $doc3->file_path,
                'file_size' => $doc3->file_size ?: 12400000,
                'file_hash' => hash('sha256', 'v3-doc3-executed'),
                'uploaded_by' => $rajesh->id,
                'change_summary' => 'Executed and notarized affidavits by authorized bank representative with board resolution',
                'change_description' => 'Executed and notarized affidavits by authorized bank representative with board resolution',
                'previous_version_id' => $v3_2->id,
                'created_at' => now()->subDays(1),
            ]);

            $doc3->update([
                'version' => 3,
                'document_number' => $doc3->document_number ?: 'DOC-2026-0003',
                'document_status' => 'final',
            ]);
        }

        // For any remaining documents without versions, create v1
        $docsWithoutVersions = Document::whereDoesntHave('versions')->get();
        foreach ($docsWithoutVersions as $doc) {
            DocumentVersion::create([
                'document_id' => $doc->id,
                'version_number' => $doc->version ?: 1,
                'filename' => $doc->filename,
                'version_status' => in_array(strtolower($doc->document_status ?? ''), ['final', 'approved', 'executed']) ? 'Final' : 'Draft',
                'file_path' => $doc->file_path ?: 'documents/default.pdf',
                'file_size' => $doc->file_size ?: 102400,
                'file_hash' => $doc->sha256 ?: hash('sha256', 'default-hash-'.$doc->id),
                'uploaded_by' => $doc->user_id ?: ($rajesh->id ?? null),
                'change_summary' => 'Initial vault record',
                'change_description' => 'Initial vault record',
                'previous_version_id' => null,
                'created_at' => $doc->created_at ?: now(),
            ]);
            if (! $doc->document_number) {
                $doc->update([
                    'document_number' => 'DOC-'.($doc->created_at ? $doc->created_at->format('Y') : date('Y')).'-'.str_pad($doc->id, 4, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }
}
