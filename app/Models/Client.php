<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'trust_balance' => 'decimal:2',
        'date_of_birth' => 'date',
        'poa_date' => 'date',
        'invitation_sent_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
        'age' => 'integer',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primaryAttorney(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_attorney_id');
    }

    public function matters(): HasMany
    {
        return $this->hasMany(Matter::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ClientMember::class);
    }

    public function primarySignatory(): HasOne
    {
        return $this->hasOne(ClientMember::class)->where('is_primary_signatory', true);
    }

    // Helper Classification Methods
    public function isIndividual(): bool
    {
        return in_array($this->category ?? $this->type, ['individual']);
    }

    public function isJoint(): bool
    {
        return in_array($this->category, ['joint', 'multiple']);
    }

    public function isCorporate(): bool
    {
        return in_array($this->category ?? $this->type, ['corporate', 'company', 'llp']);
    }

    public function isInstitutional(): bool
    {
        return in_array($this->category, ['institution', 'trust', 'society', 'partnership', 'proprietorship']);
    }

    public function isOfflineOnly(): bool
    {
        return $this->onboarding_mode === 'assisted_offline' || $this->portal_status === 'offline_only';
    }

    public function isPortalActive(): bool
    {
        return $this->portal_status === 'active' || (! empty($this->user_id) && ! $this->isOfflineOnly());
    }

    // Indian Statutory & Court Attributes
    public function getMaskedAadhaarAttribute(): ?string
    {
        return $this->aadhaar_last_four ? 'XXXX-XXXX-'.$this->aadhaar_last_four : null;
    }

    public function getFormattedCourtTitleAttribute(): string
    {
        if ($this->father_husband_name && $this->isIndividual()) {
            return "{$this->name}, {$this->father_husband_name}";
        }

        return $this->name;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1 ?: $this->address,
            $this->address_line_2,
            $this->city,
            $this->district,
            $this->state,
            $this->pincode ? 'PIN: '.$this->pincode : null,
            $this->police_station ? '(P.S.: '.$this->police_station.')' : null,
        ]);

        return implode(', ', $parts);
    }

    // Invitation Token Lifecycle
    public function generateInvitationToken(): string
    {
        $token = Str::random(48);
        $this->update([
            'invitation_token' => $token,
            'invitation_sent_at' => now(),
            'invitation_expires_at' => now()->addDays(7),
            'portal_status' => 'invited',
        ]);

        return $token;
    }

    public function hasActiveInvitation(): bool
    {
        return ! empty($this->invitation_token)
            && $this->invitation_expires_at
            && $this->invitation_expires_at->isFuture();
    }

    // Scopes
    public function scopePortalActive(Builder $query): Builder
    {
        return $query->where('portal_status', 'active')->orWhereNotNull('user_id');
    }

    public function scopeAssistedOffline(Builder $query): Builder
    {
        return $query->where('onboarding_mode', 'assisted_offline')->orWhere('portal_status', 'offline_only');
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
