<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientMember extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_primary_signatory' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function getMaskedAadhaarAttribute(): ?string
    {
        return $this->aadhaar_last_four ? 'XXXX-XXXX-'.$this->aadhaar_last_four : null;
    }
}
