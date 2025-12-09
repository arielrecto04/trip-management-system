<?php

namespace App\Services;

use App\Repositories\Interfaces\MaintenanceRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class MaintenanceServices {

    public function __construct(
        protected MaintenanceRepositoryInterface $maintenanceRepo,
    )
    {}

    public function getAllMaintenanceLogs()
    {
        $maintenances = $this->maintenanceRepo->all();

        return $maintenances;
    }

    public function getAllMaintenanceWithRelations()
    {
        $maintenances = $this->maintenanceRepo->allWithRelations([
            'attachments',
            'vehicle',
        ]);

        return $maintenances;
    }

    public function getMaintenanceWithRelations($id)
    {
        $maintenance = $this->maintenanceRepo->findWithRelations($id, [
            'attachments',
            'vehicle',
        ]);

        return $maintenance;
    }

    public function createMaintenanceLog(array $data)
    {
        $maintenanceData = [
            'vehicle_id' => $data['vehicle_id'],
            'start_maintenance_date' => $data['start_maintenance_date'],
            'end_maintenance_date' => $data['end_maintenance_date'],
            'technician' => $data['technician'],
            'work_performed' => $data['work_performed'],
            'cost' => $data['cost'],
            'current_odometer' => $data['current_odometer'],
            'status' => 'Pending',
        ];

        $maintenance = $this->maintenanceRepo->create($maintenanceData);

        $attachments = $data['attachments'];

        if(!empty($attachments)) {
            foreach($attachments as $attachment) {
                $path = $attachment->store('uploads/maintenance', 'public');

                $maintenance->attachments()->create([
                    'name' => $attachment->getClientOriginalName(),
                    'type' => 'maintenance_image',
                    'extension' => $attachment->getClientOriginalExtension(),
                    'size' => $attachment->getSize(),
                    'url' => $path,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return $maintenance;
    }

    public function updateMaintenanceLog(int $id, array $data)
    {
        $updateData = [
            'vehicle_id' => $data['vehicle_id'],
            'start_maintenance_date' => $data['start_maintenance_date'],
            'end_maintenance_date' => $data['end_maintenance_date'],
            'technician' => $data['technician'],
            'work_performed' => $data['work_performed'],
            'cost' => $data['cost'],
            'current_odometer' => $data['current_odometer'],
        ];

        $maintenance = $this->maintenanceRepo->update($id, $updateData);

        if (!empty($data['deleted_attachments'])) {
            foreach ($data['deleted_attachments'] as $file) {
                if (!isset($file['id'])) continue;

                $attachment = $maintenance->attachments()->find($file['id']);
                if ($attachment) {
                    if ($attachment->url && Storage::disk('public')->exists($attachment->url)) {
                        Storage::disk('public')->delete($attachment->url);
                    }
                    $attachment->delete();
                }
            }
        }

        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $path = $file->store('uploads/maintenance', 'public');

                $maintenance->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'type' => 'maintenance_image',
                    'extension' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'url' => $path,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return $this->getMaintenanceWithRelations($id);
    }



    public function deleteMaintenanceLog(int $id)
    {
        $delete = $this->maintenanceRepo->delete($id);

        return $delete;
    }
}