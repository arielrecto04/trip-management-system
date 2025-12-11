<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        for ($i = 1; $i <= 3; $i++) {
            Warehouse::create([
                'contact_person' => $user->id,
                'name' => "Manila Central Warehouse {$i}",
                'house_number' => '123',
                'street' => 'Mabini St.',
                'region_code' => '130000000',
                'region_name' => 'National Capital Region',
                'province_code' => '133900000',
                'province_name' => 'Metro Manila',
                'city_code' => '133900000',
                'city_name' => 'Makati City',
                'barangay_code' => '133900013',
                'barangay_name' => 'Guadalupe Nuevo',
                'zip_code' => '1000',
                'latitude' => 14.5995,
                'longitude' => 120.9842,
            ]);
        }
    }
}
