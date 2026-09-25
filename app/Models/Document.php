<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_client_visible' => 'boolean',
        'tags' => 'array',
    ];

    public const DOCUMENT_TYPES = [
        'Pleading' => 'Pleading',
        'Motion' => 'Motion',
        'Brief' => 'Brief',
        'Order' => 'Court Order',
        'Judgment' => 'Judgment / Decree',
        'Contract' => 'Contract / Agreement',
        'Correspondence' => 'Correspondence / Legal Notice',
        'Discovery' => 'Discovery / Interrogatories',
        'Deposition' => 'Deposition / Statement',
        'Evidence' => 'Evidence / Material Object',
        'Exhibit' => 'Exhibit',
        'Affidavit' => 'Affidavit / Oath',
        'Declaration' => 'Declaration',
        'Court Filing' => 'Court Filing / Petition',
        'Notice' => 'Notice / Summons',
        'Legal Research' => 'Legal Research Memo',
        'Client Document' => 'Client Document / KYC',
        'Financial Document' => 'Financial / Audit Record',
        'Medical Record' => 'Medical / Medico-Legal Record',
        'Other' => 'Other',
    ];

    public const CLASSIFICATIONS = [
        'public' => 'Public',
        'internal' => 'Internal Chambers',
        'confidential' => 'Confidential',
        'highly_confidential' => 'Highly Confidential',
        'attorney_client_privileged' => 'Attorney-Client Privileged',
        'attorney_work_product' => 'Attorney Work Product',
    ];

    public const VISIBILITIES = [
        'internal_only' => 'Internal Chambers Only',
        'attorney_only' => 'Attorneys Only',
        'legal_team' => 'Legal Team Only',
        'client_visible' => 'Client Portal Visible',
        'restricted' => 'Restricted Access',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }
}
