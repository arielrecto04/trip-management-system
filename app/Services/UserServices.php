<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
        $data = $this->preparePassword($data);

        $roles = $this->extractRoles($data);
        $profile_picture = $this->extractProfilePicture($data);

        $user = $this->userRepo->create($data);

        $user->roles()->sync($roles);

        if ($profile_picture) {
            $this->uploadProfilePicture($user, $profile_picture);
        }

        return $user;
    }

    public function editUser(int $id, array $data)
    {
        $roles = $this->extractRoles($data);
        $profile_picture = $this->extractProfilePicture($data);
        $removePicture = $data['remove_profile_picture'] ?? false;

        $data = $this->preparePassword($data);

        $user = $this->userRepo->update($id, $data);

        $user->roles()->sync($roles);

        $this->handleProfilePicture($user, $profile_picture, $removePicture);

        return $user;
    }

    public function deleteUser($id) 
    {
        $user = $this->userRepo->delete($id);

        return $user;
    }



    // private
    private function preparePassword(array $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password'], $data['password_confirmation']);
        }

        return $data;
    }

    private function extractRoles(array $data)
    {
        $roles = $data['roles'];
        unset($data['roles']);

        return $roles;
    }

    private function extractProfilePicture(array $data)
    {
        $file = $data['profile_picture'] ?? null;
        unset($data['profile_picture']);

        return $file;
    }

    private function handleProfilePicture($user, $file, bool $remove)
    {
        if($file)
        {
            $this->uploadProfilePicture($user, $file);
            return;
        }

        if($remove)
        {
            $this->deleteProfilePicture($user);
        }
    }

    private function uploadProfilePicture($user, $file)
    {
        $path = $file->store('uploads/profile_pictures', 'public');

        if ($user->profilePicture) {
            Storage::disk('public')->delete($user->profilePicture->url);
            $user->profilePicture->update([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'url' => $path,
                'uploaded_by' => auth()->id(),
            ]);
        } else {
            $user->profilePicture()->create([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'url' => $path,
                'uploaded_by' => auth()->id(),
                'type' => 'profile_picture',
            ]);
        }
    }

    private function deleteProfilePicture($user)
    {
        if(!$user->profilePicture)
        {
            return;
        }

        Storage::disk('public')->delete($user->profilePicture->url);
        $user->profilePicture()->delete();
    }
}