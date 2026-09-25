<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matter extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'budget' => 'decimal:2',
        'opened_at' => 'date',
        'closed_at' => 'date',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function leadAttorney(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_attorney_id');
    }

    public const TEAM_ROLES = [
        'lead_attorney' => 'Lead Attorney',
        'supervising_attorney' => 'Supervising Attorney',
        'associate' => 'Associate Counsel',
        'paralegal' => 'Paralegal',
        'legal_assistant' => 'Legal Assistant',
        'case_manager' => 'Case Manager',
        'clerk' => 'Chamber Clerk',
        'staff' => 'Staff',
    ];

    public const ACCESS_LEVELS = [
        'read' => 'Read Only',
        'write' => 'Read & Write',
        'admin' => 'Full Administrative',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'access_level', 'assignment_date', 'removal_date', 'is_active'])
            ->withTimestamps();
    }

    public function teamMembers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_active', true);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(MatterActivity::class);
    }

    public function conflictChecks(): HasMany
    {
        return $this->hasMany(ConflictCheck::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function opinions(): HasMany
    {
        return $this->hasMany(Opinion::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function caseNotes(): HasMany
    {
        return $this->hasMany(CaseNote::class);
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function totalBilledAmount(): float
    {
        return (float) $this->timeEntries()->where('is_billable', true)->sum('total_amount');
    }

    public function totalHours(): float
    {
        return (float) $this->timeEntries()->sum('hours');
    }
}
