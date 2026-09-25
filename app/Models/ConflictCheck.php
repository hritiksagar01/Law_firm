<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ConflictCheck extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'checked_at' => 'datetime',
        'review_date' => 'datetime',
        'conflict_identified' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ConflictCheck $check) {
            if (empty($check->uuid)) {
                $check->uuid = (string) Str::uuid();
            }
            if (empty($check->check_number)) {
                $year = date('Y');
                $seq = str_pad((string) (static::where('firm_id', $check->firm_id)->count() + 1), 4, '0', STR_PAD_LEFT);
                $check->check_number = "CC-{$year}-{$seq}";
            }
        });
    }

    public const STATUS_PENDING = 'pending';

    public const STATUS_CLEAR = 'clear';

    public const STATUS_POTENTIAL_CONFLICT = 'potential_conflict';

    public const STATUS_CONFLICT_IDENTIFIED = 'conflict_identified';

    public const STATUS_WAIVER_REQUIRED = 'waiver_required';

    public const STATUS_CLEARED_BY_ATTORNEY = 'cleared_by_attorney';

    public const STATUS_REJECTED = 'rejected';

    /** @var array<string, string> Status labels for display */
    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_CLEAR => 'Clear',
        self::STATUS_POTENTIAL_CONFLICT => 'Potential Conflict',
        self::STATUS_CONFLICT_IDENTIFIED => 'Conflict Identified',
        self::STATUS_WAIVER_REQUIRED => 'Waiver Required',
        self::STATUS_CLEARED_BY_ATTORNEY => 'Cleared by Attorney',
        self::STATUS_REJECTED => 'Rejected',
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

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
}
