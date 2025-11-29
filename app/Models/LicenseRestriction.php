<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseRestriction extends Model
{
    protected $fillable = [
        'driver_id',
        'code'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
