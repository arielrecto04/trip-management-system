<?php

namespace App\Services;

use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class RoleServices
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepo
    ) {}

    public function showAllRoles()
    {
        $roles = $this->roleRepo->all();

        return $roles;
    }


}