<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceServices;
use App\Services\VehicleServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaintenanceController extends Controller
{
    public function __construct(
        protected VehicleServices $vehicleServices,
        protected MaintenanceServices $maintenanceServices
    )
    {}

    public function index()
    {
        $maintenanceLogs = $this->maintenanceServices->getAllMaintenanceWithRelations();
        $vehicles = $this->vehicleServices->getAllVehicles();

        return Inertia::render('Maintenance/Index', [
            'maintenanceLogs' => $maintenanceLogs,
            'vehicles' => $vehicles,
        ]);
    }

    public function create()
    {
        $vehicles = $this->vehicleServices->getAllVehicles();

        return Inertia::render('Maintenance/Create', [
            'vehicles' => $vehicles
        ]);
    }

    public function edit($id)
    {
        $maintenance = $this->maintenanceServices->getMaintenance($id);

        return Inertia::render('Maintenance/Edit', [
            'maintenance' => $maintenance,
        ]);
    }

    public function store(Request $request) 
    {
        $maintenanceData = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_maintenance_date' => 'required|date|before:end_maintenance_date',
            'end_maintenance_date' => 'required|date|after:start_maintenance_date',
            'technician' => 'required|string|max:255',
            'work_performed' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'current_odometer' => 'required|numeric|min:0',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $this->maintenanceServices->createMaintenanceLog($maintenanceData);

        return redirect()->route('maintenance');
    }

    public function update(Request $request, $id)
    {
        $maintenanceData = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_maintenance_date' => 'required|date|before:end_maintenance_date',
            'end_maintenance_date'   => 'required|date|after:start_maintenance_date',
            'technician' => 'required|string|max:255',
            'work_performed' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'current_odometer' => 'required|numeric|min:0',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'existing_attachments' => 'nullable|array',
            'deleted_attachments' => 'nullable|array',
            'deleted_attachments.*.id' => 'required|integer',
        ]);

        $this->maintenanceServices->updateMaintenanceLog($id, $maintenanceData);

        return redirect()->route('maintenance');
    }

    public function destroy($id)
    {
        $this->maintenanceServices->deleteMaintenanceLog($id);

        return redirect()->route('maintenance');
    }
}
