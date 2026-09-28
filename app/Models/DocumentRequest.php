<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_assisted_submission' => 'boolean',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assistedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assisted_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Ensure a client has active sample document requests awaiting upload or resubmission.
     */
    public static function ensureSampleRequestsForClient(Client $client): void
    {
        $pendingCount = static::where('client_id', $client->id)
            ->whereIn('status', ['pending', 'rejected', 'in_review'])
            ->count();

        if ($pendingCount >= 3) {
            return;
        }

        $firm = $client->firm ?: Firm::find($client->firm_id);
        $currency = $firm?->currency ?? 'USD';

        $matters = Matter::where('client_id', $client->id)->get();
        if ($matters->isEmpty()) {
            $leadAttorneyId = $client->primary_attorney_id
                ?: User::where('firm_id', $client->firm_id)->whereIn('role', ['partner', 'associate'])->first()?->id
                ?: User::where('firm_id', $client->firm_id)->first()?->id;

            $prefix = $currency === 'INR' ? 'CS(COMM)' : 'MAT';
            $caseNumber = sprintf('%s/%s/%04d-%03d', $prefix, date('Y'), $client->id, rand(100, 999));

            while (Matter::where('case_number', $caseNumber)->exists()) {
                $caseNumber = sprintf('%s/%s/%04d-%04d', $prefix, date('Y'), $client->id, rand(1000, 9999));
            }

            $defaultMatter = Matter::create([
                'firm_id' => $client->firm_id,
                'client_id' => $client->id,
                'case_number' => $caseNumber,
                'title' => $currency === 'INR' ? 'Commercial Arbitration & Debt Recovery Proceedings' : 'Commercial Contract & Supply Chain Litigation',
                'status' => 'open',
                'stage' => 'Discovery',
                'priority' => 'urgent',
                'lead_attorney_id' => $leadAttorneyId,
                'billing_type' => 'hourly',
                'budget' => 150000,
                'opened_at' => Carbon::now()->subMonths(2),
            ]);
            $matters = collect([$defaultMatter]);
        }

        $requestedBy = $client->primary_attorney_id
            ?: User::where('firm_id', $client->firm_id)->whereIn('role', ['partner', 'associate', 'superadmin'])->first()?->id
            ?: User::first()?->id;

        $samples = $currency === 'INR' ? [
            [
                'title' => 'Board Resolution Authorizing Section 11 Arbitration Filing & Counsel Appearance',
                'description' => 'Certified extract of the Board resolution passed under Section 179 of the Companies Act 2013 authorizing representative to sign pleadings and appear before the High Court.',
                'category' => 'Corporate Governance',
                'priority' => 'urgent',
                'status' => 'pending',
                'due_date' => Carbon::now()->addDays(2)->toDateString(),
            ],
            [
                'title' => 'Audited Balance Sheets & Profit & Loss Statement FY 2024-25',
                'description' => 'Audited financial statements certified by statutory auditors showing working capital cycle and disputed ledger reconciliation vouchers.',
                'category' => 'Financial Statements',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => Carbon::now()->addDays(4)->toDateString(),
            ],
            [
                'title' => 'Signed Sworn Statement of Truth & Commercial Court Verification Affidavit',
                'description' => 'Commercial Courts Act verification affidavit signed by authorized representative with notary registration seal.',
                'category' => 'Affidavits & Verification',
                'priority' => 'urgent',
                'status' => 'rejected',
                'review_notes' => 'Missing notary seal and stamp on page 3 verification clause. Please re-sign before oath commissioner and re-upload.',
                'due_date' => Carbon::now()->addDays(1)->toDateString(),
            ],
            [
                'title' => 'Original Tripartite Supply Agreement & Bank Sanction Letter',
                'description' => 'Executed agreement clauses regarding indemnity, limitation of liability, and jurisdiction for arbitral claim submission.',
                'category' => 'Contracts',
                'priority' => 'normal',
                'status' => 'pending',
                'due_date' => Carbon::now()->addDays(7)->toDateString(),
            ],
            [
                'title' => 'GST Invoices & Work Orders for Disputed Contract Deliverables',
                'description' => 'Running invoices and delivery challans submitted to opposing party with receipt acknowledgments.',
                'category' => 'Tax & Invoices',
                'priority' => 'high',
                'status' => 'submitted',
                'submitted_at' => Carbon::now()->subDay(),
                'client_notes' => 'Uploaded scanned copies with tax invoice serial numbers.',
                'due_date' => Carbon::now()->subDays(2)->toDateString(),
            ],
        ] : [
            [
                'title' => 'Certificate of Good Standing & Board Officer Authorization Resolution',
                'description' => 'Certified Secretary of State good standing certificate and corporate resolution appointing lead counsel for federal court filing.',
                'category' => 'Corporate Governance',
                'priority' => 'urgent',
                'status' => 'pending',
                'due_date' => Carbon::now()->addDays(2)->toDateString(),
            ],
            [
                'title' => 'Audited Financial Ledger & Disputed Transaction Escrow Statements',
                'description' => 'Certified accounting ledger showing disputed supply chain disbursements, wire confirmations, and escrow payment vouchers.',
                'category' => 'Financial Statements',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => Carbon::now()->addDays(4)->toDateString(),
            ],
            [
                'title' => 'Sworn Interrogatory Verification & Expert Deposition Exhibit Disclosures',
                'description' => 'Signed Rule 33 verification page affirming factual interrogatory responses and production exhibits before trial call.',
                'category' => 'Affidavits & Verification',
                'priority' => 'urgent',
                'status' => 'rejected',
                'review_notes' => 'Corporate verification executed by unauthorized personnel. Must be executed by designated corporate officer named in initial disclosures.',
                'due_date' => Carbon::now()->addDays(1)->toDateString(),
            ],
            [
                'title' => 'Master Services Agreement & Fully Executed Amendments Schedule',
                'description' => 'Countersigned agreement including non-disclosure covenants, warranty schedules, and arbitration protocols.',
                'category' => 'Contracts',
                'priority' => 'normal',
                'status' => 'pending',
                'due_date' => Carbon::now()->addDays(7)->toDateString(),
            ],
            [
                'title' => 'Regulatory Compliance Filings & Pre-Merger Hart-Scott-Rodino Notice',
                'description' => 'Itemized submission dossier provided to regulatory authorities regarding market concentration and transaction timing.',
                'category' => 'Regulatory Filings',
                'priority' => 'high',
                'status' => 'submitted',
                'submitted_at' => Carbon::now()->subDay(),
                'client_notes' => 'All HSR filing schedules attached in PDF bundle.',
                'due_date' => Carbon::now()->subDays(2)->toDateString(),
            ],
        ];

        foreach ($samples as $idx => $sample) {
            $exists = static::where('client_id', $client->id)
                ->where('title', $sample['title'])
                ->exists();

            if (! $exists) {
                $targetMatter = $matters[$idx % $matters->count()];
                static::create(array_merge($sample, [
                    'firm_id' => $client->firm_id,
                    'matter_id' => $targetMatter->id,
                    'client_id' => $client->id,
                    'requested_by' => $requestedBy,
                ]));
            }
        }
    }
}
