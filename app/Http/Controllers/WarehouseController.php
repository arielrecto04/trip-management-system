<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WarehouseServices;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function __construct(protected WarehouseServices $warehouseService)
    {
    }

    /**
     * Display a listing of the warehouses.
     */
    public function index()
    {
        $warehouses = $this->warehouseService->getAllWarehouses(); // Service handles eager loading
        return Inertia::render('Warehouse/Index', [
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create()
    {
        $admins = $this->warehouseService->getAllAdmins();

        return Inertia::render('Warehouse/Create', [
            'admins' => $admins,
        ]);
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255|unique:warehouses,name',
            'house_number'    => 'nullable|string|max:255',
            'street'          => 'nullable|string|max:255',
            'region_code'     => 'required|string',
            'region_name'     => 'required|string',
            'province_code'   => 'nullable|string',
            'province_name'   => 'nullable|string',
            'city_code'       => 'required|string',
            'city_name'       => 'required|string',
            'barangay_code'   => 'required|string',
            'barangay_name'   => 'required|string',
            'zip_code'        => 'nullable|string|max:10',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'contact_person'  => 'nullable|exists:users,id',
        ]);

        $this->warehouseService->createWarehouse($data);

        return redirect()->route('warehouses');
    }

    /**
     * Show the form for editing a warehouse.
     */
    public function edit(int $id)
    {
        $warehouse = $this->warehouseService->findWarehouseById($id);
        $users = User::all();

        return Inertia::render('Warehouse/Edit', [
            'warehouse' => $warehouse,
            'users' => $users,
        ]);
    }

    /**
     * Update a warehouse.
     */
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'house_number'    => 'nullable|string|max:255',
            'street'          => 'nullable|string|max:255',
            'region_code'     => 'required|string',
            'region_name'     => 'required|string',
            'province_code'   => 'nullable|string',
            'province_name'   => 'nullable|string',
            'city_code'       => 'required|string',
            'city_name'       => 'required|string',
            'barangay_code'   => 'required|string',
            'barangay_name'   => 'required|string',
            'zip_code'        => 'nullable|string|max:10',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
        ]);

        $this->warehouseService->updateWarehouse($id, $data);

        return redirect()->route('warehouses');
    }

    /**
     * Delete a warehouse.
     */
    public function destroy(int $id)
    {
        $this->warehouseService->deleteWarehouse($id);
        return redirect()->route('warehouses');
    }
}
