<?php 


namespace App\Repositories;

use App\Models\Driver;
use App\Repositories\Interfaces\DriverRepositoryInterface;


class DriverRepository implements DriverRepositoryInterface {
    
    public function all(array $relations = [], array $withCount = [])
    {
        return Driver::with($relations)
            ->withCount($withCount)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function find(int $id, array $relations = [], array $withCount = [])
    {
        return Driver::with($relations)
            ->withCount($withCount)
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return Driver::create($data);
    }

    public function update(int $id, array $data)
    {
        $driver = Driver::findOrFail($id);
        $driver->update($data);
        return $driver;
    }

    public function delete($id)
    {
        $driver = Driver::findOrFail($id);

        return $driver->delete();
    }

}