<?php

namespace App\Services;

use App\Repositories\Interfaces\DriverRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class DriverServices {

    public function __construct(
        protected DriverRepositoryInterface $driverRepo,
        protected DriverLicenseServices $licenseServices
    )
    {}

    public function showAllDrivers()
    {
        $drivers = $this->driverRepo->all();

        return $drivers;
    }

    public function showDriverWithId(int $id)
    {
        $driver = $this->driverRepo->findWithRelation($id, [
            'licenseRestrictions',
            'user',
            'complianceDocs.attachments'
        ]);

        if (!$driver) {
            return null;
        }

        $driver->driver_license = $driver->complianceDocs
            ->where('doc_type', 'license')
            ->flatMap(function ($licenseDoc) {
                return $licenseDoc->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'url' => $attachment->url,
                        'name' => $attachment->name,
                    ];
                });
            })
            ->values();
            
        return $driver;
    }

    public function showDriverById(int $id)
    {
        $driver = $this->driverRepo->find($id);

        return $driver;
    }

    public function createDriver(array $data)
    {
        $driverData = [
            'user_id' => $data['user_id'],
            'license_number' => $data['license_number'],
            'license_expiration' => $data['license_expiration'],
        ];
        
        $licenseData = [
            'license_expiration' => $data['license_expiration'],
            'license_number' => $data['license_number'],
            'license_images' => $data['license_images']
        ];

        $driver = $this->driverRepo->create($driverData);

        $this->licenseServices->createLicense($driver, $licenseData);
        $this->licenseServices->syncRestrictions($driver, $data['license_restriction']);

        return $driver;
    }

    public function updateDriver(int $id, array $data)
    {
        $driver = $this->driverRepo->find($id);

        $driverUpdateData = [
            'license_number'     => $data['license_number'] ?? $driver->license_number,
            'license_expiration' => $data['license_expiration'] ?? $driver->license_expiration,
        ];

        $this->driverRepo->update($id, $driverUpdateData);

        $licenseData = [
            'license_number'      => $data['license_number'] ?? $driver->license_number,
            'license_expiration'  => $data['license_expiration'] ?? $driver->license_expiration,
            'new_license_images'  => $data['new_license_images'] ?? [],
            'remove_existing_license_images' => $data['remove_existing_license_images'] ?? false,
            'license_restriction' => $data['license_restriction'] ?? [],
        ];

        $this->licenseServices->updateLicense($driver, $licenseData);
        $this->licenseServices->syncRestrictions($driver, $licenseData['license_restriction']);

        return $driver->fresh();
    }

    public function deleteDriver(int $id) 
    {
        $driver = $this->driverRepo->delete($id);

        return $driver;
    }


}