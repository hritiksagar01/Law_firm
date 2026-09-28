<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Message extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_privileged' => 'boolean',
        'is_internal' => 'boolean',
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Technical database separation: clients can NEVER query internal communications.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('clientDatabaseIsolation', function (Builder $builder) {
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();
                if ($user->isClient()) {
                    $builder->where('messages.is_internal', false);
                }
            }
        });

        static::creating(function (Message $message) {
            if (empty($message->message_number)) {
                $year = date('Y');
                $next = (static::withoutGlobalScopes()->whereYear('created_at', $year)->max('id') ?? 0) + 1;
                $message->message_number = sprintf('MSG-%s-%04d', $year, $next);
            }
            if (empty($message->sent_at)) {
                $message->sent_at = now();
            }
            if (empty($message->status)) {
                $message->status = 'sent';
            }
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();
                if ($user->isClient()) {
                    // Force client message to never be internal
                    $message->is_internal = false;
                }
            }
        });

        static::created(function (Message $message) {
            if ($message->thread_id) {
                MessageThread::withoutGlobalScopes()
                    ->where('id', $message->thread_id)
                    ->update(['last_message_at' => $message->created_at ?? now()]);
            }
        });
    }

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class, 'message_id');
    }

    public function getFormattedIdAttribute(): string
    {
        return $this->message_number ?? 'MSG-'.$this->id;
    }

    public function getHasAttachmentsAttribute(): bool
    {
        return ! empty($this->attachment_path) || $this->attachments()->exists();
    }

    public function markAsRead(?User $reader = null): void
    {
        if (! $this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
                'status' => 'read',
            ]);
        }
    }

    public function scopeClientVisible(Builder $query): Builder
    {
        return $query->where('messages.is_internal', false);
    }

    public function scopeInternalOnly(Builder $query): Builder
    {
        return $query->where('messages.is_internal', true);
    }

    /**
     * Ensure the firm has active sample client inquiries awaiting advocate reply in "Waiting on you".
     */
    public static function ensureSampleClientInquiriesForFirm(int $firmId): void
    {
        $clientInquiryCount = static::withoutGlobalScopes()
            ->where('firm_id', $firmId)
            ->whereHas('sender', fn ($q) => $q->where('role', 'client'))
            ->count();

        if ($clientInquiryCount >= 2) {
            return;
        }

        $firm = Firm::find($firmId);
        $currency = $firm?->currency ?? 'USD';

        $clientUser = User::where('firm_id', $firmId)->where('role', 'client')->first();
        $client = Client::where('firm_id', $firmId)->whereNotNull('user_id')->first()
            ?: Client::where('firm_id', $firmId)->first();

        if (! $clientUser) {
            if ($client && $client->user_id) {
                $clientUser = User::find($client->user_id);
            }
            if (! $clientUser) {
                $clientUser = User::create([
                    'firm_id' => $firmId,
                    'name' => $client ? ($client->contact_person ?? $client->name) : 'Corporate Legal Representative',
                    'email' => 'client.'.($client ? $client->id : uniqid()).'@clientportal.example',
                    'password' => bcrypt('password123'),
                    'role' => 'client',
                    'status' => 'active',
                ]);
            }
        }

        if ($client && ! $client->user_id) {
            $client->update(['user_id' => $clientUser->id]);
        }

        $matter = Matter::where('firm_id', $firmId)->first();
        if (! $matter) {
            $prefix = $currency === 'INR' ? 'CS(COMM)' : 'MAT';
            $caseNumber = sprintf('%s/%s/%04d-%03d', $prefix, date('Y'), $client?->id ?? 1, rand(100, 999));
            while (Matter::where('case_number', $caseNumber)->exists()) {
                $caseNumber = sprintf('%s/%s/%04d-%04d', $prefix, date('Y'), $client?->id ?? 1, rand(1000, 9999));
            }

            $matter = Matter::create([
                'firm_id' => $firmId,
                'client_id' => $client?->id,
                'case_number' => $caseNumber,
                'title' => $currency === 'INR' ? 'Commercial Arbitration & Debt Recovery Proceedings' : 'Commercial Contract & Supply Chain Litigation',
                'status' => 'open',
                'stage' => 'Discovery',
                'priority' => 'urgent',
                'billing_type' => 'hourly',
                'opened_at' => now()->subMonth(),
            ]);
        }

        $recipientId = $matter->lead_attorney_id
            ?: User::where('firm_id', $firmId)->whereIn('role', ['partner', 'associate'])->first()?->id
            ?: User::where('firm_id', $firmId)->first()?->id;

        $inquiries = $currency === 'INR' ? [
            [
                'subject' => 'Clarification on Section 11 Arbitration Filing Timeline',
                'body' => 'Dear Counsel, our board has approved the arbitration petition. Could you please confirm if the affidavit verification and Vakalatnama have been accepted by the High Court Registry for urgent listing?',
                'hours_ago' => 2,
            ],
            [
                'subject' => 'Disputed Invoice Ledger & Bank Vouchers Uploaded',
                'body' => 'We have uploaded the audited delivery ledger reconciliation and bank sanction letter in the document vault. Please review to ensure compliance with the Commercial Court rules.',
                'hours_ago' => 14,
            ],
            [
                'subject' => 'Supplementary Cause List Inquiry - Bench 4',
                'body' => 'Following up on our conference call yesterday: Has our interim relief application been included on the supplementary cause list before Courtroom 14 for tomorrow?',
                'hours_ago' => 26,
            ],
        ] : [
            [
                'subject' => 'Urgent: Pretrial Disclosure Exhibits & Deposition Timeline',
                'body' => 'Dear Counsel, our executive team has finalized the responses to the Rule 33 interrogatories. Could you review the exhibit list and confirm the scheduled deposition dates?',
                'hours_ago' => 3,
            ],
            [
                'subject' => 'Executed Master Services Agreement Schedule Uploaded',
                'body' => 'We have uploaded the signed amendment schedule and escrow transaction confirmations to the vault. Please let us know if any further documentation is required prior to conference call.',
                'hours_ago' => 16,
            ],
            [
                'subject' => 'Summary Judgment Motion Hearing Schedule',
                'body' => 'Please confirm if the District Court clerk has issued the hearing date for the oral arguments on our pending motion for summary judgment.',
                'hours_ago' => 28,
            ],
        ];

        foreach ($inquiries as $inq) {
            $exists = static::withoutGlobalScopes()
                ->where('firm_id', $firmId)
                ->where('body', $inq['body'])
                ->exists();

            if (! $exists) {
                static::create([
                    'firm_id' => $firmId,
                    'matter_id' => $matter->id,
                    'sender_id' => $clientUser->id,
                    'recipient_id' => $recipientId,
                    'subject' => $inq['subject'],
                    'body' => $inq['body'],
                    'is_internal' => false,
                    'is_read' => false,
                    'status' => 'sent',
                    'sent_at' => now()->subHours($inq['hours_ago']),
                    'created_at' => now()->subHours($inq['hours_ago']),
                ]);
            }
        }
    }
}
