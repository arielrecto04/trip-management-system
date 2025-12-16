<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\Repositories\Interfaces\VehicleRepositoryInterface;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function all(array $relations = [], array $withCount = [])
    {
        return Vehicle::with($relations)
            ->withCount($withCount)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function find(int $id, array $relations = [], array $withCount = [])
    {
        return Vehicle::with($relations)
            ->withCount($withCount)
            ->findOrFail($id);
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(int $id, array $data): Vehicle
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update($data);
        return $vehicle;
    }

    public function delete(int $id): bool
    {
        $vehicle = Vehicle::findOrFail($id);
        return $vehicle->delete();
    }

    
    public function getActiveVehicles(array $relations = [], array $withCount = [])
    {
        return Vehicle::with($relations)
            ->withCount($withCount)
            ->where('is_active', true)
            ->orderBy('created_by', 'desc')
            ->get();
    }
}
