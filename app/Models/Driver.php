<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Driver extends Model
{
    /** @use HasFactory<\Database\Factories\DriverFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'license_expiration',
    ];

    protected $with = [
        'user',
        'licenseRestrictions',
    ];

    protected static function booted()
    {
        static::deleting(function ($driver) {

            foreach ($driver->complianceDocs as $doc) {

                foreach ($doc->attachments as $docAttachment) {
                    Storage::disk('public')->delete($docAttachment->url);
                    $docAttachment->delete();
                }

                $doc->delete();
            }
        });
    }

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
        return $this->morphMany(ComplianceDoc::class, 'compliable');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function driverLicense()
    {
        return $this->hasManyThrough(
            Attachment::class,       // final target
            ComplianceDoc::class,    // intermediate
            'compliable_id',         // FK on compliance_docs
            'attachable_id',         // FK on attachments
            'id',                    // local key on driver
            'id'                     // local key on compliance_doc
        )
        ->where('compliable_type', Driver::class) // only docs of this driver
        ->where('type', 'driver_license');       // only license images
    }

    public function licenseRestrictions()
    {
        return $this->hasMany(LicenseRestriction::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
