<?php

namespace App\Repositories\Interfaces;

use App\Models\Vehicle;

interface VehicleRepositoryInterface
{
    public function all(array $relations = [], array $withCount = []);
    public function find(int $id, array $relations = [], array $withCount = []);
    public function create(array $data): Vehicle;
    public function update(int $id, array $data): Vehicle;
    public function delete(int $id): bool;
}
