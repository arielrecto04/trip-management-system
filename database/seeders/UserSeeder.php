<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Fetch roles
        $logisticManager = Role::where('slug', 'logistics-manager')->first();
        $driverRole = Role::where('slug', 'driver')->first();
        $fleetManager = Role::where('slug', 'fleet-manager')->first();
        $dispatcher = Role::where('slug', 'dispatcher')->first();
        $customerServiceRep = Role::where('slug', 'customer-service-rep')->first();
        $financeAdminClerk = Role::where('slug', 'finance-admin-clerk')->first();

        // Create users and attach roles
        $driver = User::factory()->create([
            'name' => 'Driver User',
            'email' => 'driver@driver.com',
            'password' => Hash::make('driver123'),
            'phone_number' => '0123812903'
        ]);
        $driver->roles()->attach($driverRole->id);

        $admin = User::factory()->create([
            'name' => 'Logistics Manager',
            'email' => 'admin@admin',
            'password' => Hash::make('admin'),
            'phone_number' => '09671178770'
        ]);
        $admin->roles()->attach($logisticManager->id);

        $fleet = User::factory()->create([
            'name' => 'Fleet Manager',
            'email' => 'fleet@company.com',
            'password' => Hash::make('fleet123'),
            'phone_number' => '09123456789'
        ]);
        $fleet->roles()->attach($fleetManager->id);

        $dispatcherUser = User::factory()->create([
            'name' => 'Dispatcher User',
            'email' => 'dispatcher@company.com',
            'password' => Hash::make('dispatch123'),
            'phone_number' => '09111222333'
        ]);
        $dispatcherUser->roles()->attach($dispatcher->id);

        $csr = User::factory()->create([
            'name' => 'Customer Service Rep',
            'email' => 'csr@company.com',
            'password' => Hash::make('csr123'),
            'phone_number' => '09223334444'
        ]);
        $csr->roles()->attach($customerServiceRep->id);

        $finance = User::factory()->create([
            'name' => 'Finance Admin Clerk',
            'email' => 'finance@company.com',
            'password' => Hash::make('finance123'),
            'phone_number' => '09334445555'
        ]);
        $finance->roles()->attach($financeAdminClerk->id);
    }
}
