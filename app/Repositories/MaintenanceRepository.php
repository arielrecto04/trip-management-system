<?php

namespace App\Repositories;

use App\Models\MaintenanceLog;
use App\Repositories\Interfaces\MaintenanceRepositoryInterface;

class MaintenanceRepository implements MaintenanceRepositoryInterface
{
    public function all(array $relations = [], array $withCount = [])
    {
        return MaintenanceLog::with($relations)
            ->withCount($withCount)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function find(int $id, array $relations = [], array $withCount = [])
    {
        return MaintenanceLog::with($relations)
            ->withCount($withCount)
            ->findOrFail($id);
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
