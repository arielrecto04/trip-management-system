<?php

namespace App\Services;

use App\Repositories\Interfaces\DriverRepositoryInterface;
use Illuminate\Support\Facades\Storage;

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

    public function showDriverById(int $id)
    {
        $driver = $this->driverRepo->find($id);

        return $driver;
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
            $path = $image->store('uploads/driver_licenses', 'public');

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

    public function updateDriver(int $id, array $data)
    {
        $driver = $this->driverRepo->find($id);

        // extract
        $newLicenseImages = $data['new_license_images'] ?? [];
        unset($data['new_license_images']);

        $removeOldImages = $data['remove_existing_license_images'] ?? false;
        unset($data['remove_existing_license_images']);

        $licenseRestrictions = $data['license_restriction'] ?? [];
        unset($data['license_restriction']);

        $this->driverRepo->update($id, $data);

        $driver->licenseRestrictions()->delete();
        foreach($licenseRestrictions as $code) {
            $driver->licenseRestrictions()->create([
                'code' => $code,
            ]);
        }

        $licenseDoc = $driver->complianceDocs()
            ->where('doc_type', 'license')
            ->first();

        if (!$licenseDoc) {
            $licenseDoc = $driver->complianceDocs()->create([
                'doc_type'       => 'license',
                'doc_number'     => $data['license_number'],
                'expiration_date'=> $data['license_expiration'],
            ]);
        } else {
            $licenseDoc->update([
                'doc_number'     => $data['license_number'],
                'expiration_date'=> $data['license_expiration'],
            ]);
        }

        $licenseDoc->load('attachments');

        if ($removeOldImages) {
            foreach ($licenseDoc->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->url);
                $attachment->delete();
            }
        }

        foreach ($newLicenseImages as $image) {
            $path = $image->store('uploads/driver_licenses', 'public');

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

    public function deleteDriver(int $id) 
    {
        $driver = $this->driverRepo->delete($id);

        return $driver;
    }


}