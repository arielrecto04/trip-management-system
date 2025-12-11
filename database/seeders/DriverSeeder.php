<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $driverRole = Role::where('slug', 'driver')->first();

        // Create User
        $user = User::factory()->create([
            'name' => 'John Driver',
            'email' => 'driver1@example.com',
            'password' => Hash::make('driver123'),
            'phone_number' => '0912342689'
        ]);
        $user->roles()->attach($driverRole->id);

        // Create Driver
        Driver::create([
            'user_id' => $user->id,
            'license_number' => 'DL-1234567890',
            'license_expiration' => now()->addYears(3),
        ]);
    }
}
