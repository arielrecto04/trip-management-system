<?php 


namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\RoleRepositoryInterface;

class UserRoleServices {

    public function __construct(
        protected RoleRepositoryInterface $roleRepo,
    ) {}

    public function assignRole(User $user, $roleId)
    {
        $role = $this->roleRepo->find($roleId);

        if ($user->driver && $role->slug !== 'driver'){
            throw new \Exception('Driver cannot have non-driver roles.');
        }

        if ($role->slug === 'driver'){
            return $user->roles()->sync([$role->id]);
        }

        return $user->roles()->syncWithoutDetaching([$role->id]);
    }

    public function assignDriverRole(User $user)
    {
        $driverRoleId = $this->roleRepo->getIdBySlug('driver');

        $user->roles()->sync([$driverRoleId]);
    }

}