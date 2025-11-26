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

        $file = $data['profile_picture'] ?? null;
        unset($data['profile_picture']);

        $user = $this->userRepo->create($data);

        $user->roles()->sync($roles);

        if ($file) {
            $path = $file->store('uploads/profile_pictures', 'public');

            $user->profilePicture()->create([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'url' => $path,
                'uploaded_by' => auth()->id(),
                'type' => 'profile_picture',
            ]);
        }

        return $user;
    }

    public function editUser(int $id, array $data)
    {
        $roles = $data['roles'];
        unset($data['roles']);

        $file = $data['profile_picture'] ?? null;
        unset($data['profile_picture']);

        if(!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password'], $data['password_confirmation']);
        }


        $user = $this->userRepo->update($id, $data);

        $user->roles()->sync($roles);

        if (!empty($userData['remove_profile_picture'])) {
            $user->load('profilePicture');
            
            if ($user->profilePicture && $user->profilePicture->url) {
                Storage::delete($user->profilePicture->url);
                $user->profilePicture->delete();
            }
        }

        if ($file) {
            $path = $file->store('uploads/profile_pictures', 'public');

            // Replace or create profile picture
            if ($user->profilePicture) {
                // Delete old file
                \Storage::disk('public')->delete($user->profilePicture->url);
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
        } elseif ($data['remove_profile_picture'] ?? false) {
            // Remove old picture if user clicked remove
            if ($user->profilePicture) {
                \Storage::disk('public')->delete($user->profilePicture->url);
                $user->profilePicture()->delete();
            }
        }

        return $user;
    }

    public function deleteUser($id) 
    {
        $user = $this->userRepo->delete($id);

        return $user;
    }

}