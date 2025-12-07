<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'maintenance_date',
        'technician',
        'work_performed',
        'cost',
        'current_odometer'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
