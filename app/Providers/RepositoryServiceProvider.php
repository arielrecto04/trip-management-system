<?php

// app/Providers/RepositoryServiceProvider.php (Create this file)

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\DriverRepositoryInterface;
use App\Repositories\Eloquent\EloquentDriverRepository;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the interface to the concrete implementation
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class,
        );

        $this->app->bind(
            RoleRepositoryInterface::class,

            RoleRepository::class,
        );
        
        // ... continue binding all other interfaces (e.g., VehicleRepositoryInterface)
    }
}