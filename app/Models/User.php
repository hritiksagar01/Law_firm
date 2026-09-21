<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'hourly_rate' => 'decimal:2',
            'id_expiration' => 'date',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get full display name. Uses first_name + middle_name + surname if set, else falls back to name.
     */
    public function getFullDisplayNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->surname]);
        if (! empty($parts)) {
            return implode(' ', $parts);
        }

        return $this->name;
    }

    /**
     * Get resolved avatar — returns avatar_url, or uploaded file URL, or null for fallback.
     */
    public function getResolvedAvatarAttribute(): ?string
    {
        if ($this->avatar_url) {
            if (str_starts_with($this->avatar_url, 'http://') || str_starts_with($this->avatar_url, 'https://')) {
                return $this->avatar_url;
            }

            return asset('storage/'.$this->avatar_url);
        }

        return null;
    }

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function matters(): BelongsToMany
    {
        return $this->belongsToMany(Matter::class);
    }

    public function leadMatters(): HasMany
    {
        return $this->hasMany(Matter::class, 'lead_attorney_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function signInHistories(): HasMany
    {
        return $this->hasMany(SignInHistory::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'group_user');
    }

    public function hasRole(string|array $roles): bool
    {
        $roleList = is_array($roles) ? $roles : [$roles];

        if (in_array($this->role, $roleList)) {
            return true;
        }

        if ($this->roleRelation && in_array($this->roleRelation->slug, $roleList)) {
            return true;
        }

        return false;
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check via assigned Role model
        if ($this->relationLoaded('roleRelation')) {
            if ($this->roleRelation && $this->roleRelation->hasPermission($permissionSlug)) {
                return true;
            }
        } elseif ($this->role_id) {
            $role = $this->roleRelation()->with('permissions')->first();
            if ($role && $role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        // Check via User Groups
        foreach ($this->groups()->with('permissions')->get() as $group) {
            if ($group->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        // Fallback default permissions based on role string
        $defaultMatrix = [
            'admin' => ['*'],
            'partner' => ['*'],
            'lawyer' => [
                'matters.view', 'matter.view', 'matters.create', 'matter.create', 'matters.edit', 'matter.edit', 'matters.close', 'matter.close', 'matters.assign',
                'clients.view', 'client.view', 'clients.create', 'clients.edit',
                'documents.view', 'document.view', 'documents.upload', 'document.upload', 'documents.download', 'document.download', 'documents.share', 'document.share', 'documents.request', 'document.request',
                'message.view', 'message.send',
                'tasks.view', 'task.view', 'tasks.create', 'task.create', 'tasks.edit', 'tasks.assign', 'task.assign', 'tasks.complete', 'task.complete',
                'note.view', 'note.create',
                'opinions.view', 'opinions.create', 'opinions.review', 'opinions.publish',
                'calendar.view', 'hearings.manage', 'appointments.manage',
                'reports.view', 'reports.export',
            ],
            'associate' => [
                'matters.view', 'matter.view', 'matters.create', 'matter.create', 'matters.edit', 'matter.edit', 'matters.close', 'matter.close', 'matters.assign',
                'clients.view', 'client.view', 'clients.create', 'clients.edit',
                'documents.view', 'document.view', 'documents.upload', 'document.upload', 'documents.download', 'document.download', 'documents.share', 'document.share', 'documents.request', 'document.request',
                'message.view', 'message.send',
                'tasks.view', 'task.view', 'tasks.create', 'task.create', 'tasks.edit', 'tasks.assign', 'task.assign', 'tasks.complete', 'task.complete',
                'note.view', 'note.create',
                'opinions.view', 'opinions.create', 'opinions.review', 'opinions.publish',
                'calendar.view', 'hearings.manage', 'appointments.manage',
                'reports.view', 'reports.export',
            ],
            'paralegal' => [
                'matters.view', 'matter.view', 'clients.view', 'client.view',
                'documents.view', 'document.view', 'documents.upload', 'document.upload', 'documents.download', 'document.download',
                'message.view',
                'tasks.view', 'task.view', 'tasks.create', 'task.create', 'tasks.edit', 'tasks.complete', 'task.complete',
                'note.view', 'note.create',
                'opinions.view', 'opinions.create',
                'calendar.view', 'hearings.manage', 'appointments.manage',
            ],
            'support_staff' => ['clients.view', 'client.view', 'calendar.view', 'appointments.manage', 'tasks.view', 'task.view'],
            'client' => ['portal.access', 'client.view', 'document.view', 'matters.view'],
        ];

        $currentRole = $this->roleRelation ? $this->roleRelation->slug : $this->role;
        $allowed = $defaultMatrix[$currentRole] ?? [];

        return in_array('*', $allowed) || in_array($permissionSlug, $allowed);
    }

    public function isPartner(): bool
    {
        return in_array($this->role, ['partner', 'senior_partner', 'admin']) || ($this->roleRelation && in_array($this->roleRelation->slug, ['admin', 'partner']));
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'partner', 'superadmin']) || ($this->roleRelation && in_array($this->roleRelation->slug, ['admin', 'partner']));
    }

    public function isAttorney(): bool
    {
        return in_array($this->role, ['partner', 'senior_partner', 'associate', 'lawyer', 'admin']) || ($this->roleRelation && in_array($this->roleRelation->slug, ['admin', 'partner', 'lawyer']));
    }

    public function isLawyer(): bool
    {
        return $this->isAttorney();
    }

    public function isParalegal(): bool
    {
        return $this->role === 'paralegal' || ($this->roleRelation && $this->roleRelation->slug === 'paralegal');
    }

    public function isSupportStaff(): bool
    {
        return in_array($this->role, ['support_staff', 'support', 'finance']) || ($this->roleRelation && $this->roleRelation->slug === 'support_staff');
    }

    public function isClient(): bool
    {
        return $this->role === 'client' || ($this->roleRelation && $this->roleRelation->slug === 'client');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }
}
