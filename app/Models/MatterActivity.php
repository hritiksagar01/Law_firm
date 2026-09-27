<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MatterActivity extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'is_client_safe' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    /** @var array<string, string> Activity type icons for timeline display */
    public const TYPE_ICONS = [
        'matter_created' => 'folder_open',
        'document_uploaded' => 'upload_file',
        'document_viewed' => 'visibility',
        'document_downloaded' => 'download',
        'document_shared' => 'share',
        'message_sent' => 'chat',
        'task_created' => 'add_task',
        'task_completed' => 'task_alt',
        'note_added' => 'edit_note',
        'status_changed' => 'swap_horiz',
        'client_activity' => 'person',
        'calendar_event' => 'event',
        'assignment_changed' => 'group_add',
        'conflict_check_completed' => 'policy',
        'complaint_drafted' => 'description',
        'complaint_filed' => 'gavel',
        'hearing_scheduled' => 'calendar_month',
        'medical_records_reviewed' => 'medical_information',
        'milestone' => 'flag',
        'procedural_milestone' => 'timeline',
    ];

    /** @var array<string, string> Human-readable activity labels */
    public const TYPE_LABELS = [
        'matter_created' => 'Matter Initiated',
        'document_uploaded' => 'Document Uploaded',
        'document_viewed' => 'Document Viewed',
        'document_downloaded' => 'Document Downloaded',
        'document_shared' => 'Document Shared',
        'message_sent' => 'Message Sent',
        'task_created' => 'Task Created',
        'task_completed' => 'Task Completed',
        'note_added' => 'Case Note Recorded',
        'status_changed' => 'Status Changed',
        'client_activity' => 'Client Activity',
        'calendar_event' => 'Calendar Event',
        'assignment_changed' => 'Assignment Changed',
        'conflict_check_completed' => 'Conflict Check',
        'complaint_drafted' => 'Complaint Drafted',
        'complaint_filed' => 'Complaint Filed',
        'hearing_scheduled' => 'Hearing Scheduled',
        'medical_records_reviewed' => 'Medical Records Reviewed',
        'milestone' => 'Milestone Recorded',
        'procedural_milestone' => 'Procedural Milestone',
    ];

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Polymorphic subject (Document, Task, Message, CaseNote, Event, etc.)
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->activity_type] ?? 'info';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->activity_type] ?? ucwords(str_replace('_', ' ', $this->activity_type));
    }

    public function getEffectiveOccurredAtAttribute(): Carbon
    {
        return $this->occurred_at ?? $this->created_at ?? now();
    }

    /**
     * Scope for client-safe activities (for client portal & unprivileged views)
     */
    public function scopeClientSafe($query, bool $clientSafe = true)
    {
        return $query->where('is_client_safe', $clientSafe);
    }

    /**
     * Order chronologically by effective date
     */
    public function scopeChronological($query, string $direction = 'desc')
    {
        $dir = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        return $query->orderByRaw("COALESCE(occurred_at, created_at) {$dir}")->orderBy('id', $dir);
    }

    /**
     * Helper to log an activity on a matter.
     */
    public static function log(
        Matter $matter,
        string $activityType,
        string $description,
        ?Model $subject = null,
        ?int $userId = null,
        ?int $clientId = null,
        ?array $metadata = null,
        bool $isClientSafe = true,
        ?\DateTimeInterface $occurredAt = null
    ): self {
        return self::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'user_id' => $userId ?? auth()->id(),
            'client_id' => $clientId ?? $matter->client_id,
            'activity_type' => $activityType,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
            'metadata' => $metadata,
            'is_client_safe' => $isClientSafe,
            'occurred_at' => $occurredAt ?? now(),
        ]);
    }
}
