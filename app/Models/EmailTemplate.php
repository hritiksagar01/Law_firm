<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'variables' => 'array',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }
}
