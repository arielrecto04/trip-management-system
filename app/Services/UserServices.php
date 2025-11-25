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

    public function findUserById($id)
    {
        $user = $this->userRepo->find($id);

        return $user;
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

    public function editUser(int $id, array $data)
    {
        $roles = $data['roles'];
        unset($data['roles']);

        if(!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password'], $data['password_confirmation']);
        }

        $user = $this->userRepo->update($id, $data);

        $user->roles()->sync($roles);

        return $user;
    }

    public function deleteUser($id) 
    {
        $user = $this->userRepo->delete($id);

        return $user;
    }

}