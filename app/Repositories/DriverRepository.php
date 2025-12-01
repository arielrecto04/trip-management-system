<?php 


namespace App\Repositories;

use App\Models\Driver;
use App\Repositories\Interfaces\DriverRepositoryInterface;


class DriverRepository implements DriverRepositoryInterface {
    
    public function all()
    {
        return Driver::all();
    }

    public function find($id)
    {
        return Driver::findOrFail($id);
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