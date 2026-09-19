<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Firm extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'practice_areas' => 'array',
        'default_hourly_rate' => 'decimal:2',
        'allow_client_signup' => 'boolean',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function matters(): HasMany
    {
        return $this->hasMany(Matter::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Derive "Valid Upto" from the firm's latest active subscription.
     * Returns Carbon date or null if no subscription exists.
     */
    public function getValidUptoAttribute(): ?Carbon
    {
        $sub = $this->relationLoaded('currentSubscription')
            ? $this->currentSubscription
            : $this->currentSubscription()->first();

        return $sub?->ends_at;
    }

    /**
     * Return display_name if set, otherwise fall back to name.
     */
    public function getDisplayTitleAttribute(): string
    {
        return $this->display_name ?: $this->name;
    }

    /**
     * Get the firm's primary admin user (first partner user).
     */
    public function primaryAdmin(): HasOne
    {
        return $this->hasOne(User::class)->where('role', 'partner')->oldestOfMany();
    }
}
