<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'is_court_vacation' => 'boolean',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }
}
