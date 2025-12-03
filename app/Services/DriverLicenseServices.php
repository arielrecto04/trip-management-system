<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class DriverLicenseServices
{
    public function createLicense($driver, array $data)
    {
        $licenseDoc = $driver->complianceDocs()->create([
            'doc_type' => 'license',
            'doc_number' => $data['license_number'],
            'expiration_date' => date('Y-m-d', strtotime($data['license_expiration'])),
        ]);

        foreach ($data['license_images'] as $image) {
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

        return $licenseDoc;
    }

    public function updateLicense($driver, array $data)
    {
        $licenseDoc = $driver->complianceDocs()
            ->where('doc_type', 'license')
            ->first();

        if (!$licenseDoc) {
            return $this->createLicense($driver, $data);
        }

        $licenseDoc->update([
            'doc_number'      => $data['license_number'],
            'expiration_date' => date('Y-m-d', strtotime($data['license_expiration'])),
        ]);

        if (!empty($data['remove_existing_license_images'])) {
            foreach ($licenseDoc->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->url);
                $attachment->delete();
            }
        }

        foreach ($data['new_license_images'] ?? [] as $image) {
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

        return $licenseDoc;
    }


    public function syncRestrictions($driver, array $codes)
    {
        $driver->licenseRestrictions()->delete();

        foreach ($codes as $code) {
            $driver->licenseRestrictions()->create(['code' => $code]);
        }
    }
}
