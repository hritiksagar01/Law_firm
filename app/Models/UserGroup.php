<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserGroup extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'group_permission');
    }
}
