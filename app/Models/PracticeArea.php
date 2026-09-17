<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeArea extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(PracticeAreaTask::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
