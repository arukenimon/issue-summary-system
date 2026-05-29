<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    // Comments only carry a created_at timestamp; disable updated_at management.
    public const UPDATED_AT = null;

    protected $fillable = [
        'author_name',
        'body',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
