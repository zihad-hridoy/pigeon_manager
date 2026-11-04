<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseRecord extends Model
{
    protected $fillable = [
        'pigeon_id',
        'disease_name',
        'diagnosis_date',
        'treatment_given',
        'is_quarantined',
        'recovery_status',
        'recovery_date',
        'notes',
    ];

    protected $casts = [
        'diagnosis_date' => 'date',
        'recovery_date' => 'date',
        'is_quarantined' => 'boolean',
    ];

    public function pigeon(): BelongsTo
    {
        return $this->belongsTo(Pigeon::class);
    }
}
