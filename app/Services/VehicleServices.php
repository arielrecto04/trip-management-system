<?php

namespace App\Services;

use App\Repositories\Interfaces\VehicleRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class VehicleServices {
    
    public function __construct(
        protected VehicleRepositoryInterface $vehicleRepo,
    )
    {}

    public function getAllVehicleWithRelation()
    {
        $vehicles = $this->vehicleRepo->allWithRelations(
            ['currentTrip', 'attachments'],
            ['complianceDocs']
        );

        return $vehicles;
    }

    public function getVehicleById(int $id)
    {
        return $this->vehicleRepo->findWithRelations(
            $id,
            ['complianceDocs.attachments', 'attachments'],
            ['complianceDocs']
        );
    }

    public function createVehicle(array $data)
    {
        $vehicleData = [
            'vin' => $data['vin'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'license_plate' => $data['license_plate'],
            'capacity_kg' => $data['capacity_kg'],
            'capacity_m3' => $data['capacity_m3'],
            'is_active' => $data['is_active'],
        ];

        $vehicle = $this->vehicleRepo->create($vehicleData);

        $orImage = $data['or_image'];

        if(!empty($orImage)) {
            $path = $orImage->store('uploads/or_images', 'public');

            $vehicleCompliance = $vehicle->complianceDocs()->create([
                'doc_type' => 'OR',
                'doc_number' => $data['or_number'] ?? null,
                'doc_expiration' => $data['or_expiration'] ?? null,
            ]);
            
            $vehicleCompliance->attachments()->create([
                'name' => $orImage->getClientOriginalName(),
                'type' => 'OR',
                'extension' => $orImage->getClientOriginalExtension(),
                'size' => $orImage->getSize(),
                'url' => $path,
                'uploaded_by' => auth()->id(),
            ]);
        }

        $crImage = $data['cr_image'];

        if(!empty($crImage)) {
            $path = $crImage->store('uploads/cr_images', 'public');

            $vehicleCompliance = $vehicle->complianceDocs()->create([
                'doc_type' => 'CR',
                'doc_number' => $data['or_number'] ?? null,
                'doc_expiration' => $data['or_expiration'] ?? null,
            ]);
            
            $vehicleCompliance->attachments()->create([
                'name' => $crImage->getClientOriginalName(),
                'type' => 'CR',
                'extension' => $crImage->getClientOriginalExtension(),
                'size' => $crImage->getSize(),
                'url' => $path,
                'uploaded_by' => auth()->id(),
            ]);
        }

        $vehicleImages = $data['vehicle_images'];

        if(!empty($vehicleImages)) {
            foreach($vehicleImages as $image) {
                $path = $image->store('uploads/vehicle_images', 'public');

                $vehicle->attachments()->create([
                    'name' => $image->getClientOriginalName(),
                    'type' => 'vehicle_image',
                    'extension' => $image->getClientOriginalExtension(),
                    'size' => $image->getSize(),
                    'url' => $path,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return $vehicle;
    }

    public function updateVehicle(int $id, array $data)
    {
        $vehicle = $this->vehicleRepo->findWithRelations(
            $id,
            ['complianceDocs.attachments', 'attachments'],
            []
        );

        $vehicleData = [
            'vin' => $data['vin'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'license_plate' => $data['license_plate'],
            'capacity_kg' => $data['capacity_kg'],
            'capacity_m3' => $data['capacity_m3'],
            'is_active' => $data['is_active'],
        ];

        $this->vehicleRepo->update($id, $vehicleData);

        $orDoc = $vehicle->complianceDocs()->where('doc_type', 'OR')->first();

        if (!empty($data['remove_existing_or']) && $orDoc) {
            foreach ($orDoc->attachments as $attach) {
                Storage::disk('public')->delete($attach->url);
                $attach->delete();
            }
            $orDoc->delete();
        }

        if (!empty($data['or_image'])) {
            $orPath = $data['or_image']->store('uploads/or_images', 'public');

            if (!$orDoc) {
                $orDoc = $vehicle->complianceDocs()->create([
                    'doc_type' => 'OR',
                    'doc_number' => $data['or_number'] ?? null,
                    'doc_expiration' => $data['or_expiration'] ?? null,
                ]);
            }

            foreach ($orDoc->attachments as $attach) {
                Storage::disk('public')->delete($attach->url);
                $attach->delete();
            }

            $orDoc->attachments()->create([
                'name' => $data['or_image']->getClientOriginalName(),
                'type' => 'OR',
                'extension' => $data['or_image']->getClientOriginalExtension(),
                'size' => $data['or_image']->getSize(),
                'url' => $orPath,
                'uploaded_by' => auth()->id(),
            ]);
        }

        $crDoc = $vehicle->complianceDocs()->where('doc_type', 'CR')->first();

        if (!empty($data['remove_existing_cr']) && $crDoc) {
            foreach ($crDoc->attachments as $attach) {
                Storage::disk('public')->delete($attach->url);
                $attach->delete();
            }
            $crDoc->delete();
        }

        if (!empty($data['cr_image'])) {
            $crPath = $data['cr_image']->store('uploads/cr_images', 'public');

            if (!$crDoc) {
                $crDoc = $vehicle->complianceDocs()->create([
                    'doc_type' => 'CR',
                    'doc_number' => $data['cr_number'] ?? null,
                    'doc_expiration' => $data['cr_expiration'] ?? null,
                ]);
            }

            foreach ($crDoc->attachments as $attach) {
                Storage::disk('public')->delete($attach->url);
                $attach->delete();
            }

            $crDoc->attachments()->create([
                'name' => $data['cr_image']->getClientOriginalName(),
                'type' => 'CR',
                'extension' => $data['cr_image']->getClientOriginalExtension(),
                'size' => $data['cr_image']->getSize(),
                'url' => $crPath,
                'uploaded_by' => auth()->id(),
            ]);
        }

        if (!empty($data['remove_existing_vehicle_images'])) {
            foreach ($vehicle->attachments as $img) {
                if ($img->type === 'vehicle_image') {
                    Storage::disk('public')->delete($img->url);
                    $img->delete();
                }
            }
        }

        if (!empty($data['vehicle_images'])) {
            foreach ($data['vehicle_images'] as $image) {
                $path = $image->store('uploads/vehicle_images', 'public');

                $vehicle->attachments()->create([
                    'name' => $image->getClientOriginalName(),
                    'type' => 'vehicle_image',
                    'extension' => $image->getClientOriginalExtension(),
                    'size' => $image->getSize(),
                    'url' => $path,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return $vehicle->fresh(['complianceDocs.attachments', 'attachments']);
    }

    public function deleteVehicle($id)
    {
        $driver = $this->vehicleRepo->delete($id);

        return $driver;
    }

}