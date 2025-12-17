<?php

namespace App\Services;

use App\Repositories\Interfaces\MaintenanceRepositoryInterface;
use App\Repositories\Interfaces\VehicleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MaintenanceServices {

    public function __construct(
        protected MaintenanceRepositoryInterface $maintenanceRepo,
        protected VehicleRepositoryInterface $vehicleRepo,
    )
    {}

    private function computeStatus($start, $end)
    {
        $today = Carbon::today();

        if ($today->between(Carbon::parse($start), Carbon::parse($end))) {
            return 'In Progress';
        }

        if ($today->gt(Carbon::parse($end))) {
            return 'Completed';
        }

        return 'Pending';
    }

    private function updateVehicleStatus($vehicle, $status)
    {
        $vehicle->is_active = ($status !== 'In Progress');
        $vehicle->save();
    }

    public function getAllMaintenanceLogs()
    {
        $maintenances = $this->maintenanceRepo->all();

        return $maintenances;
    }

    public function getAllMaintenanceWithRelations()
    {
        $maintenances = $this->maintenanceRepo->all([
            'attachments',
            'vehicle',
        ]);

        return $maintenances;
    }

    public function getMaintenance($id)
    {
        $maintenance = $this->maintenanceRepo->find($id, [
            'attachments',
            'vehicle',
        ]);

        return $maintenance;
    }

    public function createMaintenanceLog(array $data)
    {
        $status = $this->computeStatus($data['start_maintenance_date'], $data['end_maintenance_date']);

        $vehicle = $this->vehicleRepo->find($data['vehicle_id']);
        $this->updateVehicleStatus($vehicle, $status);

        $maintenanceData = [
            'vehicle_id' => $data['vehicle_id'],
            'start_maintenance_date' => $data['start_maintenance_date'],
            'end_maintenance_date' => $data['end_maintenance_date'],
            'technician' => $data['technician'],
            'work_performed' => $data['work_performed'],
            'cost' => $data['cost'],
            'current_odometer' => $data['current_odometer'],
        ];

        $maintenance = $this->maintenanceRepo->create($maintenanceData);

        $attachments = $data['attachments'] ?? [];

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
        $status = $this->computeStatus($data['start_maintenance_date'], $data['end_maintenance_date']);

        $vehicle = $this->vehicleRepo->find($data['vehicle_id']);
        $this->updateVehicleStatus($vehicle, $status);

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

        return $this->getMaintenance($id);
    }



    public function deleteMaintenanceLog(int $id)
    {
        $delete = $this->maintenanceRepo->delete($id);

        return $delete;
    }
}