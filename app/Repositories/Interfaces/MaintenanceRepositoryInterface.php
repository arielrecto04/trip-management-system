<?php

namespace App\Repositories\Interfaces;

interface MaintenanceRepositoryInterface
{
    public function all();
    public function allWithRelations(array $relations);
    public function findWithRelations(int $id, array $relations);

    public function find(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id);
}
