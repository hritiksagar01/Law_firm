<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class MessageThread extends Model
{
    use HasFactory;

    public const TYPE_CLIENT_COMMUNICATION = 'client_communication';

    public const TYPE_DOCUMENT_REQUEST = 'document_request';

    public const TYPE_GENERAL_MATTER = 'general_matter_communication';

    public const TYPE_INTERNAL_TEAM = 'internal_team';

    public const TYPES = [
        self::TYPE_CLIENT_COMMUNICATION => 'Client Communication',
        self::TYPE_DOCUMENT_REQUEST => 'Document Request',
        self::TYPE_GENERAL_MATTER => 'General Matter Communication',
        self::TYPE_INTERNAL_TEAM => 'Internal Chambers Communication',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_internal' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    /**
     * Boot model and register strict database authorization scopes.
     * Technical database separation: clients can NEVER query internal communications.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('clientDatabaseIsolation', function (Builder $builder) {
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();
                if ($user->isClient()) {
                    $builder->where('message_threads.is_internal', false)
                        ->where('message_threads.thread_type', '!=', self::TYPE_INTERNAL_TEAM);
                }
            }
        });

        static::creating(function (MessageThread $thread) {
            if (empty($thread->thread_number)) {
                $year = date('Y');
                $next = (static::withoutGlobalScopes()->whereYear('created_at', $year)->max('id') ?? 0) + 1;
                $thread->thread_number = sprintf('THR-%s-%04d', $year, $next);
            }
            if (empty($thread->last_message_at)) {
                $thread->last_message_at = now();
            }
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();
                if ($user->isClient()) {
                    // Force client threads to never be internal
                    $thread->is_internal = false;
                    if ($thread->thread_type === self::TYPE_INTERNAL_TEAM) {
                        $thread->thread_type = self::TYPE_CLIENT_COMMUNICATION;
                    }
                }
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'thread_id')->orderBy('created_at', 'asc');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'thread_id')->latestOfMany();
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->thread_type] ?? 'Matter Communication';
    }

    public function scopeClientVisible(Builder $query): Builder
    {
        return $query->where('is_internal', false)
            ->where('thread_type', '!=', self::TYPE_INTERNAL_TEAM);
    }

    public function scopeInternalOnly(Builder $query): Builder
    {
        return $query->where('is_internal', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('thread_type', $type);
    }

    public function unreadCountFor(?User $user = null): int
    {
        if (! $user) {
            return 0;
        }

        return $this->messages()
            ->where('is_read', false)
            ->where('sender_id', '!=', $user->id)
            ->count();
    }
}
