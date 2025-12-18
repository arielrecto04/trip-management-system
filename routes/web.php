<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MappingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WarehouseController;
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
    Route::get('/test', [DashboardController::class, 'test'])
        ->name('test');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])
        ->name('users');
    Route::get('/users/create', [UserController::class, 'create'])
        ->name('users.create');
    Route::post('/users', [UserController::class, 'store'])
        ->name('users.store'); 
    
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

    Route::get('/vehicles', [VehicleController::class, 'index'])
        ->name('vehicles');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])
        ->name('vehicles.create');
    Route::get('/vehicles/{id}/edit', [VehicleController::class, 'edit'])
        ->name('vehicles.edit');
    Route::put('/vehicles/{id}', [VehicleController::class, 'update'])
        ->name('vehicles.update');
    Route::post('/vehicles', [VehicleController::class, 'store'])
        ->name('vehicles.store');
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy'])
        ->name('vehicles.destroy');
    Route::patch('/vehicles/{id}/toggle-active', [VehicleController::class, 'toggleActive'])
        ->name('vehicles.toggle');

    Route::get('/maintenance', [MaintenanceController::class, 'index'])
        ->name('maintenance');
    Route::get('/maintenance/create', [MaintenanceController::class, 'create'])
        ->name('maintenance.create');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])
        ->name('maintenance.store');
    Route::get('/maintenance/{id}/edit', [MaintenanceController::class, 'edit'])
        ->name('maintenance.edit');
    Route::delete('/maintenance/{id}', [MaintenanceController::class, 'destroy'])
        ->name('maintenance.destroy');
    Route::put('/maintenance/{id}', [MaintenanceController::class, 'update'])
        ->name('maintenance.update');

    Route::get('/trips', [TripController::class, 'index'])
        ->name('trips');
    Route::get('/trips/create', [TripController::class, 'create'])
        ->name('trips.create');
    Route::post('/trips', [TripController::class, 'store'])
        ->name('trips.store');
    Route::get('/trips/{id}/edit', [TripController::class, 'edit'])
        ->name('trips.edit');
    Route::delete('/trips/{id}', [TripController::class, 'destroy'])
        ->name('trips.destroy');
    Route::put('/trips/{id}', [TripController::class, 'update'])
        ->name('trips.update');

    Route::get('/warehouses', [WarehouseController::class, 'index'])
        ->name('warehouses');
    Route::get('/warehouses/create', [WarehouseController::class, 'create'])
        ->name('warehouses.create');
    Route::post('/warehouses', [WarehouseController::class, 'store'])
        ->name('warehouses.store');
    Route::get('/warehouses/{id}/edit', [WarehouseController::class, 'edit'])
        ->name('warehouses.edit');
    Route::delete('/warehouses/{id}', [WarehouseController::class, 'destroy'])
        ->name('warehouses.destroy');
    Route::put('/warehouses/{id}', [WarehouseController::class, 'update'])
        ->name('warehouses.update');
});

Route::middleware(['auth', 'role:fleet-manager'])->group(function () {
    Route::get('/fleet/dashboard', [DashboardController::class, 'fleet'])
        ->name('fleet.dashboard');
});

Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/driver/dashboard', [DashboardController::class, 'driver'])
        ->name('driver.dashboard');
});

Route::middleware(['auth', 'role:dispatcher'])->group(function () {
    Route::get('/dispatcher/dashboard', [DashboardController::class, 'dispatcher'])
        ->name('dispatcher.dashboard');
});

Route::middleware(['auth', 'role:finance-admin-clerk'])->group(function () {
    Route::get('/finance/dashboard', [DashboardController::class, 'finance'])
        ->name('finance.dashboard');
});

Route::middleware(['auth', 'role:sales-rep'])->group(function () {
    Route::get('/sales/dashboard', [DashboardController::class, 'sales'])
        ->name('sales.dashboard');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
