<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccinationRecord extends Model
{
    protected $fillable = [
        'pigeon_id',
        'vaccine_name',
        'administered_date',
        'next_due_date',
        'notes',
    ];

    protected $casts = [
        'administered_date' => 'date',
        'next_due_date' => 'date',
    ];

    public function pigeon(): BelongsTo
    {
        return $this->belongsTo(Pigeon::class);
    }
}
