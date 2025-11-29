<?php


namespace App\Repositories\Interfaces;

interface RoleRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findBySlug(string $slug);
    public function getIdBySlug(string $slug);
    
}