<?php


namespace App\Repositories\Interfaces;

interface DriverRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findWithRelation(int $id, array $relation);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}