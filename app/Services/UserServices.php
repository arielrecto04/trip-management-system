<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserServices
{
    public function __construct(
        protected UserRepositoryInterface $userRepo
    ) {}

    public function showAllUsers()
    {
        $users = $this->userRepo->all();

        return $users;
    }

    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);

        $roles = $data['roles'];
        unset($data['roles']);

        $user = $this->userRepo->create($data);

        $user->roles()->sync($roles);

        return $user;
    }

    public function deleteUser($id) 
    {
        $user = $this->userRepo->delete($id);

        return $user;
    }

}