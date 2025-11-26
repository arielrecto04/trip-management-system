<?php

namespace App\Http\Controllers;

use App\Services\RoleServices;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        protected UserServices $userService,
        protected RoleServices $roleServices
    ){}
    
    public function index()
    {
        $users = $this->userService->showAllUsers();
        $users->load('profilePicture', 'roles');

        return Inertia::render('User/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $allRoles = $this->roleServices->showAllRoles();

        return Inertia::render('User/Create', [
            'roles' => $allRoles
        ]);
    }

    public function store(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|integer|unique:users,phone_number',
            'password' => 'required|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        $user = $this->userService->createUser($userData);
        $user->load('roles', 'profilePicture');

        return redirect()->route('users');
    }

    public function update(Request $request, $id)
    {
        $user = $this->userService->findUserById($id);
        $user->load('profilePicture', 'roles');

        $userData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'password' => 'nullable|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'profile_picture' => 'nullable|image|max:2048',
            'remove_profile_picture' => 'sometimes|boolean',
        ]);

        $this->userService->editUser($id, $userData);
        
        return redirect()->route('users');
    }

    public function edit($id)
    {
        $user = $this->userService->findUserById($id);
        $user->load('roles', 'profilePicture');
        $roles = $this->roleServices->showAllRoles();

        return Inertia::render('User/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function destroy($id)
    {
        $this->userService->deleteUser($id);

        return redirect()->route('users');
    }
}
