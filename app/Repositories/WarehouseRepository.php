<?php 


namespace App\Repositories;

use App\Models\Warehouse;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;


class WarehouseRepository implements WarehouseRepositoryInterface {
    
    public function all(array $relations = [], array $withCount = [])
    {
        return Warehouse::with($relations)
            ->withCount($withCount)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function find(int $id, array $relations = [], array $withCount = [])
    {
        return Warehouse::with($relations)
            ->withCount($withCount)
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return Warehouse::create($data);
    }

    public function update(int $id, array $data)
    {
        $driver = Warehouse::findOrFail($id);
        $driver->update($data);
        return $driver;
    }

    public function delete($id)
    {
        $driver = Warehouse::findOrFail($id);

        return $driver->delete();
    }

}