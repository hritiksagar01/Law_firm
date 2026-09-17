<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }
}
