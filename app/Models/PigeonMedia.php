<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PigeonMedia extends Model
{
    protected $table = 'pigeon_media';

    protected $fillable = [
        'pigeon_id',
        'media_type',
        'file_path',
        'title',
    ];

    public function pigeon(): BelongsTo
    {
        return $this->belongsTo(Pigeon::class);
    }
}
