<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultDocumentCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
