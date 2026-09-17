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

    public function category(): BelongsTo
    {
        return $this->belongsTo(NoteCategory::class, 'category_id');
    }
}
