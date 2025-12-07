<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Services\DriverServices;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function __construct(
        protected DriverServices $driverServices,
        protected UserServices $userServices,

    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drivers = $this->driverServices->showAllDriversWithRelations();

        return Inertia::render('Driver/Index', [
            'drivers' => $drivers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $drivers = $this->userServices->showUsersWithDriverRole();
        return Inertia::render('Driver/Create', [
            'drivers' => $drivers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $driverData = $request->validate([
            'user_id' => 'required|unique:drivers,user_id',
            'license_number' => 'required|string',
            'license_restriction' => 'required',
            'license_expiration' => 'required',
            'license_images' => 'array|required',
            'license_images.*' => 'image|max:2048'
        ]);

        $this->driverServices->createDriver($driverData);

        return redirect()->route('drivers');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $driver = $this->driverServices->showDriverWithId($id);
        
        return Inertia::render('Driver/Edit', [
            'driver' => $driver,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $driverData = $request->validate([
            'license_number' => 'required|string',
            'license_restriction' => 'required',
            'license_expiration' => 'required',

            'new_license_images' => 'array',
            'new_license_images.*' => 'image|max:2048',

            'remove_existing_license_images' => 'boolean',
        ]);

        $this->driverServices->updateDriver($id, $driverData);

        return redirect()->route('drivers');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->driverServices->deleteDriver($id);

        return redirect()->route('drivers');
    }
}
