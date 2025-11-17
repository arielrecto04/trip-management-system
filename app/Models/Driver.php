<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    /** @use HasFactory<\Database\Factories\DriverFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'license_restriction',
        'license_expiration',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicleHistory()
    {
        return $this->hasMany(DriverVehicleHistory::class);
    }

    public function complianceDocs()
    {
        return $this->morphMany(ComplianceDocs::class, 'compliable');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
