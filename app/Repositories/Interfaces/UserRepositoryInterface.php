<?php


namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);

    public function getUserByRoleSlug(string $slug);
    
    public function allWithRelations(array $relations);
    public function findWithRelations(int $id, array $relations);
}