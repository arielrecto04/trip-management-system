<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverVehicleHistory extends Model
{
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'assigned_at',
        'unassigned_at'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
