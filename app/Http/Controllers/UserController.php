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
        return Inertia::render('User/Create');
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

        return redirect()->route('users')->with('newUser', $user);
    }

    public function destroy($id)
    {
        $this->userService->deleteUser($id);

        return redirect()->route('users');
    }
}
