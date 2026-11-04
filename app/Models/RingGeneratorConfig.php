<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RingGeneratorConfig extends Model
{
    protected $fillable = [
        'owner_id',
        'prefix',
        'year_format',
        'last_sequence_number',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
