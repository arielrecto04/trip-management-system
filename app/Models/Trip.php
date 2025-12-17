<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'warehouse_id',
        'dispatcher_id',
        'status',
        'planned_start_time',
        'actual_start_time',
        'route',
        'route_distance_km',
        'is_liquidation_required',
        'is_pre_trip_checked',
        'customer_name',
        'customer_address',
        'latitude',
        'longitude',
    ];

    protected $appends = ['computed_status'];

    public function getComputedStatusAttribute()
    {
        $now = now();
        $planned = $this->planned_start_time ? Carbon::parse($this->planned_start_time) : null;

        if(!$planned) return $this->status;

        if($now->lt($planned)) {
            return 'pending';
        }

        if($now->gte($planned) && !in_array($this->status, ['completed', 'cancelled'])) {
            return 'in progress';
        }

        return $this->status;
    }

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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    
}