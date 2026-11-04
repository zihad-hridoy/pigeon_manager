<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightRecord extends Model
{
    protected $fillable = [
        'pigeon_id',
        'event_type',
        'flight_date',
        'distance_km',
        'duration_minutes',
        'flight_speed_mpm',
        'rank_position',
        'weather_condition',
        'notes',
    ];

    protected $casts = [
        'flight_date' => 'date',
    ];

    public function pigeon(): BelongsTo
    {
        return $this->belongsTo(Pigeon::class);
    }
}
