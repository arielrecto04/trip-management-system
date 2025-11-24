<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $roles = [
            ['name' => 'Logistics Manager', 'slug' => 'logistics-manager'],
            ['name' => 'Fleet Manager', 'slug' => 'fleet-manager'],
            ['name' => 'Dispatcher', 'slug' => 'dispatcher'],
            ['name' => 'Driver', 'slug' => 'driver'],
            ['name' => 'Finance / Admin Clerk', 'slug' => 'finance-admin-clerk'],
            ['name' => 'Customer Service Rep', 'slug' => 'customer-service-rep'],
        ];

        foreach($roles as $role) {
             Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
