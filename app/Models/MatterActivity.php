<?php

namespace App\Models;

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
        ?array $metadata = null
    ): self {
        return self::create([
            'firm_id' => $matter->firm_id,
            'matter_id' => $matter->id,
            'user_id' => $userId ?? auth()->id(),
            'client_id' => $clientId,
            'activity_type' => $activityType,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
