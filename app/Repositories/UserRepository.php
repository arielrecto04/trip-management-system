<?php 


namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;


class UserRepository implements UserRepositoryInterface 
{
    public function all()
    {
        return User::with('roles')->get();
    }

    public function find($id)
    {
        return User::findOrFail($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        $user = User::findOrFail($id);
        return $user->update($data);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }

    public function getUserByRoleSlug($slug)
    {
        return User::whereHas('roles', fn($q) => $q->where('slug', $slug))->get();
    }

}
