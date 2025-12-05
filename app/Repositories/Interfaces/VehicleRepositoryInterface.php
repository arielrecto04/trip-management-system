<?php


namespace App\Repositories\Interfaces;

interface VehicleRepositoryInterface
{
    public function all();
    public function find(int $id);

    public function allWithRelations(array $relations = [], array $withCount = []);
    public function findWithRelations(int $id, array $relations = [], array $withCount = []);

    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}