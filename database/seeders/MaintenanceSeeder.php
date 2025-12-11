<?php

namespace Database\Seeders;

use App\Models\MaintenanceLog;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = Vehicle::all();

        if ($vehicles->isEmpty()) {
            $this->command->info('No vehicles found, skipping maintenance seeding.');
            return;
        }

        foreach ($vehicles as $vehicle) {
            // Seed 1-3 maintenance logs per vehicle
            $logsCount = rand(1, 3);

            for ($i = 0; $i < $logsCount; $i++) {
                $startDate = now()->subDays(rand(1, 30));
                $endDate = (clone $startDate)->addDays(rand(1, 5));

                MaintenanceLog::create([
                    'vehicle_id' => $vehicle->id,
                    'start_maintenance_date' => $startDate,
                    'end_maintenance_date' => $endDate,
                    'technician' => 'Technician ' . Str::random(4),
                    'work_performed' => 'Performed routine check and repairs',
                    'cost' => rand(1000, 5000),
                    'current_odometer' => rand(5000, 50000),
                ]);
            }
        }
    }
}
