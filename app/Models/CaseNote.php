<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseNote extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_privileged' => 'boolean',
    ];

    /**
     * Scope query to only non-privileged notes (safe for general staff).
     */
    public function scopeNonPrivileged($query)
    {
        return $query->where('is_privileged', false)
            ->whereNotIn('type', ['privileged', 'attorney_only']);
    }

    /**
     * Scope query based on viewing user's authorization to access privileged attorney notes.
     */
    public function scopeForUser($query, User $user)
    {
        if (! $user->canAccessPrivilegedNotes()) {
            return $this->scopeNonPrivileged($query);
        }

        return $query;
    }

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NoteCategory::class, 'category_id');
    }

    /**
     * Ensure the firm has sample strategic case notes and privileged memos.
     */
    public static function ensureSampleNotesForFirm(int $firmId, ?int $userId = null): void
    {
        $noteCount = static::where('firm_id', $firmId)->count();
        if ($noteCount >= 3) {
            return;
        }

        $firm = Firm::find($firmId);
        $currency = $firm?->currency ?? 'USD';

        $authorId = $userId
            ?: User::where('firm_id', $firmId)->whereIn('role', ['partner', 'associate'])->first()?->id
            ?: User::where('firm_id', $firmId)->first()?->id;

        if (! $authorId) {
            return;
        }

        $matters = Matter::where('firm_id', $firmId)->get();
        if ($matters->isEmpty()) {
            return;
        }

        $samples = $currency === 'INR' ? [
            [
                'title' => 'Strategy Memo: Senior Counsel Consultation on Section 16 Jurisdiction Objection',
                'body' => "Conferred with Senior Advocate regarding opposing counsel's preliminary objection under Section 16 of the Arbitration Act.\n1. Opposing party argues absence of tripartite arbitration clause in sub-agreement.\n2. Strategy: Rely on Hon'ble Supreme Court ruling in Chloro Controls regarding composite corporate transaction doctrine.\n3. Draft compilation of authorities ready for submission before the arbitral tribunal.",
                'type' => 'case_brief',
                'is_pinned' => true,
                'is_privileged' => true,
            ],
            [
                'title' => 'Privileged Client Conference Memo: Disputed Financial Audit Reconciliation',
                'body' => "Key takeaways from consultation with Client CFO and Lead Counsel:\n- Verified that running GST ledgers reflect undisputed acknowledgment of debt.\n- Bank guarantee invocation threatened by claimant; urgent application under Section 9 drafted for protective interim injunction.\n- Settlement discussion threshold approved by board at ₹1.85 Cr minimum recovery.",
                'type' => 'privileged',
                'is_pinned' => true,
                'is_privileged' => true,
            ],
            [
                'title' => 'Trial Preparation: Cross-Examination Pointers on Delivery Ledger',
                'body' => "Focus areas for claimant witness cross-examination:\n- Establish discrepancies between physical delivery challans and electronic ledger entries.\n- Question warehouse supervisor regarding signature verification on received goods notice dated 14th Nov.\n- Highlight failure to provide statutory defect notice within 30-day contractual window.",
                'type' => 'trial_prep',
                'is_pinned' => false,
                'is_privileged' => true,
            ],
            [
                'title' => 'Procedural Case Summary for Client: Hearing Status & Next Steps',
                'body' => "Official Counsel Update for Client:\n- Registry has cleared all scrutiny objections on the amended plaint.\n- Application for interim stay is listed before the Hon'ble High Court on the upcoming cause list.\n- Client representative is requested to keep original board resolution ready for production if summoned.",
                'type' => 'client_visible',
                'is_pinned' => false,
                'is_privileged' => false,
            ],
        ] : [
            [
                'title' => 'Strategy Memo: Rule 12(b)(6) Motion to Dismiss & Choice of Law Analysis',
                'body' => "Conferred with litigation partner regarding defense motion to dismiss:\n1. Opposing party claims lack of personal jurisdiction over Delaware parent entity.\n2. Strategy: Establish purposeful availment based on executed supply chain contracts and in-forum deliveries.\n3. Motion response brief and supporting declarations scheduled for submission ahead of hearing.",
                'type' => 'case_brief',
                'is_pinned' => true,
                'is_privileged' => true,
            ],
            [
                'title' => 'Privileged Conference Notes: Escrow Accounting & Settlement Parameters',
                'body' => "Confidential conference with General Counsel and Managing Partner:\n- Escrow account balance verified with escrow agent ($2.4M held in trust).\n- Opposing counsel opened preliminary mediation dialogue with mediator Judge Vance.\n- Client executive committee authorized settlement discussion range between $1.2M and $1.6M subject to mutual release.",
                'type' => 'privileged',
                'is_pinned' => true,
                'is_privileged' => true,
            ],
            [
                'title' => 'Deposition Outline: Examination of Former Operations Director',
                'body' => "Core examination points for upcoming remote deposition:\n- Establish timeline of email communications preceding contract renegotiation.\n- Confront deponent with internal audit findings regarding inventory write-offs.\n- Probe knowledge of third-party warranty claims and supply chain defect notifications.",
                'type' => 'trial_prep',
                'is_pinned' => false,
                'is_privileged' => true,
            ],
            [
                'title' => 'Procedural Case Status Briefing for Client Representative',
                'body' => "Official Counsel Update for Client:\n- Joint Rule 26(f) discovery report filed with the District Court clerk.\n- Scheduling order entered; initial disclosures exchanged with opposing counsel.\n- Next milestone: Exchange of document production requests and responses.",
                'type' => 'client_visible',
                'is_pinned' => false,
                'is_privileged' => false,
            ],
        ];

        foreach ($samples as $idx => $s) {
            $exists = static::where('firm_id', $firmId)
                ->where('title', $s['title'])
                ->exists();

            if (! $exists) {
                $matter = $matters[$idx % $matters->count()];
                static::create(array_merge($s, [
                    'firm_id' => $firmId,
                    'matter_id' => $matter->id,
                    'user_id' => $authorId,
                ]));
            }
        }
    }
}
