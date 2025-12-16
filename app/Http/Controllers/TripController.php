<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\TripServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TripController extends Controller
{
    public function __construct(
        protected TripServices $tripService
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trips = $this->tripService->getAllTrips();

        return Inertia::render('Trip/Index', [
            'trips' => $trips
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Trip/Create', $this->tripService->getCreateFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tripData = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'dispatcher_id' => 'required|exists:users,id',
            'warehouse_id' => 'required',
            'status' => 'nullable|string',
            'route' => 'required|string',
            'planned_start_time' => 'required|date',
            'actual_start_time' => 'nullable|date',
            'route_distance_km' => 'nullable|numeric|min:0',
            'is_liquidation_required' => 'boolean',
            'is_pre_trip_checked' => 'boolean',
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $this->tripService->createTrip($tripData);

        return redirect()->route('trips');
    }

    /**
     * Display the specified resource.
     */
    public function show(Trip $trip)
    {
        $trip->load(['driver', 'vehicle', 'dispatcher', 'stops', 'liquidation']);

        return Inertia::render('Trips/Show', [
            'trip' => $trip
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $trip = $this->tripService->getTrip($id);

        return Inertia::render('Trip/Edit', [
            'trip' => $trip,
            'drivers' => Driver::all(),
            'warehouses' => Warehouse::all(),
            'vehicles' => Vehicle::all(),
            'dispatchers' => User::where('role', 'Dispatcher')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $tripData = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'dispatcher_id' => 'required|exists:users,id',
            'warehouse_id' => 'required',
            'status' => 'string',
            'route' => 'required|string',
            'planned_start_time' => 'required|date',
            'actual_start_time' => 'nullable|date',
            'route_distance_km' => 'nullable|numeric|min:0',
            'is_liquidation_required' => 'boolean',
            'is_pre_trip_checked' => 'boolean',
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $this->tripService->editTrip($id, $tripData);

        return redirect()->route('trips');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->tripService->deleteTrip($id);

        return redirect()->route('trips');
    }
}
