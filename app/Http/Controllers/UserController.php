<?php

namespace App\Http\Controllers;

use App\Services\RoleServices;
use App\Services\UserServices;
use Illuminate\Http\Request;
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
        $allRoles = $this->roleServices->showAllRoles();

        return Inertia::render('User/Index', [
            'users' => $users,
            'roles' => $allRoles,
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
        ]);

        $user = $this->userService->createUser($userData);
        $user->load('roles');

        return redirect()->route('users');
    }

    public function update(Request $request, $id)
    {
        $userData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'password' => 'nullable|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = $this->userService->editUser($id, $userData);
        $user->load('roles');

        return redirect()->route('users');
    }

    public function edit($id)
    {
        $user = $this->userService->findUserById($id)->load('roles');
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
