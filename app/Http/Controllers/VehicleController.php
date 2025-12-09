<?php

namespace App\Http\Controllers;

use App\Services\VehicleServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleServices $vehicleServices
    )
    {}

    public function index()
    {
        $vehicles = $this->vehicleServices->getAllVehicles(
            ['currentTrip', 'complianceDocs.attachments','attachments', 'maintenances'],
            ['complianceDocs']
        );

        return Inertia::render('Vehicle/Index', [
            'vehicles' => $vehicles,
        ]);
    }

    public function create()
    {
        return Inertia::render('Vehicle/Create');
    }

    public function store(Request $request)
    {
        $vehicleData = $request->validate([
            'vin'            => ['required', 'string', 'max:255'],
            'brand'          => ['required', 'string', 'max:255'],
            'model'          => ['required', 'string', 'max:255'],
            'license_plate'  => ['required', 'string', 'max:20'],
            'capacity_kg'    => ['required', 'numeric', 'min:1'],
            'capacity_m3'    => ['required', 'numeric', 'min:0.1'],
            'is_active'      => ['required', 'integer', 'in:0,1'],

            'or_image'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cr_image'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'vehicle_images'   => ['required', 'array', 'min:1'],
            'vehicle_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $this->vehicleServices->createVehicle($vehicleData);

        return redirect()->route('vehicles');
    }

    public function update(Request $request, $id)
    {
        $vehicleData = $request->validate([
            'vin'            => ['required', 'string', 'max:255'],
            'brand'          => ['required', 'string', 'max:255'],
            'model'          => ['required', 'string', 'max:255'],
            'license_plate'  => ['required', 'string', 'max:20'],
            'capacity_kg'    => ['required', 'numeric', 'min:1'],
            'capacity_m3'    => ['required', 'numeric', 'min:0.1'],
            'is_active'      => ['required', 'integer', 'in:0,1'],

            'or_image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cr_image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'vehicle_images'   => ['nullable', 'array'],
            'vehicle_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_existing_vehicle_images' => ['nullable', 'boolean'],
            'remove_existing_or' => ['nullable', 'boolean'],
            'remove_existing_cr' => ['nullable', 'boolean'],
        ]);

        $this->vehicleServices->updateVehicle($id, $vehicleData);

        return redirect()->route('vehicles');
    }

    public function edit($id)
    {
        $vehicle = $this->vehicleServices->getVehicleById(
            $id,
            ['complianceDocs.attachments', 'attachments'],
            ['complianceDocs']
        );

        return Inertia::render('Vehicle/Edit', [
            'vehicle' => $vehicle
        ]);
    }

    public function destroy($id)
    {
        $this->vehicleServices->deleteVehicle($id);

        return back();
    }

    public function toggleActive(Request $request, $id)
    {
        $request->validate([
            'is_active' => ['required', 'integer', 'in:0,1'],
        ]);

        $toggle = $request->input('is_active');

        $vehicle = $this->vehicleServices->toggleActive($id, $toggle);

        return response()->json([
            'message' => 'Vehicle status updated',
            'is_active' => (bool) $vehicle->is_active,
        ])->header('X-Inertia', 'false');
    }

}
