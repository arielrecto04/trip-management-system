<?php 


namespace App\Repositories;

use App\Models\User;
use App\Models\Vehicle;
use App\Repositories\Interfaces\VehicleRepositoryInterface;


class VehicleRepository implements VehicleRepositoryInterface 
{
    public function all()
    {
        return Vehicle::get();
    }

    public function find(int $id)
    {
        return Vehicle::findOrFail($id);
    }

    public function create(array $data)
    {
        return Vehicle::create($data);
    }

    public function update(int $id, array $data)
    {
        $user = Vehicle::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id)
    {
        $user = Vehicle::findOrFail($id);
        $user->delete();
        return $user;
    }

    public function allWithRelations(array $relations = [], array $withCount = [])
    {
        return Vehicle::with($relations)
            ->withCount($withCount)
            ->get();
    }

    public function findWithRelations(int $id, array $relations = [], array $withCount = [])
    {
        return Vehicle::with($relations)
            ->withCount($withCount)
            ->findOrFail($id);
    }
}
