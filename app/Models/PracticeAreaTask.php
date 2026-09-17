<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeAreaTask extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function practiceArea(): BelongsTo
    {
        return $this->belongsTo(PracticeArea::class);
    }
}
