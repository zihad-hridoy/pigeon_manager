<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PigeonMetadata extends Model
{
    protected $table = 'pigeon_metadata';

    protected $fillable = [
        'type',
        'item_value',
    ];
}
