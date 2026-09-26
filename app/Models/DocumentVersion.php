<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
    ];

    public const STATUS_DRAFT = 'Draft';

    public const STATUS_REVIEW = 'Review';

    public const STATUS_FINAL = 'Final';

    public const STATUS_EXECUTED = 'Executed';

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'previous_version_id');
    }

    public function nextVersions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'previous_version_id');
    }

    public function getFilenameAttribute(?string $value): string
    {
        if (! empty($value)) {
            return $value;
        }

        if ($this->relationLoaded('document') && $this->document && ! empty($this->document->filename)) {
            return $this->document->filename;
        }

        return $this->file_path ? basename($this->file_path) : 'document_v'.$this->version_number.'.pdf';
    }

    public function getChangeDescriptionAttribute(?string $value): ?string
    {
        return $value ?: $this->change_summary;
    }

    public function getVersionStatusAttribute(?string $value): string
    {
        return $value ?: 'Draft';
    }

    public function isCurrentVersion(): bool
    {
        if ($this->relationLoaded('document') && $this->document) {
            return (int) $this->document->version === (int) $this->version_number;
        }

        return false;
    }

    public function formattedLabel(): string
    {
        $status = $this->version_status ?: 'Draft';

        return match (strtolower($status)) {
            'final' => 'Final (v'.$this->version_number.')',
            'executed' => 'Executed (v'.$this->version_number.')',
            'review' => 'Review v'.$this->version_number,
            default => 'Draft v'.$this->version_number,
        };
    }

    public function formattedSize(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }
}
