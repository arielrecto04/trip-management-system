<?php

namespace App\Repositories;

use App\Models\MaintenanceLog;
use App\Repositories\Interfaces\MaintenanceRepositoryInterface;

class MaintenanceRepository implements MaintenanceRepositoryInterface
{
    public function all()
    {
        return MaintenanceLog::all();
    }

    public function allWithRelations(array $relations)
    {
        return MaintenanceLog::with($relations)->get();
    }

    public function find(int $id)
    {
        return MaintenanceLog::findOrFail($id);
    }

    public function findWithRelations(int $id, array $relations)
    {
        return MaintenanceLog::with($relations)->findOrFail($id);
    }

    public function create(array $data)
    {
        return MaintenanceLog::create($data);
    }

    public function update(int $id, array $data)
    {
        $maintenance = $this->find($id);
        $maintenance->update($data);
        return $maintenance;
    }

    public function delete(int $id)
    {
        $maintenance = $this->find($id);
        return $maintenance->delete();
    }
}
