<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    public const STATUS_NOT_STARTED = 'not_started';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_WAITING = 'waiting';

    public const STATUS_BLOCKED = 'blocked';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_NOT_STARTED => 'Not Started',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_WAITING => 'Waiting',
        self::STATUS_BLOCKED => 'Blocked',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_NORMAL = 'normal';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    public const PRIORITY_CRITICAL = 'critical';

    public const PRIORITIES = [
        self::PRIORITY_LOW => 'Low',
        self::PRIORITY_NORMAL => 'Normal',
        self::PRIORITY_HIGH => 'High',
        self::PRIORITY_URGENT => 'Urgent',
        self::PRIORITY_CRITICAL => 'Critical',
    ];

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_date' => 'date',
        'tags' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->task_number)) {
                $year = date('Y');
                $next = (static::whereYear('created_at', $year)->max('id') ?? 0) + 1;
                $task->task_number = sprintf('TSK-%s-%04d', $year, $next);
            }

            if (empty($task->priority)) {
                $task->priority = self::PRIORITY_NORMAL;
            }

            if (empty($task->status)) {
                $task->status = self::STATUS_NOT_STARTED;
            }

            if ($task->status === self::STATUS_COMPLETED && empty($task->completed_date)) {
                $task->completed_date = now()->toDateString();
            }

            // Derive related client from matter if not explicitly set
            if (empty($task->related_client_id) && ! empty($task->matter_id)) {
                $task->related_client_id = $task->matter?->client_id;
            }
        });

        static::saving(function (Task $task) {
            if ($task->status === self::STATUS_COMPLETED) {
                if (empty($task->completed_date)) {
                    $task->completed_date = now()->toDateString();
                }
            } else {
                $task->completed_date = null;
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

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function relatedDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'related_document_id');
    }

    public function relatedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'related_client_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function getFormattedIdAttribute(): string
    {
        return $this->task_number ?? 'TSK-'.$this->id;
    }

    public function getStatusLabelAttribute(): string
    {
        // Handle legacy 'todo' if present in data
        if ($this->status === 'todo') {
            return 'Not Started';
        }

        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getPriorityLabelAttribute(): string
    {
        // Handle legacy 'medium' if present in data
        if ($this->priority === 'medium') {
            return 'Normal';
        }

        return self::PRIORITIES[$this->priority] ?? ucfirst($this->priority);
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            self::STATUS_IN_PROGRESS => 'bg-blue-50 text-blue-800 border-blue-200',
            self::STATUS_WAITING => 'bg-amber-50 text-amber-800 border-amber-200',
            self::STATUS_BLOCKED => 'bg-rose-50 text-rose-800 border-rose-200',
            self::STATUS_CANCELLED => 'bg-stone-100 text-stone-600 border-stone-200',
            default => 'bg-[#f5f3ed] text-[#23493a] border-[#e5e3dc]', // Not Started
        };
    }

    public function getPriorityBadgeClassesAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_CRITICAL => 'bg-rose-100 text-rose-800 border-rose-300 font-bold',
            self::PRIORITY_URGENT => 'bg-red-50 text-red-700 border-red-200 font-semibold',
            self::PRIORITY_HIGH => 'bg-amber-50 text-amber-800 border-amber-200 font-medium',
            self::PRIORITY_NORMAL => 'bg-sky-50 text-sky-800 border-sky-200',
            default => 'bg-stone-100 text-stone-600 border-stone-200', // Low
        };
    }

    public function isOverdue(): bool
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            return false;
        }

        return $this->due_date && $this->due_date->isPast() && ! $this->due_date->isToday();
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED])
            ->where('due_date', '<', Carbon::today());
    }

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeOfPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Ensure a firm has active sample litigation tasks with overdue, due today, and upcoming milestones.
     */
    public static function ensureSampleTasksForFirm(int $firmId, ?int $userId = null): void
    {
        $incompleteCount = static::where('firm_id', $firmId)
            ->where('status', '!=', self::STATUS_COMPLETED)
            ->count();

        $firm = Firm::find($firmId);
        $currency = $firm?->currency ?? 'USD';

        if ($incompleteCount < 4) {
            $matter = Matter::where('firm_id', $firmId)->first();
            if (! $matter) {
                $client = Client::where('firm_id', $firmId)->first();
                if (! $client) {
                    $client = Client::create([
                        'firm_id' => $firmId,
                        'name' => $currency === 'INR' ? 'Premier Industrial Enterprises' : 'Beacon Global Enterprises Inc.',
                        'type' => 'corporate',
                        'contact_person' => 'Corporate Legal Representative',
                        'email' => 'contact@'.($firm?->slug ?? 'client').'.example',
                        'phone' => '+1 (555) 019-2831',
                        'status' => 'active',
                    ]);
                }

                $matter = Matter::create([
                    'firm_id' => $firmId,
                    'client_id' => $client->id,
                    'case_number' => $currency === 'INR' ? 'DEL/2026/0142' : '2026-0142',
                    'title' => $currency === 'INR' ? 'Commercial Arbitration & Recovery Proceedings' : 'Commercial Contract & Supply Chain Litigation',
                    'status' => 'open',
                    'stage' => 'Litigation',
                    'priority' => 'urgent',
                    'lead_attorney_id' => $userId,
                    'billing_type' => 'hourly',
                    'budget' => 150000,
                    'opened_at' => Carbon::now()->subMonth(),
                ]);
            }

            $matters = Matter::where('firm_id', $firmId)->get();

            $samples = $currency === 'INR' ? [
                [
                    'title' => 'File Sworn Statement of Truth & Vakalatnama at High Court Registry',
                    'description' => 'Ensure affidavit is duly notarized and signed with client verification stamp for electronic filing before cutoff.',
                    'priority' => self::PRIORITY_URGENT,
                    'status' => self::STATUS_NOT_STARTED,
                    'due_date' => Carbon::now()->subDays(1)->startOfDay(),
                ],
                [
                    'title' => 'Draft Rejoinder & Comparative Statement of Claims',
                    'description' => 'Address preliminary objections raised in Bank written statement; cite Hon. SC ruling in Union of India v. D.N. Revri.',
                    'priority' => self::PRIORITY_URGENT,
                    'status' => self::STATUS_IN_PROGRESS,
                    'due_date' => Carbon::now()->startOfDay(),
                ],
                [
                    'title' => 'Prepare Cross-Examination Brief & Exhibit Dossier for Hearing',
                    'description' => 'Collate petitioner lease deeds, municipal notices, and revenue survey maps into indexed judge bundle.',
                    'priority' => self::PRIORITY_HIGH,
                    'status' => self::STATUS_NOT_STARTED,
                    'due_date' => Carbon::now()->addDay()->startOfDay(),
                ],
                [
                    'title' => 'Client Briefing & Interlocutory Affidavit Verification',
                    'description' => 'Conduct pre-hearing briefing with authorized signatory to verify affidavit deposition and evidence exhibits.',
                    'priority' => self::PRIORITY_HIGH,
                    'status' => self::STATUS_NOT_STARTED,
                    'due_date' => Carbon::now()->addDays(2)->startOfDay(),
                ],
                [
                    'title' => 'Review Resolution Plan & Committee of Creditors Protocol',
                    'description' => 'Scrutinize prospective resolution applicant submissions against Section 29A disqualification criteria.',
                    'priority' => self::PRIORITY_URGENT,
                    'status' => self::STATUS_IN_PROGRESS,
                    'due_date' => Carbon::now()->addDays(3)->startOfDay(),
                ],
                [
                    'title' => 'Scrutinize Opposing Counsel\'s Additional Documents & Rebuttal Memo',
                    'description' => 'Review railway demurrage vouchers and draft itemized objection schedule for the arbitrator.',
                    'priority' => self::PRIORITY_NORMAL,
                    'status' => self::STATUS_NOT_STARTED,
                    'due_date' => Carbon::now()->addDays(4)->startOfDay(),
                ],
            ] : [
                [
                    'title' => 'File Notice of Appearance & Corporate Disclosure Statement at District Court',
                    'description' => 'Complete Rule 7.1 corporate disclosure statement and electronically file notice of lead counsel appearance.',
                    'priority' => self::PRIORITY_URGENT,
                    'status' => self::STATUS_NOT_STARTED,
                    'due_date' => Carbon::now()->subDays(1)->startOfDay(),
                ],
                [
                    'title' => 'Draft Summary Judgment Motion & Responsive Statement of Undisputed Facts',
                    'description' => 'Finalize Rule 56 motion with citations to deposition transcripts and counter-affidavits.',
                    'priority' => self::PRIORITY_URGENT,
                    'status' => self::STATUS_IN_PROGRESS,
                    'due_date' => Carbon::now()->startOfDay(),
                ],
                [
                    'title' => 'Prepare Cross-Examination & Deposition Exhibit Binder for Hearing',
                    'description' => 'Assemble marked trial exhibits, witness outline, and prior contradictory statements for hearing.',
                    'priority' => self::PRIORITY_HIGH,
                    'status' => self::STATUS_NOT_STARTED,
                    'due_date' => Carbon::now()->addDay()->startOfDay(),
                ],
                [
                    'title' => 'Client Case Conference & Interlocutory Affidavit Verification',
                    'description' => 'Meet with corporate representative to finalize verified interrogatory answers and production disclosures.',
                    'priority' => self::PRIORITY_HIGH,
                    'status' => self::STATUS_NOT_STARTED,
                    'due_date' => Carbon::now()->addDays(2)->startOfDay(),
                ],
                [
                    'title' => 'Review Transaction Covenants & Closing Disclosure Protocol',
                    'description' => 'Audit acquisition escrow conditions and regulatory filings prior to transaction cutoff.',
                    'priority' => self::PRIORITY_URGENT,
                    'status' => self::STATUS_IN_PROGRESS,
                    'due_date' => Carbon::now()->addDays(3)->startOfDay(),
                ],
                [
                    'title' => 'Scrutinize Opposing Counsel\'s Expert Rebuttal Disclosures',
                    'description' => 'Evaluate economic expert report methodology and prepare Daubert challenge motions.',
                    'priority' => self::PRIORITY_NORMAL,
                    'status' => self::STATUS_NOT_STARTED,
                    'due_date' => Carbon::now()->addDays(4)->startOfDay(),
                ],
            ];

            $assigneeId = $userId
                ?: User::where('firm_id', $firmId)->whereIn('role', ['partner', 'associate', 'superadmin'])->first()?->id
                ?: User::where('firm_id', $firmId)->first()?->id
                ?: User::first()?->id;

            foreach ($samples as $idx => $sample) {
                $targetMatter = $matters[$idx % $matters->count()] ?? $matter;
                static::create(array_merge($sample, [
                    'firm_id' => $firmId,
                    'matter_id' => $targetMatter->id,
                    'assigned_to' => $assigneeId,
                    'created_by' => $assigneeId,
                ]));
            }
        }

        // If user is provided and has 0 active tasks assigned to them, assign top active firm tasks to this user
        if ($userId) {
            $userTasksCount = static::where('firm_id', $firmId)
                ->where('assigned_to', $userId)
                ->where('status', '!=', self::STATUS_COMPLETED)
                ->count();

            if ($userTasksCount === 0) {
                static::where('firm_id', $firmId)
                    ->where('status', '!=', self::STATUS_COMPLETED)
                    ->take(3)
                    ->update(['assigned_to' => $userId]);
            }
        }
    }
}
