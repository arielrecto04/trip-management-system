<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function userRoles()
    {
        return $this->hasMany(RoleUser::class);
    }
}
