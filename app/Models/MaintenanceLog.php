<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'start_maintenance_date',
        'end_maintenance_date',
        'technician',
        'work_performed',
        'cost',
        'current_odometer',
    ];

    protected $appends = ['status'];

    public function getStatusAttribute()
    {
        $today = Carbon::today();
        $start = Carbon::parse($this->start_maintenance_date);
        $end = Carbon::parse($this->end_maintenance_date);

        if ($today->between($start, $end)) return 'In Progress';
        if ($today->gt($end)) return 'Completed';

        return 'Pending';
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
