<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdType extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function generateNextNumber(): string
    {
        $seq = str_pad($this->next_number, 4, '0', STR_PAD_LEFT);
        $year = date('Y');
        $prefix = $this->prefix ?? 'HO';

        $number = str_replace(
            ['{PREFIX}', '{YYYY}', '{SEQ}'],
            [$prefix, $year, $seq],
            $this->format_mask ?? '{PREFIX}-{YYYY}-{SEQ}'
        );

        $this->increment('next_number');

        return $number;
    }
}
