<?php

namespace App\Services;

use App\Repositories\Interfaces\WarehouseRepositoryInterface;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WarehouseServices
{
    public function __construct(
        protected WarehouseRepositoryInterface $warehouseRepo,
        protected UserRepositoryInterface $userRepo,

        ) {}

    public function getAllWarehouses(): Collection
    {
        return $this->warehouseRepo->all([], []);
    }

    public function findWarehouseById(int $id)
    {
        return $this->warehouseRepo->find($id,[], []);
    }

    public function createWarehouse(array $data)
    {
        return $this->warehouseRepo->create($data);
    }

    public function updateWarehouse(int $id, array $data)
    {
        return $this->warehouseRepo->update($id, $data);
    }

    public function deleteWarehouse(int $id)
    {
        return $this->warehouseRepo->delete($id);
    }

    public function getAllAdmins()
    {
        $admins = $this->userRepo->getUserByRoleSlug('logistics-manager');

        return $admins;
    }

}
