<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pigeon extends Model
{
    use HasFactory;

    protected $fillable = [
        'ring_number',
        'nickname',
        'gender',
        'breed_type',
        'strain_breed',
        'color',
        'hatch_date',
        'status',
        'father_id',
        'mother_id',
        'owner_id',
        'profile_pic_path',
        'eye_pic_path',
        'wing_pic_path',
        'notes',
    ];

    protected $casts = [
        'hatch_date' => 'date',
    ];

    public function father(): BelongsTo
    {
        return $this->belongsTo(Pigeon::class, 'father_id');
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Pigeon::class, 'mother_id');
    }

    public function offspringAsFather(): HasMany
    {
        return $this->hasMany(Pigeon::class, 'father_id');
    }

    public function offspringAsMother(): HasMany
    {
        return $this->hasMany(Pigeon::class, 'mother_id');
    }

    public function offspring()
    {
        return Pigeon::where('father_id', $this->id)
            ->orWhere('mother_id', $this->id);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PigeonMedia::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(VaccinationRecord::class);
    }

    public function diseaseRecords(): HasMany
    {
        return $this->hasMany(DiseaseRecord::class);
    }

    public function flightRecords(): HasMany
    {
        return $this->hasMany(FlightRecord::class);
    }
}
