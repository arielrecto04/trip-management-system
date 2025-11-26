<?php 


namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;


class UserRepository implements UserRepositoryInterface 
{
    public function all()
    {
        return User::get();
    }

    public function find(int $id)
    {
        return User::findOrFail($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update(int $id, array $data)
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return $user;
    }

    public function getUserByRoleSlug(string $slug)
    {
        return User::whereHas('roles', fn($q) => $q->where('slug', $slug))->get();
    }

}
