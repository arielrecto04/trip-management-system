<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'vin',
        'brand',
        'model',
        'capacity_kg',
        'capacity_m3',
        'is_active',
        'license_plate',
    ];

    public function currentTrip()
    {
        return $this->belongsTo(Trip::class, 'current_trip_id');
    }

    public function driverHistory()
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
}
