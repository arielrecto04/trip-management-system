<?php

namespace App\Repositories;

use App\Models\MaintenanceLog;
use App\Repositories\Interfaces\MaintenanceRepositoryInterface;

class MaintenanceRepository implements MaintenanceRepositoryInterface
{
    public function all()
    {
        return MaintenanceLog::with('vehicle')->get();
    }

    public function find(int $id)
    {
        return MaintenanceLog::with('vehicle')->findOrFail($id);
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
