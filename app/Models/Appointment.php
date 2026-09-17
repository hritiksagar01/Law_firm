<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function attorney(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())->orderBy('scheduled_at', 'asc');
    }

    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now())->orderBy('scheduled_at', 'desc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }
}
