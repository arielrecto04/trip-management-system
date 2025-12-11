<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trips = Trip::all();

        // Seed 5 vehicles as example
        for ($i = 1; $i <= 5; $i++) {
            Vehicle::create([
                'current_trip_id' => null, // nullable if no trips exist
                'vin' => 'VIN' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'brand' => 'Brand' . $i,
                'model' => 'Model' . $i,
                'capacity_kg' => 1000 + ($i * 100),
                'capacity_m3' => 10 + $i,
                'is_active' => true,
                'license_plate' => 'XYZ-' . rand(1000, 9999),
            ]);
        }
    }
}
