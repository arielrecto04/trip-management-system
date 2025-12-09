<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = [
        'has_in_progress_maintenance'
    ];

    protected static function booted()
    {
        static::deleting(function ($vehicle) {

            foreach ($vehicle->attachments as $imageAttachment) {
                Storage::disk('public')->delete($imageAttachment->url);
                $imageAttachment->delete();
            }

            foreach ($vehicle->complianceDocs as $doc) {

                foreach ($doc->attachments as $docAttachment) {
                    Storage::disk('public')->delete($docAttachment->url);
                    $docAttachment->delete();
                }

                $doc->delete();
            }
        });
    }

    public function maintenances()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function getHasInProgressMaintenanceAttribute()
    {
        $today = now()->startOfDay();
        return $this->maintenances
                    ->contains(function($maintenance) use ($today) {
                        $start = Carbon::parse($maintenance->start_maintenance_date)->startOfDay();
                        $end   = Carbon::parse($maintenance->end_maintenance_date)->endOfDay();
                        return $today->between($start, $end);
                    });
    }

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
        return $this->morphMany(ComplianceDoc::class, 'compliable');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
