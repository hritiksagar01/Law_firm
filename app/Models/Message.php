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
}
