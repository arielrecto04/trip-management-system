<?php

namespace App\Models;

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
