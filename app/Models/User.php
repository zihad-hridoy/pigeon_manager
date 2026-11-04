<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'loft_name',
        'phone',
        'profile_pic_path',
        'bird_limit',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function pigeons(): HasMany
    {
        return $this->hasMany(Pigeon::class, 'owner_id');
    }
}
