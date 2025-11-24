<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RoleSeeder::class);
        $logisticManager = Role::where('slug', 'logistics-manager')->first();
        $driverRole = Role::where('slug', 'driver')->first();

        $driver = User::factory()->create([
            'name' => 'driver',
            'email' => 'driver@driver',
            'password' => 'driver',
            'phone_number' => '0123812903'
        ]);
        $driver->roles()->attach($driverRole->id);

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin',
            'password' => 'admin',
            'phone_number' => '09671178770'
        ]);

        $admin->roles()->attach($logisticManager->id);
    }
}
