<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'role:logistics-manager'])->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])
        ->name('users');
    Route::get('/users/create', [UserController::class, 'create'])
        ->name('users.create');
    Route::post('/users', [UserController::class, 'store'])
        ->name('users.store'); // Add name for consistency
    
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])
        ->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])
        ->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])
        ->name('users.destroy');

    Route::get('/drivers', [DriverController::class, 'index'])
        ->name('drivers');
    Route::get('/drivers/create', [DriverController::class, 'create'])
        ->name('drivers.create');
    Route::get('/drivers/{id}/edit', [DriverController::class, 'edit'])
        ->name('drivers.edit');
    Route::put('/drivers/{id}', [DriverController::class, 'update'])
        ->name('drivers.update');
    Route::post('/drivers', [DriverController::class, 'store'])
        ->name('drivers.store');
    Route::delete('/drivers/{id}', [DriverController::class, 'destroy'])
        ->name('drivers.destroy');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
