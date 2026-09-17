<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CaseNote::class, 'category_id');
    }
}
