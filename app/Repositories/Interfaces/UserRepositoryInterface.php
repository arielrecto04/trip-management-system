<?php


namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function all(array $relations = [], array $withCount = []);
    public function find(int $id, array $relations = [], array $withCount = []);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function getUserByRoleSlug(string $slug);
}