<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'dispatcher_id',
        'status',
        'planned_start_time',
        'actual_start_time',
        'route',
        'route_distance_km',
        'route_distance_minutes',
        'is_liquidation_required',
        'is_pre_trip_checked'
    ];

    public function stops()
    {
        return $this->hasMany(TripStop::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatcher_id');
    }

    public function liquidation()
    {
        return $this->hasOne(Liquidation::class);
    }
    
}