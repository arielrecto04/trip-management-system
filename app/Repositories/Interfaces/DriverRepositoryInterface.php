<?php


namespace App\Repositories\Interfaces;

interface DriverRepositoryInterface
{
    public function all(array $relation = [], array $withCount = []);
    public function find(int $id, array $relation = [], array $withCount = []);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}