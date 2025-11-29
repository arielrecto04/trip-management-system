<?php

namespace App\Services;

use App\Repositories\Interfaces\DriverRepositoryInterface;

class DriverServices {

    public function __construct(
        protected DriverRepositoryInterface $driverRepo,
    )
    {}

    public function showAllDrivers()
    {
        $drivers = $this->driverRepo->all();

        return $drivers;
    }

    public function createDriver(array $data)
    {
        $license_images = $data['license_images'];
        unset($data['license_images']);

        $license_restriction = $data['license_restriction'];
        unset($data['license_restriction']);

        $driver = $this->driverRepo->create($data);

        foreach($license_restriction as $code) {
            $driver->licenseRestrictions()->create([
                'code' => $code
            ]);
        }

        $licenseDoc = $driver->complianceDocs()->create([
            'doc_type' => 'license',
            'doc_number' => $data['license_number'],
            'expiration_date' => date('Y-m-d', strtotime($data['license_expiration'])),
        ]);

        foreach($license_images as $image) {
            $path = $image->store('driver_licenses', 'public');

            $licenseDoc->attachments()->create([
                'name'        => $image->getClientOriginalName(),
                'type'        => 'driver_license',
                'extension'   => $image->getClientOriginalExtension(),
                'size'        => $image->getSize(),
                'url'         => $path,
                'uploaded_by' => auth()->id(),
            ]);
        }

        return $driver;
    }


}