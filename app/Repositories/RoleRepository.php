<?php 


namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Interfaces\RoleRepositoryInterface;


class RoleRepository implements RoleRepositoryInterface 
{
    public function all()
    {
        return Role::all();
    }

    public function find(int $id)
    {
        return Role::findOrFail($id);
    }

    public function findBySlug(string $slug)
    {
        return Role::where('slug', $slug)->firstOrFail();
    }

    public function getIdBySlug(string $slug)
    {
        return Role::where('slug', $slug)->value('id');
    }
}