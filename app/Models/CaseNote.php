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
}
